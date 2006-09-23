<?php
	// $Id$
	// $Author: rufustfirefly $

LoadObjectDependency('_FreeMED.EMRModule');

class PhotoIdView extends EMRModule {

	var $MODULE_NAME = "Photo Id";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_HIDDEN = false;

	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	// Dummy array for prototype:
	var $summary_items = array ( 1,2,3 );

	function PhotoIdView () {
		// Call parent constructor
		$this->EMRModule();
	} // end constructor 

	// The EMR box; probably the most important part of this module
	function summary ($patient, $num_summary_items) {
                if (file_exists(freemed::image_filename(
                                $patient,
                                'identification',
                                'djvu'))) {
			$buffer .= "
                        <DIV ALIGN=\"CENTER\">
                        <A HREF=\"patient_image_handler.php?".
                        "patient=".urlencode($patient)."&".
                        "id=identification\" TARGET=\"new\"
                        onMouseOver=\"window.status='".__("Enlarge image")."'; return true;\"
                        onMouseOut=\"window.status=''; return true;\"
                        ><EMBED SRC=\"patient_image_handler.php?".
                        "patient=".urlencode($patient)."&id=identification\"
                         BORDER=\"0\" ALT=\"Photographic Identification\"
                         WIDTH=\"200\" HEIGHT=\"150\"
                         TYPE=\"image/x.djvu\"
                         PLUGINSPAGE=\"".COMPLETE_URL."support/\"
                         ></EMBED></A>
                        </DIV>
			";
		} else {
			$buffer .= __("No photographic identification on file.");
		}
		return $buffer;
	} // end method summary

	function summary_bar() {
                if (file_exists(freemed::image_filename($_REQUEST['id'], 'identification', 'djvu'))) {
			$buffer .= "<a href=\"photo_id.php?patient=".urlencode($_REQUEST['id'])."&".
                        "return=manage\"
                         >".__("Update")."</a> |
                        <a href=\"photo_id.php?patient=".urlencode($_REQUEST['id'])."&".
                        "action=remove&return=manage\"
                         >".__("Remove")."</a>\n";
		} else {
			$buffer .= "<a HREF=\"photo_id.php?patient=".urlencode($_REQUEST['id'])."\">".__("Update")."</a>\n";
		}
		return $buffer;
	} // end method summary_bar

} // end class PhotoIdView

register_module ("PhotoIdView");

?>
