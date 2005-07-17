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

function __sort_modules ( $a, $b ) {
	if ($a['order'] == $b['order']) { return 0; }
	return ( $a['order'] < $b['order'] ? -1 : 1 );
}

// Special widgets for priority, etc
function my_checkbox_widget ( $varname, $value, $actual, $div ) {
	return "<input TYPE=\"CHECKBOX\" NAME=\"".$varname."\" ".
		"onClick=\"move_div(".
			"(this.checked==1 ? 'enabled_wrapper' : 'disabled_wrapper'), ".
			"'".$div."'); return true;\" ".
		"VALUE=\"".$value."\" ".
		( ( $value == $actual ) ? "checked=\"CHECKED\" " : "" ).
		"/>\n";
} // end function my_checkbox_widget
function my_hidden_widget ( $varname, $value ) {
	return "<input TYPE=\"HIDDEN\" NAME=\"".$varname."\" ".
		"VALUE=\"".prepare($value)."\"/>\n";
} // end function my_hidden_widget
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
} // end function my_select_widget
function my_module_table ( $enabled, $disabled ) {
	// Create javascipt
	$buffer .= "
	<script language=\"javascript\">

	function move_div(dest_name, node_name) {
		// Move actual element using DOM
		dest = document.getElementById(dest_name);
		node = document.getElementById(node_name);
		dest.appendChild(node);
		// Change order values in each div appropriately
		reorder_values('enabled_wrapper');
		reorder_values('disabled_wrapper');
	} // end function move_div

	function get_next_node(container, node_position) {
		for (i=node_position+1; i<=container.childNodes.length; i++) {
			if (container.childNodes[i].nodeName == 'DIV') { return i; }
		}
		// We fail here
		return container.childNodes.length;
	} // end method get_next_node
	
	function get_previous_node(container, node_position) {
		for (i=node_position-1; i>=0; i--) {
			if (container.childNodes[i].nodeName == 'DIV') { return i; }
		}
		// We fail here
		return 0;
	} // end method get_previous_node
	
	function change_position(container, node_name, change) {
		// Get position
		position = -1;
		for (i=0; i<container.childNodes.length; i++) {
			if (container.childNodes[i].id == node_name) {
				position = i;
			}
		}
		if (position == -1) { alert('This should never happen, position = -1!'); return false; }

		// Check for out of bounds
		if (position + change > container.childNodes.length) { return false; }
		if (position + change < 1) { return false; }
		
		// Swap the nodes ...
		node = container.childNodes[position];
		if (change == -1) {
			// Move up
			container.insertBefore(container.childNodes[position], container.childNodes[get_previous_node(container, position)]);
			//alert('move to position ' + get_previous_node(container, position) + ' from ' + position );
		} else {
			// Move down
			container.insertBefore(container.childNodes[get_next_node(container, position)], container.childNodes[position]);
		}

		// Make sure to reorder so we can submit the form
		reorder_values('enabled_wrapper');
		reorder_values('disabled_wrapper');
	} // end function change_position

	function reorder_values(node_name) {
		count = 0;
		var output = '';
		node = document.getElementById(node_name);
		for (i=0; i<node.childNodes.length; i++) {
			if (node.childNodes[i].nodeName == 'DIV') {
				for (j=0; j<node.childNodes[i].childNodes.length; j++) {
					if (node.childNodes[i].childNodes[j].nodeName == 'INPUT') {
						if (node.childNodes[i].childNodes[j].id.indexOf('_order') > -1) {
							count++;
							node.childNodes[i].childNodes[j].value = count;
							output = output + 'node ' + node.childNodes[i].childNodes[j].id + ' = ' + count + '\\n';
						}
					}
				}			
			}
		}
		//alert (output);
	} // end function reorder_values

	function count_children(node_name) {
		count = 0;
		node = document.getElementById(node_name);
		for (i=0; i<node.childNodes.length; i++) {
			if (node.childNodes[i].nodeName == 'DIV') { count++; }
		}
		return count;
	} // end function count_children

	</script>
	<style type=\"text/css\">
		div.module_config_box {
			border: 1pt solid #000000;
			background: #ccccff;
			width: 300px;
			padding: 3px;
			margin: 2px;
		}
		#enabled_wrapper div { text-weight: bold; }
		#disabled_wrapper div { text-weight: normal; }
	</style>
	";

	// Sort both enabled and disabled for initial view
	uasort($enabled, '__sort_modules');
	uasort($disabled, '__sort_modules');
	
	// Create the enabled, first
	$buffer .= "<div id=\"enabled_wrapper\">\n";
	foreach ($enabled AS $k => $v) {
		// Create sanitized key
		$key = $v['name'];
		$order = $v['order'];
		
		// Create enabled
		$buffer .= "<div class=\"module_config_box\" id=\"".$k."\">\n";
		$buffer .= "<input type=\"hidden\" id=\"".$k."_order\" ".
			"name=\"components[".$k."][order]\" ".
			"value=\"".$order."\" />\n";
		$buffer .= my_checkbox_widget(
			'components['.$k.'][module]',
			1,
			1, // enabled
			$k
	       	);
		$buffer .= $key;
		$buffer .= "&nbsp;\n";
		$buffer .= "<img src=\"lib/template/default/img/move_up.png\" ".
			"onClick=\"change_position(document.getElementById('".$k."').parentNode, '".$k."', -1);\" border=\"0\" />\n";
		$buffer .= "<img src=\"lib/template/default/img/move_down.png\" ".
			"onClick=\"change_position(document.getElementById('".$k."').parentNode, '".$k."', 1);\" border=\"0\" />\n";
		$buffer .= "</div>\n";
	}
	$buffer .= "</div>\n";

	// Divider between enabled and disabled
	$buffer .= "/\\ <b>".
		__("Enabled").
	       "</b> &nbsp; &nbsp; &nbsp; &nbsp; \\/ <b>".
	       __("Disabled").
	       "</b>\n";

	// Create the enabled, first
	$buffer .= "<div id=\"disabled_wrapper\">\n";
	foreach ($disabled AS $k => $v) {
		// Create sanitized key
		$key = $v['name'];
		$order = $v['order'];
		
		// Create enabled
		$buffer .= "<div class=\"module_config_box\" id=\"".$k."\">\n";
		$buffer .= "<input type=\"hidden\" id=\"".$k."_order\" ".
			"name=\"components[".$k."][order]\" ".
			"value=\"".$order."\" />\n";
		$buffer .= my_checkbox_widget(
			'components['.$k.'][module]',
			1,
			0, // disabled
			$k
	       	);
		$buffer .= $key;
		$buffer .= "&nbsp;\n";
		$buffer .= "<img src=\"lib/template/default/img/move_up.png\" ".
			"onClick=\"change_position(document.getElementById('".$k."').parentNode, '".$k."', -1);\" border=\"0\" />\n";
		$buffer .= "<img src=\"lib/template/default/img/move_down.png\" ".
			"onClick=\"change_position(document.getElementById('".$k."').parentNode, '".$k."', 1);\" border=\"0\" />\n";
		$buffer .= "</div>\n";
	}
	$buffer .= "</div>\n";

	// Divider between enabled and disabled
	return $buffer;
} // end function my_module_table

//----- Create configuration notebook
$book = CreateObject('PHP.notebook',
	array(
		"action", // so that we remain in configure
		"id" // to keep manage remembering the patient
	),
	NOTEBOOK_STRETCH | NOTEBOOK_COMMON_BAR
);

//----- Define list of configuration vars
$config_vars = array (
	"automatic_refresh_time",
	"display_columns",
	"num_summary_items",
	"components"
);

//----- Pull out proper pieces
if (!$book->been_here()) {
	// Check if there are any config variables
	if (count($this_user->manage_config) > 0) {
		// Extract the variables into the global domain
		extract($this_user->manage_config);
	} else {
		foreach ($config_vars AS $c) {
			if (!isset($GLOBALS[$c])) { global ${$c}; ${$c} = $_REQUEST[$c]; }
		}		
	} // end checking for config
} // end pulling out old values
//print "<pre>"; print_r($this_user->manage_config); die("</pre>");

//print "<pre>"; print_r($_REQUEST); print "</pre>\n"; die();

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

/*
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
				'1 ('.__("top").')' => '1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9 ('.__("bottom").')' => '9'
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
				'1 ('.__("top").')' => '1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9 ('.__("bottom").')' => '9'
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
				'1 ('.__("top").')' => '1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9 ('.__("bottom").')' => '9'
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
				'1 ('.__("top").')' => '1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9 ('.__("bottom").')' => '9'
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
				'1 ('.__("top").')' => '1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9 ('.__("bottom").')' => '9'
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
				'1 ('.__("top").')' => '1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9 ('.__("bottom").')' => '9'
			),
			$static_components[photo_id][order]
		)

	)).
	"</center>\n"
);
*/

//----- Create module list for modular configuration
$module_list = freemed::module_cache();

//----- This is *so* jimmy rigged... -----

// Make sure that whatever it is, it's an array
if (!is_array($components)) {
	$components = array($components);
}

// Create basic template for split
$module_template = "#name#:#class#/";
// Form the actual hash with the template
$class_hash = $module_list->generate_list ( "Electronic Medical Record",
	0, $module_template );

//print "class_hash = $class_hash<BR>\n";

// Break apart key/value pairs into array
$class_array = explode( "/", $class_hash );
// Loop through array to separate key and val
$max = 0;
foreach ($class_array AS $k => $class_pair) {
	if (!empty($class_pair)) {
		list ($key, $val) = explode (":", $class_pair);
		if ($components[$val][order] > $max) {
			$max = $components[$val][order];
		}
	}
}
foreach ($class_array AS $k => $class_pair) {
	if (!empty($class_pair)) {
		list ($key, $val) = explode (":", $class_pair);
	}
}
foreach($class_array AS $k => $class_pair) {
	if (!empty($class_pair)) {
		// Break it
		list ($key, $val) = explode (":", $class_pair);
		// Add it
		//$classes["$key"] = $val; // this would be for anything else

		// If we don't have it, and it's enabled, add it to the end
		if (! $components[$val][order] and $components[$val][module] ) {
			//print "<b>for $val, set as $max</b><br/>\n"; die();
			$max++;
			$components[$val][order] = $max;
		}

		// Check for access using ACLs
		if (freemed::module_check_acl($val)) {
			// Actually add it
			$value = array (
				'name' => $key,
				'order' => $components[$val][order]
			);

			// Is module enabled?
			if ( $components[$val][module] ) {
				$enabled[$val] = $value;
			} else {
				$disabled[$val] = $value;
			} // end checking if module is enabled

		} // end freemed::module_check_acl check
	} // end checking for empties
} // end while loop

//----- Module configuration
$book->add_page("Components",
	array ( "components" ),
	my_module_table ( $enabled, $disabled )
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
				break;

			default:
				$mc[$k] = $v;
		}
	}
	// This is *really* fun. Make the appropriate hashes...
	foreach ($config_vars AS $opt) {
		switch ($opt) {
			case 'components':
			unset($a);
			foreach (${$opt} AS $k => $v) { 
				if (isset($v['module']) and isset($v['order'])) {
					//print "<b>"; print_r($v); print"</b><br/>\n";
					$a[$k] = $v;
					$a[$k][module] = $k;
				}
			}
			$mc[$opt] = $a;
			break;
		
			case 'modular_components':
			unset(${$opt});
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
		//$mc[$opt] = ${$opt};
	} // end looping through config_vars

	//print "<pre>"; print_r($mc); print "</pre>\n"; die();

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
