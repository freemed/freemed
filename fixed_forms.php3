<?php
 // file: fixed_forms.php3
 // desc: fixed type forms editing engine
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

 $page_name   = "fixed_forms.php3";
 $record_name = "Fixed Form";
 $db_name     = "fixedform";

 include ("lib/freemed.php");
 include ("lib/API.php");

 freemed_open_db ($LoginCookie);
 freemed_display_html_top ();

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
        $result = $sql->query ($query);
        $r      = $sql->fetch_array ($result);
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
   for ($i=0;$i<=20;$i++) ${"_type_".$i} = "";
   ${"_type_".$fftype} = "SELECTED";

   freemed_display_box_top ("$this_action "._($record_name));
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
    freemed_display_box_bottom ();
   break;

  case "add":
   freemed_display_box_top (_("Adding")." "._($record_name));
   echo "
     <CENTER><$STDFONT_B>"._("Adding")." ...
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
     '".addslashes($ffpagelength)."',
     '".addslashes($fflinelength)."',
     '".addslashes($ffloopnum)."',
     '".addslashes($ffloopoffset)."',
     '".addslashes($ffcheckchar)."',
     '".addslashes($row_a)."',
     '".addslashes($col_a)."',
     '".addslashes($len_a)."',
     '".addslashes($data_a)."',
     '".addslashes($format_a)."',
     '".addslashes($comment_a)."',
     NULL )";
   $result = $sql->query ($query);
   if ($result) { echo "<B>"._("done").".</B>"; }
    else        { echo "<B>"._("ERROR")."</B>"; }
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
      ffpagelength = '".addslashes($ffpagelength)."',
      fflinelength = '".addslashes($fflinelength)."',
      ffloopnum    = '".addslashes($ffloopnum)."',
      ffloopoffset = '".addslashes($ffloopoffset)."',
      ffcheckchar  = '".addslashes($ffcheckchar)."',
      ffrow        = '".addslashes($row_a)."',
      ffcol        = '".addslashes($col_a)."',
      fflength     = '".addslashes($len_a)."',
      ffdata       = '".addslashes($data_a)."',
      ffformat     = '".addslashes($format_a)."',
      ffcomment    = '".addslashes($comment_a)."'
      WHERE id='".addslashes($id)."'";
   $result = $sql->query ($query);
   if ($debug) echo "query = \"$query\" <BR>";
   if ($result) { echo "<B>"._("done").".</B>"; }
    else        { echo "<B>"._("ERROR")."</B>"; }
   echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
      ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    "; 
   freemed_display_box_bottom ();
   break;

  case "del": case "delete":
   freemed_display_box_top (_("Deleting")." "._($record_name));
   echo "
    <P><CENTER>
    <$STDFONT_B>"._("Deleting")." ...
    ";
   $query = "DELETE * FROM $db_name WHERE id='".addslashes($id)."'";
   $result = $sql->query ($query);
   if ($result) { echo "<B>"._("done").".</B>"; }
    else        { echo "<B>"._("ERROR")."</B>"; }  
   echo "
    <$STDFONT_E>
    </CENTER>
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
   $result = $sql->query ("SELECT * FROM $db_name
                         ORDER BY ffname, ffdescrip");
   echo freemed_display_itemlist (
     $sql->query ("SELECT * FROM $db_name ORDER BY ffname, ffdescrip"),
     $page_name,
     array (
	_("Name")		=>	"ffname",
	_("Description")	=>	"ffdescrip"
     ),
     array ( "", _("NO DESCRIPTION") )
   );  
     
   freemed_display_box_bottom ();
   break;
 } // end master switch

 freemed_close_db ();
 freemed_display_html_bottom ();
?>
