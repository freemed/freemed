<?php
  // $Id$
  // note: physician/provider specialties db
  // code: adam b (gdrago23@yahoo.com) -- complete rewrite
  // lic : GPL, v2

if(!defined("__PROVIDER_SPECIALTIES_MODULE_PHP__")) {

define(__PROVIDER_SPECIALTIES_MODULE_PHP__, true);

class providerSpecialtiesMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME    = "Provider Specialties Maintenance";
	var $MODULE_VERSION = "0.1";

	var $table_name     = "specialties";
	var $record_name    = "Specialty";
	var $order_field    = "specname";

	var $variables      = array (
		"specname",
		"specdesc"
	);

	function providerSpecialtiesMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor providerSpecialtiesMaintenance

	function form () { $this->view(); }

	function view () {
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		if ($action=="modform") {
			$result = $sql->query("SELECT * FROM $this->table_name ".
				"WHERE id='".addslashes($id)."'");
			$r = $sql->fetch_array($result);
			extract($r);
			break;
		} // if this is a modform...

		echo freemed_display_itemlist(
			$sql->query ("SELECT * FROM $this->table_name ".
				( (strlen($_s_val)>0) ?
					"WHERE $_s_field LIKE '%".addslashes($_s_val)."%' " : "" ).
				"ORDER BY specname,specdesc"),
			$this->page_name,
			array (
				_("Specialty") 			=> 	"specname",
				_("Specialty Description") 	=> 	"specdesc"
			),
			array ("", _("NO DESCRIPTION")), "", "s_page"
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
    <$STDFONT_B>"._("Specialty")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"specname\" SIZE=10 MAXLENGTH=50 
     VALUE=\"".prepare($specname)."\">
   </TD></TR>

   <TR><TD ALIGN=RIGHT>   
    <$STDFONT_B>"._("Specialty Description")." : <$STDFONT_E>
   </TD><TD ALIGN=LEFT>
    <INPUT TYPE=TEXT NAME=\"specdesc\" SIZE=30 MAXLENGTH=100
     VALUE=\"".prepare($specdesc)."\">
   </TD></TR>
   
   <TR><TD ALIGN=CENTER COLSPAN=2>   
    <INPUT TYPE=SUBMIT VALUE=\"".(($action=="modform") ? 
      _("Update") : _("Add"))." \">
    <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
    </FORM>
   </TD></TR>
   </TABLE>
   </CENTER>
  ";
  
  if ($action=="modform") echo "
    <CENTER><$STDFONT_B>
    <A HREF=\"$this->page_name?$_auth&method=$method&action=view\"
     >"._("Abandon Modification")."</A>
    <$STDFONT_E></CENTER>
  ";
	} // end function providerSpecialtiesMaintenance->view()

} // end class providerSpecialtiesMaintenance

register_module ("providerSpecialtiesMaintenance");

} // end if not defined

?>
