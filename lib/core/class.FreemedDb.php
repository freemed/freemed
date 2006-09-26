<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
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

LoadObjectDependency('net.php.pear.MDB2');

// Declare macros
define ( 'SQL__BIT_SHIFT',		8);
define ( 'SQL__BLOB',			1);
function SQL__CHAR ($size)              { return 2 + ($size<<SQL__BIT_SHIFT); }
define ( 'SQL__DATE',			3);
function SQL__DOUBLE ($size)            { return 4 + ($size<<SQL__BIT_SHIFT); }
function SQL__ENUM ($elements = "") { if ($elements != "") return array(5, $elements); else return 5; } // end SQL__ENUM
function SQL__INT ($size)		{ return 6 + ($size<<SQL__BIT_SHIFT); }
function SQL__INT_UNSIGNED ($size)	{ return 7 + ($size<<SQL__BIT_SHIFT); }
define ( 'SQL__TEXT',			8);
function SQL__VARCHAR ($size)           { return 9 + ($size<<SQL__BIT_SHIFT); }
define ( 'SQL__REAL',			10);
function SQL__TIMESTAMP ($size)		{ return 11 + ($size<<SQL__BIT_SHIFT); }
define ( 'SQL__SERIAL',			12);
function SQL__AUTO_INCREMENT ($var)	{ return ($var) + (1<<(SQL__BIT_SHIFT*3)); }
function SQL__NOT_NULL ($var)   { return ($var) + (2<<(SQL__BIT_SHIFT*3)); }
define ( 'SQL__THRESHHOLD', (1<<(SQL__BIT_SHIFT*3))-1 );
define ( 'SQL__NOW', 			"~~~~~NOW~~~~~" );

// Class: org.freemedsoftware.core.FreemedDb
//
class FreemedDb extends MDB2 {

	private $db;
	private $data;

	public function __construct (  ) {
		PEAR::setErrorHandling ( PEAR_ERROR_RETURN );
		$uri = "mysqli://". DB_USER .":". DB_PASSWORD ."@". DB_HOST ."/". DB_NAME;
		$this->db =& MDB2::factory ( $uri );
		if ( PEAR::isError ( $this->db ) ) {
			trigger_error ( $this->db->getMessage(), E_USER_ERROR );
		}

		$this->db->setFetchMode( MDB2_FETCHMODE_ASSOC );
		$this->db->loadModule( 'Extended' );
		$this->db->loadModule( 'Manager' );
		$this->db->loadModule( 'Reverse' );
	} // end constructor

	// Method: __call
	//
	//	Magic method to push calls which come into this object into the $db
	//	object.
	//
	function __call ( $method, $param ) {
		if ( method_exists ( $this, $method ) ) {
			return call_user_func_array ( array ( $this, $method ), $param );
		} elseif ( method_exists ( $this->db, $method ) ) {
			return call_user_func_array ( array ( $this->db, $method ), $param );
		} else {
			trigger_error ( "Could not load method $method", E_USER_ERROR );
		}
	}

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
		if ( !is_array ( $values ) ) { return false; }
		unset ($this->data);
		foreach ( $values AS $k => $v ) {
			$this->data[$k] = $v;
		}	
	} // end public function load_data

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
		$query = "SELECT DISTINCT `".$this->db->escape($field)."` FROM `".$this->db->escape($table)."` ".
			( $where ? " WHERE ${where} " : " " ).
			"ORDER BY `".$this->db->escape($field)."`";
		$result = $this->db->queryCol( $query );
		if ( PEAR::isError( $result ) ) { return array ( ); }
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
	// Returns:
	//
	//	INSERT SQL query
	//
	public function insert_query ( $table, $values ) {
		$in_loop = false;
		foreach ($values AS $k => $v) {
			if ( (($k+0) > 0) or empty( $k ) ) {
				$k = $v; $v = $this->data[$k];
			}

			// Handle timestamp
			if ($v == SQL__NOW) {
				$values_hash .= ( $in_loop ? ", " : " " )."NOW()";
			} else {
				$values_hash .= ( $in_loop ? ", " : " " ).$this->db->quote( is_array($v) ? join(',', $v) : $v );
			}
			$cols_hash .= ( $in_loop ? ", " : " " )."`".$this->db->escape( $k )."`";
			$in_loop = true;
		}

		$query = "INSERT INTO `".$this->db->escape($table)."` ( ${cols_hash} ) VALUES ( ${values_hash} )";
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
	// Returns:
	//
	//	UPDATE SQL query
	//
	public function update_query ( $table, $values, $where ) {
		foreach ( $values AS $k => $v ) {
			if ( (($k+0) > 0) or empty( $k ) ) {
				$k = $v; $v = $this->data[$k];
			}

			// Handle timestamp
			if ($v == SQL__NOW) {
				$values_clause[] = "`".$this->db->escape($k)."` = NOW()";
			} else {
				$values_clause[] = "`".$this->db->escape($k)."` = ".$this->db->quote( is_array( $v ) ? join(',', $v) : $v );
			}
		}

		foreach ( $where AS $k => $v ) {
			$where_clause[] = "`".$this->db->escape( $k )."` = ".$this->db->quote( $v );
		}

		$query = "UPDATE `".$this->db->escape($table)."` SET ".join(', ', $values_clause)." WHERE ".join(' AND ', $where_clause);
		return $query;
	} // end public function update_query

} // end class FreemedDb

?>
