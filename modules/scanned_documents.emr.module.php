<?php
 // $Id$
 // $Author$
 // note: images module for patient management
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class ScannedDocuments extends EMRModule {

	var $MODULE_NAME = "Scanned Documents";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_DESCRIPTION = "
		FreeMED Patient Images allows images to be
		stored, as if they were in a paper chart.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name   = "Scanned Documents";
	var $table_name    = "images";
	var $patient_field = "imagepat";
	var $order_by      = "imagedt";

	function ScannedDocuments () {
		// Get browser information
		$browser = CreateObject('PHP.browser_detect');
		if ($browser->BROWSER=="IE") $this->IEupload = true;

		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date")        =>	"imagedt",
			__("Type")        =>	"imagetype",
			__("Description") =>	"imagedesc"
		);
		$this->summary_options |= SUMMARY_VIEW;

		// Define table
		$this->table_definition = array (
			"imagedt"	=>	SQL__DATE,
			"imagepat"	=>	SQL__INT_UNSIGNED(0),
			"imagetype"	=>	SQL__VARCHAR(50),
			"imagedesc"	=>	SQL__VARCHAR(150),
			"imageeoc"	=>	SQL__TEXT,
			"imagefile"	=>	SQL__VARCHAR(100),
			"id"		=>	SQL__SERIAL
		);

		// Set associations
		$this->_SetAssociation('EpisodeOfCare');
		$this->_SetMetaInformation('EpisodeOfCareVar', 'imageeoc');

		// Call parent constructor
		$this->EMRModule();
	} // end constructor ScannedDocuments

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

	function add () {
		global $display_buffer, $sql, $imageeoc, $patient, $module;
		$display_buffer .= "
			<div ALIGN=\"CENTER\"><b>".__("Adding")." ... </b>
		";

		// Have to add then update to get file name
		$query = $sql->insert_query (
			$this->table_name,
			array (
				"imagedt" => fm_date_assemble("imagedt"),
				"imagepat" => $patient,
				"imagetype",
				"imagedesc",
				"imageeoc"
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
		global $id, $patient;
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
		$display_buffer .= "
		<div ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<embed SRC=\"patient_image_handler.php?".
		"patient=".urlencode($patient)."&".
		"id=".urlencode($id)."\" BORDER=\"0\"
		PLUGINSPAGE=\"".COMPLETE_URL."support/\"
		FLAGS=\"width=100% height=100% passive=yes zoom=stretch\"
		TYPE=\"image/x.djvu\" WIDTH=\"".
		( $GLOBALS['__freemed']['Mozilla'] ? '600' : '100%' ).
		"\" HEIGHT=\"800\"></embed>
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
			foreach ($r AS $k => $v) {
				global ${$k};
				${$k} = sql_expand($v);
			}
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
		"VALUE=\"".prepare($id)."\"/>
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
			html_form::select_widget(
				"imagetype",
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
			)

		), $related_episode_array,
		array (

			__("Description") =>
			html_form::text_widget("imagedesc", 30, 150),

			__("Attach Image") =>
			( (($action=="add") || ($action=="addform")) ?
			( ($this->IEupload) ? $this->activeXupload() :
			"<input TYPE=\"FILE\" NAME=\"imageupload\"/>" ) :
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
		$display_buffer .= "
			<div ALIGN=\"CENTER\"><b>".__("Modifying")." ... </b>
		";

		// Have to add then update to get file name
		$query = $sql->update_query (
			$this->table_name,
			array (
				"imagedt" => fm_date_assemble("imagedt"),
				"imagepat" => $patient,
				"imagetype",
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
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL
		);
		$display_buffer .= "\n<p/>\n";
	} // end function ScannedDocuments->view()

} // end of class ScannedDocuments

register_module ("ScannedDocuments");

?>
