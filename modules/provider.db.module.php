<?php
  // $Id$
  // note: provider (formerly physician) database services
  // code: jeff b (jeff@univrel.pr.uconn.edu)
  //       adam b (gdrago23@yahoo.com)
  // translation: max k <amk@span.ch>
  // lic : GPL

if (!defined("__PROVIDER_MODULE_PHP__")) {

define(__PROVIDER_MODULE_PHP__, true);

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
		foreach ($GLOBALS AS $k => $v) global $$k;
		$this->freemedMaintenanceModule();
	} // end constructor providerMaintenance

	// send 'em to the form for add and mod, due to notebook
	function add() { $this->form(); }
	function mod() { $this->form(); }

	function form() {
		global $display_buffer, $action;
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$book = new notebook (
			array ("action", "id", "module"),
			NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR,
			4 
		);
		switch ($action) {
			case "add": case "addform":
				$book->set_submit_name(_("Add")); break;
			case "mod": case "modform":
				$book->set_submit_name(_("Modify")); break;
		}
  
 		// load the values
		if (($action=="modform") AND (!$book->been_here())) {
			reset ($this->variables);
			while(list($k,$v)=each($this->variables)) global ${$v};
			global $physsn1,$physsn2,$physsn3;

			$r = freemed::get_link_rec ($id, $this->table_name);
			extract ($r);
			$phychargemap = fm_split_into_array( $r[phychargemap] );
			$phyidmap = fm_split_into_array( $r[phyidmap] );

			// disassemble ssn
			$physsn1    = substr($physsn,    0, 3);
			$physsn2    = substr($physsn,    3, 2);
			$physsn3    = substr($physsn,    5, 4);

			if (strlen($phyaddr1b)>0) $has_second_addr=true;
		} // fetch the data first time through
  
		// have the result ready for display_selectbox
		$stat_q = "SELECT * FROM phystatus ORDER BY phystatus";
		$stat_r = $sql->query($stat_q);

		$book->add_page (
			_("Primary Information"),
			array (
			"phylname", "phyfname", "phytitle", "phymname",
			"phytitle", "phypracname", "phyid1", "phystatus"
			),
			html_form::form_table(array(
	_("Last Name") =>
	html_form::text_widget("phylname", 25, 50),

	_("First Name") =>
	html_form::text_widget("phyfname", 25, 50),

	_("Middle Name") =>
	html_form::text_widget("phymname", 25, 50),

	_("Title") =>
	html_form::text_widget("phytitle", 10),

	_("Practice Name") =>
	html_form::text_widget("phypracname", 25, 30),

	_("Internal ID #") =>
	html_form::text_widget("phyid1", 10),

	_("Status") =>
	freemed_display_selectbox($stat_r, "#phystatus#", "phystatus")
			))
		);

		$book->add_page (
			"Contact",
			array_merge (
				array( "phyemail"),
				phone_vars("phycellular"),
				phone_vars("phypager") 
			),
			html_form::form_table(array(
		_("Email Address") =>
		html_form::text_widget("phyemail", 25, 30),

		_("Cellular Phone #") =>
		fm_phone_entry ("phycellular"),

		_("Beeper / Pager #") =>
		fm_phone_entry ("phypager")
			))
		);

		$book->add_page (
			_("Address"),
			array_merge ( array(
				"has_second_addr", "phyaddr1a", "phyaddr2a",
				"phycitya", "phystatea", "phyzipa"),
				phone_vars("phyphonea"),
				phone_vars("phyfaxa")
			),
		html_form::form_table(array(
	_("Primary Address Line 1") =>
	html_form::text_widget("phyaddr1a", 25, 30),

	_("Primary Address Line 2") =>
	html_form::text_widget("phyaddr2a", 25, 30),

	_("Primary Address City") =>
	html_form::text_widget("phycitya", 20),

	_("Primary Address State") =>
	html_form::state_pulldown("phystatea"),

	_("Primary Address Zip") =>
	html_form::text_widget("phyzipa", 10),

	_("Primary Address Phone #") =>
	fm_phone_entry ("phyphonea"),

	_("Primary Address Fax #") =>
	fm_phone_entry ("phyfaxa"),

	_("Has Second Address") =>
	"<INPUT TYPE=CHECKBOX NAME=\"has_second_addr\" ".
	($has_second_addr ? "CHECKED" : "").">" 
			))
		);

  if ($has_second_addr)
    $book->add_page (
      _("Address 2"),
      array_merge ( 
		phone_vars("phyphoneb"),
		phone_vars("phyfaxb"),
       array("phyaddr1b", "phyaddr2b", "phycityb", "phystateb", "phyzipb"
      ) ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
    "._("Secondary Address Line 1")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr1b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1b\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    "._("Secondary Address Line 2")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr2b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2b\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    "._("Secondary Address City")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phycityb SIZE=20 MAXLENGTH=20
     VALUE=\"$phycityb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    "._("Secondary Address State")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phystateb SIZE=6 MAXLENGTH=5
     VALUE=\"$phystateb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    "._("Secondary Address Zip")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyzipb SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    "._("Secondary Address Phone #")." :
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyphoneb")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    "._("Secondary Address Fax #")." :
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
			html_form::form_table(array(
		_("UPIN Number") =>
		html_form::text_widget("phyupin", 15),

		_("Social Security #") =>
		html_form::text_widget("physsn1", 3)." <B>-</B> ".
		html_form::text_widget("physsn2", 2)." <B>-</B> ".
		html_form::text_widget("physsn3", 4),

		_("Degree 1") =>
		freemed_display_selectbox ($phy_deg_r, 
			"#degdegree#, #degname#", "phydeg1"),

		_("Degree 2") =>
		freemed_display_selectbox ($phy_deg_r, 
			"#degdegree#, #degname#", "phydeg2"),

		_("Degree 3") =>
		freemed_display_selectbox ($phy_deg_r, 
			"#degdegree#, #degname#", "phydeg3"),

		_("Specialty 1") =>
		freemed_display_selectbox ($spec_r, 
			"#specname#, #specdesc#", "physpe1"),

		_("Specialty 2") =>
		freemed_display_selectbox ($spec_r, 
			"#specname#, #specdesc#", "physpe2"),

		_("Specialty 3") =>
		freemed_display_selectbox ($spec_r, 
			"#specname#, #specdesc#", "physpe3"),

		_("Physician Internal/External") =>
		html_form::select_widget(
			"phyref",
			array (
				_("In-House") => "no",
				_("Referring") => "yes"
			)
		)
			))
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

	// Check for referring physician
	global $phyref;
	if (!($phyref=="yes")) 
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
		// Handle cancel action
		if ($book->is_cancelled()) {
			Header("Location: ".page_name()."?module=".$this->MODULE_CLASS);
			die("");
		}
		// now display the thing
		if (!$book->is_done()) {
			$display_buffer .= "<CENTER>\n".$book->display().
				"</CENTER>\n";
		} else { // submit has been clicked
			global $phyphonea, $phyfaxa, $phyphoneb, $phyfaxb,
				$phycellular, $phypager, $physsn;
			$phyphonea	= fm_phone_assemble("phyphonea");
			$phyfaxa	= fm_phone_assemble("phyfaxa");
			$phyphoneb	= fm_phone_assemble("phyphoneb");
			$phyfaxb	= fm_phone_assemble("phyfaxb");
			$phycellular= fm_phone_assemble("phycellular");
			$phypager	= fm_phone_assemble("phypager");
			$physsn		= $GLOBALS["physsn1"].$GLOBALS["physsn2"].$GLOBALS["physsn3"];
  			if ($action=="modform") {
				$this->_mod();
			} else if ($action=="addform") {
				$this->_add();
			} else { // error
      			$display_buffer .= "
   	  			<P ALIGN=CENTER>
				"._("ERROR")."! \$action=$action!
				</P>
  		  		";
			} // error handler
		} // if executing the action
	} // end function providerMaintenance->form()

	function display () {
		global $display_buffer;
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$phy = freemed::get_link_rec($id, $this->table_name);
		$display_buffer .= "
   <CENTER>
    <TABLE WIDTH=\"100%\">
     <TR><TD ALIGN=RIGHT>
      "._("Name")." :
     </TD><TD ALIGN=LEFT>
      $phy[phyfname] 
          $phy[phymname] $phy[phylname], $phy[phytitle]
     </TD></TR>
  ";
  if (freemed::user_flag(USER_DATABASE))
   $display_buffer .= "
     <TR><TD COLSPAN=2 ALIGN=CENTER>
      <A HREF=\"physician.php?action=modform&id=$id\"
       >"._("Modify")." "._($record_name)."</A>
     </TD></TR>
   ";
  $display_buffer .= "
     <TR><TD COLSPAN=2 ALIGN=CENTER>
      <A HREF=\"physician.php?id=$id\"
       >"._("back")."</A>
     </TD></TR>
    </TABLE>
   </CENTER>
  ";
	} // end function providerMaintenance->display()

	function view () {
		global $display_buffer;
		global $sql;

  $phy_q = "SELECT phylname,phyfname,id FROM ".$this->table_name." ".
    "ORDER BY phylname,phyfname";
  $phy_r = $sql->query($phy_q);
  $display_buffer .= freemed_display_itemlist (
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
