<?php
 # file: fixed_forms.php3
 # desc: fixed type forms editing engine
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name   = "fixed_forms.php3";
 $record_name = "Fixed Form";
 $db_name     = "fixedform";

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
        $query  = "SELECT * FROM $db_name WHERE id='$id'";
        $result = fdb_query ($query);
        $r      = fdb_fetch_array ($result);
        $ffname       = $r["ffname"      ];
        $ffdescrip    = $r["ffdescrip"   ];
        $fftype       = $r["fftype"      ]; 
        $ffcheckchar  = $r["ffcheckchar" ];
        $ffpagelength = $r["ffpagelength"];
        $fflinelength = $r["fflinelength"];
        $ffloopnum    = $r["ffloopnum"   ];
        $ffloopoffset = $r["ffloopoffset"];
        $row          = fm_split_into_array ($r["ffrow"]);
        $col          = fm_split_into_array ($r["ffcol"]);
        $len          = fm_split_into_array ($r["fflength"]);
        $data         = fm_split_into_array ($r["ffdata"]);
        $format       = fm_split_into_array ($r["ffformat"]);
        $comment      = fm_split_into_array ($r["ffcomment"]);
        break;
      } // end checking if we have been here yet...
   } // end of interior switch

   // set the fftype properly
   for ($i=0;$i<=20;$i++) 
    eval ("\$_type_".$i."     = \"\"         ; ");
   eval ("\$_type_".$fftype." = \"SELECTED\" ; ");

   freemed_display_box_top ("$this_action $record_name");
   $cur_line_count = 0; // zero the current line count (displayed)
   $prev_line_total = count ($row); // previous # of lines
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
     <TD ALIGN=RIGHT><$STDFONT_B>Name of Form : <$STDFONT_E></TD>
      <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffname\" SIZE=20 MAXLENGTH=50
       VALUE=\"".fm_prep($ffname)."\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Page Length : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffpagelength\" SIZE=5 MAXLENGTH=5
       VALUE=\"$ffpagelength\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Description : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffdescrip\" SIZE=20 MAXLENGTH=100
       VALUE=\"".fm_prep($ffdescrip)."\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Line Length : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"fflinelength\" SIZE=5 MAXLENGTH=5
       VALUE=\"$fflinelength\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Loop Repetitions : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffloopnum\" SIZE=5 MAXLENGTH=5
       VALUE=\"$ffloopnum\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Check Char : <BR>
                     (<I>ex: \"X\"</I>)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffcheckchar\" SIZE=2 MAXLENGTH=1
       VALUE=\"".fm_prep($ffcheckchar)."\"> 
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT><$STDFONT_B>Loop Line Offset : <BR>
                     (<I>1 skips to the next line</I>)<$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"ffloopoffset\" SIZE=5 MAXLENGTH=5
       VALUE=\"$ffloopoffset\">
     </TD>
     <TD ALIGN=RIGHT><$STDFONT_B>Type : <$STDFONT_E></TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"fftype\">
       <OPTION VALUE=\"0\" $_type_0>generic
       <OPTION VALUE=\"1\" $_type_1>insurance claim
       <OPTION VALUE=\"2\" $_type_2>patient bill
      </SELECT>
     </TD>
    </TR>
    </TABLE>
    <P>
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
     <TR BGCOLOR=#000000>
      <TD><$STDFONT_B COLOR=#ffffff>#<$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><CENTER><B>Ins/Del</B></CENTER>
        <$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Row/Line</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Column</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Length</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Data</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Format</B><$STDFONT_E></TD>
      <TD><$STDFONT_B COLOR=#ffffff><B>Comment</B><$STDFONT_E></TD>
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
       <TR BGCOLOR=\"$_alternate\">
        <TD><$STDFONT_B COLOR=\"$num_color\">".($cur_line_count+1)."<$STDFONT_E></TD>
        <TD><CENTER>
            <INPUT TYPE=CHECKBOX NAME=\"ins$brackets\"
             VALUE=\"$cur_line_count\">
            <INPUT TYPE=CHECKBOX NAME=\"del$brackets\"
             VALUE=\"$cur_line_count\"></CENTER></TD>
        <TD><INPUT TYPE=TEXT NAME=\"row$brackets\" SIZE=5
          MAXLENGTH=3 VALUE=\"".fm_prep($row[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"col$brackets\" SIZE=5
          MAXLENGTH=3 VALUE=\"".fm_prep($col[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"len$brackets\" SIZE=5
          MAXLENGTH=3 VALUE=\"".fm_prep($len[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"data$brackets\" SIZE=15
          MAXLENGTH=100 VALUE=\"".fm_prep($data[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"format$brackets\" SIZE=5
          MAXLENGTH=100 VALUE=\"".fm_prep($format[$i])."\"></TD>
        <TD><INPUT TYPE=TEXT NAME=\"comment$brackets\" SIZE=15
          MAXLENGTH=100 VALUE=\"".fm_prep($comment[$i])."\"></TD>
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
        <TR BGCOLOR=\"$_alternate\">
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
    freemed_display_box_bottom ();
   break;

  case "add":
   freemed_display_box_top ("$Adding $record_name");
   echo "
     $Adding ...
   ";
   // check to see if we need to compact into a string...
   if (count($row)>0) {
     $row_a     = fm_join_from_array ($row    );
     $col_a     = fm_join_from_array ($col    );
     $len_a     = fm_join_from_array ($len    );
     $data_a    = fm_join_from_array ($data   );
     $format_a  = fm_join_from_array ($format );
     $comment_a = fm_join_from_array ($comment);
   } else {
     $row_a     = $row;
     $col_a     = $col;
     $len_a     = $len;
     $data_a    = $data;
     $format_a  = $format;
     $comment_a = $comment;
   }
   $query = "INSERT INTO $db_name VALUES (
     '".addslashes($ffname)."',
     '".addslashes($ffdescrip)."',
     '".addslashes($fftype)."',
     '$ffpagelength',
     '$fflinelength',
     '$ffloopnum',
     '$ffloopoffset',
     '$ffcheckchar',
     '".addslashes($row_a)."',
     '".addslashes($col_a)."',
     '".addslashes($len_a)."',
     '".addslashes($data_a)."',
     '".addslashes($format_a)."',
     '".addslashes($comment_a)."',
     NULL )";
   //echo " (query = $query) ";
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

   // prepare data for squash
   $row_a     = fm_join_from_array ($row    );
   $col_a     = fm_join_from_array ($col    );
   $len_a     = fm_join_from_array ($len    );
   $data_a    = fm_join_from_array ($data   );
   $format_a  = fm_join_from_array ($format );
   $comment_a = fm_join_from_array ($comment);

   // do query
   $query = "UPDATE $db_name SET
      ffname       = '".addslashes($ffname)."',
      ffdescrip    = '".addslashes($ffdescrip)."',
      fftype       = '".addslashes($fftype)."',
      ffpagelength = '$ffpagelength',
      fflinelength = '$fflinelength',
      ffloopnum    = '$ffloopnum',
      ffloopoffset = '$ffloopoffset',
      ffcheckchar  = '$ffcheckchar',
      ffrow        = '".addslashes($row_a)."',
      ffcol        = '".addslashes($col_a)."',
      fflength     = '".addslashes($len_a)."',
      ffdata       = '".addslashes($data_a)."',
      ffformat     = '".addslashes($format_a)."',
      ffcomment    = '".addslashes($comment_a)."'
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
   $query = "DELETE * FROM $db_name WHERE id='$id'";
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
   $result = fdb_query ("SELECT * FROM $db_name
                         ORDER BY ffname, ffdescrip");
   if (fdb_num_rows($result)>0) {

    // display action bar
    freemed_display_actionbar ();

    // display table top
    echo "
      <P>
      <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=2 BORDER=0
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
      $ffname     = fm_prep($r["ffname"   ]);
      $ffdescrip  = fm_prep($r["ffdescrip"]);
      $id         =         $r["id"       ] ;

      echo "
        <TR BGCOLOR=\"$_alternate\">
         <TD>$ffname</TD>
         <TD>$ffdescrip</TD>
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
