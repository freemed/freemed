<?php
 // $Id$
 // desc: CPT (procedural codes) database
 // lic : GPL, v2

 // TODO: STILL NEED TO INTEGRATE REST OF FRED'S CHANGES TO THIS MODULE

LoadObjectDependency('FreeMED.MaintenanceModule');

class CptMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "CPT Codes Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name = "CPT Codes";
	var $table_name = "cpt";

	var $variables = array (
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
		"cptstdfee",
		"cpttos",
		"cpttosprfx"
	);

	function CptMaintenance () {
		$this->MaintenanceModule();
	} // end constructor CptMaintenance

	function add () { $this->form(); }
	function mod () { $this->form(); }

	function form () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global $$k;

		$book = CreateObject('PHP.notebook',
			array ("action", "id", "module"),
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
			$cptstdfee    = fm_split_into_array ($cptstdfee);
			$cpttos       = fm_split_into_array ($cpttos);
			break;
		} // end switch
		} // end checking if been here

  $book->add_page (
    _("Primary Information"),
    array ("cptcode", "cptnameint", "cptnameext", "cptgender",
           "cpttaxed", "cpttype"),
    html_form::form_table (array (
      _("Procedural Code") =>
	html_form::text_widget("cptcode", 7)." &nbsp;".
	$book->generate_refresh(),

      _("Internal Description") =>
      "<INPUT TYPE=TEXT NAME=\"cptnameint\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($cptnameint)."\">",
      _("External Description") =>
      "<INPUT TYPE=TEXT NAME=\"cptnameext\" SIZE=20 MAXLENGTH=50
       VALUE=\"".prepare($cptnameext)."\">",
      _("Gender Restriction") =>
       "<SELECT NAME=\"cptgender\">
       <OPTION VALUE=\"n\" ".
         ( ($cptgender=="n") ? "SELECTED" : "" ).">"._("no restriction")."
       <OPTION VALUE=\"f\" ".
         ( ($cptgender=="f") ? "SELECTED" : "" ).">"._("female only")."
       <OPTION VALUE=\"m\" ".
         ( ($cptgender=="m") ? "SELECTED" : "" ).">"._("male only")."
      </SELECT>",
      _("Taxed?") =>
      "<SELECT NAME=\"cpttaxed\">
       <OPTION VALUE=\"n\" ".
         ( ($cpttaxed=="n") ? "SELECTED" : "" ).">"._("no")."
       <OPTION VALUE=\"y\" ".
         ( ($cpttaxed=="y") ? "SELECTED" : "" ).">"._("yes")."
      </SELECT>",
      _("Internal Service Types") =>
     freemed_display_selectbox(
       $sql->query("SELECT * FROM intservtype"),
       "#intservtype#",
       "cpttype")
       ))
  );

		$book->add_page (
			_("Billing Information"),
			array ("cptrelval", "cptdeftos", "cptdefstdfee"),
			html_form::form_table(array(
	_("Relative Value") =>
	html_form::text_widget("cptrelval", 9),

	_("Default Type of Service") =>
	freemed_display_selectbox (
          $sql->query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
  	  "#tosname# #tosdescrip#",
	  "cptdeftos"
	),

	_("Default Standard Fee") =>
	html_form::text_widget("cptdefstdfee", 8)
			))
		);

  $book->add_page (
    _("Inclusion/Exclusion"),
    array ("cptreqicd", "cptexcicd", "cptreqcpt", "cptexccpt"),
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT>
      "._("Diagnosis Required")." : 
     </TD><TD ALIGN=LEFT>
   ".freemed_multiple_choice ("SELECT * FROM icd9
                               ORDER BY icd9code,icd9descrip",
                              "icd9code:icd9descrip",
                              "cptreqicd",
                              $cptreqicd,
                              false)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      "._("Diagnosis Excluded")." : 
     </TD><TD ALIGN=LEFT>
   ".freemed_multiple_choice ("SELECT * FROM icd9
                              ORDER BY icd9code,icd9descrip",
                             "icd9code:icd9descrip",
                             "cptexcicd",
                             $cptexcicd,
                             true)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      "._("Procedural Codes Required")." : 
     </TD><TD ALIGN=LEFT>
   ".freemed_multiple_choice ("SELECT * FROM cpt
                               ORDER BY cptnameint,cptcode",
                              "cptcode:cptnameint",
                              "cptreqcpt",
                              $cptreqcpt,
                              false)."
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      "._("Procedural Codes Excluded")." : 
     </TD><TD ALIGN=LEFT>
   ".freemed_multiple_choice ("SELECT * FROM cpt
                               ORDER BY cptcode,cptnameint",
                              "cptcode:cptnameint",
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
      <TD><B>"._("Insurance Company")."</B>&nbsp;</TD>
      <TD><B>"._("Type of Service")."</B>&nbsp;</TD>
      <TD><B>"._("Standard Fee")."</B></TD>
     </TR>
    ";
	$i = 1;
    while ($insrow = $sql->fetch_array($insco_result)) { // loop thru inscos
     if (empty($cptstdfee[$i])) $cptstdfee[$i] = "0.00";
     $this_insco = CreateObject('FreeMED.InsuranceCompany', $insrow[id]);
     $serv_buffer .= "
      <TR CLASS=\"".freemed_alternate()."\">
       <TD>".prepare($this_insco->insconame)."</TD>
       <TD>
        ".freemed_display_selectbox (
          $sql->query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
  	  "#tosname# #tosdescrip#",
	  "cpttos[$i]"
	  )."
       </TD>
       <TD>
        <INPUT TYPE=TEXT NAME=\"cptstdfee$brackets\" SIZE=10
         MAXLENGTH=9 VALUE=\"".prepare($cptstdfee[$i])."\">
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
    _("Fee Profiles"),
    array (""),
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2
      ALIGN=CENTER>

      <!-- first values, to push offset to 1 from 0 -->
      <INPUT TYPE=HIDDEN NAME=\"cpttos$brackets\"    VALUE=\"\">
      <INPUT TYPE=HIDDEN NAME=\"cptstdfee$brackets\" VALUE=\"\">

     <TR>
      <TD ALIGN=RIGHT WIDTH=\"50%\">
       <B>"._("Procedural Code")."</B> : </TD>
      <TD ALIGN=LEFT>".prepare($cptcode)."
       <I>(".prepare($cptnameint).")</I></TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       "._("Default Standard Fee")." : </TD>
      <TD ALIGN=LEFT>
       ".bcadd($cptdefstdfee,0,2)."
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       "._("Default Type of Service")." : </TD>
      <TD ALIGN=LEFT>
       ".freemed::get_link_field ($cptdeftos, "tos", "tosname")."</TD>
     </TR>

     <TR>
      <TD COLSPAN=2><FONT SIZE=-1><I>
       "._("Please note that selecting \"0\" or \"NONE SELECTED\" will cause the default values to be used.")."
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

	} // end function CptMaintenance->form()

/*
 case "profileform": // insurance company profiles form
  $num_inscos = $sql->num_rows ($sql->query ("SELECT * FROM insco"));
  $this_code  = freemed::get_link_rec ($id, $this->table_name);
  $cpttos     = fm_split_into_array ($this_code["cpttos"]);
  $cptstdfee  = fm_split_into_array ($this_code["cptstdfee"]);
  $page_title = _($record_name);
  $display_buffer .= "
   <P>
    <CENTER>
    <B>"._("Current Code")."</B> :
    <A HREF=\"$page_name?id=$id&action=modform\"
    >".$this_code["cptcode"]."</A>&nbsp;
    <I>(".$this_code["cptnameint"].")</I>
    <BR>
    <U>"._("Default Standard Fee")."</U> :
    ".bcadd($this_code["cptdefstdfee"],0,2)."
    <BR>
    <U>"._("Default Type of Service")."</U> :
    ".freemed::get_link_field ($this_code["cptdeftos"], "tos", "tosname")."
    </CENTER> 
   <P>
   <CENTER>
    <FONT SIZE=-1><I>
     "._("Please note that selecting \"0\" or \"NONE SELECTED\" will cause the default values to be used.")."
    </I></FONT> 
   </CENTER>
   <P>
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"profile\">

    <!-- first values, to push offset to 1 from 0 -->
    <INPUT TYPE=HIDDEN NAME=\"cpttos$brackets\"    VALUE=\"\">
    <INPUT TYPE=HIDDEN NAME=\"cptstdfee$brackets\" VALUE=\"\">

   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 VALIGN=MIDDLE
    ALIGN=CENTER>
   <TR>
    <TD><B>"._("Insurance Company")."</B>&nbsp;</TD>
    <TD><B>"._("Type of Service")."</B>&nbsp;</TD>
    <TD><B>"._("Standard Fee")."</B></TD>
   </TR>
  ";
  for ($i=1;$i<=$num_inscos;$i++) { // loop thru inscos
   if (empty($cptstdfee[$i])) $cptstdfee[$i] = "0.00";
   $this_insco = CreateObject('FreeMED.InsuranceCompany', $i);
   $display_buffer .= "
    <TR CLASS=\"".freemed_alternate()."\">
     <TD>".prepare($this_insco->insconame)."</TD>
     <TD>
      ".freemed_display_selectbox (
        $sql->query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
	"#tosname# #tosdescrip#",
	"cpttos[$i]"
	)."
     </TD>
     <TD>
      <INPUT TYPE=TEXT NAME=\"cptstdfee$brackets\" SIZE=10
       MAXLENGTH=9 VALUE=\"".prepare($cptstdfee[$i])."\">
     </TD>
    </TR>
   ";
  } // end loop thru inscos
  $display_buffer .= "
   </TABLE>
   <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\""._("Modify")."\">
     <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER>
   <P>
   </FORM>
  ";
  break; // end insurance company profiles form 

 case "profile": // modification for the profile form
  $page_title =  _("Modifying")." "._($record_name);
  $query = "UPDATE $this->table_name SET
            cpttos='".fm_join_from_array($cpttos)."',
            cptstdfee='".fm_join_from_array($cptstdfee)."'
            WHERE id='$id'";
  $display_buffer .= "
   <P>
   "._("Modifying")." ... 
  ";
  $result = $sql->query ($query);
  if ($result) { $display_buffer .= _("done")."."; }
   else        { $display_buffer .= _("ERROR");    }
  $display_buffer .= "
   <P>
   <CENTER>
    <A HREF=\"$page_name\"
    >"._("back")."</A>
   </CENTER>
   <P>
  ";
  break; // end of mod for the profile form
*/
	} // end function CptMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;

		$result = $sql->query ($query);
		$display_buffer .= freemed_display_itemlist (
			$sql->query ("SELECT cptcode,cptnameint,id FROM ".
				$this->table_name." ORDER BY cptcode"),
			$this->page_name,
			array (
				_("Procedural Code")	=>	"cptcode",
				_("Internal Description")	=>	"cptnameint"
			),
			array ("", "")
		);
	} // end function CptMaintenance->view()

} // end class CptMaintenance

register_module ("CptMaintenance");

?>
