<?php
  # file: frmlry.php3
  # note: formulary, with prescribing info
  # code: chrisb chrisb@kippona.com
  #       jeff b (jeff@univrel.pr.uconn.edu) -- template
  # lic : GPL
  # 
  # please note that you _can_ remove the comments down below,
  # but everything above here should remain untouched. please
  # do _not_ remove my name or address from this file, since I
  # have worked very hard on it. the license must also always
  # remain GPL.                                     -- jeff b

#############################################################
#
# chrisb section
#
#  This is a preliminary pharmacy, or formulary module.
#  It interacts with a database created as follows:
#
#   create table package.frmlry ( 
#   frmlrydtadd DATE,                  // jeff - date added
#   frmlrydtmod DATE,                  // jeff - date modified
#   class VARCHAR(20),                 // nsaid, etc.
#   gnrcname VARCHAR(20),              // generic name
#   trdmrkname VARCHAR(20),            // trademark name
#   ind1 VARCHAR(50),                  // indication 1
#   ind2 VARCHAR(50),                  // indication 2
#   ind3 VARCHAR(50),                  // indication 3
#   id INT NOT NULL AUTO_INCREMENT,
#   PRIMARY KEY (id) );
#
#############################################################
# 19990718 rev
#  added "class" to database for "nsaid, etc."
#  changed table fields for compact display   
#############################################################

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="frmlry.php3"; // for help info, later
  $db_name  ="frmlry";       // get this from jeff
  $record_name="Formulary";  // such as Room for Rooms module
                                      // or "CPT Modifiers" for cptmod
  $order_field="class";             // what field the records are
                                      // sorted by... multiples can
                                      // be used with commas
                                      // ("value_a, value_b")

    // *** includes section ***

  include ("lib/freemed.php");         // load global variables
  include ("lib/API.php");  // API functions

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

    <$STDFONT_B>$Class<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=class SIZE=10 MAXLENGTH=20 
     VALUE=\"$class\">
    <BR>

    <$STDFONT_B>$Generic_Name<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=gnrcname SIZE=10 MAXLENGTH=20 
     VALUE=\"$gnrcname\">
    <BR>

    <$STDFONT_B>$Trademark_Name<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=trdmrkname SIZE=10 MAXLENGTH=20 
     VALUE=\"$trdmrkname\">
    <BR>

    <$STDFONT_B>$Indication_1<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ind1 SIZE=10 MAXLENGTH=50 
     VALUE=\"$ind1\">
    <BR>

    <$STDFONT_B>$Indication_2<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ind2 SIZE=10 MAXLENGTH=50 
     VALUE=\"$ind2\">
    <BR>

    <$STDFONT_B>$Indication_3<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ind3 SIZE=10 MAXLENGTH=50 
     VALUE=\"$ind3\">
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
     ><$STDFONT_B>$Abandon_Addition<$STDFONT_E></A>
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
  $query = "INSERT INTO $db_name VALUES ( ".
    "'$cur_date', '$cur_date', ".
    "'$class', '$gnrcname', '$trdmrkname', ".
    "'$ind1', '$ind2', '$ind3', NULL ) ";

    // query the db with new values
  $result = $sql->query($query);

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
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display the bottom of the box
  freemed_display_bottom_links ();

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  if (strlen($id)<1) {
    echo "

     <B><CENTER>Please use the MODIFY form to MODIFY a
       $record_name!</B>
     </CENTER>

     <P>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <BR><BR>
      ";
    }

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  $result = $sql->query("SELECT * FROM $db_name ".
    "WHERE ( id = '$id' )");

    // display for debugging purposes
  if ($debug==1) {
    echo " <B>RESULT</B> = [$result]<BR><BR> ";
  }

  $r = $sql->fetch_array($result); // dump into array r[]

    $frmlrydtadd = $r["frmlrydtadd"];
    $class       = $r["class"      ];
    $gnrcname    = $r["gnrcname"   ];
    $trdmrkname  = $r["trdmrkname" ];
    $ind1        = $r["ind1"       ];
    $ind2        = $r["ind2"       ];
    $ind3        = $r["ind3"       ];

  echo "
    <P>
    <$HEADERFONT_B>$Date_Added : <I>$frmlrydtadd</I><$HEADERFONT_E>
    <P>
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <$STDFONT_B>$Class<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=class SIZE=10 MAXLENGTH=20 
     VALUE=\"$class\">
    <BR>

    <$STDFONT_B>$Generic_Name<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=gnrcname SIZE=10 MAXLENGTH=20 
     VALUE=\"$gnrcname\">
    <BR>

    <$STDFONT_B>$Trademark_Name<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=trdmrkname SIZE=10 MAXLENGTH=20 
     VALUE=\"$trdmrkname\">
    <BR>

    <$STDFONT_B>$Indication_1<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ind1 SIZE=10 MAXLENGTH=50 
     VALUE=\"$ind1\">
    <BR>

    <$STDFONT_B>$Indication_2<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ind2 SIZE=10 MAXLENGTH=50 
     VALUE=\"$ind2\">
    <BR>

    <$STDFONT_B>$Indication_3<$STDFONT_E>
    <INPUT TYPE=TEXT NAME=ind3 SIZE=10 MAXLENGTH=50 
     VALUE=\"$ind3\">
    <BR>

    <BR>
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
     ><$STDFONT_B>$Abandon_Modification<$STDFONT_E></A>
    </CENTER>
  ";

} elseif ($action=="mod") {

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
  $query = "UPDATE $db_name SET ".
    "frmlrydtmod = '$cur_date',     ".
    "class       = '$class',        ".
    "gnrcname    = '$gnrcname',     ".
    "trdmrkname  = '$trdmrkname',   ".
    "ind1        = '$ind1',         ".
    "ind2        = '$ind2',         ".
    "ind3        = '$ind3'          ".
    "WHERE id='$id'";

  $result = $sql->query($query); // execute query

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
  } // end of error reporting clause

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom (); // display box bottom 
  freemed_display_bottom_links ();

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = $sql->query("DELETE FROM $db_name
    WHERE (id = \"$id\")");

  echo "
    <P>
    <I>$record_name <B>$id</B> $Deleted<I>.
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
  freemed_display_bottom_links ();

} else {

  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...

  $query = "SELECT * FROM $db_name ".
   "ORDER BY $order_field";

  $result = $sql->query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php";
    } // if no ref, then return to home page...

    // if you would rather have the add form built onto the view
    // menu, uncomment the next few lines. 

    // echo "
    //   <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
    //    CELLSPACING=0 CELLPADDING=3>
    //   <TR BGCOLOR=#000000>
    //   <TD ALIGN=LEFT>&nbsp;</TD>
    //   <TD WIDTH=30%>&nbsp;</TD>
    //   <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
    //    ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
    //    SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
    //   </TR></TABLE>
    // ";

    // and comment this line:
    freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Class</B></TD>
       <TD><B>$Generic_Name</B></TD>
       <TD><B>$Trademark_Name</B></TD>
       <TD><B>$Indication_1</B></TD>
       <TD><B>$Indication_2</B></TD>
       <TD><B>$Indication_3</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = $sql->fetch_array($result)) {

    $class      = $r["class"     ];
    $gnrcname   = $r["gnrcname"  ];
    $trdmrkname = $r["trdmrkname"];
    $ind1       = $r["ind1"      ];
    $ind2       = $r["ind2"      ];
    $ind3       = $r["ind3"      ];
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
        <TD>$class</TD>
        <TD>$gnrcname</TD>
        <TD><I>$trdmrkname</I></TD>
        <TD>$ind1</TD>
        <TD>$ind2</TD>
        <TD>$ind3</TD>
        <TD> <A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$lang_MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($LoginCookie)>$delete_level)
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$lang_DEL$id_mod</FONT></A>
        "; // show delete
      echo "
        </TD></TR>
      ";

    } // while there are no more

      // now, we put the add table part...
      // uncomment if needed.

    // $_alternate = freemed_bar_alternate_color ($_alternate);
    // echo "
    //   <TR BGCOLOR=$_alternate VALIGN=CENTER>
    //   <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
    //    ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
    //    <td><INPUT TYPE=TEXT NAME=\"class\" SIZE=21
    //     MAXLENGTH=20></TD>
    //    <td><INPUT TYPE=TEXT NAME=\"gnrcname\" SIZE=21
    //     MAXLENGTH=20></TD>
    //    <td><INPUT TYPE=TEXT NAME=\"trdmrkname\" SIZE=21
    //     MAXLENGTH=20></TD>
    //    <td><INPUT TYPE=TEXT NAME=\"ind1\" SIZE=15
    //     MAXLENGTH=50></TD>
    //    <td><INPUT TYPE=TEXT NAME=\"ind2\" SIZE=15
    //     MAXLENGTH=50></TD>
    //    <td><INPUT TYPE=TEXT NAME=\"ind3\" SIZE=15
    //     MAXLENGTH=50></TD>
    //   <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"ADD\"></FORM></TD>
    //   </TR>
    // ";

    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php";
    } // if no ref, then return to home page...

    //  if you would rather have the add form built onto the view
    //  menu, just uncomment the next few lines for a bar without
    //  the add function...

    // echo "
    //   <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
    //    CELLSPACING=0 CELLPADDING=3>
    //   <TR BGCOLOR=#000000>
    //   <TD ALIGN=LEFT>&nbsp;</TD>
    //   <TD WIDTH=30%>&nbsp;</TD>
    //   <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
    //    ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
    //    SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
    //   </TR></TABLE>
    // ";

    // then comment this:
    freemed_display_actionbar ($page_name, $_ref);
    if ( file_exists ("lang/$language/doc/$page_name.$language.html") ) 
      echo "
        <BR><CENTER>
        <A HREF=\"help.php3?$_auth&page_name=$page_name\" TARGET=_HELP_
         ><$STDFONT_B>$lang_HELP<$STDFONT_E></A>
        </CENTER><BR>
      ";
    freemed_display_box_bottom (); // display bottom of the box
    freemed_display_bottom_links ();

  } else {
    echo "\n<B>$No_Records_Found</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
