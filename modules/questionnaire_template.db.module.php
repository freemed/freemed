<?php
 // $Id$
 // desc: questionnaire template editing engine
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class QuestionnaireTemplateMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Questionnaire Template Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

 	var $record_name = "Questionnaire Templates";
	var $table_name     = "qtemplate";

	var $variables = array (
		"qname",
		"qdescrip",
		"qfname",
		"qvar",
		"qftype",
		"qftypefor",
		"qfmaxlen",
		"qftext"
	);

	function QuestionnaireTemplateMaintenance () {
		// Table definition
		$this->table_definition = array (
			'qname' => SQL_VARCHAR(100),
			'qdescrip' => SQL_VARCHAR(100),
			'qfname' => SQL_TEXT,
			'qvar' => SQL_TEXT,
			'qftype' => SQL_TEXT,
			'qftypefor' => SQL_TEXT,
			'qfmaxlen' => SQL_TEXT,
			'qftext' => SQL_TEXT,
			'id' => SQL_SERIAL
		);

		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor QuestionnaireTemplateMaintenance

	function form () {
		global $display_buffer;
		foreach($GLOBALS AS $k => $v) global ${$k};

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
       $page_title = _($record_name)." :: "._("ERROR");
       $display_buffer .= "
         "._("You must select a record to modify.")."
       ";
       template_display();
      } // end of if.. statement checking for id #

      if ($been_here != "yes") {
         // now we extract the data, since the record was given...
        $query  = "SELECT * FROM $this->table_name WHERE id='".addslashes($id)."'";
        $result = $sql->query ($query);
        $r      = $sql->fetch_array ($result);
        $qname      = $r["qname"     ];
        $qdescrip   = $r["qdescrip"  ]; 
        $qfname     = fm_split_into_array ($r["qfname"   ]);
        $qftype     = fm_split_into_array ($r["qftype"   ]);
        $qftypefor  = fm_split_into_array ($r["qftypefor"]);
        $qfmaxlen   = fm_split_into_array ($r["qfmaxlen" ]);
        $qvar       = fm_split_into_array ($r["qvar"     ]);
        $qftext     = fm_split_into_array ($r["qftext"   ]);
        break;
      } // end checking if we have been here yet...
   } // end of interior switch
   $cur_line_count = 0; // zero the current line count (displayed)
   $prev_line_total = count ($qfname); // previous # of lines
     // display the top of the repetitive table
   if ($prev_line_total == 0) {
     $insert[0]="ON";
     $first_insert = true;
   }
   $display_buffer .= "
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\">
     <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT>"._("Name of Template")."</TD>
      <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"qname\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($qname)."\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>"._("Description")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"qdescrip\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($qdescrip)."\">
     </TD>
    </TR><TR>
    </TABLE>
    <P>
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR CLASS=\"reverse\">
      <TD><FONT COLOR=\"#ffffff\">#</FONT></TD>
      <TD><FONT_B COLOR=#ffffff><CENTER><B>"._("Ins/Del")."</B></CENTER>
        </FONT></TD>
      <TD><FONT COLOR=\"#ffffff\"><B>"._("Caption")."</B></FONT></TD>
      <TD><FONT COLOR=\"#ffffff\"><B>Variable</B></FONT></TD>
      <TD><FONT COLOR=\"#ffffff\"><B>"._("Limits")."</B></FONT></TD>
      <TD><FONT COLOR=\"#ffffff\"><B>"._("Type")."</B></FONT></TD>
      <TD><FONT COLOR=\"#ffffff\"><B>Type Formatting</B></FONT></TD>
      <TD><FONT COLOR=\"#ffffff\"><B>Text of Question</B></FONT></TD>
     </TR>
    ";

   $i = 0;
   while (($i < $prev_line_total) OR ($first_insert)) {
     if (!fm_value_in_array ($del, $i)) {
      // check for problems ...
      if ( (empty($qfname[$i])) or
           (empty($qftype[$i])) ) { $num_color = "#ff0000"; }
       else                         { $num_color = "#000000"; }
      // print actual record
      $display_buffer .= "
       <TR CLASS=\"".freemed_alternate()."\">
        <TD ALIGN=RIGHT><FONT COLOR=\"$num_color\"
         >".($cur_line_count+1)."</FONT></TD>
        <TD><CENTER>
            <INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
             VALUE=\"$cur_line_count\">
            <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
             VALUE=\"$cur_line_count\"></CENTER></TD>
        <TD><INPUT TYPE=TEXT NAME=\"qfname$brackets\" SIZE=15
          MAXLENGTH=100 VALUE=\"".prepare($qfname[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"qvar$brackets\" SIZE=10
          MAXLENGTH=20 VALUE=\"".prepare($qvar[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"qfmaxlen$brackets\" SIZE=5
          MAXLENGTH=20 VALUE=\"".prepare($qfmaxlen[$i])."\"></TD>
        <TD>
       ";
       // figure out what it should be...
       $type_l = $type_m = $type_s = $type_t = $type_c = $type_i =
       $type_n = $type_1 = $type_d = $type_h = $type_p = "";
       switch ($qftype[$i]) {
         case "link":    $type_l = "SELECTED"; break;
         case "multi":   $type_m = "SELECTED"; break;
         case "number":  $type_1 = "SELECTED"; break;
         case "date":    $type_d = "SELECTED"; break;
         case "select":  $type_s = "SELECTED"; break;
         case "text":    $type_t = "SELECTED"; break;
         case "phone":   $type_p = "SELECTED"; break;
         case "heading": $type_h = "SELECTED"; break;
         case "check":   $type_c = "SELECTED"; break;
         case "time":    $type_i = "SELECTED"; break;
         default:        $type_n = "SELECTED"; break;
       } // end switch
       $display_buffer .= "
          <SELECT NAME=\"qftype$brackets\">
           <OPTION VALUE=\"\"        $type_n>none selected
           <OPTION VALUE=\"text\"    $type_t>text
           <OPTION VALUE=\"number\"  $type_1>number
           <OPTION VALUE=\"date\"    $type_d>date
           <OPTION VALUE=\"time\"    $type_i>time
           <OPTION VALUE=\"phone\"   $type_p>phone number
           <OPTION VALUE=\"select\"  $type_s>selectable
           <OPTION VALUE=\"link\"    $type_l>db link
           <OPTION VALUE=\"multi\"   $type_m>multiple choice
           <OPTION VALUE=\"check\"   $type_c>checkbox
           <OPTION VALUE=\"heading\" $type_h>heading
          </SELECT>
        </TD>
        <TD><INPUT TYPE=TEXT NAME=\"qftypefor$brackets\" SIZE=25
          MAXLENGTH=1000 VALUE=\"".prepare($qftypefor[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"qftext$brackets\" SIZE=25
          MAXLENGTH=1000 VALUE=\"".prepare($qftext[$i])."\"></TD>
       </TR>
       ";
       $cur_line_count++;
     } // end checking for delete to display
     if ((fm_value_in_array($ins, $i)) or
         (($i==($prev_line_total-1)) and ($lineinsert>0))) { // insert ??
      if ((fm_value_in_array($ins, $i)) and ($i==($prev_line_total-1))
          and ($lineinsert>0)) {  $loopfor = $lineinsert+1; }
       elseif (($i==($prev_line_total-1)) AND ($lineinsert>0))
                               {  $loopfor = $lineinsert  ; }
       else                    {  $loopfor = 1            ; }
      for ($l=0;$l<$loopfor;$l++) {
       $display_buffer .= "
        <TR CLASS=\"".freemed_alternate()."\">
         <TD ALIGN=RIGHT><FONT COLOR=\"#ff0000\"
          >".($cur_line_count+1)."</FONT></TD>
         <TD><CENTER><INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
              VALUE=\"".prepare($cur_line_count)."\">
             <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
              VALUE=\"".prepare($cur_line_count)."\"></CENTER></TD>
         <TD><INPUT TYPE=TEXT NAME=\"qfname$brackets\" SIZE=15
           MAXLENGTH=100 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"qvar$brackets\" SIZE=10
           MAXLENGTH=20 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"qfmaxlen$brackets\" SIZE=5
           MAXLENGTH=20 VALUE=\"\"></TD>
         <TD>
          <SELECT NAME=\"qftype$brackets\">
           <OPTION VALUE=\"\"       >none selected
           <OPTION VALUE=\"text\"   >text
           <OPTION VALUE=\"number\" >number
           <OPTION VALUE=\"date\"   >date
           <OPTION VALUE=\"time\"   >time
           <OPTION VALUE=\"phone\"  >phone number
           <OPTION VALUE=\"select\" >selectable
           <OPTION VALUE=\"link\"   >db link
           <OPTION VALUE=\"multi\"  >multiple choice
           <OPTION VALUE=\"check\"  >checkbox
           <OPTION VALUE=\"heading\">heading
          </SELECT>
         </TD>
         <TD><INPUT TYPE=TEXT NAME=\"qftypefor$brackets\" SIZE=25
           MAXLENGTH=1000 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"qftext$brackets\" SIZE=25
           MAXLENGTH=1000 VALUE=\"\"></TD>
        </TR>
       ";
       $cur_line_count++;
       } // end of internal for loop
     } // end of insert
     $i++;                  // increase loop
     $first_insert = false; // to be sure of _no_ endless looping
   } // end of while

   // display the bottom of the repetitive table
   $display_buffer .= "
     </TABLE>
     <P>
     <CENTER>
     <FONT SIZE=\"-1\">"._("Line Insert")." :
      <INPUT TYPE=TEXT NAME=\"lineinsert\" VALUE=\"0\"
       SIZE=2 MAXLENGTH=2></FONT>
     </CENTER>
     <BR>
     <CENTER>
     <SELECT NAME=\"action\">
      <OPTION VALUE=\"$action\">"._("Update")."
      <OPTION VALUE=\"".( ($action=="addform") ? "add" : "mod" ).
		"\">".( ($action=="addform") ? _("Add") : _("Modify") )."
      <OPTION VALUE=\"view\">Back to Menu
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\"go!\">
     </CENTER>
    ";
	} // end function QuestionnaireTemplateMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query ("SELECT * FROM $this->table_name ".
				"ORDER BY qname, qdescrip"),
			$this->page_name,
			array (
				_("Name") => "qname",
				_("Description") => "qdescrip"
			),
			array (
				"", _("NO DESCRIPTION")
			)
		);
	} // end function QuestionnaireTemplateMaintenance->view()

} // end class QuestionnaireTemplateMaintenance

register_module ("QuestionnaireTemplateMaintenance");

?>
