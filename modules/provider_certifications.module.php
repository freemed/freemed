<?php
  // $Id$
  // note: provider certification database functions
  // code: jeff b (jeff@univrel.pr.uconn.edu)
  // lic : GPL

if (!defined("__PROVIDER_CERTIFICATIONS_MODULE_PHP__")) {

define(__PROVIDER_CERTIFICATIONS_MODULE_PHP__, true);

include "lib/module_maintenance.php";

class providerCertificationsMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "Provider Certifications Maintenance";
	var $MODULE_VERSION = "0.1";

	var $record_name    = "Provider Certifications";
	var $table_name 	= "degrees";

	var $variables      = array (
		"degdegree",
		"degname",
		"degdate"
	);

	function providerCertificationsMaintenance () {
		global $cur_date, $deg_date;
		$this->freemedMaintenanceModule();
		$degdate = $cur_date;
	} // end constructor providerCertificationsMaintenance

	function form () { $this->view(); }

	function view () {
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		if ($action=="modform") {
			$result = fdb_query("SELECT * FROM $this->table_name WHERE id='$id'");
			$r = fdb_fetch_array($result); // dump into array r[]
			extract ($r);
		} // modform fetching

		// display the table 
		echo freemed_display_itemlist(
			$sql->query("SELECT * FROM $this->table_name ORDER BY degdegree,degname"),
			$this->page_name,
			array (
				_("Degree") => "degdegree",
				_("Description") => "degname"
			),
			array ( "", _("NO DESCRIPTION") ), "", "d_page"
		);
  
		echo "
   <CENTER>
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
   <TR><TD ALIGN=RIGHT>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".(($action=="modform") ? 
                                                   "mod" : "add")."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >
    <INPUT TYPE=HIDDEN NAME=\"method\"   VALUE=\"".prepare($method)."\"  >

  ".(($action=="modform") ? "
    <$STDFONT_B>"._("Date Last Modified")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    $degdate
   </TD></TR>
   <TR><TD ALIGN=RIGHT>
  " : "")
  
  ."
    <$STDFONT_B>"._("Degree")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=degdegree SIZE=11 MAXLENGTH=10
     VALUE=\"$degdegree\">
   </TD></TR>
 
  <TR><TD ALIGN=RIGHT>
   <$STDFONT_B>"._("Degree Description")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=degname SIZE=30 MAXLENGTH=50
     VALUE=\"$degname\">
   </TD></TR>

   <TR><TD COLSPAN=2 ALIGN=CENTER>
    <INPUT TYPE=SUBMIT VALUE=\"".($action=="modform" ? 
        _("Update") : _("Add"))." \">
    <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
   </TD></TR>
  ";

  if ($action=="modform") 
    echo "
   <TR><TD COLSPAN=2 ALIGN=CENTER>
     <$STDFONT_B><A HREF=\"$page_name?$_auth\">".
       _("Abandon Modification")."</A><$STDFONT_E>
   </TD></TR>
    ";
    
  echo "
   </FORM>
   </TABLE>
   </CENTER>
    ";
	} // end function providerCertificationsMaintenance->view()

} // end class providerCertificationsMaintenance

register_module ("providerCertificationsMaintenance");

} // end if defined
 
?>
