<?php
	// $Id$
	// $Author$

LoadObjectDependency('FreeMED.MaintenanceModule');

class UnfiledFaxes extends MaintenanceModule {

	var $MODULE_NAME = "Unfiled Faxes";
	var $MODULE_VERSION = "0.1.1";
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

		// Add main menu notification handlers
		$this->_SetHandler('MenuNotifyItems', 'menu_notify');
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
			case __("Send to Provider"):
			case __("File without First Page"):
			$this->mod();
			return false;
			break;

			case __("File Directly"):
			$new_id = $this->mod_direct();
			return false;
			break;

			case __("Delete"):
			$this->del();
			return false;
			break;
		}

		$result = $GLOBALS['sql']->query("SELECT * FROM ".
			$this->table_name." WHERE id='".addslashes($_REQUEST['id'])."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		$display_buffer .= "
		<form action=\"".$this->page_name."\" method=\"post\" name=\"myform\">
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"view\"/>
		<input type=\"hidden\" name=\"date\" value=\"".prepare($r['uffdate'])."\"/>
		<input type=\"hidden\" name=\"been_here\" value=\"1\"/>
		<div align=\"center\">
		".html_form::form_table(array(
			__("Date") => $r['uffdate'],
			__("Patient") => freemed::patient_widget("patient"),
			__("Physician") => freemed_display_selectbox ($GLOBALS['sql']->query("SELECT * FROM physician WHERE phyref='no' ORDER BY phylname,phyfname"), "#phylname#, #phyfname#", "physician"),
			__("Type") => module_function(
				'ScannedDocuments',
				'tc_widget',
				array('type')
			),
			__("Note") => html_form::text_widget("note", array('length'=>150))
		))."
		</div>
		<div align=\"center\">
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Send to Provider")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("File without First Page")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("File Directly")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Cancel")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Delete")."\"/>
		</div>
		<br/><br/><br/>
		<div align=\"center\">
                <embed SRC=\"data/fax/unfiled/".$r['ufffilename']."\"
		BORDER=\"0\"
		FLAGS=\"width=100% height=100% passive=yes toolbar=yes keyboard=yes zoom=stretch\"
                PLUGINSPAGE=\"".COMPLETE_URL."support/\"
                TYPE=\"image/x.djvu\" WIDTH=\"".
		( $GLOBALS['__freemed']['Mozilla'] ? '800' : '100%' ).
		"\" HEIGHT=\"800\"></embed>
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

		// If we're removing the first page, do that now
		if ($_REQUEST['submit_action'] == __("File without First Page")) {
			$command = "/usr/bin/djvm -d data/fax/unfiled/".
				$filename." 1";
			`$command`;
			$GLOBALS['display_buffer'] .= __("Removed first page.")."<br/>\n";
		}

		// Move actual file to new location
		//echo "mv data/fax/unfiled/$filename data/fax/unread/$filename -f";
		`mv data/fax/unfiled/$filename data/fax/unread/$filename -f`;

		// Insert new table query in unread
		$result = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
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
		$new_id = $GLOBALS['sql']->last_record($result);

		$GLOBALS['display_buffer'] .= __("Moved fax to unread box.");
		$GLOBALS['display_buffer'] .= '<p>'.
			'<a href="'.$this->page_name.'?module='.get_class($this).'" class="button">'.__("File Another Fax").'</a>'.
			'</p>';

		$GLOBALS['sql']->query("DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'");

		// Refresh to unfiled faxes main screen
		global $refresh;
		$refresh = $this->page_name . "?".
			"module=".urlencode(get_class($this));

		return $new_id;
	} // end method mod

	function mod_direct ($_id = -1) {
		$id = $_REQUEST['id'];
		$rec = freemed::get_link_rec($id, $this->table_name);
		$filename = freemed::secure_filename($rec['ufffilename']);

		// Extract type and category
		list ($type, $cat) = explode('/', $_REQUEST['type']);
		
		// Insert new table query in unread
		$query = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
			'images',
			array (
				"imagedt" => $_REQUEST['date'],
				"imagepat" => $_REQUEST['patient'],
				"imagetype" => $type,
				"imagecat" => $cat,
				"imagedesc" => $_REQUEST['note']
			)
		));
		$new_id = $GLOBALS['sql']->last_record($query, 'images');

		$new_filename = freemed::image_filename(
			freemed::secure_filename($_REQUEST['patient']),
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
		syslog(LOG_INFO, "UnfiledFax| query = $query, result = $result");

		// Move actual file to new location
		//echo "mv data/fax/unfiled/$filename $new_filename -f<br/>\n";
		$dirname = dirname($new_filename);
		`mkdir -p $dirname`;
		//echo "mkdir -p $dirname";
		`mv data/fax/unfiled/$filename $new_filename -f`;

		$GLOBALS['display_buffer'] .= __("Moved fax to scanned documents.");

		$GLOBALS['sql']->query("DELETE FROM ".$this->table_name." ".
			"WHERE id='".addslashes($id)."'");

		global $refresh;
		//$refresh = $page_name."?module=".get_class($this);

		$GLOBALS['display_buffer'] = '<br/>'.
			template::link_bar(array(
				__("View Patient Record") =>
				'manage.php?id='.urlencode($_REQUEST['patient']),
				__("Return to Unfiled Fax Menu") =>
				$this->page_name.'?module='.get_class($this)
			));
	} // end method mod_direct

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
				"<a href=\"module_loader.php?module=".urlencode(get_class($this))."&action=display\" class=\"reverse\">".
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

	function menu_notify ( ) {
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
				sprintf(__("You have %d unfiled faxes"), $unfiled),
				"module_loader.php?module=".urlencode(get_class($this))."&action=display"
			);
		} else {
			// For now, we're just going to return nothing so that
			// the box doesn't show up
			return false;
		}
	} // end method menu_notify

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
