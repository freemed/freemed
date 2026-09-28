<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('net.php.pear.DB');
LoadObjectDependency('org.freemedsoftware.core.SqlIdent');

define ( 'SQL__NOW', 			"~~~~~NOW~~~~~" );

// Class: org.freemedsoftware.core.FreemedDb
//
class FreemedDb extends DB {

	private $db;
	private $data;

	public function __construct (  ) { $this->init( ); }

	public function __wakeup ( ) { $this->init( ); }

	protected function init ( $multi_query = false ) {
		PEAR::setErrorHandling ( PEAR_ERROR_RETURN );
		$uri = DB_ENGINE . "://". DB_USER .":". DB_PASSWORD ."@". DB_HOST ."/". DB_NAME;
		//$this->db =& DB::factory ( DB_ENGINE );
		$this->db = DB::connect($uri);
		if ( $this->db instanceof PEAR_Error ) {
			print_r($this->db);
			trigger_error ( $this->db->getMessage(), E_USER_ERROR );
		}

		$this->db->setFetchMode( DB_FETCHMODE_ASSOC );
		/*
		$this->db->loadModule( 'Extended' );
		$this->db->loadModule( 'Manager' );
		$this->db->loadModule( 'Reverse' );
		$this->db->loadModule( 'Function' );
		*/

		// Required multi query option for stored procedures
		$this->db->setOption( 'multi_query', $multi_query );

		// Turn off "portability" option, which stops forcing lowercase keys
		$this->db->setOption( 'portability', false );
	} // end method init

	// Method: GetMDB2Object
	//
	//	Get underlying MDB2 object.
	//
	public function GetMDB2Object ( ) {
		return $this->db;
	} // end method GetMDB2Object

	// Method: __call
	//
	//	Magic method to push calls which come into this object into the $db
	//	object.
	//
	function __call ( $method, $param ) {
		if ( method_exists ( $this, $method ) ) {
			$value = call_user_func_array ( array ( $this, $method ), $param );
		} elseif ( method_exists ( $this->db, $method ) ) {
			$value = call_user_func_array ( array ( $this->db, $method ), $param );
		//} elseif ( method_exists ( $this->db->function, $method ) ) {
		//	$value = call_user_func_array ( array ( $this->db->function, $method ), $param );
		} else {
			trigger_error ( "Could not load method $method", E_USER_ERROR );
		}
		if ( $value instanceof PEAR_Error ) {
			syslog( LOG_ERR, "FreemedDb: " . $value->userinfo );
			return false;
		}
		return $value;
	} // end method __call

	// MDB2 compatibility functions	
	public function queryAll( $query ) { return $this->db->getAll($query); }
	public function queryOne( $query ) { return $this->db->getOne($query); }
	public function queryRow( $query ) { return $this->db->getRow($query); }
	public function queryCol( $query ) { return $this->db->getCol($query); }
	public function escape  ( $val   ) { return $this->db->escapeSimple($val); }

	// Method: queryOneStoredProc
	//
	//	queryOne wrapper for MDB2 to work around the necessity of reconnecting
	//	using multi_query to execute stored procedures, which otherwise causes
	//	massive ugly failures.
	//
	// Parameters:
	//
	//	$query - SQL query
	//
	// Returns:
	//
	//	PEAR::Error on error, or value on success.
	//
	public function queryOneStoredProc ( $query ) {
		$this->init( true );
		$res = $this->db->getOne( $query );
		$this->init( false );
		return $res;
	} // end method queryOneStoredProc

	// Method: queryAllStoredProc
	//
	//	queryAll wrapper for MDB2 to work around the necessity of reconnecting
	//	using multi_query to execute stored procedures, which otherwise causes
	//	massive ugly failures.
	//
	// Parameters:
	//
	//	$query - SQL query
	//
	// Returns:
	//
	//	PEAR::Error on error, or array of hashes on success.
	//
	public function queryAllStoredProc ( $query ) {
		$this->init( true );
		$res = $this->db->getAll( $query );
		$this->init( false );
		return $res;
	} // end method queryAllStoredProc

	// Method: load_data
	//
	//	Load data to be used for insert and update queries which are not
	//	explicitly specified.
	//
	// Parameters:
	//
	//	$values - hash of data
	//
	public function load_data ( $values ) {
		if ( !is_array( $values ) and !is_object( $values ) ) { return false; }
		unset ($this->data);
		foreach ( $values AS $k => $v ) {
			$this->data[$k] = $v;
		}	
	} // end public function load_data

	// Method: get_link
	//
	//	Retrieve a linked record by table name and index.
	//
	// Parameters:
	//
	//	$table - Table name
	//
	//	$key - Key value for field
	//
	//	$field - (optional) Field name to index by. Defaults to 'id'
	//
	// Returns:
	//
	//	Hash of table row.
	//
	public function get_link ( $table, $key, $field = 'id' ) {
		// Category B+A (2.6b F1): escapeSimple()/addslashes() escape *values*;
		// neither quotes an identifier. The table and field names are validated
		// and emitted by SqlIdent (a refusal is logged, never fatal - R12), and
		// the key is handed to the driver's quote(), which supplies the quotes.
		$table_id = SqlIdent::name( $table );
		$field_id = SqlIdent::name( $field );
		if ( $table_id === false or $field_id === false ) {
			syslog( LOG_ERR, 'FreemedDb::get_link| refusing invalid identifier '.var_export(array($table, $field), true) );
			return NULL;
		}
		$query = sprintf( 'SELECT * FROM %s WHERE %s = %s LIMIT 1',
			$table_id, $field_id, $this->db->quote( $key ) );
		return $this->db->getAll( $query )[0];
	} // end public function get_link

	// Method: distinct_values
	//
	//	Produce a list of distinct values for an SQL table field
	//
	// Parameters:
	//
	//	$table - SQL table name
	//
	//	$field - SQL field name
	//
	//	$where - (optional) Where clause contents. Example: "msgid=3" or
	//	"id=10 AND patient=12
	//
	// Returns:
	//
	//	Array of distinct values for the selected field
	//
	public function distinct_values ( $table, $field, $where = NULL ) {
		// Category B: table/field identifiers (escapeSimple() is not an
		// identifier quoter); a refusal is logged, never fatal (R12).
		$table_id = SqlIdent::name( $table );
		$field_id = SqlIdent::name( $field );
		if ( $table_id === false or $field_id === false ) {
			syslog( LOG_ERR, 'FreemedDb::distinct_values| refusing invalid identifier '.var_export(array($table, $field), true) );
			return array();
		}
		// Category C (2.6b): $where is a caller-composed WHERE fragment and no
		// in-tree caller passes it (SupportModule::distinct calls this with two
		// arguments). A DB layer cannot quote predicates it did not compose, so
		// the fragment is shape-checked here instead: anything that could
		// terminate the statement (`;`), open a comment (`--`, `/*`, `#`) or
		// re-quote an identifier (backtick) is refused with a log line and no
		// query is sent. Refused rather than dropped: dropping a filter would
		// return every row's distinct values instead of the filtered set.
		$where_sql = ' ';
		if ( $where !== NULL and trim( (string) $where ) != '' ) {
			if ( !is_string( $where ) or preg_match( '/[;`\x00]|--|\/\*|#/', $where ) ) {
				syslog( LOG_ERR, 'FreemedDb::distinct_values| refusing unsafe where fragment '.var_export($where, true) );
				return array();
			}
			$where_sql = sprintf(' WHERE %s ', $where);
		}
		$query = sprintf( 'SELECT DISTINCT %s FROM %s %s ORDER BY %s',
			$field_id, $table_id,
			$where_sql,
			$field_id );
		$result = $this->db->queryCol( $query );
		if ( $result instanceof PEAR_Error ) { return array ( ); }
		return $result;
	} // end public function distinct_values

	// Method: insert_query
	//
	//	Form an SQL INSERT query.
	//
	// Parameters:
	//
	//	$table - Table name
	//
	//	$values - Hash of values
	//
	//	$date_fields - (optional) Array of date/timestamp fields
	//	which should remove invalid entries rather than commit them.
	//
	// Returns:
	//
	//	INSERT SQL query
	//
	public function insert_query ( $table, $values, $date_fields=NULL ) {
		// Category B: table identifier (refused by log, R12).
		$table_id = SqlIdent::name( $table );
		if ( $table_id === false ) {
			syslog( LOG_ERR, 'FreemedDb::insert_query| refusing invalid table name '.var_export($table, true) );
			return false;
		}
		$values_hash = "";
		$cols_hash = "";
		$in_loop = false;
		foreach ($values AS $k => $v) {
			if ( is_int($k) or empty( $k ) ) {
				$k = $v; $v = $this->data[$k];
			}

			// Check for date_fields
			if ($date_fields != NULL) {
				$found = false;
				foreach ($date_fields AS $df) {
					if ($df == $k) { $found = true; }
				}
				// Check for bad values
				if ( $found ) {
					if ( $v == '' ) {
						continue;
					}
					if ( $v == '0000-00-00' ) {
						continue;
					}
					if ( $v == '0000-00-00 00:00:00' ) {
						continue;
					}
				}
			}

			// Handle timestamp
			if ("${v}" == SQL__NOW) {
				$values_hash .= ( $in_loop ? ", " : " " )."NOW()";
			} else {
				$values_hash .= ( $in_loop ? ", " : " " ).( "${v}" == "" ? "''" : $this->db->quote( is_array($v) ? join(',', $v) : $v ) );
			}
			// Category B: the column name is an identifier, not a value.
			$col = SqlIdent::name( $k );
			if ( $col === false ) {
				syslog( LOG_ERR, 'FreemedDb::insert_query| refusing invalid column name '.var_export($k, true) );
				return false;
			}
			$cols_hash .= ( $in_loop ? ", " : " " ).$col;
			$in_loop = true;
		}

		$query = sprintf( 'INSERT INTO %s ( %s ) VALUES ( %s )', $table_id, $cols_hash, $values_hash );
		return $query;
	} // end public function insert_query 

	// Method: update_query
	//
	//	Form an SQL UPDATE query.
	//
	// Parameters:
	//
	//	$table - Table name
	//
	//	$values - Hash of values
	//
	//	$where - Hash of values to qualify the update
	//
	//	$date_fields - (optional) Array of date/timestamp fields
	//	which should remove invalid entries rather than commit them.
	//
	// Returns:
	//
	//	UPDATE SQL query
	//
	public function update_query ( $table, $values, $where, $date_fields=NULL ) {
		// Category B: table identifier (refused by log, R12).
		$table_id = SqlIdent::name( $table );
		if ( $table_id === false ) {
			syslog( LOG_ERR, 'FreemedDb::update_query| refusing invalid table name '.var_export($table, true) );
			return false;
		}
		foreach ( $values AS $k => $v ) {
			if ( ((int)$k > 0) or empty( $k ) ) {
				$k = $v; $v = $this->data[$k];
			}

			// Category B: the column name is an identifier, not a value.
			$col = SqlIdent::name( $k );
			if ( $col === false ) {
				syslog( LOG_ERR, 'FreemedDb::update_query| refusing invalid column name '.var_export($k, true) );
				return false;
			}

			// Check for date_fields
			if ($date_fields != NULL) {
				$found = false;
				foreach ($date_fields AS $df) {
					if ($df == $k) { $found = true; }
				}
				// Check for bad values
				if ( $found ) {
					if ( $v == '' ) {
						$values_clause[] = $col." = NULL";
						continue;
					}
					if ( $v == '0000-00-00' ) {
						$values_clause[] = $col." = NULL";
						continue;
					}
				}
			}

			// Handle timestamp
			if ("{$v}" == SQL__NOW) {
				print "timestamp\n";
				$values_clause[] = $col." = NOW()";
			} else {
				if ( $v !== '' ) {
					$values_clause[] = $col." = ".( "{$v}" == "" ? "''" : $this->db->quote( is_array( $v ) ? join(',', $v) : $v ) );
				}
			}
		}

		foreach ( $where AS $k => $v ) {
			// Category B: the WHERE key is an identifier, not a value.
			$wcol = SqlIdent::name( $k );
			if ( $wcol === false ) {
				syslog( LOG_ERR, 'FreemedDb::update_query| refusing invalid where column '.var_export($k, true) );
				return false;
			}
			$where_clause[] = sprintf('%s = %s', $wcol, $this->db->quote( $v ));
		}

		$query = sprintf( 'UPDATE %s SET %s WHERE %s', $table_id, join(', ', $values_clause), join(' AND ', $where_clause) );
		return $query;
	} // end public function update_query

} // end class FreemedDb

?>
