<?php
  // $Id$
  // note: provider certification database functions
  // lic : GPL

LoadObjectDependency('FreeMED.MaintenanceModule');

class ProviderCertificationsMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Provider Certifications Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "Provider Certifications";
	var $table_name 	= "degrees";

	var $variables      = array (
		"degdegree",
		"degname",
		"degdate"
	);

	function ProviderCertificationsMaintenance () {
		global $cur_date, $deg_date;
		$this->MaintenanceModule();
		$degdate = date("Y-m-d");
	} // end constructor ProviderCertificationsMaintenance

	function form () { $this->view(); }

	function view () {
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		if ($action=="modform") {
			$result = $sql->query("SELECT * FROM $this->table_name WHERE id='$id'");
			$r = $sql->fetch_array($result); // dump into array r[]
			extract ($r);
		} // modform fetching

		// display the table 
		$display_buffer .= freemed_display_itemlist(
			$sql->query("SELECT * FROM $this->table_name ORDER BY degdegree,degname"),
			$this->page_name,
			array (
				_("Degree") => "degdegree",
				_("Description") => "degname"
			),
			array ( "", _("NO DESCRIPTION") ), "", "d_page"
		);
  
		$display_buffer .= "
   <CENTER>
   <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
   <TR><TD ALIGN=RIGHT>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".(($action=="modform") ? 
                                                   "mod" : "add")."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >
    <INPUT TYPE=HIDDEN NAME=\"module\"   VALUE=\"".prepare($module)."\"  >

  ".(($action=="modform") ? "
    "._("Date Last Modified")." :
   </TD><TD ALIGN=LEFT>
    $degdate
   </TD></TR>
   <TR><TD ALIGN=RIGHT>
  " : "")
  
  ."
    "._("Degree")." :
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=degdegree SIZE=11 MAXLENGTH=10
     VALUE=\"$degdegree\">
   </TD></TR>
 
  <TR><TD ALIGN=RIGHT>
   "._("Degree Description")." :
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
    $display_buffer .= "
   <TR><TD COLSPAN=2 ALIGN=CENTER>
     <A HREF=\"$page_name\">".
       _("Abandon Modification")."</A>
   </TD></TR>
    ";
    
  $display_buffer .= "
   </FORM>
   </TABLE>
   </CENTER>
    ";
	} // end function ProviderCertificationsMaintenance->view()

} // end class ProviderCertificationsMaintenance

register_module ("ProviderCertificationsMaintenance");

?>
