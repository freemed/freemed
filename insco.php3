<?php
 // file: insco.php3
 // note: insurance company database services
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

  $page_name   = "insco.php3";
  $record_name = "Insurance Company";
  $db_name     = "insco";

  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top();
  freemed_display_banner();

switch ($action) {
 case "addform":
 case "modform":

  switch ($action) {
    case "addform":
      // no prep work here
      break; // end of addform
    case "modform":
      $r = freemed_get_link_rec ($id, $db_name);
      extract ($r); 
      break; // end of modform
  } // end inner action switch

  $book = new notebook ( array ("action", "_auth", "id", "been_here"),
    NOTEBOOK_COMMON_BAR|NOTEBOOK_STRETCH);

  $book->add_page(
   _("Contact Information"),
   array(""),"
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
   
    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Company Name (full)")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"insconame\" SIZE=20 MAXLENGTH=50
     VALUE=\"".prepare($insconame)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Company Name (on forms)")." : 
      <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoalias\" SIZE=20 MAXLENGTH=30
     VALUE=\"".prepare($inscoalias)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Address Line 1")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoaddr1\" SIZE=30 MAXLENGTH=30
     VALUE=\"".prepare($inscoaddr1)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Address Line 2")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoaddr2\" SIZE=30 MAXLENGTH=30
     VALUE=\"".prepare($inscoaddr2)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("City").", "._("State")."
      "._("Zip")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT>  
    <INPUT TYPE=TEXT NAME=\"inscocity\" SIZE=20 MAXLENGTH=20
     VALUE=\"".prepare($inscocity)."\"><B>,</B>
    <INPUT TYPE=TEXT NAME=\"inscostate\" SIZE=4 MAXLENGTH=3
     VALUE=\"".prepare($inscostate)."\">
    <INPUT TYPE=TEXT NAME=\"inscozip\" SIZE=10 MAXLENGTH=10
     VALUE=\"".prepare($inscozip)."\">
    </TD> 
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Contact Phone")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT>".fm_phone_entry ("inscophone")."</TD>
    </TR>
  
    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Fax Number")."<$STDFONT_E></TD>
    <TD ALIGN=LEFT>".fm_phone_entry ("inscofax")."</TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Email Address")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoemail\" SIZE=20 MAXLENGTH=50
     VALUE=\"".prepare($inscoemail)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Web Site")."
      (<I>http://insco.com</I>)<$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscowebsite\" SIZE=15 MAXLENGTH=100
     VALUE=\"".prepare($inscowebsite)."\"></TD>
    </TR>

    </TABLE>
  ");

  $book->add_page(
   _("Internal Information"),
   array(""),"
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3>
   
    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("NEIC ID")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoid\" SIZE=11 MAXLENGTH=10
     VALUE=\"".prepare($inscoid)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Insurance Group")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT>".freemed_display_selectbox(
      fdb_query("SELECT inscogroup FROM inscogroup ORDER BY inscogroup"),
      "#inscogroup#", "inscogroup")."</TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Insurance Type")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscotype\" SIZE=10 MAXLENGTH=30
     VALUE=\"".prepare($inscotype)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>Insurance Assign? : <$STDFONT_E></TD>
    <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"inscoassign\" SIZE=10 MAXLENGTH=12
     VALUE=\"".prepare($inscoassign)."\"></TD>
    </TR>

    <TR>
    <TD ALIGN=RIGHT><$STDFONT_B>"._("Insurance Modifiers")." : <$STDFONT_E></TD>
    <TD ALIGN=LEFT>".freemed_multiple_choice ("SELECT * FROM insmod
      ORDER BY insmoddesc", "insmoddesc", "inscomod",
      $inscomod, false)."</TD>
    </TR>

    </TABLE>
  "); 
  
  freemed_display_box_top ( ( ($action=="addform") ? _("Add") : _("Modify") ).
    " $record_name", $page_name);
  if (!$book->is_done()) {
    $book->display();
  } else {
    switch ($action) {
      case "add": case "addform":
       $inscodtadd = $cur_date; // set date added to current
       $inscodtmod = $cur_date; // set date modified to current

       $query = "INSERT INTO $db_name VALUES ( ".
        "'$inscodtadd',     ".
         "'$inscodtmod',     ".
         "'".addslashes($insconame)."',          ".
         "'".addslashes($inscoalias)."',         ".
         "'".addslashes($inscoaddr1)."',         ".
         "'".addslashes($inscoaddr2)."',         ".
         "'".addslashes($inscocity)."',          ".
         "'".addslashes($inscostate)."',         ".
         "'".addslashes($inscozip)."',           ".
         "'".fm_phone_assemble("inscophone")."', ".
         "'".fm_phone_assemble("inscofax")."',   ".
         "'".addslashes($inscocontact)."',       ".
         "'".addslashes($inscoid)."',            ".
         "'".addslashes($inscowebsite)."',       ".
         "'".addslashes($inscoemail)."',         ".
         "'".addslashes($inscogroup)."',         ".
         "'".addslashes($inscotype)."',          ".
         "'".addslashes($inscoassign)."',        ".
         "'".addslashes(fm_join_from_array($inscomod))."', ".
         " NULL ) ";
       break; // end add/addform

      case "mod": case "modform":
       $inscodtmod = $cur_date; // set date modified to current

       $query = "UPDATE $db_name SET ".
        "inscodtmod   ='".addslashes($inscodtmod)."',   ".
        "insconame    ='".addslashes($insconame)."',    ".
        "inscoalias   ='".addslashes($inscoalias)."',   ".
        "inscoaddr1   ='".addslashes($inscoaddr1)."',   ".
        "inscoaddr2   ='".addslashes($inscoaddr2)."',   ".
        "inscocity    ='".addslashes($inscocity)."',    ".
        "inscostate   ='".addslashes($inscostate)."',   ".
        "inscozip     ='".addslashes($inscozip)."',     ".
        "inscophone   ='".addslashes($inscophone)."',   ".
        "inscofax     ='".addslashes($inscofax)."',     ".
        "inscocontact ='".addslashes($inscocontact)."', ".
        "inscoid      ='".addslashes($inscoid)."',      ".
        "inscowebsite ='".addslashes($inscowebsite)."', ".
        "inscoemail   ='".addslashes($inscoemail)."',   ".
        "inscogroup   ='".addslashes($inscogroup)."',   ".
        "inscotype    ='".addslashes($inscotype)."',    ".
        "inscoassign  ='".addslashes($inscoassign)."',  ".
        "inscomod     ='".addslashes(fm_join_from_array($inscomod))."'  ". 
        "WHERE id='$id'";
       break; // end mod/modform
    } // end interior action switch

    // common add/modify code
    echo "
     <CENTER><$STDFONT_B>".( (($action=="mod") OR ($action=="modform")) ?
       _("Modifying") : _("Adding") )." ... ";
    $result = fdb_query ($query);
    echo ( ($result) ? _("done") : _("ERROR") )."
     <$STDFONT_E>
     <P>
     <$STDFONT_B>
      ???
     <$STDFONT_E>
     </CENTER>
    ";
  } // end checking if book is done
  freemed_display_box_bottom();
  break; // end addform/modform

 case "del":
  freemed_display_box_top(_("Deleting")." $record_name", $page_name);

  $result = fdb_query("DELETE FROM $db_name WHERE id = '".prepare($id)."'");

  echo "
    <P>
    <I>Insurance Company $id deleted</I>.
  ";
  echo "
    <BR><BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >Delete Another</A></CENTER>
  ";

  echo "
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\">Return
     to the Insurance Company Menu</A>
    </CENTER>
  ";

  freemed_display_box_bottom ();

 case "show":
  // pull up an insurance company record (for view or
  // printout, not modification)

  freemed_display_box_top("$record_name "._("Details"), $page_name);

  if (empty($id)) {
    echo "

     <CENTER>
      <B>You must specify an id #!</B>
      <BR><BR>
      <A HREF=\"$page_name?$_auth&action=view\"
       >Return to the Insurance Company Menu</A>
     </CENTER>

     <BR><BR>
    ";

    freemed_display_box_bottom ();
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >"._("Return to the Main Menu")."</A>
      </CENTER>
    ";
    DIE("");
  }

  $r = freemed_get_link_rec ($id, $db_name);
  extract ($r);

  echo "
    <P>

    <$STDFONT_B>Date added : <$STDFONT_E>
    $inscodtadd
    <BR>

    <$STDFONT_B>Date last modified : <$STDFONT_E>
    $inscodtmod
    <BR>

    <P>
    <B> NOT DONE YET! </B>
    <P>

  ";

  echo "
    <BR>
    <CENTER>
     <A HREF=\"$page_name?$_auth&action=view\"
      >Return to Insurance Company Menu</A>
    </CENTER>
  "; // abandon modification

  freemed_display_box_bottom ();
  break; // end of "show"
  
 default: // default action
  freemed_display_box_top(_("Insurance Companies"));
  echo freemed_display_itemlist(
    fdb_query("SELECT * FROM $db_name ORDER BY insconame"),
    $page_name,
    array (
     _("Name")	=>	"insconame",
     _("City")	=>	"inscocity",
     _("State")	=>	"inscostate",
     _("Group")	=>	"inscogroup"
    ),
    array ("", "", ""),
    "", "", "",
    ITEMLIST_MOD|ITEMLIST_VIEW
  );
  freemed_display_box_bottom ();
  break; // end of default action
} // end of master case action statement

freemed_close_db (); // close the db
freemed_display_html_bottom ();

?>
