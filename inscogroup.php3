<?php
 // file: inscogroup.php3
 // note: insurance company group(s) functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

  $page_name="inscogroup.php3"; // for help info, later
  $db_name="inscogroup";
  $record_name="Insurance Company Groups";
  include ("global.var.inc");
  include ("freemed-functions.inc");

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top();

switch ($action) {
 case "add":

  freemed_display_box_top(_("Adding")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Adding")." ... 
  ";

  $query = "INSERT INTO inscogroup VALUES ( ".
    "'".addslashes($inscogroup)."',   NULL ) ";

  $result = fdb_query($query);

  if ($result) 
    echo "<B>"._("done").".</B><$STDFONT_E>\n";
   else echo "<B>"._("ERROR")." ($result)</B>\n"; 

  echo "
    </CENTER>
    <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
    </CENTER>
    <P>
  ";

  freemed_display_box_bottom();
  break; // end add action
  
 case "modform":
  freemed_display_box_top (_("Modify")." "._($record_name));

  $r = freemed_get_link_rec ($id, $db_name);
  extract ($r);

  echo "
    <BR><BR>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    <CENTER>
    <$STDFONT_B>"._("Name")." : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=\"inscogroup\" SIZE=20 MAXLENGTH=20
     VALUE=\"$inscogroup\">
    </CENTER>
    <P>

    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
  ";
  freemed_display_box_bottom();

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
  break; // end modform action

 case "mod":
  freemed_display_box_top (_("Modifying")." "._($record_name));

  echo "
    <P><CENTER>
    <$STDFONT_B>"._("Modifying")." ... 
  ";

  $query = "UPDATE inscogroup SET ".
    "inscogroup = '".addslashes($inscogroup)."' ". 
    "WHERE id='".addslashes($id)."'";

  $result = fdb_query($query);

  if ($result) 
    echo "<B>"._("done").".</B><$STDFONT_E>\n";
   else echo "<B>"._("ERROR")." ($result)</B>\n"; 

  echo "
    <P>
    <A HREF=\"$page_name?$_auth\">"._("back")."</A>
    </CENTER>
  ";

  freemed_display_box_bottom ();
  break; // end mod action
  
 case "del": case "delete":
  freemed_display_box_top (_("Deleting")." "._($record_name));

  $result = fdb_query("DELETE FROM inscogroup
    WHERE (id = '".addslashes($id)."')");

  echo "
    <P>
    <I>"._($record_name)." <B>$id</B> "._("Deleted")."<I>.
    <BR>
  ";
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >"._("back")."</A></CENTER>
  ";
  freemed_display_box_bottom ();
  break; // end del action
 
 default:
  $query = "SELECT * FROM inscogroup ".
   "ORDER BY inscogroup";

  freemed_display_box_top (_($record_name));

  echo freemed_display_itemlist (
    fdb_query ("SELECT inscogroup,id FROM inscogroup ORDER BY inscogroup"),
    $page_name,
    array (
	_($record_name)		=>	"inscogroup"
    ),
    array (
	""
    )
  );
  
  $_alternate = freemed_bar_alternate_color ($_alternate);

  echo "
    <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=3 BORDER=0>
     <TR BGCOLOR=$_alternate VALIGN=CENTER>
      <TD VALIGN=CENTER><FORM ACTION=\"$page_name\" METHOD=POST
       ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
       <INPUT NAME=\"inscogroup\" LENGTH=20 MAXLENGTH=30></TD>
      <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\""._("ADD")."\"></FORM></TD>
     </TR></TABLE>
   ";

  freemed_display_box_bottom (); 
  break; // end of default action
} // end of master case statement

freemed_close_db();
freemed_display_html_bottom ();

?>
