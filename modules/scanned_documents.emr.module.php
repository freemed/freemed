<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class ScannedDocuments extends EMRModule {

	var $MODULE_NAME = "Scanned Documents";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.4.2";
	var $MODULE_DESCRIPTION = "
		FreeMED Patient Images allows images to be
		stored, as if they were in a paper chart.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.7.0';

	var $record_name   = "Scanned Documents";
	var $table_name    = "images";
	var $patient_field = "imagepat";
	var $order_by      = "imagedt";
	var $widget_hash   = "##imagecat## [##imagedt##] ##imagedesc## (##imagetype##)";

	function ScannedDocuments () {
		// Get browser information
		$browser = CreateObject('PHP.browser_detect');
		if ($browser->BROWSER=="IE") $this->IEupload = true;

		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date")        =>	"my_date",
			__("Type")        =>	"imagetype",
			__("Category")	  =>	"imagecat",
			__("Description") =>	"imagedesc",
			__("Reviewed")    =>	"reviewed"
		);
		$this->summary_options |= SUMMARY_VIEW | SUMMARY_LOCK | SUMMARY_DELETE | SUMMARY_PRINT;
		$this->summary_query = array (
			"DATE_FORMAT(imagedt, '%m/%d/%Y') AS my_date",
			"CASE imagereviewed WHEN 0 THEN 'no' ELSE 'yes' END AS reviewed"
		);
		$this->summary_order_by = "imagedt";

		// Define table
		$this->table_definition = array (
			"imagedt"	=>	SQL__DATE,
			"imagepat"	=>	SQL__INT_UNSIGNED(0),
			"imagetype"	=>	SQL__VARCHAR(50),
			"imagecat"	=>	SQL__VARCHAR(50),
			"imagedesc"	=>	SQL__VARCHAR(150),
			"imageeoc"	=>	SQL__TEXT,
			"imagefile"	=>	SQL__VARCHAR(100),
			"imagephy"	=>	SQL__INT_UNSIGNED(0),
			"imagereviewed"	=>	SQL__INT_UNSIGNED(0),
			"locked"	=>	SQL__INT_UNSIGNED(0),
			"id"		=>	SQL__SERIAL
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'imageeoc');

		$this->acl = array ( 'bill', 'emr' );

		// Call parent constructor
		$this->EMRModule();
	} // end constructor ScannedDocuments

/*
	function activeXupload ($name) {
		global $display_buffer;
		$buffer .= "
		<script LANGUAGE=\"VBScript\">
		<!--
		Sub ScanControl_ScanComplete(FileName)
			document.myform.imageupload.focus()
			document.myform.ScanControl.PasteName()
		End Sub
		-->
		</script>
		<object ID=\"ScanControl\"
			CLASSID=\"CLSID:4A72D130-BBAD-45BD-AB11-E506466200EA\"
			CODEBASE=\"./support/webscanner.cab#version=1,0,0,20\">
			<!-- CODEBASE=\"http://www.internext.co.za/stefan/webtwain/WebScanner.CAB#version=1,0,0,20\"> -->
		</object>
		<br/>
		<input TYPE=\"FILE\" NAME=\"imageupload\"/>
		";
		return $buffer;
	} // end function ScannedDocuments->activeXupload
*/

	function add () {
		global $display_buffer, $sql, $imageeoc, $patient, $module;
		$display_buffer .= "
			<div ALIGN=\"CENTER\"><b>".__("Adding")." ... </b>
		";

		// Get type and category from composite widget
		list ( $imagetype, $imagecat ) = explode('/', $_REQUEST['imagetypecat']);

		// Have to add then update to get file name
		$query = $sql->insert_query (
			$this->table_name,
			array (
				"imagedt" => fm_date_assemble("imagedt"),
				"imagepat" => $patient,
				"imagetype" => $imagetype,
				"imagecat" => $imagecat,
				"imagephy",
				"imagedesc",
				"imageeoc",
				"imagereviewed" => 0
			)
		);
		$result = $sql->query ($query);
		if ($debug) $display_buffer .= "(query = '$query') ";

		// Store last record number
		$last_record = $sql->last_record($result, $this->table_name);

		// Handle upload
		if (!($imagefilename = freemed::store_image($patient,
				"imageupload", $last_record))) {
			print "FAILED TO UPLOAD!\n";
		}

		if ($result) {
			$display_buffer .= " <b> ".__("done").". </b>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <b> <font COLOR=\"#ff0000\">".__("ERROR")."</font> </b>\n";
		}
		$display_buffer .= "<br/>\n";

		// Update database with proper file name
		$display_buffer .= "
			<div ALIGN=\"CENTER\"><b>".__("Updating database")." ... </b>
		";

		$query = $sql->update_query (
			$this->table_name,
			array ( "imagefile" => $imagefilename ),
			array ( "id" => $last_record )
		);
		$result = $sql->query ($query);

		if ($result) {
			$display_buffer .= " <b> ".__("done").". </b>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <b> <FONT COLOR=#ff0000>".__("ERROR")."</FONT> </b>\n";
		}

		$display_buffer .= "
		</div>
		<p/>
		<div ALIGN=\"CENTER\"><a HREF=\"manage.php?id=$patient\"
		class=\"button\">".__("Manage Patient")."</a>
		<a HREF=\"$this->page_name?module=$module&patient=$patient\"
		class=\"button\">".__($this->record_name)."</a>
		<a HREF=\"$this->page_name?module=$module&patient=$patient&".
		"action=addform\" class=\"button\"
		>".__("Add Another")."</a>
		</div>
		";
		global $refresh, $manage;
		if ($return=="manage") {
			$refresh = "manage.php?id=".$patient."&ts=".urlencode(mktime());
		}
 	} // end method ScannedDocuments->add

	function del () {
		// Delete actual image
		global $id, $patient, $display_buffer;

		if (!freemed::lock_override()) {
			if ($this->locked($id)) {
				$display_buffer .= __("Record is locked.");
				return false;
			}
		}
		
		unlink(freemed::image_filename(
			freemed::secure_filename($patient),
			freemed::secure_filename($id),
			'djvu'
		));

		// Run stock deletion routine
		$this->_del();
	} // end function ScannedDocuments->del

	function display () {
		global $sql, $id, $patient, $display_buffer, $return;
		if (!$patient or !$id) return false;
		include_once(freemed::template_file('djvuviewer.php'));
		$display_buffer .= "
		<div ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<!--
		<embed SRC=\"patient_image_handler.php?".
		"patient=".urlencode($patient)."&".
		"id=".urlencode($id)."\" BORDER=\"0\"
		PLUGINSPAGE=\"".COMPLETE_URL."support/\"
		FLAGS=\"width=100% height=100% passive=yes toolbar=yes keyboard=yes zoom=stretch\"
		TYPE=\"image/x.djvu\" WIDTH=\"".
		( $GLOBALS['__freemed']['Mozilla'] ? '600' : '100%' ).
		"\" HEIGHT=\"800\"></embed>
		-->
		".djvu_widget( $id, $patient )."
		</div>
		<div ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<a class=\"button\" HREF=\"";
		if ($return=="manage") {
			$display_buffer .= "manage.php?id=$patient\">".
			__("Manage Patient");
		} else {
			$display_buffer .= $this->page_name."?".
			"module=".urlencode($module)."&".
			"action=view\">". __($this->record_name);
		}
		$display_buffer .= "</a>
		</div>
		";
	} // end function ScannedDocuments->display

	function form () {
		global $display_buffer, $sql, $module, $patient, $imageeoc,
			$action, $id;

		// Globalize all variables
		foreach ($GLOBALS AS $k => $V) global ${$k};

		// If this is modification, we pull it apart
		switch ($action) {
			case "mod": case "modform":
			global $id;
			$r = freemed::get_link_rec($id, $this->table_name);
			if ($r['locked']) {
				$display_buffer .= __("Record is locked.");
				return false;
			}
			foreach ($r AS $k => $v) {
				global ${$k};
				if ($k == 'imageeoc') {
					${$k} = sql_expand($v); 
				} else {
					${$k} = $v;
				}
			}
			$this->prepare_tc_widget('imagetypecat', $_REQUEST['id']);
			break; // end mod/form

			default: break;
		}

		// If there's an episode of care module installed...
		if (check_module("EpisodeOfCare")) {
			// Actual piece
			$imageeoc = sql_squash($imageeoc); // for multiple choice (HACK)
			$related_episode_array = array (
			__("Related Episode(s)") =>
			module_function('EpisodeOfCare', 'widget',
				array('imageeoc', $patient))
			);
		} else {
			// Put in blank array instead
			$related_episode_array = array ("" => "");
		}

		$display_buffer .= "
		<div ALIGN=\"CENTER\">
		<form METHOD=\"POST\" ACTION=\"module_loader.php\" ".
		"ENCTYPE=\"multipart/form-data\" NAME=\"myform\">
		<input TYPE=\"HIDDEN\" NAME=\"module\" ".
		"VALUE=\"".prepare($module)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"id\" ".
		"VALUE=\"".prepare($_REQUEST['id'])."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"action\" ".
		"VALUE=\"".($action=="addform" ? "add" : "mod" )."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"patient\" ".
		"VALUE=\"".prepare($patient)."\"/>
		<input TYPE=\"HIDDEN\" NAME=\"MAX_FILE_SIZE\" VALUE=\"10000000\"/>
		";

		$display_buffer .= html_form::form_table(array_merge(
		array(

			__("Date") =>
			fm_date_entry ("imagedt"),

			__("Type of Image") =>
			$this->tc_widget('imagetypecat'),

			__("Physician") => freemed_display_selectbox ($GLOBALS['sql']->query("SELECT * FROM physician WHERE phyref='no' ORDER BY phylname,phyfname"), "#phylname#, #phyfname#", "imagephy")

		), $related_episode_array,
		array (

			__("Description") =>
			html_form::text_widget("imagedesc", 30, 150),

			__("Attach Image") =>
			( (($action=="add") || ($action=="addform")) ?
			// Remove webscanner ActiveX thing from here
			//( ($this->IEupload) ? $this->activeXupload() :
			//"<input TYPE=\"FILE\" NAME=\"imageupload\"/>" ) :
			"<input TYPE=\"FILE\" NAME=\"imageupload\"/>" :
			__("ATTACHED") )

		)));

		$display_buffer .= "
			<div ALIGN=\"CENTER\">
			<input TYPE=\"SUBMIT\" VALUE=\"".
			( ($action=="add" || $action=="addform") ?
			__("Attach Image") : __("Modify") )."\" class=\"button\"/>
			<input TYPE=\"SUBMIT\" NAME=\"submit\" ".
			 "VALUE=\"".__("Cancel")."\" class=\"button\"/>
			</div>

		</form></div>
		";
	} // end of function ScannedDocuments->form()

	function mod () {
		global $display_buffer, $sql, $imageeoc, $patient, $module, $id;
		if ($this->locked($id)) {
			$display_buffer .= __("Record is locked.");
			return false;
		}
		
		$display_buffer .= "
			<div ALIGN=\"CENTER\"><b>".__("Modifying")." ... </b>
		";

		// Get type and category from composite widget
		list ( $imagetype, $imagecat ) = explode('/', $_REQUEST['imagetypecat']);

		// Have to add then update to get file name
		$query = $sql->update_query (
			$this->table_name,
			array (
				"imagedt" => fm_date_assemble("imagedt"),
				"imagepat" => $patient,
				"imagetype" => $imagetype,
				"imagecat" => $imagecat,
				"imagephy",
				"imagedesc",
				"imageeoc"
			), array ( "id" => $id )
		);
		$result = $sql->query ($query);
		if ($debug) $display_buffer .= "(query = '$query') ";

		if ($result) {
			$display_buffer .= " <b> ".__("done").". </b>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <b> <FONT COLOR=#ff0000>".__("ERROR")."</FONT> </b>\n";
		}

		$display_buffer .= "
		</div>
		<p/>
		<div ALIGN=\"CENTER\"><a HREF=\"manage.php?id=$patient\"
		class=\"button\">".__("Manage Patient")."</a>
		<a HREF=\"$this->page_name?module=$module&patient=$patient\"
		class=\"button\">".__($this->record_name)."</a>
		</div>
		";
 	} // end method ScannedDocuments->mod

	function view ($condition = false) {
		global $display_buffer;
		global $patient, $action;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// Check for "view" action (actually display)
		if ($action=="view") {
			$this->display();
			return NULL;
		}

		$display_buffer .= freemed_display_itemlist(
			$sql->query(
				"SELECT * FROM ".$this->table_name." ".
				"WHERE (imagepat='".addslashes($patient)."') ".
				freemed::itemlist_conditions(false)." ".
				( $condition ? 'AND '.$condition : '' )." ".
				"ORDER BY imagedt"
			),
			$this->page_name,
			array (
				"Date"        => "imagedt",
				"Description" => "imagedesc"
			), // array
			array (
				"",
				__("NO DESCRIPTION")
			),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL | ITEMLIST_LOCK
		);
		$display_buffer .= "\n<p/>\n";
	} // end function ScannedDocuments->view()

	function additional_move ( $id, $from, $to ) {
		$orig = freemed::image_filename($from, $id, 'djvu');
		$new = freemed::image_filename($to, $id, 'djvu');
		$q = $GLOBALS['sql']->update_query(
			$this->table_name,
			array ( 'imagefilename' => $new ),
			array ( 'id' => $id )
		);

		syslog(LOG_INFO, "Scanned Documents| moved $orig to $new");

		$result = $GLOBALS['sql']->query($q);
		//if (!$result) { return false; }

		$result = rename ( $orig, $new );
		$dir = dirname($new);
		`mkdir -p "$dir"`;
		`mv "$orig" "$new"`;
		//print "mv \"$orig\" \"$new\"<br/>\n";
		//print "orig = $orig, new = $new<br/>\n";
		//if (!$result) { return false; }

		return true;
	} // end method additional_move

	function prepare_tc_widget ( $varname, $id ) {
		global ${$varname};
		$r = freemed::get_Link_rec($id, $this->table_name);
		${$varname} = $r['imagetype'] . "/" . $r['imagecat'];
	} // end method prepare_tc_widget

	function tc_widget ( $varname ) {
		return html_form::select_widget(
			$varname,
			array (
				__("Operative Report") => "op_report/misc",
					"- ".__("Colonoscopy") => "op_report/colonoscopy",
					"- ".__("Endoscopy") => "op_report/endoscopy",
				__("Miscellaneous") => "misc/misc",
					"- ".__("Consult") => "misc/consult",
					"- ".__("Discharge Summary") => "misc/discharge_summary",
					"- ".__("History and Physical") => "misc/history_and_physical",
				__("Lab Report") => "lab_report/misc",
					"- ".__("CBC") => "lab_report/cbc",
					"- ".__("C8") => "lab_report/c8",
					"- ".__("LFT") => "lab_report/lft",
					"- ".__("Lipid Profile") => "lab_report/lipid_profile",
					"- ".__("UA") => "lab_report/ua",
					"- ".__("Thyroid Profile") => "lab_report/thyroid_profile",
				__("Letters") => "letters/misc",
				__("Oncology") => "oncology/misc",
				__("Hospital Records") => "hospital/misc",
					"- ".__("Discharge Summary") => "hospital/discharge",
				__("Pathology") => "pathology/misc",
				__("Patient History") => "patient_history/misc",
				__("Questionnaire") => "questionnaire/misc",
				__("Radiology") => "radiology/misc",
					"- ".__("Abdominal Radiograph") => "radiology/abdominal_radiograph",
					"- ".__("Chest Radiograph") => "radiology/chest_radiograph",
					"- ".__("Abdominal CT Reports") => "radiology/abdominal_ct_reports",
					"- ".__("Chest CT Reports") => "radiology/chest_ct_reports",
					"- ".__("Mammogram Reports") => "radiology/mammogram_reports",
				__("Insurance Card") => "insurance_card",
				__("Referral") => "referral/misc",
					"- ".__("Notes") => "referral/notes",
					"- ".__("Radiographs") => "referral/radiographs",
					"- ".__("Lab Reports") => "referral/lab_reports",
					"- ".__("Consult") => "referral/consult",
				__("Financial Information") => "financial/misc"
			)
		);
	} // end method tc_widget

	function print_override ( $id ) {
		// Create djvu object
		$rec = freemed::get_link_rec($id, $this->table_name);
		$filename = freemed::image_filename($rec[$this->patient_field], $id, 'djvu');
		$d = CreateObject('FreeMED.Djvu', $filename);
		return $d->ToPDF(true);
	} // end method print_override

	function fax_widget ( $varname, $id ) {
		global $sql, ${$varname};
		$r = freemed::get_link_rec($id, $this->table_name);
		$phy = freemed::get_link_rec($r['imagephy'], 'physician');
		${$varname} = $phy['phyfaxa'];
		return module_function('faxcontacts',
			'widget',
			array ( $varname, false, 'phyfaxa' )
		);
	} // end method fax_widget

	function _update () {
		$version = freemed::module_version($this->MODULE_NAME);
		// Version 0.3
		//
		//	Add "category" sub-column
		//
		if (!version_check($version, '0.3')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN imagecat VARCHAR(50) AFTER imagetype');
		}

		// Version 0.4
		//
		//	Add physician field
		//
		if (!version_check($version, '0.4')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN imagephy INT UNSIGNED AFTER imagefile');
		}

		// Version 0.4.1
		//
		//	Add locking
		//
		if (!version_check($version, '0.4.1')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN locked INT UNSIGNED AFTER imagephy');
		}

		// Version 0.4.2
		//
		//	Add reviewed flag
		//
		if (!version_check($version, '0.4.2')) {
			$GLOBALS['sql']->query('ALTER TABLE '.$this->table_name.
				' ADD COLUMN imagereviewed INT UNSIGNED AFTER imagephy');
			$GLOBALS['sql']->query('UPDATE '.$this->table_name.' '.
				'SET imagereviewed=0');
		}
	} // end method _update

} // end of class ScannedDocuments

register_module ("ScannedDocuments");

?>
