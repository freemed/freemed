/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dojo.require("dojo.date");

/* Supplementary Date Functions
 *******************************/

function test_date_setDayOfYear () {
	//dojo.date.setDayOfYear(new Date(2006,2,1), 23);
}

function test_date_getDayOfYear () {
	//dojo.date.getDayOfYear(new Date(2006,0,1));
}




function test_date_setWeekOfYear () {
	//dojo.date.setWeekOfYear(new Date(2006,2,1), 34);
	//dojo.date.setWeekOfYear(new Date(2006,2,1), 34, 1);
}

function test_date_getWeekOfYear () {
	//dojo.date.getWeekOfYear(new Date(2006,1,1));
	//dojo.date.getWeekOfYear(new Date(2006,1,1), 1);
}




function test_date_setIsoWeekOfYear () {
	//dojo.date.setIsoWeekOfYear(new Date(2006,2,1), 34);
	//dojo.date.setIsoWeekOfYear(new Date(2006,2,1), 34, 1);
}

function test_date_getIsoWeekOfYear () {
	//dojo.date.getIsoWeekOfYear(new Date(2006,1,1));
	//dojo.date.getIsoWeekOfYear(new Date(2006,1,1), 1);
}




/* ISO 8601 Functions
 *********************/

function test_date_fromIso8601() {
	var iso  = "20060210T000000Z"
	var date = dojo.date.fromIso8601(iso);
	jum.assertEquals("fromIso8601_test1",2006,date.getFullYear());
	jum.assertEquals("fromIso8601_test2",1,date.getMonth());
	jum.assertEquals("fromIso8601_test3",10,date.getDate());
}

function test_date_fromIso8601Date () {
	
	//YYYY-MM-DD
	var date = dojo.date.fromIso8601Date("2005-02-22");
	jum.assertEquals("fromIso8601Date_test7", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test8", 1, date.getMonth());
	jum.assertEquals("fromIso8601Date_test9", 22, date.getDate());
	
	//YYYYMMDD
	var date = dojo.date.fromIso8601Date("20050222");
	jum.assertEquals("fromIso8601Date_test10", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test11", 1, date.getMonth());
	jum.assertEquals("fromIso8601Date_test12", 22, date.getDate());
	
	//YYYY-MM
	var date = dojo.date.fromIso8601Date("2005-08");
	jum.assertEquals("fromIso8601Date_test13", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test14", 7, date.getMonth());
	
	//YYYYMM
	var date = dojo.date.fromIso8601Date("200502");
	jum.assertEquals("fromIso8601Date_test15", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test16", 1, date.getMonth());
	
	//YYYY
	var date = dojo.date.fromIso8601Date("2005");
	jum.assertEquals("fromIso8601Date_test17", 2005, date.getFullYear());
	
	//1997-W01 or 1997W01
	var date = dojo.date.fromIso8601Date("2005-W22");
	jum.assertEquals("fromIso8601Date_test18", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test19", 5, date.getMonth());
	jum.assertEquals("fromIso8601Date_test20", 6, date.getDate());

	var date = dojo.date.fromIso8601Date("2005W22");
	jum.assertEquals("fromIso8601Date_test21", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test22", 5, date.getMonth());
	jum.assertEquals("fromIso8601Date_test23", 6, date.getDate());
	
	//1997-W01-2 or 1997W012
	var date = dojo.date.fromIso8601Date("2005-W22-4");
	jum.assertEquals("fromIso8601Date_test24", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test25", 5, date.getMonth());
	jum.assertEquals("fromIso8601Date_test26", 9, date.getDate());

	var date = dojo.date.fromIso8601Date("2005W224");
	jum.assertEquals("fromIso8601Date_test27", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test28", 5, date.getMonth());
	jum.assertEquals("fromIso8601Date_test29", 9, date.getDate());

		
	//1995-035 or 1995035
	var date = dojo.date.fromIso8601Date("2005-146");
	jum.assertEquals("fromIso8601Date_test30", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test31", 4, date.getMonth());
	jum.assertEquals("fromIso8601Date_test32", 26, date.getDate());
	
	var date = dojo.date.fromIso8601Date("2005146");
	jum.assertEquals("fromIso8601Date_test33", 2005, date.getFullYear());
	jum.assertEquals("fromIso8601Date_test34", 4, date.getMonth());
	jum.assertEquals("fromIso8601Date_test35", 26, date.getDate());
	
}

function test_date_fromIso8601Time () {
	
	//23:59:59
	var date = dojo.date.fromIso8601Time("18:46:39");
	jum.assertEquals("fromIso8601Time_test36", 18, date.getHours());
	jum.assertEquals("fromIso8601Time_test37", 46, date.getMinutes());
	jum.assertEquals("fromIso8601Time_test38", 39, date.getSeconds());
	
	//235959
	var date = dojo.date.fromIso8601Time("184639");
	jum.assertEquals("fromIso8601Time_test39", 18, date.getHours());
	jum.assertEquals("fromIso8601Time_test40", 46, date.getMinutes());
	jum.assertEquals("fromIso8601Time_test41", 39, date.getSeconds());
	
	//23:59, 2359, or 23
	var date = dojo.date.fromIso8601Time("18:46");
	jum.assertEquals("fromIso8601Time_test42", 18, date.getHours());
	jum.assertEquals("fromIso8601Time_test43", 46, date.getMinutes());

	var date = dojo.date.fromIso8601Time("1846");
	jum.assertEquals("fromIso8601Time_test44", 18, date.getHours());
	jum.assertEquals("fromIso8601Time_test45", 46, date.getMinutes());

	var date = dojo.date.fromIso8601Time("18");
	jum.assertEquals("fromIso8601Time_test46", 18, date.getHours());

	//23:59:59.9942 or 235959.9942
	var date = dojo.date.fromIso8601Time("18:46:39.9942");
	jum.assertEquals("fromIso8601Time_test47", 18, date.getHours());
	jum.assertEquals("fromIso8601Time_test48", 46, date.getMinutes());
	jum.assertEquals("fromIso8601Time_test49", 39, date.getSeconds());
	jum.assertEquals("fromIso8601Time_test50", 994, date.getMilliseconds());

	var date = dojo.date.fromIso8601Time("184639.9942");
	jum.assertEquals("fromIso8601Time_test51", 18, date.getHours());
	jum.assertEquals("fromIso8601Time_test52", 46, date.getMinutes());
	jum.assertEquals("fromIso8601Time_test53", 39, date.getSeconds());
	jum.assertEquals("fromIso8601Time_test54", 994, date.getMilliseconds());
	
	//1995-02-04 24:00 = 1995-02-05 00:00
	
	//TODO: timezone tests
	
	//+hh:mm, +hhmm, or +hh
	
	//-hh:mm, -hhmm, or -hh
	
}


/* Informational Functions
 **************************/

function test_date_getDaysInMonth () {
	// months other than February
	jum.assertEquals("getDaysInMonth_test1", 31, dojo.date.getDaysInMonth(new Date(2006,0,1)));
	jum.assertEquals("getDaysInMonth_test2", 31, dojo.date.getDaysInMonth(new Date(2006,2,1)));
	jum.assertEquals("getDaysInMonth_test3", 30, dojo.date.getDaysInMonth(new Date(2006,3,1)));
	jum.assertEquals("getDaysInMonth_test4", 31, dojo.date.getDaysInMonth(new Date(2006,4,1)));
	jum.assertEquals("getDaysInMonth_test5", 30, dojo.date.getDaysInMonth(new Date(2006,5,1)));
	jum.assertEquals("getDaysInMonth_test6", 31, dojo.date.getDaysInMonth(new Date(2006,6,1)));
	jum.assertEquals("getDaysInMonth_test7", 31, dojo.date.getDaysInMonth(new Date(2006,7,1)));
	jum.assertEquals("getDaysInMonth_test8", 30, dojo.date.getDaysInMonth(new Date(2006,8,1)));
	jum.assertEquals("getDaysInMonth_test9", 31, dojo.date.getDaysInMonth(new Date(2006,9,1)));
	jum.assertEquals("getDaysInMonth_test10", 30, dojo.date.getDaysInMonth(new Date(2006,10,1)));
	jum.assertEquals("getDaysInMonth_test11", 31, dojo.date.getDaysInMonth(new Date(2006,11,1)));

	// Februarys
	jum.assertEquals("getDaysInMonth_test12", 28, dojo.date.getDaysInMonth(new Date(2006,1,1)));
	jum.assertEquals("getDaysInMonth_test13", 29, dojo.date.getDaysInMonth(new Date(2004,1,1)));
	jum.assertEquals("getDaysInMonth_test14", 29, dojo.date.getDaysInMonth(new Date(2000,1,1)));
	jum.assertEquals("getDaysInMonth_test15", 28, dojo.date.getDaysInMonth(new Date(1900,1,1)));
	jum.assertEquals("getDaysInMonth_test16", 28, dojo.date.getDaysInMonth(new Date(1800,1,1)));
	jum.assertEquals("getDaysInMonth_test17", 28, dojo.date.getDaysInMonth(new Date(1700,1,1)));
	jum.assertEquals("getDaysInMonth_test18", 29, dojo.date.getDaysInMonth(new Date(1600,1,1)));
}

function test_date_isLeapYear () {
	jum.assertFalse("isLeapYear_test1", dojo.date.isLeapYear(new Date(2006,0,1)));
	jum.assertTrue("isLeapYear_test2", dojo.date.isLeapYear(new Date(2004,0,1)));
	jum.assertTrue("isLeapYear_test3", dojo.date.isLeapYear(new Date(2000,0,1)));
	jum.assertFalse("isLeapYear_test4", dojo.date.isLeapYear(new Date(1900,0,1)));
	jum.assertFalse("isLeapYear_test5", dojo.date.isLeapYear(new Date(1800,0,1)));
	jum.assertFalse("isLeapYear_test6", dojo.date.isLeapYear(new Date(1700,0,1)));
	jum.assertTrue("isLeapYear_test7", dojo.date.isLeapYear(new Date(1600,0,1)));
}



function test_date_getOrdinal () {
	jum.assertEquals("getOrdinal_test1", "st", dojo.date.getOrdinal(new Date(2006,0,1)));
	jum.assertEquals("getOrdinal_test2", "nd", dojo.date.getOrdinal(new Date(2006,0,2)));
	jum.assertEquals("getOrdinal_test3", "rd", dojo.date.getOrdinal(new Date(2006,0,3)));
	jum.assertEquals("getOrdinal_test4", "th", dojo.date.getOrdinal(new Date(2006,0,4)));
	jum.assertEquals("getOrdinal_test5", "th", dojo.date.getOrdinal(new Date(2006,0,11)));
	jum.assertEquals("getOrdinal_test6", "th", dojo.date.getOrdinal(new Date(2006,0,12)));
	jum.assertEquals("getOrdinal_test7", "th", dojo.date.getOrdinal(new Date(2006,0,13)));
	jum.assertEquals("getOrdinal_test8", "th", dojo.date.getOrdinal(new Date(2006,0,14)));
	jum.assertEquals("getOrdinal_test9", "st", dojo.date.getOrdinal(new Date(2006,0,21)));
	jum.assertEquals("getOrdinal_test10", "nd", dojo.date.getOrdinal(new Date(2006,0,22)));
	jum.assertEquals("getOrdinal_test11", "rd", dojo.date.getOrdinal(new Date(2006,0,23)));
	jum.assertEquals("getOrdinal_test12", "th", dojo.date.getOrdinal(new Date(2006,0,24)));
}



/* Date Formatter Functions
 ***************************/

function test_date_format () {

}

function test_date_sql() {
	jum.assertEquals("date.fromSql test", new Date("5/1/2006").valueOf(), dojo.date.fromSql("2006-05-01 00:00:00").valueOf());
}

/* Date compare and add Functions
 *********************************/

function test_date_compare(){
	var d1=new Date();
	d1.setHours(0);
	var d2=new Date();
	d2.setFullYear(2005);
	d2.setHours(12);
	jum.assertEquals("compare_test1", 0, dojo.date.compare(d1, d1));
	jum.assertEquals("compare_test2", 1, dojo.date.compare(d1, d2, dojo.date.compareTypes.DATE));
	jum.assertEquals("compare_test3", -1, dojo.date.compare(d2, d1, dojo.date.compareTypes.DATE));
	jum.assertEquals("compare_test4", -1, dojo.date.compare(d1, d2, dojo.date.compareTypes.TIME));
	jum.assertEquals("compare_test5", 1, dojo.date.compare(d1, d2, dojo.date.compareTypes.DATE|dojo.date.compareTypes.TIME));
}

function test_date_add(){
	var d=new Date(2005,10,1,12,0,0,0);
	jum.assertEquals("add_test1", new Date(2006,10,1,12,0,0,0), dojo.date.add(d, dojo.date.dateParts.YEAR));
	jum.assertEquals("add_test2", new Date(2005,9,1,12,0,0,0), dojo.date.add(d, dojo.date.dateParts.MONTH, -1));
	jum.assertEquals("add_test3", new Date(2005,10,5,12,0,0,0), dojo.date.add(d, dojo.date.dateParts.DAY, 4));
	jum.assertEquals("add_test4", new Date(2005,10,1,10,0,0,0), dojo.date.add(d, dojo.date.dateParts.HOUR, -2));
	jum.assertEquals("add_test5", new Date(2005,10,1,12,10,0,0), dojo.date.add(d, dojo.date.dateParts.MINUTE, 10));
	jum.assertEquals("add_test6", new Date(2005,10,1,11,59,25,0), dojo.date.add(d, dojo.date.dateParts.SECOND, -35));
}
