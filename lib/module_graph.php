<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__MODULE_GRAPH_PHP__")) {

define ('__MODULE_GRAPH_PHP__', true);

// Include proper functions for graphing
include_once ("class.phplot.php");

// class freemedGraphModule extends freeMedmodule
class freemedGraphModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Graph";
	var $CATEGORY_VERSION = "0.1";

	// vars to be passed from child modules

	// contructor method
	function freemedGraphModule () {
		// call parent constructor
		$this->freemedModule();
	} // end function freemedGraphModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module))
		{
			trigger_error("No Module Defined", E_ERROR);
		}
		return true;
	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $action;

		switch ($action) {
			case "display":
				$this->display();
				break;
			case "view":
			default:
				$this->view();
				break;
		} // end switch action
	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************
	function header() {
		global $SESSION, $graphmode;
		if ($graphmode) {
			// don't display the box top
			freemed_open_db();
			return;
		}
		freemedModule::header();
	} // end function header

	function footer() {
		global $graphmode, $display_buffer;

		// dont display the bottom
		if ($graphmode) return;
		$display_buffer .= "<P><CENTER><A HREF=\"reports.php\">"._("Reports Menu")."</A></CENTER>\n";
		template_display();
	} // end function footer

	function GetGraphOptions($title) {
		global $action, $module, $start_dt, $end_dt;

		$buffer = "
		<CENTER>
        <B>".$title."</B>
        <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
         VALIGN=MIDDLE ALIGN=CENTER>

        <FORM ACTION=\"".$this->page_name."\" METHOD=POST>
        	<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"display\">
        	<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"$module\">
		<INPUT TYPE=HIDDEN NAME=\"graphmode\" VALUE=\"1\">


		<TR>
		<TD>"._("Start Date").": </TD>
		<TD>".fm_date_entry("start_dt")."</TD>
		<TD>"._("End Date").": </TD>
		<TD>".fm_date_entry("end_dt")."</TD>
		</TR>
		<TR>
		<TD COLSPAN=\"5\" ALIGN=\"CENTER\"><INPUT TYPE=\"SUBMIT\" NAME=\"Submit\" VALUE=\"Submit\"></TD>
		</FORM>
		</TABLE> 
		</CENTER>
        ";

		return $buffer;

	} // end function GetGraphOptions

} // end class freemedGraphModule

} // end define




