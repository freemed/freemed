<?php
 // $Id$
 // $Author$

/*
	This function is meant to be a drop-in replacement for those
	people who don't have bcmath functionality in their PHP
	distribution. It probably has problems, and the best solution
	is to build yourself a version with bcmath support. Barring
	this, you can use this hack.

		- Jeff
*/

function bcadd ($left, $right, $scale) {
	// first add the two numbers
	$sum = (double)($left + $right);

	// check for a dot in the number
	if (strpos($sum, ".") === false) {
		// not found, integer
		$int_part = $sum;
		$real_part = 0;
	} else {
		// if not, we split
		list ($int_part, $real_part) = explode (".", $sum);
	} // end checking for a dot

	// handle scale of 0
	if ($scale == 0) return $int_part;

	// handle real parts that need more precision
	if ($scale > strlen($real_part)) {
		for ($i=0;$i<=($scale - strlen($real_part));$i++)
			$real_part .= "0";
	} // end checking for more precision needed

	// return built string
	return $int_part . "." . substr($real_part, 0, $scale);
} // end function bcadd

?>
