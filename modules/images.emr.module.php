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
		// Define variables for EMR summary
		$this->summary_vars = array (
			_("Date")        =>	"imagedt",
			_("Type")        =>	"imagetype",
			_("Description") =>	"imagedesc"
		);
		$this->summary_view_link = true;

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
	} // end constructor progressNotes

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
		global $HTTP_POST_FILES;
$debug = true;
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

		// Update database with proper file name
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
		";
 	} // end method patientImages->add

	function display () {
		global $sql, $id, $patient, $display_buffer;
		if (!$patient or !$id) return false;
		$display_buffer .= "
		<DIV ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<IMG SRC=\"patient_image_handler.php?".
		"patient=".urlencode($patient)."&".
		"id=".urlencode($id)."\" BORDER=\"0\">
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
		"ENCTYPE=\"multipart/form-data\">
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
					_("Miscellaneous") => "misc",
					_("Questionnaire") => "questionnaire",
					_("Referral") => "referral"
				)
			)

		), $related_episode_array,
		array (

			_("Description") =>
			html_form::text_widget("imagedesc", 30, 150),

			_("Attach Image") =>
			( (($action=="add") || ($action=="addform")) ?
			"<INPUT TYPE=\"FILE\" NAME=\"imageupload\">" :
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
