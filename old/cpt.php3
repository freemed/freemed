<?php
  # file: cpt.php3
  # note: cpt codes database functions
  # code: used the template due to extreme sloth
  #       jeff b (jeff@univrel.pr.uconn.edu) -- template
  # lic : GPL
  # mod : 19990917

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="cpt.php3";              // for help info, later
  $db_name  ="cpt";                   // get this from jeff
  $record_name="CPT Code";            // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="cptcode,cptnameint";  // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")

    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** setting _ref cookie ***
    // if you are going to be "chaining" out from this
    // function and want users to be able to return to
    // it, uncomment this and it will set the cookie to
    // return people using the bar.
  //SetCookie("_ref", $page_name, time()+$_cookie_expire);

    // *** authorizing user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

// *** main action loop ***
// (default action is "view")
if ($action=="addform") {

  freemed_display_box_top ("$Add $record_name", $page_name);

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <TT>$CPT_Code : </TT>
    <INPUT TYPE=TEXT NAME=cptcode SIZE=8 MAXLENGTH=7 
     VALUE=\"$cptcode\">
    <BR>

    <TT>$CPT_Name_Internal : </TT>
    <INPUT TYPE=TEXT NAME=cptnameint SIZE=20 MAXLENGTH=50
     VALUE=\"$cptnameint\">
    <BR>
    <TT>$CPT_Name_External : </TT>
    <INPUT TYPE=TEXT NAME=cptnameext SIZE=20 MAXLENGTH=50
     VALUE=\"$cptnameext\">
    <BR>    
    <TT>$Relative_Value ($Example: 2.1) : </TT>
    <INPUT TYPE=TEXT NAME=cptrelvalue SIZE=10 MAXLENGTH=7
     VALUE=\"$cptrelvalue\">
    <BR>

    <TT>$Type_of_Service : </TT>
    <SELECT NAME=\"cpttos\">
  ";

  freemed_display_tos ($cpttos);

  echo "
    </SELECT>

    <BR>
    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \">
    <INPUT TYPE=RESET  VALUE=\" $Clear \">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Addition</A>
    </CENTER>
  ";

} elseif ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

    // build the query to MySQL:
    // the last value has to be NULL so that it auto
    // increments record numbers.
  $query = "INSERT INTO $database.$db_name VALUES ( ".
    "'$cptcode',      ".
    "'$cptnameint',   ".
    "'$cptnameext',   ".
    "'$cptrelvalue',  ".
    "'$cptgender',    ".
    "'$cpttos',       ".
    "'$cur_date',     ". // cptdtadd
    "'$cur_date',     ". // cptdtmod
    " NULL ) ";

    // query the db with new values
  $result = fdb_query($query);

  if ($debug==1) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;      
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=addform\"
     ><$STDFONT_B>Add Another $record_name<$STDFONT_E></A> <B>|</B>
     <A HREF=\"$page_name?$_auth&action=view\"
     ><$STDFONT_B>$View_Manage $record_name<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display the bottom of the box
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name");

  if (empty($id)) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <BR><BR>
    ";

    if ($debug) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
      ";
    }

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  $result = fdb_query("SELECT * FROM $database.$db_name ".
    "WHERE ( id = '$id' )");

    // display for debugging purposes
  if ($debug==1) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

  $cptcode     = $r["cptcode"    ];
  $cptnameint  = $r["cptnameint" ];
  $cptnameext  = $r["cptnameext" ];
  $cptrelvalue = $r["cptrelvalue"];
  $cptgender   = $r["cptgender"  ];
  $cpttos      = $r["cpttos"     ];

  echo "
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"$id\">

    <$STDFONT_B>$CPT_Code : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=cptcode SIZE=8 MAXLENGTH=7 
     VALUE=\"$cptcode\">
    <BR>

    <$STDFONT_B>$CPT_Name_Internal : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=cptnameint SIZE=20 MAXLENGTH=50
     VALUE=\"$cptnameint\">
    <BR>
    <$STDFONT_B>$CPT_Name_External : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=cptnameext SIZE=20 MAXLENGTH=50
     VALUE=\"$cptnameext\">
    <BR>    
    <$STDFONT_B>$Relative_Value ($Example: 2.1) : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=cptrelvalue SIZE=10 MAXLENGTH=7
     VALUE=\"$cptrelvalue\">
    <BR>

    <$STDFONT_B>$Type_of_Service : <$STDFONT_E>
    <SELECT NAME=\"cpttos\">
  ";

  freemed_display_tos ($cpttos);

  echo "
    </SELECT>

    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
  ";

  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Modification</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

   #      M O D I F Y - R O U T I N E

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

    // build update query:
    // only set the values that need to be
    // changed... for example, don't set the
    // creation date in a modify. also,
    // remember the commas...
  $query = "UPDATE $database.$db_name SET ".
    "cptcode     = '$cptcode',     ".
    "cptnameint  = '$cptnameint',  ".
    "cptnameext  = '$cptnameext',  ".
    "cptrelvalue = '$cptrelvalue', ".
    "cptgender   = '$cptgender',   ".
    "cpttos      = '$cpttos',      ".
    "cptdtmod    = '$cur_date'     ".
    "WHERE id='$id'";

  $result = fdb_query($query); // execute query

  if ($debug==1) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B></TT>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  } // end of error reporting clause

  freemed_display_box_bottom (); // display box bottom 
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $database.$db_name
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> deleted<I>.
    <BR>
  ";
  if ($debug==1) {
    echo "
      <BR><B>RESULT:</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="export") {

  freemed_display_box_top ("$Export $record_name", $_ref, $page_name);
  echo "
    <P>
    <$STDFONT_B>$Exporting_data ...
  ";

  $result = freemed_export_stock_data ($db_name);

  if ($result) echo "$Done.";
   else echo "$ERROR";

  echo "
    <$STDFONT_E>

    <P>

    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>

    <P>
  ";

  freemed_display_box_bottom ();

} else {

  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

  $query = "SELECT * FROM $database.$db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // if you would rather have the add form built onto the view
    // menu, uncomment the next few lines. 

    //echo "
    //  <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
    //   CELLSPACING=0 CELLPADDING=3>
    //  <TR BGCOLOR=#000000>
    //  <TD ALIGN=LEFT>&nbsp;</TD>
    //  <TD WIDTH=30%>&nbsp;</TD>
    //  <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
    //   ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
    //   SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
    //  </TR></TABLE>
    //";

    // and comment this line:
    freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$CPT_Code</B></TD>
       <TD><B>$Internal_Name</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $cptcode    = $r["cptcode"   ];
      $cptnameint = $r["cptnameint"];
      $id         = $r["id"        ];

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$cptcode</TD>
        <TD><I>$cptnameint</I>&nbsp;</TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($user)>$delete_level)
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$DEL$id_mod</FONT></A>
        "; // show delete
      echo "
        </TD></TR>
      ";

    } // while there are no more

      // now, we put the add table part...
      // uncomment if needed.

    //$_alternate = freemed_bar_alternate_color ($_alternate);
    //echo "
    //  <TR BGCOLOR=$_alternate VALIGN=CENTER>
    //  <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
    //   ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
    //   <INPUT TYPE=TEXT NAME=\"value_a\" SIZE=3
    //    MAXLENGTH=2></TD>
    //  <TD VALIGN=CENTER>
    //   <INPUT TYPE=TEXT NAME=\"value_b\" SIZE=20
    //    MAXLENGTH=30></TD>
    //  <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"ADD\"></FORM></TD>
    //  </TR>
    //";

    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    //  if you would rather have the add form built onto the view
    //  menu, just uncomment the next few lines for a bar without
    //  the add function...

    //echo "
    //  <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
    //   CELLSPACING=0 CELLPADDING=3>
    //  <TR BGCOLOR=#000000>
    //  <TD ALIGN=LEFT>&nbsp;</TD>
    //  <TD WIDTH=30%>&nbsp;</TD>
    //  <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
    //   ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
    //   SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
    //  </TR></TABLE>
    //";

    // then comment this:
    freemed_display_actionbar ($page_name, $_ref);

    if (freemed_get_userlevel ($LoginCookie) > $export_level)
      echo "
        <BR>
        <CENTER><A HREF=\"$page_name?$_auth&action=export\"
         ><$STDFONT_B>$Export_Database<$STDFONT_E></A></CENTER>
        <BR>
      ";

    freemed_display_box_bottom (); // display bottom of the box
    freemed_display_bottom_links ($record_name, $page_name, $_ref);

  } else {
    echo "\n<B>no $record_name found with that criteria.</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
