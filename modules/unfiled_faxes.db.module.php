<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.MaintenanceModule');

class UnfiledFaxes extends MaintenanceModule {

	var $MODULE_NAME = "Unfiled Faxes";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_FILE = __FILE__;
	var $PACKAGE_MINIMUM_VERSION = "0.7.0";

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
			__("Recipient(s)") =>
			'freemed::multiple_choice ( '.
				'"SELECT CONCAT(username, \' (\', userdescrip, \')\') '.
				'AS descrip, id FROM user ORDER BY descrip", "descrip", '.
				'"uffax_user", fm_join_from_array($uffax_user))'
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
			case __("Split Batch"):
			$this->batch_split_screen();
			return false;
			break;

			case __("Split"):
			$this->batch_split();
			return false;
			break;
			
			case __("Send to Provider"):
			case __("Send to Provider without First Page"):
			$this->mod();
			return false;
			break;

			case __("File Directly"):
			$new_id = $this->mod_direct();
			return false;
			break;

			case __("File Directly without First Page"):
			$new_id = $this->mod_direct(-1, true);
			return false;
			break;

			case __("Delete"):
			$this->del();
			return false;
			break;

			case "pageview":
			$this->get_pageview();
			return false;
			break;
		}

		$result = $GLOBALS['sql']->query("SELECT * FROM ".
			$this->table_name." WHERE id='".addslashes($_REQUEST['id'])."'");
		$r = $GLOBALS['sql']->fetch_array($result);
		if (!$_REQUEST['been_here']) {
			global $date;
			$date = $r['uffdate'];
		}
		$display_buffer .= "
		<!-- Javascript for form validation -->
		<script language=\"javascript\">
		function validate ( form ) {
			if (!validateField(form.type, \"".__("Type of Document")."\")) return false;
			if (!validateField(form.physician, \"".__("Provider")."\")) return false;
			if (!validateField(form.patient, \"".__("Patient")."\")) return false;
		}
		
		function validateField (field, label) {
			var result = true;
			if ((field.value == \"\") || (field.value == \"0\")) {
				alert(\"".__("You must enter a value for:")." '\" + label + \"'\");
				field.focus();
				result = false;
			}
			return result;
		}
		</script>
		<form action=\"".$this->page_name."\" method=\"post\" name=\"myform\" id=\"myform\">
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"view\"/>
		<input type=\"hidden\" name=\"been_here\" value=\"1\"/>
		<div align=\"center\">
		".html_form::form_table(array(
			__("Date") => fm_date_entry('date'),
			__("Patient") => freemed::patient_widget("patient"),
			__("Physician") => freemed_display_selectbox ($GLOBALS['sql']->query("SELECT * FROM physician WHERE phyref='no' ORDER BY phylname,phyfname"), "#phylname#, #phyfname#", "physician"),
			__("Type") => module_function(
				'ScannedDocuments',
				'tc_widget',
				array('type')
			),
			__("Note") => html_form::text_widget("note", array('length'=>150)),
			__("Notify") => freemed_display_selectbox(
				$GLOBALS['sql']->query(
					"SELECT * FROM user ".
					"WHERE username != 'admin' ".
					"ORDER BY userdescrip"
					),
				"#username# (#userdescrip#)",
				"notify"
			),
			__("Fax Confirmation #") => html_form::text_widget("faxback")
		))."
		</div>
		<div align=\"center\">
		<input type=\"submit\" name=\"submit_action\" ".
		"onClick=\"return validate(this.form);\" ".
		"class=\"button\" value=\"".__("Send to Provider")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"onClick=\"if (confirm('".addslashes(__("Are you sure that this fax contains a cover sheet?"))."')) { return validate(this.form); } else { return false; }\" ".
		"class=\"button\" value=\"".__("Send to Provider without First Page")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"onClick=\"return validate(this.form);\" ".
		"class=\"button\" value=\"".__("File Directly")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"onClick=\"if (confirm('".addslashes(__("Are you sure that this fax contains a cover sheet?"))."')) { return validate(this.form); } else { return false; }\" ".
		"class=\"button\" value=\"".__("File Directly without First Page")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Split Batch")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Cancel")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"onClick=\"if (confirm('".addslashes(__("Are you sure that you want to permanently delete this fax?"))."')) { this.form.submit(); } else { return false; }\" ".
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

		global $refresh;
		$refresh = $this->page_name."?module=".get_class($this);
	} // end method del

	// Modify method
	function mod () {
		$id = $_REQUEST['id'];
		$rec = freemed::get_link_rec($id, $this->table_name);
		$filename = freemed::secure_filename($rec['ufffilename']);

		// Catch multiple people using the same fax
		if (!file_exists('data/fax/unfiled/'.$filename)) {
			trigger_error(__("Fax file does not exist!"));
		}

		if (!empty($_REQUEST['faxback'])) { $this->faxback(); }

		if ($_REQUEST['notify']+0 > 0) {
			$msg = CreateObject('_FreeMED.Messages');
			$msg->send(array(
				'patient' => $_REQUEST['patient'],
				'user' => $_REQUEST['notify'],
				'urgency' => 4,
				'text' => __("Fax received for patient").
					" (".$_REQUEST['note'].")"
			));
		}

		// If we're removing the first page, do that now
		if ($_REQUEST['submit_action'] == __("Send to Provider without First Page")) {
			$command = "/usr/bin/djvm -d \"data/fax/unfiled/".
				$filename."\" 1";
			`$command`;
			$GLOBALS['display_buffer'] .= __("Removed first page.")."<br/>\n";
		}

		// Move actual file to new location
		//echo "mv data/fax/unfiled/$filename data/fax/unread/$filename -f";
		if ($filename) { `mv "data/fax/unfiled/$filename" "data/fax/unread/$filename" -f`; }

		// Insert new table query in unread
		$result = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
			'unreadfax',
			array (
				"urfdate" => fm_date_assemble('date'),
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

	function mod_direct ($_id = -1, $remove_first = false) {
		$id = $_REQUEST['id'];
		$rec = freemed::get_link_rec($id, $this->table_name);
		$filename = freemed::secure_filename($rec['ufffilename']);

		// Catch multiple people using the same fax
		if (!file_exists('data/fax/unfiled/'.$filename)) {
			trigger_error(__("Fax file does not exist!"));
		}

		if (!empty($_REQUEST['faxback'])) { $this->faxback(); }

		if ($_REQUEST['notify']+0 > 0) {
			$msg = CreateObject('_FreeMED.Messages');
			$msg->send(array(
				'patient' => $_REQUEST['patient'],
				'user' => $_REQUEST['notify'],
				'urgency' => 4,
				'text' => __("Fax received for patient").
					" (".$_REQUEST['note'].")"
			));
		}

		if ($remove_first) {
			$command = "/usr/bin/djvm -d ".
				"\"".dirname(dirname(__FILE__))."/".
				"data/fax/unfiled/".
				$filename."\" 1";
			`$command`;
			$GLOBALS['display_buffer'] .= __("Removed first page.")."<br/>\n";
		}

		// Extract type and category
		list ($type, $cat) = explode('/', $_REQUEST['type']);
		
		// Insert new table query in unread
		$query = $GLOBALS['sql']->query($GLOBALS['sql']->insert_query(
			'images',
			array (
				"imagedt" => fm_date_assemble('date'),
				"imagepat" => $_REQUEST['patient'],
				"imagetype" => $type,
				"imagecat" => $cat,
				"imagedesc" => $_REQUEST['note'],
				"imagephy" => $_REQUEST['physician']
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
		`mkdir -p "$dirname"`;
		//echo "mkdir -p $dirname";
		if ($filename) { `mv "data/fax/unfiled/$filename" "$new_filename" -f`; syslog(LOG_INFO, "UnfiledFax| mv data/fax/unfiled/$filename $new_filename -f"); }

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

	function batch_split_screen ( ) {
		global $display_buffer;

		$r = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		$display_buffer .= "
		<form action=\"".$this->page_name."\" method=\"post\" name=\"myform\">
		<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
		<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
		<input type=\"hidden\" name=\"action\" value=\"view\"/>
		<input type=\"hidden\" name=\"been_here\" value=\"1\"/>
		";

		// Use Djvu object to get thumbnails, etc
		$djvu = CreateObject('_FreeMED.Djvu', 
			dirname(dirname(__FILE__)).'/data/fax/unfiled/'.
			$r['ufffilename']);
		$pages = $djvu->NumberOfPages();
		if ($pages < 2) {
			trigger_error("You can't split a single page document.");
		}

		for ( $i = 1; $i <= $pages; $i++ ) {
			// Display icon/thumb
			$display_buffer .= "
			<div align=\"center\">
			<img src=\"".$this->page_name."?module=".
			get_class($this)."&".
			"action=".$_REQUEST['action']."&".
			"submit_action=pageview&".
			"id=".$_REQUEST['id']."&".
			"page=".$i."\" alt=\"page $i\" border=\"1\" />
			</div>
			";
			if ($i != $pages) {
			$display_buffer .= "
			<div align=\"center\">
			<input type=\"checkbox\" name=\"splitafter[".$i."]\" ".
			"value=\"1\" /> ----------------------
			</div>
			";
			}
		}

		$display_buffer .= "
		<div align=\"center\">
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Split")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Cancel")."\"/>
		<input type=\"submit\" name=\"submit_action\" ".
		"class=\"button\" value=\"".__("Delete")."\"/>
		</div>

		</form>
		";
	} // end method batch_split_screen

	function batch_split ( ) {
		// Get the "splits"
		$s = $_REQUEST['splitafter'];

		// Get page information
		$r = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		$djvu = CreateObject('_FreeMED.Djvu', 
			dirname(dirname(__FILE__)).'/data/fax/unfiled/'.
			$r['ufffilename']);
		$pages = $djvu->NumberOfPages();
		$chunks = $djvu->StoredChunks();

		// Create temporary extraction location
		$dir_prefix = tempnam('/tmp', 'fmdir');
		$dir = $dir_prefix.'.d';

		// Extract
		$filename = $djvu->filename;
		`mkdir "$dir"`;
		//print "dir = $dir<br/>\n";
		`djvmcvt -i "$filename" "$dir" "$dir/index.djvu"`;

		// Figure out where the splits are ...
		$cur = 1;
		for ($i = 1; $i <= $pages; $i++) {
			$d[$cur][] = $i;
			if ($s[$i] == 1) {
				$cur++;
			}
		}

		// Reassemble
		foreach ($d AS $k => $v) {
			$hash = "";

			// Put together lists of files
			foreach ($v AS $this_file) {
				$hash .= "\"".$dir."/".$chunks[$this_file-1]."\" ";
			}

			// New Filename
			$new_filename = $filename.'.'.$k.'.djvu';

			// Create new file
			$output = `djvm -c "$new_filename" $hash`;

			// Erase temporary files
			unlink($dir."/index.djvu");
			foreach ($pages AS $_page) {
				unlink($dir."/".$_page);
			}
			unlink($dir);

			// Add new entry for fax file
			$result = $GLOBALS['sql']->query(
				$GLOBALS['sql']->insert_query(
					$this->table_name,
					array (
						'uffdate' => $r['uffdate'],
						'ufffilename' => basename(trim($new_filename))
					)
				)
			);

			// TODO TODO: Make sure to erase old fax
		}
		
		// Show some output
		$display_buffer .= __("Split fax successfully.").
			"<br/>";
		global $refresh;
		$refresh = $this->page_name."?module=".get_class($this).
			"&action=display";

		// Cleanup
		unlink($dir);
		unlink($dir_prefix);
	} // end method batch_split

	function get_pageview() {
		// Return image ...
		$r = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		$djvu = CreateObject('_FreeMED.Djvu', 
			dirname(dirname(__FILE__)).'/data/fax/unfiled/'.
			$r['ufffilename']);
		Header('Content-type: image/jpeg');
		print $djvu->GetPageThumbnail($_REQUEST['page']);
		die();
	} // end method get_pageview

	function faxback ( ) {
		global $display_buffer;
	
		$id = $_REQUEST['id'];
		$rec = freemed::get_link_rec($id, $this->table_name);
		$filename = freemed::secure_filename($rec['ufffilename']);

		// Analyze File
		$djvu = CreateObject('_FreeMED.Djvu', 
			dirname(dirname(__FILE__)).'/data/fax/unfiled/'.
			$filename);
		$pages = $djvu->NumberOfPages();

		// Fax the first page back
		$tempfile = $djvu->GetPage(1, false, true);
		$fax = CreateObject('_FreeMED.Fax',
			$tempfile,
			array (
				'sender' => INSTALLATION." (".PACKAGENAME." v".DISPLAY_VERSION.")",
				'subject' => '['.$pages.' '.__("page(s) received").']',
				'comments' => __("All pages received.").' '.
					__("Thank you.")
			)
		);
		$output = $fax->Send($_REQUEST['faxback']);
		unlink($tempfile);

		$display_buffer .= __("Confirmation fax sent.");
	} // end method faxback

	function notify ( ) {
		// Check to see if we're the person who is supposed to be
		// notified. If not, die out right now.
		$supposed = freemed::config_value('uffax_user');
		if (!(strpos($supposed, ',') === false)) {
			// Handle array
			$found = false;
			foreach (explode(',', $supposed) AS $s) {
				if ($s == $_SESSION['authdata']['user']) { $found = true; }
			}
			if (!$found) { return false; }
		} else {
			if (($supposed > 0) and ($supposed != $_SESSION['authdata']['user'])) {
				return false;
			}
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
		if (!(strpos($supposed, ',') === false)) {
			// Handle array
			$found = false;
			foreach (explode(',', $supposed) AS $s) {
				if ($s == $_SESSION['authdata']['user']) { $found = true; }
			}
			if (!$found) { return false; }
		} else {
			if (($supposed > 0) and ($supposed != $_SESSION['authdata']['user'])) {
				return false;
			}
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
