<?php
 // $Id$
 // desc: episode of care database module
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class EpisodeOfCare extends EMRModule {

	var $MODULE_NAME = "Episode of Care";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.3";
	var $MODULE_DESCRIPTION = "
		Episode of care is another portion of FreeMED
		designed to help with outcomes management. Any
		patients' treatment can be described through
		episodes of care, which may span any range of
		time, and more than one epsiode of care can
		be used per visit. 
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Episode of Care";
	var $table_name     = "eoc";
	var $patient_field  = "eocpatient";

	var $variables		= array (
		"eocpatient",
		"eocdescrip",
		"eocstartdate",
		"eocdtlastsimilar",
		"eocreferrer",
		"eocfacility",
		"eocdiagfamily",
		"eocrelpreg",
		"eocrelemp",
		"eocrelauto",
		"eocrelother",
		"eocrelstpr",
		"eoctype",
		"eochospital",
		"eocrelautoname",
		"eocrelautoaddr1",
		"eocrelautoaddr2",
		"eocrelautocity",
		"eocrelautostpr",
		"eocrelautozip",
		"eocrelautocountry",
		"eocrelautocase",
		"eocrelautorcname",
		"eocrelautorcphone",
		"eocrelempname",
		"eocrelempaddr1",
		"eocrelempaddr2",
		"eocrelempcity",
		"eocrelempstpr",
		"eocrelempzip",
		"eocrelempcountry",
		"eocrelempfile",
		"eocrelemprcname",
		"eocrelemprcphone",
		"eocrelemprcemail",
		"eocrelpregcycle",
		"eocrelpreggravida",
		"eocrelpregpara",
		"eocrelpregmiscarry",
		"eocrelpregabort",
		"eocrelpreglastper",
		"eocrelpregconfine",
		"eocrelothercomment",
		"eocdistype",
		"eocdisfromdt",
		"eocdistodt",
		"eocdisworkdt",
		"eochosadmdt",
		"eochosdischrgdt",
		"eocrelautotime"
	);

	function EpisodeOfCare () {
		// Table definition
		$this->table_definition = array (
			"eocpatient"		=> SQL__NOT_NULL(SQL__INT_UNSIGNED(0)),
			"eocdescrip"		=> SQL__VARCHAR(100),
			"eocstartdate"		=> SQL__DATE,
			"eocdtlastsimilar"	=> SQL__DATE,
			"eocreferrer"		=> SQL__INT_UNSIGNED(0),
			"eocfacility"		=> SQL__INT_UNSIGNED(0),
			"eocdiagfamily"		=> SQL__TEXT,
			"eocrelpreg"		=> SQL__ENUM(array("no", "yes")),
			"eocrelemp"		=> SQL__ENUM(array("no", "yes")),
			"eocrelauto"		=> SQL__ENUM(array("no", "yes")),
			"eocrelother"		=> SQL__ENUM(array("no", "yes")),
			"eocrelstpr"		=> SQL__VARCHAR(10),
			"eoctype"		=> SQL__ENUM(array(
				"acute",
				"chronic",
				"chronic recurrent",
				"historical"
			)),
			
			"eochospital"		=> SQL__INT_UNSIGNED(0),

			// Automobile
			"eocrelautoname"	=> SQL__VARCHAR(100),
			"eocrelautoaddr1"	=> SQL__VARCHAR(100),
			"eocrelautoaddr2"	=> SQL__VARCHAR(100),
			"eocrelautocity"	=> SQL__VARCHAR(50),
			"eocrelautostpr"	=> SQL__VARCHAR(30),
			"eocrelautozip"		=> SQL__VARCHAR(16),
			"eocrelautocountry"	=> SQL__VARCHAR(100),
			"eocrelautocase"	=> SQL__VARCHAR(30),
			"eocrelautorcname"	=> SQL__VARCHAR(100),
			"eocrelautorcphone"	=> SQL__VARCHAR(16),

			// Employment related
			"eocrelempname"		=> SQL__VARCHAR(100),
			"eocrelempaddr1"	=> SQL__VARCHAR(100),
			"eocrelempaddr2"	=> SQL__VARCHAR(100),
			"eocrelempcity"		=> SQL__VARCHAR(50),
			"eocrelempstpr"		=> SQL__VARCHAR(30),
			"eocrelempzip"		=> SQL__VARCHAR(10),
			"eocrelempcountry"	=> SQL__VARCHAR(100),
			"eocrelempfile"		=> SQL__VARCHAR(30),
			"eocrelemprcname"	=> SQL__VARCHAR(100),
			"eocrelemprcphone"	=> SQL__VARCHAR(16),
			"eocrelemprcemail"	=> SQL__VARCHAR(100),

			// Pregnancy
			"eocrelpregcyle"	=> SQL__INT_UNSIGNED(0),
			"eocrelpreggravida"	=> SQL__INT_UNSIGNED(0),
			"eocrelpregpara"	=> SQL__INT_UNSIGNED(0),
			"eocrelpregmiscarry"	=> SQL__INT_UNSIGNED(0),
			"eocrelpregabort"	=> SQL__INT_UNSIGNED(0),
			"eocrelpreglastper"	=> SQL__DATE,
			"eocrelpregconfine"	=> SQL__DATE,

			// Other
			"eocrelothercomment"	=> SQL__VARCHAR(100),

			// Admittance & Discharge
			"eocdistype"		=> SQL__INT_UNSIGNED(0),
			"eocdisfromdt"		=> SQL__DATE,
			"eocdistodt"		=> SQL__DATE,
			"eocdisworkdt"		=> SQL__DATE,
			"eochosadmdt"		=> SQL__DATE,
			"eochosdischrgdt"	=> SQL__DATE,
			"eocrelautotime"	=> SQL__CHAR(8),

			"id"			=> SQL__SERIAL
		);

		// Summary box for management
		$this->summary_vars = array (
			__("Orig") => "eocstartdate",
			__("Last") => "eocdtlastsimilar",
			__("Description") => "eocdescrip"
		);
		$this->summary_options = SUMMARY_VIEW;

		global $action, $submit, $module;
		if (strtolower($module)==get_class($this)) {
			switch ($submit) {
				case __("Add"):
					$action = "add";
					break;
				
				case __("Modify"):
					$action = "mod";
					break;
				
				case __("Refresh"):
				default:
					// do nothing otherwise
					break;
			}
		}

		// Run parent constructor
		$this->EMRModule();
	} // end constructor EpisodeOfCare

	function form () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) global ${$k};

		switch ($action) {
			case "addform":
			$go = "addform";
			$this_action = __("Add");
			if ($been_here != "yes") {
				 $eocstartdate = $eocdtlastsimilar = $cur_date;
			}
			break;

			case "modform":
			$go = "modform";
			$this_action = __("Modify");
			// check to see if an id was submitted
			if ($id<1) {
				$page_title =  __($record_name)." :: ".__("ERROR");
				$display_buffer .= __("Must select record to Modify");
				template_display();
			} // end of if.. statement checking for id #
			if ($been_here != "yes") {
				// now we extract the data, since the record was given...
				reset ($this->variables);
				foreach ($this->variables as $k => $v) { global ${$v}; }
				$r = freemed::get_link_rec ($id, $this->table_name);
				extract ($r);
				break;
			} // end checking if we have been here yet...
			break;
		} // end of interior switch

// grab important patient information
	$display_buffer .= "
	<p/>
	<form ACTION=\"$this->page_name\" METHOD=\"POST\">
	<input TYPE=\"HIDDEN\" NAME=\"been_here\" VALUE=\"yes\"/>
	<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
	<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".prepare($go)."\"/>
	<table WIDTH=\"100%\" CELLPSPACING=\"2\" CELLPADDING=\"2\" BORDER=\"0\"
	 VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
    <TR>
     <TD COLSPAN=4 ALIGN=CENTER BGCOLOR=\"#777777\">
      <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
      ".__("General Information")."
      </FONT>
     </TD>
    </TR>
    <TR>
     <TD ALIGN=RIGHT>".__("Description")."</TD>
     <TD ALIGN=LEFT>
     ".html_form::text_widget('eocdescrip', 25, 100)."
     </TD>
";
  if ($this->this_patient->isFemale()) { $display_buffer .= "
     <TD ALIGN=RIGHT>".__("Related to Pregnancy")."</TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelpreg\">
       <OPTION VALUE=\"no\"  ".
         ( ($eocrelpreg=="no") ? "SELECTED" : "" ).">".__("No")."
       <OPTION VALUE=\"yes\" ".
         ( ($eocrelpreg=="yes") ? "SELECTED" : "" ).">".__("Yes")."
      </SELECT>
     </TD>
  "; } else { $display_buffer .= "
     <TD ALIGN=RIGHT><I>".__("Related to Pregnancy")."</I></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=HIDDEN NAME=\"eocrelpreg\" VALUE=\"no\">
      <I>".__("No")."</I>
     </TD>
  "; } // end checking if female
  $display_buffer .= "  
    </TR><TR>
     <TD ALIGN=RIGHT>".__("Date of First Occurance")."</TD>
      <TD ALIGN=LEFT>
  ".fm_date_entry("eocstartdate")."
     </TD>
     <TD ALIGN=RIGHT>".__("Related to Employment")."</TD>
      <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelemp\">
       <OPTION VALUE=\"no\"  ".
         ( ($eocrelemp=="no") ? "SELECTED" : "" ).">".__("No")."
       <OPTION VALUE=\"yes\" ".
         ( ($eocrelemp=="yes") ? "SELECTED" : "" ).">".__("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>".__("Date of Last Similar")."</TD>
     <TD ALIGN=LEFT>
   ".fm_date_entry("eocdtlastsimilar")."
     </TD>
     <TD ALIGN=RIGHT>".__("Related to Automobile")."</TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelauto\">
       <OPTION VALUE=\"no\"  ".
         ( ($eocrelauto=="no") ? "SELECTED" : "" ).">".__("No")."
       <OPTION VALUE=\"yes\" ".
         ( ($eocrelauto=="yes") ? "SELECTED" : "" ).">".__("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>".__("Referring Physician")."</TD>
     <TD ALIGN=LEFT>
   ";
   $display_buffer .= freemed_display_selectbox (
     $sql->query("SELECT * FROM physician WHERE phyref='yes'
       ORDER BY phylname,phyfname"),
     "#phylname#, #phyfname#", "eocreferrer")."
     </TD>
     <TD ALIGN=RIGHT>".__("Related to Other Cause")."</TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelother\">
       <OPTION VALUE=\"no\"  ".
         ( ($eocrelother=="no") ? "SELECTED" : "" ).">".__("No")."
       <OPTION VALUE=\"yes\" ".
         ( ($eocrelother=="yes") ? "SELECTED" : "" ).">".__("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>".__("Facility")."</TD>
     <TD ALIGN=LEFT>
   ";
   if (empty($eocfacility)) $eocfacility = $_SESSION["default_facility"];
   
   $display_buffer .= 
     freemed_display_selectbox (
       $sql->query("SELECT * FROM facility ORDER BY psrname,psrnote"),
       "#psrname# [#psrnote#]", 
       "eocfacility")."
     </TD>
     <TD ALIGN=RIGHT>".__("State/Province")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelstpr\" SIZE=5 MAXLENGTH=5
       VALUE=\"".prepare($eocrelstpr)."\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>".__("Diagnosis Family")."</TD>
     <TD ALIGN=LEFT>
    ";
    // compact and display eocdiagfamily
    $display_buffer .= freemed::multiple_choice (
      "SELECT * FROM diagfamily ORDER BY dfname, dfdescrip",
      "##dfname## (##dfdescrip##)",
      "eocdiagfamily",
      fm_join_from_array($eocdiagfamily), false)."
     </TD>
     <TD ALIGN=RIGHT>".__("Episode Type")."</TD>
     <TD ALIGN=LEFT>
    ";
    // case statement for eoctype
    switch ($eoctype) {
      case "acute":             $type_a  = "SELECTED"; break;
      case "chronic":           $type_c  = "SELECTED"; break;
      case "chronic recurrent": $type_cr = "SELECTED"; break;
      case "historical":        $type_h  = "SELECTED"; break;
    } // end switch for $eoctype
    $display_buffer .= "
      <SELECT NAME=\"eoctype\">
       <OPTION VALUE=\"\" >".__("NONE SELECTED")."
       <OPTION VALUE=\"acute\" ".
         ( ($eoctype=="acute") ? "SELECTED" : "" ).">".__("acute")."
       <OPTION VALUE=\"chronic\" ".
         ( ($eoctype=="chronic") ? "SELECTED" : "" ).">".__("chronic")."
       <OPTION VALUE=\"chronic recurrent\" ".
         ( ($eoctype=="chronic recurrent") ? "SELECTED" : "" ).">".
	 __("chronic recurrent")."
       <OPTION VALUE=\"historical\" ".
         ( ($eoctype=="historical") ? "SELECTED" : "" ).">".__("historical")."
      </SELECT>
     </TD>
    </TR>
	";
	$display_buffer .= "
    <TR>
	<TD ALIGN=RIGHT>".__("Disability Type")."</TD>
    <TD ALIGN=LEFT>
      <SELECT NAME=\"eocdistype\">
       <OPTION VALUE=\"0\" ".
         ( ($eocdistype==0) ? "SELECTED" : "" ).">".__("Unknown")."
       <OPTION VALUE=\"1\" ".
         ( ($eocdistype==1) ? "SELECTED" : "" ).">".__("LT")."
       <OPTION VALUE=\"2\" ".
         ( ($eoctype==2) ? "SELECTED" : "" ).">".__("ST")."
       <OPTION VALUE=\"3\" ".
         ( ($eocdistype==3) ? "SELECTED" : "" ).">". __("Permanent")."
       <OPTION VALUE=\"4\" ".
         ( ($eoctype==4) ? "SELECTED" : "" ).">".__("No Disability")."
      </SELECT>
	</TD>
	<TD ALIGN=RIGHT>".__("Hospital")."</TD>
    <TD ALIGN=LEFT>
      <SELECT NAME=\"eochospital\">
       <OPTION VALUE=\"0\" ".
         ( ($eochospital==0) ? "SELECTED" : "" ).">".__("No")."
       <OPTION VALUE=\"1\" ".
         ( ($eochospital==1) ? "SELECTED" : "" ).">".__("Yes")."
      </SELECT>
	</TD>
	</TR>
	";
	$display_buffer .= "
    <TR>
	<TD ALIGN=RIGHT>".__("Disability From Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eocdisfromdt")."
	</TD>
	<TD ALIGN=RIGHT>".__("Hospitial Admission Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eochosadmdt")."
	</TD>
	</TR>
	";
	$display_buffer .= "
    <TR>
	<TD ALIGN=RIGHT>".__("Disability To Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eocdistodt")."
	</TD>
	<TD ALIGN=RIGHT>".__("Hospitial Discharge Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eochosdischrgdt")."
	</TD>
	</TR>
	";
	$display_buffer .= "
    <TR>
	<TD ALIGN=RIGHT>".__("Disability Back to Work Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eocdisworkdt")."
	</TD>
	</TR>
	";
	
	$display_buffer .= "
    </table>
    <p/>
   ";

   if ($eocrelauto=="yes") { $display_buffer .= "
      <!-- conditional auto table -->

     <CENTER>
     <table WIDTH=\"100%\" CELLPSPACING=\"2\" CELLPADDING=\"2\" BORDER=\"0\"
      VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
     <TR>
     <TD ALIGN=CENTER COLSPAN=4 BGCOLOR=\"#777777\">
      <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
      ".__("Automobile Related Information")."
      </FONT>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT>".__("Auto Insurance")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoname\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautoname)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("Case Number")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocase\" SIZE=10 MAXLENGTH=20
       VALUE=\"".prepare($eocrelautocase)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Address (Line 1)")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautoaddr1)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("Contact Name")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautorcname)."\">
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Address (Line 2)")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautoaddr2)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("Contact Phone")."</TD>
     <TD ALIGN=LEFT>
   ".
   fm_phone_entry("eocrelautorcphone")
   ."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("City, State/Prov, Postal Code")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocity\" SIZE=10 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautocity)."\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelautostpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"".prepare($eocrelautostpr)."\">
      <INPUT TYPE=TEXT NAME=\"eocrelautozip\" SIZE=11 MAXLENGTH=10
       VALUE=\"".prepare($eocrelautozip)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("Email Address")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautorcemail)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Country")."</TD>
     <TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"eocrelautocountry\" SIZE=10 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautocountry)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("Time of Accident")."</TD>
     <TD ALIGN=LEFT>
	 ".fm_time_entry("eocrelautotime")."
     </TD>
     </TR>
     </table>
     </CENTER>
   "; } // end of conditional auto info




   if ($eocrelemp=="yes") { $display_buffer .= "
      <!-- conditional employment table -->

     <CENTER>
     <table WIDTH=\"100%\" CELLPSPACING=\"2\" CELLPADDING=\"2\" BORDER=\"0\"
      VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
     ".__("Employment Related Information")."
     </FONT>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT>".__("Name of Employer")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempname\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempname)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("File Number")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempfile\" SIZE=10 MAXLENGTH=20
       VALUE=\"".prepare($eocrelempfile)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Address (Line 1)")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempaddr1)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("Contact Name")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelemprcname)."\">
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Address (Line 2)")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempaddr2)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("Contact Phone")."</TD>
     <TD ALIGN=LEFT>
   ".fm_phone_entry("eocrelemprcphone")."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("City, State/Prov, Postal Code")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempcity\" SIZE=10 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempcity)."\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelempstpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"".prepare($eocrelempstpr)."\">
      <INPUT TYPE=TEXT NAME=\"eocrelempzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"".prepare($eocrelempzip)."\">
     </TD>
     <TD ALIGN=RIGHT>".__("Email Address")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelemprcemail)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Country")."</TD>
     <TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"eocrelempcountry\" SIZE=10 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempcountry)."\">
     </TD>
     <TD ALIGN=RIGHT>&nbsp; <!-- placeholder --></TD>
     <TD ALIGN=LEFT>
       &nbsp; <!-- placeholder -->
     </TD>
     </TR>
     </table>
     </CENTER>
   "; } // end of conditional employment info


   if ($eocrelpreg=="yes") { $display_buffer .= "
      <!-- conditional pregnancy table -->

     <CENTER>
     <table WIDTH=\"100%\" CELLPSPACING=\"2\" CELLPADDING=\"2\" BORDER=\"0\"
      VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
     ".__("Pregnancy Related Information")."
     </FONT>
     <TR>
     <TD ALIGN=RIGHT>".__("Length of Cycle")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select ("eocrelpregcycle", 10, 40)."
     </TD>
     <TD ALIGN=RIGHT>".__("Last Menstrual Period")."</TD>
     <TD ALIGN=LEFT>
   ".fm_date_entry("eocrelpreglastper")."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Gravida")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpreggravida", 0, 15)."
     </TD>
     <TD ALIGN=RIGHT>".__("Date of Confinement")."</TD>
     <TD ALIGN=LEFT>
   ".fm_date_entry("eocrelpregconfine");
   $display_buffer .= "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Para")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregpara", 0, 15)."
     </TD>
     <TD ALIGN=RIGHT>".__("Miscarries")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregmiscarry", 0, 15)."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>".__("Abortions")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregabort", 0, 15)."
     </TD>
     <TD ALIGN=RIGHT>&nbsp; <!-- placeholder --></TD>
     <TD ALIGN=LEFT>
       &nbsp; <!-- placeholder -->
     </TD>
     </TR>
     </table>
     </CENTER>
   "; } // end of conditional pregnancy info

   if ($eocrelother=="yes") { $display_buffer .= "
      <!-- conditional other table -->

     <CENTER>
     <table WIDTH=\"100%\" CELLPSPACING=\"2\" CELLPADDING=\"2\" BORDER=\"0\"
      VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
      ".__("Other Related Information")."
     </FONT>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT>".__("More Information")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelothercomment\" SIZE=35 MAXLENGTH=100
       VALUE=\"".prepare($eocrelothercomment)."\">
     </TD>
     </TR>
    </table>
    "; } // end of other conditional reason

   $display_buffer .= "
     <p/>
     <div ALIGN=\"CENTER\">
     <input name=\"submit\" type=\"submit\" class=\"button\" value=\"".$this_action."\"/>
     <input name=\"submit\" type=\"submit\" class=\"button\" value=\"".__("Refresh")."\"/>
     <input name=\"submit\" type=\"submit\" class=\"button\" value=\"".__("Cancel")."\"/>
     </div>
    ";
	} // end function EpisodeOfCare->form

	function prepare () {
		global $display_buffer;
		global $eocdiagfamily,
			$eocstartdate,
			$eocdtlastsimilar,
			$eocrelpreglastper,
			$eocrelpregconfine,
			$eocrelautorcphone,
			$eocrelemprcphone,
			$eocpatient,$patient,
			$eocdisfromdt,
			$eocdistodt,
			$eocdisworkdt,
			$eochosadmdt,
			$eochosdischrgdt,
			$eocrelautotime;

			// compact 3d arrays into strings...
		$eocdiagfamily     = fm_join_from_array ($eocdiagfamily     );

			// assemble all "normal" dates
		$eocstartdate      = fm_date_assemble   ("eocstartdate"     );
		$eocdtlastsimilar  = fm_date_assemble   ("eocdtlastsimilar" );
		$eocrelpreglastper = fm_date_assemble   ("eocrelpreglastper");
		$eocrelpregconfine = fm_date_assemble   ("eocrelpregconfine");
		$eocdisfromdt      = fm_date_assemble   ("eocdisfromdt");
		$eocdistodt        = fm_date_assemble   ("eocdistodt");
		$eocdisworkdt      = fm_date_assemble   ("eocdisworkdt");
		$eochosadmdt       = fm_date_assemble   ("eochosadmdt");
		$eochosdischrgdt   = fm_date_assemble   ("eochosdischrgdt");
		$eocrelautotime    = fm_time_assemble   ("eocrelautotime");

			// assemble all phone numbers
		$eocrelautorcphone = fm_phone_assemble  ("eocrelautorcphone");
		$eocrelemprcphone  = fm_phone_assemble  ("eocrelemprcphone" );

			// move patient over
		$eocpatient = $patient;
	} // end function EpisodeOfCare->prepare

	function add () {
		$this->prepare();
		$this->_add();
	} // end function EpisodeOfCare->add

	function mod () {
		$this->prepare();
		$this->_mod();
	} // end function EpisodeOfCare->mod

    // view of entire episode (central control screen)
	function display () {
		global $display_buffer, $id, $sql;
		global $save_module, $module, $_pass;
		global $patient;

		if ($id<1) {
			$page_title = __($this->record_name)." :: ".__("ERROR");
			$display_buffer .= "
			<P>
			".__("You must specify an ID to view an Episode!")."
			<P>
			<CENTER>
			<A HREF=\"manage.php?id=$patient\"
			>".__("Manage Patient")."</A>
			</CENTER>
			";
			template_display();
		} // end checking for ID as valid

		$eoc = freemed::get_link_rec($id, $this->table_name);
		// display vitals for current episode
		$display_buffer .= "
		<P>
		<!-- Vitals Display Table -->
     <table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"1\"
      ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
     <tr>
      <td ALIGN=\"CENTER\">
       ".__("Starting Date")."
      </td>
      <td ALIGN=\"CENTER\">
       ".__("Description")."
      </td>
     </tr>
     <tr>
      <td ALIGN=\"CENTER\">
       ".prepare($eoc['eocstartdate'])."
      </td>
      <td ALIGN=\"CENTER\">
       ".prepare($eoc['eocdescrip'])."
      </td>
     </tr>
     </table>
     <!-- End Vitals Display Table -->
		<P>
		";

		// DEBUG
		global $record_name, $_pass, $module;
		foreach ($this->_GetAssociations() as $_garbage => $m) {
			// Backup settings
			$save_module = $module;
			$r_name = $record_name;

			// Fudge information to pass along
			$var = freemed::module_get_meta($m, 'EpisodeOfCareVar');
			$_pass = $var."=".urlencode($id); 
			$module = $m;
			$record_name = freemed::module_get_value($m, 'MODULE_NAME');

			// Run actual module view, with doctored query
			module_function($m, 'view',
				array(
				"(($var LIKE '".addslashes($id).":%') OR ".
				"($var LIKE '%:".addslashes($id)."') OR ".
				"($var LIKE '%:".addslashes($id).":%') OR ".
				"($var='".addslashes($id)."'))"
				)
			);

			// Restore
			$module = $save_module;
			$record_name = $r_name;

			$display_buffer .= "<p/>\n";
		}
		
		/*

		* Everything below is old static EOC modules code. It has
		* been replaced by the few lines of code above. To be EOC
		* ready, a module needs to allow passing of query parts
		* via the view() method, and needs to set the appropriate
		* association and meta information for EpisodeOfCareVar.

		// procedures display
		// special jimmy-rigged query to find in 3d array...
		$query = "SELECT * FROM procrec
             WHERE ((proceoc LIKE '".addslashes($id).":%') OR
                    (proceoc LIKE '%:".addslashes($id)."') OR
                    (proceoc LIKE '%:".addslashes($id).":%') OR
                    (proceoc='".addslashes($id)."'))
             ORDER BY procdt DESC";
		$result = $sql->query ($query);
  
		$r_name = $record_name; // backup
		$_pass = "proceoc=".urlencode($id);  
		$record_name = "Procedure";
		$save_module = $module;
		$module = "procedureModule"; // pass for the module loader
		$display_buffer .= freemed_display_itemlist (
     $result,
     "module_loader.php",
     array (
       __("Date") => "procdt",
       __("Procedure") => "proccpt",
       "" => "proccptmod",
       __("Comment") => "proccomment"
     ),
     array (
       "",
       "",
       "",
       __("NO COMMENT")
     ),
     array (
       "",
       "cpt" => "cptcode",
       "cptmod" => "cptmod",
       ""
     )
   );
		// end of procedures display
		$module = $save_module;
   
		$display_buffer .= "<P>\n";
   
		// progress notes display
   
		// special jimmy-rigged query to find in 3d array...
		$result = 0;
		$query = "SELECT * FROM pnotes
             WHERE ((pnotespat='".addslashes($patient)."') AND
                    ((pnoteseoc LIKE '".addslashes($id).":%') OR
                    (pnoteseoc LIKE '%:".addslashes($id)."') OR
                    (pnoteseoc LIKE '%:".addslashes($id).":%') OR
                    (pnoteseoc='".addslashes($id)."')))
             ORDER BY pnotesdt DESC";
		$result = $sql->query ($query);
     
		$record_name = "Progress Notes";
		$save_module = $module;
		$module = "progressNotes";
		$display_buffer .= freemed_display_itemlist (
			$result,
			"module_loader.php",
			array (
				__("Date") => "pnotesdt", 
				__("Description") => "pnotesdescrip"
			),
			array ( "", __("NO DESCRIPTION") )
		);
		$module = $save_module;

		$record_name = $r_name; // restore from backup var

		// images display
		// special jimmy-rigged query to find in 3d array...
		$display_buffer .= "<P>\n";
		$result = $sql->query ($query);
  
		$r_name = $record_name; // backup
		$_pass = "imageeoc=".urlencode($id);  
		$record_name = __("Patient Images");
		$save_module = $module;
		$module = "patientImages"; // pass for the module loader
		$display_buffer .= freemed_display_itemlist (
			$sql->query (
				"SELECT * ".
				"FROM images ".
				"WHERE ((imageeoc LIKE '".addslashes($id).":%') OR ".
				"(imageeoc LIKE '%:".addslashes($id)."') OR ".
				"(imageeoc LIKE '%:".addslashes($id).":%') OR ".
				"(imageeoc='".addslashes($id)."')) ".
				//freemed::itemlist_conditions(false)." ".
				"ORDER BY imagedt DESC"
			),
			"module_loader.php",
			array (
				__("Date") => "imagedt",
				__("Description") => "imagedesc"
			),
			array (
				"",
				__("NO DESCRIPTION")
			)
		);

		// end of procedures display
		$module = $save_module;
		$record_name = $r_name; // restore from backup var
		// end of progress notes display
  
		*/

		// Display management link at the bottom...
		$display_buffer .= "
		<p/>
		<div ALIGN=\"CENTER\">
		<a HREF=\"$this->page_name?patient=$patient&module=$module\"
		 class=\"button\">".__("Select Another")."</a>
		</div>
		<P>
		";
	} // end function EpisodeOfCare->display

	function view () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) { global ${$k}; }

		$display_buffer .= freemed_display_itemlist(
			$GLOBALS['sql']->query (
				"SELECT * ".
				"FROM ".$this->table_name." ".
				"WHERE eocpatient='".addslashes($patient)."' ".
				freemed::itemlist_conditions(false)." ".
				"ORDER BY eocstartdate DESC"
			),
			$this->page_name,
			array (
				__("Starting Date") => "eocstartdate",
				__("Description")   => "eocdescrip"
			),
			array (
				"",
				__("NO DESCRIPTION")
			)
		);
	} // end function EpisodeOfCare->view

	// ----- Widgets, etc

	function widget ($varname, $patient) {
		global ${$varname};
		return freemed::multiple_choice(
			"SELECT id,eocdescrip,eocstartdate,eocdtlastsimilar ".
			"FROM ".$this->table_name." WHERE ".
			"eocpatient='".addslashes($patient)."'",
			"##eocdescrip## (##eocstartdate## ".__("to")." ".
				"##eocdtlastsimilar##)",
			$varname,
			${$varname},
			false
		);
	} // end method EpisodeOfCare->widget

} // end class EpisodeOfCare

register_module ("EpisodeOfCare");

?>
