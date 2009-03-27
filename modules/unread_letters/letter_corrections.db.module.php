<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class LetterCorrections extends MaintenanceModule {

	var $MODULE_NAME = "Letter Corrections";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_HIDDEN = true;

	var $MODULE_FILE = __FILE__;

	function LetterCorrections ( ) {
		// Set menu notify on the sidebar (or wherever the current
		// template decides to hide the notify items)
		$this->_SetHandler('MenuNotifyItems', 'notify');

		// Add this as a main menu handler as well
		$this->_SetHandler('MainMenu', 'MainMenuNotify');

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor LetterCorrections

	function notify ( ) {
		// Get current user object
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('FreeMED.User');
		}

		// Get number of items to correct
		$result = $GLOBALS['sql']->query("SELECT COUNT(*) AS count ".
			"FROM lettersrepository ".
			"WHERE letteruser='".addslashes($GLOBALS['this_user']->user_number)."' ".
			"AND LENGTH(lettercorrect) > 0");
		$r = $GLOBALS['sql']->fetch_array($result);
		if ($r['count'] < 1) { return false; }

		return array (sprintf(__("You have %d letters to correct"), $r['count']), 
			"module_loader.php?module=".urlencode(get_class($this)).
			"&action=display");
	} // end method notify

	function MainMenuNotify ( ) {
		// Try to import the user object
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('FreeMED.User');
		}

		// Get number of items to correct
		$result = $GLOBALS['sql']->query("SELECT COUNT(*) AS count ".
			"FROM lettersrepository ".
			"WHERE letteruser='".addslashes($GLOBALS['this_user']->user_number)."' ".
			"AND LENGTH(lettercorrect) > 0");
		$r = $GLOBALS['sql']->fetch_array($result);
		if ($r['count'] < 1) { return false; }

		return array (
			__("Letter Corrections"),
			"<a href=\"module_loader.php?module=".urlencode(get_class($this)).
			"&action=display\">".
			sprintf(__("You have %d letters to correct"), $r['count']).
			"</a>"
		); 
	} // end method MainMenuNotify

	// Throw back to letters repository if they try to click 'ADD'
	function addform ( ) {
		Header('Location: module_loader.php?module=lettersrepository&action=addform');
		die();
	}

	// For some strange reason, action=display calls method view.
	// Go figure.
	function view ( ) {
		// Get current user object
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}

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
		$query = "SELECT * FROM lettersrepository ".
			"WHERE letteruser='".addslashes($this_user->user_number)."' ".
			"AND LENGTH(lettercorrect) > 0 ".
                        freemed::itemlist_conditions(false)." ".
                        ( $condition ? 'AND '.$condition : '' )." ".
                        "ORDER BY letterdt";
                $result = $sql->query ($query);

                $display_buffer .= freemed_display_itemlist(
                        $result,
                        $this->page_name,
                        array (
                                __("Date")        => "letterdt",
				__("Patient")     => "letterpatient",
				" "               => "letterpatient"
                        ), // array
                        array (
                                "",
				"",
				""
                        ),
			array (
				"",
				"patient" => "ptlname",
				"patient " => "ptfname"
			),
                        NULL, NULL,
                        ITEMLIST_VIEW | ITEMLIST_DEL
                );
                $display_buffer .= "\n<p/>\n";
	} // end method view

	function del ( ) {
		$this->table_name = 'lettersrepository';
		$this->_del();
	}

	function display ( ) {
		global $display_buffer, $id;

		if ($_REQUEST['submit_action'] == __("Send to Provider")) {
			$this->mod();
			return false;
		}

		if ($_REQUEST['submit_action'] == __("Delete")) {
			$this->del();
			return false;
		}

		$result = $GLOBALS['sql']->query("SELECT * FROM lettersrepository WHERE id='".addslashes($_REQUEST['id'])."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		global $lettertext; $lettertext = $r['lettertext'];
		$this_patient = CreateObject('FreeMED.Patient', $r['letterpatient']);
		$display_buffer .= "
		<form action=\"".$this->page_name."\" method=\"post\" name=\"myform\">
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"view\"/>
		<input type=\"hidden\" name=\"date\" value=\"".prepare($r['urldate'])."\"/>
		<input type=\"hidden\" name=\"been_here\" value=\"1\"/>
		<div align=\"left\" style=\"border: 1px dotted; padding: 1em;\">
		".freemed::rich_text_area('lettertext', 25, 70)."
		</div>
		<div>
		<b>".__("Corrections")."</b><br/>
		".prepare(str_replace("\n", "<br/>\n", $r['lettercorrect']))."
		</div>
		<div align=\"center\">
		".html_form::form_table(array(
			"Date" => $r['letterdt'],
			"Entered By" => $r['lettertypist'],
			"Patient" => $this_patient->fullName(),
			"Fax To Number" => $r['letterfax'],
		))."
		</div>
		<div align=\"center\">
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Send to Provider")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Cancel")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"onClick=\"if (confirm('".addslashes(__("Are you sure that you want to permanently remove this letter?"))."')) { return true; } else { return false; }\" ".
		"class=\"button\" value=\"".__("Delete")."\"/>
		</div>
		</form>
		";
	} // end method display

	function mod ($_id = -1) {
		if ($id > 0) {
			$id = $_id;
		} else {
			$id = $_REQUEST['id'];
		}

		// Create user object
		$this_user = CreateObject('FreeMED.User');
		
		// Insert new table query in unread
		$query = $GLOBALS['sql']->update_query(
			'lettersrepository',
			array ( 
				// Commit corrections
				'lettertext' => $_REQUEST['lettertext'],
				// Set as null so it doesn't appear here again
				'lettercorrect' => ''
			),
			array ( 'id' => $id )
		);
		$result = $GLOBALS['sql']->query( $query );
		syslog(LOG_INFO, "LetterCorrections| query = $query, result = $result");

		global $refresh;
		//$refresh = $page_name."?module=".get_class($this);

		if ($_id == -1) {
			$GLOBALS['display_buffer'] .= '<br/>'.
				template::link_bar(array(
					__("View Patient Record") =>
					'manage.php?id='.urlencode($rec['letterpatient']),
					__("Return to Letter Corrections Menu") =>
					$this->page_name.'?module='.get_class($this)
				));
		}
	} // end method mod

} // end class LetterCorrections

register_module('LetterCorrections');

?>
