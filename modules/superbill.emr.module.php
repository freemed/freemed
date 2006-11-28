<?php
	// $Id$
	// $Author$

LoadObjectDependency('_FreeMED.EMRModule');

class SuperBill extends EMRModule {

	var $MODULE_NAME = "Superbills";
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.8.3';

	var $record_name   = "Superbill";
	var $table_name    = "superbill";
	var $patient_field = "patient";
	var $widget_hash   = "##pnotesdt## ##pnotesdescrip##";

	var $print_template = 'superbill';

	function SuperBill () {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }

		// Table description
		$this->table_definition = array (
			'dateofservice' => SQL__DATE,
			'enteredby' => SQL__INT_UNSIGNED(0),
			'patient' => SQL__INT_UNSIGNED(0),
			'procs' => SQL__BLOB,
			'dx' => SQL__BLOB,
			'note' => SQL__VARCHAR(250),
			'reviewed' => SQL__INT_UNSIGNED(0),
			'id' => SQL__SERIAL
		);

		$this->variables = array (		
			'dateofservice' => date('Y-m-d'),
			'enteredby' => $this_user->user_number,
			'patient',
			'note',
			'procs' => join ( ',', $_REQUEST['procs'] ),
			'dx' => join ( ',', $_REQUEST['dx'] ),
			'reviewed' => 0,
		);

		// Define variables for EMR summary
		$this->summary_vars = array (
			__("Date") => "dateofservice",
			__("Note") => "note",
			__("Reviewed") => "_reviewed"
		);
		$this->summary_options |= SUMMARY_VIEW;
		$this->summary_query = array(
			"CASE reviewed WHEN 0 THEN 'not reviewed' ELSE 'reviewed' END AS _reviewed"
		);

		// Set associations
		$this->acl = array ( 'emr', 'bill' );
		$this->_SetHandler( 'MainMenu', 'MainMenuNotify' );

		// Call parent constructor
		$this->EMRModule();
	} // end constructor SuperBill

	function form_table ( ) {
		return array (
			__("Procedures") => "
			<script language=\"javascript\">
			function addPx ( ) {
				container = document.getElementById('px_container');
				id = document.getElementById('p').value;
				text = document.getElementById('p_text').value;
				if ( id != '' && id > 0 ) {
					var tempElement;
					try {
						tempElement = document.getElementById('procs_div_'+id);
						if (!tempElement.innerHTML) {

							tempElement = document.createElement('div');
							tempElement.id = 'procs_div_'+id;
						}
					} catch (err) {
						tempElement = document.createElement('div');
						tempElement.id = 'procs_div_'+id;
					}
					tempElement.innerHTML = '<input type=\"hidden\" name=\"procs[]\" value=\"'+id+'\" /> '+
						text +
						'[<a onClick=\"removePx('+id+')\">X</a>]';
					container.appendChild( tempElement );
				}
			}
			function removePx ( id ) {
				try {
					document.getElementById('procs_div_'+id).innerHTML = '';
				} catch (err) { }
			}
			</script>
			".module_function('cptmaintenance', 'widget', array ('p'))."
			<input type=\"button\" class=\"button\" value=\"Add Procedure\" onClick=\"addPx(); return true;\" />
			<br/>
			<div id=\"px_container\">
			</div>
			",
			__("Diagnoses") => "
			<script language=\"javascript\">
			function addDx ( ) {
				container = document.getElementById('dx_container');
				id = document.getElementById('d').value;
				text = document.getElementById('d_text').value;
				if ( id != '' && id > 0 ) {
					var tempElement;
					try {
						tempElement = document.getElementById('dx_div_'+id);
						if (!tempElement.innerHTML) {

							tempElement = document.createElement('div');
							tempElement.id = 'dx_div_'+id;
						}
					} catch (err) {
						tempElement = document.createElement('div');
						tempElement.id = 'dx_div_'+id;
					}
					tempElement.innerHTML = '<input type=\"hidden\" name=\"dx[]\" value=\"'+id+'\" /> '+
						text +
						'[<a onClick=\"removeDx('+id+')\">X</a>]';
					container.appendChild( tempElement );
				}
			}
			function removeDx ( id ) {
				try {
					document.getElementById('dx_div_'+id).innerHTML = '';
				} catch (err) { }
			}
			</script>
			".module_function('icdmaintenance', 'widget', array ('d'))."
			<input type=\"button\" class=\"button\" value=\"Add Dx\" onClick=\"addDx(); return true;\" />
			<br/>
			<div id=\"dx_container\">
			</div>
			",
			__("Note") => html_form::text_widget('note', 250)
		);
	} // end method form_table

	function modform ( ) { $this->display(); }

	function mod ( ) {
		global $this_user;
		if (!is_object($this_user)) { $this_user = CreateObject('_FreeMED.User'); }
		$id = $_REQUEST['id'] + 0;
		$GLOBALS['sql']->query($GLOBALS['sql']->update_query(
			$this->table_name,
			array (
				'reviewed' => $this_user->user_number
			), array ( 'id' => $id )
		));
                // Check for return to management screen
		if ($GLOBALS['return'] == 'manage') {
			global $refresh, $patient;
			$refresh = "manage.php?id=".urlencode($patient);
			Header('Location: '.$refresh);
			die();
		}
	}

	function view ($condition = false) {
		global $display_buffer;
		foreach ($GLOBALS AS $k => $v) { global ${$k}; }

		// Check for "view" action (actually display)
		if ($_REQUEST['action'] == "view") {
			$this->display();
			return NULL;
		}

		$query = "SELECT * FROM ".$this->table_name." ".
			"WHERE (".$this->patient_field."='".addslashes($_REQUEST['patient'])."') ".
			freemed::itemlist_conditions(false)." ".
			( $condition ? 'AND '.$condition : '' )." ".
			"ORDER BY id DESC";
		$result = $GLOBALS['sql']->query ($query);

		$display_buffer .= freemed_display_itemlist(
			$result,
			$this->page_name,
			array (
				__("Date")        => "dateofservice",
				__("Note")        => "note"
			), // array
			array ( "", "" ),
			NULL, NULL, NULL,
			ITEMLIST_MOD | ITEMLIST_VIEW 
		);
		$display_buffer .= "\n<p/>\n";
	} // end method view

	function display ( ) {
		global $display_buffer;

		$display_buffer .= "<center><table><tr><td align=\"left\">";

		$rec = freemed::get_link_rec( $_REQUEST['id'], $this->table_name );

		$display_buffer .= "<b>".__("Date").":</b> ".$rec['dateofservice']."<br/>\n";
		$display_buffer .= "<b>".__("Note").":</b> ".prepare($rec['note'])."<br/>\n";
		$display_buffer .= "<ul><b>".__("Procedures").":</b>\n";
		foreach ( explode(',', $rec['procs']) AS $p ) {
			$display_buffer .= "<li>".module_function( 'cptmaintenance', 'to_text', array ( $p ) )."</li>\n";
		}
		$display_buffer .= "</ul>\n";
		$display_buffer .= "<ul><b>".__("Diagnoses").":</b>\n";
		foreach ( explode(',', $rec['dx']) AS $d ) {
			$display_buffer .= "<li>".module_function( 'icdmaintenance', 'to_text', array ( $d ) )."</li>\n";
		}
		$display_buffer .= "</ul>\n";

		// form
		$display_buffer .= "</td></tr><tr><td align=\"center\">\n";
		$display_buffer .= "<a href=\"".$this->page_name."?action=mod&id=".urlencode($_REQUEST['id']+0)."&patient=".urlencode($rec['patient']+0)."&module=".get_class($this)."\" class=\"button\">Mark as Reviewed</a>\n";

		$display_buffer .= "</td></tr></table></center>";
	} // end method display

	function MainMenuNotify ( ) {
		$cresult = $GLOBALS['sql']->query("SELECT COUNT(*) AS my_count FROM ".$this->table_name." WHERE reviewed='0' GROUP BY patient");
		if ($cresult['my_count'] == 0) {
			return array (
				__("Superbill"),
				__("There are currently no superbills waiting for entry.")
			);
		}

		// Fetch the first five
		$result = $GLOBALS['sql']->query("SELECT *,s.id AS actual_id FROM ".$this->table_name." AS s LEFT OUTER JOIN patient p ON p.id=s.patient WHERE reviewed='0' GROUP BY s.patient LIMIT 10");
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
			unset ($e);
			$entry[] = $r['ptlname'].', '.$r['ptfname'].' : <a href="module_loader.php?module='.get_class($this).'&action=modform&id='.$r['actual_id'].'">'.$r['dateofservice']."</a>";
		}

		// Display ...
		return array (
			__("Superbill"),
			sprintf(__("There are currently %d superbills waiting for entry."), $cresult['my_count'])."<br/>\n".
			"<table>".
			"<tr><td>".join('</td></tr><tr><td>', $entry)."</td></tr>".
			"</table>"
		);
	} // end method MainMenuNotify

} // end class SuperBill

register_module ("SuperBill");

?>
