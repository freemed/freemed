<?php
  // $Id$
  // note: provider (formerly physician) database services
  // code: jeff b (jeff@univrel.pr.uconn.edu)
  //       adam b (gdrago23@yahoo.com)
  // translation: max k <amk@span.ch>
  // lic : GPL

if (!defined("__PROVIDER_MODULE_PHP__")) {

define(__PROVIDER_MODULE_PHP__, true);

include "lib/module_maintenance.php";

class providerMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "Provider Maintenance";
	var $MODULE_VERSION = "0.1";

	var $record_name    = "Provider";
	var $table_name     = "physician";

	var $variables      = array (
        "phylname",
        "phyfname",
        "phytitle",
        "phymname",
        "phypracname",
        "phyaddr1a",
        "phyaddr2a",
        "phycitya",
        "phystatea",
        "phyzipa",
        "phyphonea",
        "phyfaxa",
        "phyaddr1b",
        "phyaddr2b",
        "phycityb",
        "phystateb",
        "phyzipb",
        "phyphoneb",
        "phyfaxb",
        "phyemail",
        "phycellular",
        "phypager",
        "phyupin",
        "physsn",
        "phydeg1",
        "phydeg2",
        "phydeg3",
        "physpe1",
        "physpe2",
        "physpe3",
        "phyid1",
        "phystatus",
        "phyref",
        "phyrefcount",
        "phyrefamt",
        "phyrefcoll",
        "phychargemap",
        "phyidmap"
	); // end of variables list

	function providerMaintenance () {
		global $phyphonea, $phyfaxa, $phyfaxb, $phycellular, $phypager,
			$physsn;
		$this->freemedMaintenanceModule();
        $phyphonea	= fm_phone_assemble("phyphonea");
        $phyfaxa	= fm_phone_assemble("phyfaxa");
        $phyphoneb	= fm_phone_assemble("phyphoneb");
        $phyfaxb	= fm_phone_assemble("phyfaxb");
        $phycellular= fm_phone_assemble("phycellular");
        $phypager	= fm_phone_assemble("phypager");
        $physsn		= $GLOBALS["physsn1"].$GLOBALS["physsn2"].$GLOBALS["physsn3"];
	} // end constructor providerMaintenance

	// send 'em to the form for add and mod, due to notebook
	function add() { $this->form(); }
	function mod() { $this->form(); }

	function form() {
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$book = new notebook (
			array ("action", "_auth", "id", "module"),
			NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR,
			4 
		);
		$book->set_submit_name("OK"); // not sure what this does...
  
  if (($action=="modform") AND (!$book->been_here())) { // load the values
    $r = freemed_get_link_rec ($id, $this->table_name);
    extract ($r);
    $phychargemap = fm_split_into_array( $r[phychargemap] );
    $phyidmap = fm_split_into_array( $r[phyidmap] );

    // disassemble ssn
    $physsn1    = substr($physsn,    0, 3);
    $physsn2    = substr($physsn,    3, 2);
    $physsn3    = substr($physsn,    5, 4);

    if (strlen($phyaddr1b)>0) $has_second_addr=true;
  } // fetch the data first time through
  
  $stat_q = "SELECT * FROM phystatus ORDER BY phystatus";
  $stat_r = $sql->query($stat_q); // have the result ready for display_selectbox

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
  $phy_deg_r = $sql->query($phy_deg_q);
  $spec_q = "SELECT * FROM specialties ORDER BY ".
            "specname, specdesc";
  $spec_r = $sql->query($spec_q);

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
  // $brackets is defined in lib/freemed.php
  $cmap_buf="";
  $int_r = $sql->query("SELECT * FROM intservtype");
  while ($i_r = $sql->fetch_array ($int_r)) {
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
  $i_res = $sql->query("SELECT * FROM inscogroup");
  while ($i_r = $sql->fetch_array ($i_res)) {
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
				$this->_mod();
			} else if ($action=="addform") {
				$this->_add();
			} else { // error
      			echo "
   	  			<P ALIGN=CENTER>
				<$STDFONT_B>"._("ERROR")."! \$action=$action!<$STDFONT_E>
				</P>
  		  		";
			} // error handler
		} // if executing the action
	} // end function providerMaintenance->form()

	function display () {
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$phy = freemed_get_link_rec($id, $this->table_name);
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
      <$STDFONT_B><A HREF=\"physician.php?$_auth&action=modform&id=$id\"
       >"._("Modify")." "._($record_name)."</A><$STDFONT_E>
     </TD></TR>
   ";
  echo "
     <TR><TD COLSPAN=2 ALIGN=CENTER>
      <$STDFONT_B><A HREF=\"physician.php?$_auth&id=$id\"
       >"._("back")."</A><$STDFONT_E>
     </TD></TR>
    </TABLE>
   </CENTER>
  ";
	} // end function providerMaintenance->display()

	function view () {
		global $sql;

  $phy_q = "SELECT phylname,phyfname,id FROM ".$this->table_name." ".
    "ORDER BY phylname,phyfname";
  $phy_r = $sql->query($phy_q);
  echo freemed_display_itemlist (
    $phy_r,
    $this->page_name,
    array (
      _("Last Name") => "phylname",
      _("First Name") => "phyfname"
    ),
    array (
      "",
      ""
    )
  );
	} // end function providerMaintenance->view()

} // end class providerMaintenance

register_module ("providerMaintenance");

} // end if defined

?>
