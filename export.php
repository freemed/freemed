<?php
 // $Id$
 // desc: administrative export module
 // lic : GPL, v2

$page_name = "export.php";
include ("lib/freemed.php");

freemed_open_db ();
$this_user = CreateObject('FreeMED.User');

if (!freemed::user_flag(USER_ADMIN)) {
	$display_buffer .= "$page_name :: You do not have access to this module";
	template_display();
}

switch ($action) {
 case "export":
  $page_title = _("Export Database");
  $display_buffer .= "
   <P>
   "._("Exporting Database")." \"$db\" ... 
  ";
  if (freemed_export_stock_data ($db)) { $display_buffer .= "$Done."; }
   else                                { $display_buffer .= "$ERROR"; }
  $display_buffer .= "
   <P>
    <CENTER>
    <A HREF=\"$page_name\"
     >"._("Export Another Database")."</A> <B>|</B>
    <A HREF=\"admin.php\"
     >"._("Return to Administration Menu")."</A>
    </CENTER>
   <P>
  ";
  break;
 default:
  $page_title = _("Export Database");
  $display_buffer .= "
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"export\">
    <P>
    "._("Select Database to Export")." : 
    <SELECT NAME=\"db\">
     <OPTION VALUE=\"authorizations\"
                                   >Authorizations (authorizations)
     <OPTION VALUE=\"room\"        >Booking Locations (room)
     <OPTION VALUE=\"roomequip\"   >Booking Locations Equipment (roomequip)
     <OPTION VALUE=\"callin\"      >Call-In Patients (callin)
     <OPTION VALUE=\"cpt\"         >CPT/Procedural Codes (cpt)
     <OPTION VALUE=\"cptmod\"      >CPT Modifiers (cptmod)
     <OPTION VALUE=\"patrecdata\"  >Custom Records (patrecdata)
     <OPTION VALUE=\"patrectemplate\"
                                   >Custom Record Templates (patrectemplate)
     <OPTION VALUE=\"degrees\"     >Degrees (degrees)
     <OPTION VALUE=\"diagfamily\"  >Diagnosis Families (diagfamily)
     <OPTION VALUE=\"eoc\"         >Episodes of Care (eoc)
     <OPTION VALUE=\"infaxlut\"    >Fax Sender Lookup Table (infaxlut)
     <OPTION VALUE=\"fixedform\"   >Fixed-Length Forms (fixedform)
     <OPTION VALUE=\"frmlry\"      >Formulary/Drugs (frmlry)
     <OPTION VALUE=\"icd9\"        >ICD/Diagnosis Codes (icd9)
     <OPTION VALUE=\"infaxes\"     >Incoming Faxes (infaxes)
     <OPTION VALUE=\"insco\"       >Insurance Companies (insco)
     <OPTION VALUE=\"inscogroyp\"  >Insurance Company Groups (inscogroup)
     <OPTION VALUE=\"insmod\"      >Insurance Company Modifiers (insmod)
     <OPTION VALUE=\"intservtype\" >Internal Service Types (intservtype)
     <OPTION VALUE=\"log\"         >Log File (log)
     <OPTION VALUE=\"oldreports\"  >Old Reports (oldreports)
     <OPTION VALUE=\"patimg\"      >Patient Images (patimg)
     <OPTION VALUE=\"patient\"     >Patient Record (patient)
     <OPTION VALUE=\"payrec\"      >Payment/Ledget Records (payrec)
     <OPTION VALUE=\"phyavailmap\" >Physician Availability Map (phyavailmap)
     <OPTION VALUE=\"physician\"   >Physicians/Providers (physician)
     <OPTION VALUE=\"phygroup\"    >Physician/Provider Group (phygroup)
     <OPTION VALUE=\"phystatus\"   >Physician/Provider Status (phystatus)
     <OPTION VALUE=\"facility\"    >Place of Service (facility) 
     <OPTION VALUE=\"rx\"          >Prescriptions (rx)
     <OPTION VALUE=\"printer\"     >Printers (printer)
     <OPTION VALUE=\"procedure\"   >Procedures (procedure)
     <OPTION VALUE=\"pnotes\"      >Progress Notes (pnotes)
     <OPTION VALUE=\"queries\"     >Query Maker (queries)
     <OPTION VALUE=\"scheduler\"   >Scheduler (scheduler)
     <OPTION VALUE=\"simplereport\">Simple Reports (simplereport)
     <OPTION VALUE=\"specialties\" >Specialties (specialties)
     <OPTION VALUE=\"tos\"         >Type of Service (tos)
     <OPTION VALUE=\"user\"        >User Database (user)
    </SELECT>
    <P>
    <CENTER>
     <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\""._("Export")."\"/>
    </CENTER>
    <P>
    <CENTER>
     <A HREF=\"admin.php\"
     >"._("Return to Administration Menu")."</A>
    </CENTER>
    <P>
   </FORM>
  ";
  break;
} // end action switch

template_display();

?>
