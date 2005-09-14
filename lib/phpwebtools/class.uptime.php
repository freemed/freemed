<?php
 // $Id$
 // desc: returns uptime information
 // code: Jonas Koch Bentzen (http://jonaskochbentzen.dk)
 // lic : LGPL

/*

class.uptime.php - a PHP class for fetching uptime information
Version 0.01
Released under the terms of the GNU General Public License

Example usage:
	$uptime = new uptime ();
	echo "Uptime for this server:";
	echo "Years: $uptime->years";
	echo "Months: $uptime->months";
	echo "Days: $uptime->days";
	echo "Hours: $uptime->hours";
	echo "Minutes: $uptime->minutes";
	echo "Seconds: $uptime->seconds";

*/

class uptime {
	var $years;
	var $months;
	var $days;
	var $hours;
	var $minutes;
	var $seconds;

	function uptime ($uptimeFile = "/proc/uptime") {
		$uptime = @file($uptimeFile) or
			die("<p><b>class.uptime.php: Can't open $uptimeFile for reading.</b></p>");
		$uptime = $uptime[0];
		$uptime = substr($uptime, 0, strpos($uptime, " "));

		$secYears = 31536000;
		$secMonths = 2592000;
		$secDays = 86400;
		$secHours = 3600;
		$secMinutes = 60;

		$this->years = floor($uptime / $secYears);
		$uptime = $uptime % $secYears;

		$this->months = floor($uptime / $secMonths);
		$uptime = $uptime % $secMonths;

		$this->days = floor($uptime / $secDays);
		$uptime = $uptime % $secDays;

		$this->hours = floor($uptime / $secHours);
		$uptime = $uptime % $secHours;

		$this->minutes = floor($uptime / $secMinutes);
		$uptime = $uptime % $secMinutes;

		$this->seconds = $uptime;

	} // end constructor uptime

} // end class uptime

?>
