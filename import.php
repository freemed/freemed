<?php
 // $Id$
 // desc: administrative import module for older databases
 // lic : GPL, v2

 $page_name = "import.php";
 include ("lib/freemed.php");
 include ("lib/API.php");

 freemed_open_db ($LoginCookie);
 $this_user = new User ($LoginCookie);

freemed_display_html_top ();
freemed_display_banner ();

if ($this_user->getLevel()<$admin_level)
 DIE("$page_name :: You do not have access to this module");

switch ($action) {
 case "import":
  freemed_display_box_top (_("Import Database"));
  echo "
   <P>
   "._("Importing Database")." \"$db\" ... 
  ";
  if (freemed_import_stock_data ($db)) { echo _("done");  }
   else                                { echo _("ERROR"); }
  echo "
   <P>
    <CENTER>
     <A HREF=\"$page_name?$_auth\"
     >"._("Import Another Database")."</A> <B>|</B>
     <A HREF=\"admin.php?$_auth\"
     >"._("Return to Administration Menu")."</A>
    </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break;
 default:
  freemed_display_box_top (_("Import Database"));
  echo "
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"import\">
    <P>
    "._("Select Database to Import")." : 
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
     <OPTION VALUE=\"scheduler\"   >Scheduler (scheduler)
     <OPTION VALUE=\"simplereport\">Simple Reports (simplereport)
     <OPTION VALUE=\"specialties\" >Specialties (specialties)
     <OPTION VALUE=\"tos\"         >Type of Service (tos)
     <OPTION VALUE=\"user\"        >User Database (user)
    </SELECT>
    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\"Import\">
    </CENTER>
    <P>
    <CENTER>
     <A HREF=\"admin.php?$_auth\"
     >"._("Return to Administration Menu")."</A>
    </CENTER>
    <P>
   </FORM>
  ";
  freemed_display_box_bottom ();
  break;
} // end action switch

freemed_close_db ();
freemed_display_html_bottom ();

?>
