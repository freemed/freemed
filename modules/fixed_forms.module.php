<?php
 // $Id$
 // desc: fixed type forms editing engine
 // lic : GPL, v2

if (!defined("__FIXED_FORMS_MODULE_PHP__")) {

define (__FIXED_FORMS_MODULE_PHP__, true);

class fixedFormsMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME	= "Fixed Forms Maintenance";
	var $MODULE_VERSION	= "0.1";

	var $record_name	= "Fixed Form";
	var $table_name		= "fixedform";

	var $variables		= array (
		"ffname",
		"ffdescrip",
		"fftype",
		"ffpagelength",
		"fflinelength",
		"ffloopnum",
		"ffloopoffset",
		"ffcheckchar",
		"ffrow",
		"ffcol",
		"fflength",
		"ffdata",
		"ffformat",
		"ffcomment"
	);

	function fixedFormsMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor fixedFormsMaintenance

	function form () {
		foreach ($GLOBALS as $k => $v) global $$k;
   switch ($action) {
     case "addform":
      $go = "add";
      $this_action = _("Add");
      break;
     case "modform":
      $go = "mod";
      $this_action = _("Modify");
       // check to see if an id was submitted
      if ($id<1) {
       echo "
         "._("You must select a record to modify.")."
       ";
       freemed_display_box_bottom ();
       freemed_close_db ();
       freemed_display_html_bottom ();
       DIE("");
      } // end of if.. statement checking for id #

      if ($been_here != "yes") {
         // now we extract the data, since the record was given...
        $query  = "SELECT * FROM $db_name WHERE id='$id'";
        $result = $sql->query ($query);
        $r      = $sql->fetch_array ($result);
		extract ($r);
        $row          = fm_split_into_array ($ffrow);
        $col          = fm_split_into_array ($ffcol);
        $len          = fm_split_into_array ($fflength);
        $data         = fm_split_into_array ($ffdata);
        $format       = fm_split_into_array ($ffformat);
        $comment      = fm_split_into_array ($ffcomment);
        break;
      } // end checking if we have been here yet...
   } // end of interior switch

   // set the fftype properly
   for ($i=0;$i<=20;$i++) ${"_type_".$i} = "";
   ${"_type_".$fftype} = "SELECTED";

   $cur_line_count = 0; // zero the current line count (displayed)
   $prev_line_total = count ($row); // previous # of lines
     // display the top of the repetitive table
   if ($prev_line_total == 0) {
     $insert[0]="ON";
     $first_insert = true;
   }
   echo "
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
     <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Name of Form")." : <$STDFONT_E></TD>
      <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffname\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($ffname)."\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Page Length")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffpagelength\" SIZE=5 MAXLENGTH=5
       VALUE=\"".prepare($ffpagelength)."\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Description")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffdescrip\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($ffdescrip)."\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Line Length")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"fflinelength\" SIZE=5 MAXLENGTH=5
       VALUE=\"".prepare($fflinelength)."\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Loop Repetitions")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffloopnum\" SIZE=5 MAXLENGTH=5
       VALUE=\"".prepare($ffloopnum)."\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Check Char")." : <BR>
                     "._("(<I>example: \"X\"</I>)")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffcheckchar\" SIZE=2 MAXLENGTH=1
       VALUE=\"".prepare($ffcheckchar)."\"> 
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Loop Line Offset")." : <BR>
                "._("(<I>\"1\" skips to the next line</I>)")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffloopoffset\" SIZE=5 MAXLENGTH=5
       VALUE=\"".prepare($ffloopoffset)."\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Type")." : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"fftype\">
       <OPTION VALUE=\"0\" ".
         ( ($fftype==0) ? "SELECTED" : "" ).">"._("Generic")."
       <OPTION VALUE=\"1\" ".
         ( ($fftype==1) ? "SELECTED" : "" ).">"._("Insurance Claim")."
       <OPTION VALUE=\"2\" ".
         ( ($fftype==2) ? "SELECTED" : "" ).">"._("Patient Bill")."
      </SELECT>
     </TD>
    </TR>
    </TABLE>
    <P>
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR BGCOLOR=\"#000000\">
      <TD><$STDFONT_B COLOR=\"#ffffff\">#<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><CENTER><B>"._("Ins/Del")."</B></CENTER>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"
       ><B>"._("Row/Line")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Column")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Length")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Data")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Format")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Comment")."</B><$STDFONT_E></TD>
     </TR>
    ";

   $i = 0;
   while (($i < $prev_line_total) OR ($first_insert)) {
     if (!fm_value_in_array ($del, $i)) {
      // check for problems ...
      if ( (strlen($row[$i])<1) or (strlen($col[$i])<1) or
           (strlen($len[$i])<1) ) { $num_color = "#ff0000"; }
       else                       { $num_color = "#000000"; }
      // print actual record
      $_alternate = freemed_bar_alternate_color ($_alternate);
      echo "
       <TR BGCOLOR=\"".
        ($_alternate = freemed_bar_alternate_color ($_alternate))."\">
        <TD><$STDFONT_B COLOR=\"$num_color\">".($cur_line_count+1)."<$STDFONT_E></TD>
        <TD><CENTER>
            <INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
             VALUE=\"$cur_line_count\">
            <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
             VALUE=\"$cur_line_count\"></CENTER></TD>
        <TD><INPUT TYPE=TEXT NAME=\"row$brackets\" SIZE=5
          MAXLENGTH=3 VALUE=\"".prepare($row[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"col$brackets\" SIZE=5
          MAXLENGTH=3 VALUE=\"".prepare($col[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"len$brackets\" SIZE=5
          MAXLENGTH=3 VALUE=\"".prepare($len[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"data$brackets\" SIZE=15
          MAXLENGTH=100 VALUE=\"".prepare($data[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"format$brackets\" SIZE=5
          MAXLENGTH=100 VALUE=\"".prepare($format[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"comment$brackets\" SIZE=15
          MAXLENGTH=100 VALUE=\"".prepare($comment[$i])."\"></TD>
       </TR>
       ";
       $cur_line_count++;
     } // end checking for delete to display
     if ((fm_value_in_array($ins, $i)) or
         (($i==($prev_line_total-1)) and ($lineinsert>0))) { // if there is an insert
      if ((fm_value_in_array($ins, $i)) and ($i==($prev_line_total-1))
          and ($lineinsert>0)) {  $loopfor = $lineinsert+1; }
       if (($i==($prev_line_total-1)) AND ($lineinsert>0))
                               {  $loopfor = $lineinsert  ; }
       else                    {  $loopfor = 1            ; }
      for ($l=0;$l<$loopfor;$l++) {
       $_alternate = freemed_bar_alternate_color ($_alternate);
       echo "
        <TR BGCOLOR=\"".
         ($_alternate = freemed_bar_alternate_color ($_alternate))."\">
         <TD><$STDFONT_B COLOR=\"#ff0000\">".($cur_line_count+1)."<$STDFONT_E></TD>
         <TD><CENTER><INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
              VALUE=\"$cur_line_count\">
             <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
              VALUE=\"$cur_line_count\"></CENTER></TD>
         <TD><INPUT TYPE=TEXT NAME=\"row$brackets\" SIZE=5
           MAXLENGTH=3 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"col$brackets\" SIZE=5
           MAXLENGTH=3 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"len$brackets\" SIZE=5
           MAXLENGTH=3 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"data$brackets\" SIZE=15
           MAXLENGTH=100 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"format$brackets\" SIZE=5
           MAXLENGTH=100 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"comment$brackets\" SIZE=15
           MAXLENGTH=100 VALUE=\"\"></TD>
        </TR>
       ";
       $cur_line_count++;
      } // end of internal for loop
     } // end of insert
     $i++;                  // increase loop
     $first_insert = false; // to be sure of _no_ endless looping
   } // end of while

   // display the bottom of the repetitive table
   echo "
     </TABLE>
     <P>
     <CENTER>
     <$STDFONT_B SIZE=-1>Line Insert :
      <INPUT TYPE=TEXT NAME=\"lineinsert\" VALUE=\"0\"
       SIZE=2 MAXLENGTH=2><$STDFONT_E>
     </CENTER>
     <BR>
     <CENTER>
     <SELECT NAME=\"action\">
      <OPTION VALUE=\"$action\">Update
      <OPTION VALUE=\"$go\">$this_action
      <OPTION VALUE=\"view\">Back to Menu
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\"go!\">
     </CENTER>
    ";
	} // end function fixedFormsMaintenance->form

	function add () {
		foreach ($GLOBALS as $k => $v) global $$k;
		$GLOBALS["ffrow"    ] = fm_join_from_array ($row    );
		$GLOBALS["ffcol"    ] = fm_join_from_array ($col    );
		$GLOBALS["fflength" ] = fm_join_from_array ($len    );
		$GLOBALS["ffdata"   ] = fm_join_from_array ($data   );
		$GLOBALS["ffformat" ] = fm_join_from_array ($format );
		$GLOBALS["ffcomment"] = fm_join_from_array ($comment);
		$this->_add();
	} // end function fixedFormsMaintenance->add

	function mod () {
		foreach ($GLOBALS as $k => $v) global $$k;
		$ffrow     = fm_join_from_array ($row    );
		$ffcol     = fm_join_from_array ($col    );
		$fflength  = fm_join_from_array ($len    );
		$ffdata    = fm_join_from_array ($data   );
		$ffformat  = fm_join_from_array ($format );
		$ffcomment = fm_join_from_array ($comment);
		$this->_mod();
	} // end function fixedFormsMaintenance->mod

	function view () {
		global $sql;
		echo freemed_display_itemlist (
			$sql->query ("SELECT * FROM $this->table_name ".
				"ORDER BY ffname, ffdescrip"),
			$this->page_name,
			array (
				_("Name")			=>	"ffname",
				_("Description")	=>	"ffdescrip"
			),
			array ( "", _("NO DESCRIPTION") )
		);  
	} // end function fixedFormsMaintenance->view     

} // end class fixedFormsMaintenance

register_module ("fixedFormsMaintenance");

} // end if not defined

?>
