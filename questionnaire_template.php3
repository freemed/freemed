<?php
 // file: questionnaire_template.php3	
 // desc: questionnaire template editing engine
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

 $page_name   = "questionnaire_template.php3";
 $record_name = "Questionnaire Templates";
 $db_name     = "qtemplate";

 include ("global.var.inc");
 include ("freemed-functions.inc");

 freemed_open_db ($LoginCookie);
 freemed_display_html_top ();
 freemed_display_banner ();

 switch ($action) {
  // trying to combine add and modify forms for simplicity
  case "addform": case "modform":
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
       freemed_display_box_top (_($record_name)." :: "._("ERROR"));
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
        $result = fdb_query ($query);
        $r      = fdb_fetch_array ($result);
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
   freemed_display_box_top ("$this_action "._($record_name));
   $cur_line_count = 0; // zero the current line count (displayed)
   $prev_line_total = count ($qfname); // previous # of lines
     // display the top of the repetitive table
   if ($prev_line_total == 0) {
     $insert[0]="ON";
     $first_insert = true;
   }
   echo "
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"".prepare($id)."\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Name of Template<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"qname\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($qname)."\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>"._("Description")."<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"qdescrip\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($qdescrip)."\">
     </TD>
    </TR><TR>
    </TABLE>
    <P>
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR BGCOLOR=#000000>
      <TD><$STDFONT_B COLOR=#ffffff>#<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><CENTER><B>"._("Ins/Del")."</B></CENTER>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>"._("Caption")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Variable</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>"._("Limits")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>"._("Type")."</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Type Formatting</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Text of Question</B><$STDFONT_E></TD>
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
      echo "
       <TR BGCOLOR=\"".
        ($_alternate = freemed_bar_alternate_color ($_alternate))."\">
        <TD ALIGN=RIGHT><$STDFONT_B COLOR=\"$num_color\"
         >".($cur_line_count+1)."<$STDFONT_E></TD>
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
       echo "
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
       $_alternate = freemed_bar_alternate_color ($_alternate);
       echo "
        <TR BGCOLOR=\"$_alternate\">
         <TD ALIGN=RIGHT><$STDFONT_B COLOR=\"#ff0000\"
          >".($cur_line_count+1)."<$STDFONT_E></TD>
         <TD><CENTER><INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
              VALUE=\"$cur_line_count\">
             <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
              VALUE=\"$cur_line_count\"></CENTER></TD>
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
      <OPTION VALUE=\"view\">Back to Menu
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\"go!\">
     </CENTER>
    ";
    freemed_display_box_bottom ();
   break;

  case "add":
   freemed_display_box_top ("$Adding $record_name");
   echo "
     <P><CENTER>
     <$STDFONT_B>"._("Adding")." ...
   ";
   $query = "INSERT INTO $db_name VALUES (
     '".addslashes($qname)."',
     '".addslashes($qdescrip)."',
     '".addslashes(fm_join_from_array($qfname)).   "',
     '".addslashes(fm_join_from_array($qvar)).     "',
     '".addslashes(fm_join_from_array($qftype)).   "',
     '".addslashes(fm_join_from_array($qftypefor))."',
     '".addslashes(fm_join_from_array($qfmaxlen)). "',
     '".addslashes(fm_join_from_array($qftext)).   "',
     NULL )";
   if ($debug) echo " (query = \"$query\") <P>";
   $result = fdb_query ($query);
   if ($result) { echo _("done")."."; }
    else        { echo _("ERROR");    }
   echo "
     <$STDFONT_E></CENTER>
     <P>
     <CENTER><A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>"._("back")."<$STDFONT_E></A></CENTER>
     <BR>
   ";
   freemed_display_box_bottom ();
   break;

  case "mod":
   freemed_display_box_top (_("Modifying")." "._($record_name));
   echo "
     <P><CENTER>
     <$STDFONT_B>"._("Modifying")." ...
   ";

   // do query
   $query = "UPDATE $db_name SET
      qname       = '".addslashes($qname)."',
      qdescrip    = '".addslashes($qdescrip)."',
      qfname      = '".addslashes(fm_join_from_array($qfname))."',
      qvar        = '".addslashes(fm_join_from_array($qvar))."',
      qftype      = '".addslashes(fm_join_from_array($qftype))."',
      qftypefor   = '".addslashes(fm_join_from_array($qftypefor))."',
      qfmaxlen    = '".addslashes(fm_join_from_array($qfmaxlen))."',
      qftext      = '".addslashes(fm_join_from_array($qftext))."'
      WHERE id='$id'";
   $result = fdb_query ($query);
   if ($debug) echo "query = \"$query\" <BR>";
   if ($result) { echo _("done")."."; }
    else        { echo _("ERROR");    }
   echo "
    <$STDFONT_E></CENTER>
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    "; 
   freemed_display_box_bottom ();
   break;

  case "del":
   freemed_display_box_top (_("Deleting")." "._($record_name));
   echo "
    <P><CENTER>
    <$STDFONT_B>"._("Deleting")." ...
    ";
   $query = "DELETE * FROM $db_name WHERE id='".addslashes($id)."'";
   $result = fdb_query ($query);
   if ($result) { echo _("done")."."; }
    else        { echo _("ERROR");    }
   echo "
    <$STDFONT_E></CENTER>
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER> 
   ";
   freemed_display_box_bottom ();
   break;

  default: // default action -- menu
   freemed_display_box_top (_($record_name));
   echo freemed_display_itemlist (
     fdb_query ("SELECT * FROM $db_name ORDER BY qname, qdescrip"),
     $page_name,
     array (
       _("Name")	=>	"qname",
       _("Description")	=>	"qdescrip"
     ),
     array (
       "", _("NO DESCRIPTION")
     )
   );
   freemed_display_box_bottom ();
   break;
 } // end master switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
