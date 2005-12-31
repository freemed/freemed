<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.UtilityModule');

class FaxMultiple extends UtilityModule {

	var $MODULE_NAME = "Fax Multiple Attachments";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";

	var $MODULE_FILE = __FILE__;

	function FaxMultiple ( ) {
		// GettextXML:
		//	__("Fax Multiple Attachments")

		// Set appropriate associations
		$this->_SetHandler('Utilities', 'utility');
		$this->_SetMetaInformation('UtilityName', __("Fax Multiple Attachments"));
		$this->_SetMetaInformation('UtilityDescription', __("Fax multiple segments of an electronic medical record."));

		// Call parent constructor
		$this->UtilityModule();
	} // end constructor FaxMultiple

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
			case 'faxstatus':
				return $this->fax_status();
				break;

			// By default, we show the form with information
			// regarding what is going on.
			default:
				return $this->fax_wizard();
				break;

		}
	} // end method utility

	function fax_status ( ) {
		global $display_buffer;
		$fax = CreateObject('_FreeMED.Fax', '/dev/null');
		$status = $fax->State($_REQUEST['faxstatus']);
		$display_buffer .= "<b>".$output."</b>\n";
		if ($status == 1) {
			$display_buffer .= "<div align=\"center\"><b>".__("Fax sent successfully.")."</b></div>\n";
			$display_buffer .= "<div align=\"center\"><a onClick=\"javascript:close();\" class=\"button\">".__("Close")."</div>\n";
		} else {
			$display_buffer .= "<b>".__("Fax is attempting to send: ")."</b>".$status."\n";
			$GLOBALS['__freemed']['automatic_refresh'] = 10;
		}
		template_display();
	} // end method fax_status

	function fax_wizard ( ) {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }
	
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
		$w->add_page ( __("Step Two: Choose the Attachments to Fax"),
			array ( 'attachment' ),
			html_form::form_table ( array (
				__("Attachment") => $this->_multiple_select(
					'attachment',
					$attachments
				)
			) )
		);

		// Page Three: Select a Patient
		$w->add_page ( __("Step Three: Select a Destination"),
			array ( 'to', 'to_number' ),
			html_form::form_table ( array (
				__("Destination (provider)") =>
				module_function('providermodule',
					'widget',
					array ( 'to', false, 'phyfaxa' )
				),

				__("Destination (number)") =>
				html_form::text_widget('to_number')
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
			// Finished
			foreach ($_REQUEST['attachment'] AS $a) {
				list ($module, $record) = explode ('|', $a);
				$f = freemed::module_get_meta ( $module, 'patient_field' );
				$t = freemed::module_get_meta ( $module, 'table_name' );

				if ($f and $t) {
					include_once(resolve_module($module));
					//print "module = $module<br/>\n";
					$m = new $module ( );
					//print_r($m); print "<br/>\n";
				
					// Handle render
					if ($render = $m->print_override($record)) {
						// Handle this elsewhere
					} else {
						// Create TeX object for patient
						$TeX = CreateObject('FreeMED.TeX', array());
	
						// Actual renderer for formatting array
						if ($this->summary_query) {
							// If this is an EMR module with additional
							// fields, import them
							$query = "SELECT *".
								( (count($this->summary_query)>0) ? 
								",".join(",", $this->summary_query)." " : " " ).
								"FROM ".$t." ".
								"WHERE id='".addslashes($record)."'";
								$result = $GLOBALS['sql']->query($query);
								$rec = $GLOBALS['sql']->fetch_array($result);
						} else {
							$rec = freemed::get_link_rec($record, $t);
						} // end checking for summary_query

						$TeX->_buffer = $TeX->RenderFromTemplate(
							$m->print_template,
							$rec
						);
						$render = $TeX->RenderToPDF(!(empty($m->print_template)));
					} // end render if/else

					// If render exists, drop to a file
					//print $render; print "<br/>\n";
					if ($render) { $files[] = $render; }
				} // end if f and t
			} // end foreach

			//print_r($files);
			$fax = CreateObject('FreeMED.Fax', 
				$files,
				array (
					'sender' => $this_user->user_descrip,
					'comment' => __("HIPPA Compliance Notice: This transmission contains sensitive medical data.")
				)
			);
			//print ($_REQUEST['fax_number']);
			$output = $fax->Send($_REQUEST['to'] ? $_REQUEST['to'] : $_REQUEST['to_number']);
			$display_buffer .= "<b>".$output."</b>\n";
			$this_user->setFaxInQueue(
				$output,
				$_REQUEST['from'],
				$_REQUEST['fax_number']
				);
			return $buffer;
		}
	} // end method menu

	function GetEMRAttachments ( $patient ) {
		if ($patient <= 0) { return array (); }

		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $k => $v) {
			$f = freemed::module_get_meta ( $v['MODULE_CLASS'], 'patient_field' );
			$t = freemed::module_get_meta ( $v['MODULE_CLASS'], 'table_name' );
			$h = freemed::module_get_meta ( $v['MODULE_CLASS'], 'widget_hash' );
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

	function _multiple_select ( $name, $values ) {
		$buffer .= "<select name=\"".prepare($name)."[]\" ".
			"size=\"6\" multiple=\"multiple\">\n";
		foreach ($values AS $k => $v) {
			$buffer .= html_form::select_option (
				$name,
				$v,
				$k
			);
		}
		$buffer .= "</select>\n";
		return $buffer;
	} // end method _multiple_select

} // end class FaxMultiple

register_module('FaxMultiple');

?>
