<?php
 // $Id$
 // $Author$
 // desc: library for default template

define ('IMAGE_TYPE', "png");

// Class: template
//
//	Defines template namespace. This should be defined in any
//	template, and all methods should do *something*, otherwise
//	FreeMED may act very unpredictable.
//
class template {

	// Method: link_bar
	//
	//	Produce XHTML group of links.
	//
	// Parameters:
	//
	//	$links - Associative array of links, where they are in the
	//	form of the key being the link text and the value being the
	//	URL.
	//
	//	$opts - (optional) An associative array of options. "align"
	//	sets the alignment of the DIV for the links.
	//
	// Returns:
	//
	//	XHTML code
	//
	function link_bar ( $links, $opts = '' ) {
		// Check for valid input
		if (!array($links)) return false;

		// Process each one ...
		foreach ($links AS $text => $url) {
			if (!empty($text)) {
				$bar[] = "<span style=\"padding: 2px\">".
					template::link_button($text, $url).
					"</span>";
			}
		}

		// ... then join them back together
		return "<div align=\"".( $opts['align'] ? $opts['align'] : 'center' ).
			"\">".join('', $bar)."</div>\n";
	} // end function template::link_bar

	// Method: link_button
	//
	//	Creates an XHTML "button" for a link
	//
	// Parameters:
	//
	//	$text - Text to be displayed on the button
	//
	//	$url - URL for the button to access
	//
	//	$options - (optional) Associative array of options.
	//	"type" sets the class of the button.
	//
	// Returns:
	//
	//	XHTML code
	//
	function link_button ($text, $url, $options = NULL) {
		return "<a class=\"".
			( $options['type'] ? $options['type'] : 'button' ).
			"\" href=\"".$url."\" ".
			"onMouseOver=\"window.status=''; return true;\">".
			prepare($text)."</a>";
	} // end function template::link_button

	// Method: patient_box
	//
	//	Creates a patient information box
	//
	// Parameters:
	//
	//	$patient_object - FreeMED.Patient object
	//
	// Returns:
	//
	//	XHTML code
	//
	// See Also:
	//	<freemed::patient_box>
	//
	function patient_box ( $patient_object ) {
		// empty buffer
		$buffer = "";

		// top of box
		$buffer .= "
    <div ALIGN=\"CENTER\">
    <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"5\" WIDTH=\"100%\">
     <tr CLASS=\"patientbox\"
	onClick=\"window.location='manage.php?id=".
		urlencode($patient_object->id)."'; return true;\"
      ><td VALIGN=\"CENTER\" ALIGN=\"LEFT\">
      <a HREF=\"manage.php?id=".urlencode($patient_object->id)."\"
       CLASS=\"patientbox\" NAME=\"patientboxlink\"><b>".
       $patient_object->fullName().
      "</b></a>
     </td><td ALIGN=\"CENTER\" VALIGN=\"CENTER\">
      ".( (!empty($patient_object->local_record["ptid"])) ?
          $patient_object->idNumber() : "(no id)" )."
     </td><td ALIGN=\"CENTER\" VALIGN=\"CENTER\">
	".template::patient_box_iconbar($patient_object->id)."
     </td><td VALIGN=\"CENTER\" ALIGN=\"RIGHT\">
      <font COLOR=\"#cccccc\">
       <small>".$patient_object->age()." old, DOB ".
        $patient_object->dateOfBirth()."</small>
      </font>
     </td></tr>
    </table>
    </div>
		";
  
		// return buffer
		return $buffer;
	} // end method patient_box

	// Method: patient_box_iconbar
	//
	//	Creates the iconbar used by the default template in the
	//	<template::patient_box> method.
	//
	// Parameters:
	//
	//	$patient - id of the current patient
	//
	// Returns:
	//
	//	XHTML code
	//
	// See Also:
	//	<template::patient_box>
	//
	function patient_box_iconbar ($patient) {
		$buffer .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"1\">\n".
			"<tr>".
			
			// Icon for patient appointments
			"<td><a href=\"book_appointment.php?patient=".urlencode($patient).
			"&type=pat".
			"\" onMouseOver=\"window.status='".__("Book Appointment")."'; ".
				"return true;\" ".
			"onMouseOut=\"window.status=''; return true;\"".
			"><img src=\"lib/template/".(
				file_exists("lib/template/".$GLOBALS['template']."/img/pbox_book_appointment.".IMAGE_TYPE) ?
				$GLOBALS['template'] : 'default' )."/img/".
			"pbox_book_appointment.".IMAGE_TYPE."\" border=\"0\" ".
			"width=\"16\" height=\"16\" ".
			"alt=\"".__("Book Appointment")."\"/></a></td>\n".
			"<td><a href=\"patient.php\" ".
			"onMouseOver=\"window.status='".__("Choose Another Patient")."'; return true;\" ".
			"onMouseOut=\"window.status=''; return true;\"".
			"><img src=\"lib/template/".(
				file_exists("lib/template/".$GLOBALS['template']."/img/magnifying_glass.".IMAGE_TYPE) ?
				$GLOBALS['template'] : 'default' )."/img/".
			"magnifying_glass.".IMAGE_TYPE."\" border=\"0\" ".
			"width=\"16\" height=\"16\" ".
			"alt=\"".__("Choose Another Patient")."\"/></a></td>\n".
			
			"</tr></table>\n";
		return $buffer;
	} // end method patient_box_iconbar

	// Method: summary_delete_link
	//
	//	Creates a delete link for the EMR summary screen
	//
	// Parameters:
	//
	//	$class - Class of the EMR module in question
	//
	//	$url - Location that should be loaded if this is
	//	successful
	//
	// Returns:
	//
	//	XHTML widget
	//
	function summary_delete_link($class, $url) {
		$buffer .= html_form::confirm_link_widget($url,
			"<img SRC=\"lib/template/default/img/summary_delete.png\"
			BORDER=\"0\" ALT=\"".__("Delete")."\"/>",
			array(
				'confirm_text' =>
				__("Are you sure you want to delete this?"),

				'text' => __("Delete"),
				//'class' => 'button'
			)
		);
		return $buffer;
	} // end function summary_delete_link

	// Method: summary_lock_link
	//
	//	Creates a lock link for the EMR summary screen
	//
	// Parameters:
	//
	//	$class - Class of the EMR module in question
	//
	//	$url - Location that should be loaded if this is
	//	successful
	//
	// Returns:
	//
	//	XHTML widget
	//
	function summary_lock_link($class, $url) {
		$buffer .= html_form::confirm_link_widget($url,
			"<img SRC=\"lib/template/default/img/summary_lock.png\"
			BORDER=\"0\" ALT=\"".__("Lock")."\"/>",
			array(
				'confirm_text' =>
				__("Are you sure you want to lock this record?"),

				'text' => __("Lock"),
				//'class' => 'button'
			)
		);
		return $buffer;
	} // end function summary_lock_link

	// Method: summary_locked_link
	//
	//	Creates a locked link for the EMR summary screen
	//
	// Parameters:
	//
	//	$class - Class of the EMR module in question
	//
	//	$url - Location that should be loaded if this is
	//	successful
	//
	// Returns:
	//
	//	XHTML widget
	//
	function summary_locked_link($class, $url) {
		$buffer .= "<a onClick=\"var a=alert('".
			__("This record has been locked, and can no longer be modified.").
			"'); return true;\"
			><img SRC=\"lib/template/default/img/summary_locked.png\"
			BORDER=\"0\" ALT=\"".__("Locked")."\"/></a>\n";
		return $buffer;
	} // end function summary_locked_link

	// Method: summary_modify_link
	//
	//	Creates a modify link for the EMR summary screen
	//
	// Parameters:
	//
	//	$class - Class of the EMR module in question
	//
	//	$url - Location that should be loaded if this is
	//	successful
	//
	// Returns:
	//
	//	XHTML widget
	//
	function summary_modify_link($class, $url) {
		$buffer .= "<a href=\"".$url."\" ".
			//"class=\"button\" ".
			"><img SRC=\"lib/template/default/img/summary_modify.png\"
			BORDER=\"0\" ALT=\"".__("Modify")."\"/></a>";
		return $buffer;
	} // end function summary_modify_link

	// Method: summary_print_link
	//
	//	Creates a print link for the EMR summary screen
	//
	// Parameters:
	//
	//	$class - Class of the EMR module in question
	//
	//	$url - Location that should be loaded if this is
	//	successful
	//
	// Returns:
	//
	//	XHTML widget
	//
	function summary_print_link($class, $url) {
		$buffer .= "<a href=\"#\" onClick=\"printWindow=".
			"window.open('".$url."', 'printWindow', ".
			"'width=400,height=200,menubar=no,titlebar=no'); ".
			"printWindow.opener=self; return true;\" ".
			"><img SRC=\"lib/template/default/img/summary_print.png\"
			BORDER=\"0\" ALT=\"".__("Print")."\"/></a>";
		return $buffer;
	} // end function summary_print_link

	// Method: summary_view_link
	//
	//	Creates a view link for the EMR summary screen
	//
	// Parameters:
	//
	//	$class - Class of the EMR module in question
	//
	//	$url - Location that should be loaded if this is
	//	successful
	//
	// Returns:
	//
	//	XHTML widget
	//
	function summary_view_link($class, $url, $newwindow = false) {
		$buffer .= "<a HREF=\"".$url."\" ".
			//"class=\"button\" ".
			( $newwindow ? "TARGET=\"".$class."_view\"" : ""
			)."><img SRC=\"lib/template/default/img/summary_view.png\"
			BORDER=\"0\" ALT=\"".__("View")."\"/></a>";
		return $buffer;
	} // end function summary_view_link

} // end class template

?>
