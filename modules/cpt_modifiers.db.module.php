<?php
 // $Id$
 // note: cpt modifier functions
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class CptModifiersMaintenance extends MaintenanceModule {

	var $MODULE_NAME    = "CPT Modifiers Maintenance";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name    = "CPT Modifiers";
	var $table_name     = "cptmod";

	var $variables = array (
		"cptmod",
		"cptmoddescrip"
	);

	function CptModifiersMaintenance () {
		global $display_buffer;

			// table definition (inside constructor, as outside definitions
			// do NOT allow function calls)
		$this->table_definition = array (
			"cptmod"		=>	SQL__CHAR(2),
			"cptmoddescrip"		=>	SQL__VARCHAR(50),
			"id"			=>	SQL__SERIAL
		);
		if ($debug) {
		global $sql;$display_buffer .= "query = \"".$sql->create_table_query(
			$this->table_name, $this->table_definition).
			"\"<BR>\n";
		} // end if $debug

			// Run constructor
		$this->MaintenanceModule();
	} // end constructor CptModifiersMaintenance

	function form () {
		global $display_buffer, $action;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		switch ($action) { // inner switch
			case "addform":
			break;

			case "modform":
			if ($id<1) trigger_error ("NO ID", E_USER_ERROR);
			$r = freemed::get_link_rec ($id, $this->table_name);
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = $v;
			}
			break;
		} // end inner switch

		$display_buffer .= "
		<p/>
		<form ACTION=\"".$this->page_name."\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
		( ($action=="addform") ? "add" : "mod" )."\"/> 
		<input TYPE=\"HIDDEN\" NAME=\"id\"   VALUE=\"".prepare($id)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\"   VALUE=\"".prepare($module)."\"/>
		".html_form::form_table ( array (
		__("Modifier") =>
		array(
			'content' => html_form::text_widget('cptmod', 2)	
		),

		__("Description") =>
		array(
		    'help' => __("Helpful description of the modifier"),
		    'content' => html_form::text_widget('cptmoddescrip', 20, 30)
		)
		) )."
		<p/>
		<div ALIGN=\"CENTER\">
		<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\" ".
		( ($action=="addform") ? __("Add") : __("Modify") )." \"/>
		<input class=\"button\" NAME=\"submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".__("Cancel")."\"/>
		</div></form>
		";
	} // end function CptModifiersMaintenance->form()

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query (
				"SELECT cptmod,cptmoddescrip,id ".
				"FROM ".addslashes($this->table_name)." ".
				freemed::itemlist_conditions().
                		"ORDER BY cptmod,cptmoddescrip"
			),
			$this->page_name,
			array (
				__("Modifier")		=>	"cptmod",
				__("Description")	=>	"cptmoddescrip"
			),
			array ("", __("NO DESCRIPTION"))
		);
	} // end function CptModifiersMaintenance->view()

} // end class CptModifiersMaintenance

register_module ("CptModifiersMaintenance");

?>
