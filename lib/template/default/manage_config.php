<?php
 // $Id$
 // $Author$
 // note: configuration for management functions
 // lic : GPL, v2

//----- Load the user object
if (!is_object($this_user)) $this_user = CreateObject('FreeMED.User');

//----- Override title for this page
$page_title = _("Management Configuration");

//----- Add menu help item for this
$menu_bar[_("Configuration Help")] = help_url("manage.php", "configure");

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

//----- Define list of configuration vars
$config_vars = array (
	"automatic_refresh_time",
	"display_columns",
	"num_summary_items",
	"static_components",
	"modular_components"
);

//----- Basic configuration for management
$book->add_page("General",
	array (
		"automatic_refresh_time",
		"display_columns",
		"num_summary_items"
	),
	html_form::form_table(array(
		_("Automatic Refresh Time") =>
		html_form::select_widget("automatic_refresh_time",
			array (
				_("NONE") => "0",
				"1m" => "60",
				"2m" => "120",
				"5m" => "300",
				"15m" => "900",
				"30m" => "1800",
				"60m" => "3600"
			)
		),

		_("Columns in Display") =>
		html_form::select_widget("display_columns",
			array (
				"1" => "1",
				"2" => "2",
				"3" => "3"
			)
		),

		_("Number of Summary Items") =>
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

//----- Static (non-modular) configuration
if (!defined($static_components)) $static_components[] = NULL;
$book->add_page("Static Components",
	array ( "static_components" ),
	"<CENTER>\n".
	html_form::form_table(array(
		_("Appointments") =>
		html_form::checkbox_widget(
			"static_components", "appointments", " "
		),

		_("Custom Reports") =>
		html_form::checkbox_widget(
			"static_components", "custom_reports", " "
		),

		_("Medical Information") =>
		html_form::checkbox_widget(
			"static_components", "medical_information", " "
		),

		_("Messages") =>
		html_form::checkbox_widget(
			"static_components", "messages", " "
		),

		_("Patient Information") =>
		html_form::checkbox_widget(
			"static_components", "patient_information", " "
		),

		_("Photographic Identification") =>
		html_form::checkbox_widget(
			"static_components", "photo_id", " "
		)

	)).
	"</CENTER>\n"
);

//----- Create module list for modular configuration
$module_list = CreateObject('PHP.module_list', PACKAGENAME, ".emr.module.php");

//----- This is *so* jimmy rigged... -----

// Make sure that whatever it is, it's an array
if (!is_array($modular_components))
	$modular_components = array($modular_components);

// Create basic template for split
$module_template = "#name#:#class#/";
// Form the actual hash with the template
$class_hash = $module_list->generate_list ( "Electronic Medical Record",
	0, $module_template );
// Break apart key/value pairs into array
$class_array = explode( "/", $class_hash );
// Loop through array to separate key and val
foreach($class_array AS $k => $class_pair) {
	if (!empty($class_pair)) {
		// Break it
		list ($key, $val) = explode (":", $class_pair);
		// Add it
		//$classes["$key"] = $val; // this would be for anything else
		$modules_to_choose[_("$key")] =
			html_form::checkbox_widget (
				"modular_components", $val, " "
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
	$display_buffer .= "<CENTER>\n";
	$display_buffer .= $book->display();
	$display_buffer .= "</CENTER>\n";
} else { // checking if book is done
	// This is *really* fun. Make the appropriate hashes...
	foreach ($config_vars AS $__lotta_garbage_var__ => $opt) {
		if (is_array(${$opt})) {
			// Start off array hash and add name
			$final_hash .= "/".$opt."=";
			foreach (${$opt} AS $gargage_too => $v) {
				// Add actual array item and separator
				$final_hash .= ":".$v;
			} // inner loop
		} else { // if it is an array
			// Create scalar hash portion
			$final_hash .= "/".$opt."=".${$opt};
		} // end if is_array opt
	} // end looping through config_vars

	// Form SQL query
	$query = $sql->update_query ( 
		"user",
		array ( "usermanageopt" => $final_hash ),
		array ( "id" => $this_user->user_number )
	);

	// Display results of query
	if ($result = $sql->query ($query)) {
		// Set automatic refresh on success...
		$refresh = "manage.php?action=menu&id=$id";
		// Display the page just in case...
		$display_buffer .= _("Updated configuration");
		$display_buffer .= "
			<P>
			<CENTER>
			<A HREF=\"manage.php?action=menu&id=$id\"
			>"._("Manage Patient")."</A>
			</CENTER>
		";
	} else {
		$display_buffer .= _("ERROR")." (query=\"".prepare($query)."\"";
		template_display();
	}
} // end checking if book is done

?>
