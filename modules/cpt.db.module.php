<?php
 // $Id$
 // desc: CPT (procedural codes) database
 // lic : GPL, v2

if (!defined("__CPT_MODULE_PHP__")) {

define (__CPT_MODULE_PHP__, true);

class cptMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME = "CPT Codes Maintenance";
	var $MODULE_VERSION = "0.1";

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
		"cpttos"
	);

	function cptMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor cptMaintenance

	function add () { $this->form(); }
	function mod () { $this->form(); }

	function form () {
		while (list($k,$v)=each($GLOBALS)) global $$k;

		$book = new notebook (
			array ("action", "_auth", "id", "module"),
			NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH);
    
  		if (!$book->been_here()) {
			switch ($action) {
				case "mod":
    			case "modform":
		// we need to do this before the extract or mod form does not
		// show the second page on existing data ??
        while(list($k,$v)=each($this->variables))
        {
            global $$v;
        }

     if ($id<1) DIE ("$page_name :: need to have id for modform");
     $this_record  = freemed_get_link_rec ($id, $this->table_name);
     extract ($this_record);
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
       "<INPUT TYPE=TEXT NAME=\"cptcode\" SIZE=8 MAXLENGTH=7
        VALUE=\"".prepare($cptcode)."\"> &nbsp;".
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
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Relative Value")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"cptrelval\" SIZE=10 MAXLENGTH=9
       VALUE=\"".prepare($cptrelval)."\">
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Default Type of Service")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
        ".freemed_display_selectbox (
          $sql->query ("SELECT tosname,tosdescrip,id FROM tos ORDER BY tosname"),
  	  "#tosname# #tosdescrip#",
	  "cptdeftos"
	  )."
      </SELECT>
     </TD>
    </TR>

    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Default Standard Fee")." : <$STDFONT_E>
     </TD><TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"cptdefstdfee\" SIZE=10 MAXLENGTH=8
       VALUE=\"".prepare($cptdefstdfee)."\">
     </TD>
    </TR>

    </TABLE>
  ");

  $book->add_page (
    _("Inclusion/Exclusion"),
    array ("cptreqicd", "cptexcicd", "cptreqcpt", "cptexccpt"),
    "<TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD ALIGN=RIGHT>
      <$STDFONT_B>"._("Diagnosis Required")." : <$STDFONT_E>
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
      <$STDFONT_B>"._("Diagnosis Excluded")." : <$STDFONT_E>
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
      <$STDFONT_B>"._("Procedural Codes Required")." : <$STDFONT_E>
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
      <$STDFONT_B>"._("Procedural Codes Excluded")." : <$STDFONT_E>
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
     $this_insco = new InsuranceCompany ($insrow[id]);
     $serv_buffer .= "
      <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
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
       <$STDFONT_B><B>"._("Procedural Code")."</B> : <$STDFONT_E></TD>
      <TD ALIGN=LEFT><$STDFONT_B>".prepare($cptcode)."<$STDFONT_E>
       <$STDFONT_B><I>(".prepare($cptnameint).")</I><$STDFONT_E></TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>"._("Default Standard Fee")." : <$STDFONT_E></TD>
      <TD ALIGN=LEFT>
       <$STDFONT_B>".bcadd($this_code["cptdefstdfee"],0,2)."<$STDFONT_E>
      </TD>
     </TR>

     <TR>
      <TD ALIGN=RIGHT>
       <$STDFONT_B>"._("Default Type of Service")." : <$STDFONT_E></TD>
      <TD ALIGN=LEFT>
       <$STDFONT_B>".freemed_get_link_field ($cptdeftos, "tos",
        "tosname")."<$STDFONT_E></TD>
     </TR>

     <TR>
      <TD COLSPAN=2><$STDFONT_B SIZE=-1><I>
       "._("Please note that selecting \"0\" or \"NONE SELECTED\" will cause the default values to be used.")."
      </I><$STDFONT_E>
     </TD></TR>
     
     </TABLE>

     <! -- fee profiles stuff here -->
     $serv_buffer

  ");
 } // end of fee profiles conditional

		if (!$book->is_done()) {
			echo $book->display();
		} else {
			switch ($action) {
				case "add": case "addform":
					$this->_add();
					break;
				case "mod": case "modform":
					$this->_mod();
					break;
			} // end switch

	} // end function cptMaintenance->form()

/*
 case "profileform": // insurance company profiles form
  $num_inscos = $sql->num_rows ($sql->query ("SELECT * FROM insco"));
  $this_code  = freemed_get_link_rec ($id, $this->table_name);
  $cpttos     = fm_split_into_array ($this_code["cpttos"]);
  $cptstdfee  = fm_split_into_array ($this_code["cptstdfee"]);
  freemed_display_box_top (_($record_name));
  echo "
   <P>
    <CENTER>
    <$STDFONT_B><B>"._("Current Code")."</B> : <$STDFONT_E>
    <A HREF=\"$page_name?$_auth&id=$id&action=modform\"
    ><$STDFONT_B>".$this_code["cptcode"]."<$STDFONT_E></A>&nbsp;
    <$STDFONT_B><I>(".$this_code["cptnameint"].")</I><$STDFONT_E>
    <BR>
    <$STDFONT_B><U>"._("Default Standard Fee")."</U> :
    ".bcadd($this_code["cptdefstdfee"],0,2)."<$STDFONT_E>
    <BR>
    <$STDFONT_B><U>"._("Default Type of Service")."</U> :
    ".freemed_get_link_field ($this_code["cptdeftos"], "tos",
      "tosname")."<$STDFONT_E>
    </CENTER> 
   <P>
   <CENTER>
    <$STDFONT_B SIZE=-1><I>
     "._("Please note that selecting \"0\" or \"NONE SELECTED\" will cause the default values to be used.")."
    </I><$STDFONT_E> 
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
   $this_insco = new InsuranceCompany ($i);
   echo "
    <TR BGCOLOR=".($_alternate=freemed_bar_alternate_color($_alternate)).">
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
  echo "
   </TABLE>
   <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\""._("Modify")."\">
     <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER>
   <P>
   </FORM>
  ";
  freemed_display_box_bottom ();
  break; // end insurance company profiles form 

 case "profile": // modification for the profile form
  freemed_display_box_top (_("Modifying")." "._($record_name));
  $query = "UPDATE $this->table_name SET
            cpttos='".fm_join_from_array($cpttos)."',
            cptstdfee='".fm_join_from_array($cptstdfee)."'
            WHERE id='$id'";
  echo "
   <P>
   <$STDFONT_B>"._("Modifying")." ... 
  ";
  $result = $sql->query ($query);
  if ($result) { echo _("done")."."; }
   else        { echo _("ERROR");    }
  echo "
   <$STDFONT_E>
   <P>
   <CENTER>
    <A HREF=\"$page_name?$_auth\"
    ><$STDFONT_B>"._("back")."<$STDFONT_E></A>
   </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break; // end of mod for the profile form
*/
	} // end function cptMaintenance->form()

	function view () {
		global $sql;

		$result = $sql->query ($query);
		echo freemed_display_itemlist (
			$sql->query ("SELECT cptcode,cptnameint,id FROM ".
				$this->table_name." ORDER BY cptcode"),
			$this->page_name,
			array (
				_("Procedural Code")	=>	"cptcode",
				_("Internal Description")	=>	"cptnameint"
			),
			array ("", "")
		);
	} // end function cptMaintenance->view()

} // end class cptMaintenance

register_module ("cptMaintenance");

} // end if not defined

?>
