<?php
 // $Id$
 // desc: patient record template editing engine
 // lic : GPL, v2

if (!defined("__PATIENT_RECORD_TEMPLATE_MODULE_PHP__")) {

define (__PATIENT_RECORD_TEMPLATE_MODULE_PHP__, true);

class patientRecordTemplateMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "Patient Record Template Maintenance";
	var $MODULE_VERSION = "0.1";

	var $record_name    = "Patient Record Templates";
	var $table_name     = "patrectemplate";

	var $variables = array (
		"prtname",
		"prtdescrip",
		"prtfname",
		"prtvar",
		"prtftype",
		"prtftypefor",
		"prtfmaxlen",
		"prtfcom"
	);

	function patientRecordTemplateMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor patientRecordTemplateMaintenance

	function add () {
		global $prtfname, $prtvar, $prtftype, $prtftypefor,
			$prtfmaxlen, $prtfcom;
		$prtfname      = fm_join_from_array($prtfname);
		$prtvar        = fm_join_from_array($prtvar);
		$prtftype      = fm_join_from_array($prtftype);
		$prtftypefor   = fm_join_from_array($prtftypefor);
		$prtfmaxlen    = fm_join_from_array($prtfmaxlen);
		$prtfcom       = fm_join_from_array($prtfcom);
		$this->_add();
	} // end function patientRecordTemplateMaintenance->add()

	function mod () {
		global $prtfname, $prtvar, $prtftype, $prtftypefor,
			$prtfmaxlen, $prtfcom;
		$prtfname      = fm_join_from_array($prtfname);
		$prtvar        = fm_join_from_array($prtvar);
		$prtftype      = fm_join_from_array($prtftype);
		$prtftypefor   = fm_join_from_array($prtftypefor);
		$prtfmaxlen    = fm_join_from_array($prtfmaxlen);
		$prtfcom       = fm_join_from_array($prtfcom);
		$this->_mod();
	} // end function patientRecordTemplateMaintenance->mod()

	function form () {
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

   switch ($action) {
     case "addform":
      $go = "add";
      $this_action = _("Add");
      break;
     case "modform":
      $go = "mod";
      $this_action = _("Modify");

      if ($been_here != "yes") {
         // now we extract the data, since the record was given...
        $query  = "SELECT * FROM $this->table_name ".
			"WHERE id='".addslashes($id)."'";
        $result = $sql->query ($query);
        $r      = $sql->fetch_array ($result);
        $prtname      = $r["prtname"     ];
        $prtdescrip   = $r["prtdescrip"  ]; 
        $prtfname     = fm_split_into_array ($r["prtfname"   ]);
        $prtftype     = fm_split_into_array ($r["prtftype"   ]);
        $prtftypefor  = fm_split_into_array ($r["prtftypefor"]);
        $prtfmaxlen   = fm_split_into_array ($r["prtfmaxlen" ]);
        $prtvar       = fm_split_into_array ($r["prtvar"     ]);
        $prtfcom      = fm_split_into_array ($r["prtfcom"    ]);
        break;
      } // end checking if we have been here yet...
   } // end of interior switch
   $cur_line_count = 0; // zero the current line count (displayed)
   $prev_line_total = count ($prtfname); // previous # of lines
     // display the top of the repetitive table
   if ($prev_line_total == 0) {
     $insert[0]="ON";
     $first_insert = true;
   }
   echo "
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
     <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Name of Template<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"prtname\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($prtname)."\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Description")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"prtdescrip\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($prtdescrip)."\">
     </TD>
    </TR><TR>
    </TABLE>
    <P>
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR BGCOLOR=\"#000000\">
      <TD><$STDFONT_B COLOR=\"#ffffff\">#<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><CENTER><B>"._("Ins/Del")."</B></CENTER>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Text Name")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Variable")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Limits")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Type")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Type Formatting")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=\"#ffffff\"><B>"._("Comment")."</B><$STDFONT_E></TD>
     </TR>
    ";

   $i = 0;
   while (($i < $prev_line_total) OR ($first_insert)) {
     if (!fm_value_in_array ($del, $i)) {
      // check for problems ...
      if ( (empty($prtfname[$i])) or
           (empty($prtftype[$i])) ) { $num_color = "#ff0000"; }
       else                         { $num_color = "#000000"; }
      // print actual record
      echo "
       <TR BGCOLOR=\"".
        ($_alternate = freemed_bar_alternate_color ($_alternate)).
       "\">
        <TD ALIGN=RIGHT><$STDFONT_B COLOR=\"$num_color\"
         >".($cur_line_count+1)."<$STDFONT_E></TD>
        <TD><CENTER>
            <INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
             VALUE=\"$cur_line_count\">
            <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
             VALUE=\"$cur_line_count\"></CENTER></TD>
        <TD><INPUT TYPE=TEXT NAME=\"prtfname$brackets\" SIZE=15
          MAXLENGTH=100 VALUE=\"".prepare($prtfname[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"prtvar$brackets\" SIZE=10
          MAXLENGTH=20 VALUE=\"".prepare($prtvar[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"prtfmaxlen$brackets\" SIZE=5
          MAXLENGTH=20 VALUE=\"".prepare($prtfmaxlen[$i])."\"></TD>
        <TD>
       ";
       // figure out what it should be...
       $type_l = $type_m = $type_s = $type_t = $type_c = $type_i =
       $type_n = $type_1 = $type_d = $type_h = $type_p = "";
       switch ($prtftype[$i]) {
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
       echo "
          <SELECT NAME=\"prtftype$brackets\">
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
        <TD><INPUT TYPE=TEXT NAME=\"prtftypefor$brackets\" SIZE=25
          MAXLENGTH=1000 VALUE=\"".prepare($prtftypefor[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"prtfcom$brackets\" SIZE=25
          MAXLENGTH=100 VALUE=\"".prepare($prtfcom[$i])."\"></TD>
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
       echo "
        <TR BGCOLOR=\"".
         ($_alternate = freemed_bar_alternate_color ($_alternate)).
	"\">
         <TD ALIGN=RIGHT><$STDFONT_B COLOR=\"#ff0000\"
          >".($cur_line_count+1)."<$STDFONT_E></TD>
         <TD><CENTER><INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
              VALUE=\"$cur_line_count\">
             <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
              VALUE=\"$cur_line_count\"></CENTER></TD>
         <TD><INPUT TYPE=TEXT NAME=\"prtfname$brackets\" SIZE=15
           MAXLENGTH=100 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"prtvar$brackets\" SIZE=10
           MAXLENGTH=20 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"prtfmaxlen$brackets\" SIZE=5
           MAXLENGTH=20 VALUE=\"\"></TD>
         <TD>
          <SELECT NAME=\"prtftype$brackets\">
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
         <TD><INPUT TYPE=TEXT NAME=\"prtftypefor$brackets\" SIZE=25
           MAXLENGTH=1000 VALUE=\"\"></TD>
         <TD><INPUT TYPE=TEXT NAME=\"prtfcom$brackets\" SIZE=25
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
      <OPTION VALUE=\"$action\">"._("Update")."
      <OPTION VALUE=\"$go\">$this_action
      <OPTION VALUE=\"view\">"._("Back to Menu")."
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\""._("go")."\">
     </CENTER>
    ";
	} // end function patientRecordTemplateModule->form()

	function view () {
		global $sql;
		echo freemed_display_itemlist (
			$sql->query ("SELECT * FROM $this->table_name ".
				"ORDER BY prtname, prtdescrip"),
			$this->page_name,
			array (
				_("Name")        => "prtname",
				_("Description") => "prtdescrip"
			),
			array ( "", _("NO DESCRIPTION"))
		);
	} // end function patientRecordTemplateMaintenance->view()

} // end class patientRecordTemplateMaintenance

register_module ("patientRecordTemplateMaintenance");

} // end if not defined

?>
