<?php
 // $Id$
 // note: icd9 codes database functions
 // code: mark l (lesswin@ibm.net)
 //       jeff b (jeff@ourexchange.net) -- rewrite
 // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class IcdMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "ICD Maintenance";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $table_name 	 = "icd9";
	var $record_name	 = "ICD9 Code";
	var $order_field	 = "icd9code,icdnum";

	var $variables = array (
		"icd9code",
		"icd10code",
		"icd9descrip",
		"icd10descrip",
		"icdmetadesc",
		"icddrg",
		"icdng",
		// "icdnum", // Do not allow this to be manually set
		"icdamt",
		"icdcoll"
	);

	function IcdMaintenance () {
		$this->_SetMetaInformation('global_config_vars', array('icd'));
		$this->_SetMetaInformation('global_config', array(
			__("ICD Code Type") =>
			'html_form::select_widget("icd", '.
			'array ('.
				'"ICD9" => "9",'.
				'"ICD10" => "10"'.
			'))'
		));
		$this->table_definition = array (
			'icd9code' => SQL_VARCHAR(6),
			'icd10code' => SQL_VARCHAR(7),
			'icd9descrip' => SQL_VARCHAR(45),
			'icd10descrip' => SQL_VARCHAR(45),
			'icdmetadesc' => SQL_VARCHAR(30),
			'icdng' => SQL_DATE,
			'icddrg' => SQL_DATE,
			'icdnum' => SQL_INT_UNSIGNED(0),
			'icdamt' => SQL_REAL,
			'icdcoll' => SQL_REAL,
			'id' => SQL_SERIAL
		);
	
		$this->MaintenanceModule();
	} // end constructor IcdMaintenance

	function form () {
		global $display_buffer;
		foreach ($GLOBALS as $k => $v) { global ${$k}; }

		switch ($action) { // internal action switch
			case "addform":
			break;

			case "modform":
			if (!$been_here) {
				$r = freemed::get_link_rec ($id,$this->table_name);
				foreach ($r AS $k => $v) {
					global ${$k};
					${$k} = stripslashes($v);
				}
				$icddrg = sql_expand($icddrg);
				$icdamt = bcadd($icdamt, 0,2);
				$icdcoll = bcadd($icdcoll,0,2);
				$been_here=1;
			}
			break;
		} // end internal action switch

		$display_buffer .= "
		<p/>
		<form ACTION=\"$this->page_name\" METHOD=\"POST\">
		<input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"".
		( ($action=="addform") ? "add" : "mod" )."\"/> 
		<input TYPE=\"HIDDEN\" NAME=\"id\" VALUE=\"".prepare($id)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"been_here\" VALUE=\"1\"/>
		<input TYPE=\"HIDDEN\" NAME=\"module\" VALUE=\"".prepare($module)."\"/>
		";

	$display_buffer .= html_form::form_table(array(
		__("Code")." (".__("ICD9").")" =>
		html_form::text_widget("icd9code", 10, 6),

		__("Meta Description") =>
		html_form::text_widget("icdmetadesc", 10, 30),

		__("Code")." (".__("ICD10").")" =>
		html_form::text_widget("icd10code", 10, 7),

		__("Description")." (".__("ICD9").")" =>
		html_form::text_widget("icd9descrip", 20, 45),
    
		__("Description")." (".__("ICD10").")" =>
		html_form::text_widget("icd10descrip", 20, 45),

		__("Diagnosis Related Groups") =>
		freemed::multiple_choice (
			"SELECT * FROM diagfamily ORDER BY dfname, dfdescrip",
			"##dfname## (##dfdescrip##)",
			"icddrg",
			fm_join_from_array($icddrg)
		)
	));

		$display_buffer .= "
		<p/>
		<div ALIGN=\"CENTER\">
		<input class=\"button\" type=\"SUBMIT\" value=\" ".
			( ($action=="addform") ? __("Add") : __("Modify") )." \"/>
		<input class=\"button\" type=\"RESET\" value=\" ".__("Clear")." \"/>
		<input class=\"button\" type=\"SUBMIT\" name=\"submit\" ".
			"value=\"".__("Cancel")."\"/>
		</div></form>
		";
	} // end function IcdMaintenance->form

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query(
				"SELECT * ".
				"FROM $this->table_name ".
				freemed::itemlist_conditions()." ".
				"ORDER BY $this->order_field"
			),
			$this->page_name,
			array (
				__("Code")        => 	"icd9code",
				__("Description") =>	"icd9descrip"
			),
			array ("", __("NO DESCRIPTION")),
			"", 
			"t_page"
		);
	} // end function IcdMaintenance->view

} // end class IcdMaintenance

register_module ("IcdMaintenance");

?>
