<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class UnreadLetters extends MaintenanceModule {

	var $MODULE_NAME = "Unread Letters";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_DESCRIPTION = "Providers can verify letters that have been entered into the letters repository and have them automatically faxed to the appropriate destination.";
	var $MODULE_HIDDEN = true;

	var $MODULE_FILE = __FILE__;

	function UnreadLetters ( ) {
		// Set menu notify on the sidebar (or wherever the current
		// template decides to hide the notify items)
		$this->_SetHandler('MenuNotifyItems', 'notify');

		// Add this as a main menu handler as well
		$this->_SetHandler('MainMenu', 'MainMenuNotify');

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor UnreadLetters

	function notify ( ) {
		// Try to import the user object
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('FreeMED.User');
		}

		// If user isn't a physician, no handler required
		if (!$GLOBALS['this_user']->isPhysician()) return false;

		// Get number of unread letters from table
		$result = $GLOBALS['sql']->query("SELECT COUNT(*) AS count ".
			"FROM lettersrepository ".
			"WHERE letterfrom='".addslashes($GLOBALS['this_user']->getPhysician())."' ".
			"AND lettercorrect=''");
		$r = $GLOBALS['sql']->fetch_array($result);
		if ($r['count'] < 1) { return false; }

		return array (sprintf(__("You have %d unread letters"), $r['count']), 
			"module_loader.php?module=".urlencode(get_class($this)).
			"&action=display");
	} // end method notify

	function MainMenuNotify ( ) {
		// Try to import the user object
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('FreeMED.User');
		}

		// Only show something if they are a physician
		if (!$GLOBALS['this_user']->isPhysician()) {
			return false;
		}

		// Get number of unread letters from table
		$result = $GLOBALS['sql']->query("SELECT COUNT(*) AS count ".
			"FROM lettersrepository ".
			"WHERE letterfrom='".addslashes($GLOBALS['this_user']->getPhysician())."' ".
			"AND lettercorrect=''");
		$r = $GLOBALS['sql']->fetch_array($result);
		if ($r['count'] < 1) { return false; }

		return array (
			__("Unread Letters"),
			"<a href=\"module_loader.php?module=".urlencode(get_class($this)).
			"&action=display\">".
			sprintf(__("You have %d unread letters"), $r['count']).
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
			"WHERE letterfrom='".addslashes($this_user->getPhysician())."' AND lettercorrect='' ".
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
				" "               => "letterpatient",
				__("Subject")     => "lettersubject"
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

		if ($_REQUEST['submit_action'] == __("Sign")) {
			$this->mod();
			return false;
		}

		if ($_REQUEST['submit_action'] == __("Return for Corrections")) {
			$this->mod();
			return false;
		}

		if ($_REQUEST['submit_action'] == __("Delete")) {
			$this->del();
			return false;
		}

		$result = $GLOBALS['sql']->query("SELECT * FROM lettersrepository WHERE id='".addslashes($_REQUEST['id'])."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		$to_provider = CreateObject('FreeMED.Physician', $r['letterto']);
		$this_patient = CreateObject('FreeMED.Patient', $r['letterpatient']);
		$display_buffer .= "
		<form action=\"".$this->page_name."\" method=\"post\" name=\"myform\">
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"view\"/>
		<input type=\"hidden\" name=\"date\" value=\"".prepare($r['urldate'])."\"/>
		<input type=\"hidden\" name=\"been_here\" value=\"1\"/>
		<div align=\"left\" style=\"border: 1px dotted; padding: 1em;\">
		".prepare(str_replace("\n", "<br/>\n", $r['lettertext']))."

		</div>
		<div align=\"center\">
		".html_form::form_table(array(
			"Date" => $r['letterdt'],
			"Entered By" => $r['lettertypist'],
			"To" => $to_provider->fullName(),
			"Patient" => $this_patient->fullName(),
			"Fax To Number" => $r['letterfax'],
		))."
		</div>
		<div>
		<b>".__("Corrections").":</b><br/>
		".freemed::rich_text_area('corrections', 25, 70)."
		</div>
		<div>
		<i>".__("By clicking on the 'Sign' button below, I agree that I am the physician in question and have reviewed this letter.")."</i>
		</div>
		<div align=\"center\">
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Sign")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Return for Corrections")."\"/>
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

		// If we're returning for corrections ...
		if ($_REQUEST['submit_action'] == __("Return for Corrections")) {
			$query = $GLOBALS['sql']->update_query(
				'lettersrepository',
				array (
					'lettercorrect' => $_REQUEST['corrections']
				),
				array ( 'id' => $id )
			);
			$result = $GLOBALS['sql']->query($query);
			syslog(LOG_INFO, "UnreadLetters| query = $query, result = $result");
			if ($_id == -1) {
				$GLOBALS['display_buffer'] .= '<br/>'.
					template::link_bar(array(
						__("View Patient Record") =>
						'manage.php?id='.urlencode($rec['letterpatient']),
						__("Return to Unread Letters Menu") =>
						$this->page_name.'?module='.get_class($this)
					));
			}
			return false;
		} // end dealing with return for corrections
		
		$rec_save = $rec = freemed::get_link_rec($id, 'lettersrepository');

		// Take out fields we don't need
		unset($rec['letterfax']);
		unset($rec['letteruser']);
		unset($rec['lettercorrect']);
		unset($rec['id']);

		foreach ($rec AS $k => $v) {
			if (!is_integer($k)) { $r[$k] = $v; }
		}

		// Create user object
		$this_user = CreateObject('FreeMED.User');
		
		// Insert new table query in unread
		$query = $GLOBALS['sql']->insert_query(
			'letters',
			$r
		);
		$result = $GLOBALS['sql']->query( $query );
		$new_id = $GLOBALS['sql']->last_record ( $result, 'letters' );
		syslog(LOG_INFO, "UnreadLetters| query = $query, result = $result, new_id = $new_id");

		// If there's a fax number, send it.
		if ($rec_save['letterfax']) {
			// Load the letters module for printing
			include_once(resolve_module('LettersModule'));
			$l = new LettersModule ();

			// Start up TeX renderer
			$TeX = CreateObject('FreeMED.TeX', array (
				'title' => $title,
				'heading' => $heading,
				'physician' => $physician
			));
			$TeX->_buffer = $TeX->RenderFromTemplate(
				$l->print_template,
				$l->_print_mapping($TeX, $new_id)
			);

			// Render to PDF and send
			$file = $TeX->RenderToPDF(true);
			$fax = CreateObject('_FreeMED.Fax', $file, array (
				'sender' => PACKAGENAME.' v'.DISPLAY_VERSION
			));
			$output = $fax->send($rec_save['letterfax']);
			$display_buffer .= "<b>".$output."</b><br/>\n";
			unlink($file);
		}

		$GLOBALS['sql']->query("DELETE FROM lettersrepository ".
			"WHERE id='".addslashes($id)."'");

		global $refresh;
		//$refresh = $page_name."?module=".get_class($this);

		if ($_id == -1) {
			$GLOBALS['display_buffer'] .= '<br/>'.
				template::link_bar(array(
					__("View Patient Record") =>
					'manage.php?id='.urlencode($rec['letterpatient']),
					__("Return to Unread Letters Menu") =>
					$this->page_name.'?module='.get_class($this)
				));
		}
	} // end method mod

} // end class UnreadLetters

register_module('UnreadLetters');

?>
