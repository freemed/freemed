<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class SchedulerStatusType extends MaintenanceModule {

	var $MODULE_NAME    = "Scheduler Status Type";
	var $MODULE_AUTHOR  = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $record_name    = "Scheduler Status Type";
	var $table_name     = "schedulerstatustype";

	var $widget_hash    = "##sname## (##sdescrip##)";

	var $variables = array (
		'sname',
		'sdescrip',
		'scolor'
	);

	function SchedulerStatusType () {
		// For i18n: __("Scheduler Status")

		$this->table_definition = array (
			'sname'		=>	SQL__VARCHAR(50),
			'sdescrip'	=>	SQL__BLOB,
			'scolor'	=>	SQL__CHAR(7),
			'id'		=>	SQL__SERIAL
		);

		$this->rpc_field_map = array (
			'name' => 'sname',
			'description' => 'sdescrip',	
			'color' => 'scolor'
		);

			// Run constructor
		$this->MaintenanceModule();
	} // end constructor SchedulerStatusType

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
		<input TYPE=\"HIDDEN\" NAME=\"return\"   VALUE=\"".prepare($_REQUEST['return'])."\"/>
		".html_form::form_table ( array (
		__("Status Name") => html_form::text_widget('sname', array('length'=>50)),
		__("Description") => html_form::text_widget('sdescrip', array('length'=>50)),
		__("Color") => html_form::color_widget('scolor')

		) )."
		<p/>
		<div ALIGN=\"CENTER\">
		<input class=\"button\" TYPE=\"SUBMIT\" VALUE=\" ".
		( ($action=="addform") ? __("Add") : __("Modify") )." \"/>
		<input class=\"button\" NAME=\"submit\" TYPE=\"SUBMIT\" ".
			"VALUE=\"".__("Cancel")."\"/>
		</div></form>
		";
	} // end method form

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query (
				"SELECT sname, sdescrip, id ".
				"FROM ".addslashes($this->table_name)." ".
				freemed::itemlist_conditions().
                		"ORDER BY sname"
			),
			$this->page_name,
			array (
				__("Name") => 'sname',
				__("Description") => 'sdescrip'
			),
			array ("", __("NO DESCRIPTION"))
		);
	} // end method view

	function _update ( ) {
		$version = freemed::module_version ( $this->MODULE_NAME );
	} // end method _update

} // end class SchedulerStatusType

register_module ("SchedulerStatusType");

?>
