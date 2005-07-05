<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.UtilityModule');

class EMRAttachments extends UtilityModule {

	var $MODULE_NAME = "EMR Attachments";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";

	var $MODULE_FILE = __FILE__;

	function EMRAttachments ( ) {
		// GettextXML:
		//	__("EMR Attachments")

		// Set appropriate associations
		$this->_SetHandler('Utilities', 'utility');
		$this->_SetMetaInformation('UtilityName', __("EMR Attachments"));
		$this->_SetMetaInformation('UtilityDescription', __("Move EMR segments from one patient record to another."));

		// Call parent constructor
		$this->UtilityModule();
	} // end constructor EMRAttachments

	function utility () {
		global $display_buffer, $utility_action;
		
		// Handle "Cancel" submit action by returning to previous
		// menu through refresh.
		if ($__submit == __("Cancel")) {
			global $refresh;
			$refresh = "utilities.php";
			return '';
		}

		// Act as a switchboard for everything
		switch ($utility_action) {

			// By default, we show the form with information
			// regarding what is going on.
			default:
				return $this->move_wizard();
				break;

		}
	} // end method utility

	function move_wizard ( ) {
		$w = CreateObject('PHP.wizard', array (
			'action',
			'type',
			'patient',
			'utility_action'
		));

		if (!$w->been_here()) {
			global $from;
			$from = $GLOBALS['patient'];
		}

		// Page One: Select a Patient
		$w->add_page ( __("Step One: Select a Patient"),
			array ( 'from' ),
			html_form::form_table ( array (
				__("Patient") => freemed::patient_widget('from', $w->formname, '__action')
			) )
		);

		// Processing for stage two: figure out which attachments ...
		if ($_REQUEST['from'] > 0) {
			$attachments = $this->GetEMRAttachments($_REQUEST['from']);
		}
		$w->add_page ( __("Step Two: Choose the Attachment to Move"),
			array ( 'attachment' ),
			html_form::form_table ( array (
				__("Attachment") => html_form::select_widget(
					'attachment',
					$attachments
				)
			) )
		);

		// Page Three: Select a Patient
		$w->add_page ( __("Step Three: Select a Destination"),
			array ( 'to' ),
			html_form::form_table ( array (
				__("Destination Patient") => freemed::patient_widget('to', $w->formname, '__action')
			) )
		);

		if (!$w->is_done() and !$w->is_cancelled()) {
			// Display
			$buffer .= "<center>\n".
				$w->display().
				"</center>\n";
			return $buffer;
		} elseif ($w->is_cancelled()) {
			// Cancelled
		} else {
			if (!$_REQUEST['from'] or !$_REQUEST['to']) {
				trigger_error(__("Operation cannot be completed, since either source or destination patient record were not specified."), E_USER_ERROR);
			}
			
			// Finished
			list ($module, $record) = explode ('|', $_REQUEST['attachment']);
			$f = freemed::module_get_meta ( $module, 'patient_field' );
			$t = freemed::module_get_meta ( $module, 'table_name' );
			if ($f and $t) {
				$buffer .= "Moving record ".$_REQUEST['attachment'].
					" from patient ".$_REQUEST['from'].
					" to patient ".$_REQUEST['to']."<br/>\n";
				$q = $GLOBALS['sql']->update_query(
					$t,
					array ( $f => $_REQUEST['to'] ),
					array ( 'id' => $record )
				);
				$result = $GLOBALS['sql']->query($q);
				if ($result) {
					$buffer .= __("Record was successfully moved.")."<br/>\n";
				} else {
					$buffer .= __("Failed to move record.")."<br/>\n";
					return $buffer;
				}

				// Move annotations, if there are any
				$q = "UPDATE annotations ".
					"SET ".
						"apatient = '".addslashes($_REQUEST['to'])."' ".
					"WHERE ".
						"apatient = '".addslashes($_REQUEST['from'])."' AND ".
						"atable = '".addslashes($t)."' AND ".
						"aid = '".addslashes($record)."'";
				$result = $GLOBALS['sql']->query($q);
				if ($result) {
					$buffer .= __("Annotations were successfully moved.")."<br/>\n";
				}

				// Additional move
				module_function($module, 'additional_move',
					array (
						$record,
						$_REQUEST['from'],
						$_REQUEST['to']
					)
				);
			}
			return $buffer;
		}
	} // end method menu

	function GetEMRAttachments ( $patient ) {
		if ($patient <= 0) { return array (); }

		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $k => $v) {
			$f = freemed::module_get_meta ( $v['MODULE_CLASS'], 'patient_field' );
			$t = freemed::module_get_meta ( $v['MODULE_CLASS'], 'table_name' );
			$h = freemed::module_get_meta($v['MODULE_CLASS'], 'widget_hash');
			if ($f and $t and $h) {
				$q = "SELECT * FROM ".addslashes($t)." WHERE ".
					addslashes($f)."='".addslashes($patient)."'";
				$res = $GLOBALS['sql']->query($q);
				// If there are results, add them
				if ($GLOBALS['sql']->results($res)) {
					while ($r = $GLOBALS['sql']->fetch_array($res)) {
						// Blank key/value pair
						$new_k = $new_v = '';

						// Value is the class and id
						$new_v = $v['MODULE_CLASS'].'|'.$r['id'];
						$new_k = $v['MODULE_NAME'].'| '.$this->WidgetHash($h, $r);

						// Add to stack
						$a[$new_k] = $new_v;
					} // end while results
				} // end if results
			} // end if this is even an EMR module
		} // end looping
		return $a;
	} // end method GetEMRAttachments

	function WidgetHash ( $hash, $data ) {
		if (!(strpos($hash, '##') === false)) {
			$split = explode('##', $hash);
			foreach ($split AS $k => $v) {
				if (!($k & 1)) {
					$r .= prepare($v);
				} else {
					if (!(strpos($v, ':') === false)) {
						$_v = explode(':', $v);
						$r .= prepare(freemed::get_link_field($data[$_v[0]], $_v[1], $_v[2]));
					} else {
						$r .= prepare($data[$v]);
					}
				}
			}
		} else {
			$r = $hash;
		}
		return $r;
	} // end method WidgetHash

} // end class EMRAttachments

register_module('EMRAttachments');

?>
