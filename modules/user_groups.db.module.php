<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class UserGroupsMaintenance extends MaintenanceModule {
	// __("User Groups Maintenance")
	var $MODULE_NAME    = "User Groups Maintenance";
	var $MODULE_AUTHOR  = "jeff@ourexchange.net";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE    = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.1';

	var $table_name     = "usergroup";
	// __("User Group")
	var $record_name    = "User Group";
	var $order_field    = "usergroupname";

	var $widget_hash    = '##usergroupname##';

	var $variables = array (
		"usergroupname",
		"usergroupfac",
		"usergroupdtadd",
		"usergroupdtmod",
		"usergroup"
	);

	function UserGroupsMaintenance () {
		global $usergroupdtmod;
		$usergroupdtmod = date("Y-m-d");

		// Table definition
		$this->table_definition = array (
			'usergroupname' => SQL__VARCHAR(100),
			'usergroupfac' => SQL__INT_UNSIGNED(0),
			'usergroupdtadd' => SQL__DATE,
			'usergroupdtmod' => SQL__DATE,
			'usergroup' => SQL__TEXT,
			'id' => SQL__SERIAL
		);

		// Run constructor
		$this->MaintenanceModule();
	} // end constructor UserGroupsMaintenance

	function view () {  
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist(
			$sql->query(
				"SELECT usergroupname,usergroupfac,id ".
				"FROM ".$this->table_name." ".
				freemed::itemlist_conditions()." ".
				"ORDER BY usergroupname"
			),
			$this->page_name,
			array (
				__("User Group Name") => "usergroupname",
				__("Facility")     => "usergroupfac"
			),
			array ("",""),
			array (
				""         => "",
				"facility" => "psrname"
			)
		); // display main itemlist
	} // end method view

	function form () {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		if ($_REQUEST['__submit'] == __("Cancel")) {
			global $refresh;
			$refresh = $this->page_name.'?module='.get_class($this);
			return false;
		}

		// too much data for this now
		//$this->view();

		switch($action) { // inner action switch
			case "modform":
				if (strlen($id)<1) {
					$action="addform";
					break;
				}
				foreach ($this->variables AS $k => $v) { global ${$v}; }
				$r = freemed::get_link_rec($id, $this->table_name);
				extract ($r);
				break;
			case "addform": // addform *is* the default
			default:
				// nothing right here...
				break;
		} // inner action switch

		// set date of addition if not set 
		if (!isset($usergroupdtadd)) $usergroupdtadd = date('Y-m-d');

		$display_buffer .= "
		<table CELLSPACING=\"0\" CELLPADDING=\"0\" BORDER=\"0\" WIDTH=\"100%\">
		<tr><td ALIGN=\"CENTER\">
		<form ACTION=\"$this->page_name\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
		  (($action=="modform") ? "mod" : "add")."\"> 
		<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"usergroupdtadd\" VALUE=\"".prepare($usergroupdtadd)."\"/>";
 
		$display_buffer .= html_form::form_table( array (

			__("User Group Name") => 
				html_form::text_widget('usergroupname', 20, 100),
		
			__("Facility") => freemed_display_selectbox(

					$sql->query("SELECT psrname,psrnote,id FROM facility ORDER BY psrname,psrnote"),
					"#psrname# [#psrnote#]",
       					"usergroupfac"),

			__("Users") => freemed::multiple_choice(
					"SELECT username,userdescrip,id FROM user WHERE username != '' ORDER BY username",
					"##username## (##userdescrip##)",
					"usergroup",
					$usergroup,
					false)
		) );

		$display_buffer .= "<p/>
		<tr><td ALIGN=\"CENTER\">
		<input CLASS=\"button\" name=\"__submit\" TYPE=\"SUBMIT\" VALUE=\"".
		(($action=="modform") ? __("Modify") : __("Add"))."\"/>
		<input CLASS=\"button\" name=\"__submit\" TYPE=\"SUBMIT\" VALUE=\"".__("Cancel")."\"/>
		<input CLASS=\"button\" TYPE=\"RESET\" VALUE=\"".__("Remove Changes")."\"/>
		</form>
		</td></tr>
		</table>
		";
	} // end method form

} // end class UserGroupsMaintenance

register_module ("UserGroupsMaintenance");

?>
