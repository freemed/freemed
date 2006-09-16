<?php
 // $Id$
 // desc: forms widgets
 // code: jeff b <jeff@ourexchange.net>
 // lic : LGPL

if (!defined("__CLASS_HTML_FORM_PHP__")) {

define ('__CLASS_HTML_FORM_PHP__', true);

// Class: PHP.html_form
//
//	Contains various methods for creating HTML FORM widgets and
//	processing them.
//
class html_form {

	// Method: html_form::color_widget
	//
	//	Color selection widget.
	//
	//	WARNING - This is just awful, and needs to be replaced with something
	//	that makes more sense.
	//
	// Parameters:
	//
	//	$varname - Variable name
	//
	// Returns:
	//
	//	XHTML widget
	//
	function color_widget ( $varname ) {
		global ${$varname};
		if (isset($_REQUEST[$varname])) { ${$varname} = $_REQUEST[$varname]; }
		$__colors__ = array (
			'#ffffff',
			'#ffff00',
			'#ff00ff',
			'#00ffff',
			'#ff0000',
			'#00ff00',
			'#0000ff'
		);
		$buffer .= "<select class=\"nocolor\" name=\"".$varname."\">\n";
		foreach ($__colors__ AS $__color__) {
			$buffer .= "\t<option value=\"${__color__}\" ".
				( ${$varname} == ${__color__} ? 'SELECTED' : '' ).
				" style=\"background-color: ${__color__};\">${__color__}".
				"</option>\n";
		}
		$buffer .= "</select>\n";
		return $buffer;
	} // end method color_widget

	// Method: html_form::combo_widget
	//
	//	Creates the HTML form equivalent of a combination
	//	widget (selection and manual entry).
	//
	// Parameters:
	//
	//	$varname - Name of the global variable to store and
	//	retrieve the current value from.
	//
	//	$values - Array of values for the selection portion
	//	of the widget.
	//
	// Returns:
	//
	//	XHTML-compliant combination widget code
	//
	// See Also:
	//	<html_form::combo_assemble>
	//
	function combo_widget ($varname, $values) {
		global $$varname, ${$varname."_text"};

		// reset buffer
		$buffer = "";

		// form select box first
		$select_box = ""; $max_length = 1;
		$select_box .= "\t<select NAME=\"$varname\">\n";
		$select_box .= "\t\t<option VALUE=\"\"> ----&gt;\n";
		if (is_array($values)) {
			reset($values);
			while (list ($k, $v) = each ($values)) {
				if (strlen($k)>$max_length)
					$max_length=strlen($k); 
				$select_box .= "\t\t<option VALUE=\"".
					prepare($v)."\" ".
					( ($$varname==$v) ? "SELECTED" : "" ).
					">".prepare($k)."</option>\n";
			} // end looping through everything
		} // end checking if array
		$select_box .= "    </select>\n";

		// Set minimum maxlength to 15
		$max_length = ( ($maxlength>15) ? $maxlength : 15 );
  
		$buffer .= "
			<table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"0\">
			<tr>
			<td>$select_box</td>
			<td><input TYPE=\"TEXT\" NAME=\"".prepare($varname)."_text\" ".
			"SIZE=\"".$max_length."\"
			VALUE=\"".prepare(${$varname."_text"})."\"/>
			</tr>
			</table>
		";

		return $buffer;
	} // end function html_form::combo_widget

	// Method: html_form::combo_assemble
	//
	//	Derives the value passed by a combination widget
	//	generated by <html_form::combo_widget>.
	//
	// Parameters:
	//
	//	$varname - Name of the variable containing the widget
	//	form data.
	//
	// Returns:
	//
	//	Scalar value.
	//
	// See Also:
	//	<html_form::combo_widget>
	//
	function combo_assemble ($varname) {
		global $$varname, ${$varname."_text"};
		return ( !empty($$varname) ? $$varname : ${$varname."_text"} );
	} // end function html_form::combo_assemble

	// Method: html_form::confirm_link_widget
	//
	//	Creates a link which requires a confirmation before
	//	proceeding to its destination.
	//
	// Parameters:
	//
	//	$link - URL location for the link to go to
	//
	//	$link_text - Text that is shown in the A tag
	//
	//	$_options - (optional) Associative array containing
	//	optional parameters. Possible values are:
	//		* confirm_text - Text of the actual confirmation
	//		message.
	//		* class - Class of the A tag
	//
	// Returns:
	//
	//	XHTML-compliant confirmation widget code.
	//
	function confirm_link_widget ($link, $link_text, $_options) {
		if (is_array($_options)) { $options = $_options; }
		if (!isset($_options['confirm_text'])) {
			$options['confirm_text'] = 'Are you sure?';
		}
		$buffer .= "<a href=\"".$link."\" ".
			( $options['class'] ? "class=\"".$options['class']."\"" : "" ).
			( !empty($options['text']) ?
			"onMouseOver=\"window.status='".addslashes($options['text']).
			"'; return true;\" ".
			"onMouseOut=\"window.status=''; return true;\" " : "" ).
			"onClick=\"if (confirm('".addslashes($options['confirm_text']).
			"')) { window.location='".$link."'; } else { ".
			"return false; } ".
			"\">".$link_text."</a>";
		return $buffer;
	} // end function html_form::confirm_link_widget

	// Method: html_form::submit
	//
	//	Creates a submit button
	//
	// Parameters:
	//
	//	$text - Text on the submit button
	//
	//	$_options - (optional) Associative array of options
	//		* class - CLASS property of submit button
	//		* name - NAME property of submit button
	//		* style - STYLE property of submit button
	//
	// Returns:
	//
	//	XHTML-compliant INPUT TYPE=submit button code
	//
	function submit ($text, $_options=NULL) {
		if (is_array($_options)) { $options = $_options; }
		return "<input TYPE=\"SUBMIT\" ".
			( isset($options['class']) ? "CLASS=\"".$options['class']."\"" : "" ).
			( isset($options['name']) ? "NAME=\"".$options['name']."\"" : "" ).
			( isset($options['style']) ? "STYLE=\"".$options['style']."\"" : "" ).
			"VALUE=\"".prepare($text)."\"/>\n";
	} // end function html_form::submit

	// Method: html_form::country_pulldown
	//
	//	Create a picklist of countries
	//
	// Parameters:
	//
	//	$varname - Name of the variable containing the data for
	//	this widget
	//
	// Returns:
	//
	//	XHTML-compliant country picklist widget code
	//
	function country_pulldown ($varname) {
		global ${$varname}; // import variable
		$countries = array (
			"United States",
			"Canada",
			"Afghanistan",
			"Austria",
			"Belgium",
			"Brazil",
			"Chile",
			"China",
			"Dominican Republic",
			"Ecuador",
			"England",
			"Egypt",
			"France",
			"Germany",
			"Haiti",
			"Holland",
			"Hungary",
			"Iran",
			"Iraq",
			"Ireland",
			"Israel",
			"Italy",
			"Japan",
			"Mexico",
			"Norway",
			"Packistan",
			"Paraguay",
			"Poland",
			"Portugal",
			"South Africa",
			"Spain",
			"Syria",
			"Thailand",
			"Uruguay"
		);

		// reset everything
		reset ($countries); $buffer = "";

		// start select box
		$buffer .= "\n<select NAME=\"".prepare($varname)."\">\n";

		while (list($key, $val) = each($countries)) {
			$buffer .= "<option VALUE=\"".prepare($val)."\" ".
			( in_this (${$varname}, $val) ? "SELECTED" : "" ).
			">".prepare($val)."</option>\n";
		} // end of while looping

		// end select box
		$buffer .= "\n</select>\n";

		// return buffer
		return $buffer;

	} // end function html_form::country_pulldown

	function gender_select ($varname) {
		global ${$varname};

		$buffer = "";

		$buffer .= "\n<input TYPE=\"RADIO\" NAME=\"".prepare($varname)."\" ".
			"VALUE=\"m\" ".
			( in_this (${$varname}, "m") ? "CHECKED" : "" )."/>Male &nbsp;\n".
			"<input TYPE=\"RADIO\" NAME=\"".prepare($varname)."\" ".
			"VALUE=\"f\" ".
			( in_this (${$varname}, "f") ? "CHECKED" : "" )."/>Female\n";

			return $buffer;
	} // end function html_form::gender_select

	// Method: html_form::_addzero
	//
	//	Adds leading zeros to a number
	//
	// Paramters:
	//
	//	$number - Initial number to process
	//
	//	$max - Maximum number to even length to. (A max value of
	//	10000 would make a number of 10 become "00010")
	//
	// Returns:
	//
	//	Processed number.
	//
	function _addzero ($number, $max) { // (INTERNAL FUNCTION)
		$my_number = floor($number); // need in our scope
		$my_max = floor($max);
		while (strlen($my_number) < strlen($my_max)) 
			$my_number = "0".$my_number;
		return $my_number;
	} // end function html_form::_addzero (INTERNAL FUNCTION)

	// Method: html_form::number_pulldown
	//
	//	Creates a number picklist.
	//
	// Parameters:
	//
	//	$varname - Name of the variable encapsulating the data
	//	for this widget.
	//
	//	$start - Beginning number
	//
	//	$end - Ending number
	//
	//	$step - (optional) Incremental value between numbers.
	//	Default is 1.
	//
	//	$addzero - (optional) Boolean, make all numbers flush
	//	with longest number. Defaults to true.
	//
	// Returns:
	//
	//	XHTML-compliant number picklist widget code.
	//
	// See Also:
	//	<html_form::_addzero>
	//
	function number_pulldown ($varname, $start, $end, $step=1, $addzero=true) {
		global ${$varname};

		$buffer = "";

		// display top of selet box
		$buffer .= "\n<select NAME=\"".prepare($varname)."\">\n";

		// loop
		$v = ${$varname} + 0;
		for ($i=$start;$i<=$end;$i+=$step) {
			$buffer .= "<option VALUE=\"".
			( $addzero ? html_form::_addzero($i,$end) : $i )."\" ";
			if ($step < 1) {
				if (is_array(${$varname})) { print "WARNING! Precision not enabled for arrays yet!<br/>\n"; }
				// Calculate precision for comparison
				$precision = -(log10($step));
				if (bcadd($v, 0, $precision) == bcadd($i, 0, $precision)) {
					$buffer .= "SELECTED";
				}
			} else {
				if ($v == $i) { $buffer .= "SELECTED"; }
			}
			$buffer .= ">".
			//( in_this (${$varname}, $i) ? "SELECTED" : "" ).">".
			( $addzero ? html_form::_addzero($i,$end) : $i )."</option>\n";
		} // end loop

		// display bottom of select box
		$buffer .= "\n</select>\n";

		return $buffer;
	} // end function html_form::number_pulldown

	// Method: html_form::select_option
	//
	//	Create code for single option as part of an XHTML
	//	SELECT widget
	//
	// Parameters:
	//
	//	$varname - Name of variable that encapsulates the
	//	data for this widget
	//
	//	$value - Value of this select item
	//
	//	$text - (optional) Text that is displayed for this
	//	select value. If this is not given, it defaults to
	//	$value.
	//
	// Returns:
	//
	//	XHTML OPTION sub-widget code
	//
	// See Also:
	//	<html_form::select_widget>
	//
	function select_option ($varname, $value, $text="") {
		global ${$varname};

		// dump value into text if nothing presented
		if (empty($text)) $text=$value;

		// empty buffer
		$buffer = "";

		// Deal with possible fudging of in_this()
		$selected = false;
		if (${$varname} == $value) {
			$selected = true;
		}
		if (in_this(${$varname}, $value)) {
			$selected = true;
		}

		// create option
		$buffer .= "<option VALUE=\"".prepare($value)."\" ".
			( $selected ? "SELECTED" : "" ).
			">".prepare($text)."</option>\n";

		// return buffer
		return $buffer;
	} // end function html_form::select_option

	// Method: html_form::select_widget
	//
	//	Creates a picklist widget
	//
	// Parameters:
	//
	//	$varname - Name of the variable that encapsulates the
	//	data for this widget
	//
	//	$values - An associative array of values defining the
	//	possible options for this array. Keys are the displayed
	//	text, and values are the values passed back to the
	//	program.
	//
	//	$_options - (optional) Associative array of optional
	//	parameters.
	//		* array_index - Index of $varname, if $varname
	//		is an array
	//		* class - Defines the CLASS parameter
	//		* form_name - Name of the HTML form that this
	//		widget is placed in. Required for refresh
	//		to work most of the time.
	//		* on_change - Defines the onChange Javascript
	//		parameter
	//		* refresh - Defines whether the widget will
	//		submit the entire form when the value changes
	//		* style - Defines the STYLE parameter
	function select_widget ($varname, $values, $_options = -1) {
		// Handle legacy calls to this function	
		if (!is_array($_options)) {
			$options['array_index'] = $_options;
		} else {
			// Pass it thru.
			$options = $_options;
			if (empty($options['array_index']))
				$options['array_index'] = -1;
		}

		// For some reason, it sometimes loses the fact that it
		// has no array index, with unpredictable results.
		if (!isset($options['array_index'])) {
			$options['array_index'] = -1;
		}

		// Determine if we are using an array member...
		$array_member = (! (strpos($varname, "[") === false) );

		if (!$array_member) {
			// Do what we'd normally do...
			global ${$varname};
		} else {
			// Extract variable and index
			list ($_varname, $_index) = explode ("[", $varname);
			// Remove trailing bracket
			$_index = substr($_index, 0, strlen($_index)-1);
			// Globalize...
			global ${$_varname};
		}

		// no nested arrays here!
		//$this_values = flatten_array ($values);
		$this_values = $values; // FIXME: flatten_array is still broken

		// fix for values not being an array
		if (!is_array($this_values))
			$this_values[] = $this_values;

		// clear buffer
		$buffer = "";

		$__method_for_submit = 'this.form.submit';
		if ($options['form_name']) {
			$__method_for_submit = 'document.'.
				$options['form_name'].'.submit';
		}

		// display top of select box
		$buffer .= "\n<select NAME=\"".prepare($varname).
			( ($options['array_index'] != -1) ? "[]" : "" )."\" ".
			"ID=\"".prepare($varname)."\" ".
			( ($options['refresh']) ? " onChange=\"".$__method_for_submit."(); return true;\"" : "" ).
			( ($options['class']) ? " class=\"".$options['class']."\"" : "" ).
			( ($options['style']) ? " style=\"".$options['style']."\"" : "" ).
			( ($options['on_change']) ? " onChange=\"".$options['on_change']."; return true;\"" : "" ).
			">\n";

		reset ($this_values);
		if (is_array($this_values[0])) { return $buffer .= "</select>\n"; }
		while (list($k, $v) = each ($this_values)) {
			if ( (is_integer($k) and ($this_values[($k+0)] == $v))
				 or (empty($k)) ) {
				$k = $v;	// pass name pair
			} // end checking if there is no index
			if (($options['array_index'] == -1) and (!$array_member)) {
				$buffer .= html_form::select_option (
					$varname,
					$v,
					$k
				);
			} else { // if there is an index ...
				$array_index = $options['array_index'];
				if (!$array_member) {
				$buffer .= "<option VALUE=\"".prepare($v)."\" ".
					( ((${$varname}["$array_index"])==$v) ? "SELECTED" : "" ).
					">".prepare($k)."</option>\n";
				} else {
				$buffer .= "<option VALUE=\"".prepare($v)."\" ".
					( ((${$_varname}["$_index"])==$v) ? "SELECTED" : "" ).
					">".prepare($k)."</option>\n";

				} // end checking for array member
			} // end if
		} // end looping

		// end select box
		$buffer .= "\n</select>\n";

		// return buffer
		return $buffer;
	} // end function html_form::select_widget

	// Method: html_form::checkbox_widget
	//
	//	Creates a checkbox widget
	//
	// Parameters:
	//
	//	$varname - Name of the variable containing the data
	//	for the widget
	//
	//	$value - Value that checking off this box will return.
	//
	//	$text - (optional) Text that will be displayed next to
	//	the checkbox. If this is not given, it will default to
	//	$value.
	//
	//	$helptext - (optional) Text that will be displayed in a
	//	mouseover of the text next to the checkbox. Defaults to
	//	none.
	//
	// Returns:
	//
	//	XHTML-compliant checkbox widget code
	//
	function checkbox_widget ($varname, $value, $text="", $helptext="") {
		global ${$varname};

		// dump value into text if nothing presented
		if (empty($text)) $text=$value;

		// empty buffer
		$buffer = "";

		// create option
		$buffer .= "<input TYPE=\"CHECKBOX\" NAME=\"".prepare($varname).
			( is_array (${$varname}) ? "[]" : "" )."\" ".
			"VALUE=\"".prepare($value)."\" ".
			( in_this(${$varname}, $value) ? "CHECKED" : "" ).
			"/>".(
				(!empty($helptext)) ?
				"<abbr TITLE=\"".prepare($helptext)."\">" : ""
			).prepare($text).(
				(!empty($helptext)) ?
				"</abbr>" : ""
			)."\n";

		// return buffer
		return $buffer;
	} // end function html_form::checkbox_widget

	// Method: html_form::form_table
	//
	//	Creates a pre-formatted simple table for displaying
	//	HTML forms
	//
	// Parameters:
	//
	//	$cells - Associative array, with the keys being labels
	//	for cells and their values being the code put in
	//	corresponding cells. Be careful not to duplicate keys,
	//	as those entries will be lost (append spaces to duplicate
	//	labels to avoid this).
	//
	//	$error_bg - (optional) Background color of items containing
	//	errors, in #RRGGBB format. Defaults to #ff8888.
	//
	//	$error_fg - (optional) Foreground color of items containing
	//	errors, in #RRGGBB format. Defaults to #ffffff.
	//
	//	$seperator - (optional) Characters used to seperate the
	//	labels from their items. Defaults to ' : '.
	//
	// Returns:
	//
	//	XHTML-compliant form table code.
	//
	// Example:
	//
	//>	print html_form::form_table(array(
	//>		"Item 1" => html_form::text_widget('item1'),
	//>		"Item 2" => html_form::text_widget('item2')
	//>	));
	//
	function form_table ($cells, $error_bg = "#ff8888", $error_fg = "#ffffff", $seperator = " : ") {
		// import form error and warning variables
		global $FORM_ERROR, $FORM_WARNING;

		$buffer = "";

		$buffer .= "
 			<div ALIGN=\"CENTER\">
			<table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"3\"
				ALIGN=\"CENTER\">
		";

		reset ($cells);
		while (list ($k, $v) = each ($cells)) {
			if (!empty ($v)) {
				// no flags, by default
				$flag_ERROR = $flag_WARNING = false;

				// check for value in $v
				if (is_array($FORM_WARNING)) {
					foreach($FORM_WARNING AS $garbage => $var) {
						if (stristr($v, "NAME=\"".$var."\""))
							$flag_WARNING = true;
					} // end loop
				} // end if is_array FORM_WARNING	
				if (is_array($FORM_ERROR)) {
					foreach($FORM_ERROR AS $garbage => $var) {
						if (stristr($v, "NAME=\"".$var."\""))
							$flag_ERROR = true;
					} // end loop
				} // end if is_array FORM_ERROR	

				// Check for special cases of k (arrays)
				$t = '';
				if (is_array($v)) {
					$t = ( isset($v['help']) ?
					"<acronym TITLE=\"".prepare($v['help']).
					"\">".prepare($k)."</acronym>" :
					prepare($k) );
					$v = $v['content'];
				} else {
					// Do nothing with v
					$t = prepare(( empty($k) ? '&nbsp;' : $k ));
				}

				// add to buffer
				if (!empty($k)) $buffer .= "
					<tr>
					<td COLSPAN=\"1\" ALIGN=\"RIGHT\" VALIGN=\"MIDDLE\" ".
					( ($flag_ERROR) ? "BGCOLOR=\"".$error_bg."\"" : "" ).">
					".( 
						($flag_ERROR) ?
						"<font COLOR=\"".$error_fg."\"><b>" : ""
					). $t .
					( 
						($flag_ERROR) ?
						"</b></font>" : ""
					)."</td><td COLSPAN=\"2\" ALIGN=\"LEFT\" VALIGN=\"MIDDLE\" ".
					( ($flag_ERROR) ? "BGCOLOR=\"".$error_bg."\"" : "" ).">
					".$v."
					</td>
					</tr>\n";
			} // end if not empty    
		} // end looping through cells

		$buffer .= "
			</table>
			</div>
		";

		// return the buffer
		return $buffer;
	} // end function html_form::form_table

	// Method: html_form::password_widget
	//
	//	Creates a password (hidden text) widget
	//
	// Parameters:
	//
	//	$varname - Name of the variable that encapsulates the
	//	data of the widget.
	//
	//	$length - (optional) Size of the password widget box, in
	//	characters. Defaults to 20.
	//
	//	$maxlength - (optional) Maximum allowable text length.
	//	Defaults to $length.
	//
	//	$array_index - (optional) Index of the $varname array that
	//	this widget is acting on. Defaults to no array present.
	//
	// Returns:
	//
	//	XHTML-compliant password widget code.
	//
	// See Also:
	//	<html_form::text_widget>
	//
	function password_widget ($varname, $length=20, $_maxlength=-1, $array_index=-1) {
		global ${$varname};

		// Determine if we are using an array member...
		$array_member = (! (strpos($varname, "[") === false) );

		if (!$array_member) {
			// Do what we'd normally do...
			global ${$varname};
		} else {
			// Extract variable and index
			list ($_varname, $_index) = explode ("[", $varname);
			// Remove trailing bracket
			$_index = substr($_index, 0, strlen($_index)-1);
			// Globalize...
			global ${$_varname};
		}

		if ($_maxlength != -1) $maxlength=$_maxlength;
		 else $maxlength = $length;
		return "<input TYPE=\"PASSWORD\" ".
			"NAME=\"".prepare($varname)."\" ".
			"SIZE=\"".( ($length < 50) ? ($length + 1) : 50 )."\" ".
			"MAXLENGTH=\"$maxlength\" ".
			"VALUE=\"".prepare( (
				( $array_member ) ?
				${$_varname}["$_index"] : ${$varname}
			))."\"/>\n";
	} // end function html_form::password_widget

	// Method: html_form::radio_widget
	//
	//	Creates a radio box widget
	//
	// Parameters:
	//
	//	$varname - Name of the variable which encapsulates the
	//	data for the widget
	//
	//	$value - Value that this radio widget produces, if
	//	selected.
	//
	//	$text - (optional) Text which is shown to the side of
	//	the widget. Defaults to $value.
	//
	// Returns:
	//
	//	XHTML-compliant INPUT TYPE=RADIO widget code
	//
        function radio_widget ($varname, $value, $text="") {
		global ${$varname};

		// dump value into text if nothing presented
		if (empty($text)) $text=$value;

		// empty buffer
		$buffer = "";

		// create option
		$buffer .= "<input TYPE=\"RADIO\" NAME=\"".prepare($varname).
			( is_array ($$varname) ? "[]" : "" )."\" ".
			"VALUE=\"".prepare($value)."\" ".
			( in_this($$varname, $value) ? "CHECKED" : "" ).
			"/>".prepare($text)."\n";

		// return buffer
		return $buffer;
	} // end function html_form::radio_widget

	// Method: html_form::state_pulldown
	//
	//	Creates a picklist of United States states
	//
	// Parameters:
	//
	//	$varname - Name of the variable that contains the
	//	data for the widget
	//
	//	$is_full - (optional) Boolean, whether the full state
	//	names will be displayed. Defaults to false.
	//
	// Returns:
	//
	//	XHTML-compliant state picklist
	//
	function state_pulldown ($varname, $is_full=false) {
		$states = array (
			"AL" => "Alabama",
			"AK" => "Alaska",
			"AS" => "American Samoa",
			"AZ" => "Arizona",
			"AR" => "Arkansas",
			"CA" => "California",
			"CO" => "Colorado",
			"CT" => "Connecticut",
			"DE" => "Delaware",
			"DC" => "District of Columbia",
			"FM" => "Federated States of Micronesia",
			"FL" => "Florida",
			"GA" => "Georgia",
			"GU" => "Guam",
			"HI" => "Hawaii",
			"ID" => "Idaho",
			"IL" => "Illinois",
			"IN" => "Indiana",
			"IA" => "Iowa",
			"KS" => "Kansas",
			"KY" => "Kentucky",
			"LA" => "Louisiana",
			"ME" => "Maine",
			"MH" => "Marshall Islands",
			"MD" => "Maryland",
			"MA" => "Massachussetts",
			"MI" => "Michigan",
			"MN" => "Minnesota",
			"MS" => "Mississippi",
			"MO" => "Missouri",
			"MT" => "Montana",
			"NE" => "Nebraska",
			"NV" => "Nevada",
			"NH" => "New Hampshire",
			"NJ" => "New Jersey",
			"NM" => "New Mexico",
			"NY" => "New York",
			"NC" => "North Carolina",
			"ND" => "North Dakota",
			"NP" => "Northern Mariana Islands",
			"OH" => "Ohio",
			"OK" => "Oklahoma",
			"OR" => "Oregon",
			"PW" => "Palau",
			"PA" => "Pennsylvania",
			"PR" => "Puerto Rico",
			"RI" => "Rhode Island",
			"SC" => "South Carolina",
			"SD" => "South Dakota",
			"TN" => "Tennessee",
			"TX" => "Texas",
			"UT" => "Utah",
			"VT" => "Vermont",
			"VI" => "Virgin Islands",
			"VA" => "Virginia",
			"WA" => "Washington",
			"WV" => "West Virginia",
			"WI" => "Wisconsin",
			"WY" => "Wyoming"
		);
		global ${$varname}; // import variable

		// reset everything
		reset ($states); $buffer = "";

		// sort by the proper category
		if ($is_full) { sort ($states); } else { ksort ($states); }

		// start select box
		$buffer .= "\n<select NAME=\"".prepare($varname)."\">\n";
		$buffer .= "<option VALUE=\"\">--</option>\n";

		while (list($key, $val) = each($states)) {
			$buffer .= "<option VALUE=\"".prepare($key)."\" ".
			( in_this ($$varname, $key) ? "SELECTED" : "" ).
			">".prepare(( $is_full ? $val : $key ))."</option>\n";
		} // end of while looping

		// end select box
		$buffer .= "\n</select>\n";

		// return buffer
		return $buffer;

	} // end function html_form::state_pulldown

	// Method: html_form::text_area
	//
	//	Creates a text area widget
	//
	// Parameters:
	//
	//	$varname - Name of the variable the contains the
	//	data for the widget
	//
	//	$wrap - (optional) Type of wrapping, as is passed to
	//	the HTML TEXTAREA tag. Defaults to "ON".
	//
	//	$rows - (optional) Number of rows in the text box.
	//	Defaults to 4.
	//
	//	$cols - (optional) Number of columns in the text
	//	box. Defaults to 40.
	//
	// Returns:
	//
	//	XHTML-compliant TEXTAREA widget code
	//
	function text_area ($varname, $wrap="ON", $rows=4, $cols=40) {
		global ${$varname};

		// Determine if we are using an array member...
		$array_member = (! (strpos($varname, "[") === false) );

		if (!$array_member) {
			// Do what we'd normally do...
			global ${$varname};
		} else {
			// Extract variable and index
			list ($_varname, $_index) = explode ("[", $varname);
			// Remove trailing bracket
			$_index = substr($_index, 0, strlen($_index)-1);
			// Globalize...
			global ${$_varname};
		}

		return "<textarea NAME=\"".prepare($varname)."\" ".
			"ID=\"".prepare($varname)."\" ".
			"ROWS=\"$rows\" ".
			"COLS=\"$cols\" WRAP=\"$wrap\">".prepare((
				( $array_member ) ?
				${$_varname}["$_index"] : ${$varname}

			), true).
			"</textarea>\n";
	} // end function html_form::text_area

	// Method: html_form::text_widget
	//
	//	Creates a text widget
	//
	// Parameters:
	//
	//	$varname - Name of the variable that contains the
	//	data used by the widget
	//
	//	$_options - (optional) Associative array of optional
	//	parameters.
	//		* id - HTML ID parameters
	//		* length - Size of this widget, in characters
	//		* refresh - Boolean, whether the page is refreshed
	//		when focus on this widget is lost. Defaults to false.
	//
	// Returns:
	//
	//	XHTML-compliant INPUT TYPE=TEXT widget code
	//
	function text_widget ($varname, $_options=NULL, $_maxlength=-1, $array_index=-1) {
		global ${$varname};

		// Defaults?
		$length = 20;
		$refresh = false;

		// Check options
		if ($_options != NULL) {
			if (!is_array($_options)) {
				// Backwards compatibility
				$length = $_options;
			} else {
				$options = $_options;
				$length = (isset($options['length']) ? $options['length'] : 20);
				$refresh = (isset($options['refresh']) ? $options['refresh'] : false);
				$css_id = (isset($options['id']) ? $options['id'] : NULL);
			}
		}

		// Determine if we are using an array member...
		$array_member = (! (strpos($varname, "[") === false) );

		if (!$array_member) {
			// Do what we'd normally do...
			global ${$varname};
		} else {
			// Extract variable and index
			list ($_varname, $_index) = explode ("[", $varname);
			// Remove trailing bracket
			$_index = substr($_index, 0, strlen($_index)-1);
			// Globalize...
			global ${$_varname};
		}

		if ($_maxlength != -1) $maxlength=$_maxlength;
		 else $maxlength = $length;
		return "<input TYPE=\"TEXT\" NAME=\"".prepare($varname)."\" ".
			"SIZE=\"".( ($length < 50) ? ($length + 1) : 50 )."\" ".
			"MAXLENGTH=\"$maxlength\" ".
			( $css_id ?  "ID=\"$css_id\" " : "" ).
			"VALUE=\"".prepare( (
				( $array_member ) ?
				${$_varname}["$_index"] : ${$varname}
			))."\"".
			( $refresh ?
			"onBlur=\"this.form.submit(); return true;\" ".
			"onChange=\"this.form.submit(); return true;\"" : "" ).
			"/>\n";
	} // end function html_form::text_widget

} // end class html_form

} // end checking if defined

?>
