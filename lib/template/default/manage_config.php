<?php
 // $Id$
 // $Author$
 // note: configuration for management functions
 // lic : GPL, v2

//----- Load the user object
if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

//----- Override title for this page
$page_title = __("Management Configuration");

//----- Add menu help item for this
$menu_bar[__("Configuration Help")] = help_url("manage.php", "configure");

// Special widgets for priority, etc
function my_checkbox_widget ( $varname, $value, $actual ) {
	return "<input TYPE=\"CHECKBOX\" NAME=\"".$varname."\" ".
		"VALUE=\"".$value."\" ".
		( ( $value == $actual ) ? "checked=\"CHECKED\" " : "" ).
		"/>\n";
}
function my_select_widget ( $varname, $values, $actual ) {
	$buffer = "<select name=\"".$varname."\">\n";
	foreach ($values AS $k => $v) {
		if ( (is_integer($k) and ($values[($k+0)] == $v)) or empty($k) ) { $k = $v; }
		$buffer .= "<option value=\"".prepare($v)."\" ".
			( ($v == $actual) ? "selected=\"selected\" " : "" ).
			">".prepare($k)."</option>\n";
	}
	$buffer .= "</select>\n";
	return $buffer;
}

//----- Create configuration notebook
$book = CreateObject('PHP.notebook',
	array(
		"action", // so that we remain in configure
		"id" // to keep manage remembering the patient
	),
	NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR
);

//----- Pull out proper pieces
if (!$book->been_here()) {
	// Check if there are any config variables
	if (count($this_user->manage_config) > 0) {
		// Extract the variables into the global domain
		extract($this_user->manage_config);
	} // end checking for config
} // end pulling out old values
//print "<pre>"; print_r($this_user->manage_config); die("</pre>");

//----- Define list of configuration vars
$config_vars = array (
	"automatic_refresh_time",
	"display_columns",
	"num_summary_items",
	"static_components",
	"modular_components"
);

//print_r($_REQUEST); die();

//----- Basic configuration for management
$book->add_page("General",
	array (
		"automatic_refresh_time",
		"display_columns",
		"num_summary_items"
	),
	html_form::form_table(array(
		__("Automatic Refresh Time") =>
		html_form::select_widget("automatic_refresh_time",
			array (
				__("NONE") => "0",
				"1m" => "60",
				"2m" => "120",
				"5m" => "300",
				"15m" => "900",
				"30m" => "1800",
				"60m" => "3600"
			)
		),

		__("Columns in Display") =>
		html_form::select_widget("display_columns",
			array (
				"1" => "1",
				"2" => "2",
				"3" => "3"
			)
		),

		__("Number of Summary Items") =>
		html_form::select_widget("num_summary_items",
			array (
				"1"  => "1",
				"2"  => "2",
				"3"  => "3",
				"4"  => "4",
				"5"  => "5",
				"6"  => "6",
				"7"  => "7",
				"8"  => "8",
				"9"  => "9",
				"10" => "10"
			)
		)
	))
);

// Defaults for static components
$_scs = array (
	'appointments',
	'custom_reports',
	'medical_information',
	'messages',
	'patient_information',
	'photo_id'
);
foreach ($_scs AS $this_component) {
	if (!isset($static_components[$this_component][order])) {
		$static_components[$this_component][order] = 5;
	}
}

//----- Static (non-modular) configuration
if (!isset($static_components)) $static_components = array();
$book->add_page("Static Components",
	array ( "static_components" ),
	"<center>\n".
	html_form::form_table(array(
		__("Appointments") =>
		my_checkbox_widget(
			"static_components[appointments][static]", "appointments", $static_components[appointments]['static']
		).
		my_select_widget (
			"static_components[appointments][order]",
			array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			$static_components[appointments][order]
		),

		__("Custom Reports") =>
		my_checkbox_widget(
			"static_components[custom_reports][static]", "custom_reports", $static_components[custom_reports]['static']
		).
		my_select_widget (
			"static_components[custom_reports][order]",
			array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			$static_components[custom_reports][order]
		),

		__("Medical Information") =>
		my_checkbox_widget(
			"static_components[medical_information][static]", "medical_information", $static_components[medical_information]['static']
		).
		my_select_widget (
			"static_components[medical_information][order]",
			array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			$static_components[medical_information][order]
		),

		__("Messages") =>
		my_checkbox_widget(
			"static_components[messages][static]", "messages", $static_components[messages]['static']
		).
		my_select_widget (
			"static_components[messages][order]",
			array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			$static_components[messages][order]
		),

		__("Patient Information") =>
		my_checkbox_widget(
			"static_components[patient_information][static]", "patient_information", $static_components[patient_information]['static']
		).
		my_select_widget (
			"static_components[patient_information][order]",
			array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			$static_components[patient_information][order]
		),

		__("Photographic Identification") =>
		my_checkbox_widget(
			"static_components[photo_id][static]", "photo_id", $static_components[photo_id]['static']
		).
		my_select_widget (
			"static_components[photo_id][order]",
			array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9'
			),
			$static_components[photo_id][order]
		)

	)).
	"</center>\n"
);

//----- Create module list for modular configuration
$module_list = CreateObject(
	'PHP.module_list',
	PACKAGENAME,
	array(
		'cache_file' => 'data/cache/modules'	
	)
);

//----- This is *so* jimmy rigged... -----

// Make sure that whatever it is, it's an array
if (!is_array($modular_components))
	$modular_components = array($modular_components);

// Create basic template for split
$module_template = "#name#:#class#/";
// Form the actual hash with the template
$class_hash = $module_list->generate_list ( "Electronic Medical Record",
	0, $module_template );

//print "class_hash = $class_hash<BR>\n";

// Break apart key/value pairs into array
$class_array = explode( "/", $class_hash );
// Loop through array to separate key and val
foreach($class_array AS $k => $class_pair) {
	if (!empty($class_pair)) {
		// Break it
		list ($key, $val) = explode (":", $class_pair);
		// Add it
		//$classes["$key"] = $val; // this would be for anything else
		if (!isset($modular_components[$val][order])) {
			$modular_components[$val][order] = 5;
		}
		$modules_to_choose[__($key)] =
			my_checkbox_widget (
				"modular_components[$val][module]", $val, $modular_components[$val][module]
			).
			my_select_widget (
				"modular_components[$val][order]",
				array (
					'1',
					'2',
					'3',
					'4',
					'5',
					'6',
					'7',
					'8',
					'9'
				),
				$modular_components[$val][order]
			);
	} // end checking for empties
} // end while loop

//----- Module configuration
$book->add_page("Modular Components",
	array ( "modular_components" ),
	html_form::form_table($modules_to_choose)
);

//----- Handle cancel
if ($book->is_cancelled()) {
	Header("Location: manage.php?action=menu&id=".urlencode($id));
	die("");
}

//----- Either display the book or finish up
if (!$book->is_done()) {
	$display_buffer .= "<center>\n";
	$display_buffer .= $book->display();
	$display_buffer .= "</center>\n";
} else { // checking if book is done
	// Grab the old options, so we don't lose them
	$old = unserialize($this_user->local_record['usermanageopt']);
	foreach ($old as $k => $v) {
		switch ($k) {
			case 'modular_components':
			case 'static_components':
				break;

			default:
				$mc[$k] = $v;
		}
	}
	// This is *really* fun. Make the appropriate hashes...
	foreach ($config_vars AS $opt) {
		switch ($opt) {
			case 'modular_components':
			unset($a);
			foreach (${$opt} AS $v) { 
				if ($v[module]) {
					//print "<b>"; print_r($v); print"</b><br/>\n";
					$a[] = $v;
				}
			}
			$mc[$opt] = $a;
			break;
			
			case 'static_components':
			unset($a);
			foreach (${$opt} AS $v) { 
				if ($v['static']) {
					//print "<b>"; print_r($v); print"</b><br/>\n";
					$a[] = $v;
				}
			}
			$mc[$opt] = $a;
			break;

			default:
			$mc[$opt] = ${$opt};
			break;
		}
		//print $opt." ";
		//print_r($mc[$opt]); print "<br/>\n";
		$mc[$opt] = ${$opt};
	} // end looping through config_vars
	
	// Form SQL query
	$query = $sql->update_query ( 
		"user",
		array ( "usermanageopt" => serialize($mc) ),
		array ( "id" => $this_user->user_number )
	);

	// Display results of query
	if ($result = $sql->query ($query)) {
		// Set automatic refresh on success...
		$refresh = "manage.php?action=menu&id=$id";
		// Display the page just in case...
		$display_buffer .= __("Updated configuration");
		$display_buffer .= "
			<p/>
			<div align=\"CENTER\">
			<a HREF=\"manage.php?action=menu&id=$id\"
			 class=\"button\">".__("Manage Patient")."</a>
			</div>
		";
	} else {
		$display_buffer .= __("ERROR")." (query=\"".prepare($query)."\"";
		template_display();
	}
} // end checking if book is done

?>
