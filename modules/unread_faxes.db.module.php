<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class UnreadFaxes extends MaintenanceModule {

	var $MODULE_NAME = "Unread Faxes";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_HIDDEN = true;

	var $MODULE_FILE = __FILE__;

	var $table_name = 'unreadfax';

	function UnreadFaxes ( ) {
		// Set menu notify on the sidebar (or wherever the current
		// template decides to hide the notify items)
		$this->_SetHandler('MenuNotifyItems', 'notify');

		// Add this as a main menu handler as well
		$this->_SetHandler('MainMenu', 'MainMenuNotify');

		$this->table_definition = array (
			'urfdate'      => SQL__DATE, // date received
			'urffilename'  => SQL__VARCHAR(150), // temp file name
			'urftype'      => SQL__VARCHAR(50), // document type
			'urfpatient'   => SQL__INT_UNSIGNED(0),
			'urfphysician' => SQL__INT_UNSIGNED(0),
			'urfnote'      => SQL__TEXT, // note from filer
			'id' => SQL__SERIAL
		);

		// Call parent constructor
		$this->MaintenanceModule();
	} // end constructor UnreadFaxes

	function notify ( ) {
		// Get current user object
		$user = CreateObject('FreeMED.User');

		// If user isn't a physician, no handler required
		if (!$user->isPhysician()) return false;

		// Get number of unread faxes from table
		$result = $GLOBALS['sql']->query("SELECT COUNT(*) AS count ".
			"FROM ".$this->table_name." ".
			"WHERE urfphysician='".addslashes($user->getPhysician())."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		if ($r['count'] < 1) { return false; }

		return array (sprintf(__("You have %d unread faxes"), $r['count']), 
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

		// Get number of unread faxes from table
		$result = $GLOBALS['sql']->query("SELECT COUNT(*) AS count ".
			"FROM ".$this->table_name." ".
			"WHERE urfphysician='".addslashes($GLOBALS['this_user']->getPhysician())."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		if ($r['count'] < 1) { return false; }

		return array (
			__("Unread Faxes"),
			( $r['count']==1 ?
			__("There is currently 1 unread fax in the system.") :
			sprintf(__("There are currently %d unread faxes in the system."), $unfiled) )." ".
			"<a href=\"module_loader.php?module=".urlencode(get_class($this))."&action=display\">".
			"[".__("Read")."]</a>",
			"img/facsimile_icon.png"
		); 
	} // end method MainMenuNotify

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
		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE urfphysician='".addslashes($this_user->getPhysician())."' ".
                        freemed::itemlist_conditions(false)." ".
                        ( $condition ? 'AND '.$condition : '' )." ".
                        "ORDER BY urfdate";
                $result = $sql->query ($query);

                $display_buffer .= freemed_display_itemlist(
                        $result,
                        $this->page_name,
                        array (
                                __("Date")        => "urfdate",
				__("Patient")     => "urfpatient",
				" "               => "urfpatient",
				__("Description") => "urfnote"
                        ), // array
                        array (
                                "",
				"",
				"",
                                __("NO DESCRIPTION")
                        ),
			array (
				"",
				"patient" => "ptlname",
				"patient " => "ptfname",
				""
			),
                        NULL, NULL,
                        ITEMLIST_VIEW | ITEMLIST_DEL
                );
                $display_buffer .= "\n<p/>\n";
	} // end method view

	function display ( ) {
		global $display_buffer, $id;

		if ($_REQUEST['submit_action'] == __("Sign")) {
			$this->mod();
			return false;
		}

		if ($_REQUEST['submit_action'] == __("Delete")) {
			$this->del();
			return false;
		}

		if ($_REQUEST['submit_action'] == __("Send to Another Provider")) {
			$this->move_to_another_provider();
			return false;
		}

		$result = $GLOBALS['sql']->query("SELECT * FROM ".
			$this->table_name." WHERE id='".addslashes($_REQUEST['id'])."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		$this_patient = CreateObject('FreeMED.Patient', $r['urfpatient']);
		$display_buffer .= "
		<form action=\"".$this->page_name."\" method=\"post\" name=\"myform\">
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"view\"/>
		<input type=\"hidden\" name=\"date\" value=\"".prepare($r['urfdate'])."\"/>
		<input type=\"hidden\" name=\"been_here\" value=\"1\"/>
		<div align=\"center\">
                <embed SRC=\"data/fax/unread/".$r['urffilename']."\"
		BORDER=\"0\" 
		FLAGS=\"width=100% height=100% passive=yes toolbar=yes keyboard=yes zoom=stretch\"
                PLUGINSPAGE=\"".COMPLETE_URL."support/\"
                TYPE=\"image/x.djvu\" WIDTH=\"".
		( $GLOBALS['__freemed']['Mozilla'] ? '800' : '100%' ).
		"\" HEIGHT=\"800\"></embed>

		</div>
		<div align=\"center\">
		".html_form::form_table(array(
			__("Date") => $r['urfdate'],
			__("Patient") => $this_patient->fullName(),
			__("Type") => $r['urftype'],
			__("Note") => $r['urfnote']
		))."
		</div>
		<div>
		<i>".__("By clicking on the 'Sign' button below, I agree that I am the physician in question and have reviewed this facsimile transmission.")."</i>
		</div>
		<div align=\"center\">
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Sign")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Cancel")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Send to Another Provider")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"onClick=\"if (confirm('".addslashes(__("Are you sure that you want to permanently remove this fax?"))."')) { return true; } else { return false; }\" ".
		"class=\"button\" value=\"".__("Delete")."\"/>
		</div>
		</form>
		";
	} // end method display

	function move_to_another_provider ( $_id = -1 ) {
		if ($id > 0) {
			$id = $_id;
		} else {
			$id = $_REQUEST['id'];
		}
		$rec = freemed::get_link_rec($id, $this->table_name);

		$filename = freemed::secure_filename($rec['urffilename']);

		// Fax sanity check
		if (!file_exists('data/fax/unread/'.$filename) or empty($filename)) {
			syslog(LOG_INFO, "UnreadFax| attempted to file fax that doesn't exist ($filename)");
			return false;
		}

		if ($_REQUEST['to']) {
			$q = $GLOBALS['sql']->update_query(
				$this->table_name,
				array (
					'urfphysician' => $_REQUEST['to']
				), array ('id' => $id)
			);
			$r = $GLOBALS['sql']->query($q);
			if ($_id == -1) {
			$GLOBALS['display_buffer'] .= '<br/>'.
				template::link_bar(array(
					__("View Patient Record") =>
					'manage.php?id='.urlencode($rec['urfpatient']),
					__("Return to Unread Fax Menu") =>
					$this->page_name.'?module='.get_class($this)
				));
			}
			return false;
		}

		// Otherwise, show form
		global $display_buffer;
		$display_buffer .= "
		<form action=\"".$this->page_name."\" method=\"post\" name=\"myform\">
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"view\"/>
		";
		$display_buffer .= html_form::form_table(array(
			__("Provider") => module_function('providermodule', 'widget', array ( 'to' ))
		));
		$display_buffer .= "<center><input type=\"submit\" name=\"submit_action\" value=\"".__("Send to Another Provider")."\" /></center></form>\n";
	} // end method move_to_another_provider

	function mod ($_id = -1) {
		if ($id > 0) {
			$id = $_id;
		} else {
			$id = $_REQUEST['id'];
		}
		$rec = freemed::get_link_rec($id, $this->table_name);

		$filename = freemed::secure_filename($rec['urffilename']);

		// Fax sanity check
		if (!file_exists('data/fax/unread/'.$filename) or empty($filename)) {
			syslog(LOG_INFO, "UnreadFax| attempted to file fax that doesn't exist ($filename)");
			return false;
		}

		// Extract type and category
		list ($type, $cat) = explode('/', $rec['urftype']);

		// Create user object
		$this_user = CreateObject('FreeMED.User');
		
		// Insert new table query in unread
		$query = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
			'images',
			array (
				"imagedt" => $rec['urfdate'],
				"imagepat" => $rec['urfpatient'],
				"imagetype" => $type,
				"imagecat" => $cat,
				"imagedesc" => $rec['urfnote'],
				"imagephy" => $rec['urfphysician'],
				"imagereviewed" => $this_user->user_number
			)
		));
		$new_id = $GLOBALS['sql']->last_record($query, 'images');

		$new_filename = freemed::image_filename(
			freemed::secure_filename($rec['urfpatient']),
			$new_id,
			'djvu',
			true
		);

		$query = $GLOBALS['sql']->update_query(
			'images',
			array ( 'imagefile' => $new_filename ),
			array ( 'id' => $new_id )
		);
		$result = $GLOBALS['sql']->query( $query );
		syslog(LOG_INFO, "UnreadFax| query = $query, result = $result");

		// Move actual file to new location
		//echo "mv data/fax/unread/$filename $new_filename -f<br/>\n";
		$dirname = dirname($new_filename);
		`mkdir -p "$dirname"`;
		//echo "mkdir -p $dirname";
		`mv "data/fax/unread/$filename" "$new_filename" -f`;

		$GLOBALS['display_buffer'] .= __("Moved fax to scanned documents.");

		$GLOBALS['sql']->query("DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'");

		global $refresh;
		//$refresh = $page_name."?module=".get_class($this);

		if ($_id == -1) {
			$GLOBALS['display_buffer'] .= '<br/>'.
				template::link_bar(array(
					__("View Patient Record") =>
					'manage.php?id='.urlencode($rec['urfpatient']),
					__("Return to Unread Fax Menu") =>
					$this->page_name.'?module='.get_class($this)
				));
		}
	} // end method mod

} // end class UnreadFaxes

register_module('UnreadFaxes');

?>
