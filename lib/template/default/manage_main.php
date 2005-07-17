<?php
	// $Id$
	// $Author$
	// note: template for patient management functions
	// lic : GPL, v2

//----- Pull configuration for this user
if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

//----- Make sure all module functions are loaded
LoadObjectDependency('PHP.module');

//----- Extract all configuration data
if (is_array($this_user->manage_config)) extract($this_user->manage_config);

//----- Load scheduler functions
if (!is_object($scheduler)) $scheduler = CreateObject('FreeMED.Scheduler');

//----- Check for a *reasonable* refresh time and summary items
if ($automatic_refresh_time > 14) {
	$GLOBALS['__freemed']['automatic_refresh'] = $automatic_refresh_time;
}
if ($num_summary_items < 1) $num_summary_items = 5;

//----- Display patient information box...
$display_buffer .= freemed::patient_box($this_patient);

//----- Create module list
if (!is_object($module_list)) { $module_list = freemed::module_cache(); }


/*
 ********************* DEPRECIATED ******************************
 FIXME: Remove this as soon as photo id is migrated to its own module

//----- Suck in management panels
//-- Static first...
foreach ($static_components AS $garbage => $__component) {
	if (is_array($__component)) {
		$component = $__component;
	} else {
		$component = array (
			'static' => $__component,
			'order'  => '5'
		);
	}
	if (!$already_set[$component['static']]) {
	switch ($component['static']) {
		case "photo_id":
		// If there is a file with that name, show it, else box
		//print "filename = ".freemed::image_filename($id, 'identification', 'djvu')."<br>";
		$static_name = __("Photo ID");
		if (file_exists(freemed::image_filename(
				$id,
				'identification',
				'djvu'))) {
			$modules[__("Photo ID")] = "photo_id";
			$panel[__("Photo ID")] = "
			<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\"
			 CELLPADDING=\"3\" CLASS=\"thinbox\"
			<tr><TD VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			<A HREF=\"photo_id.php?patient=".urlencode($id)."&".
			"return=manage\"
			 >".__("Update")."</A> |
			<A HREF=\"photo_id.php?patient=".urlencode($id)."&".
			"action=remove&return=manage\"
			 >".__("Remove")."</A>
			</TD></tr>
			<tr><TD ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
			<DIV ALIGN=\"CENTER\">
			<A HREF=\"patient_image_handler.php?".
			"patient=".urlencode($patient)."&".
			"id=identification\" TARGET=\"new\"
			onMouseOver=\"window.status='".__("Enlarge image")."'; return true;\"
			onMouseOut=\"window.status=''; return true;\"
			><EMBED SRC=\"patient_image_handler.php?".
			"patient=".urlencode($id)."&id=identification\"
			 BORDER=\"0\" ALT=\"Photographic Identification\"
			 WIDTH=\"200\" HEIGHT=\"150\"
			 TYPE=\"image/x.djvu\"
			 PLUGINSPAGE=\"".COMPLETE_URL."support/\"
			 ></EMBED></A>
			</DIV>
			</TD></tr>
			</table>
			";

		} else {
			$panel[__("Photo ID")] = "
			<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\"
			 CELLPADDING=\"3\" CLASS=\"thinbox\"
			<tr><TD VALIGN=\"MIDDLE\" ALIGN=\"CENTER\"
			 CLASS=\"menubar_items\">
			<A HREF=\"photo_id.php?patient=".urlencode($id)."\"
			 >".__("Update")."</A>
			</TD></tr>
			<tr><TD ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
			<DIV ALIGN=\"CENTER\">
			".__("No photographic identification on file.")."
			<BR><BR>
			</DIV>
			</TD></tr>
			</table>
			";
		}
		break; // end photo_id

		default: // Everything else.... do nothing (ERROR)
		break; // end default
	} // end component switch
	} // end checking for already set

	$already_set[$component['static']] = true;
	$ms[$static_name] = $component;
} // end static components
*/

//-- ... then modular
foreach ($components AS $garbage => $__component) {
	// End checking for component
	if (is_array($__component)) {
		$component = $__component;
	} else {
		$component = array (
			'module' => $__component,
			'order'  => '999999' // make it last
		);
	}
	if ($module_list->check_for($component['module']) and (!$already_set[$component['module']])) {
		// Wrap this whole thing in ACL check
		if (freemed::module_check_acl($component['module'])) {

		// Execute proper portion and add to panel
		$modules[__($module_list->get_module_name($component['module']))] =
			$component['module'];
		$panel[__($module_list->get_module_name($component['module']))] .= "
			<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\"
			 CELLPADDING=\"3\" CLASS=\"thinbox\"
			<tr><td VALIGN=\"MIDDLE\" ALIGN=\"CENTER\"
			 CLASS=\"menubar_items\">".
			module_function($component['module'], "summary_bar", array ( $id )).
			"</td></tr>
			<tr><td ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
			".module_function($component['module'], "summary",
				array (
					$id, // patient ID
					$num_summary_items // items per panel
				)
			)."</td></tr></table>
		";

		$already_set[$component['module']] = true;
		$ms[__($module_list->get_module_name($component['module']))] = $component;

		} // end wrapper ACL check
	} else {
		// Don't do anything if it doesn't exist
	} // end checking for component existing
} // end static components

//----- Determine column requirements
if ($display_columns < 1) $display_columns = 1;
if (count($panel) > 0) {
	$column_cutoff = ceil ( count($panel) / $display_columns );
} // check for ability to display panels

//----- Display tables

function __sort_panels ($a, $b) {
	if ($a['order'] == $b['order']) {
	       	$c_a = isset($a['module']) ? $a['module'] : $a['static'];
	       	$c_b = isset($b['module']) ? $b['module'] : $b['static'];
		return ($c_a < $c_b) ? -1 : 1;
	}
	return ($a['order'] < $b['order']) ? -1 : 1;
}

if (count($ms) > 0) {
	// Sort by panel names
	uasort($ms, '__sort_panels');

	// Table header
	$display_buffer .= "
	<table WIDTH=\"100%\" CELLSPACING=\"3\" CELLPADDING=\"0\" BORDER=\"0\">
	<tr VALIGN=MIDDLE ALIGN=CENTER>
	";

	$column = 1; reset ($ms);
	foreach ($ms AS $k => $_v) {
		if (!empty($k)) {
		$v = $panel[$k];
		
		// Check to see if we're on a new row yet
		if ($column > $display_columns) {
			$column = 1;

			// Display footer and new header
			$display_buffer .= "
			</tr><tr VALIGN=MIDDLE ALIGN=CENTER>
			";
		}

		// Add panel
		$myk = str_replace(" ", "_", $k);
		$display_buffer .= "
		<TD VALIGN=\"TOP\" ALIGN=\"CENTER\" WIDTH=\"".
			( (int) (100 / $display_columns) )."%\">
		<table WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\"
		 CELLPADDING=\"0\">
		<tr><TD CLASS=\"reverse\" VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
		<B>".prepare($k)."</B>
		</TD><TD CLASS=\"reverse\" VALIGN=\"MIDDLE\" ALIGN=\"RIGHT\">
		".( ($ms[$k][order] > 1) ? "
		<A HREF=\"manage.php?id=".urlencode($id)."&".
		"action=moveup&module=".urlencode($modules[$k])."\"
		onMouseOver=\"document.images.".$myk."_up.src='lib/template/default/img/move_up_pressed.png'; return true;\"
		onMouseOut=\"document.images.".$myk."_up.src='lib/template/default/img/move_up.png'; return true;\"
		><IMG NAME=\"".$myk."_up\"
		SRC=\"lib/template/default/img/move_up.png\"
		BORDER=\"0\" ALT=\"X\"></A>" : "" ).
		( ($ms[$k][order] < 99999999) ? "
		<A HREF=\"manage.php?id=".urlencode($id)."&".
		"action=movedown&module=".urlencode($modules[$k])."\"
		onMouseOver=\"document.images.".$myk."_down.src='lib/template/default/img/move_down_pressed.png'; return true;\"
		onMouseOut=\"document.images.".$myk."_down.src='lib/template/default/img/move_down.png'; return true;\"
		><IMG NAME=\"".$myk."_down\"
		SRC=\"lib/template/default/img/move_down.png\"
		BORDER=\"0\" ALT=\"X\"></A>" : "" )."
		<A HREF=\"manage.php?id=".urlencode($id)."&".
		"action=remove&module=".urlencode($modules[$k])."\"
		onMouseOver=\"document.images.".$myk."_close.src='lib/template/default/img/close_x_pressed.png'; return true;\"
		onMouseOut=\"document.images.".$myk."_close.src='lib/template/default/img/close_x.png'; return true;\"
		><IMG NAME=\"".$myk."_close\"
		SRC=\"lib/template/default/img/close_x.png\"
		BORDER=\"0\" ALT=\"X\"></A></TD></tr>
		<tr><TD VALIGN=\"MIDDLE\" ALIGN=\"CENTER\" COLSPAN=\"2\">
		<CENTER>$v</CENTER>
		</TD></tr></table>
		</TD>
		";

		// Move to the next column
		$column += 1;
		} // !empty key
	} // end looping

	// Fill up empty space
	if ($column < $display_columns) {
		for ($i=1; $i<=($display_columns-$column); $i++)
			$display_buffer .= "<TD>&nbsp;</TD>\n";
	} // end filling up empty space

	// Table footer
	$display_buffer .= "
	</tr></table>
	";

} else {
	// Display warning if no panels
	$display_buffer .= "
	<p/>
	<div align=\"CENTER\">
	<b>".__("Please configure panels through the \"Configure\" option of the patients menu.")."</b>
	</div>
	<p/>
	";
} // end checking for *any* panels

// Add configure to the menu bar
if ($action != "config") {
	$menu_bar[__("Configure")] = "manage.php?id=$id&action=config";
}


//----- Add to menu bar
if (!is_object($module_list)) {
	$module_list = CreateObject(
		'PHP.module_list',
		PACKAGENAME,
		array(
			'cache_file' => 'data/cache/modules'
		)
	);
}
// Form template for menubar
$menu_bar = array_merge (
	$menu_bar,
	$module_list->generate_array(
		"Electronic Medical Record",
		0,
		"#name#",
		"module_loader.php?module=#class#&patient=$id"
	)
);

?>
