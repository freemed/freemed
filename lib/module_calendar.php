<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

if (!defined("__MODULE_CALENDAR_PHP__")) {

define ('__MODULE_CALENDAR_PHP__', true);

// class freemedCalendarModule
class freemedCalendarModule extends freemedModule {

	// override variables
	var $CATEGORY_NAME = "Calendar";
	var $CATEGORY_VERSION = "0.1";

	// vars to be passed from child modules
	var $order_field;
	var $form_vars;
	var $table_name;

    // calendar specific data
	var $ProcessType = "VIEW";
	var $SECONDS_PER_DAY = 86400; // 60*60*24
	var $months_hash = array( "01" => "January",
								"02" => "February",
								"03" => "March",
								"04" => "April",
								"05" => "May",
								"06" => "June",
								"07" => "July",
								"08" => "August",
								"09" => "September",
								"10" => "October",
								"11" => "November",
								"12" => "December" );

	var $month_name;
	var $month_number;
	var $year;
	var $month_data;
	var $nextmonth;
	var $nextyear;
	var $prevmonth;
	var $prevyear;

	

	// contructor method
	function freemedCalendarModule () {
		// call parent constructor
		$this->freemedModule();
	} // end function freemedCalendarModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module, $patient, $LoginCookie;
		if (!isset($module)) 
		{
			trigger_error("No Module Defined", E_ERROR);
		}
		if ($patient < 1) 
		{
			trigger_error( "No Patient Defined", E_ERROR);
		}
		// check access to patient
		if (!freemed_check_access_for_patient($LoginCookie, $patient)) 
		{
			trigger_error("User not Authorized for this function", E_USER_ERROR);
		}
		return true;

	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $action, $patient, $LoginCookie;

		if (!isset($this->this_patient))
			$this->this_patient = new Patient ($patient);
		if (!isset($this->this_user))
			$this->this_user    = new User ($LoginCookie);

		// display universal patient box
        if ($patient)
			echo freemed_patient_box($this->this_patient)."<P>\n";

		switch ($action) {
			case "add":
				$this->add();
				break;

			case "addform":
				$this->addform();
				break;

			case "del":
			case "delete":
				$this->del();
				break;

			case "mod":
			case "modify":
				$this->mod();
				break;

			case "modform":
				$this->modform();
				break;

			case "display";
				$this->display();
				break;

			case "view":
			default:
				$this->view();
				break;
		} // end switch action
	} // end function main

	// ********************** MODULE SPECIFIC ACTIONS *********************

	// function add
	// - addition routine
	function add () { $this->_add(); }
	function _add () {
		foreach ($GLOBALS as $k => $v) global $$k;
	
		echo "
			<P><CENTER>
			<$STDFONT_B>"._("Adding")." ...
		";

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) { echo "<B>"._("done").".</B>\n"; }
		 else		 { echo "<B>"._("ERROR")."</B>\n"; }

		echo "
			<$STDFONT_E></CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient\"
				><$STDFONT_B>"._("back")."<$STDFONT_E></A>
			</CENTER>
		";

	} // end function _add

	// function del
	// - delete function
	function del () { $this->_del(); }
	function _del () {
		global $STDFONT_B, $STDFONT_E, $id, $sql;
		echo "<P ALIGN=CENTER>".
			"<$STDFONT_B>"._("Deleting")." . . . \n";
		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) { echo _("done"); }
		 else		 { echo "<FONT COLOR=\"#ff0000\">"._("ERROR")."</FONT>"; }
		echo "<$STDFONT_E></P>\n";
	} // end function _del

	// function mod
	// - modification function
	function mod () { $this->_mod(); }
	function _mod () {
		foreach ($GLOBALS as $k => $v) global $$k;
	
		echo "
			<P><CENTER>
			<$STDFONT_B>"._("Modifying")." ...
		";

		$result = $sql->query (
			$sql->update_query (
				$this->table_name,
				$this->variables,
				array (
					"id"	=>		$id
				)
			)
		);

		if ($result) { echo "<B>"._("done").".</B>\n"; }
		 else		 { echo "<B>"._("ERROR")."</B>\n"; }

		echo "
			<$STDFONT_E></CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient\"
				><$STDFONT_B>"._("back")."<$STDFONT_E></A>
			</CENTER>
		";

	} // end function _mod

	// function add/modform
	// - wrappers for form
	function addform () { $this->form(); }
	function modform () { $this->form(); }

	// function display
	// by default, a wrapper for view
	function display () { $this->view(); }

	// function form
	// - add/mod form stub
	function form () {
		global $action, $id, $sql;

		if (is_array($this->form_vars)) {
			reset ($this->form_vars);
			while (list ($k, $v) = each ($this->form_vars)) global $$v;
		} // end if is array

		switch ($action) {
			case "addform":
				break;

			case "modform":
				$result = $sql->query ("SELECT * FROM ".$this->table_name.
					" WHERE ( id = '".prepare($id)."' )");
				$r = $sql->fetch_array ($result);
				extract ($r);
				break;
		} // end of switch action
		
	} // end function form

	// function view
	// - view stub
	function view () {
		global $sql;
		$result = $sql->query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		echo freemed_display_itemlist (
			$result,
			"module_loader.php",
			$this->form_vars,
			array ("", _("NO DESCRIPTION")),
			"",
			"t_page"
		);
	} // end function view

	// calendar specific functions

	// trim leading and trailing whitespace
	function cl_trim ($s) {
	  $ret = eregi_replace ("^[[:space:]]+|[[:space:]]+$","",$s);
	  return $ret;
	}

	function setProcessType($type)
	{
		$this->ProcessType = $type;
	}


	/*
	 * function to generate the javascript needed for the popup
	 */
	function js_popup($w=500,$h=300)
	{
	  global $module, $_auth, $action;

	  $buffer = "";
	  $buffer .= "<script language=\"javascript\">\n";
	  $buffer .= "<!--\n\n";
	  $buffer .= "function openWin(id){\n\n";
	  $buffer .= "  URL = \"$this->page_name?id=\" + id + \"&_auth=".prepare($_auth)."&action=display&module=".prepare($module)."\"\n";
	  $buffer .= "  NAME = \"Event\" + id\n";
	  $buffer .= "  w = window.open(";
	  $buffer .= "URL,";
	  $buffer .= "NAME,'toolbar=yes,";
	  $buffer .= "location=no,directories=no,status=no,menubar=no,scrollbars=no,";
	  $buffer .= "resizable=no,width=$w,height=$h');\n\n";
	  $buffer .= "}\n\n";
	  $buffer .= "// -->\n";
	  $buffer .= "</script>\n\n";
	  return $buffer;
	}

	function month( $thismonth = "" , $thisyear = "" )
    {
		global $sql;

		if( !$thismonth ){
		  $thismonth = date("m");
		}

		if( !$thisyear ){
		  $thisyear = date("Y");
		}

		$this->month_name = $this->months_hash[$thismonth];
		$this->month_number = $thismonth;
		$this->year = $thisyear;

		$this->nextmonth = sprintf("%02d",$this->month_number+1);
		$this->prevmonth = sprintf("%02d",$this->month_number-1);
		$this->nextyear = $this->prevyear = $thisyear;

		if( $this->month_number == "12" ){
		  $this->nextmonth = "01";
		  $this->nextyear = $thisyear + 1;
		}
		if( $this->month_number == "01" ){
		  $this->prevmonth = "12";
		  $this->prevyear = $thisyear - 1;
		}

		/*
		 * month data
		 */
		$query = "SELECT id,DAYOFMONTH(caldateof) as day, calprenote FROM scheduler WHERE
			   MONTH(caldateof)='" . $this->month_number . "' AND
			   YEAR(caldateof)='" . $this->year ."' ORDER BY day";
		$result = $sql->query($query);


		if( !$result ){
		  trigger_error("SQL Error reading scheduler table",E_ERROR); 
		}

		while ($tmp = $sql->fetch_array($result)) 
   	    {
		  $calnote = $tmp["calprenote"];
		  if( empty($calnote ))
				$calnote = "No Comment";
			$this->month_data[$tmp["day"]]["id"][] = $tmp["id"];
			$this->month_data[$tmp["day"]]["event_title"][] = $calnote;
		}
	  } // end function month
	

	// obvious function
	function print_month_name() {
	   return $this->month_name;
	}

	// obvious function
	function print_year() {
	   return $this->year;
	}

	// obvious function
	function print_datestring() {
	  return $this->month_name . ", " . $this->year;
	}

	///////////////////////////////////////////
	// returns the number of days for a given
	// month and year. Months go 1-12 and
	// years are numeric such as "1999"
	///////////////////////////////////////////

	function days_in_month( $month, $year ){
	// older versions of php don't support "t" in the date() function,
	// so I have to do this really kludgy thing.
	if( $month == "01" ){
	  $days_in_month = 31;
	}
	// have to handle leap year
	if( $month == "02" && $year % 4 == 0 && ($year % 100 != 0 || $year % 1000 == 0) ){
	  $days_in_month = 29;
	}
	else if( $month == "02" ){
	  $days_in_month = 28;
	}

	if( $month == "03" ){
	  $days_in_month = 31;
	}
	if( $month == "04" ){
	  $days_in_month = 30;
	}
	if( $month == "05" ){
	  $days_in_month = 31;
	}
	if( $month == "06" ){
	  $days_in_month = 30;
	}
	if( $month == "07" ){
	  $days_in_month = 31;
	}
	if( $month == "08" ){
	  $days_in_month = 31;
	}
	if( $month == "09" ){
	  $days_in_month = 30;
	}
	if( $month == "10" ){
	  $days_in_month = 31;
	}
	if( $month == "11" ){
	  $days_in_month = 30;
	}
	if( $month == "12" ){
	  $days_in_month = 31;
	}
	return $days_in_month;
	}


	/* this one's for internal use */
	function _get_date_by_counter($i,$month,$year){

	$first_day = date("w" , mktime(0,0,0,$month,1,$year));
	//$days_in_month = date("t" , mktime(0,0,0,$month,1,$year));
	// older versions of php don't support "t" in the date() function,
	// so I have to do this really kludgy thing.
	if( $month == "01" ){
	  $days_in_month = 31;
	}
	// have to handle leap year
	if( $month == "02" && $year % 4 == 0 && ($year % 100 != 0 || $year % 1000 == 0) ){
	  $days_in_month = 29;
	}
	else if( $month == "02" ){
	  $days_in_month = 28;
	}

	if( $month == "03" ){
	  $days_in_month = 31;
	}
	if( $month == "04" ){
	  $days_in_month = 30;
	}
	if( $month == "05" ){
	  $days_in_month = 31;
	}
	if( $month == "06" ){
	  $days_in_month = 30;
	}
	if( $month == "07" ){
	  $days_in_month = 31;
	}
	if( $month == "08" ){
	  $days_in_month = 31;
	}
	if( $month == "09" ){
	  $days_in_month = 30;
	}
	if( $month == "10" ){
	  $days_in_month = 31;
	}
	if( $month == "11" ){
	  $days_in_month = 30;
	}
	if( $month == "12" ){
	  $days_in_month = 31;
	}

	if( $i < $first_day ){
	  return "&nbsp;";
	}
	if( $i >= $days_in_month+$first_day ){
	  return "&nbsp;";
	}

	return ($i+1-$first_day);
	}


	/*
	* this is the big cahoona function,
	* draws a calendar.
	*/
	function draw($draw_array = "") {
		global $module, $action, $_auth;

	/*
	* this is a long section which simply gets
	* the parameters which are used in the drawing
	* of the calendar. It's not pretty, but it's
	* simple to understand and modify so I'm
	* running with it.
	*/


	echo $this->js_popup();
	if( !$draw_array["textcolor"] ){
	  $textcolor = "#000000";
	}
	else{
	  $textcolor = $draw_array["textcolor"];
	}

	if( !$draw_array["bgcolor"] ){
	  $bgcolor = "#FFFFFF";
	}
	else{
	  $bgcolor = $draw_array["bgcolor"];
	}

	if( !$draw_array["font_face"] ){
	  $font_face = "Verdana, Arial, Helvetica";
	}
	else{
	  $font_face = $draw_array["font_face"];
	}

	if( !$draw_array["font_size"] ){
	  $font_size = "-1";
	}
	else{
	  $font_size = $draw_array["font_size"];
	}

	if( !$draw_array["table_width"] ){
	  $table_width = "100";
	}
	else{
	  $table_width = $draw_array["table_width"];
	}

	if( !$draw_array["table_height"] ){
	  $table_height = "100";
	}
	else{
	  $table_height = $draw_array["table_height"];
	}

	if( !$draw_array["cellpadding"] ){
	  $cellpadding = "0";
	}
	else{
	  $cellpadding = $draw_array["cellpadding"];
	}

	if( !$draw_array["cellspacing"] ){
	  $cellspacing = "0";
	}
	else{
	  $cellspacing = $draw_array["cellspacing"];
	}

	if( !$draw_array["table_border"] ){
	  $table_border = "0";
	}
	else{
	  $table_border = $draw_array["table_border"];
	}

	if( !$draw_array["top_row_align"] ){
	  $table_top_row_align = "left";
	}
	else{
	  $table_top_row_align = $draw_array["top_row_align"];
	}

	if( !$draw_array["top_row_valign"] ){
	  $table_top_row_valign = "top";
	}
	else{
	  $table_top_row_valign = $draw_array["top_row_valign"];
	}

	if( !$draw_array["row_align"] ){
	  $table_row_align = "left";
	}
	else{
	  $table_row_align = $draw_array["row_align"];
	}

	if( !$draw_array["row_valign"] ){
	  $table_row_valign = "top";
	}
	else{
	  $table_row_valign = $draw_array["row_valign"];
	}

	if( !$draw_array["top_row_cell_height"] ){
	  $table_top_row_cell_height = "";
	}
	else{
	  $table_top_row_cell_height = $draw_array["top_row_cell_height"];
	}

	/*
	 * end of "getting drawing parameters section.
	 */
	/***************************************************/

	/* adjust if width is specified in pixels */
	if( eregi("px",$table_width) ){
	  $table_width = eregi_replace("px" , "" , $table_width);
	}
	else if( $table_width ){
	  $table_width = $table_width . "%";
	}

	/*
	* for some reason, it seems that we have to handle height
	* a little bit differently. It should always be in pixels
	*/

	$table_height = eregi_replace("[^[:digit:]]" , "" , $table_height);
	if( !ereg("^[[:digit:]]+$" , $table_height ) ){
	  $table_height = "250";
	}


	/*
	 * we need to know how many rows are going to be in this table
	 */


	if( $this->days_in_month($this->month_number,$this->year) == 28 && date("w" , mktime(0,0,0,2,1,$this->year)) == 0 ){
	  $num_of_rows = 4;
	}
	else if( $this->days_in_month($this->month_number,$this->year) == 30 && date("w" , mktime(0,0,0,$this->month_number,1,$this->year)) > 5 ){
	  $num_of_rows = 6;
	}
	else if( $this->days_in_month($this->month_number,$this->year) == 31 && date("w" , mktime(0,0,0,$this->month_number,1,$this->year)) > 4 ){
	  $num_of_rows = 6;
	}
	else{
	  $num_of_rows = 5;
	}

	/* start printout of main calendar table */
	echo "<!-- begin lucid calendar printout, http://www.luciddesigns.com -->\n";
	echo "<table cellspacing=\"$cellspacing\" cellpadding=\"$cellpadding\" width=\"$table_width\" height=\"$table_height\" border=\"$table_border\">\n";

	/*
	 * we need to figure out the cell height and width for each of these.
	 */

	$dates_cell_height = ceil(($table_height - $table_top_row_cell_height) / $num_of_rows);

	/* deal with widths given in percentages or in pixels */
	if( ereg( "%" , $table_width ) ){
	  $dates_cell_width = sprintf( "%.3f" , eregi_replace("%","",$table_width)/7 ) . "%";
	}
	else{
	  $dates_cell_width = ceil( $table_width / 7 );
	}

	/*
	 * this prints out the top row, which has the names of the
	 * days of the week. I consider it a distinct sort of thing.
	 */
	echo "<tr>\n";
	echo "  <td bgcolor=\"$bgcolor\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Sunday</b></font></td>\n";
	echo "  <td bgcolor=\"$bgcolor\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Monday</b></font></td>\n";
	echo "  <td bgcolor=\"$bgcolor\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Tuesday</b></font></td>\n";
	echo "  <td bgcolor=\"$bgcolor\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Wednesday</b></font></td>\n";
	echo "  <td bgcolor=\"$bgcolor\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Thursday</b></font></td>\n";
	echo "  <td bgcolor=\"$bgcolor\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Friday</b></font></td>\n";
	echo "  <td bgcolor=\"$bgcolor\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Saturday</b></font></td>\n";
	echo "</tr>\n";


	/*
	 * now print out all the cells for the days of the
	 * month. This is the "heart" of this function.
	 */

	for( $i=0 ; $i < $num_of_rows*7 ; $i++ ){
	  /* start first row */
	  if( $i==0 ){
		echo "<tr>\n";
	  }
	  /* break into a new row at the appropriate places */ 
	  if( $i%7 == 0 && $i != 0){
		echo "</tr>\n";
		echo "<tr>\n";
	  }

	  /*
	   * get the current day
	   */
	  $theday = $this->_get_date_by_counter($i,$this->month_number, $this->year);



	  /*
	   * if there's an event for this day, get it.
	   * otherwise, set to "" string
	   */

	  $theevent = "";
	  if( $this->month_data[$theday]["event_title"][0] )
	  {
		for( $j=0 ; $j <  count($this->month_data[$theday]["event_title"]) ; $j++ )
		{
		  if ($this->ProcessType == "ADMIN")
		  {
			$theevent .= $this->month_data[$theday]["event_title"][$j]."<BR>";

		  }
		  else
		  {
		  	//$c = $this->month_data[$theday]["id"][$j];
		  	//echo "id is $c<BR>";
		  	$theevent .= "<font face=\"$font_face\" size=\"$font_size\"><a href=\"javascript:openWin(";
		  	$theevent .= $this->month_data[$theday]["id"][$j]; 
		  	$theevent .= ")\">";
     	  	$theevent .= $this->month_data[$theday]["event_title"][$j]; 
		  	$theevent .= "</a></font><br><br>\n";
		  }
		}
	  }


	  if ($this->ProcessType == "ADMIN")
	  {
  		  $theday_link = "<A HREF=\"$this->page_name?$_auth&action=display&module=".prepare($module).
		  	  "&month=$this->month_number&day=$theday&year=$this->year\"".
			  ">$theday</A>";
		  $theday = $theday_link;
			
	  }

	  echo "<td bgcolor=\"$bgcolor\" align=\"$table_row_align\" valign=\"$table_row_valign\" height=\"$dates_cell_height\"";
	  echo "width=\"$dates_cell_width\">";
	  echo "<font face=\"$font_face\" size=\"$font_size\"";
	  echo ">$theday<br>\n";
      echo "$theevent</font>";
	  echo "</td>\n";
	  /* be sure to clear out $theevent */
	  $theevent = "";
	  /* close the last row */
	  if( $i == $num_of_rows*7-1 ){
		echo "</tr>\n";
	  }
	}
	echo "</table>\n";
	echo "<!-- end lucid calendar printout -->\n"; /* end of calendar printout */
	} /* end draw function */




} // end class freemedCalendarModule

} // end if not defined

?>
