<?php
  // $Id$
  // note: provider groups, used for booking? and user levels
  // code: jeff b (jeff@univrel.pr.uconn.edu) -- template
  //       adam b (gdrago23@yahoo.com) -- redesign and update
  // lic : GPL

if (!defined("__PROVIDER_GROUPS_MODULE_PHP__")) {

define(__PROVIDER_GROUPS_MODULE_PHP__, true);

include "lib/module_maintenance.php";

class providerGroupsMaintenance extends freemedMaintenanceModule {
	var $MODULE_NAME    = "Provider Groups Maintenance";
	var $MODULE_VERSION = "0.1";

	var $table_name     = "phygroup";
	var $record_name    = "Provider Group";
	var $order_field    = "phygroupname";

	var $variables      = array (
		"phygroupname",
		"phygroupfac",
		"phygroupdtadd",
		"phygroupdtmod"
	);

	function providerGroupsMaintenance () {
		global $phygroupdtmod, $cur_date;
		$this->freemedMaintenanceModule();
		$phygroupdtmod = $cur_date;
	} // end constructor providerGroupsMaintenance

	function view () {  
		global $sql;
		echo freemed_display_itemlist(
			$sql->query("SELECT phygroupname,phygroupfac,id FROM $this->table_name"),
			$this->page_name,
			array (
				_("Physician Group Name") => "phygroupname",
				_("Default Facility")     => "phygroupfac"
			),
			array ("",""),
			array (
				""         => "",
				"facility" => "psrname"
			)
		); // display main itemlist
	} // end function providerGroupsMaintenance->view()

	function form () {
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

		$this->view();

		switch($action) { // inner action switch
			case "modform":
				if (strlen($id)<1) {
					$action="addform";
					break;
				}
				$r = freemed_get_link_rec($id,$this->table_name);
				extract ($r);
				break;
			case "addform": // addform *is* the default
			default:
				// nothing right here...
				break;
		} // inner action switch

		// set date of addition if not set 
		if (!isset($phygroupdtadd)) $phygroupdtadd = $cur_date;
 
		$fac_r = fdb_query("SELECT * FROM facility ORDER BY psrname,psrnote");
		echo "
			<TABLE CELLSPACING=0 CELLPADDING=0 BORDER=0 WIDTH=\"100%\">
   <TR><TD ALIGN=CENTER>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
      (($action=="modform") ? "mod" : "add")."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\">
    <INPUT TYPE=HIDDEN NAME=\"method\" VALUE=\"".prepare($method)."\">
    <INPUT TYPE=HIDDEN NAME=\"phygroupdtadd\" VALUE=\"".prepare($phygroupdtadd)."\">
   
    <$STDFONT_B>"._("Physician Group Name")." : <$STDFONT_E>
    <INPUT TYPE=TEXT NAME=phygroupname SIZE=20 MAXLENGTH=100
     VALUE=\"".prepare($phygroupname)."\">
   </TD></TR>

   <TR><TD ALIGN=CENTER>
    <$STDFONT_B>"._("Default Facility")." : <$STDFONT_E>
    ".freemed_display_selectbox($fac_r, "#psrname# [#psrnote#]", 
       "phygroupfac")."
   </TD></TR>
   
   <TR><TD ALIGN=CENTER>
    <INPUT TYPE=SUBMIT VALUE=\"".
      (($action=="modform") ? _("Modify") : _("Add"))."\">
    <INPUT TYPE=RESET  VALUE=\""._("Remove Changes")."\">
    </FORM>
   </TD></TR>
   </TABLE>
  ";
		if ($action=="modform") echo "
			<CENTER>
			<$STDFONT_B><A HREF=\"$this->page_name?$_auth&method=$method&action=view\"
			 >"._("Abandon Modification")."</A><$STDFONT_E>
			</CENTER>\n";
	} // end function providerGroupsMaintenance->form()

} // end class providerGroupsMaintenance

register_module ("providerGroupsMaintenance");

} // end if not defined

?>
