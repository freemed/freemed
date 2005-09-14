<?php
	// $Id$
	// code: jeff b <jeff@ourexchange.net>
	// lic : LGPL

// File: SQL API

if (!defined("__SQL_TOOLS_PHP__")) {

define ('__SQL_TOOLS_PHP__', true);

// database engines' shorthand
define ('SQL_MSQL',		1);
define ('SQL_MSSQL',		2);
define ('SQL_MYSQL', 		3);
define ('SQL_ODBC',  		4);
define ('SQL_ORACLE',		5);
define ('SQL_POSTGRES',		6);
define ('SQL_SQLITE',		7);

function sql_squash ($val, $squash_char=":") {
  if (!is_array ($val)) {
    // handle as scalar
    return $val;
  } else {
    // handle as array ... must recursively loop through *all* elements
    // to make sure that all array parts are handled
    $internal = $val; // pass to here
    reset ($internal); // move pointer to beginning
    while (list ($k, $v) = each ($internal)) {
      $internal["$k"] = sql_squash($v, $squash_char);
    } // end looping
    reset ($internal); // reset pointer (again)
    return implode ($internal, $squash_char);
  } // end checking how to handle

  // if all else fails... false
  return false;
} // end function sql_squash

function sql_expand ($val, $squash_char=":") {
  // return self if not valid
  if (empty ($val)) return $val;

  // perform preliminary explode
  $internal = explode ($squash_char, $val);

  // check to see if we should just return the presented value
  if ((!is_array($internal)) or (count($internal)==1)) return $val;

  // recursive looping through all elements (as in sql_squash)
  reset ($internal); // move pointer to beginning
  while (list ($k, $v) = each ($internal)) {
    $internal["$k"] = sql_expand($v, $squash_char);
  } // end looping

  // return result array
  reset ($internal);
  return $internal;
} // end function sql_expand

//-----------------------------------------------------------------
// functions that can be used in building SQL queries

// Function: SQL_FISCAL_YEAR
//
//	Creates a fiscal year SQL WHERE clause
//
// Parameters:
//
//	$var - Name of the SQL DATE column to be searched
//
//	$value - (optional) Fiscal year to search for. If not given,
//	defaults to the current year
//
// Returns:
//
//	SQL WHERE clause.
//
function SQL_FISCAL_YEAR ($var, $value="") {
	if ($value=="") { $year = date("Y"); }
	 else { $year = $value; }
	return "
	(
		(
			(YEAR($var) = '".addslashes($year-1)."')
			AND
			(MONTH($var) > 6)
		)
		OR
		(
			(YEAR($var) = '".addslashes($year)."')
			AND
			(MONTH($var) < 7)

		)
	)
	";
} // end function SQL_FISCAL_YEAR

} // end checking if defined

?>
