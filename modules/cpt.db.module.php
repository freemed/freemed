<?php
	// $Id$
	// lic : GPL, v2

LoadObjectDependency('_FreeMED.MaintenanceModule');

class CptMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "CPT Codes Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "CPT Codes";
	var $table_name = "cpt";
	var $order_fields = "cptcode";
	var $widget_hash = "##cptcode## ##cptnameint##";

	function CptMaintenance () {
		$this->variables = array (
			"cptcode",
			"cptnameint",
			"cptnameext",
			"cptgender",
			"cpttaxed",
			"cpttype",
			"cptreqcpt",
			"cptexccpt",
			"cptreqicd",
			"cptexcicd",
			"cptrelval",
			"cptdeftos",
			"cptdefstdfee",
			"cptstdfee" => serialize($_REQUEST['cptstdfee']),
			"cpttos" => serialize($_REQUEST['cpttos']),
			"cpttosprfx"
		);

		// Table definition
		$this->table_definition = array (
			'cptcode' => SQL__CHAR(7),
			'cptnameint' => SQL__VARCHAR(50),
			'cptnameext' => SQL__VARCHAR(50),
			'cptgender' => SQL__ENUM(array('n', 'm', 'f')),
			'cpttaxed' => SQL__ENUM(array('n', 'y')),
			'cpttype' => SQL__INT_UNSIGNED(0),
			'cptreqcpt' => SQL__TEXT,
			'cptexccpt' => SQL__TEXT,
			'cptreqicd' => SQL__TEXT,
			'cptexcicd' => SQL__TEXT,
			'cptrelval' => SQL__REAL,
			'cptdeftos' => SQL__INT_UNSIGNED(0),
			'cptdefstdfee' => SQL__REAL,
			'cptstdfee' => SQL__TEXT,
			'cpttos' => SQL__TEXT,
			'cpttosprfx' => SQL__TEXT,
			'id' => SQL__SERIAL
		);
	
		// Run parent constructor
		$this->MaintenanceModule();
	} // end constructor CptMaintenance

	function add () { $this->form(); }
	function mod () { $this->form(); }

	function form () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;

		$book = CreateObject('PHP.notebook',
			array ("action", "id", "module", "return"),
			NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH);
    
  		if (!$book->been_here()) {
			switch ($action) {
			case "mod":
    			case "modform":
		// we need to do this before the extract or mod form does not
		// show the second page on existing data ??
		while(list($k,$v)=each($this->variables)) { global ${$v}; }

			if ($id<1) {
		$display_buffer .= "$page_name :: need to have id for modform";
		template_display();
			}
			$this_record  = freemed::get_link_rec ($id,
				$this->table_name);
			foreach ($this_record AS $k => $v) {
				global ${$k};
				${$k} = stripslashes($v);
			}
			$cptreqcpt    = fm_split_into_array ($cptreqcpt);
			$cptexccpt    = fm_split_into_array ($cptexccpt);
			$cptreqicd    = fm_split_into_array ($cptreqicd);
			$cptexcicd    = fm_split_into_array ($cptexcicd);
			$cptrelval    = bcadd($cptrelval, 0, 2);
			$cptstdfee    = unserialize($cptstdfee);
			$cpttos       = unserialize($cpttos);
			break;
		} // end switch
		} // end checking if been here

  $book->add_page (
    __("Primary Information"),
    array ("cptcode", "cptnameint", "cptnameext", "cptgender",
           "cpttaxed", "cpttype"),
    html_form::form_table (array (
      __("Procedural Code") =>
	html_form::text_widget("cptcode", 7)." &nbsp;".
	$book->generate_refresh(),

      __("Internal Description") =>
      "<INPUT TYPE=TEXT NAME=\"cptnameint\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($cptnameint)."\">",
      __("External Description") =>
      "<INPUT TYPE=TEXT NAME=\"cptnameext\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($cptnameext)."\">",
      __("Gender Restriction") =>
       "<SELECT NAME=\"cptgender\">
       <OPTION VALUE=\"n\" ".
         ( ($cptgender=="n") ? "SELECTED" : "" ).">".__("no restriction")."
       <OPTION VALUE=\"f\" ".
         ( ($cptgender=="f") ? "SELECTED" : "" ).">".__("female only")."
       <OPTION VALUE=\"m\" ".
         ( ($cptgender=="m") ? "SELECTED" : "" ).">".__("male only")."
      </SELECT>",
      __("Taxed?") =>
      "<SELECT NAME=\"cpttaxed\">
       <OPTION VALUE=\"n\" ".
         ( ($cpttaxed=="n") ? "SELECTED" : "" ).">".__("no")."
       <OPTION VALUE=\"y\" ".
         ( ($cpttaxed=="y") ? "SELECTED" : "" ).">".__("yes")."
      </SELECT>",
      __("Internal Service Types") =>
     freemed_display_selectbox(
       $sql->query("SELECT * FROM intservtype"),
       "#intservtype#",
       "cpttype")
       ))
  );

		$book->add_page (
			__("Billing Information"),
			array ("cptrelval", "cptdeftos", "cptdefstdfee"),
			html_form::form_table(array(
	__("Relative Value") =>
	html_form::text_widget("cptrelval", 9),

	__("Default Type of Service") =>
	freemed_display_selectbox (
          $sql->query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
  	  "#tosname# #tosdescrip#",
	  "cptdeftos"
	),

	__("Default Standard Fee") =>
	html_form::text_widget("cptdefstdfee", 8)
			))
		);

  $book->add_page (
    __("Inclusion/Exclusion"),
    array ("cptreqicd", "cptexcicd", "cptreqcpt", "cptexccpt"),
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT>
      ".__("Diagnosis Required")." : 
     </TD><TD ALIGN=LEFT>
   ".freemed::multiple_choice ("SELECT * FROM icd9 ".
                              "ORDER BY icd9code,icd9descrip",
                              "##icd9code## (##icd9descrip##)",
                              "cptreqicd",
                              $cptreqicd,
                              false)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      ".__("Diagnosis Excluded")." : 
     </TD><TD ALIGN=LEFT>
   ".freemed::multiple_choice ("SELECT * FROM icd9 ".
                             "ORDER BY icd9code,icd9descrip",
                             "##icd9code## (##icd9descrip##)",
                             "cptexcicd",
                             $cptexcicd,
                             true)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      ".__("Procedural Codes Required")." : 
     </TD><TD ALIGN=LEFT>
   ".freemed::multiple_choice ("SELECT * FROM cpt ".
                              "ORDER BY cptnameint,cptcode",
                              "##cptcode## (##cptnameint##)",
                              "cptreqcpt",
                              $cptreqcpt,
                              false)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      ".__("Procedural Codes Excluded")." : 
     </TD><TD ALIGN=LEFT>
   ".freemed::multiple_choice ("SELECT * FROM cpt ".
                              "ORDER BY cptcode,cptnameint",
                              "##cptcode## (##cptnameint##)",
                              "cptexccpt",
                              $cptexccpt,
                              true)."
     </TD>
    </TR>

    </TABLE>
  ");

  if ( (!empty($cptcode)) and (!empty($cptnameint)) ) {
	$insco_result = $sql->query ("SELECT * FROM insco");
    $serv_buffer = "
     <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
      <TD><B>".__("Insurance Company")."</B>&nbsp;</TD>
      <TD><B>".__("Type of Service")."</B>&nbsp;</TD>
      <TD><B>".__("Standard Fee")."</B></TD>
     </TR>
    ";
	$i = 1;
    while ($insrow = $sql->fetch_array($insco_result)) { // loop thru inscos
     if (empty($cptstdfee[$insrow['id']])) $cptstdfee[$insrow['id']] = "0.00";
     $this_insco = CreateObject('FreeMED.InsuranceCompany', $insrow['id']);
     $serv_buffer .= "
      <TR CLASS=\"".freemed_alternate()."\">
       <TD>".prepare($this_insco->insconame)."</TD>
       <TD>
        ".freemed_display_selectbox (
          $sql->query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
  	  "#tosname# #tosdescrip#",
	  "cpttos[".$insrow['id']."]"
	  )."
       </TD>
       <TD>
        <INPUT TYPE=TEXT NAME=\"cptstdfee[".$insrow['id']."]\" SIZE=10
         MAXLENGTH=9 VALUE=\"".prepare($cptstdfee[$insrow['id']])."\">
       </TD>
      </TR>
     ";
	  $i++; // next cptstdfee
    } // end loop thru inscos
    $serv_buffer .= "
     </TABLE>
    ";

	global $cptdefstdfee;
  $book->add_page (
    __("Fee Profiles"),
    array (""),
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2
      ALIGN=CENTER>

     <TR>
      <TD ALIGN=RIGHT WIDTH=\"50%\">
       <B>".__("Procedural Code")."</B> : </TD>
      <TD ALIGN=LEFT>".prepare($cptcode)."
       <I>(".prepare($cptnameint).")</I></TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       ".__("Default Standard Fee")." : </TD>
      <TD ALIGN=LEFT>
       ".bcadd($cptdefstdfee,0,2)."
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       ".__("Default Type of Service")." : </TD>
      <TD ALIGN=LEFT>
       ".freemed::get_link_field ($cptdeftos, "tos", "tosname")."</TD>
     </TR>

     <TR>
      <TD COLSPAN=2><FONT SIZE=-1><I>
       ".__("Please note that selecting \"0\" or \"NONE SELECTED\" will cause the default values to be used.")."
      </I></FONT>
     </TD></TR>
     
     </TABLE>

     <! -- fee profiles stuff here -->
     $serv_buffer

  ");
 } // end of fee profiles conditional

		// Handle cancel
		if ($book->is_cancelled()) {
			Header("Location: ".$this->page_name."?patient=".
				urlencode($patient)."&module=".
				urlencode($this->MODULE_CLASS));
			die("");
		}

		if (!$book->is_done()) {
			$display_buffer .= $book->display();
		} else {
			switch ($action) {
				case "add": case "addform":
					$this->_add();
					break;
				case "mod": case "modform":
					$this->_mod();
					break;
			} // end switch
		}
	} // end function CptMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;

		$result = $sql->query ($query);
		$display_buffer .= freemed_display_itemlist (
			$sql->query (
				"SELECT cptcode,cptnameint,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY ".$this->order_fields
			),
			$this->page_name,
			array (
				__("Procedural Code")	=>	"cptcode",
				__("Internal Description")	=>	"cptnameint"
			),
			array ("", "")
		);
	} // end function CptMaintenance->view()

} // end class CptMaintenance

register_module ("CptMaintenance");

?>
