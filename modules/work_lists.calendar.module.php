<?php
	// $Id: updates.module.php 2333 2005-11-22 21:13:38Z jeff $
	// $Author$

LoadObjectDependency('_FreeMED.BaseModule');

class WorkListsModule extends BaseModule {

	var $MODULE_NAME = "Work Lists";
	var $MODULE_VERSION = "0.1";
	var $MODULE_AUTHOR = "jeff@ourexchange.net";
	var $MODULE_FILE = __FILE__;
	var $PACKAGE_MINIMUM_VERSION = "0.8.2";

	function WorkListsModule ( ) {
		// __("Work Lists")

		// Add main menu notification handlers
		$this->_SetHandler('MainMenu', 'notify');

		// Form proper configuration information
		$this->_SetMetaInformation('global_config_vars', array(
			'worklist_enabled',
			'worklist_providers'
		));
		$this->_SetMetaInformation('global_config', array(
			__("Show Work Lists") =>
			'html_form::select_widget ( '.
			'"worklist_enabled", '.
			'array ('.
				__("enabled").' => 1, '.
				__("disabled").' => 0, '.
			' ) ) ',
			__("Work List Providers") =>
			'freemed::multiple_choice ( '.
				'"SELECT phylname, phyfname, id '.
				'FROM physician WHERE phyref=\'no\' '.
				'ORDER BY phylname, phyfname", '.
				'"##phylname##, ##phyfname##", '.
				'"worklist_providers", fm_join_from_array($worklist_providers), '.
				'false )'
			)
		);
		
		// Call parent constructor
		$this->BaseModule();
	} // end constructor WorkListsModule

	function notify ( ) {
		global $display_buffer;

		$enable = freemed::config_value( 'worklist_enabled' );
		$providers = freemed::config_value( 'worklist_providers' );

		// Skip if this is not enabled
		if (!$enable) { return false; }

		include_once(freemed::template_file('ajax.php'));

		$display_buffer .= "
		<script language=\"javascript\">
		var workListElementActive = 0;
		var workListElementCurrent;

		function workListClick ( id ) {
			if (workListElementActive) { return false; }
			workListElementCurrent = id;
			document.getElementById(id).innerHTML = '<select id=\"worklist_select\" onChange=\"workListProcess(\'worklist_select\', \'' + id + '\'); return true;\">' +
			'<option value=\"\">-</option>'+
		";

		$q = $GLOBALS['sql']->query("SELECT * FROM schedulerstatustype ORDER BY id");
		while ($r = $GLOBALS['sql']->fetch_array($q)) {
			$display_buffer .= "'<option value=\"${r['id']}\">${r['sname']}</option>'+\n";
		}
		$display_buffer .= "
			'</select>';
			workListElementActive = 1;
		}

		function workListProcess ( id, parent ) {
			if (! document.getElementById(id).value ) {
				document.getElementById(parent).innerHTML = '&nbsp;';
				workListElementCurrent = '';
				workListElementActive = 0;
				return false;
			}
			x_module_html('".get_class($this)."', 'ajax_process', document.getElementById(id).value + workListElementCurrent, workListPopulate);
		}

		function workListPopulate ( value ) {
			document.getElementById(workListElementCurrent).style.backgroundColor = value;
			document.getElementById(workListElementCurrent).innerHTML = '&nbsp;';
			workListElementCurrent = '';
			workListElementActive = 0;
		}

		</script>
		";

		// Get list of providers
		$p = explode(',', $providers);

		$buffer .= "<table border=\"0\" cellspacing=\"5\"><tr>\n";
		foreach ($p AS $v) {
			$buffer .= "<td valign=\"top\">".$this->generate_worklist( $v )."</td>\n";
		}
		$buffer .= "</tr></table>\n";

		return array (
			__("Work Lists"),
			$buffer
		);
	} // end method notify

	function ajax_process ( $v ) {
		if (!is_object($GLOBALS['this_user'])) {
			$GLOBALS['this_user'] = CreateObject('FreeMED.User');
		}

		list( $status, $appt ) = explode('x', $v);

		// Keep funky things from happening
		if ($status+0 == 0) { return ''; }

		// Actual change
		$a = freemed::get_link_rec($appt, 'scheduler');
		$GLOBALS['sql']->query(
			$GLOBALS['sql']->insert_query(
				'scheduler_status',
				array (
					'csstamp' => SQL__NOW,
					'cspatient' => $a['calpatient'],
					'csappt' => $appt,
					'csstatus' => $status,
					'csuser' => $GLOBALS['this_user']->user_number
				)
			)
		);

		// Return status color properly
		$q = $GLOBALS['sql']->query("SELECT * FROM schedulerstatustype WHERE id='".addslashes($status)."'");
		$r = $GLOBALS['sql']->fetch_array($q);
		return $r['scolor'];
	}

	// ----- Internal methods ------------------------------------------------------

	function generate_worklist ( $provider ) {
		$date = date('Y-m-d');
		$date = '2006-05-01'; // FIXME FIXME FIXME

		// Load lookup table
		$q = $GLOBALS['sql']->query( "SELECT * FROM schedulerstatustype" );
		while ($r = $GLOBALS['sql']->fetch_array( $q )) {
			$lookup[$r['id']] = $r['scolor'];
		}
		unset ($q); unset ($r);

		$pobj = CreateObject( '_FreeMED.Physician', $provider );
		LoadObjectDependency( '_FreeMED.Scheduler' );

		$buffer = '<table border="0" cellpadding="2" cellspacing="0" bgcolor="#aaaaff">'.
			'<tr><td colspan="3"><b><a href="physician_day_view.php?physician='.$provider.'">'.$pobj->fullName().'</a></b></td></tr>'.
			'<tr><th>'.__("Name").'</th>'.
			'<th>'.__("Time").'</th>'.
			'<th>'.__("Status").'</th></tr>';

		$query = "SELECT s.id AS id, p.id AS s_patient_id, CONCAT(p.ptlname,', ', p.ptfname) AS s_patient, s.calhour AS s_hour, s.calminute AS s_minute, s.calduration AS s_duration FROM scheduler s LEFT OUTER JOIN patient p ON p.id=s.calpatient WHERE s.caldateof='".addslashes($date)."' AND s.calphysician='".addslashes($provider)."' ORDER BY s_hour, s_minute";
		$q = $GLOBALS['sql']->query( $query );
		while ( $r = $GLOBALS['sql']->fetch_array( $q ) ) {
			$current_status = module_function( 'schedulerpatientstatus', 'getPatientStatus', array( $r['s_patient_id'], $r['id'] ) );
			
			$buffer .= "<tr>\n".
				"<td><a href=\"manage.php?id=${r['s_patient_id']}\">".prepare($r['s_patient'])."</a></td>\n".
				"<td>".Scheduler::display_time($r['s_hour'], $r['s_minute'])."</td>\n".
				"<td ".( $current_status ? "bgcolor=\"${lookup[$current_status]}\"" : "" )." id=\"x${r['id']}\" onClick=\"workListClick('x${r['id']}'); return true;\">&nbsp;</td>\n".
				"</tr>\n";
		} // end fetch_array

		// Footer
		$buffer .= "</table>\n";

		return $buffer;
	} // end method generate_worklist

} // end class WorkListsModule

register_module('WorkListsModule');

?>
