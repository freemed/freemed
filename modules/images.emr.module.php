<?php
 // $Id$
 // $Author$
 // note: images module for patient management
 // lic : GPL, v2

if (!defined("__IMAGES_MODULE_PHP__")) {

define ('__IMAGES_MODULE_PHP__', true);

class patientImages extends freemedEMRModule {

	var $MODULE_NAME = "Patient Images";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
		FreeMED Patient Images allows images to be
		stored, as if they were in a paper chart.
	";

	var $record_name   = "Patient Images";
	var $table_name    = "images";
	var $patient_field = "imagepat";
	var $order_by      = "imagedt";

	function patientImages () {
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
			"id"		=>	SQL_NOT_NULL(
							SQL_AUTO_INCREMENT(
							SQL_INT_UNSIGNED(0)
							)
						)
		);

		// call parent constructor
		$this->freemedEMRModule();
	} // end constructor patientImages

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
	} // end function patientImages->activeXupload

	function add () {
		global $display_buffer, $sql, $imageeoc, $patient, $module;
		$display_buffer .= "
			<CENTER><B>"._("Adding")." ... </B>
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
			$display_buffer .= " <B> "._("done").". </B>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <B> <FONT COLOR=#ff0000>"._("ERROR")."</FONT> </B>\n";
		}
		$display_buffer .= "<BR>\n";

		// Update database with proper file name
		$display_buffer .= "
			<CENTER><B>"._("Updating database")." ... </B>
		";

		$query = $sql->update_query (
			$this->table_name,
			array ( "imagefile" => $imagefilename ),
			array ( "id" => $last_record )
		);
		$result = $sql->query ($query);

		if ($result) {
			$display_buffer .= " <B> "._("done").". </B>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <B> <FONT COLOR=#ff0000>"._("ERROR")."</FONT> </B>\n";
		}

		$display_buffer .= "
		</CENTER>
		<BR><BR>
		<CENTER><A HREF=\"manage.php?id=$patient\"
		>"._("Manage Patient")."</A>
		<B>|</B>
		<A HREF=\"$this->page_name?module=$module&patient=$patient\"
		>"._($this->record_name)."</A>
		<B>|</B>
		<A HREF=\"$this->page_name?module=$module&patient=$patient&".
		"action=addform\"
		>"._("Add Another")."</A>
		";
		global $refresh, $manage;
		if ($return=="manage") {
			$refresh = "manage.php?id=".$patient;
		}
 	} // end method patientImages->add

	function del () {
		// Delete actual image
		global $id, $patient;
		unlink("img/store/".freemed::secure_filename($patient).
			".".freemed::secure_filename($id).".djvu");

		// Run stock deletion routine
		$this->_del();
	} // end function patientImages->del

	function display () {
		global $sql, $id, $patient, $display_buffer, $return;
		if (!$patient or !$id) return false;
		$display_buffer .= "
		<DIV ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<EMBED SRC=\"patient_image_handler.php?".
		"patient=".urlencode($patient)."&".
		"id=".urlencode($id)."\" BORDER=\"0\"
		PLUGINSPAGE=\"".COMPLETE_URL."support/\"
		TYPE=\"image/x.djvu\" WIDTH=\"100%\" HEIGHT=\"600\"></EMBED>
		</DIV>
		<DIV ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<A HREF=\"";
		if ($return=="manage") {
			$display_buffer .= "manage.php?id=$patient\">".
			_("Manage Patient");
		} else {
			$display_buffer .= $this->page_name."?".
			"module=".urlencode($module)."&".
			"action=view\">". _($record_name);
		}
		$display_buffer .= "</A>
		</DIV>
		";
	} // end function patientImages->display

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
			freemed_multiple_choice ("SELECT id,eocdescrip,eocstartdate,".
                                  "eocdtlastsimilar FROM eoc WHERE ".
                                  "eocpatient='".addslashes($patient)."'",
                                  "eocdescrip:eocstartdate:eocdtlastsimilar",
                                  "imageeoc",
                                  $imageeoc,
                                  false),
			);
		} else {
			// Put in blank array instead
			$related_episode_array = array ("" => "");
		}

		$display_buffer .= "
		<DIV ALIGN=\"CENTER\">
		<FORM METHOD=\"POST\" ACTION=\"module_loader.php\" ".
		"ENCTYPE=\"multipart/form-data\" NAME=\"myform\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"module\" ".
		"VALUE=\"".prepare($module)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"id\" ".
		"VALUE=\"".prepare($id)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"action\" ".
		"VALUE=\"".($action=="addform" ? "add" : "mod" )."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"patient\" ".
		"VALUE=\"".prepare($patient)."\">
		<INPUT TYPE=\"HIDDEN\" NAME=\"MAX_FILE_SIZE\" VALUE=\"10000000\">
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
			"<INPUT TYPE=\"FILE\" NAME=\"imageupload\">" ) :
			_("ATTACHED") )

		)));

		$display_buffer .= "
			<DIV ALIGN=\"CENTER\">
			<INPUT TYPE=\"SUBMIT\" VALUE=\"".
			( ($action=="add" || $action=="addform") ?
			_("Attach Image") : _("Modify") )."\">
			<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" ".
			 "VALUE=\""._("Cancel")."\">
			</DIV>

		</FORM></DIV>
		";
	} // end of function patientImages->form()

	function mod () {
		global $display_buffer, $sql, $imageeoc, $patient, $module, $id;
		$display_buffer .= "
			<CENTER><B>"._("Modifying")." ... </B>
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
			$display_buffer .= " <B> "._("done").". </B>\n";
		} else {
			if ($debug) $display_buffer .= "(query = '$query') ";
			 $display_buffer .= " <B> <FONT COLOR=#ff0000>"._("ERROR")."</FONT> </B>\n";
		}

		$display_buffer .= "
		</CENTER>
		<BR><BR>
		<CENTER><A HREF=\"manage.php?id=$patient\"
		>"._("Manage Patient")."</A>
		<B>|</B>
		<A HREF=\"$this->page_name?module=$module&patient=$patient\"
		>"._($this->record_name)."</A>
		";
 	} // end method patientImages->add

	function view () {
		global $display_buffer;
		global $patient, $action;
		reset ($GLOBALS);
		while (list($k,$v)=each($GLOBALS)) global $$k;

		// Check for "view" action (actually display)
		if ($action=="view") {
			$this->display();
			return NULL;
		}

		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE (imagepat='".addslashes($patient)."') ".
			"ORDER BY imagedt";
		$result = $sql->query ($query);

		$display_buffer .= freemed_display_itemlist(
			$result,
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
		$display_buffer .= "\n<P>\n";
	} // end function patientImages->view()

} // end of class patientImages

register_module ("patientImages");

} // end if defined

?>
