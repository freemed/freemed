<?php
 // file: cptmod.php3
 // note: cpt modifier functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

  $page_name     = "cptmod.php3";   // for help info, later
  $record_name   = "CPT Modifiers"; // actual name of the record (english)
  $db_name       = "cptmod";
  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

switch ($action) {
 case "add":
  freemed_display_box_top(_("Adding")." "._($record_name));

  echo "
    <P>
    <$STDFONT_B>"._("Adding")." ... 
  ";

  $query = "INSERT INTO cptmod VALUES ( ".
    "'$cptmod', '$cptmoddescrip', NULL ) ";

  $result = fdb_query($query);

  if ($result) echo "<B>"._("done").".</B><$STDFONT_E>\n";
   else echo "<B>"._("ERROR")." ($result)</B>\n"; 

  echo "
   <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
   <P>
  ";

  freemed_display_box_bottom ();
  break; // end action add

 case "addform": case "modform":
  switch ($action) { // inner switch
    case "addform":
     break;

    case "modform":
     if ($id<1) die ("NO ID");
     $r = freemed_get_link_rec ($id, $db_name);
     extract ($r);
     break;
  } // end inner switch
  freemed_display_box_top( ( ($action=="addform") ? _("Add") : _("Modify") ).
   " "._($record_name));

  echo "
    <P>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
      ( ($action=="addform") ? "add" : "mod" )."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >
   ".form_table ( array (
    _("Modifier") =>
    "<INPUT TYPE=TEXT NAME=\"cptmod\" SIZE=3 MAXLENGTH=2
     VALUE=\"".prepare($cptmod)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"cptmoddescrip\" SIZE=20 MAXLENGTH=30
     VALUE=\"".prepare($cptmoddescrip)."\">"
   ) )."
    <P>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".
      ( ($action=="addform") ? _("Add") : _("Modify") )." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom ();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("Abandon ".
       ( ($action=="addform") ? "Addition" : "Modification" ))."</A>
    </CENTER>
  ";
  break; // end add/mod form

 case "mod":

  freemed_display_box_top (_("Modifying")." "._($record_name));

  echo "
    <P>
    <$STDFONT_B>"._("Modifying")." ... 
  ";

  $query = "UPDATE cptmod SET ".
    "cptmod        = '".addslashes($cptmod)."',       ".
    "cptmoddescrip = '".addslashes($cptmoddescrip)."' ". 
    "WHERE id='".addslashes($id)."'";

  $result = fdb_query($query);

  if ($result) echo "<B>"._("done").".</B><$STDFONT_E>\n";
   else echo "<B>"._("ERROR")." ($result)</B>\n"; 

  echo "
   <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
   <P>
  ";

  freemed_display_box_bottom ();
  break; // end mod action

 case "del": case "delete":
  freemed_display_box_top (_("Deleting")." "._($record_name));

  $result = fdb_query("DELETE FROM cptmod WHERE (id = '".addslashes($id)."')");
  
  echo "
    <P>
    <I>"._($record_name)." <B>$id</B> "._("Deleted")."<I>.
    <P>
  ";
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end del/delete action

 default: // default action

  $query = "SELECT * FROM cptmod ORDER BY cptmod, cptmoddescrip";

  freemed_display_box_top (_($record_name));
  echo freemed_display_itemlist (
    fdb_query ("SELECT cptmod,cptmoddescrip,id FROM cptmod
                ORDER BY cptmod,cptmoddescrip"),
    _($record_name),
    array (
	_("Modifier")		=>	"cptmod",
	_("Description")	=>	"cptmoddescrip"
    ),
    array ("", _("NO DESCRIPTION"))
  );
  freemed_display_box_bottom ();
  break; // end of default action
} // end of master case statement

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
