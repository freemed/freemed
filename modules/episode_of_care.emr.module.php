<?php
 // $Id$
 // desc: episode of care database module
 // lic : GPL, v2

if (!defined("__EPISODE_OF_CARE_MODULE_PHP__")) {

define (__EPISODE_OF_CARE_MODULE_PHP__, true);

class episodeOfCare extends freemedEMRModule {

	var $MODULE_NAME 	= "Episode of Care";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
		Episode of care is another portion of FreeMED
		designed to help with outcomes management. Any
		patients' treatment can be described through
		episodes of care, which may span any range of
		time, and more than one epsiode of care can
		be used per visit. 
	";

	var $record_name	= "Episode of Care";
	var $table_name     = "eoc";

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
		"eocrelautotime",
		"eochospital"
	);

	function episodeOfCare () {
		$this->freemedEMRModule();
	} // end constructor episodeOfCare

	function form () {
		reset ($GLOBALS);
		foreach ($GLOBALS as $k => $v) global $$k;

   switch ($action) {
     case "addform":
      $go = "add";
      $this_action = "Add";
      if ($been_here != "yes") $eocstartdate = $eocdtlastsimilar = $cur_date;
      break;
     case "modform":
      $go = "mod";
      $this_action = "Modify";
       // check to see if an id was submitted
      if ($id<1) {
       freemed_display_box_top (_("$record_name")." :: "._("ERROR"));
       echo _("Must select record to Modify");
       freemed_display_box_bottom ();
       freemed_close_db ();
       freemed_display_html_bottom ();
       DIE("");
      } // end of if.. statement checking for id #

      if ($been_here != "yes") {
         // now we extract the data, since the record was given...
		reset ($this->variables);
		foreach ($this->variables as $k => $v) global $$v;
        $r      = freemed_get_link_rec ($id, $this->table_name);
        extract ($r);
        break;
      } // end checking if we have been here yet...
   } // end of interior switch

    // grab important patient information
    echo "
	<P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
     <INPUT TYPE=HIDDEN NAME=\"been_here\" VALUE=\"yes\">
     <INPUT TYPE=HIDDEN NAME=\"id\"        VALUE=\"".prepare($id)."\">
     <INPUT TYPE=HIDDEN NAME=\"patient\"   VALUE=\"".prepare($patient)."\">
     <INPUT TYPE=HIDDEN NAME=\"module\"    VALUE=\"".prepare($module)."\">
    <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
     ALIGN=CENTER>
    <TR>
     <TD COLSPAN=4 ALIGN=CENTER BGCOLOR=\"#777777\">
      <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
      "._("General Information")."
      </FONT>
     </TD>
    </TR>
    <TR>
     <TD ALIGN=RIGHT>"._("Description")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocdescrip\" SIZE=25 MAXLENGTH=100
       VALUE=\"".prepare($eocdescrip)."\">
     </TD>
  ";
  if ($this->this_patient->isFemale()) { echo "
     <TD ALIGN=RIGHT>"._("Related to Pregnancy")."</TD>
     <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelpreg\">
       <OPTION VALUE=\"no\"  ".
         ( ($eocrelpreg=="no") ? "SELECTED" : "" ).">"._("No")."
       <OPTION VALUE=\"yes\" ".
         ( ($eocrelpreg=="yes") ? "SELECTED" : "" ).">"._("Yes")."
      </SELECT>
     </TD>
  "; } else { echo "
     <TD ALIGN=RIGHT><I>"._("Related to Pregnancy")."</I></TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=HIDDEN NAME=\"eocrelpreg\" VALUE=\"no\">
      <I>"._("No")."</I>
     </TD>
  "; } // end checking if female
  echo "  
    </TR><TR>
     <TD ALIGN=RIGHT>"._("Date of First Occurance")."</TD>
      <TD ALIGN=LEFT>
  ".fm_date_entry("eocstartdate")."
     </TD>
     <TD ALIGN=RIGHT>"._("Related to Employment")."</TD>
      <TD ALIGN=LEFT>
      <SELECT NAME=\"eocrelemp\">
       <OPTION VALUE=\"no\"  ".
         ( ($eocrelemp=="no") ? "SELECTED" : "" ).">"._("No")."
       <OPTION VALUE=\"yes\" ".
         ( ($eocrelemp=="yes") ? "SELECTED" : "" ).">"._("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>"._("Date of Last Similar")."</TD>
     <TD ALIGN=LEFT>
   ".fm_date_entry("eocdtlastsimilar")."
     </TD>
     <TD ALIGN=RIGHT>"._("Related to Automobile")."</TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelauto\">
       <OPTION VALUE=\"no\"  ".
         ( ($eocrelauto=="no") ? "SELECTED" : "" ).">"._("No")."
       <OPTION VALUE=\"yes\" ".
         ( ($eocrelauto=="yes") ? "SELECTED" : "" ).">"._("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>"._("Referring Physician")."</TD>
     <TD ALIGN=LEFT>
   ";
   echo freemed_display_selectbox (
     $sql->query("SELECT * FROM physician WHERE phyref='yes'
       ORDER BY phylname,phyfname"),
     "#phylname#, #phyfname#", "eocreferrer")."
     </TD>
     <TD ALIGN=RIGHT>"._("Related to Other Cause")."</TD>
     <TD ALIGN=LEFT>
     <SELECT NAME=\"eocrelother\">
       <OPTION VALUE=\"no\"  ".
         ( ($eocrelother=="no") ? "SELECTED" : "" ).">"._("No")."
       <OPTION VALUE=\"yes\" ".
         ( ($eocrelother=="yes") ? "SELECTED" : "" ).">"._("Yes")."
      </SELECT>
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>"._("Facility")."</TD>
     <TD ALIGN=LEFT>
   ";
   if (empty($eocfacility)) $eocfacility = $default_facility;
   
   echo 
     freemed_display_selectbox (
       $sql->query("SELECT * FROM facility ORDER BY psrname,psrnote"),
       "#psrname# [#psrnote#]", 
       "eocfacility")."
     </TD>
     <TD ALIGN=RIGHT>"._("State/Province")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelstpr\" SIZE=5 MAXLENGTH=5
       VALUE=\"".prepare($eocrelstpr)."\">
     </TD>
    </TR><TR>
     <TD ALIGN=RIGHT>"._("Diagnosis Family")."</TD>
     <TD ALIGN=LEFT>
    ";
    // compact and display eocdiagfamily
    echo freemed_multiple_choice ("SELECT * FROM diagfamily
           ORDER BY dfname, dfdescrip", "dfname:dfdescrip", "eocdiagfamily",
           fm_join_from_array($eocdiagfamily), false)."
     </TD>
     <TD ALIGN=RIGHT>"._("Episode Type")."</TD>
     <TD ALIGN=LEFT>
    ";
    // case statement for eoctype
    switch ($eoctype) {
      case "acute":             $type_a  = "SELECTED"; break;
      case "chronic":           $type_c  = "SELECTED"; break;
      case "chronic recurrent": $type_cr = "SELECTED"; break;
      case "historical":        $type_h  = "SELECTED"; break;
    } // end switch for $eoctype
    echo "
      <SELECT NAME=\"eoctype\">
       <OPTION VALUE=\"\" >"._("NONE SELECTED")."
       <OPTION VALUE=\"acute\" ".
         ( ($eoctype=="acute") ? "SELECTED" : "" ).">"._("acute")."
       <OPTION VALUE=\"chronic\" ".
         ( ($eoctype=="chronic") ? "SELECTED" : "" ).">"._("chronic")."
       <OPTION VALUE=\"chronic recurrent\" ".
         ( ($eoctype=="chronic recurrent") ? "SELECTED" : "" ).">".
	 _("chronic recurrent")."
       <OPTION VALUE=\"historical\" ".
         ( ($eoctype=="historical") ? "SELECTED" : "" ).">"._("historical")."
      </SELECT>
     </TD>
    </TR>
	";
	echo "
    <TR>
	<TD ALIGN=RIGHT>"._("Disability Type")."</TD>
    <TD ALIGN=LEFT>
      <SELECT NAME=\"eocdistype\">
       <OPTION VALUE=\"0\" ".
         ( ($eocdistype==0) ? "SELECTED" : "" ).">"._("Unknown")."
       <OPTION VALUE=\"1\" ".
         ( ($eocdistype==1) ? "SELECTED" : "" ).">"._("LT")."
       <OPTION VALUE=\"2\" ".
         ( ($eoctype==2) ? "SELECTED" : "" ).">"._("ST")."
       <OPTION VALUE=\"3\" ".
         ( ($eocdistype==3) ? "SELECTED" : "" ).">". _("Permanent")."
       <OPTION VALUE=\"4\" ".
         ( ($eoctype==4) ? "SELECTED" : "" ).">"._("No Disability")."
      </SELECT>
	</TD>
	<TD ALIGN=RIGHT>"._("Hospital")."</TD>
    <TD ALIGN=LEFT>
      <SELECT NAME=\"eochospital\">
       <OPTION VALUE=\"0\" ".
         ( ($eochospital==0) ? "SELECTED" : "" ).">"._("No")."
       <OPTION VALUE=\"1\" ".
         ( ($eochospital==1) ? "SELECTED" : "" ).">"._("Yes")."
      </SELECT>
	</TD>
	</TR>
	";
	echo "
    <TR>
	<TD ALIGN=RIGHT>"._("Disability From Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eocdisfromdt")."
	</TD>
	<TD ALIGN=RIGHT>"._("Hospitial Admission Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eochosadmdt")."
	</TD>
	</TR>
	";
	echo "
    <TR>
	<TD ALIGN=RIGHT>"._("Disability To Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eocdistodt")."
	</TD>
	<TD ALIGN=RIGHT>"._("Hospitial Discharge Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eochosdischrgdt")."
	</TD>
	</TR>
	";
	echo "
    <TR>
	<TD ALIGN=RIGHT>"._("Disability Back to Work Date")."</TD>
    <TD ALIGN=LEFT>
   	    ".fm_date_entry("eocdisworkdt")."
	</TD>
	</TR>
	";
	
	echo "
    </TABLE>
    <P>
   ";

   if ($eocrelauto=="yes") { echo "
      <!-- conditional auto table -->

     <CENTER>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=CENTER COLSPAN=4 BGCOLOR=\"#777777\">
      <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
      "._("Automobile Related Information")."
      </FONT>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT>"._("Auto Insurance")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoname\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautoname)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("Case Number")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocase\" SIZE=10 MAXLENGTH=20
       VALUE=\"".prepare($eocrelautocase)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Address (Line 1)")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautoaddr1)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("Contact Name")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautorcname)."\">
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Address (Line 2)")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautoaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautoaddr2)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("Contact Phone")."</TD>
     <TD ALIGN=LEFT>
   ".
   fm_phone_entry("eocrelautorcphone")
   ."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("City, State/Prov, Postal Code")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautocity\" SIZE=10 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautocity)."\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelautostpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"".prepare($eocrelautostpr)."\">
      <INPUT TYPE=TEXT NAME=\"eocrelautozip\" SIZE=11 MAXLENGTH=10
       VALUE=\"".prepare($eocrelautozip)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("Email Address")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelautorcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautorcemail)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Country")."</TD>
     <TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"eocrelautocountry\" SIZE=10 MAXLENGTH=100
       VALUE=\"".prepare($eocrelautocountry)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("Time of Accident")."</TD>
     <TD ALIGN=LEFT>
	 ".fm_time_entry("eocrelautotime")."
     </TD>
     </TR>
     </TABLE>
     </CENTER>
   "; } // end of conditional auto info




   if ($eocrelemp=="yes") { echo "
      <!-- conditional employment table -->

     <CENTER>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
     "._("Employment Related Information")."
     </FONT>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT>"._("Name of Employer")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempname\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempname)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("File Number")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempfile\" SIZE=10 MAXLENGTH=20
       VALUE=\"".prepare($eocrelempfile)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Address (Line 1)")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr1\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempaddr1)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("Contact Name")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcname\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelemprcname)."\">
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Address (Line 2)")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempaddr2\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempaddr2)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("Contact Phone")."</TD>
     <TD ALIGN=LEFT>
   ".fm_phone_entry("eocrelemprcphone")."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("City, State/Prov, Postal Code")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelempcity\" SIZE=10 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempcity)."\"> <B>,</B>
      <INPUT TYPE=TEXT NAME=\"eocrelempstpr\" SIZE=4 MAXLENGTH=3
       VALUE=\"".prepare($eocrelempstpr)."\">
      <INPUT TYPE=TEXT NAME=\"eocrelempzip\" SIZE=11 MAXLENGTH=10
       VALUE=\"".prepare($eocrelempzip)."\">
     </TD>
     <TD ALIGN=RIGHT>"._("Email Address")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelemprcemail\" SIZE=20 MAXLENGTH=100
       VALUE=\"".prepare($eocrelemprcemail)."\">
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Country")."</TD>
     <TD ALIGN=LEFT>
       <INPUT TYPE=TEXT NAME=\"eocrelempcountry\" SIZE=10 MAXLENGTH=100
       VALUE=\"".prepare($eocrelempcountry)."\">
     </TD>
     <TD ALIGN=RIGHT>&nbsp; <!-- placeholder --></TD>
     <TD ALIGN=LEFT>
       &nbsp; <!-- placeholder -->
     </TD>
     </TR>
     </TABLE>
     </CENTER>
   "; } // end of conditional employment info


   if ($eocrelpreg=="yes") { echo "
      <!-- conditional pregnancy table -->

     <CENTER>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
     "._("Pregnancy Related Information")."
     </FONT>
     <TR>
     <TD ALIGN=RIGHT>"._("Length of Cycle")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select ("eocrelpregcycle", 10, 40)."
     </TD>
     <TD ALIGN=RIGHT>"._("Last Menstrual Period")."</TD>
     <TD ALIGN=LEFT>
   ".fm_date_entry("eocrelpreglastper")."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Gravida")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpreggravida", 0, 15)."
     </TD>
     <TD ALIGN=RIGHT>"._("Date of Confinement")."</TD>
     <TD ALIGN=LEFT>
   ".fm_date_entry("eocrelpregconfine");
   echo "
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Para")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregpara", 0, 15)."
     </TD>
     <TD ALIGN=RIGHT>"._("Miscarries")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregmiscarry", 0, 15)."
     </TD>
     </TR><TR>
     <TD ALIGN=RIGHT>"._("Abortions")."</TD>
     <TD ALIGN=LEFT>
   ".fm_number_select("eocrelpregabort", 0, 15)."
     </TD>
     <TD ALIGN=RIGHT>&nbsp; <!-- placeholder --></TD>
     <TD ALIGN=LEFT>
       &nbsp; <!-- placeholder -->
     </TD>
     </TR>
     </TABLE>
     </CENTER>
   "; } // end of conditional pregnancy info

   if ($eocrelother=="yes") { echo "
      <!-- conditional other table -->

     <CENTER>
     <TABLE WIDTH=100% CELLPSPACING=2 CELLPADDING=2 BORDER=0 VALIGN=MIDDLE
      ALIGN=CENTER>
     <TR>
     <TD ALIGN=CENTER BGCOLOR=\"#777777\" COLSPAN=4>
     <FONT SIZE=\"+1\" COLOR=\"#ffffff\">
      "._("Other Related Information")."
     </FONT>
     </TD>
     </TR>
     <TR>
     <TD ALIGN=RIGHT>"._("More Information")."</TD>
     <TD ALIGN=LEFT>
      <INPUT TYPE=TEXT NAME=\"eocrelothercomment\" SIZE=35 MAXLENGTH=100
       VALUE=\"".prepare($eocrelothercomment)."\">
     </TD>
     </TR>
    </TABLE>
    "; } // end of other conditional reason

   echo "
     <P>
     <CENTER>
     <SELECT NAME=\"action\">
      <OPTION VALUE=\"$action\">"._("Update")."
      <OPTION VALUE=\"$go\">"._("$this_action")."
      <OPTION VALUE=\"view\">"._("back")."
     </SELECT>
     <INPUT TYPE=SUBMIT VALUE=\""._("Go")."\">
     </CENTER>
    ";
	} // end function episodeOfCare->form

	function prepare () {
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
	} // end function episodeOfCare->prepare

	function add () {
		$this->prepare();
		$this->_add();
	} // end function episodeOfCare->add

	function mod () {
		$this->prepare();
		$this->_mod();
	} // end function episodeOfCare->mod

    // view of entire episode (central control screen)
	function display () {
   if ($id<1) {
     freemed_display_box_top (_("ERROR"));
     echo "
       <P>
       "._("You must specify an ID to view an Episode!")."
       <P>
       <CENTER>
        <A HREF=\"manage.php?$_auth&id=$patient\"
        >"._("Manage Patient")."</A>
       </CENTER>
     ";
     freemed_display_box_bottom ();
     freemed_close_db ();
     freemed_display_html_bottom ();
     DIE("");
   } // end checking for ID as valid

   $eoc = freemed_get_link_rec($id, $this->table_name);
   // display vitals for current episode
   echo "
     <P>
     <!-- Vitals Display Table -->
     <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=1
      ALIGN=CENTER VALIGN=MIDDLE>
     <TR>
      <TD ALIGN=CENTER>
       "._("Starting Date")."
      </TD>
      <TD ALIGN=CENTER>
       "._("Description")."
      </TD>
     </TR>
     <TR>
      <TD ALIGN=CENTER>
       $eoc[eocstartdate]
      </TD>
      <TD ALIGN=CENTER>
       ".prepare($eoc[eocdescrip])."
      </TD>
     </TR>
     </TABLE>
     <!-- End Vitals Display Table -->
     <P>
   ";
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
   $_auth = "proceoc=".urlencode($id);  
   $record_name = "Procedure";
   $save_module = $module;
   $module = "procedureModule"; // pass for the module loader
   echo freemed_display_itemlist (
     $result,
     "module_loader.php",
     array (
       _("Date") => "procdt",
       _("Procedure") => "proccpt",
       "" => "proccptmod",
       _("Comment") => "proccomment"
     ),
     array (
       "",
       "",
       "",
       _("NO COMMENT")
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
   
   echo "
   <P>\n";
   
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
     
   $_auth = "pnoteseoc=".urlencode($id);  
   $record_name = "Progress Notes";
   $save_module = $module;
   $module = "progressNotes";
   echo freemed_display_itemlist (
     $result,
     "module_loader.php",
     array (
       _("Date") => "pnotesdt"
     ),
     array (
       ""
     )
   );
   $module = $save_module;

   $record_name = $r_name; // restore from backup var

   // end of progress notes display
   // display management link at the bottom...
   echo "
     <P>
     <CENTER>
      <A HREF=\"$this->page_name?$_auth&patient=$patient&module=$module\"
      >"._("Choose Another $record_name")."</A>
     </CENTER>
     <P>
   ";
	} // end function episodeOfCare->display

	function view () {
		//global $sql;
		reset ($GLOBALS);
		foreach ($GLOBALS as $k => $v) global $$k;

		echo freemed_display_itemlist(
			$sql->query ("SELECT * FROM $this->table_name
                         WHERE eocpatient='".addslashes($patient)."'
                         ORDER BY eocstartdate DESC"),
			$this->page_name,
			array (
				_("Starting Date") => "eocstartdate",
				_("Description")   => "eocdescrip"
			),
			array (
				"",
				_("NO DESCRIPTION")
			)
		);
	} // end function episodeOfCare->view

} // end class episodeOfCare

register_module ("episodeOfCare");

} // if not defined

?>
