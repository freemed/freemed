<?php
 # file: patient_record_template.php3	
 # desc: patient record template editing engine
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name   = "patient_record_template.php3";
 $record_name = "Patient Record Templates";
 $db_name     = "patrectemplate";

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
      $this_action = "$Add";
      break;
     case "modform":
      $go = "mod";
      $this_action = "$Modify";
       // check to see if an id was submitted
      if ($id<1) {
       freemed_display_box_top ("$record_name :: $ERROR");
       echo "
         You must select a record to modify.
       ";
       freemed_display_box_bottom ();
       freemed_close_db ();
       freemed_display_html_bottom ();
       DIE("");
      } // end of if.. statement checking for id #

      if ($been_here != "yes") {
         // now we extract the data, since the record was given...
        $query  = "SELECT * FROM $database.$db_name WHERE id='$id'";
        $result = fdb_query ($query);
        $r      = fdb_fetch_array ($result);
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
   freemed_display_box_top ("$this_action $record_name");
   $cur_line_count = 0; // zero the current line count (displayed)
   $prev_line_total = count ($prtfname); // previous # of lines
     // display the top of the repetitive table
   if ($prev_line_total == 0) {
     $insert[0]="ON";
     $first_insert = true;
   }
   echo "
    <FORM ACTION=\"$page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"id\" VALUE=\"$id\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Name of Template<$STDFONT_E></TD>
      <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"prtname\" SIZE=20 MAXLENGTH=50
       VALUE=\"".fm_prep($prtname)."\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Description<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"prtdescrip\" SIZE=20 MAXLENGTH=100
       VALUE=\"".fm_prep($prtdescrip)."\">
     </TD>
    </TR><TR>
    </TABLE>
    <P>
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR BGCOLOR=#000000>
      <TD><$STDFONT_B COLOR=#ffffff>#<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><CENTER><B>Ins/Del</B></CENTER>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Text Name</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Variable</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Limits</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Type</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Type Formatting</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Comment</B><$STDFONT_E></TD>
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
      $_alternate = freemed_bar_alternate_color ($_alternate);
      echo "
       <TR BGCOLOR=\"$_alternate\">
        <TD ALIGN=RIGHT><$STDFONT_B COLOR=\"$num_color\"
         >".($cur_line_count+1)."<$STDFONT_E></TD>
        <TD><CENTER>
            <INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
             VALUE=\"$cur_line_count\">
            <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
             VALUE=\"$cur_line_count\"></CENTER></TD>
        <TD><INPUT TYPE=TEXT NAME=\"prtfname$brackets\" SIZE=15
          MAXLENGTH=100 VALUE=\"".fm_prep($prtfname[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"prtvar$brackets\" SIZE=10
          MAXLENGTH=20 VALUE=\"".fm_prep($prtvar[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"prtfmaxlen$brackets\" SIZE=5
          MAXLENGTH=20 VALUE=\"".fm_prep($prtfmaxlen[$i])."\"></TD>
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
          MAXLENGTH=1000 VALUE=\"".fm_prep($prtftypefor[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"prtfcom$brackets\" SIZE=25
          MAXLENGTH=100 VALUE=\"".fm_prep($prtfcom[$i])."\"></TD>
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
      <OPTION VALUE=\"$action\">Update
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
     $Adding ...
   ";
   $query = "INSERT INTO $database.$db_name VALUES (
     '".addslashes($prtname)."',
     '".addslashes($prtdescrip)."',
     '".addslashes(fm_join_from_array($prtfname)).   "',
     '".addslashes(fm_join_from_array($prtvar)).     "',
     '".addslashes(fm_join_from_array($prtftype)).   "',
     '".addslashes(fm_join_from_array($prtftypefor))."',
     '".addslashes(fm_join_from_array($prtfmaxlen)). "',
     '".addslashes(fm_join_from_array($prtfcom)).    "',
     NULL )";
   if ($debug) echo " (query = \"$query\") <P>";
   $result = fdb_query ($query);
   if ($result) { echo "$Done."; }
    else        { echo "$ERROR"; }
   echo "
     <P>
     <CENTER><A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A></CENTER>
     <BR>
   ";
   freemed_display_box_bottom ();
   break;

  case "mod":
   freemed_display_box_top ("$Modifying $record_name");
   echo "
     <P>
     <$STDFONT_B>$Modifying ...
   ";

   // do query
   $query = "UPDATE $database.$db_name SET
      prtname       = '".addslashes($prtname)."',
      prtdescrip    = '".addslashes($prtdescrip)."',
      prtfname      = '".addslashes(fm_join_from_array($prtfname))."',
      prtvar        = '".addslashes(fm_join_from_array($prtvar))."',
      prtftype      = '".addslashes(fm_join_from_array($prtftype))."',
      prtftypefor   = '".addslashes(fm_join_from_array($prtftypefor))."',
      prtfmaxlen    = '".addslashes(fm_join_from_array($prtfmaxlen))."',
      prtfcom       = '".addslashes(fm_join_from_array($prtfcom))."'
      WHERE id='$id'";
   $result = fdb_query ($query);
   if ($debug) echo "query = \"$query\" <BR>";
   if ($result) { echo "$Done. <$STDFONT_E>";  }
    else        { echo "$ERROR! <$STDFONT_E>"; }
   echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    "; 
   freemed_display_box_bottom ();
   break;

  case "del":
   freemed_display_box_top ("$Deleting $record_name");
   echo "
    <P>
    <$STDFONT_B>$Deleting ...
    ";
   $query = "DELETE * FROM $database.$db_name WHERE id='$id'";
   $result = fdb_query ($query);
   if ($result) { echo "$Done\n";    }
    else        { echo "$ERROR\n";   }
   echo "
    <$STDFONT_E>
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER> 
   ";
   freemed_display_box_bottom ();
   break;

  default: // default action -- menu
   freemed_display_box_top ("$record_name");
   $result = fdb_query ("SELECT * FROM $database.$db_name
                         ORDER BY prtname, prtdescrip");
   if (fdb_num_rows($result)>0) {

    // display action bar
    freemed_display_actionbar ();

    // display table top
    echo "
      <P>
      <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3 BORDER=0
       BGCOLOR=#000000 VALIGN=MIDDLE ALIGN=CENTER>
      <TR BGCOLOR=#000000>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>Name<$STDFONT_E></TD>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>Description<$STDFONT_E></TD>
       <TD BGCOLOR=#000000>
        <$STDFONT_B COLOR=#ffffff>Action<$STDFONT_E></TD>
      </TR>
     ";

    // loop for all
    while ($r = fdb_fetch_array ($result)) {
      $_alternate = freemed_bar_alternate_color ($_alternate);
      $prtname     = fm_prep($r["prtname"   ]);
      $prtdescrip  = fm_prep($r["prtdescrip"]);
      $id          =         $r["id"        ] ;

      echo "
        <TR BGCOLOR=\"$_alternate\">
         <TD><B>$prtname</B></TD>
         <TD><I>$prtdescrip</I></TD>
         <TD>
       ";

      if (freemed_get_userlevel($LoginCookie)>$database_level)
       echo "
        <A HREF=\"$page_name?$_auth&action=modform&id=$id\"
         ><$STDFONT_B SIZE=-1>$lang_MOD<$STDFONT_E></A>
       ";

      if (freemed_get_userlevel($LoginCookie)>$delete_level)
       echo "
        <A HREF=\"$page_name?$_auth&action=del&id=$id\"
         ><$STDFONT_B SIZE=-1>$lang_DEL<$STDFONT_E></A>
       ";

      echo "
         &nbsp;</TD>
        </TR>
       ";
    } // end of while loop 

    // display table bottom
    echo "
      </TABLE>
      <P>
     ";
 
    // display bottom action bar
    freemed_display_actionbar ();
   } else { // if there aren't any records, tell us so
    echo "
      <P>
      <CENTER>
       <B><$STDFONT_B>There are no records.<$STDFONT_E></B>
       <P>
       <A HREF=\"$page_name?$_auth&action=addform\"
        ><$STDFONT_B>$Add $record_name<$STDFONT_E></A>
      </CENTER>
      <P>
    ";
   }
   freemed_display_box_bottom ();
   break;
 } // end master switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
