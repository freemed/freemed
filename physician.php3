<?php
  // file: physician.php3
  // note: physician database services
  // code: jeff b (jeff@univrel.pr.uconn.edu)
  //       adam b (gdrago23@yahoo.com)
  // translation: max k <amk@span.ch>
  // lic : GPL, v2

  $page_name   ="physician.php3"; // for help info, later
  $record_name ="Provider";
  $db_name     ="physician";

  include "global.var.inc";
  include "freemed-functions.inc"; // API functions

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

switch($action) {
 case "addform": case "add": // 'form' actions not necessary
 case "modform": case "mod": // in notebook implementation
  $book = new notebook (
    array ("action", "_auth", "id", "been_here"),
    NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR );
  $book->set_submit_name("OK"); // not sure what this does...
  
  if (($action=="modform") AND (empty($been_here))) { // load the values
    $r = freemed_get_link_rec ($id, $db_name);
    extract ($r);
    $phychargemap = fm_split_into_array( $r[phychargemap] );
    $phyidmap = fm_split_into_array( $r[phyidmap] );

    // disassemble ssn
    $physsn1    = substr($physsn,    0, 3);
    $physsn2    = substr($physsn,    3, 2);
    $physsn3    = substr($physsn,    5, 4);

    if (strlen($phyaddr1b)>0) $has_second_addr=true;
  } // fetch the data first time through
  
  switch($action) {
   case "addform": case "add":
    if (empty($been_here)) 
      $been_here=1;
   break; // inner addform/add switch
   case "modform": case "mod": 
    if (empty($been_here)) 
      $been_here=1;
   break; // inner addform/add switch
  } // inner add/mod[form] switch
  
  $stat_q = "SELECT * FROM phystatus ORDER BY phystatus";
  $stat_r = fdb_query($stat_q); // have the result ready for display_selectbox

  $book->add_page (
    _("Primary Information"),
    array (
      "phylname", "phyfname", "phytitle", "phymname",
      "phytitle", "phypracname", "phyid1", "phystatus"
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Last Name")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phylname SIZE=25 MAXLENGTH=52
     VALUE=\"".prepare($phylname)."\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("First Name")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyfname SIZE=25 MAXLENGTH=50
     VALUE=\"".prepare($phyfname)."\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Middle Name")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phymname SIZE=25 MAXLENGTH=50
     VALUE=\"$phymname\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Title")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phytitle SIZE=10 MAXLENGTH=10
     VALUE=\"$phytitle\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Practice Name")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phypracname SIZE=25 MAXLENGTH=30
     VALUE=\"$phypracname\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Internal ID #")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyid1 SIZE=11 MAXLENGTH=10
     VALUE=\"$phyid1\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Status")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".
    freemed_display_selectbox($stat_r, "#phystatus#", "phystatus")
    ."
    </TD></TR>
   </TABLE>
    "
  );

  $book->add_page (
    "Contact",
    array (
     "phyemail", "phycellular", "phypager",
     "phycellular_1", "phycellular_2", "phycellular_3", "phycellular_4",
     "phycellular_5",
     "phypager_1", "phypager_2", "phypager_3", "phypager_4",
     "phypager_5",
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT> 
    <$STDFONT_B>"._("Email Address")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyemail SIZE=25 MAXLENGTH=30
     VALUE=\"$phyemail\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Cellular Phone #")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phycellular")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Beeper / Pager #")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phypager")."
    </TD></TR>
   </TABLE>
    "
  );
 
  $book->add_page (
    _("Address"),
    array (
     "phyaddr1a", "phyaddr2a", "phycitya", "phystatea", "phyphonea", "phyzipa",
     "phyphonea_1", "phyphonea_2", "phyphonea_3", "phyphonea_4",
     "phyphonea_5",
     "phyfaxa_1", "phyfaxa_2", "phyfaxa_3", "phyfaxa_4", "phyfaxa",
     "phyfaxa_5",
     "has_second_addr"
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Primary Address Line 1")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr1a SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1a\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Primary Address Line 2")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr2a SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2a\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Primary Address City")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phycitya SIZE=21 MAXLENGTH=20
     VALUE=\"$phycitya\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Primary Address State")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phystatea SIZE=6 MAXLENGTH=5
     VALUE=\"$phystatea\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Primary Address Zip")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyzipa SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipa\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Primary Address Phone #")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyphonea")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Primary Address Fax #")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyfaxa")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Has Second Address")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=CHECKBOX NAME=\"has_second_addr\" ".
    ($has_second_addr ? "CHECKED" : "").">". 
    "</TD></TR>
   </TABLE>

    "
  );

  if ($has_second_addr)
    $book->add_page (
      _("Address 2"),
      array (
       "phyphoneb_1", "phyphoneb_2", "phyphoneb_3", "phyphoneb_4",
       "phyphoneb_5",
       "phyfaxb_1", "phyfaxb_2", "phyfaxb_3", "phyfaxb_4", "phyfaxb",
       "phyfaxb_5",
       "phyaddr1b", "phyaddr2b", "phycityb", "phystateb", "phyphoneb", "phyzipb"
      ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Secondary Address Line 1")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr1b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1b\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Secondary Address Line 2")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr2b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2b\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Secondary Address City")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phycityb SIZE=20 MAXLENGTH=20
     VALUE=\"$phycityb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Secondary Address State")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phystateb SIZE=6 MAXLENGTH=5
     VALUE=\"$phystateb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Secondary Address Zip")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyzipb SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Secondary Address Phone #")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyphoneb")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Secondary Address Fax #")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyfaxb")."
    </TD></TR>
   </TABLE>
      "
    ); // second address page

  $phy_deg_q = "SELECT * FROM degrees ORDER BY ".
               "degdegree, degname";
  $phy_deg_r = fdb_query($phy_deg_q);
  $spec_q = "SELECT * FROM specialties ORDER BY ".
            "specname, specdesc";
  $spec_r = fdb_query($spec_q);

  $book->add_page(
    _("Personal"),
    array (
      "phyupin", "phyref",
      "physsn1", "physsn2", "physsn3", 
      "phydeg1", "phydeg2", "phydeg3",
      "physpe1", "physpe2", "physpe3"
    ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
     <$STDFONT_B>"._("UPIN Number")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyupin SIZE=16 MAXLENGTH=15
     VALUE=\"$phyupin\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Social Security #")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=physsn1 SIZE=4 MAXLENGTH=3
     VALUE=\"$physsn1\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=physsn2 SIZE=3 MAXLENGTH=2
     VALUE=\"$physsn2\"> <B>-</B>
    <INPUT TYPE=TEXT NAME=physsn3 SIZE=5 MAXLENGTH=4
     VALUE=\"$physsn3\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Degree 1")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($phy_deg_r, 
       "#degdegree#, #degname#", "phydeg1")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Degree 2")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($phy_deg_r, 
       "#degdegree#, #degname#", "phydeg2")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Degree 3")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($phy_deg_r, 
       "#degdegree#, #degname#", "phydeg3")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Specialty 1")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($spec_r, 
       "#specname#, #specdesc#", "physpe1")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Specialty 2")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($spec_r, 
       "#specname#, #specdesc#", "physpe2")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Specialty 3")." : <$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    ".freemed_display_selectbox ($spec_r, 
       "#specname#, #specdesc#", "physpe3")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    <$STDFONT_B>"._("Physician Internal/External")."<$STDFONT_E>
    </TD><TD ALIGN=LEFT>
    <SELECT NAME=\"phyref\">
      <OPTION VALUE=\"no\" ".
       ( ($phyref != "yes") ? "SELECTED" : "" ).">"._("In-House")."
      <OPTION VALUE=\"yes\" ".
       ( ($phyref == "yes") ? "SELECTED" : "" ).">"._("Referring")."
    </SELECT>
    </TD></TR>
  

   </TABLE>
    "
  );

  // cache this outside of the function call (can't abstract that while-loop)
  // $brackets is defined in global.var.inc
  $cmap_buf="";
  $int_r = fdb_query("SELECT * FROM intservtype");
  while ($i_r = fdb_fetch_array ($int_r)) {
    $i_id = $i_r ["id"];
    $cmap_buf .= "
     <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color ($_alternate)).">
      <TD>".prepare($i_r["intservtype"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phychargemap$brackets\"
        SIZE=15 MAXLENGTH=30 VALUE=\"".$phychargemap[$i_id]."\">
      </TD>
     </TR>
    ";
  } // end looping for service types

  $book->add_page(
    _("Charge Map"),
    array (
      "phychargemap"
    ),
    "
    <INPUT TYPE=HIDDEN NAME=\"phychargemap$brackets\" VALUE=\"0\">

   <CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
    BGCOLOR=\"#000000\"> <!-- black border --><TR><TD>
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#aaaaaa>
     <TD><B>"._("Internal Type")."</B></TD>
     <TD><B>"._("Amount")."</B></TD>
    </TR>
    $cmap_buf
    </TABLE>
   </TD></TR></TABLE></CENTER>
    "
  );

  $insmap_buf = ""; // cache the output, as above
  $i_res = fdb_query("SELECT * FROM inscogroup");
  while ($i_r = fdb_fetch_array ($i_res)) {
    $i_id = $i_r ["id"];
    $insmap_buf .= "
     <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
      <TD>".prepare($i_r["inscogroup"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phyidmap$brackets\"
        SIZE=15 MAXLENGTH=30 VALUE=\"".$phyidmap[$i_id]."\">
      </TD>
     </TR>
    ";
  } // end looping for service types

  $book->add_page(
    _("Insurance IDs"),
    array (
      "phyidmap"
    ),
    "
  <CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 
   BGCOLOR=\"#000000\"> <!-- black border --><TR><TD>

    <!-- hide record zero, since it isn't used... -->
    <INPUT TYPE=HIDDEN NAME=\"phyidmap$brackets\" VALUE=\"0\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR BGCOLOR=#aaaaaa>
     <TD><B>"._("Insurance Group")."</B></TD>
     <TD><B>"._("ID Number")."</B></TD>
    </TR>
    $insmap_buf
    </TABLE>
  </TD></TR></TABLE></CENTER>
    "
  );
  // now display the thing
  freemed_display_box_top( 
    ( ($action=="addform" or $action=="add") ? _("Add") : _("Modify") ).
    " "._($record_name));
  if (!$book->is_done()) {
    echo "<CENTER>\n".$book->display()."</CENTER>
    <P ALIGN=CENTER>
     <$STDFONT_B>
     <A HREF=\"$page_name?$_auth\">
      "._("Abandon ".( (($action=="modform") OR ($action=="mod")) ? 
      "Modification" : "Addition") )."
     </A>
     <$STDFONT_E>
    ";
  } else { // submit has been clicked
    if ($action=="modform") {
      echo "
        <P ALIGN=CENTER>
        <$STDFONT_B>"._("Modifying")." . . . <$STDFONT_E>
      ";

      // reassemble phone #s
      $phyphonea = fm_phone_assemble ("phyphonea");
      $phyphoneb = fm_phone_assemble ("phyphoneb");
      $phyfaxa   = fm_phone_assemble ("phyfaxa");
      $phyfaxb   = fm_phone_assemble ("phyfaxb");
      $phycellular = fm_phone_assemble ("phycellular");
      $phypager    = fm_phone_assemble ("phypager");
  
      // reassemble ssn #
      $physsn    = $physsn1.$physsn2.$physsn3;
  
      $query = "UPDATE $db_name SET ".
        "phylname   ='$phylname',    ".
        "phyfname   ='$phyfname',    ".
        "phytitle   ='$phytitle',    ". 
        "phymname   ='$phymname',    ".     
        "phypracname='$phypracname', ".
        "phyaddr1a  ='$phyaddr1a',   ". 
        "phyaddr2a  ='$phyaddr2a',   ".
        "phycitya   ='$phycitya',    ".
        "phystatea  ='$phystatea',   ".
        "phyzipa    ='$phyzipa',     ". 
        "phyphonea  ='$phyphonea',   ".
        "phyfaxa    ='$phyfaxa',     ".
        "phyaddr1b  ='$phyaddr1b',   ".    
        "phyaddr2b  ='$phyaddr2b',   ".
        "phycityb   ='$phycityb',    ".
        "phystateb  ='$phystateb',   ".
        "phyzipb    ='$phyzipb',     ".
        "phyphoneb  ='$phyphoneb',   ".
        "phyfaxb    ='$phyfaxb',     ".
        "phyemail   ='$phyemail',    ".
        "phycellular = '$phycellular', ".
        "phypager   ='$phypager',    ".
        "phyupin    ='$phyupin',     ".
        "physsn     ='$physsn',      ".
        "phydeg1    ='$phydeg1',     ".
        "phydeg2    ='$phydeg2',     ".
        "phydeg3    ='$phydeg3',     ".
        "physpe1    ='$physpe1',     ".
        "physpe2    ='$physpe2',     ".
        "physpe3    ='$physpe3',     ".
        "phyid1     ='$phyid1',      ".
        "phystatus  ='$phystatus',   ".
        "phyref     ='$phyref',      ".
        "phyrefcount='$phyrefcount', ".
        "phyrefamt  ='$phyrefamt',   ".
        "phyrefcoll ='$phyrefcoll',  ".
        "phychargemap='".fm_join_from_array($phychargemap)."', ".
        "phyidmap    ='".fm_join_from_array($phyidmap)    ."'  ". 
        "WHERE id='$id'";
  
      $result = fdb_query($query);
      
      if ($result) {
        echo "
	<$STDFONT_B>"._("done").".<$STDFONT_E>
	";
      } else { // error!
        echo "
	<$STDFONT_B>"._("ERROR")."! [$query, $result]<$STDFONT_E>
	";
      }  
      // finished the mod database call
    } else if ($action=="addform") {
      echo "
    <P ALIGN=CENTER>
    <$STDFONT_B>"._("Adding")." . . . 
      ";

      // assemble phone #s
      $phyphonea   = fm_phone_assemble ("phyphonea");
      $phyphoneb   = fm_phone_assemble ("phyphoneb");
      $phyfaxa     = fm_phone_assemble ("phyfaxa");
      $phyfaxb     = fm_phone_assemble ("phyfaxb");
      $phycellular = fm_phone_assemble ("phycellular");
      $phypager    = fm_phone_assemble ("phypager");

      // assemble ssn
      $physsn    = $physsn1.$physsn2.$physsn3;

      // actual query/insert
      $query = "INSERT INTO $db_name VALUES ( 
        '$phylname',
        '$phyfname',
        '$phytitle',    
        '$phymname',
        '$phypracname',
        '$phyaddr1a',   
        '$phyaddr2a',
        '$phycitya',
        '$phystatea',
        '$phyzipa',     
        '$phyphonea',
        '$phyfaxa',
        '$phyaddr1b',
        '$phyaddr2b',
        '$phycityb',
        '$phystateb',
        '$phyzipb',    
        '$phyphoneb',
        '$phyfaxb',
        '$phyemail',   
        '$phycellular',
        '$phypager', 
        '$phyupin',
        '$physsn',
        '$phydeg1',    
        '$phydeg2',
        '$phydeg3',
        '".addslashes($physpe1).             "',
        '".addslashes($physpe2).             "',
        '".addslashes($physpe3).             "',
        '".addslashes($phyid1).              "',
        '".addslashes($phystatus).           "',
        '".addslashes($phyref).              "',
        '".addslashes($phyrefcount).         "',
        '".addslashes($phyrefamt).           "',
        '".addslashes($phyrefcoll).          "',
        '".fm_join_from_array($phychargemap)."',
        '".fm_join_from_array($phyidmap).    "',
        NULL ) ";

      $result = fdb_query($query);

      if ($result) {
        echo "
	<$STDFONT_B>"._("done").".<$STDFONT_E>
	";
      } else { // error!
        echo "
	<$STDFONT_B>"._("ERROR")."! [$query, $result]<$STDFONT_E>
	";
      }
    
    } else { // error
      echo "
        <P ALIGN=CENTER>
	<$STDFONT_B>"._("ERROR")."! \$action=$action!<$STDFONT_E>
	</P>
      ";
    } // error handler
    echo "
    <P ALIGN=CENTER>
    <A HREF=\"$page_name?$_auth\">
    <$STDFONT_B>"._("back")."<$STDFONT_E>
    </A>
    ";
  } // if executing the action
  freemed_display_box_bottom();
 
 break; // master add/mod[form]

 case "delete":
  freemed_display_box_top(_("Deleting $record_name"));
  echo "<P ALIGN=CENTER><$STDFONT_B>Deleting...";
  $query = "DELETE FROM physician WHERE id='$id'";
  $result = fdb_query($query);
  if ($result) 
    echo _("done").".";
  else
    echo _("ERROR")."! [$query, $result]";
  echo "<$STDFONT_E>";
  echo "
  <P ALIGN=CENTER>
  <A HREF=\"$page_name?$_auth\">
  <$STDFONT_B>"._("back")."<$STDFONT_E>
  </A>
  ";
  freemed_display_box_bottom();
 break;

 case "display" :
  freemed_display_box_top(_("$record_name View"));
  $phy = freemed_get_link_rec($id, "physician");
  echo "
   <CENTER>
    <TABLE WIDTH=\"100%\">
     <TR><TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Name")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <$STDFONT_B>$phy[phyfname] 
          $phy[phymname] $phy[phylname], $phy[phytitle]<$STDFONT_E>
     </TD></TR>
  ";
  if (freemed_get_userlevel($LoginCookie)>$database_level)
   echo "
     <TR><TD COLSPAN=2 ALIGN=CENTER>
      <$STDFONT_B><A HREF=\"physician.php3?$_auth&action=modform&id=$id\"
       >"._("Modify $record_name")."</A><$STDFONT_E>
     </TD></TR>
   ";
  echo "
     <TR><TD COLSPAN=2 ALIGN=CENTER>
      <$STDFONT_B><A HREF=\"physician.php3?$_auth&id=$id\"
       >"._("back")."</A><$STDFONT_E>
     </TD></TR>
    </TABLE>
   </CENTER>
  ";
  freemed_display_box_bottom();
 break;

 default:
  freemed_display_box_top("$record_name");
  $phy_q = "SELECT * FROM physician ORDER BY phylname,phyfname";
  $phy_r = fdb_query($phy_q);
  echo freemed_display_itemlist (
    $phy_r,
    "physician.php3",
    array (
      _("Last Name") => "phylname",
      _("First Name") => "phyfname"
    ),
    array (
      "",
      ""
    )
  );
  freemed_display_box_bottom();
 break;
} // master action switch

/*
if ($action=="addform") {

  freemed_display_box_top ("$Add_Physician", $page_name);
  echo "
    <BR>
    <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <!-- this shouldn't be here !!! HELP!! HELP!!
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Number_of_Referrals<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyrefcount SIZE=10 MAXLENGTH=10
     VALUE=\"$phyrefcount\">
     </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Referral_Amount ($S_charged)<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyrefamt SIZE=10 MAXLENGTH=10
     VALUE=\"$phyrefamt\">
    </TD></TR>
    <TR><TD>&nbsp;</TD><TD COLSPAN=2>
    <$STDFONT_B>$Referral_Amount ($S_received)<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=phyrefcoll SIZE=10 MAXLENGTH=10
     VALUE=\"$phyrefcoll\">
    </TD></TR> -->

    </TABLE>
*/
freemed_close_db (); // close the database

freemed_display_html_bottom ();

?>
