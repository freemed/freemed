<?php
 // $Id$
 // $Author$
 // note: images module for patient management
 // lic : GPL, v2

LoadObjectDependency('FreeMED.EMRModule');

class PatientImages extends EMRModule {

	var $MODULE_NAME = "Patient Images";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1.1";
	var $MODULE_DESCRIPTION = "
		FreeMED Patient Images allows images to be
		stored, as if they were in a paper chart.
	";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	var $record_name   = "Patient Images";
	var $table_name    = "images";
	var $patient_field = "imagepat";
	var $order_by      = "imagedt";

	function PatientImages () {
		// Get browser information
		$browser = CreateObject('PHP.browser_detect');
		if ($browser->BROWSER=="IE") $this->IEupload = true;

		// Define variables for EMR summary
		$this->summary_vars = array (
			_("Date")        =>	"imagedt",
			_("Type")        =>	"imagetype",
			_("Description") =>	"imagedesc"
		);
		$this->summary_options |= SUMMARY_VIEW;

		// Define table
		$this->table_definition = array (
			"imagedt"	=>	SQL_DATE,
			"imagepat"	=>	SQL_INT_UNSIGNED(0),
			"imagetype"	=>	SQL_VARCHAR(50),
			"imagedesc"	=>	SQL_VARCHAR(150),
			"imageeoc"	=>	SQL_TEXT,
			"imagefile"	=>	SQL_VARCHAR(100),
			"id"		=>	SQL_SERIAL
		);

		// call parent constructor
		$this->EMRModule();
	} // end constructor PatientImages

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
	} // end function PatientImages->activeXupload

	function add () {
		global $display_buffer, $sql, $imageeoc, $patient, $module;
		$display_buffer .= "
			<div ALIGN=\"CENTER\"><b>"._("Adding")." ... </b>
		";

		// Have to add then update to get file name
		$query = $sql->insert_query (
			$this->table_name,
			array (
				"imagedt" => date_assemble("imagedt"),
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
			$display_buffer .= " <b> "._("done").". </b>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <b> <FONT COLOR=#ff0000>"._("ERROR")."</FONT> </b>\n";
		}
		$display_buffer .= "<br/>\n";

		// Update database with proper file name
		$display_buffer .= "
			<div ALIGN=\"CENTER\"><b>"._("Updating database")." ... </b>
		";

		$query = $sql->update_query (
			$this->table_name,
			array ( "imagefile" => $imagefilename ),
			array ( "id" => $last_record )
		);
		$result = $sql->query ($query);

		if ($result) {
			$display_buffer .= " <b> "._("done").". </b>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <b> <FONT COLOR=#ff0000>"._("ERROR")."</FONT> </b>\n";
		}

		$display_buffer .= "
		</div>
		<p/>
		<div ALIGN=\"CENTER\"><a HREF=\"manage.php?id=$patient\"
		>"._("Manage Patient")."</a>
		<b>|</b>
		<a HREF=\"$this->page_name?module=$module&patient=$patient\"
		>"._($this->record_name)."</a>
		<b>|</b>
		<a HREF=\"$this->page_name?module=$module&patient=$patient&".
		"action=addform\"
		>"._("Add Another")."</a>
		</div>
		";
		global $refresh, $manage;
		if ($return=="manage") {
			$refresh = "manage.php?id=".$patient;
		}
 	} // end method PatientImages->add

	function del () {
		// Delete actual image
		global $id, $patient;
		unlink("img/store/".freemed::secure_filename($patient).
			".".freemed::secure_filename($id).".djvu");

		// Run stock deletion routine
		$this->_del();
	} // end function PatientImages->del

	function display () {
		global $sql, $id, $patient, $display_buffer, $return;
		if (!$patient or !$id) return false;
		$display_buffer .= "
		<div ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<embed SRC=\"patient_image_handler.php?".
		"patient=".urlencode($patient)."&".
		"id=".urlencode($id)."\" BORDER=\"0\"
		PLUGINSPAGE=\"".COMPLETE_URL."support/\"
		TYPE=\"image/x.djvu\" WIDTH=\"100%\" HEIGHT=\"600\"></embed>
		</div>
		<div ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<a HREF=\"";
		if ($return=="manage") {
			$display_buffer .= "manage.php?id=$patient\">".
			_("Manage Patient");
		} else {
			$display_buffer .= $this->page_name."?".
			"module=".urlencode($module)."&".
			"action=view\">". _($record_name);
		}
		$display_buffer .= "</a>
		</div>
		";
	} // end function PatientImages->display

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
		if(check_module("episodeOfCare")) {
			// Actual piece
			$imageeoc = sql_squash($imageeoc); // for multiple choice (HACK)
			$related_episode_array = array (
			_("Related Episode(s)") =>
			freemed::multiple_choice ("SELECT id,eocdescrip,eocstartdate,".
                                  "eocdtlastsimilar FROM eoc WHERE ".
                                  "eocpatient='".addslashes($patient)."'",
                                  "##eocdescrip## (##eocstartdate## to ##eocdtlastsimilar##)",
                                  "imageeoc",
                                  $imageeoc,
                                  false),
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

			_("Date") =>
			date_entry ("imagedt"),

			_("Type of Image") =>
			html_form::select_widget(
				"imagetype",
				array(
					_("Insurance Card") => "insurance_card",
					_("Lab Report") => "lab_report",
					_("Miscellaneous") => "misc",
					_("Operative Report") => "op_report",
					_("Pathology") => "pathology",
					_("Patient History") => "patient_history",
					_("Questionnaire") => "questionnaire",
					_("Radiology") => "radiology",
					_("Referral") => "referral"
				)
			)

		), $related_episode_array,
		array (

			_("Description") =>
			html_form::text_widget("imagedesc", 30, 150),

			_("Attach Image") =>
			( (($action=="add") || ($action=="addform")) ?
			( ($this->IEupload) ? $this->activeXupload() :
			"<input TYPE=\"FILE\" NAME=\"imageupload\"/>" ) :
			_("ATTACHED") )

		)));

		$display_buffer .= "
			<div ALIGN=\"CENTER\">
			<input TYPE=\"SUBMIT\" VALUE=\"".
			( ($action=="add" || $action=="addform") ?
			_("Attach Image") : _("Modify") )."\" class=\"button\"/>
			<input TYPE=\"SUBMIT\" NAME=\"submit\" ".
			 "VALUE=\""._("Cancel")."\" class=\"button\"/>
			</div>

		</form></div>
		";
	} // end of function PatientImages->form()

	function mod () {
		global $display_buffer, $sql, $imageeoc, $patient, $module, $id;
		$display_buffer .= "
			<div ALIGN=\"CENTER\"><b>"._("Modifying")." ... </b>
		";

		// Have to add then update to get file name
		$query = $sql->update_query (
			$this->table_name,
			array (
				"imagedt" => date_assemble("imagedt"),
				"imagepat" => $patient,
				"imagetype",
				"imagedesc",
				"imageeoc"
			), array ( "id" => $id )
		);
		$result = $sql->query ($query);
		if ($debug) $display_buffer .= "(query = '$query') ";

		if ($result) {
			$display_buffer .= " <b> "._("done").". </b>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <b> <FONT COLOR=#ff0000>"._("ERROR")."</FONT> </b>\n";
		}

		$display_buffer .= "
		</div>
		<p/>
		<div ALIGN=\"CENTER\"><a HREF=\"manage.php?id=$patient\"
		>"._("Manage Patient")."</a>
		<b>|</b>
		<a HREF=\"$this->page_name?module=$module&patient=$patient\"
		>"._($this->record_name)."</a>
		</div>
		";
 	} // end method PatientImages->add

	function view () {
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
				"ORDER BY imagedt"
			),
			$this->page_name,
			array (
				"Date"        => "imagedt",
				"Description" => "imagedesc"
			), // array
			array (
				"",
				_("NO DESCRIPTION")
			),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW | ITEMLIST_DEL
		);
		$display_buffer .= "\n<p/>\n";
	} // end function PatientImages->view()

} // end of class PatientImages

register_module ("PatientImages");

?>
