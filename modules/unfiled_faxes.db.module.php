<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.MaintenanceModule');

class UnfiledFaxes extends MaintenanceModule {

	var $MODULE_NAME = "Unfiled Faxes";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_FILE = __FILE__;
	var $PACKAGE_MINIMUM_VERSION = "0.6.2";

	var $table_name = 'unfiledfax';

	function UnfiledFaxes ( ) {
		// __("Unfiled Faxes")
		$this->table_definition = array (
			'uffdate'      => SQL__DATE, // date received
			'ufffilename'  => SQL__VARCHAR(150), // temp file name
			'id' => SQL__SERIAL
		);

		// Add main menu notification handler
		$this->_SetHandler('MainMenu', 'notify');
		
		// Form proper configuration information
		$this->_SetMetaInformation('global_config_vars', array(
			'uffax_user'
		));
		$this->_SetMetaInformation('global_config', array(
			__("Single Recipient") =>
			'html_form::select_widget("uffax_user", '.
				'module_function ( "UnfiledFaxes", '.
				'"user_select" ) )'
			)
		);
		
		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor UnfiledFaxes

	function view ( ) {
		global $display_buffer, $sql, $action;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }
		if ($_REQUEST['condition']) { unset($condition); }
		// Check for "view" action (actually display)
                if ($_REQUEST['action']=="view") {
			if (!($_REQUEST['submit_action'] == __("Cancel"))) {
                        	$this->display();
				return false;
			}
                }
		$query = "SELECT * FROM ".$this->table_name." ".
                        freemed::itemlist_conditions(true)." ".
                        ( $condition ? 'AND '.$condition : '' )." ".
                        "ORDER BY uffdate";
                $result = $sql->query ($query);

                $display_buffer .= freemed_display_itemlist(
                        $result,
                        $this->page_name,
                        array (
                                __("Date")        => "uffdate",
                                __("File name")   => "ufffilename"
                        ), // array
                        array (
                                "",
                                __("NO DESCRIPTION")
                        ),
                        NULL, NULL, NULL,
                        ITEMLIST_VIEW | ITEMLIST_DEL
                );
                $display_buffer .= "\n<p/>\n";
	} // end method view

	function display ( ) {
		global $display_buffer, $id;

		switch ($_REQUEST['submit_action']) {
			case __("File"):
			$this->mod();
			return false;

			case __("Delete"):
			$this->del();
			return false;
		}

		$result = $GLOBALS['sql']->query("SELECT * FROM ".
			$this->table_name." WHERE id='".addslashes($_REQUEST['id'])."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		$display_buffer .= "
		<br/><br/><br/>
		<form action=\"".$this->page_name."\" method=\"post\" name=\"myform\">
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"view\"/>
		<input type=\"hidden\" name=\"date\" value=\"".prepare($r['uffdate'])."\"/>
		<input type=\"hidden\" name=\"been_here\" value=\"1\"/>
		<div align=\"center\">
                <embed SRC=\"data/fax/unfiled/".$r['ufffilename']."\"
		BORDER=\"0\"
                PLUGINSPAGE=\"".COMPLETE_URL."support/\"
                TYPE=\"image/x.djvu\" WIDTH=\"100%\" HEIGHT=\"600\"></embed>

		</div>
		<div align=\"center\">
		".html_form::form_table(array(
			__("Date") => $r['uffdate'],
			__("Patient") => freemed::patient_widget("patient"),
			__("Physician") => freemed_display_selectbox ($GLOBALS['sql']->query("SELECT * FROM physician WHERE phyref='no' ORDER BY phylname,phyfname"), "#phylname#, #phyfname#", "physician"),
			__("Type") => html_form::select_widget(
				"type",
				array(
					__("Insurance Card") => "insurance_card",
                                        __("Lab Report") => "lab_report",
                                        __("Miscellaneous") => "misc",
                                        __("Operative Report") => "op_report",
                                        __("Pathology") => "pathology",
                                        __("Patient History") => "patient_history",
                                        __("Questionnaire") => "questionnaire",
                                        __("Radiology") => "radiology",
                                        __("Referral") => "referral"
				)
			),
			__("Note") => html_form::text_widget("note")
		))."
		</div>
		<div align=\"center\">
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("File")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Cancel")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Delete")."\"/>
		</div>
		</form>
		";
	} // end method display

	// Delete method
	function del () {
		$id = $_REQUEST['id'];
		$rec = freemed::get_link_rec($id, $this->table_name);
		$filename = freemed::secure_filename($rec['ufffilename']);

		// Remove file name
		unlink('data/fax/unfiled/'.$filename);

		// Insert new table query in unread
		$this->_del();
	} // end method del

	// Modify method
	function mod () {
		$id = $_REQUEST['id'];
		$rec = freemed::get_link_rec($id, $this->table_name);
		$filename = freemed::secure_filename($rec['ufffilename']);

		// Move actual file to new location
		//echo "mv data/fax/unfiled/$filename data/fax/unread/$filename -f";
		`mv data/fax/unfiled/$filename data/fax/unread/$filename -f`;

		// Insert new table query in unread
		$GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
			'unreadfax',
			array (
				"urfdate" => $_REQUEST['date'],
				"urffilename" => $filename,
				"urfpatient" => $_REQUEST['patient'],
				"urfphysician" => $_REQUEST['physician'],
				"urftype" => $_REQUEST['type'],
				"urfnote" => $_REQUEST['note']
			)
		));

		$GLOBALS['display_buffer'] .= __("Moved fax to unread box.");

		$GLOBALS['sql']->query("DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'");
	} // end method mod

	function notify ( ) {
		// Check to see if we're the person who is supposed to be
		// notified. If not, die out right now.
		$supposed = freemed::config_value('uffax_user');
		if (($supposed > 0) and ($supposed != $_SESSION['authdata']['user'])) {
			return false;
		}
	
		// Decide if we have any "unfiled faxes" in the system
		$query = "SELECT COUNT(*) AS unfiled FROM ".$this->table_name;
		$result = $GLOBALS['sql']->query($query);
		extract($GLOBALS['sql']->fetch_array($result));
		if ($unfiled > 0) {
			return array (
				__("Unfiled Faxes"),
				sprintf(__("There are currently %d unfiled faxes in the system."), $unfiled)."&nbsp;".
				"<a href=\"module_loader.php?module=unfiledfaxes&action=display\" class=\"reverse\">".
				"<img src=\"lib/template/default/add.png\" ".
				"border=\"0\" alt=\"[".__("File")."]\" /></a>"
			);
		} else {
			// For now, we're just going to return nothing so that
			// the box doesn't show up
			return false;
			return array (
				__("Unfiled Faxes"),
				__("There are no unfiled faxes at this time.")
			);
		}
	} // end method notify

	function user_select ( ) {
		$results[__("NONE")] = 0;
		$result = $GLOBALS['sql']->query("SELECT * FROM user ".
			"ORDER BY username");
		while ($r = $GLOBALS['sql']->fetch_array($result)) {
			$results[$r['username']." (".$r['userdescrip'].")"] = $r['id'];
		}
		return $results;
	} // end method user_select

} // end class UnfiledFaxes

register_module('UnfiledFaxes');

?>
