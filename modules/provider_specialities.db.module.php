<?php
  // $Id$
  // note: physician/provider specialties db
  // code: adam b (gdrago23@yahoo.com) -- complete rewrite
  // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class ProviderSpecialtiesMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "Provider Specialties Maintenance";
	var $MODULE_AUTHOR  = "Adam (gdrago23@yahoo.com)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name     = "specialties";
	var $record_name    = "Specialty";
	var $order_field    = "specname";

	var $variables      = array (
		"specname",
		"specdesc"
	);

	function ProviderSpecialtiesMaintenance () {
		// Table definition
		$this->table_definition = array (
			'specname' => SQL__VARCHAR(50),
			'specdesc' => SQL__VARCHAR(100),
			'specdatestamp' => SQL__DATE,
			'id' => SQL__SERIAL
		);
	
		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor ProviderSpecialtiesMaintenance

	function form () { $this->view(); }

	function view () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) global ${$k};

		if ($action=="modform") {
			$result = $sql->query("SELECT * FROM $this->table_name ".
				"WHERE id='".addslashes($id)."'");
			$r = $sql->fetch_array($result);
			extract($r);
		} // if this is a modform...

		$display_buffer .= freemed_display_itemlist(
			$sql->query (
				"SELECT * ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY specname,specdesc"
			),
			$this->page_name,
			array (
				__("Specialty") 			=> 	"specname",
				__("Specialty Description") 	=> 	"specdesc"
			),
			array ("", __("NO DESCRIPTION"))
			)."
			<CENTER>
			<TABLE>".
    (($action=="modform") ? "
      <FORM ACTION=\"$this->page_name\" METHOD=POST>
      <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
      <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"> 
      <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\">
      " : "
      <FORM ACTION=\"$this->page_name\" METHOD=POST>
      <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
      <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"> 
    ")."
      
   
   <TR><TD ALIGN=RIGHT>   
    ".__("Specialty")." :
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"specname\" SIZE=10 MAXLENGTH=50 
     VALUE=\"".prepare($specname)."\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>   
    ".__("Specialty Description")." :
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"specdesc\" SIZE=30 MAXLENGTH=100
     VALUE=\"".prepare($specdesc)."\">
   </TD></TR>
   
   <TR><TD ALIGN=CENTER COLSPAN=2>   
    <INPUT TYPE=SUBMIT VALUE=\"".(($action=="modform") ? 
      __("Update") : __("Add"))." \">
    <INPUT TYPE=RESET  VALUE=\"".__("Remove Changes")."\">
    </FORM>
   </TD></TR>
   </TABLE>
   </CENTER>
  ";
  
  if ($action=="modform") $display_buffer .= "
    <CENTER>
    <A HREF=\"$this->page_name?module=$module&action=view\"
     >".__("Abandon Modification")."</A>
    </CENTER>
  ";
	} // end function ProviderSpecialtiesMaintenance->view()

} // end class ProviderSpecialtiesMaintenance

register_module ("ProviderSpecialtiesMaintenance");

?>
