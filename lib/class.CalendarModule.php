<?php
 // $Id$
 // desc: module prototype
 // lic : GPL, v2

LoadObjectDependency('FreeMED.BaseModule');

class CalendarModule extends BaseModule {

	// override variables
	var $CATEGORY_NAME = "Calendar";
	var $CATEGORY_VERSION = "0.2";

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
	function CalendarModule () {
		// call parent constructor
		$this->BaseModule();
	} // end function CalendarModule

	// override check_vars method
	function check_vars ($nullvar = "") {
		global $module, $patient;
		if (!isset($module)) 
		{
			trigger_error("No Module Defined", E_ERROR);
		}
		if ($patient < 1) 
		{
			trigger_error( "No Patient Defined", E_ERROR);
		}
		// check access to patient
		if (!freemed::check_access_for_patient($patient)) 
		{
			trigger_error("User not Authorized for this function", E_USER_ERROR);
		}
		return true;

	} // end function check_vars

	// function main
	// - generic main function
	function main ($nullvar = "") {
		global $display_buffer;
		global $action, $patient;

		if (!isset($this->this_patient))
			$this->this_patient = CreateObject('FreeMED.Patient', $patient);
		if (!isset($this->this_user))
			$this->this_user    = CreateObject('FreeMED.User');

		// display universal patient box
        if ($patient)
			$display_buffer .= freemed::patient_box($this->this_patient)."<p/>\n";

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

			// admin functions

			case "book";
				$this->book();
				break;

			case "manage";
				$this->manage();
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
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;
	
		$display_buffer .= "
			<P><CENTER>
			"._("Adding")." ...
		";

		$result = $sql->query (
			$sql->insert_query (
				$this->table_name,
				$this->variables
			)
		);

		if ($result) { $display_buffer .= "<B>"._("done").".</B>\n"; }
		 else		 { $display_buffer .= "<B>"._("ERROR")."</B>\n"; }

		$display_buffer .= "
			</CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?module=$module&patient=$patient\"
				>"._("back")."</A>
			</CENTER>
		";

	} // end function _add

	// function del
	// - delete function
	function del () { $this->_del(); }
	function _del () {
		global $display_buffer;
		global $id, $sql;
		$display_buffer .= "<P ALIGN=CENTER>".
			_("Deleting")." . . . \n";
		$query = "DELETE FROM $this->table_name ".
			"WHERE id = '".prepare($id)."'";
		$result = $sql->query ($query);
		if ($result) { $display_buffer .= _("done"); }
		 else		 { $display_buffer .= "<FONT COLOR=\"#ff0000\">"._("ERROR")."</FONT>"; }
		$display_buffer .= "</P>\n";
	} // end function _del

	// function mod
	// - modification function
	function mod () { $this->_mod(); }
	function _mod () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;
	
		$display_buffer .= "
			<P><CENTER>
			"._("Modifying")." ...
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

		if ($result) { $display_buffer .= "<B>"._("done").".</B>\n"; }
		 else		 { $display_buffer .= "<B>"._("ERROR")."</B>\n"; }

		$display_buffer .= "
			</CENTER>
			<P>
			<CENTER>
				<A HREF=\"$this->page_name?module=$module&patient=$patient\"
				>"._("back")."</A>
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
		global $display_buffer;
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
		global $display_buffer;
		global $sql;
		$result = $sql->query ("SELECT ".$this->order_fields." FROM ".
			$this->table_name." ORDER BY ".$this->order_fields);
		$display_buffer .= freemed_display_itemlist (
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
		global $display_buffer;
	  $ret = eregi_replace ("^[[:space:]]+|[[:space:]]+$","",$s);
	  return $ret;
	}

	function setProcessType($type) {
		global $display_buffer;
		$this->ProcessType = $type;
	}


	/*
	 * function to generate the javascript needed for the popup
	 */
	function js_popup($w=500,$h=300) {
		global $display_buffer;
	  global $module, $action;

	  $buffer = "";
	  $buffer .= "<script language=\"javascript\">\n";
	  $buffer .= "<!--\n\n";
	  $buffer .= "function openWin(id){\n\n";
	  $buffer .= "  URL = \"$this->page_name?id=\" + id + \"&action=display&module=".prepare($module)."\"\n";
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

	function month( $thismonth = "" , $thisyear = "" ) {
		global $display_buffer;
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
		$query = "SELECT a.calprenote,a.calhour,a.calminute,a.id,DAYOFMONTH(a.caldateof) AS day, b.roomname,".
				"c.ptlname,c.ptfname ".
				"FROM scheduler AS a, room AS b, patient AS c ".
				"WHERE a.calroom = b.id AND a.calpatient = c.id AND ".
			   "MONTH(a.caldateof)='" . $this->month_number . "' AND ".
			   "YEAR(a.caldateof)='" . $this->year ."' ORDER BY day,a.calhour,a.calminute";

		//$display_buffer .= "$query<BR>";
		$result = $sql->query($query);


		if( !$result ){
		  trigger_error("SQL Error reading scheduler table",E_ERROR); 
		}

		while ($tmp = $sql->fetch_array($result)) 
   	    {
		  $calroom = $tmp["roomname"];
		  $calnote = $tmp["calprenote"];
		  $ptlname = $tmp["ptlname"];
		  $ptfname = $tmp["ptfname"];
		  if( empty($calnote ))
				$calnote = "No Comment";
			$this->month_data[$tmp["day"]]["id"][] = $tmp["id"];
			$this->month_data[$tmp["day"]]["event_title"][] = prepare($calnote);
			$wrkh = $tmp[calhour];
			$wrkm = $tmp[calminute];
			$this->month_data[$tmp["day"]]["time"][] = fc_get_time_string($wrkh,$wrkm);
			$this->month_data[$tmp["day"]]["room"][] = prepare($calroom);
			$this->month_data[$tmp["day"]]["patient"][] = prepare($ptlname).", ".prepare($ptfname);
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
		global $display_buffer;
		global $module, $action;

	/*
	* this is a long section which simply gets
	* the parameters which are used in the drawing
	* of the calendar. It's not pretty, but it's
	* simple to understand and modify so I'm
	* running with it.
	*/


	$display_buffer .= $this->js_popup();

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
	$display_buffer .= "<!-- begin lucid calendar printout, http://www.luciddesigns.com -->\n";
	$display_buffer .= "<table cellspacing=\"2\" cellpadding=\"2\" width=\"100%\" border=\"0\" class=\"calendar\">\n";

	/*
	 * we need to figure out the cell height and width for each of these.
	 */

	$dates_cell_height = ceil(($table_height - $table_top_row_cell_height) / $num_of_rows);

	/* deal with widths given in percentages or in pixels */
	if( ereg( "%" , $table_width ) ){
	  $dates_cell_width = sprintf( "%.3f" , eregi_replace("%","",$table_width)/7 ) . "%";
	}
	else{
	  $dates_cell_width = ceil( 100 / 7 )."%";
	}

	/*
	 * this prints out the top row, which has the names of the
	 * days of the week. I consider it a distinct sort of thing.
	 */
	$display_buffer .= "<tr>\n";
	$display_buffer .= "  <td class=\"cell\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Sunday</b></font></td>\n";
	$display_buffer .= "  <td class=\"cell\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Monday</b></font></td>\n";
	$display_buffer .= "  <td class=\"cell\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Tuesday</b></font></td>\n";
	$display_buffer .= "  <td class=\"cell\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Wednesday</b></font></td>\n";
	$display_buffer .= "  <td class=\"cell\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Thursday</b></font></td>\n";
	$display_buffer .= "  <td class=\"cell\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Friday</b></font></td>\n";
	$display_buffer .= "  <td class=\"cell\" align=\"$table_top_row_align\" valign=\"$table_top_row_valign\" height=\"$table_top_row_cell_height\" width=\"$dates_cell_width\"><font face=\"$font_face\" size=\"$font_size\"><b>Saturday</b></font></td>\n";
	$display_buffer .= "</tr>\n";


	/*
	 * now print out all the cells for the days of the
	 * month. This is the "heart" of this function.
	 */

	for( $i=0 ; $i < $num_of_rows*7 ; $i++ ){
	  /* start first row */
	  if( $i==0 ){
		$display_buffer .= "<tr>\n";
	  }
	  /* break into a new row at the appropriate places */ 
	  if( $i%7 == 0 && $i != 0){
		$display_buffer .= "</tr>\n";
		$display_buffer .= "<tr>\n";
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
	  $theevent_info = "";
	  if( $this->month_data[$theday]["event_title"][0] )
	  {
		for( $j=0 ; $j <  count($this->month_data[$theday]["event_title"]) ; $j++ )
		{
		  if ($this->ProcessType == "ADMIN")
		  {
			$theevent .= $this->month_data[$theday]["time"][$j]." - ";
			$theevent .= $this->month_data[$theday]["room"][$j]."<BR>";
			$theevent .= "&nbsp;&nbsp;".$this->month_data[$theday]["patient"][$j]."<BR>";
			$theevent .= "&nbsp;&nbsp;".$this->month_data[$theday]["event_title"][$j]."<BR>";
		  }
		  else
		  {
			$theevent .= "<div class=\"thinbox_noscroll\">\n";
			$theevent .= "<a href=\"javascript:openWin(";
		  	$theevent .= $this->month_data[$theday]["id"][$j]; 
		  	$theevent .= ")\"><small>";
			$theevent .= $this->month_data[$theday]["time"][$j]." - ";
	     	  	$theevent .= $this->month_data[$theday]["room"][$j];
		  	$theevent .= "</a></small><br/>\n"; 
     		  	$theevent .= "&nbsp;&nbsp;<abbr title=\"".$this->month_data[$theday]["event_title"][$j]."\">".
				"<small>".$this->month_data[$theday]["patient"][$j]."</small></abbr></div>\n"; 
		  }
		}
	  }


	  if ($this->ProcessType == "ADMIN")
	  {
  		  $theday_link = "<A HREF=\"$this->page_name?action=display&module=".prepare($module).
		  	  "&month=$this->month_number&day=$theday&year=$this->year\"".
			  ">$theday</A>";
		  $theday = $theday_link;
			
	  }

	  $display_buffer .= "<td class=\"cell_alt\" align=\"$table_row_align\" valign=\"TOP\" height=\"$dates_cell_height\"";
	  $display_buffer .= "width=\"$dates_cell_width\">";
	  $display_buffer .= "<small>$theday<br>\n";
	$display_buffer .= "$theevent";
	  $display_buffer .= "</small>";
	  $display_buffer .= "</td>\n";
	  /* be sure to clear out $theevent */
	  $theevent = "";
	  $theevent_info = "";
	  /* close the last row */
	  if( $i == $num_of_rows*7-1 ){
		$display_buffer .= "</tr>\n";
	  }
	}
	$display_buffer .= "</table>\n";
	$display_buffer .= "<!-- end lucid calendar printout -->\n"; /* end of calendar printout */
	} /* end draw function */

} // end class CalendarModule

?>
