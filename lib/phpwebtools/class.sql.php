<?php
	// $Id$
	// $Author$

// Class: PHP.sql
//
//	SQL wrapper class
//
class sql {
  var $Connection;		// connection variable
  var $SQL_Seek_Position;	// position pointer
  var $DB_ENGINE;		// current database engine
  var $VB;			// verbose?
  var $Database;	// connected to

	// Method: sql constructor
	//
	// Parameters:
	//
	//	$type - Type of database backend to use. These are the
	//	SQL_ macros defined in macros.php
	//
	//	$options - (optional) Associative array of additional
	//	options
	//		* verbose - Set verbosity (boolean)
	//		* serialize - 'squash' or 'serialize'. Defaults to
	//		squash
	//		* host - Host computer name
	//		* user - Username for database server
	//		* password - Password for database server
	//		* database - Select database to use
	//		* path - (only for SQL_SQLLITE) Set path to
	//		SQL store
	//
  function sql ($type, $options) {
    $this->DB_ENGINE = $type;
    $this->VB        = $options['verbose'];
    // Check for serialize method, else use squash by default
    $this->serialize = isset($options['serialize']) ? $options['serialize'] :
       'squash';

    // Make options available
    extract($options);
    
    switch ($type) {
      case SQL_MSQL:
        $this->Connection = msql_pconnect ($host) or
	  DIE ("sql->constructor(SQL_MSQL) : could not connect");
	msql_select_db ($database, $this->Connection) or
	  DIE ("sql->constructor(SQL_MSQL) : could not select database");
        break; // end SQL_MSQL

      case SQL_MSSQL:
        $this->Connection = mssql_pconnect ($host, $user, $password) or
	  DIE ("sql->constructor(SQL_MSSQL) : could not connect");
	mssql_select_db ($database) or
	  DIE ("sql->constructor(SQL_MSSQL) : could not select database");
        break; // end SQL_MSSQL
	
      case SQL_MYSQL:
        $this->Connection = mysql_pconnect ($host, $user, $password) or
	  DIE ("sql->constructor(SQL_MYSQL) : could not connect");
	mysql_select_db ($database) or
	  DIE ("sql->constructor(SQL_MYSQL) : could not select database");
		$this->Database = $database;
        break; // end SQL_MYSQL
	
      case SQL_ODBC:
        $this->Connection = odbc_pconnect ($host, $user, $password) or
	  DIE ("sql->constructor(SQL_ODBC) : could not connect");
	odbc_autocommit ($this->Connection, true) or  
	  DIE ("sql->constructor(SQL_ODBC) : could not set autocommit");
        break; // end SQL_ODBC

      case SQL_ORACLE:
        $this->Connection = OCIPLogon ($user."@".$host, $password, $database) or
	  DIE ("sql->constructor(SQL_ORACLE) : could not connect");
	Ora_CommitOn ($this->Connection) or  
	  DIE ("sql->constructor(SQL_ORACLE) : could not set autocommit");
        break; // end SQL_ORACLE

      case SQL_POSTGRES:
        $this->Connection = pg_pConnect ("host=$host, user=$user, ".
	                   "password=$password dbname=$database") or
	  DIE ("sql->constructor(SQL_POSTGRES) : could not connect");
        break; // end SQL_POSTGRES

      case SQL_SQLITE:
        if (function_exists('sqlite_open')) {
		$this->Connection = @sqlite_open ($options['path'])
			or DIE("sql->constructor(SQL_SQLITE) : could not connect");
	} else {
		DIE("sql->constructor(SQL_SQLITE) : no support compiled in PHP");
	}
        break; // end SQL_POSTGRES

      default:
        DIE("sql->constructor() : invalid database engine");
	break; // end of default
    } // end type switch
  } // end constructor sql

	// Method: data_seek
	//
	//	Moves the result position pointer to a certain position. This
	//	is just a wrapper for <sql->seek>
	//
	// Parameters:
	//
	//	$result - SQL result (as returned by <sql->query>
	//
	//	$pos - Position to move to
	//
  function data_seek ($result, $pos) {
    // wrapper for sql->seek
    return $this->seek ($result, $pos);
  } // end function sql->data_seek

	// Method: db_query
	//
	//	SQL database query execute with database selection
	//
	// Parameters:
	//
	//	$database - Database to select
	//
	//	$query - SQL query string
	//
	// Returns:
	//
	//	SQL result
	//
  function db_query ($database, $query) {
    switch ($this->DB_ENGINE) {
      case SQL_MSQL:
        $result = msql_db_query ($database, $query);
        break; // end SQL_MSQL

      case SQL_MSSQL:
        $result = mssql_db_query ($database, $query);
        break; // end SQL_MSSQL

      case SQL_MYSQL:
        $result = mysql_db_query ($database, $query);
        break; // end SQL_MYSQL

      case SQL_ODBC:
        $result = odbc_exec ($this->Connection, $query);
        break; // end SQL_ODBC

      case SQL_ORACLE:
        // FIXME!: probably broken
        OCIParse ($this->Connection, $query);
	OCIExecute ($result);
	OCICommit ($this->Connection);
        break; // end SQL_ORACLE

      case SQL_POSTGRES:
        $result = pg_exec ($this->Connection, $query);
        break; // end SQL_POSTGRES

      default:
        DIE("sql->db_query() : invalid database engine");
	break; // end of default
    } // end type switch

    // common code
    if ($this->VB and !($result)) echo "ERROR";
    $this->SQL_Seek_Position["$result"] = 0;
    return $result;
  } // end function sql->db_query

	// Method: fetch_array
	//
	//	Returns an associative array for the next table row
	//	of an SQL result. Functions identically to mysql_fetch_array.
	//
	// Parameters:
	//
	//	$result - SQL result
	//
	// Returns:
	//
	//	Associative array.
	//
	function fetch_array ($result) {
		if ($this->num_rows($result) <= $this->SQL_Seek_Position["$result"])
			return NULL;
		switch ($this->DB_ENGINE) {
			case SQL_MSQL:
			msql_data_seek ($result, $this->SQL_Seek_Position["$result"]);
			$this_result = msql_fetch_array ($result);
			break; // end SQL_MSQL

			case SQL_MSSQL:
			mssql_data_seek ($result, $this->SQL_Seek_Position["$result"]);
			$this_result = mssql_fetch_array ($result);
			break; // end SQL_MSSQL

			case SQL_MYSQL:
			mysql_data_seek ($result, $this->SQL_Seek_Position["$result"]);
			$this_result = mysql_fetch_array ($result);
			break; // end SQL_MYSQL

			case SQL_ODBC:
			$odbc_cols = odbc_fetch_into ($result,
				$this->SQL_Seek_Position["$result"],
				&$raw_array);
			unset ($this_result);  
			for ($i=0;$i<$odbc_cols;$i++) {
				$this_name = odbc_field_name ($result, $i);
				$this_result["$this_name"] = $raw_array[$i];
			} // end looping through columns
			break; // end SQL_ODBC

			case SQL_ORACLE:
			// FIXME!: this is probably *very* broken
			OCIFetchInto ($result, &$this_result, OCI_ASSOC);
			break; // end SQL_ORACLE

			case SQL_POSTGRES:
			$this_result = pg_Fetch_Array ($result,
			$this->SQL_Seek_Position["$result"]);
			break; // end SQL_POSTGRES

			case SQL_SQLITE:
				// Check for past indexing count, so while loops work
			if ($this->SQL_Seek_Position["$result"] >= count($this->_cache["$result"])) return false;
				// Get actual result set
			$res_set = $this->_cache["$result"][$this->SQL_Seek_Position["$result"]];
				// Get key list
			$keys = sqlite_fetch_field_array($result);
				// Loop through result set and key list
			foreach ($res_set AS $k => $v) {
				$this_result[$keys[$k]] = stripslashes($v);
			}
			break; // end SQL_SQLITE

			default:
			DIE("sql->fetch_array() : invalid database engine");
			break; // end of default
		} // end type switch
    
		// common stuff
		$this->SQL_Seek_Position["$result"]++;
		return $this_result;
	} // end function sql->fetch_array

	// Method: last_record
	//
	//	Returns the last SERIAL field to be added to a database.
	//	A result and/or table name may be needed, depending on
	//	the database engine that is being used.
	//
	// Parameters:
	//
	//	$result - (optionally needed) SQL result (from an
	//	<sql->query> call)
	//
	//	$table - (optionally needed) Database table name
	//
	// Returns:
	//
	//	Integer, value of last SERIAL column set.
	//
	function last_record ($result = NULL, $table = NULL) {
		switch ($this->DB_ENGINE) {
			case SQL_MSQL:
			return msql_insert_id ($result);
			break; // end SQL_MSQL

			case SQL_MSSQL:
			die ("sql->last_record (mssql): not implemented");
			break; // end SQL_MSSQL

			case SQL_MYSQL:
			// OLD CODE:
			//if ($table != NULL) return mysql_insert_id ($result, $table);
			// else return mysql_insert_id ($result);
			// NEW CODE (thanks to Fred Forester):
			return mysql_insert_id ( $this->Connection ); 
			break; // end SQL_MYSQL

			case SQL_ODBC:
			die ("sql->last_record (odbc): not implemented");
			break; // end SQL_ODBC

			case SQL_ORACLE:
			die ("sql->last_record (oracle): not implemented");
			break; // end SQL_ORACLE

			case SQL_POSTGRES:
			return pg_getlastoid ($result);
			break; // end SQL_POSTGRES

			case SQL_SQLITE:
			return sqlite_last_insert_rowid($this->Connection);
			break; // end SQL_SQLITE

			default:
			DIE("sql->last_record() : invalid database engine");
			break;
		} // end of DB_ENGINE SWITCH
	} // end function sql->last_record

	// Method: num_rows
	//
	//	Get number of rows in a result
	//
	// Parameters:
	//
	//	$result - SQL query result reference (as returned by
	//	<sql->query>
	//
	// Returns:
	//
	//	Integer, number of rows in a result
	//
  function num_rows ($result) {
    if ($result == 0) return 0;
    switch ($this->DB_ENGINE) {
      case SQL_MSQL:
        $num = msql_num_rows ($result);
        break; // end SQL_MSQL

      case SQL_MSSQL:
        $num = mssql_num_rows ($result);
        break; // end SQL_MSSQL

      case SQL_MYSQL:
        $num = mysql_num_rows ($result);
        break; // end SQL_MYSQL

      case SQL_ODBC:
        $num = odbc_num_rows ($result);
        break; // end SQL_ODBC

      case SQL_ORACLE:
        $num = OCIRowCount ($result);
        break; // end SQL_ORACLE

      case SQL_POSTGRES:
        $num = pg_Num_Rows ($result);
        break; // end SQL_POSTGRES

      case SQL_SQLITE:
        $num = count($this->_cache["$result"]);
	break; // end SQL_SQLITE

      default:
        DIE("sql->num_rows() : invalid database engine");
	break; // end of default
    } // end type switch

    // common stuff
    if ($this->VB and !($num)) echo _("ERROR");
    return $num;
  } // end function sql->num_rows

	// Method: query
	//
	//	Execute an SQL query using the current SQL engine
	//
	// Parameters:
	//
	//	$query - Text of SQL query
	//
	// Returns:
	//
	//	SQL result reference
	//
  function query ($query) {
    switch ($this->DB_ENGINE) {
      case SQL_MSQL:
        $result = msql_query ($query);
        break; // end SQL_MSQL

      case SQL_MSSQL:
        $result = mssql_query ($query);
        break; // end SQL_MSSQL

      case SQL_MYSQL:
        $result = mysql_query ($query);
        break; // end SQL_MYSQL

      case SQL_ODBC:
        $result = odbc_exec ($this->Connection, $query);
        break; // end SQL_ODBC

      case SQL_ORACLE:
        // FIXME!: probably broken
        OCIParse ($this->Connection, $query);
	OCIExecute ($result);
	OCICommit ($this->Connection);
        break; // end SQL_ORACLE

      case SQL_POSTGRES:
        $result = pg_exec ($this->Connection, $query);
        break; // end SQL_POSTGRES

      case SQL_SQLITE:
        // Check if query is valid
	if (!sqlite_complete($query.';')) {
          DIE("sql->query(sqlite) : malformed query");
	}
        $result = sqlite_exec($query.';', $this->Connection);
	$this->_cache["$result"] = sqlite_fetch_array($result);
	break; // end SQL_SQLITE

      default:
        DIE("sql->query() : invalid database engine");
	break; // end of default
    } // end type switch

    // common code
    if ($this->VB and !($result)) echo "ERROR";
    $this->SQL_Seek_Position["$result"] = 0;
    return $result;
  } // end function sql->query

	// Method: results
	//
	//	Determine if any results were generated from the
	//	specified query
	//
	// Parameters:
	//
	//	$result - SQL query result (as generated by <sql->query>
	//
	// Returns:
	//
	//	Boolean, whether there are any results
	function results ($result) {
		return ($result and ($this->num_rows($result)>0));
	} // end function sql->result

	// Method: seek
	//
	//	Moves the result position pointer to a certain position.
	//
	// Parameters:
	//
	//	$result - SQL result (as returned by <sql->query>
	//
	//	$pos - Position to move to
	//
	function seek ($result, $pos) {
		// move internal pointer
		$this->SQL_Seek_Position["$result"] = $pos;
	} // end function sql->seek

  /* SQL query wrappers ------------------------------------------------ */

	// Method: create_table_query
	//
	//	SQL query wrapper for creating an SQL table definition
	//
	// Parameters:
	//
	//	$table - Name of table
	//
	//	$values - Associative array describing the table
	//	definition
	//
	//	$keys - (optional) Array of keys. The first entry is
	//	considered the table's primary key.
	//
	// Returns:
	//
	//	SQL table definition query text
	//
  function create_table_query ($table, $values, $keys=NULL) {
    // clear query
    $query = "";

    // produce first portion
    $query .= "CREATE TABLE ".addslashes($table)." ( ";

    // loop through values
    reset ($values); $in_loop = false;
    while (list ($k, $v) = each ($values)) {

      // Check for an array, then handle that and fake like we're normal
      if (is_array($v)) {
        // Split into type and elements
        list ($type, $elements) = $v;

	// Handle type, save elements for later
	switch ($type) {
          case SQL__ENUM():
	    $v = $type;
	    break; // end of known cases
	
          default:
	    die("class.sql.php :: invalid array type presented");
	    break; // kinda useless here
	} // end switch for type
      } // end array handling

      // if there is a bit shifted portion, get that
      $bit_shifted = (int)( ($v & SQL__THRESHHOLD) >>SQL__BIT_SHIFT);
    
      // build depending on params
      switch ((($v & SQL__THRESHHOLD) % pow(2,SQL__BIT_SHIFT))) {
      
        case SQL__BLOB:
         $query .= ( $in_loop ? ", " : " " ).addslashes($k)." BLOB";
	 break; // end SQL__BLOB

	case SQL__CHAR(0):
         $query .= ( $in_loop ? ", " : " " ).addslashes($k).
	  " CHAR(".($bit_shifted + 0).")".
			// check for NOT NULL
		( ((($v>>(SQL__BIT_SHIFT*3))&2) == 2 ) ?
		( ($this->DB_ENGINE != SQL_SQLITE) ? " NOT NULL" : "" ) : "" );
	 break; // end SQL__CHAR

        case SQL__DATE:
         $query .= ( $in_loop ? ", " : " " ).addslashes($k)." DATE".
			// check for NOT NULL
		( ((($v>>(SQL__BIT_SHIFT*3))&2) == 2 ) ?
		( ($this->DB_ENGINE != SQL_SQLITE) ? " NOT NULL" : "" ) : "" );
	 break; // end SQL__DATE

	case SQL__DOUBLE(0):
         $query .= ( $in_loop ? ", " : " " ).addslashes($k).
	  " DOUBLE". ( ($bit_shifted>0) ? "(".($bit_shifted + 0).")" : "" );
	 break; // end SQL__DOUBLE

	case SQL__ENUM():
	 // start header
         $query .= ( $in_loop ? ", " : " " ).addslashes($k).  " ENUM ( ";
	 // loop through values
	 $in_internal_loop = false;
	 foreach ($elements AS $internal_k => $internal_v) {
           $query .= ( $in_internal_loop ? ", " : " " ).
	   	"'".addslashes($internal_v)."'";
	   $in_internal_loop = true;
	 } // end internal loop
	 // create footer
	 $query .= " ) ";
	 // check for NOT NULL
	 $query .= ( ((($v>>(SQL__BIT_SHIFT*3))&2) == 2 ) ?
		( ($this->DB_ENGINE != SQL_SQLITE) ? " NOT NULL" : "" ) : "" );
	 break; // end SQL__ENUM

	case SQL__INT(0):
	case SQL__INT_UNSIGNED(0):
	 // Handle sqlite
         if ($this->DB_ENGINE == SQL_SQLITE) {
	   $query .= ( $in_loop ? ", " : " " ).addslashes($k).
	    " INTEGER".
	    // sqlite has no AUTO_INCREMENT, so we kludge it with INTEGER PRIMARY KEY
	    ( ((($v>>(SQL__BIT_SHIFT*3))&1) == 1 ) ?
	    " PRIMARY KEY" : "" );
           break;
	 }
         // Handle PostgreSQL
	 if ($this->DB_ENGINE == SQL_POSTGRES) {
	   $query .= ( $in_loop ? ", " : " " ).addslashes($k).
	    // PostgreSQL uses SERIAL instead of INT AUTO_INCREMENT
	    ( ((($v>>(SQL__BIT_SHIFT*3))&1) == 1 ) ?
	    " SERIAL" :
	    " INT". ( ($bit_shifted>0) ? "(".($bit_shifted + 0).")" : "" ).
	    ( ((($v & SQL__THRESHHOLD) % pow(2,SQL__BIT_SHIFT))==SQL__INT_UNSIGNED(0)) ?
	    " UNSIGNED" : "" ) );
           break;
	 }
	 // Handle everything else
         $query .= ( $in_loop ? ", " : " " ).addslashes($k).
	  " INT". ( ($bit_shifted>0) ? "(".($bit_shifted + 0).")" : "" ).
	  ( ((($v & SQL__THRESHHOLD) % pow(2,SQL__BIT_SHIFT))==SQL__INT_UNSIGNED(0)) ?
	    " UNSIGNED" : "" ).
			// check for NOT NULL
		( ((($v>>(SQL__BIT_SHIFT*3))&2) == 2 ) ?
		( ($this->DB_ENGINE != SQL_SQLITE) ? " NOT NULL" : "" ) : "" ).
			// check for auto_increment
		( ((($v>>(SQL__BIT_SHIFT*3))&1) == 1 ) ?
		" AUTO_INCREMENT" : "" );
	 break; // end SQL__{INT,INT_UNSIGNED} {AUTO_INCREMENT}

	case SQL__REAL:
         $query .= ( $in_loop ? ", " : " " ).addslashes($k)." REAL";
	 break; // end SQL__REAL

	case SQL__SERIAL:
	 $query .= ( $in_loop ? ", " : " " ).addslashes($k);
	 switch ($this->DB_ENGINE) {
           case SQL_SQLITE:
	   $query .= " INTEGER PRIMARY KEY";
           break;
	
	   case SQL_POSTGRES:
	   $query .= " SERIAL";
           break;

	   case SQL_MYSQL:
	   $query .= " INT UNSIGNED NOT NULL AUTO_INCREMENT";
           break;

	   default:
	   die('class.sql.php: SQL_SERIAL not supported by this database engine');
	   break;
         }
	 break; // end SQL__SERIAL

	case SQL__TEXT:
	 // this maps to BLOB unless you use MySQL
         $query .= ( $in_loop ? ", " : " " ).addslashes($k)." ".
	  ( ($this->DB_ENGINE==SQL_MYSQL) ? "BLOB" : "TEXT");
	 break; // end SQL__TEXT

	case SQL__VARCHAR(0):
         $query .= ( $in_loop ? ", " : " " ).addslashes($k).
	  " ". ( ($this->DB_ENGINE==SQL_MYSQL) ? "VARCHAR" : "CHAR" ).
	  "(".($bit_shifted + 0).")".
			// check for NOT NULL
		( ((($v>>(SQL__BIT_SHIFT*3))&2) == 2 ) ?
		( ($this->DB_ENGINE != SQL_SQLITE) ? " NOT NULL" : "" ) : "" );
	 break; // end SQL__VARCHAR

	case SQL__TIMESTAMP(0):
         $query .= ( $in_loop ? ", " : " " ).addslashes($k).
	  " TIMESTAMP(".($bit_shifted + 0).")";
	 break; // end SQL__TIMESTAMP

        default: // check for errors
	 echo("sql->create_table_query : improper type<br/>\n");
	 break; // end everything else

      } // end value switch
      $in_loop = true;
    } // end while loop through values

    if (!empty($keys) and ($this->DB_ENGINE != SQL_SQLITE)) {
      $query .= ", PRIMARY KEY (".addslashes($keys[0]).")";
      if (count($keys) > 1) {
        // Add all but the first key as KEY(a, b, c) etc ...
        $_keys = $keys;
        unset($_keys[0]);
	foreach ($_keys AS $this_key) { $query .= ", KEY(".$this_key.")"; }
      }
    } // end if not empty $keys

    // add ending braces (thanks to Fred Forrester)
    $query .= " ) ";

    // return query
    return $query;
  } // end function sql->create_table_query

	// Method: drop_table_query
	//
	//	SQL query wrapper for dropping a table
	//
	// Parameters:
	//
	//	$table - Name of table
	//
	// Returns:
	//
	//	SQL query text
	//
  function drop_table_query ($table) {
    // produce first portion
    $query = "DROP TABLE ".addslashes($table);

    return $query;
  } // end function sql->drop_table_query

	// Method: insert_query
	//
	//	SQL query wrapper for SQL INSERT command
	//
	// Parameters:
	//
	//	$table - Name of table
	//
	//	$values - Associative array of values to insert into the
	//	specified table. Keys are the table column names and
	//	values are the values to be inserted into the table.
	//	Arrays and values with special SQL characters are handled
	//	properly.
	//
	// Returns:
	//
	//	SQL query text
	//
  function insert_query ($table, $values) {
    // clear query
    $query = "";

    // loop through values
    reset ($values); $in_loop = false; $values_hash = ""; $cols_hash = "";
    while (list ($k, $v) = each ($values)) {
      // decide whether we use the key/value pairs or just the value
      if ( (($k+0)>0) or (empty($k)) ) {
        $k = $v; 		// pass name pair
	$v = $GLOBALS[$v];	// grab value from GLOBALS[]
      } // end checking for key/value pairs

      // add to hashes
      if ("$v" == SQL__NOW) {
        $values_hash .= ( $in_loop ? ", " : " " )."NOW()";
      } else {
        $values_hash .= ( $in_loop ? ", " : " " ).
          "'".addslashes($this->squash($v))."'";
      }
      $cols_hash .= ( $in_loop ? ", " : " " ).	
        addslashes($this->squash($k));
      $in_loop = true;
    } // end while loop through values
    
    // form query from parts
    $query .= "INSERT INTO ".addslashes($table).
      " ( ".$cols_hash." ) ".
      "VALUES ( ".$values_hash." )";

    // return formed query
    return $query;
  } // end function sql->insert_query

	// Method: update_query
	//
	//	SQL query wrapper for SQL UPDATE command
	//
	// Parameters:
	//
	//	$table - Name of table
	//
	//	$values - Associative array of values to update in the
	//	specified table. Keys are the table column names and
	//	values are the values to be updated in the table.
	//	Arrays and values with special SQL characters are handled
	//	properly.
	//
	//	$where_var - Associative array of WHERE clause conditions
	//	for the table update. These are in the form of key =
	//	value.
	//
	// Returns:
	//
	//	SQL query text
	//
  function update_query ($table, $values, $where_var) {
    // clear query
    $query = "";

    // loop through values
    reset ($values); $in_loop = false; $values_hash = "";
    while (list ($k, $v) = each ($values)) {
      // decide whether we use the key/value pairs or just the value
      if ( (($k+0)>0) or (empty($k)) ) {
        $k = $v; 		// pass name pair
	$v = $GLOBALS[$v];	// grab value from GLOBALS[]
      } // end checking for key/value pairs
      
      // add to hashes
      if ("$v" == SQL__NOW) {
        $values_hash .= ( $in_loop ? ", " : " " ).
          $k . " = NOW()";
      } else {
        $values_hash .= ( $in_loop ? ", " : " " ).
          $k . " = '".addslashes($this->squash($v))."'";
      }
      $in_loop = true;
    } // end while loop through values
    
    // figure out wherevar syntax
    if (is_array ($where_var)) { // if an array, then array (name => val)
      reset ($where_var);
      list ($where_key, $where_val) = each ($where_var);
    } else { // if no array, it must be the name of the variable
      $where_key = $where_var;
      $where_val = $GLOBALS["$where_var"];
    } // end checking
    
    // form query from parts
    $query .= "UPDATE ".addslashes($table)." ".
      "SET ".$values_hash." ".
      "WHERE ( ".$where_key." = '".addslashes($where_val)."' )";

    // return formed query
    return $query;
  } // end function sql->update_query

	// Method: select_record_query
	//
	//	SQL query wrapper for simple SELECT queries
	//
	// Parameters:
	//
	//	$table - Name of the table
	//
	//	$criteria - Associative array of selection crieria. This
	//	is in the form of key = value.
	//
	//	$fields - (optional) Array of fields to select. Defaults
	//	to all.
	//
	// Returns:
	//
	//	SQL query text
	//
	function select_record_query ( $table, $criteria, $fields=NULL ) {
		// Check for improper parameters
		if (empty($table)) return false;
		if (!is_array($criteria)) return false;

		// Form query up to WHERE clause
		$query = "SELECT ".(
			($fields==NULL) ? "*" : join(",",$fields)
			)." FROM ".addslashes($table)." WHERE ";

		// Form where clause step by step
		$first_where = true; // for first iteration
		foreach ( $criteria AS $key => $value ) {
			if (is_array($value)) {
				print "SQL: FIXME! NEED TO SUPPORT!\n";
			} else {
				// If it's scalar, join in on
				if (!$first_where) $query .= " AND ";
				$first_where = false; // unset this now
				$query .= "(".addslashes($key)."=".
					"'".addslashes($value)."')";
			} // end checking for value being an array
		} // end foreach criteria

		// Return the finished query
		return $query;
	} // end function sql->select_record_query

  // functions for database info
  function list_dbs($dummy="")
  {
	$result = "";
    switch ($this->DB_ENGINE) {
      case SQL_MYSQL:
        $result = mysql_list_dbs($this->Connection);
        break; // end SQL_MYSQL

      default:
        DIE("sql->list_dbs() : invalid database engine");
		break; // end of default

    } // end type switch
	return $result;

  } // end list_dbs

	function tablename($r,$i) { return $this->table_name($r,$i); }
	function table_name($res,$index) {
		switch ($this->DB_ENGINE) {
			case SQL_MYSQL:
			$result = mysql_tablename($res,$index);
			break; // end SQL_MYSQL

			default:
			DIE("sql->table_name() : invalid database engine");
			break; // end of default
		} // end type switch
		return $result;
	} // end sql->table_name

	function listtables() { return $this->list_tables(); }
	function list_tables($dummy="") {
		switch ($this->DB_ENGINE) {
			case SQL_MYSQL:
			$result = mysql_listtables($this->Database);
			break; // end SQL_MYSQL

			default:
			DIE("sql->list_tables() : invalid database engine");
			break; // end of default
		} // end type switch
		return $result;
	} // end tablename

	function num_fields ( $res ) {
		switch ($this->DB_ENGINE) {
			case SQL_MYSQL:
			$result = mysql_num_fields($res);
			break; // end SQL_MYSQL

			case SQL_SQLITE:
			$result = count(sqlite_fetch_field_array($res));
			break; // end SQL_SQLITE

			default:
			DIE("sql->num_fields() : invalid database engine");
			break; // end of default
		} // end type switch
		return $result;
	} // end sql->num_fields

	function field_name($res, $index) {
		switch ($this->DB_ENGINE) {
			case SQL_MYSQL:
			$result = mysql_field_name($res,$index);
			break; // end SQL_MYSQL

			case SQL_SQLITE:
			$result_array = sqlite_fetch_field_array($res);
			$result = $result_array["$index"];
			break; // end SQL_SQLITE

			default:
			DIE("sql->field_name() : invalid database engine");
			break; // end of default
		} // end type switch
		return $result;
	} // end sql->field_name

	function distinct_values ( $table, $field ) {
		$result = $this->query("SELECT DISTINCT ".addslashes($field).
			" FROM ".addslashes($table)." ORDER BY ".
			addslashes($field));
		// If nothing, return a null array
		if (!$this->results($result)) return array();

		// Otherwise, run through the values and parse
		unset($values);
		while ($r = $this->fetch_array($result)) {
			if (!empty($r[$field])) {
				$values[(stripslashes($r[$field]))] = stripslashes($r[$field]);
			}
		} // end looping through values
		return $values;
	} // end function sql->distinct_values

	function generate_hash ( $table, $key, $val, $clause="" ) {
		// Decide if we're dealing with a hash for key or value
		if (!(strpos($key, "##") === false)) {
			$k_format = explode("##", $key);
			foreach ($k_format as $k_k => $k_v) {
				if (!($k_k & 1) ) continue;
				else $query_vars[] = $k_v;
			}
		} else {
			$query_vars[] = $key;
		}

		if (!(strpos($val, "##") === false)) {
			$v_format = explode("##", $val);
			foreach ($v_format as $v_k => $v_v) {
				if (!($v_k & 1) ) continue;
				else $query_vars[] = $v_v;
			}
		} else {
			$query_vars[] = $val;
		}

		// Create and perform query
		$query = "SELECT ".join(",", $query_vars)." FROM ".
			$table." ".$clause;
		$result = $this->query($query);

		// Create result array
		while ($r = $this->fetch_array($result)) {
			$_k = $_v = "";
		
			if (!(strpos($key, "##") === false)) {
				$k_format = explode("##", $key);
				foreach ($k_format as $k_k => $k_v) {
					if (!($k_k & 1) ) $_k .= $k_v;
					else $_k .= $r[$k_v];
				}
			} else {
				$_k = $r[$key];
			}
			
			if (!(strpos($val, "##") === false)) {
				$v_format = explode("##", $val);
				foreach ($v_format as $v_k => $v_v) {
					if (!($v_k & 1) ) $_v .= $v_v;
					else $_v .= $r[$v_v];
			 	}
			} else {
				$_v = $r[$val];
			}

			$hash[$_k] = $_v;
		} // end fetch array

		// Return array/hash
		return $hash;
	} // end function sql->generate_hash

	function squash ($value) {
		// If this isn't an array, we don't need to squash
		if (!is_array($value)) { return $value; }

		// Check depending on squash method
		switch (strtolower($this->serialize)) {
			case 'serialize':
				return serialize($value);
				break;
		
			case 'squash':
			default:
				return join(',', $value);
				break;
		}
	} // end function sql->squash

	function unsquash ($value) {
		switch (strtolower($this->serialize)) {
			case 'serialize':
				// Check to see if false
				if (unserialize($value)) {
					return unserialize($value);
				} else {
					// Returns false if not serialized
					// to begin with.
					return $value;
				}
				break;

			case 'squash':
			default:
				// Check for delimiter
				if (!(strpos($value, ':') === false)) {
					return explode(':', $value);
				} else {
					return $value;
				}
				break;
		}
	} // end function sql->unsquash
} // end class sql

?>
