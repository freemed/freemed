<?php
 // file: facility.php3
 // note: facility database functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // small mods by max k <amk@span.ch>
 // lic : GPL, v2

  $page_name  ="facility.php3";
  $record_name="Facility";
  $db_name    ="facility";

  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie);
  freemed_display_html_top ();
  freemed_display_banner ();

switch ($action) { // master action switch
 case "addform": case "add":
 case "modform": case "mod":
  // for either add or modify form
  if (!$been_here) {
   switch ($action) { // internal case 
    case "addform": // internal addform
     $next_action = "add";
     break; // end internal addform
    case "modform": // internal modform
     $next_action = "mod";
     if (strlen($id)<1) {
       echo "
        <B><CENTER>Please use the MODIFY form to MODIFY a code!</B>
        </CENTER>
        <P>
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
     break;
   } // end internal case
   $been_here = 1;
  } // end if not been here

  freemed_display_box_top (( ($action=="addform") ? _("Add") : _("Modify") ).
   " "._($record_name));

  $book = new notebook (array ("action", "been_here", "id"),
    NOTEBOOK_STRETCH|NOTEBOOK_COMMON_BAR);

  $book->add_page (_("Primary Information"),
    array (
	"psrname", "psraddr1", "psraddr2",
	"psrcity", "psrstate", "psrzip", "psrcountry"
    ),
    form_table ( array (
      _("Facility Name") =>
      "<INPUT TYPE=TEXT NAME=\"psrname\" SIZE=20 MAXLENGTH=25
      VALUE=\"".prepare($psrname)."\">",

      _("Address (Line 1)") =>
      "<INPUT TYPE=TEXT NAME=\"psraddr1\" SIZE=20 MAXLENGTH=25
       VALUE=\"".prepare($psraddr1)."\">",

      _("Address (Line 2)") =>
      "<INPUT TYPE=TEXT NAME=\"psraddr2\" SIZE=20 MAXLENGTH=25
       VALUE=\"".prepare($psraddr2)."\">",

      _("City, State, Zip") =>
      "<INPUT TYPE=TEXT NAME=\"psrcity\" SIZE=10 MAXLENGTH=15
       VALUE=\"".prepare($psrcity)."\">
      <INPUT TYPE=TEXT NAME=\"psrstate\" SIZE=4 MAXLENGTH=3
       VALUE=\"".prepare($psrstate)."\">
      <INPUT TYPE=TEXT NAME=\"psrzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"".prepare($psrzip)."\">",

      _("Country") =>
      country_pulldown("psrcountry")
     ) )
    );

    $book->add_page (
      _("Contact"),
      array (
        phone_vars("psrphone"), phone_vars("psrfax"), "psremail"
      ),
      form_table ( array (
        _("Phone Number") =>
        fm_phone_entry ("psrphone"),

        _("Fax Number") =>
        fm_phone_entry ("psrfax"),

        _("Email Address") =>
        "<INPUT TYPE=TEXT NAME=\"psremail\" SIZE=25 MAXLENGTH=25
          VALUE=\"".prepare($psremail)."\">"

      ) )
    );

    $book->add_page (
      _("Details"),
      array (
        "psrnote", "psrdefphy", "psrein", "psrintext"
      ),
      form_table ( array (
        _("Description") =>
        "<INPUT TYPE=TEXT NAME=\"psrnote\" SIZE=20 MAXLENGTH=40
         VALUE=\"".prepare($psrnote)."\">",

        _("Default Provider") =>
	freemed_display_selectbox (
          fdb_query("SELECT * FROM physician ORDER BY phylname,phyfname"),
	  "#phylname#, #phyfname#",
	  "psrdefphy" 
	),
	
        _("Employer Identification Number") =>
        "<INPUT TYPE=TEXT NAME=\"psrein\" SIZE=10 MAXLENGTH=9
         VALUE=\"".prepare($psrein)."\">",

        _("Internal or External Facility") =>
        "<SELECT NAME=\"psrintext\">
         <OPTION VALUE=\"0\" ".
          ( ($psrintext == 0) ? "SELECTED" : "" ).">"._("Internal")."
         <OPTION VALUE=\"1\" ".
          ( ($psrintext == 1) ? "SELECTED" : "" ).">"._("External")."
        </SELECT>"

      ) )
    );

  if (!$book->is_done()) {
    echo $book->display();
    echo "
       <P>
       <CENTER>
       <A HREF=\"$page_name?$_auth\"
        >"._("Abandon ".( ($action=="addform") ? "Addition" : "Modification" ))
         ."</A>
       </CENTER>
    ";
  } else {
    switch ($action) { // internal action switch
     case "addform": case "add":
      echo "
        <P><CENTER>
        <$STDFONT_B>"._("Adding")." ... <$STDFONT_E>
      ";
      $query = "INSERT INTO facility VALUES (
        '".addslashes($psrname).         "',
        '".addslashes($psraddr1).        "',
        '".addslashes($psraddr2).        "',
        '".addslashes($psrcity).         "',
        '".addslashes($psrstate).        "',
        '".addslashes($psrzip).          "',
        '".addslashes($psrcountry).      "',
        '".addslashes($psrnote).         "',
        '".addslashes($cur_date).        "', 
        '".addslashes($psrdefphy).       "',
        '".fm_phone_assemble("psrphone")."',
        '".fm_phone_assemble("psrfax").  "',
        '".addslashes($psremail).        "',
        '".addslashes($psrein).          "',
        '".addslashes($psrintext).       "',
         NULL ) ";

      $result = fdb_query($query);

      if ($result) echo "<B>"._("done").".</B>\n";
         else      echo "<B>"._("ERROR")."</B>\n";

      echo "
       </CENTER>
       <P>
       <CENTER>
        <A HREF=\"$page_name?$_auth\"
        ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
       </CENTER>
       <P>
      ";
      break; // end addform

     case "modform": case "mod":
      echo "
        <P><CENTER>
        <$STDFONT_B>"._("Modifying")." ... 
      ";

      $query = "UPDATE $db_name SET 
        psrname     ='".addslashes($psrname).         "',
        psraddr1    ='".addslashes($psraddr1).        "',
        psraddr2    ='".addslashes($psraddr2).        "',
        psrcity     ='".addslashes($psrcity).         "',
        psrstate    ='".addslashes($psrstate).        "',
        psrzip      ='".addslashes($psrzip).          "',
        psrcountry  ='".addslashes($psrcountry).      "',
        psrnote     ='".addslashes($psrnote).         "',
        psrdefphy   ='".addslashes($psrdefphy).       "',
        psrphone    ='".fm_phone_assemble("psrphone")."',
        psrfax      ='".fm_phone_assemble("psrfax").  "',
        psremail    ='".addslashes($psremail).        "', 
        psrein      ='".addslashes($psrein).          "',
        psrintext   ='".addslashes($psrintext).       "' 
        WHERE id='".addslashes($id)."'";

      $result = fdb_query($query);

      if ($result) echo "<B>"._("done").".</B>\n";
       else        echo "<B>"._("ERROR")."</B>\n";

      echo "
       </CENTER>
       <P>
       <CENTER>
        <A HREF=\"$page_name?$_auth&action=view\"
        ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
       </CENTER>
       <P>
      ";
      break; // end modform
    } // end of internal action switch
  } // end checking if book is displayed

  freemed_display_box_bottom ();
  break; // end of addform/modform action

 case "del": // delete action
  freemed_display_box_top (_("Deleting")." "._($record_name));

  $result = fdb_query(
    "DELETE FROM facility WHERE (id = '".addslashes($id)."')");

  echo "
    <P>
    <I>"._($record_name)." <B>$id</B> "._("Deleted")."<I>.
  ";
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end of delete action

 default: // default action
  freemed_display_box_top (_($record_name));
  echo freemed_display_itemlist (
    fdb_query ("SELECT * FROM $db_name ORDER BY psrname,psrnote"),
    $page_name,
    array (
	_("Name")		=>	"psrname",
	_("Description")	=>	"psrnote"
    ),
    array ("", _("NO DESCRIPTION"))
  );
  freemed_display_box_bottom ();
  break; // end of default action
} // end of case statement  

freemed_close_db();
freemed_display_html_bottom ();

?>
