<?php
 // $Id$
 // $Author$
 // desc: library for default template

define ('IMAGE_TYPE', "png");

class template {

	function summary_delete_link($class, $url) {
		$buffer .= html_form::confirm_link_widget($url,
			"<img SRC=\"lib/template/default/img/summary_delete.png\"
			BORDER=\"0\" ALT=\""._("Delete")."\"/>",
			array(
				'confirm_text' =>
				_("Are you sure you want to delete this?"),

				'text' => _("Delete")
			)
		);
		return $buffer;
	} // end function summary_delete_link

	function summary_modify_link($class, $url) {
		$buffer .= "<A HREF=\"".$url."\" ".
			"><IMG SRC=\"lib/template/default/img/summary_modify.png\"
			BORDER=\"0\" ALT=\""._("Modify")."\"></A>";
		return $buffer;
	} // end function summary_modify_link

	function summary_view_link($class, $url, $newwindow = false) {
		$buffer .= "<A HREF=\"".$url."\" ".(
			$newwindow ? "TARGET=\"".$class."_view\"" : ""
			)."><IMG SRC=\"lib/template/default/img/summary_view.png\"
			BORDER=\"0\" ALT=\""._("View")."\"></A>";
		return $buffer;
	} // end function summary_modify_link

} // end class template

?>
