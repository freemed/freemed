<?php
	// $Id$
	// code: jeff b (jeff@ourexchange.net), adam b (gdrago23@yahoo.com)

LoadObjectDependency('_FreeMED.MaintenanceModule');

class ProviderModule extends MaintenanceModule {

	var $MODULE_NAME    = "Provider Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3.4";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name    = "Provider";
	var $table_name     = "physician";
	var $variables      = array (
        "phylname",
        "phyfname",
        "phytitle",
        "phymname",
        "phypracname",
	"phypracein",
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
        "phyidmap",
	"phyanesth",
	"phyhl7id",
	"phydea"
	); // end of variables list
	var $order_field = 'phylname, phyfname';

	// XML-RPC field mapping
	var $rpc_field_map = array (
		'last_name' => 'phylname',
		'first_name' => 'phyfname',
		'middle_name' => 'phymname',
		'city' => 'phycitya',
		'state' => 'phystatea',
		'zip' => 'phyzipa',
		'practice' => 'phypracname'
	);
	var $widget_hash = '##phylname##, ##phyfname## ##phymname## (##phypracname##)';

	function ProviderModule () {
		// Table definition
		$this->table_definition = array (
			'phylname' => SQL__VARCHAR(52),
			'phyfname' => SQL__VARCHAR(50),
			'phymname' => SQL__VARCHAR(50),
			'phytitle' => SQL__VARCHAR(10),
			'phypracname' => SQL__VARCHAR(80),
			'phypracein' => SQL__VARCHAR(16),
				// Address 1
			'phyaddr1a' => SQL__VARCHAR(30),
			'phyaddr2a' => SQL__VARCHAR(30),
			'phycitya' => SQL__VARCHAR(20),
			'phystatea' => SQL__CHAR(5),
			'phyzipa' => SQL__VARCHAR(10),
			'phyphonea' => SQL__VARCHAR(16),
			'phyfaxa' => SQL__VARCHAR(16),
				// Address 2
			'phyaddr1b' => SQL__VARCHAR(30),
			'phyaddr2b' => SQL__VARCHAR(30),
			'phycityb' => SQL__VARCHAR(20),
			'phystateb' => SQL__CHAR(5),
			'phyzipb' => SQL__VARCHAR(10),
			'phyphoneb' => SQL__VARCHAR(16),
			'phyfaxb' => SQL__VARCHAR(16),
				// Misc
			'phyemail' => SQL__VARCHAR(50),
			'phycellular' => SQL__VARCHAR(16),
			'phypager' => SQL__VARCHAR(16),
			'phyupin' => SQL__VARCHAR(15),
			'physsn' => SQL__CHAR(9),
				// Degrees
			'phydeg1' => SQL__INT_UNSIGNED(0),
			'phydeg2' => SQL__INT_UNSIGNED(0),
			'phydeg3' => SQL__INT_UNSIGNED(0),
				// Specialties
			'physpe1' => SQL__INT_UNSIGNED(0),
			'physpe2' => SQL__INT_UNSIGNED(0),
			'physpe3' => SQL__INT_UNSIGNED(0),
				// Misc
			'phyid1' => SQL__CHAR(10),
			'phystatus' => SQL__INT_UNSIGNED(0),
			'phyref' => SQL__ENUM(array('yes', 'no')),
			'phyrefcount' => SQL__INT_UNSIGNED(0),
			'phyrefamt' => SQL__REAL,
			'phyrefcoll' => SQL__REAL,
			'phychargemap' => SQL__TEXT,
			'phyidmap' => SQL__TEXT,
			'phygrpprac' => SQL__INT_UNSIGNED(0),
			'phyanesth' => SQL__INT_UNSIGNED(0),
			'phyhl7id' => SQL__VARCHAR(16),
			'phydea' => SQL__VARCHAR(16),
			'id' => SQL__SERIAL
		);

		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor ProviderModule

	// send 'em to the form for add and mod, due to notebook
	function add() { $this->form(); }
	function mod() { $this->form(); }

	function form() {
		global $display_buffer, $action;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		$book = CreateObject('PHP.notebook',
			array ("action", "id", "module"),
			NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR,
			4 
		);
		switch ($action) {
			case "add": case "addform":
				$book->set_submit_name(__("Add")); break;
			case "mod": case "modform":
				$book->set_submit_name(__("Modify")); break;
		}
  
 		// load the values
		if (($action=="modform") AND (!$book->been_here())) {
			reset ($this->variables);
			while(list($k,$v)=each($this->variables)) global ${$v};
			global $physsn1,$physsn2,$physsn3;

			$r = freemed::get_link_rec ($id, $this->table_name);
			extract ($r);
			$phychargemap = fm_split_into_array( $r[phychargemap] );
			$phyidmap = unserialize($r['phyidmap']);

			// disassemble ssn
			$physsn1    = substr($physsn, 0, 3);
			$physsn2    = substr($physsn, 3, 2);
			$physsn3    = substr($physsn, 5, 4);

			if (strlen($phyaddr1b)>0) $has_second_addr=true;
		} // fetch the data first time through
  
		// have the result ready for display_selectbox
		$stat_r = $sql->query("SELECT * FROM phystatus ORDER BY phystatus");

		$book->add_page (
			__("Primary Information"),
			array (
			"phylname", "phyfname", "phytitle", "phymname",
			"phytitle", "phypracname", "phyid1", "phystatus",
			"phypracein"
			),
			html_form::form_table(array(
	__("Last Name") =>
	html_form::text_widget("phylname", 25, 50),

	__("First Name") =>
	html_form::text_widget("phyfname", 25, 50),

	__("Middle Name") =>
	html_form::text_widget("phymname", 25, 50),

	__("Title") =>
	html_form::text_widget("phytitle", 10),

	__("Practice Name") =>
	html_form::text_widget("phypracname", 45, 80),

	__("Practice EIN") =>
	html_form::text_widget("phypracein", 16, 16),

	__("Internal ID #") =>
	html_form::text_widget("phyid1", 10),

	__("Status") =>
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
		__("Email Address") =>
		html_form::text_widget("phyemail", 25, 30),

		__("Cellular Phone #") =>
		fm_phone_entry ("phycellular"),

		__("Beeper / Pager #") =>
		fm_phone_entry ("phypager")
			))
		);

		$book->add_page (
			__("Address"),
			array_merge ( array(
				"has_second_addr", "phyaddr1a", "phyaddr2a",
				"phycitya", "phystatea", "phyzipa"),
				phone_vars("phyphonea"),
				phone_vars("phyfaxa")
			),
		html_form::form_table(array(
	__("Primary Address Line 1") =>
	html_form::text_widget("phyaddr1a", 25, 30),

	__("Primary Address Line 2") =>
	html_form::text_widget("phyaddr2a", 25, 30),

	__("Primary Address City") =>
	html_form::text_widget("phycitya", 20),

	__("Primary Address State") =>
	html_form::state_pulldown("phystatea"),

	__("Primary Address Zip") =>
	html_form::text_widget("phyzipa", 10),

	__("Primary Address Phone #") =>
	fm_phone_entry ("phyphonea"),

	__("Primary Address Fax #") =>
	fm_phone_entry ("phyfaxa"),

	__("Has Second Address") =>
	"<INPUT TYPE=CHECKBOX NAME=\"has_second_addr\" ".
	($has_second_addr ? "CHECKED" : "").">" 
			))
		);

  if ($has_second_addr)
    $book->add_page (
      __("Address 2"),
      array_merge ( 
		phone_vars("phyphoneb"),
		phone_vars("phyfaxb"),
       array("phyaddr1b", "phyaddr2b", "phycityb", "phystateb", "phyzipb"
      ) ),
    "
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <TR><TD ALIGN=RIGHT>
    ".__("Secondary Address Line 1")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr1b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr1b\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    ".__("Secondary Address Line 2")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyaddr2b SIZE=25 MAXLENGTH=30
     VALUE=\"$phyaddr2b\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    ".__("Secondary Address City")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phycityb SIZE=20 MAXLENGTH=20
     VALUE=\"$phycityb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    ".__("Secondary Address State")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phystateb SIZE=6 MAXLENGTH=5
     VALUE=\"$phystateb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    ".__("Secondary Address Zip")." :
    </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=phyzipb SIZE=10 MAXLENGTH=10
     VALUE=\"$phyzipb\">
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    ".__("Secondary Address Phone #")." :
    </TD><TD ALIGN=LEFT>
    ".fm_phone_entry ("phyphoneb")."
    </TD></TR>
    <TR><TD ALIGN=RIGHT>
    ".__("Secondary Address Fax #")." :
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
			__("Personal"),
			array (
				"phyupin", "phyref",
				"physsn1", "physsn2", "physsn3", 
				"phydeg1", "phydeg2", "phydeg3",
				"physpe1", "physpe2", "physpe3",
				"phyanesth", "phyhl7id", "phydea"
			),
			html_form::form_table(array(
		__("UPIN Number") =>
		html_form::text_widget("phyupin", 15),

		__("Social Security #") =>
		html_form::text_widget("physsn1", 3)." <B>-</B> ".
		html_form::text_widget("physsn2", 2)." <B>-</B> ".
		html_form::text_widget("physsn3", 4),

		__("Degree 1") =>
		freemed_display_selectbox ($phy_deg_r, 
			"#degdegree#, #degname#", "phydeg1"),

		__("Degree 2") =>
		freemed_display_selectbox ($phy_deg_r, 
			"#degdegree#, #degname#", "phydeg2"),

		__("Degree 3") =>
		freemed_display_selectbox ($phy_deg_r, 
			"#degdegree#, #degname#", "phydeg3"),

		__("Specialty 1") =>
		freemed_display_selectbox ($spec_r, 
			"#specname#, #specdesc#", "physpe1"),

		__("Specialty 2") =>
		freemed_display_selectbox ($spec_r, 
			"#specname#, #specdesc#", "physpe2"),

		__("Specialty 3") =>
		freemed_display_selectbox ($spec_r, 
			"#specname#, #specdesc#", "physpe3"),

		__("Physician Internal/External") =>
		html_form::select_widget(
			"phyref",
			array (
				__("In-House") => "no",
				__("Referring") => "yes"
			)
		),

		__("Anesthesiologist") =>
		html_form::select_widget(
			"phyanesth",
			array(
				__("no") => "0",
				__("yes") => "1"
			)
		),

		__("HL7 Identifier") =>
		html_form::text_widget( 'phyhl7id' ),

		__("DEA Number") =>
		html_form::text_widget( 'phydea' )

			))
		);

  // cache this outside of the function call (can't abstract that while-loop)
  // $brackets is defined in lib/freemed.php
  $cmap_buf="";
  $int_r = $sql->query("SELECT * FROM intservtype");
  while ($i_r = $sql->fetch_array ($int_r)) {
    $i_id = $i_r ["id"];
    $cmap_buf .= "
     <TR CLASS=".freemed_alternate().">
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
    __("Charge Map"),
    array (
      "phychargemap"
    ),
    "
    <INPUT TYPE=HIDDEN NAME=\"phychargemap$brackets\" VALUE=\"0\">

   <CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2
    CLASS=\"reverse\"> <!-- black border --><TR><TD>
    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR CLASS=\"cell_hilite\">
     <TD><B>".__("Internal Type")."</B></TD>
     <TD><B>".__("Amount")."</B></TD>
    </TR>
    $cmap_buf
    </TABLE>
   </TD></TR></TABLE></CENTER>
    "
  );

  $insmap_buf = ""; // cache the output, as above
  $i_res = $sql->query("SELECT * FROM inscogroup");
  while ($i_r = $sql->fetch_array ($i_res)) {
    $i_id = $i_r ['id'];
    $insmap_buf .= "
     <TR CLASS=\"".freemed_alternate()."\">
      <TD>".prepare($i_r["inscogroup"])."</TD>
      <TD>
       <INPUT TYPE=TEXT NAME=\"phyidmap[".$i_id."]\"
        SIZE=15 MAXLENGTH=30 VALUE=\"".prepare($phyidmap[$i_id])."\">
      </TD>
     </TR>
    ";
  } // end looping for service types

  $book->add_page(
    __("Insurance IDs"),
    array (
      "phyidmap"
    ),
    "
  <CENTER><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 
   CLASS=\"reverse\"> <!-- black border --><TR><TD>

    <!-- hide record zero, since it isn't used... -->
    <INPUT TYPE=HIDDEN NAME=\"phyidmap[0]\" VALUE=\"0\">

    <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR CLASS=\"cell_hilite\">
     <TD><B>".__("Insurance Group")."</B></TD>
     <TD><B>".__("ID Number")."</B></TD>
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
			$phyidmap       = serialize($_REQUEST['phyidmap']);
  			if ($action=="modform") {
				$this->_mod();
			} else if ($action=="addform") {
				$this->_add();
			} else { // error
      			$display_buffer .= "
   	  			<P ALIGN=CENTER>
				".__("ERROR")."! \$action=$action!
				</P>
  		  		";
			} // error handler
		} // if executing the action
	} // end function ProviderModule->form()

	function display () {
		global $display_buffer;
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$phy = freemed::get_link_rec($id, $this->table_name);
		$display_buffer .= "
   <CENTER>
    <TABLE WIDTH=\"100%\">
     <TR><TD ALIGN=RIGHT>
      ".__("Name")." :
     </TD><TD ALIGN=LEFT>
      $phy[phyfname] 
          $phy[phymname] $phy[phylname], $phy[phytitle]
     </TD></TR>
  ";
  if (freemed::acl('support', 'modify'))
   $display_buffer .= "
     <TR><TD COLSPAN=2 ALIGN=CENTER>
      <A HREF=\"physician.php?action=modform&id=$id\"
       >".__("Modify")." ".$record_name."</A>
     </TD></TR>
   ";
  $display_buffer .= "
     <TR><TD COLSPAN=2 ALIGN=CENTER>
      <A HREF=\"physician.php?id=$id\"
       >".__("back")."</A>
     </TD></TR>
    </TABLE>
   </CENTER>
  ";
	} // end function ProviderModule->display()

	function view () {
		global $display_buffer;
		global $sql;

		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT phylname,phyfname,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY phylname,phyfname"
			),
			$this->page_name,
			array (
				__("Last Name") => "phylname",
				__("First Name") => "phyfname"
			),
			array ( "", "" )
		);
	} // end function ProviderModule->view()

	function _update ( ) {
		global $sql;
		$version = freemed::module_version($this->MODULE_NAME);

		// Version 0.3
		//
		//	Add hl7 id field
		//
		if (!version_check($version, '0.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN phyhl7id INT UNSIGNED AFTER phyanesth');
		}

		// Version 0.3.1
		//
		//	Add DEA number for drugs
		//	Change practice name to max 45 characters
		//
		if (!version_check($version, '0.3.1')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN phydea VARCHAR(16) AFTER phyhl7id');
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN phypracname phypracname VARCHAR(45)');
		}

		// Version 0.3.2
		//
		//	Add practice EIN number
		//
		if (!version_check($version, '0.3.2')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'ADD COLUMN phypracein VARCHAR(16) AFTER phypracname');
		}

		// Version 0.3.3
		//
		//	HL7 ID needs to be alpha
		//
		if (!version_check($version, '0.3.3')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN phyhl7id phyhl7id VARCHAR(16)');
		}

		// Version 0.3.4
		//
		//	Extend practice name to 80 chars
		//
		if (!version_check($version, '0.3.4')) {
			$sql->query('ALTER TABLE '.$this->table_name.' '.
				'CHANGE COLUMN phypracname phypracname VARCHAR(80)');
		}

	} // end method _update

} // end class ProviderModule

register_module ("ProviderModule");

?>
