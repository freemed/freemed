<?php
 // file: freemed-functions.inc
 // note: complete list of included functions for all modules
 //       basically to cut down on includes, and make everything
 //       a little easier
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       max k <amk@span.ch>
 //       adam (gdrago23@yahoo.com)
 // lic : GPL, v2
 // ver : 2000-07-25

if (!defined(__FREEMED_FUNCTIONS_INC__)) {

define (__FREEMED_FUNCTIONS_INC__, true);

// function freemed_bar_alternate_color
function freemed_bar_alternate_color ($cur_color="")
{
  global $bar_start_color, $bar_alt_color;

  switch ($cur_color) {
    case $bar_start_color:
      return $bar_alt_color;
      break;
    case $bar_alt_color:
    case "":
    default:
      return $bar_start_color;
      break;
  } // end color decision switch
} // end function freemed_bar_alternate_color

// function freemed_check_access_for_facility
function freemed_check_access_for_facility ($f_cookie, $facility_number)
{
  $f_auth = explode (":", $f_cookie);  // separate auth data
  if ($f_auth[0]==1) return true;      // root has all access...
  $f_fac = freemed_get_link_field ($f_auth[0], "user", "userfac");
  if ($facility_number == 0) return true; // 19990924 temp patch
  if ((fm_value_in_string($f_fac, "-1")) OR
      (fm_value_in_string($f_fac, $facility_number)))
    return true;                       // if all or present, return true
  return false;                        // if not, return false
} // end function freemed_check_access_for_facility

// function freemed_check_access_for_patient
function freemed_check_access_for_patient ($f_cookie, $patient_number)
{
  $f_auth = explode (":", $f_cookie);  // separate auth data
  if ($f_auth[0]==1) return true;      // root has all access...
  $f_user   = freemed_get_link_rec ($f_auth[0], "user");
    // get data records in question for the user
  $f_fac    = $f_user ["userfac"   ];
  $f_phy    = $f_user ["userphy"   ];
  $f_phygrp = $f_user ["userphygrp"];

    // retrieve patient record
  $f_pat    = freemed_get_link_rec ($patient_number, "patient");

    // check for universal access
  if ((fm_value_in_string ($f_fac,    "-1")) OR
      (fm_value_in_string ($f_phy,    "-1")) OR
      (fm_value_in_string ($f_phygrp, "-1")))
    return true;

    // check for physician in any physician fields
  if (($f_pat["ptpcp"]>0) AND
      (fm_value_in_string ($f_phy, $f_pat["ptpcp"])))
    return true;
  if (($f_pat["ptphy1"]>0) AND
      (fm_value_in_string ($f_phy, $f_pat["ptphy1"])))
    return true;
  if (($f_pat["ptphy2"]>0) AND
      (fm_value_in_string ($f_phy, $f_pat["ptphy2"])))
    return true;
  if (($f_pat["ptphy3"]>0) AND
      (fm_value_in_string ($f_phy, $f_pat["ptphy3"])))
    return true;
  if (($f_pat["ptdoc"]>0) AND
      (fm_value_in_string ($f_phy, $f_pat["ptdoc"])))
    return true;

  return false; // if all else fails, return false

} // end function freemed_check_access_for_patient

// function freemed_close_db
function freemed_close_db ($_null=" ")
{
  fdb_close(); // close the link
} // end function freemed_close_db

// function freemed_config_value
function freemed_config_value ($config_var)
{
  $query = fdb_query("SELECT * FROM config
    WHERE (c_option='$config_var')");

  if ($query < 1) return ""; // fix for config db not being there...

  $_temp_r = fdb_fetch_array($query);

  return $_temp_r["c_value"]; // return value
} // end function freemed_config_value

// function freemed_display_banner (page description)
function freemed_display_banner ($_pg_desc="")
{
  global $STDFONT_B, $STDFONT_E, $bugs_address,
         $show_top_links;

  echo " 
    <CENTER>
      <B><$STDFONT_B SIZE=+2>
        --= ".prepare(PACKAGENAME)." v".prepare(VERSION)." $_pg_desc =--
      <$STDFONT_E></B><BR>
      <$STDFONT_B>".fm_prep(INSTALLATION)."<$STDFONT_E>
  ";
  if ($show_top_links) echo "
      <BR>
      <$STDFONT_B SIZE=-1>
       <I>homepage : <A HREF=\"http://www.freemed.org\"
        >http://www.freemed.org</A></I><BR>
       <I>bugs : <A HREF=\"$bugs_address\"
        >$bugs_address</A></I>
      <$STDFONT_E>
    ";
  echo "
    </CENTER>
    <P>
  ";
} // end function freemed_display_banner

// function freemed_display_bottom_links
function freemed_display_bottom_links ($phy_name="", $pg_name="", $ref="")
{
  global $Return_to_the_Main_Menu, $Return_to_Previous_Menu, $_auth,
   $page_name, $record_name, $_ref;

  // 19990924 -- patch to eliminate neccesity of parameters
  if ($ref=="")      $ref      = $_ref;
  if ($phy_name=="") $phy_name = $record_name;
  if ($pg_name=="")  $pg_name  = $page_name;

  if ((strlen($ref)<5) OR ($ref=="main.php3")
                       OR ($ref==$pg_name)) {
    echo "
      <P>
      <CENTER><A HREF=\"main.php3?$_auth&action=view\"
       ><FONT FACE=\"Arial, Helvetica, Verdana\"
       >$Return_to_the_Main_Menu</FONT></A>
      </CENTER>
    "; 
  } else {
    echo "
      <P>
      <CENTER><A HREF=\"$ref?$_auth\"
       ><FONT FACE=\"Arial, Helvetica, Verdana\"
       >$Return_to_Previous_Menu</FONT></A>
      </CENTER>
      <P>
      <CENTER><A HREF=\"main.php3?$_auth\"
       ><FONT FACE=\"Arial, Helvetica, Verdana\"
       >$Return_to_the_Main_Menu</FONT></A>
      </CENTER>
    ";
  } // end master if/else loop
} // end function freemed_display_bottom_links

// function freemed_display_box_bottom
function freemed_display_box_bottom ($_null="")
{
  global $debug, $DEBUG_IS_ON;

  if ($debug) {
    echo "
      
      <TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=0
       BGCOLOR=#ff0000 VALIGN=BOTTOM ALIGN=CENTER>
      <TR><TD BGCOLOR=#ff0000>
      <CENTER><B><FONT SIZE=-2 COLOR=#ffffff>$DEBUG_IS_ON</FONT>
      </B></CENTER>
      </TD></TR></TABLE>

    ";
  } // end of showing debug status if on

  echo "
    </TD></TR></TABLE>
    </TD></TR></TABLE>
    </CENTER>
  ";
} // end function freemed_display_box_bottom

// function freemed_display_box_top
function freemed_display_box_top ($box_title="", $ref="", $pg_name="")
{
  global $language, $topbar_color, $lang_back, $lang_main, $lang_exit,
   $_auth, $page_name, $action, $id, $patient, $_pg_desc, $_ref,
   $admin_level, $delete_level, $export_level, $database_level,
   $menubar_color, $LoginCookie, $STDFONT_B, $STDFONT_E, $current_patient;

  if ($ref=="") $ref = $_ref;  // pass from cookie?

  // determine if we are dealing with a physician...
  $is_physician = false;   // start out assuming false
  if (!strpos($LoginCookie, ":")) {
    $_user = explode (":", $LoginCookie);
    $user = $_user[0];
    if ($user>1) {
     if (freemed_get_link_field($user, "user", "usertype")=="phy")
      $is_physician = true;
     if ($is_physician)
      $physician_number = freemed_get_link_field ($user, "user", "userrealphy");
    } // end if $user > 1 (not 0, because root is 1)
  } // end checking for bad logincookie

  // speed hack
  if ((strpos($LoginCookie, ":")) and
      ($page_name != "index.php3") and
      ($page_name != "authenticate.php3") and
      ($page_name != "logout.php3")) 
    $this_userlevel = freemed_get_userlevel ($LoginCookie);

  if (strlen($ref)<1) {
    $ref = "main.php3";
  } // if there is no ref

  if ($_pg_desc=="[HELP]") $width = "WIDTH=75%";

  if ($page_name=="index.php3") { $b_b = "<B>"; $b_e = "</B>"; }

  echo "
    <CENTER>
    <TABLE BGCOLOR=#000000 CELLSPACING=0 CELLPADDING=2 ALIGN=CENTER
     BORDER=0 VALIGN=MIDDLE $width><TR><TD BGCOLOR=#000000>

    <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 ALIGN=CENTER
     VALIGN=CENTER BGCOLOR=#dddddd WIDTH=100%><TR><TD>
  ";

  if (strlen($box_title)>0) {
    echo "
      <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=0 ALIGN=CENTER
       VALIGN=TOP BORDER=0><TR BGCOLOR=$topbar_color>
       <TD BGCOLOR=$topbar_color>
       <CENTER><FONT FACE=\"Arial, Helvetica, Verdana\"
        SIZE=+1 COLOR=#ffffff>$b_b$box_title$b_e</FONT></CENTER>
      </TD>
      <TD BGCOLOR=$topbar_color ALIGN=RIGHT WIDTH=32 
    ";

    if (($_ref!="index.php3") AND ($_ref != $pg_name)) {
      echo "
         ><A HREF=\"$ref?$_auth\"
         ><IMG SRC=\"img/back-widget.gif\" HEIGHT=16 BORDER=0
         WIDTH=16 ALT=\"[$lang_back]\"></A
      ";
   } elseif  (($_ref == $pg_name) AND
             ($pg_name != "main.php3")) { 
      // stupid ref patch, 19990701
      echo "
         ><A HREF=\"main.php3?$_auth\"
         ><IMG SRC=\"img/back-widget.gif\" HEIGHT=16 BORDER=0
         WIDTH=16 ALT=\"[$lang_main]\"></A
      ";
   }
   echo "
      ><A HREF=\"logout.php3?_URL=".
       urlencode(getenv("REQUEST_URI")).
      "\"><IMG SRC=\"img/close-widget.gif\" HEIGHT=16 BORDER=0
      WIDTH=16 ALT=\"[$lang_exit]\"></A>
      </TD></TR></TABLE>
    ";
  } // if there is a box title
  //if ($debug) echo "_ref = $_ref, pg_name = $pg_name<BR>\n";
  if (($page_name != "index.php3") and
      ($page_name != "authenticate.php3") and
      ($page_name != "logout.php3") and
      ($page_name != "main.php3") and
      ($_pg_desc != "[HELP]")) {
    // display top of table
    echo "
     <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=0 BORDER=0
      VALIGN=TOP ALIGN=CENTER BGCOLOR=$menubar_color>
     <TR BGCOLOR=$menubar_color>
    ";
    if ($current_patient>0) { 
      $p_link = "manage.php3?$_auth&id=$current_patient";
    } else {
      $p_link = "patient.php3?$_auth";
    } // end creating patient link

    if (freemed_config_value ("gfx")=="1") {
     if ($this_userlevel>$admin_level)
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=LEFT>
        <A HREF=\"admin.php3?$_auth\"
        ><IMG SRC=\"img/KeysOnChain-mini.gif\"
        WIDTH=24 HEIGHT=24 BORDER=0 ALT=\"[Admin Menu]\"></A>
       </TD>
      ";
     if ($this_userlevel>$database_level)
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=LEFT>
        <A HREF=\"billing_functions.php3?$_auth&patient=$current_patient\"
        ><IMG SRC=\"img/CashRegister-mini.gif\"
        WIDTH=24 HEIGHT=24 BORDER=0 ALT=\"[Billing]\"></A>
       </TD>
      ";
     // no if statement needed for callins...
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=LEFT>
        <A HREF=\"call-in.php3?$_auth\"
        ><IMG SRC=\"img/Text-mini.gif\"
        WIDTH=24 HEIGHT=24 BORDER=0 ALT=\"[Call-In Menu]\"></A>
       </TD>
      ";
     if ($this_userlevel>$database_level)
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=LEFT>
        <A HREF=\"db_maintenance.php3?$_auth\"
        ><IMG SRC=\"img/Database-mini.gif\"
        WIDTH=24 HEIGHT=24 BORDER=0 ALT=\"[Database]\"></A>
       </TD>
      ";
     if ($is_physician)
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=LEFT>
        <A HREF=\"physician_day_view.php3?$_auth&physician=$physician_number\"
        ><IMG SRC=\"img/karm-mini.gif\"
        WIDTH=24 HEIGHT=24 BORDER=0 ALT=\"[Day View]\"></A>
       </TD>
      ";
     if ($this_userlevel>$database_level)
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=LEFT>
        <A HREF=\"$p_link\"
        ><IMG SRC=\"img/HandOpen-mini.gif\"
        WIDTH=24 HEIGHT=24 BORDER=0 ALT=\"[Patients]\"></A>
       </TD>
      ";
     // nothing else needed for help, either
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=LEFT>
        <A HREF=\"help.php3?$_auth&page_name=$page_name\"
         TARGET=\"__HELP__\"><IMG SRC=\"img/readme-mini.gif\"
        WIDTH=24 HEIGHT=24 BORDER=0 ALT=\"[Help]\"></A>
       </TD>
      ";
     // nothing needed for return to main menu...
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=RIGHT>
        <A HREF=\"main.php3?$_auth\"
        ><IMG SRC=\"img/HandPointingLeft-mini.gif\"
        WIDTH=24 HEIGHT=24 BORDER=0 ALT=\"[Main Menu]\"></A>
       </TD>
      ";
    } else { // if graphics are disabled...
      if ($this_userlevel>$admin_level)
       echo "
        <TD BGCOLOR=$menubar_color ALIGN=LEFT>
         <A HREF=\"admin.php3?$_auth\"
         ><$STDFONT_B SIZE=-1>Admin<$STDFONT_E></A>
        </TD>
        "; // end admin
      if ($this_userlevel>$database_level)
       echo "
        <TD BGCOLOR=$menubar_color ALIGN=LEFT>
         <A HREF=\"billing_functions.php3?$_auth&patient=$current_patient\"
         ><$STDFONT_B SIZE=-1>Bill<$STDFONT_E></A>
        </TD>
       ";
      // no special things needed for callins
       echo "
        <TD BGCOLOR=$menubar_color ALIGN=LEFT>
         <A HREF=\"call-in.php3?$_auth\"
         ><$STDFONT_B SIZE=-1>Callin<$STDFONT_E></A>
        </TD>
        ";
      if ($this_userlevel>$database_level)
       echo "
        <TD BGCOLOR=$menubar_color ALIGN=LEFT>
         <A HREF=\"db_maintenance.php3?$_auth\"
         ><$STDFONT_B SIZE=-1>DB<$STDFONT_E></A>
        </TD>
        "; // end db
      if ($this_userlevel>$database_level)
       echo "
        <TD BGCOLOR=$menubar_color ALIGN=LEFT>
         <A HREF=\"$p_link\"
         ><$STDFONT_B SIZE=-1>Patient<$STDFONT_E></A>
        </TD>
        ";
      // no special conditions for help
       echo "
        <TD BGCOLOR=$menubar_color ALIGN=LEFT>
         <A HREF=\"help.php3?$_auth&page_name=$page_name\"
         ><$STDFONT_B SIZE=-1>?<$STDFONT_E></A>
        </TD>
        ";
      echo "
       <TD BGCOLOR=$menubar_color ALIGN=RIGHT>
        <A HREF=\"main.php3?$_auth\"
        ><$STDFONT_B SIZE=-1>Main<$STDFONT_E></A>
        </TD>
       ";
    } // end checking for graphics enabled/disabled
    // display bottom of table (for graphics at box top)
    echo "
      </TR>
     </TABLE>
    ";
  } // end if page (is acceptable for menubar)
} // end function freemed_display_box_top

// function freemed_display_actionbar
function freemed_display_actionbar ($this_page_name="", $__ref="") {
  global $lang_ADD, $RETURN_TO_MENU, $page_name, $patient, $_ref, $_auth;

  $buffer = "";

  if (!empty($_ref)) $__ref = $_ref;

  if ($this_page_name=="") $this_page_name = $page_name;

  if (!empty($__ref)) {
    $_ref="main.php3";
  } // if no ref, then return to home page...

    // show the actual bar, build with page_name reference
    // and global variables
  $buffer .= "
    <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
     CELLSPACING=0 CELLPADDING=3>
    <TR BGCOLOR=#000000>
    <TD ALIGN=LEFT><A HREF=\"$this_page_name?$_auth&action=addform".
     ( !empty($patient) ? "&patient=$patient" : "" )
     ."\"><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
     SIZE=-1><B>$lang_ADD</B></FONT></A></TD>
    <TD WIDTH=30%>&nbsp;</TD>
    <TD ALIGN=RIGHT><A HREF=\"$__ref?$_auth\"
     ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
     SIZE=-1><B>$RETURN_TO_MENU</B></FONT></A></TD>
    </TR></TABLE>
  ";

  return $buffer;

} // end function freemed_display_actionbar

// function freemed_display_cptcodes
//   display <SELECT>-type list of CPT codes
function freemed_display_cptcodes ($param)
{
  global $NONE_SELECTED, $SELECTED;

  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $result = fdb_query ("SELECT * FROM cpt
    ORDER BY cptcode, cptnameint");
  if (!$result) {
    // don't do anything...
  } else { // 
    while ($row=fdb_fetch_array($result)) {
      $cpt_name = $row["cptnameint"];
      $cpt_code = $row["cptcode"   ];
      $cpt_id   = $row["id"];

      echo "
        <OPTION VALUE=\"$cpt_id\" ".
         ( ($param==$row[id]) ? "SELECTED" : "" ) .
         ">$cpt_code ($cpt_name) ".
         ( $debug ? "[$cpt_id]" : "" )."\n";
    } // while there are more results...
  } // end master loop
} // end function freemed_display_cptcodes

// function freemed_display_cptmods
//   display <SELECT>-type list of CPT modifiers
function freemed_display_cptmods ($param)
{
  global $NONE_SELECTED;

  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $result = fdb_query ("SELECT * FROM cptmod
    ORDER BY cptmod, cptmoddescrip");
  if (!$result) {
    // don't do anything...
  } else { // 
    while ($row=fdb_fetch_array($result)) {
      $cpt_mod  = $row["cptmod"       ];
      $cpt_desc = $row["cptmoddescrip"];
      $cpt_id   = $row["id"];

      if ($debug) 
        $debug_var = "[$cpt_id]";
      else $debug_var = "";

      echo "
        <OPTION VALUE=\"$cpt_id\" ".
	 ( ($param == $row[id]) ? "SELECTED" : "" ).
         ".>$cpt_mod ($cpt_desc) $debug_var
      "; // actually show it
    } // while there are more results...
  } // end master loop
} // end function freemed_display_cptmods

// function freemed_display_drugs
//   display <SELECT>-type list of drugs
function freemed_display_drugs ($param)
{
  global $NONE_SELECTED;

  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $result = fdb_query ("SELECT * FROM frmlry
    ORDER BY trdmrkname");
  if (!$result) {
    // don't do anything...
  } else { // 
    while ($row=fdb_fetch_array($result)) {
      $d_name = $row["trdmrkname"];
      $d_id   = $row["id"        ];

      if ($debug) 
        $debug_var = "[$d_id]";
      else $debug_var = "";

      echo "
        <OPTION VALUE=\"$d_id\" ".
	( ($param == $row[id]) ? "SELECTED" : "" ).
        ">$d_name $debug_var
      "; // actually show it
    } // while there are more results...
  } // end master loop
} // end function freemed_display_drugs

// function freemed_display_html_bottom
function freemed_display_html_bottom ($_null="")
{
  global $_mail_handler, $lang_coded_by;

  echo "
    <CENTER>
      <I><FONT SIZE=-2 COLOR=#555555>$lang_coded_by
        <A HREF=\"$_mail_handler".BUGS_EMAIL."\">".CODED_BY."</A>
      </FONT></I>
    </CENTER>
    </BODY>
    </HTML>
  ";
} // end function freemed_display_html_bottom

// function freemed_display_html_top
function freemed_display_html_top ($_refresh_location="", $_pg_desc_given="")
{
  global $__ISO_SET__, $_pg_desc;

  if ($_pg_desc_given=="") $_pg_desc_given = $_pg_desc;

  if (strlen($_refresh_location)>0) {
    Header("Location: $_refresh_location");
    $_refresh_location = ""; // set to null
  }

  echo "
   <HTML> 
   <HEAD>
     <META HTTP-EQUIV=\"Content-Type\"
      CONTENT=\"text/html; CHARSET=$__ISO_SET__\">
     <TITLE>".PACKAGENAME." v".VERSION." $_pg_desc_given
        - ".prepare(INSTALLATION)."</TITLE>
   </HEAD>
   <BODY BGCOLOR=#ffffff ALINK=#0000ff VLINK=0000ff>
  ";
} // end function freemed_display_html_top

// function freemed_display_itemlist
function freemed_display_itemlist ($result, $page_link, $control_list,  
                                   $blank_list, $xref_list="", $title="")
{
  global $_ref, $LoginCookie, $STDFONT_B, $STDFONT_E, $record_name;
  global $modify_level, $delete_level, $patient;

  if (strlen($title)<1) $title=$record_name;

  // TODO: make sure $control_list is an array, verify the inputs, yadda yadda

  $buffer="";

  $buffer .= "
    <!-- Begin itemlist Table -->
    <TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2 BORDER=0
     ALIGN=CENTER VALIGN=MIDDLE BGCOLOR=\"#777777\">
    <TR>
     <TD ALIGN=CENTER>
      <$STDFONT_B SIZE=+1 COLOR=\"#ffffff\">$title<$STDFONT_E>
     </TD>
    </TR>
    <TR><TD>
  ".
  freemed_display_actionbar()
  ."
    </TD></TR>
    <TR><TD>
  ";
  // end header

  $buffer .= "
    <TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2 BORDER=0
     ALIGN=CENTER VALIGN=MIDDLE>
    <TR>
  ";
  while (list($k,$v)=each($control_list)) {
    $buffer .= "
      <TD BGCOLOR=\"#000000\">
       <$STDFONT_B COLOR=\"#ffffff\">$k&nbsp;<$STDFONT_E>
      </TD>
    ";
  }
  $buffer .= "
      <TD BGCOLOR=\"#000000\">
       <$STDFONT_B COLOR=\"#ffffff\">Action<$STDFONT_E>
      </TD>
    </TR>
  ";
  
  if (fdb_num_rows($result)>0) 
   while ($this_result = fdb_fetch_array($result)) {
    $first = true; // first item in the list has 'view' link
    $buffer .= "
    <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
    ";
    reset($control_list); // it's already each'd the arrays, 
    if (is_array($xref_list)) 
      reset($xref_list);    // but we have to do it again for the next iteration
    $field_num=0;
    while (list($k,$v)=each($control_list)) {
      $is_xref=false;
      if (is_array($xref_list)) {
        reset($xref_list);
        $xref_k = $xref_v = "";
        for ($i=0;$i<=$field_num;$i++)
          list ($xref_k, $xref_v) = each($xref_list);
        // the proper item is now in $xref_{k,v}
        if (strlen($xref_v)>1) {
          $is_xref=true;
          $xref_item=freemed_get_link_field($this_result[$v],
                                                    $xref_k,$xref_v);
          $item_text = ( (strlen($xref_item)<1) ?
                         fm_prep($blank_list[$field_num]) :
                         fm_prep($xref_item) );
        }
      } // if there are any xrefs in the table
      if (!$is_xref) { // not an xref item 
        $item_text = ( (strlen($this_result[$v])<1)?
                       fm_prep($blank_list[$field_num])  :
                       fm_prep($this_result[$v]) ); 
      }
      if ($first) {
        $first = false;
        $buffer .= "
      <TD>
        <A HREF=\"$page_link?$_auth&patient=$patient&action=display&id=".
	"$this_result[id]\"><$STDFONT_B
	  >$item_text<$STDFONT_E></A>&nbsp;
      </TD>
        ";
      } else {
        $buffer .= "
      <TD>
        <$STDFONT_B>$item_text&nbsp;<$STDFONT_E>
      </TD>
        ";
      }
    $field_num++;
    } // while each data field
    
    $buffer .= "
      <TD>
    ";
    if (freemed_get_userlevel($LoginCookie)>$database_level) {
      $buffer .= "
        <A HREF=\"$page_link?$_auth&patient=$patient&action=modform&id=".
	"$this_result[id]\"><$STDFONT_B>MOD<$STDFONT_E></A>&nbsp;
      ";
    }
    if (freemed_get_userlevel($LoginCookie)>$delete_level) {
      $buffer .= "
        <A HREF=\"$page_link?$_auth&patient=$patient&action=delete&id=".
	"$this_result[id]\"><$STDFONT_B>DEL<$STDFONT_E></A>&nbsp;
      ";
    }
    
    $buffer .= "
      &nbsp;</TD>
    </TR>
    ";
   } // while each result-row
  else { // no items to display
   $buffer .= "
    <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
     <TD COLSPAN=".(count($control_list)+1)." ALIGN=CENTER>
      <$STDFONT_B><I>No $title</I><$STDFONT_E>
     </TD>
    </TR>
   ";
  } // if no items to display
   
  $buffer .= "
    </TABLE>
  ";
  
  // footer
  $buffer .= freemed_display_actionbar()
    ."</TD></TR>
    </TABLE>
    <!-- End itemlist Table -->
  ";

  return $buffer; // gotta remember this part!
}

// function freemed_display_facilities (selected)
function freemed_display_facilities ($param="", $default_load = false,
                                     $intext="")
{
  global $default_facility, $NONE_SELECTED;

  switch ($intext) {
   case "0": // internal
    $intextquery = "WHERE psrintext='0'";
    break;
   case "1": // external
    $intextquery = "WHERE psrintext='1'";
    break;
   default:
    $intextquery = "";
  }

  // list doctors in SELECT/OPTION tag list, and
  // leave doctor selected who is in param
  echo "
    <OPTION VALUE=\"0\"".
     ( ($param == 0) ? " SELECTED" : "" ).">$NONE_SELECTED
  ";
  $query = "SELECT * FROM facility
            $intextquery
            ORDER BY psrname,psrnote";
  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more docs
    while ($row=fdb_fetch_array($result)) {
      if ((strlen($param)>0) AND ($param != "0") AND
          ($param == $row["id"])) 
        $selected_var = " SELECTED";
      else
        $selected_var = ""; // or not...

      $_psr_name = $row["psrname"];
      $_psr_note = $row["psrnote"];
      $_psr_id   = $row["id"];

      if (($_psr_id==$default_facility) AND ($default_facility>0))
            { $selected_var = " $SELECTED"; }

      if (strlen($_psr_note)<1) $__psr_note = "";
        else $__psr_note = "[$_psr_note]";

      echo "
        <OPTION VALUE=\"$_psr_id\" $selected_var 
         >$_psr_name $__psr_note ".
         ( $debug ? "[$_psr_id]" : "" )."
      "; // end of actual output
    } // while there are more results...
  }

} // end function freemed_display_facilities

// function freemed_display_icdcodes (selected)
function freemed_display_icdcodes ($param="", $outer_tags="")
{
  global $NONE_SELECTED;

  if (!empty($outer_tags))
   echo "
    <SELECT NAME=\"$outer_tags\">
   ";

  // first, get whether its ICD9 or ICD10 preferred
  $i_result = fdb_query("SELECT * FROM config WHERE
    c_option='icd'");
  $i_r = fdb_fetch_array($i_result);
  $_icd = $i_r["c_value"];

  if ($debug) {
    echo "<!-- ICD = $_icd -->\n";
  } // debug statement

  // list doctors in SELECT/OPTION tag list, and
  // leave doctor selected who is in param
  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";

    // select order by which type we are using
  if ($_icd=="9") {
    $query = "SELECT * FROM icd9 ORDER BY ".
       "icd9code";
  } elseif ($_icd=="10") {
    $query = "SELECT * FROM icd9 ORDER BY ".
       "icd10code";
  }

  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more docs
    while ($row=fdb_fetch_array($result)) {
      if ((strlen($param)>0) AND ($param != "0")) {
        $selected_var = " SELECTED";
      } else {
        $selected_var = ""; // or not...
      } // end of checking if selected

      if ($_icd=="9") {
        $_icd_code = $row["icd9code"];
        $_icd_desc = $row["icd9descrip"];
      } elseif ($_icd=="10") {
        $_icd_code = $row["icd10code"];
        $_icd_desc = $row["icd10descrip"];
      }
      $_icd_id   = $row["id"];

      if ($_icd_id!=$param) {
        $selected_var = "";
      }

      if ($debug) {
        $debug_var = "[$_icd_id]";
      } else {
        $debug_var = "";
      } // end debug clause

      echo "
        <OPTION VALUE=\"$_icd_id\" $selected_var 
         >$_icd_code ($_icd_desc) $debug_var
      "; // end of actual output
    } // while there are more results...
  } // end of master if clause

  if (!empty($outer_tags))
   echo "
    </SELECT>
   ";
} // end function freemed_display_icdcodes

// function freemed_display_insco
//   display <SELECT>-type list of insurance companies
function freemed_display_insco ($param="0")
{
  global $NONE_SELECTED;

  $buffer="";

  $buffer .= "
   <SELECT NAME=\"$param\">
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $result = fdb_query ("SELECT * FROM insco
    ORDER BY insconame,inscostate,inscocity");
  if (!$result) {
    // don't do anything...
  } else { // 
    while ($row=fdb_fetch_array($result)) {
      $i_name = $row["insconame"];
      $i_id   = $row["id"];

      if ($debug) 
        $debug_var = "[$i_id]";
      else $debug_var = "";

      $buffer .= "
        <OPTION VALUE=\"$i_id\" ".
	( ($param == $row[id]) ? "SELECTED" : "" )
        .">$i_name ($row[inscocity],$row[inscostate]) $debug_var
      "; // actually en-buffer it
    } // while there are more results...
  } // end master loop
  $buffer .= "
   </SELECT>
  ";
  return $buffer;
} // end function freemed_display_insco

// function freemed_display_inscogroups (selected)
function freemed_display_inscogroups ($param="")
{
  global $NONE_SELECTED;

  $buffer = "";

  // list doctors in SELECT/OPTION tag list, and
  // leave doctor selected who is in param
  $buffer .= "
   <SELECT NAME =\"$param\">
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";

  $query = "SELECT * FROM inscogroup ORDER BY ".
     "inscogroup";

  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more docs
    while ($row = fdb_fetch_array($result)) {
      if ($debug) {
        $debug_var = "[$_ic_id]";
      } else {
        $debug_var = "";
      } // end debug clause

      $buffer .= "
        <OPTION VALUE=\"$row[id]\" ".
	( ($param == $row[id]) ? "SELECTED" : "" )
        .">".htmlentities($row[inscogroup])." $debug_var
      "; // end of actual output
    } // while there are more results...
  } // end of master if clause
  $buffer .= "
   </SELECT>
  ";
  return $buffer;
} // end function freemed_display_inscogroups

// function freemed_display_ptstatus
//   displays patient statuses selectable in <SELECT>
//   list with $param selected
function freemed_display_ptstatus ($param="", $outer_tags="")
{
  global $NONE_SELECTED;
  if (!empty($outer_tags))
   echo "
    <SELECT NAME=\"$outer_tags\">
     <OPTION VALUE=\"0\">$NONE_SELECTED
   "; // end outer tags...
  $result = fdb_query ("SELECT * FROM ptstatus
                        ORDER BY ptstatusdescrip,ptstatus");
  while ($r = fdb_fetch_array ($result)) {
   $ptstatus        = fm_prep ($r["ptstatus"       ]);
   $id              =          $r["id"             ] ;
   echo "
    <OPTION VALUE=\"$id\" ".
    ( ($r[id] == $param) ? "SELECTED" : "" ).
    ">".fm_prep($r[ptstatusdescrip])." ($ptstatus)
   ";
  } // end while

  if (!empty($outer_tags))
   echo "
    </SELECT>
   "; // end outer tags...
} // end function freemed_display_ptstatus

// function freemed_display_phygroups
//   displays physician groups selectable in <SELECT>
//   list with $param selected
function freemed_display_phygroups ($param="")
{
  global $NONE_SELECTED;
  
  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $query = "SELECT * FROM phygroups ORDER BY ".
     "phygroupname";
  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more docs
    while ($row=fdb_fetch_array($result)) {
      $_pg_name = $row["phygroupname"];
      $_pg_id   = $row["id"];

      if ($debug) {
        $debug_var = "[$_pg_id]";
      } else {
        $debug_var = "";
      } // end debug clause

      echo "
        <OPTION VALUE=\"$row[id]\" ".
	( ($param == $row[id]) ? "SELECTED" : "" ).
        ">".fm_prep($row[phygroupname])." $debug_var
      "; // end of actual output
    } // while there are more results...
  }
} // end function freemed_display_phygroups


// function freemed_display_phystatus (selected)
function freemed_display_phystatus ($param="")
{
  global $NONE_SELECTED;

  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";

  $query = "SELECT * FROM phystatus ORDER BY ".
     "phystatus";

  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more docs
    while ($row=fdb_fetch_array($result)) {
      echo "
        <OPTION VALUE=\"$row[id]\" ".
	( ($row[id] == $param) ? "SELECTED" : "" ).
        ">".fm_prep($row[phystatus])."
      "; // end of actual output
    } // while there are more results...
  } // end of master if clause
} // end function freemed_display_phystatus

// function freemed_display_physicians
//   displays physicians selectable in <SELECT>
//   list with $param selected
function freemed_display_physicians ($param="", $intext="")
{
  global $NONE_SELECTED;
  $buffer = "";

  // list doctors in SELECT/OPTION tag list, and
  // leave doctor selected who is in param
  $buffer .= "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $query = "SELECT * FROM physician ".
     ( ($intext != "") ? " WHERE phyref='$intext'" : "" ).
     "ORDER BY phylname,phyfname";
  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more docs
    while ($row=fdb_fetch_array($result)) {
      $buffer .= "
        <OPTION VALUE=\"$row[id]\" ".
	( ($row[id] == $param) ? "SELECTED" : "" ).
        ">".fm_prep("$row[phylname], $row[phyfname]")."
      "; // end of actual output
    } // while there are more results...
  }
  return $buffer;
} // end function freemed_display_physicians

///////////////////////////////////////////////////
// function freemed_display_printerlist  (19991008)
// displays printers from the database
function freemed_display_printerlist ($param="")
{
  global $NONE_SELECTED;

  // list printers in SELECT/OPTION tag list, and
  // leave printer selected who is in param
  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $query = "SELECT * FROM printer ORDER BY ".
     "prnthost, prntname";
  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more printers
    while ($row=fdb_fetch_array($result)) {
      echo "
        <OPTION VALUE=\"$row[id]\" ".
	( ($param == $row[id]) ? "SELECTED" : "" ).
        ">".fm_prep("$row[prnthost] $row[prntname]")."
      "; // end of actual output
    } // while there are more results...
  }
} // end function freemed_display_printerlist

// ---------------  19990815
// function freemed_display_simplereports
//   displays simple reports templates selectable in <SELECT>
//   list with $param selected
function freemed_display_simplereports ($param="")
{
  global $NONE_SELECTED;

  // list templates in SELECT/OPTION tag list, and
  // leave report selected who is in param
  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $query = "SELECT * FROM simplereport ORDER BY ".
     "sr_type, sr_label";
  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more docs
    while ($row=fdb_fetch_array($result)) {
      echo "
        <OPTION VALUE=\"$_sr_id\" ".
	( ($row[id] == $param) ? "SELECTED" : "" ).
        ">".fm_prep("$row[sr_label], $row[sr_type]")."
      "; // end of actual output
    } // while there are more results...
  }
} // end function freemed_display_simplereports
// --------------- end 19990815

// function freemed_display_tos
//   display <SELECT>-type list of types of service
function freemed_display_tos ($param)
{
  global $NONE_SELECTED;

  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $result = fdb_query ("SELECT * FROM tos
    ORDER BY tosname,tosdescrip");
  if (!$result) {
    // don't do anything...
  } else { // 
    while ($row=fdb_fetch_array($result)) {
      echo "
        <OPTION VALUE=\"$row[id]\" ".
	( ($row[id] == $param) ? "SELECTED" : "" ).
        ">".fm_prep("$row[tosname] $row[tosdescrip]")."
      "; // actually show it
    } // while there are more results...
  } // end master loop
} // end function freemed_display_tos

// function freemed_display_rooms
//   display <SELECT>-type list of rooms
function freemed_display_rooms ($param)
{
  global $default_facility, $LoginCookie, $NONE_SELECTED; 

  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $result = fdb_query ("SELECT * FROM room
    ORDER BY roomname, roomdescrip");
  if (!$result) {
    // don't do anything...
  } else { // 
    while ($row=fdb_fetch_array($result)) {
      $f_auth=explode(":", $LoginCookie);

      if ((freemed_check_access_for_facility ($LoginCookie, $rm_pos)) OR
          ($rm_pos<1) or ($f_auth[0]==0)) 
       if ((($default_facility>0) AND ($rm_pos == $default_facility)) OR
            ($default_facility<1) OR ($rm_pos<1))
        echo "
          <OPTION VALUE=\"$row[id]\" ".
	  ( ($row[id] == $param) ? "SELECTED" : "" ).
          ">".fm_prep("$row[roomname] $row[roomdescrip]")."
        "; // actually show it

    } // while there are more results...
  } // end master loop
} // end function freemed_display_rooms

// function freemed_display_phy_specialties
//   display <SELECT>-type list of physician specialties
function freemed_display_phy_specialties ($param)
{
  global $NONE_SELECTED; 

  // list degrees in SELECT/OPTION tag list, and
  // leave degrees selected that are in param
  echo "
    <OPTION VALUE=\"0\">$NONE_SELECTED
  ";
  $query = "SELECT * FROM specialties ORDER BY ".
     "specname, specdesc";
  $result = fdb_query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more
    while ($row=fdb_fetch_array($result)) {
      echo "
        <OPTION VALUE=\"$row[id]\" ".
	( ($row[id] == $param) ? "SELECTED" : "" ).
        ">".fm_prep($row[specname].
	 ( (!empty($row[specdesc])) ? ", " : " " ).
  	  $row[specdesc])."\n";
    } // while there are more results...
  }

} // end function freemed_display_phy_specialties

// function freemed_display_selectbox
function freemed_display_selectbox ($result, $format, $param="")
{
  global $$param; // so it knows to put SELECTED on properly
  static $var; // array of $result-IDs so we only go through them once

  if (!isset($var[$result])) {
    while ($var[$result][] = fdb_fetch_array($result));
  } // if we haven't gone through this list yet
  
  $buffer = "";
  if (count($var[$result])<1) { 
    $buffer .= "NONE";
    return $buffer; // do nothing!
  } // if no result

  $buffer .= "
    <SELECT NAME =\"$param\">
      <OPTION VALUE=\"0\">NONE SELECTED
  ";
  reset($var[$result]); // if we're caching it, we have to reset it!
  while ( (list($pickle,$item) = each($var[$result])) 
                                     AND ($item[id])) { // no null values!
    // START FORMAT-FETCHING
    $format_array = explode("#",$format); // odd members are variable names!
    while (list($index,$str) = each($format_array)) {
      if ( (bcadd($index/2,0,0)) == ($index/2) ) continue; // ignore the evens!
      $format_array[$index] = $item[$str];// can't just change $str!
    } // while replacing each variable name
    $this_format = join("", $format_array); // put it back together
    // END FORMAT-FETCHING    
    $buffer .= "
      <OPTION VALUE=\"$item[id]\" ".
      ( ($item[id] == $$param) ? "SELECTED" : "" ).
      ">".fm_prep($this_format)."\n";
  } // while fetching result
  $buffer .= "
    </SELECT>
  ";
  
  return $buffer;
} // end function freemed_display_selectbox


// function freemed_export_stock_data
function freemed_export_stock_data ($table_name, $file_name="")
{
  global $physical_loc, $default_language, $cur_date_hash, 
         $debug;

  $physical_file = $physical_loc . "/data/" . $default_language . "/" .
    $table_name . "." . $default_language . "." . $cur_date_hash;

  #if (strlen ($file_name) > 2) $physical_file = $file_name;

  #if (file_exists ($physical_file)) { return false; } // fix this later

  $query = "SELECT * FROM $table_name
            INTO OUTFILE '$physical_file'
            FIELDS TERMINATED BY ','
            OPTIONALLY ENCLOSED BY ''
            ESCAPED BY '\\\\'";

  if ($debug) echo "<BR> query = \"$query\" <BR> \n";

  $result = fdb_query ($query);

  if ($debug) echo "<BR> result = \"$result\" <BR> \n";

  return $result;

} // end function freemed_export_stock_data

// function freemed_get_date_next
//  return the next valid date (YYYY-MM-DD)
function freemed_get_date_next ($cur_dt)
{
  global $cur_date;

  $y = substr ($cur_dt, 0, 4); // get year
  $m = substr ($cur_dt, 5, 2); // get month
  $d = substr ($cur_dt, 8, 2); // get date

  // check for validity of given date... if not, cur_date
  if (!checkdate($m, $d, $y)) { 
    $y = substr ($cur_date, 0, 4);
    $m = substr ($cur_date, 5, 2);
    $d = substr ($cur_date, 8, 2); 
  }

  if (!checkdate($m, $d + 1, $y)) { // roll day?
    if (!checkdate($m + 1, 1, $y)) { // roll month?
      // roll year
      return date ("Y-m-d", mktime (0,0,0,1,1,$y+1));
    } else {
      // roll month
      return date ("Y-m-d", mktime (0,0,0,$m+1,1,$y));
    }
  } else {
    // roll day
    return date ("Y-m-d", mktime (0,0,0,$m,$d+1,$y));
  }
} // end function freemed_get_date_next

// function freemed_get_date_prev
//   returns the previous date
function freemed_get_date_prev ($cur_dt)
{
  global $cur_date;

  $y = substr ($cur_dt, 0, 4); // year
  $m = substr ($cur_dt, 5, 2); // month
  $d = substr ($cur_dt, 8, 2); // day 

  if (!checkdate ($m, $d, $y)) {
    $y = substr ($cur_date, 0, 4);
    $m = substr ($cur_date, 5, 2);
    $d = substr ($cur_date, 8, 2);
  } // if not right, use current date

  if (($d==1) AND ($m>1)) { // if first day...
    $d = 31; $m--; // roll back
    while (!checkdate ($m, $d, $y)) 
      $d--; // while day too high, decrease
    return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
  } else if (($d==1) AND ($m==1)) { 
    // roll back year
    $m=12; $y--; $d=31;
    return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
  } else {
    // roll back day
    $d--;
    return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
  }  
} // end function freemed_get_date_prev

// function freemed_get_link_rec
//   return the entire record as an array for
//   a link
function freemed_get_link_rec($id="0", $db="")
{
  global $database;

  if (empty($db)) {
    return "";
  }

  $result=fdb_query("SELECT * FROM $db WHERE
    id='$id'"); // get just that record
  return fdb_fetch_array($result); // return the array

} // end function freemed_get_link_rec

// function freemed_get_link_field
//   return a particular field from a link...
function freemed_get_link_field($id="0", $db="", $field="id")
{
  global $database;

  if (strlen($db)<1) {
    return "";
  }

  $this_array=freemed_get_link_rec($id,$db);
  return $this_array["$field"];

} // end function freemed_get_link_field

// function freemed_get_userlevel
//   returns user level (1-10)
//   (assumes 1 if not found, 9 if root)
function freemed_get_userlevel ($f_cookie="")
{
  global $LoginCookie, $database;

  if ($f_cookie=="") $f_cookie = $LoginCookie; // 19990920

  $f_auth = explode (":", $f_cookie);

  if ($f_auth[0]<1) {
    return 0; // if no user, return 0
  } // end checking for null user

  if ($f_auth[0]==1) {
    return 10; // if root, give superuser access
  } else {

    $result = fdb_query("SELECT * FROM user
      WHERE id='$f_auth[0]'"); // get query...

    if (fdb_num_rows($result)!=1) {
      return 1;
    } // if there is more or less than one answer...

    $r = fdb_fetch_array($result);
    $userlevel = $r["userlevel"]; // get the userlevel
    return $userlevel;  // give it back to everything else...

  } // end else loop checking for name

} // end function freemed_get_userlevel

// function freemed_import_stock_data
//  import stock data from data/$language directory
function freemed_import_stock_data ($table_name)
{
  global $physical_loc, $default_language;

  $physical_file = $physical_loc . "/data/" . $default_language . "/" .
    $table_name . "." . $default_language . ".data";

  if (!file_exists($physical_file)) return false;

  $query = "LOAD DATA LOCAL INFILE '$physical_file' INTO
            TABLE $table_name
            FIELDS TERMINATED BY ','";
           
  $result = fdb_query ($query); // try doing it

  return $result; // send the results home...
} // end function freemed_import_stock_data

// function freemed_inscogroup_display
//   displays inscogroup entry in text format
function freemed_inscogroup_display ($param)
{
  global $NONE_SELECTED, $NO_RESULT_OF_QUERY, $SELECTED, $debug;

  if ((strlen($param)<1) OR ($param=="0")) {
    echo "
      $NONE_SELECTED
    ";
  } else {
    $query = "SELECT * FROM inscogroup ".
       "WHERE id='$param'";
    $result = fdb_query ($query);
    if (!$result) {
      echo "$NO_RESULT_OF_QUERY";
      // don't do anything...! 
    } else { // exit if no more
      $row = fdb_fetch_array($result); 
      echo fm_prep ($row[inscogroup]).
       ( $debug ? " [ $row[id] ]" : "" )."\n";
    }
  }
} // end function freemed_inscogroup_display

// function freemed_log
function freemed_log ($f_cookie="", $db_name, $record_number, $comment) {
  global $LoginCookie, $cur_date;

  if (empty($f_cookie)) $f_cookie = $LoginCookie;

  $f_auth = explode (":", $f_cookie);
  $f_user = $f_auth [0];  // extract the user number
  $query = "INSERT INTO log VALUES ( '$cur_date',
    '$f_user', '$db_name', '$record_number', '$comment', NULL )";
  $result = fdb_query ($query); // perform addition
  return true;  // return true
} // end function freemed_log

// function freemed_multiple_choice
function freemed_multiple_choice ($sql_query, $display_field, $select_name,
  $blob_data, $display_all=true)
{
  global $lang_ALL; 

  $brackets = "[]";
  $result = fdb_query ($sql_query); // check
  $all_selected = fm_value_in_string ($blob_data, -1);
  echo " 
    <SELECT NAME=\"$select_name$brackets\" MULTIPLE SIZE=5>
  ";
  if ($display_all) echo "
      <OPTION VALUE=\"-1\" ".
       ($all_selected ? "SELECTED" : "").">".fm_prep($lang_ALL)."
  "; // if there is nothing...

  if ( fdb_num_rows($result) > 0) 
   while ($r = fdb_fetch_array ($result)) {
    if (strpos ($display_field, ":")) {
      $displayed = ""; // set as null
      $split_display_field = explode (":", $display_field);
      for ($sl=0; $sl<sizeof($split_display_field); $sl++) {
        $displayed .= $r[$split_display_field[$sl]];
        if ($sl < (sizeof ($split_display_field) - 1))
          $displayed .= ", "; // if not the last, insert separator
      }
    } else { // if it is only one field
      $displayed = $r[$display_field];
    } // end if-else displayed loop
    $id = $r["id"];
    if ($debug) $debuginfo = " [$id] ";
    if (fm_value_in_string ($blob_data, $id)) $selected = $SELECTED;
      else $selected = "";
    echo "
      <OPTION VALUE=\"$id\" ".
       ( (fm_value_in_string ($blob_data, id)) ? "SELECTED" : "" ).
       ">$displayed $debuginfo
    ";
  } // end while
  echo "
    </SELECT>
  "; // end the select tag
} // end function freemed_multiple_choice

// function freemed_open_db
function freemed_open_db ($my_cookie) {

  global $LoginCookie, $Entered_incorrect_NamePasswd,
    $Possible_Cookies_Have_Expired, $base_url, $Login_Screen;

  if ($my_cookie=="") $my_cookie = $LoginCookie;

   // new authentication as of 19990701
  $connect = freemed_verify_auth ($my_cookie);

   // comment out the mess
  //echo "<!-- ";
 
  if ($connect != 1) {
    freemed_display_html_top ();
    freemed_display_banner ();
    DIE ("<!-- -->
      <CENTER>
      <B>
      <P>
      $Entered_incorrect_NamePasswd
      <BR><BR>
      <I>$Possible_Cookies_Have_Expired
      <P>
      </B>
      <A HREF=\"$base_url\">$Login_Screen</A>
      </CENTER>
      </BODY></HTML>
    ");
  } else {
    //if ((strlen($default_facility)>0) AND ($default_facility!=" "))
    //  SetCookie ("default_facility", $default_facility,
    //   time()+$_cookie_expire);
    //echo "df = $default_facility <BR>";
  } // end if connected loop

    // begin showing things again
  //echo " -->";
} // end function freemed_open_db

// function freemed_specialty_display
//   displays specialty db entry in text format
function freemed_specialty_display ($param)
{
  global $NONE_SELECTED, $SELECTED;

  if ((strlen($param)<1) OR ($param=="0")) {
    echo "
      $NONE_SELECTED
    ";
  } else {
    $query = "SELECT * FROM specialty ".
       "WHERE id='$param'";
    $result = fdb_query ($query);
    if (!$result) {
      echo "$NO_RESULT_OF_QUERY";
      // don't do anything...! 
    } else { // exit if no more
      $row=fdb_fetch_array($result); 
      echo fm_prep ($row[specdegree]." (".
                    $row[specname].")");
    }
  }
} // end function freemed_specialty_display


  // USER AUTHENTICATION FUNCTIONS (19990701)

  // these are from px.skylar.com, and not mine
  // but they are heavily modified to work with
  // a root "backdoor", so as to allow for setup...

function freemed_auth_db_connect () {
  global $Connection; // , $db_user, $db_password;
  $Connection = fdb_connect (DB_HOST, DB_USER, DB_PASSWORD);
  return $Connection;
} // end function freemed_auth_db_connect

function freemed_auth_login ($f_username, $f_password) {
  global $SessionLoginCookie, $_cookie_expire;

  if (($f_username=="root") AND ($f_password==DB_PASSWORD)) {
    $md5pw = md5($f_password);
    $f_connection = freemed_auth_db_connect ();
    SetCookie("LoginCookie", "1:$md5pw", time()+$_cookie_expire);
    $SessionLoginCookie = "1:$md5pw"; // 19990909
    return 1;
  } // endif for root case loop
  $f_connection = freemed_auth_db_connect ();
  $f_result = fdb_query ("SELECT * FROM ".
    "user WHERE username='$f_username'");
  if (!$f_result) return 0; // then fail
  $f_row = fdb_fetch_array ($f_result);
  if (($f_row["username"] == $f_username) AND
      ($f_row["userpassword"] == $f_password) AND
      ($f_username != "")) {
    $f_id = $f_row["id"];
    $md5pw = md5($f_password);
    SetCookie("LoginCookie", "$f_id:$md5pw", time()+$_cookie_expire);
    $SessionLoginCookie = "$f_id:$md5pw"; // 19990909
    // commented out following line due to global variable... 19990722
    // SetCookie("Connection", "$f_connection", time()+$_cookie_expire);
    return 1;
  } else return 0;
} // end function freeemed_auth_login

function freemed_verify_auth ($f_cookie="") {
  global $_cookie_expire, $LoginCookie, $debug, $Connection;

  if ($f_cookie=="") $f_cookie = $LoginCookie;
    // split into user/pw pair
  $f_auth = explode (":", $f_cookie);
  SetCookie ("LoginCookie", $f_cookie, time()+$_cookie_expire);
  $f_connection = freemed_auth_db_connect ();
  $Connection = $f_connection;  // not cookie -> global (19990921)
  //if ($debug) echo "f_auth[0] = $f_auth[0], f_auth[1] = $f_auth[1]";
  if (($f_auth[0]==1) AND ($f_auth[1]==md5(DB_PASSWORD))) return 1;
  $f_result = fdb_query ("SELECT * FROM user ".
    "WHERE id = '$f_auth[0]'");
  if (!$f_result) return 0; // kill
  $f_row = fdb_fetch_array($f_result); // get result array
  $md5pw = md5($f_row["userpassword"]);
  if (($f_row["id"] == $f_auth[0]) AND
      ($md5pw == $f_auth[1]) AND
      ($f_auth[0] != "")) return 1;
  else return 0;
} // end function freemed_verify_auth

 #
 # FREEMED DB WRAPPERS SECTION
 #   Added 19990714 -- allows use of other databases besides MySQL by
 #   making all database access the same, specified by a variable in
 #   global.var.inc...
 #
 # 19990722 - Merged functions from Gianugo Rabellino's SQL
 #            Abstraction Library (SAL) from px.skylar.com
 #            and added msql compatibility

function fdb_affected_rows ($result = "0") {
  switch (strtolower(DB_ENGINE)) {
    case "odbc": return odbc_num_rows ($result); break;
    case "postgres": return pg_NumRows (); break;
    case "mysql":
    default: return mysql_affected_rows (); break;
  } // end switch
} // end function fdb_affected_rows

function fdb_close ($null = "") {
  global $Connection;
  switch (strtolower(DB_ENGINE)) {
    case "odbc":     return odbc_close ($Connection); break;
    case "postgres": return pg_Close ($Connection);   break;
    case "msql":     return msql_close ();            break;
    case "mysql":
    default:         return mysql_close ();           break;
  } // end switch
} // end function fdb_close

function fdb_connect ($db_host, $user, $password) {
  global $database;
  switch (strtolower(DB_ENGINE)) {
    case "odbc": return odbc_pconnect ($host, $user, $password); break;
    case "postgres": return pg_Connect ("dbname=$database".
                                        "host=".DB_HOST." ".
                                        "user=".DB_USER." ".
                                        "password=".DB_PASSWORD);break;
    case "msql":
      $Connection = msql_pconnect (DB_HOST);
      if ($Connection) msql_select_db ($database, $Connection);
      return $Connection;
      break;
    case "mysql":
    default:
      $Connection = mysql_pconnect (DB_HOST, DB_USER, DB_PASSWORD);
      if ($Connection) mysql_select_db ($database, $Connection);
      return $Connection;
      break;
  } // end switch
} // end function fdb_connect

function fdb_create_db ($created_name) {
  global $Connection;
  switch (strtolower(DB_ENGINE)) {
    case "odbc": return odbc_exec ($Connection, "CREATE DATABASE ".
      "$created_name"); break;
    case "postgres": return pg_Exec ($Connection, "CREATE DATABASE ".
      "$created_name"); break;
    case "mysql":
    default: return mysql_create_db ($created_name); break;
  } // end switch
} // end function fdb_create_db

function fdb_drop_db ($dropped_name) {
  global $Connection;
  switch (strtolower(DB_ENGINE)) {
    case "odbc": return odbc_exec ($Connection, "DROP DATABASE ".
      "$dropped_name"); break;
    case "postgres": return pg_Exec ($Connection, "DROP DATABASE ".
      "$dropped_name"); break;
    case "mysql":
    default: return mysql_drop_db ($dropped_name); break;
  } // end switch
} // end function fdb_drop_db

function fdb_fetch_array ($result) {
  global $Connection;
  if ($result<1) return array();
  switch (strtolower(DB_ENGINE)) {
    case "odbc": 
      // thanks to Giannugo Rabellino <nemorino@opera.it>'s
      // SQL Abstraction library for this one...
      $row = array (); $res_array = array ();
      $res_array = odbc_fetch_row ($result, $Connection);
      $nf = count($result+2); // field numbering starts at 1
      for ($count=1; $count<$nf; $count++) {
        $field_name  = odbc_field_name ($result, $count);
        $field_value = odbc_result ($result, $field_name);
        $row[$field_name] = $field_value;
      } // end for loop
      return $row; // send back jimmie-rigged row array...
      break;
    case "postgres": return pg_Fetch_Array    ($result); break;
    case "msql":     return msql_fetch_array  ($result); break;
    case "mysql": 
    default:         return mysql_fetch_array ($result); break;
  } // end switch
} // end function fdb_fetch_array

function fdb_last_record ($last_result="") {
  global $Connection, $result;
  if ($last_result=="") $last_result = $result;
  switch (strtolower(DB_ENGINE)) {
    case "msql":    return msql_insert_id ();                      break;
    case "odbc":    DIE("fdb_last_record:: ODBC not implemented"); break;
      //return fdb_num_rows (
      //        fdb_query ("SELECT * FROM $tablename") );  break;
      
    case "postgres":               return pg_getlastoid ($result); break;
    case "mysql"   :
    default:
      return mysql_insert_id ();
      break;
  } // end switch
} // end function fdb_last_record

function fdb_num_rows ($result) {
  switch (strtolower(DB_ENGINE)) {
    case "odbc":     return odbc_num_rows  ($result);  break;
    case "postgres": return pg_NumRows     ($result);  break;
    case "msql":     return msql_num_rows  ($result);  break;
    case "mysql":
    default:         return mysql_num_rows ($result);  break;
  } // end switch
} // end function fdb_num_rows

function fdb_query ($querystring) {
  global $Connection, $database;
  switch (strtolower(DB_ENGINE)) {
    case "odbc": 
      return odbc_execute ( odbc_prepare ($Connection, $querystring) );
      break;
    case "postgres": 
      return pg_exec ($Connection, $querystring); break;
    case "msql":
      return msql_query ($querystring, $Connection);
      break;
    case "mysql": 
    default:
      return mysql_query ($querystring); break;
  } // end switch
} // end function fdb_query

  //
  //  FUNCTIONS FOR DEALING WITH MISCELLANEOUS STUFF
  //  (19990722)
  //

function fm_date_assemble ($datevarname="", $array_index=-1) {
  if ($datevarname=="") return ""; // return nothing if no variable is given
  eval ("global \$". $datevarname. "_m,
                \$". $datevarname. "_d,
                \$". $datevarname. "_y ; "); // get global vars
  if ($array_index == -1) {
    eval ("\$m = \$".$datevarname."_m ; ");    // month
    eval ("\$d = \$".$datevarname."_d ; ");    // day
    eval ("\$y = \$".$datevarname."_y ; ");    // year
  } else {
    eval ("\$m = \$".$datevarname."_m[$array_index] ; ");  // month
    eval ("\$d = \$".$datevarname."_d[$array_index] ; ");  // day
    eval ("\$y = \$".$datevarname."_y[$array_index] ; ");  // year 
  } // end checking for array index
  return $y."-".$m."-".$d;                     // return SQL format date
} // end function fm_date_assemble

function fm_date_entry ($datevarname="", $pre_epoch=false, $arrayvalue=-1) {
  if ($datevarname=="") return false;  // indicate problems
  if (($arrayvalue+0)==-1) { $suffix="";        
                             $pos="";                    }
  else                     { $suffix="[]";
                             $pos="[$arrayvalue]"; }
  global $lang_months;                 // import month names
  eval ("global \$". $datevarname. ", \$" .$datevarname."_y, \$".
         $datevarname."_m, \$".$datevarname."_d ; "); // get global vars
  eval ("\$w = \$".$datevarname."$pos   ; ");   // whole date
  eval ("\$m = \$".$datevarname."_m$pos ; ");   // month
  eval ("\$d = \$".$datevarname."_d$pos ; ");   // day
  eval ("\$y = \$".$datevarname."_y$pos ; ");   // year
  if (!empty($w)) {
    // if date is not empty, split into $m, $d, $y
    $y = substr ($w, 0, 4);  // split year
    $m = substr ($w, 5, 2);  // split month
    $d = substr ($w, 8, 2);  // split day
  } elseif (empty($y) and empty($m) and empty($d)) {
    $y = date ("Y")+0;
    $m = date ("m")+0;
    $d = date ("d")+0;
  } // end if not empty whole date

  switch ($pre_epoch) {
    case true:
     $starting_year = (date("Y")-120);
     $ending_year   = (date("Y")+20);
     break;
    case false: default:
     $starting_year = (date("Y")-10);
     $ending_year   = (date("Y")+20);
     break;
  } // end switch for pre_epoch

  // just in case, for legacy dates...
  if (($y>1800) AND ($y<$starting_year)) $starting_year = $y;
  if (($y>1800) AND ($y>$ending_year))   $ending_year   = $y;

  // now actually display the input boxes
  switch (freemed_config_value("dtfmt")) {
    case "mdy":
     echo "
      <SELECT NAME=\"".$datevarname."_m$suffix\">
       <OPTION VALUE=\"00\" ".
       ( ($m==0) ? "SELECTED" : "" ).">NONE
     ";
     for ($i=1;$i<=12;$i++) {
      if ($i==$m) { $this_selected="SELECTED"; }
       else       { $this_selected="";         }
      if ($i<10) { $x = "0"; } else { $x = ""; } 
      echo "\n<OPTION VALUE=\"$x$i\" $this_selected>$lang_months[$i]\n";
     } // end for loop (months) 
     echo "
      </SELECT> <B>-</B>
      <SELECT NAME=\"".$datevarname."_d$suffix\">
       <OPTION VALUE=\"00\" ".
       ( ($d==0) ? "SELECTED" : "" ).">NONE
     ";
     for ($i=1;$i<=31;$i++) {
      if ($i==$d) { $this_selected="SELECTED"; }
       else       { $this_selected="";         }
      if ($i<10) { $x = "0"; } else { $x = ""; }
      echo "\n<OPTION VALUE=\"$x$i\" $this_selected>$i\n";
     } // end for look (days)
     echo "
      </SELECT> <B>-</B>
      <SELECT NAME=\"".$datevarname."_y$suffix\">
       <OPTION VALUE=\"0000\" ".
       ( ($y==0) ? "SELECTED" : "" ).">NONE
     ";
     for ($i=$starting_year;$i<=$ending_year;$i++) {
      if ($i==$y) { $this_selected="SELECTED"; }
       else       { $this_selected="";         }
      echo "\n<OPTION VALUE=\"$x$i\" $this_selected>$i\n";
     } // end for look (years)
     echo "
      </SELECT>
     "; break;
    case "ymd": default:
     echo "
      <SELECT NAME=\"".$datevarname."_y$suffix\">
       <OPTION VALUE=\"0000\" ".
       ( ($y==0) ? "SELECTED" : "" ).">NONE
     ";
     for ($i=$starting_year;$i<=$ending_year;$i++) {
      if ($i==$y) { $this_selected="SELECTED"; }
       else       { $this_selected="";         }
      echo "\n<OPTION VALUE=\"$x$i\" $this_selected>$i\n";
     } // end for look (years)
     echo "
      </SELECT> <B>-</B>
      <SELECT NAME=\"".$datevarname."_m$suffix\">
       <OPTION VALUE=\"00\" ".
       ( ($m==0) ? "SELECTED" : "" ).">NONE
     ";
     for ($i=1;$i<=12;$i++) {
      if ($i==$m) { $this_selected = "SELECTED"; }
       else       { $this_selected = "";         }
      if ($i<10) { $x = "0"; } else { $x = ""; }
      echo "\n<OPTION VALUE=\"$x$i\" $this_selected>$lang_months[$i]\n";
     } // end looping for months
     echo "
      </SELECT> <B>-</B>
      <SELECT NAME=\"".$datevarname."_d$suffix\">
       <OPTION VALUE=\"00\" ".
       ( ($d==0) ? "SELECTED" : "" ).">NONE
     ";
     for ($i=1;$i<=31;$i++) {
      if ($i==$d) { $this_selected = "SELECTED"; }
       else       { $this_selected = "";         }
      if ($i<10) { $x = "0"; } else { $x = ""; }
      echo "\n<OPTION VALUE=\"$x$i\" $this_selected>$i\n";
     } // end looping for days
     echo "
      </SELECT>
     "; break;
  } // end switch for dtfmt config value
  return true;                         // we exited well!
} // end function fm_date_entry

function fm_date_print ($actualdate, $show_text_days=false) {
  global $lang_months, $lang_days;
  $y  = substr ($actualdate, 0, 4);        // extract year
  $m  = substr ($actualdate, 5, 2);        // extract month
  $d  = substr ($actualdate, 8, 2);        // extract day
  $ts = mktime (0, 0, 0, $m, $d, $y);      // generate timestamp

  $mt = $lang_months[($m+0)];              // month           (text)
  $wt = $lang_days[1 + (date("w", $ts))];  // day of the week (text)
  // decide if we show the week days names...
  if ($show_text_days) { $week = $wt.", "; }
   else                { $week = " ";      }
  switch (freemed_config_value("dtfmt")) {
    case "mdy":           return chop($week.$mt." ".$d.", ".$y); break;
    case "dmy":           return chop($week.$d." ".$mt.", ".$y); break;
    case "ymd": default:  return chop($y."-".$m."-".$d);         break; 
  } // end switch
} // end function fm_date_print

function fm_htmlize_array ($variable_name, $cur_array) {
  $array_length = count ($cur_array);
  if ($array_length==0) return false;  // if nothing, false!
  if ($array_length==1) {
    echo "
      <INPUT TYPE=HIDDEN NAME=\"$variable_name\"
       VALUE=\"".fm_prep($cur_array)."\">
     ";
    return true;                       // it printed, return true...
  } // end of array length = 1
  for ($i=0;$i<$array_length;$i++)
    echo "
      <INPUT TYPE=HIDDEN NAME=\"$variable_name\[\]\"
       VALUE=\"".fm_prep($cur_array[$i])."\">
     ";
  return true;                         // be nice, return true
} // end function fm_htmlize_array

function fm_join_from_array ($cur_array) {
  if (count($cur_array)==0) return ""; // error checking
  if (!is_array($cur_array)) return "$cur_array";  // error checking
  return implode ($cur_array, ":");
} // end function fm_join_from_array 

function fm_number_select ($varname, $min=0, $max=10, $step=1, $addz=false) {
  eval ("global \$".$varname." ; ");       // bring in the variable
  eval ("\$selected = \$".$varname." ; "); // get selected..?
  echo "\n<SELECT NAME=\"$varname\">\n";   // show start of select tag 
  if                   ($step==0)    $step = 1;    // bounds checking
  if ( ($min>$max) AND ($step>=0) )  return false; // bounds checking
  if ( ($min<$max) AND ($step<=0) )  return false; // bounds checking
  for ($i=$min;$i<=$max;$i+=$step) {
    if (("$selected"=="$i") or
        ($selected==$i))   { $this_selected = " SELECTED"; }
     else                  { $this_selected = "";          }
    if (($i<10) AND ($addz)) { $number = "0".$i; }
     else                    { $number = $i;     }
    echo "<OPTION VALUE=\"$number\"$this_selected>$number\n";
  } // end for loop
  echo "</SELECT>\n"; // end select tag 
} // end function fm_number_select

function fm_phone_assemble ($phonevarname="", $array_index=-1) {
  $buffer = ""; // we use buffered output for notebook class!
  if ($phonevarname=="") return ""; // return nothing if no variable is given
  eval ("global \$". $phonevarname. ",
                \$". $phonevarname. "_1,
                \$". $phonevarname. "_2,
                \$". $phonevarname. "_3,
                \$". $phonevarname. "_4 ; "); // get global vars
  if ($array_index == -1) {
    eval ("\$w  = \$".$phonevarname."   ; ");    // whole number
    eval ("\$p1 = \$".$phonevarname."_1 ; ");    // part 1
    eval ("\$p2 = \$".$phonevarname."_2 ; ");    // part 2
    eval ("\$p3 = \$".$phonevarname."_3 ; ");    // part 3
    eval ("\$p4 = \$".$phonevarname."_4 ; ");    // part 4
  } else {
    eval ("\$w  = \$".$phonevarname."[$array_index]   ; ");  // whole number
    eval ("\$p1 = \$".$phonevarname."_1[$array_index] ; ");  // part 1
    eval ("\$p2 = \$".$phonevarname."_2[$array_index] ; ");  // part 2
    eval ("\$p3 = \$".$phonevarname."_3[$array_index] ; ");  // part 3
    eval ("\$p4 = \$".$phonevarname."_4[$array_index] ; ");  // part 4
  } // end checking for array index
  switch (freemed_config_value("phofmt")) {
    case "usa":
     return $p1.$p2.$p3.$p4;        // assemble number and put it all together
    case "unformatted":
    default:
     return $w;                     // return whole number...
  } // end switch for formatting
} // end function fm_phone_assemble

function fm_phone_entry ($phonevarname="", $arrayvalue=-1) {
  if ($phonevarname=="") return false;  // indicate problems
  if (($arrayvalue+0)==-1) { $suffix="";        
                             $pos="";                    }
  else                     { $suffix="[]";
                             $pos="[$arrayvalue]"; }
  $formatting = freemed_config_value("phofmt"); // get phone formatting
  eval ("global \$".$phonevarname.",
                \$".$phonevarname."_1,
                \$".$phonevarname."_2,
                \$".$phonevarname."_3,
                \$".$phonevarname."_4 ; "); // get global vars
  eval ("\$w  = \$".$phonevarname."$pos  ; ");   // whole number
  eval ("\$p1 = \$".$phonevarname."_1$pos ; ");   // part 1
  eval ("\$p2 = \$".$phonevarname."_2$pos ; ");   // part 2
  eval ("\$p3 = \$".$phonevarname."_3$pos ; ");   // part 3
  eval ("\$p4 = \$".$phonevarname."_4$pos ; ");   // part 4
  if (!empty($w)) {
    // if date is not empty, split into $m, $d, $y
    switch ($formatting) {
      case "usa":
       $p1 = substr($w,  0, 3); // area code
       $p2 = substr($w,  3, 3); // prefix
       $p3 = substr($w,  6, 4); // local number
       $p4 = substr($w, 10, 4); // extention
       break;
      case "unformatted":
      default:
       // nothing!! hahahahahahahahahahahahaha!
       break;
    } // end formatting case statement
  } // end if not empty whole date
  // now actually display the input boxes
  switch ($formatting) {
    case "usa":
     $buffer .= "
      <B>(</B>
      <INPUT TYPE=TEXT NAME=\"".$phonevarname."_1$suffix\" SIZE=4
       MAXLENGTH=3 VALUE=\"$p1\"> <B>)</B>
      <INPUT TYPE=TEXT NAME=\"".$phonevarname."_2$suffix\" SIZE=4
       MAXLENGTH=3 VALUE=\"$p2\"> <B>-</B>
      <INPUT TYPE=TEXT NAME=\"".$phonevarname."_3$suffix\" SIZE=5
       MAXLENGTH=4 VALUE=\"$p3\"> <I>ext.</I>
      <INPUT TYPE=TEXT NAME=\"".$phonevarname."_4$suffix\" SIZE=5
       MAXLENGTH=4 VALUE=\"$p4\">
     "; break;
    case "unformatted": 
    default:
     $buffer .= "
      <INPUT TYPE=TEXT NAME=\"".$phonevarname."$suffix\" SIZE=15
       MAXLENGTH=16 VALUE=\"$w\">
     "; break;
  } // end switch for dtfmt config value
  return $buffer;                         // we exited well!
} // end function fm_phone_entry

function fm_split_into_array ($original_string) {
  return explode (":", $original_string);
} // end function fm_split_into_array

function fm_value_in_array ($cur_array, $value) {
  if (count ($cur_array) < 0) return false; // avoid errors
  $found = false; // initially presume that it is not there
  for ($c=0;$c<count($cur_array);$c++) // loop through array
    if ($cur_array[$c]==$value)        // if there is a match...
      $found = true;                   // return true.
  return $found; // send value back to program
} // end function fm_split_into_array

function fm_value_in_string ($cur_string, $value) {
  $this_array = fm_split_into_array ($cur_string);
  return fm_value_in_array ($this_array, $value);
} // end function fm_value_in_string

function fm_button ($b_text) {
  //$enc_b_text = urlencode ($b_text);
  // echo "<IMG SRC=\"generate_button.php3?".urlencode($enc_b_text)."\"".
  //     " ALT=\"".htmlentities($b_text)."\">";
  // 19990921 -- try this instead:
  echo "
    <TABLE CELLPADDING=1 CELLSPACING=0 BORDER=0 ALIGN=CENTER VALIGN=CENTER
     ><TR><TD BGCOLOR=#000000><FONT COLOR=#cccccc FACE=\"Arial, Verdana\"
     SIZE=-1><B>$b_text</B></FONT></TD></TR></TABLE>&nbsp;
  "; 
}

// fm_eval -- evaluate string variables (with security checks, of course)
function fm_eval ($orig_string) {
  // import all global variables
  // (thanks to jb@as220.org for this one)
  reset ($GLOBALS);
  //for ($i=0;$i<100;$i++) next ($GLOBALS); // skip the drek
  while (list($k,$v) = each ($GLOBALS))
   if (!isset($$k)) $$k = $GLOBALS[$k];        // $$k = $GLOBALS[$k];
  $loc_string = $orig_string;                  // transfer to internal var
  $sec_string = fm_secure ($loc_string);       // secure the string
  eval ("\$new_string = \"$sec_string\";");    // evaluate
  while (list($k,$v) = each ($GLOBALS))
   if ($k != "GLOBALS") unset ($$k);           // destroy the variables
  return $new_string;                          // return
} // end function fm_eval

// fm_prep -- prepare a string to be viewed as HTML
function fm_prep ($this_string) {
  return htmlentities(stripslashes($this_string))."";
} // end function fm_prep

// fm_secure -- secures strings that are to be evaled by simply removing
//              all secure varaibles...
function fm_secure ($orig_string) {
  $this_string = "$orig_string";  // pass to internal variable
  $this_string = str_replace ("\$db_user",     "", $this_string);
  $this_string = str_replace ("\$db_password", "", $this_string);
  $this_string = str_replace ("\$db_host",     "", $this_string);
  $this_string = str_replace ("\$database",    "", $this_string);
  $this_string = str_replace ("\$gifhome",     "", $this_string);
  $this_string = str_replace ("\$_auth",       "", $this_string);
  $this_string = str_replace ("\$db_engine",   "", $this_string);
  $this_string = str_replace ("\$LoginCookie", "", $this_string);
  return $this_string;    // return cleansed string
} // end function fm_secure

// fph -- freemed phrase handler
function fph ($this_phrase) {
  global $phrase;

  return fm_prep($phrase[$this_phrase]);
} // end function fph

} // end checking for __FREEMED_FUNCTIONS_INC__

?>
