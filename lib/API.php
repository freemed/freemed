<?php
	// $Id$
	// $Author$

// File: Core API
//
//	This is the main FreeMED API, which contains the bulk of FreeMED's
//	commonly used functions. The rest of the functions are located in
//	classes which are called dynamically using CreateObject(). It is
//	located in lib/API.php.
//

if (!defined("__API_PHP__")) {

define ('__API_PHP__', true);

// namespace/class freemed
class freemed {
	// Function: freemed::check_access_for_facility
	//
	//	Checks to see if the current user has access to the specified
	//	facility.
	//
	// Parameters:
	//
	//	$facility_number - the database id of the facility in question
	//
	// Returns:
	//
	//	$access - boolean value, whether access is granted
	//
	// See Also:
	//
	//	<freemed::check_access_for_patient>
	//
	function check_access_for_facility ($facility_number) {
		global $_SESSION;

		// Separate out authdata
		$authdata = $_SESSION['authdata'];

		// Root has all access...
		if ($_SESSION['authdata']['user'] == 1) return true;

		// Grab the authorizations field
		$f_fac = freemed::get_link_field ($authdata['user'], "user", "userfac");

		// No facility, assume no access restrictions
		if ($facility_number == 0) return true;

		// If it's an "ALL" or it is found, return true
		if ((fm_value_in_string($f_fac, "-1")) OR
				(fm_value_in_string($f_fac, $facility_number)))
			return true;

	    	// Default to false
		return false;
	} // end function freemed::check_access_for_facility

	// Function: freemed::check_access_for_patient
	//
	//	Checks to see whether the current user has access to the
	//	specified patient.
	//
	// Parameters:
	//
	//	$patient_number - The database identifier for the patient
	//
	//	$user (optional) - The database identifier for the current
	//	user. (This should be used when dealing with XML-RPC or
	//	another protocol which does not use cookie-based authentication.
	//
	// Returns:
	//
	//	$access - boolean, whether access is granted
	//
	// See Also:
	//
	//	<freemed::check_access_for_facility>
	//
	function check_access_for_patient ($patient_number, $_user=0) {
		if ($_user == 0) {
			// Grab authdata
			$_authdata = $_SESSION['authdata'];
			$user = $_authdata['user'];
		} else {
			$user = $_user;
		}
		
		//eventually logging should include different messages for all returns here...
		// Root has all access...
		if ($user == 1) return true;
	
		// Grab auth information from db
		$f_user   = freemed::get_link_rec ($user, "user");
	
		// Get data records in question for the user
		$f_fac    = $f_user ["userfac"   ];
		$f_phy    = $f_user ["userphy"   ];
		$f_phygrp = $f_user ["userphygrp"];
	
		// Retrieve patient record
		$f_pat    = freemed::get_link_rec ($patient_number, "patient");
		// check for universal access
		if ((fm_value_in_string ($f_fac,    "-1")) OR
			(fm_value_in_string ($f_phy,    "-1")) OR
			(fm_value_in_string ($f_phygrp, "-1")))
			return true;
	
		// Check for physician in any physician fields
		if (($f_pat["ptpcp"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptpcp"])))
			return true;
		if (($f_pat["ptphy1"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptphy1"])))
			return true;
		if (($f_pat["ptphy2"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptphy2"])))
			return true;
		if (($f_pat["ptphy3"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptphy3"])))
			return true;
		if (($f_pat["ptdoc"]>0) AND
			(fm_value_in_string ($f_phy, $f_pat["ptdoc"])))
			return true;

	    	// Default to false
		return false;
	} // end function freemed::check_access_for_patient

	// Function: freemed::config_value
	//
	//	Retrieves a configuration value from FreeMED's centralized
	//	configuration database table.
	//
	// Parameters:
	//
	//	$key - The name of the configuration value desired.
	//
	// Returns:
	//
	//	$value - The value of the configuration key, or NULL if the
	//	key is not found.
	//
	function config_value ($config_var) {
		static $_config;
		global $sql;
	 
 		// Set to cache values
 		if (!isset($_config)) {
			$query = $sql->query("SELECT * FROM config");
	
			// If the table doesn't exist, skip out
			if (!$query) return false;
	
			// Loop through results
			while ($r = $sql->fetch_array($query)) {
				$_config[stripslashes($r[c_option])] =
					stripslashes($r[c_value]);
			} // end of looping through results
		} // end of caching
	
		// Return from cache
		return $_config["$config_var"];
	} // end function freemed::config_value

	// Function: freemed::connect
	//
	//	Master function to run authentication routines for the
	//	current used. This method should be called at the beginning
	//	of every standalone FreeMED script when dealing with standard
	//	session based authentication.
	//
	function connect () {
		global $display_buffer;

		// Verify
		if (!freemed::verify_auth()) {
			$display_buffer .= "
			<div ALIGN=\"CENTER\">
			<b>".__("You have entered an incorrect username or password.")."</b>
			<br/><br/>
			<b><i>".__("It is possible that your cookies have expired.")."</i></b>
			<p/>
			".template::link_button(
				__("Return to the Login Screen"),
				"index.php"
			)."
			</div>
			";
			template_display();
		} // end if connected loop
	} // end function freemed::connect
	
	// Function: freemed::drug_widget
	//
	//	Creates a drug selection widget.
	//
	// Parameters:
	//
	//	$varname - Name of the variable which should contain the
	//	drug name.
	//
	//	$formname (optional) - Name of the form that contains this
	//	widget. Defaults to "myform".
	//
	//	$submitname (optional) - Name of the variable that is used
	//	to pass data between the child window and the parent window.
	//	Defaults to "submit_action".
	//
	// Returns:
	//
	//	$widget - XHTML compliant widget code
	//
	function drug_widget ( $varname, $formname="myform", $submitname="submit_action" ) {
		global ${$varname};

		// Switch depending on configuration value
		switch (freemed::config_value('drug_widget_type')) {
			case 'combobox':
			return html_form::combo_widget(
				$varname,
				$GLOBALS['sql']->distinct_values(
					'rx', 'rxdrug'
				)
			);
			break; // end case combobox
			
			// Keep rxlist as the default setting
			case 'rxlist': default:
			// If it is set, show drug name, else widget
			if (!empty(${$varname})) {
				return "<INPUT TYPE=\"HIDDEN\" ".
				"NAME=\"".prepare($varname)."\" ".
				"VALUE=\"".prepare(${$varname})."\"/>".
				${$varname}." ".
				"<input class=\"button\" TYPE=\"BUTTON\" ".
				"onClick=\"drugPopup=window.open(".
				"'drug_lookup.php?varname=".
				urlencode($varname)."&submitname=".
				urlencode($submitname)."&formname=".
				urlencode($formname)."', 'drugPopup'); ".
				"drugPopup.opener=self; return true;\" ".
				"VALUE=\"".__("Change")."\"/>";
			} else {
				return "<input type=\"HIDDEN\" ".
				"name=\"".prepare($varname)."\"/>".
				"<input class=\"button\" type=\"BUTTON\" ".
				"onClick=\"drugPopup=window.open(".
				"'drug_lookup.php?varname=".
				urlencode($varname)."&submitname=".
				urlencode($submitname)."&formname=".
				urlencode($formname)."', 'drugPopup', ".
				"'width=400,height=200,menubar=no,titlebar=no'); ".
				"drugPopup.opener=self; return true;\" ".
				"value=\"".__("Drug Lookup")."\"/>";
			}
			break; // end default action
		} // end switch
	} // end function freemed::drug_widget

	// Function: freemed::get_link_rec
	//
	//	Get a database table record by its "id" field.
	//
	// Parameters:
	//
	//	$id - Value of the id field requested.
	//
	//	$table - Name of the FreeMED database table.
	//
	// Returns:
	//
	//	$rec - Associative array / hash containing key and value
	//	pairs, where the key is the name of the database table
	//	field, and its associated value is the value found in the
	//	database.
	//
	// See Also:
	//	<freemed::get_link_field>
	//
	function get_link_rec($id="0", $table="") {
		global $sql, $_cache;

		// Handle EMRi URL
		if (!(strpos($id, "emri://") === false)) {
			return EMRi::get_link_rec($id);
		}

		// If no database is available, trigger error
		if (empty($table))
			trigger_error ("freemed::get_link_rec: no table provided",
				E_USER_ERROR);

		// Check to see if it's cached
		if (!isset($_cache[$table][$id])) {
			// Perform the actual query
			$result = $sql->query("SELECT * FROM ".addslashes($table)." ".
				"WHERE id='".addslashes($id)."'");
			// Fetch the array from the result into cache
			$_cache[$table][$id] = $sql->fetch_array($result);
		}

		// Return member from cache
		return $_cache[$table][$id];
	} // end function freemed::get_link_rec

	// Function: freemed::get_link_field
	//
	//	Return a single field from a particular database table
	//	from its "id" field.
	//
	// Parameters:
	//
	//	$id - Value of the id field requested.
	//
	//	$table - Name of the FreeMED database table.
	//
	//	$field - Name of the field in the database table.
	//
	// Returns:
	//
	//	$val - Scalar value of the database table field.
	//
	// See Also:
	//	<freemed::get_link_rec>
	//
	function get_link_field($id, $table, $field="id") {
		// Die if no table was passed
		if (empty($table))
			trigger_error ("freemed::get_link_field: no table provided",
				E_USER_ERROR);

		// Retrieve the entire record
		$this_array = freemed::get_link_rec($id, $table);

		// TODO: Get this to automatically deserialize serialized
		// data so that we can transparently get arrays. Probably
		// would break some phenomenal amount of code.

		// Return just the key asked for
		return $this_array["$field"];
	} // end function freemed::get_link_field

	// Function: freemed::itemlist_conditions
	//
	//	Creates an SQL "WHERE" clause based on search information
	//	provided by <freemed::display_itemlist>, as used by most
	//	of FreeMED's modules.
	//
	// Parameters:
	//
	//	$where (optional) - Boolean value, whether a "WHERE" should
	//	be prepended to the returned query. Defaults to true.
	//
	// Returns:
	//
	// 	$clause - SQL query "WHERE" clause
	//
	function itemlist_conditions($where = true) {
		if (strlen($GLOBALS['_s_val']) > 0) {
			$field = addslashes($GLOBALS['_s_field']);
			$val = addslashes($GLOBALS['_s_val']);
			return ( $where ? " WHERE ( " : " AND ( " ).
				$field." = '".$val."' OR ".
				$field." LIKE '%".$val."' OR ".
				$field." LIKE '".$val."%' OR ".
				$field." LIKE '%".$val."%' ) ";
		}
	} // end function freemed::itemlist_conditions

	// Function: freemed::image_filename
	//
	//	Resolves a stored document's full path based on the qualifiers
	//	presented.
	//
	// Parameters:
	//
	//	$patient - Patient identifier
	//
	//	$record - Record identifier of the "images" table
	//
	//	$type - File type (usually "djvu")
	//
	//	$img_store (optional) - Boolean, whether or not
	//	the relative pathname will be prepended (usually "img/store/").
	//
	// Returns:
	//
	//	The relative path and file name of the image.
	//
	function image_filename($patient, $record, $type, $img_store = true) {
		$m = md5($patient);
		return ($img_store ? 'img/store/' : '' ).
			$m[0].$m[1].'/'.
			$m[2].$m[3].'/'.
			$m[4].$m[5].'/'.
			$m[6].$m[7].'/'.
			substr($m, -(strlen($m)-8)).
			'/'.$record.'.'.$type;
	} // end function::image_filename

	// Function: freemed::module_check
	//
	//	Determines whether a module is installed in the system,
	//	and optionally whether it is above a certain minimum
	//	versioning number.
	//
	// Parameters:
	//
	//	$module - Name of the module
	//
	//	$minimum_version (optional) - The minimum allowable version
	//	of the specified module. If this is not specified, any
	//	version will return true.
	//
	// Returns:
	//
	//	$installed - Boolean, whether the module is installed
	//
	function module_check ($module, $minimum_version="0.01")
	{
		static $_config; global $sql;

		// cache all modules  
		if (!is_array($_config)) {
			unset ($_config);
			$query = $sql->query("SELECT * FROM module");
			while ($r = $sql->fetch_array($query)) {
				extract ( $r );
				$_config["$module_name"] = $module_version;
			} // end of while results
		} // end caching modules config
	
		// check in cache for version > minimum_version
		return version_check($_config["$module"], $minimum_version);
	} // end function freemed::module_check

	// Function: freemed::module_get_value
	//
	//	Gets a cached module value (such as "MODULE_NAME", etc)
	//	from the module cache.
	//
	// Parameters:
	//
	//	$module - Name of the module
	//
	//	$key - Variable name in question
	//
	// Returns:
	//
	//	$val - Value of the variable name in question 
	//
	// See Also:
	//	<freemed::module_get_meta>
	//
	function module_get_value ($module, $key) {
		// Get module list object
		$module_list = freemed::module_cache();

		// Not in the list, then we just skip it
		if (!$module_list->check_for($module)) {
			return false;
		}

		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $k => $v) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v[$key];
			}
		}

		// If all else fails, return false
		return false;
	} // end function freemed::module_get_value

	// Function: freemed::module_get_meta
	//
	//	Gets cached metainformation for the specified module in
	//	the module cache. Acts as a wrapper for
	//	<freemed::module_get_value>.
	//
	// Parameters:
	//
	//	$module - Name of the module
	//
	//	$key - Hash index of the metainformation in question
	//
	// Returns:
	//
	//	$val - Value of the metainformation in question 
	//
	// See Also:
	//	<freemed::module_get_value>
	//
	function module_get_meta ($module, $key) {
		$module_list = freemed::module_cache();

		// Not in the list, then we just skip it
		if (!$module_list->check_for($module)) {
			return false;
		}

		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $k => $v) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v['META_INFORMATION'][$key];
			}
		}

		// If all else fails, return false
		return false;
	} // end function freemed::module_get_meta

	// Function: freemed::module_cache
	//
	//	Provides global access to a PHP.module_list object with
	//	cached module information.
	//
	// Returns:
	//
	//	$cache - An object (PHP.module_list) containing the
	//	cached module information.
	//
	function module_cache () {
		static $_cache;
		if (!isset($_cache)) {
			$_cache = CreateObject(
				'PHP.module_list',
				PACKAGENAME,
				array(
					'cache_file' => 'data/cache/modules'
				)
			);
		}
		return $_cache;
	} // end function freemed::module_cache

	// Function: freemed::module_handler
	//
	//	Returns the list of modules associated with a certain handler.
	//
	// Parameters:
	//
	//	$handler - Scalar name of the handler. This is case sensitive.
	//
	// Returns:
	//
	//	$modules - Array of modules which are associated with the
	//	specified handler.
	//
	function module_handler ($handler) {
		// Get module list object
		$module_list = freemed::module_cache();

		// Loop through modules
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $k => $v) {
			// Check to see if this handler is registered
			if (!empty($v['META_INFORMATION']['__handler']["$handler"])) {
				$handler_data[$v['MODULE_CLASS']] = $v['META_INFORMATION']['__handler']["$handler"];
			}
		}

		// Return composite
		return $handler_data;
	} // end function freemed::module_handler

	// Function: freemed::module_lookup
	//
	//	Lookup the module name as needed by FreeMED's module calls,
	//	but by the class name of the module.
	//
	// Parameters:
	//
	//	$class - Class name of the module
	//
	// Returns:
	//
	//	$module - MODULE_NAME of the specified module.
	//
	function module_lookup ($module) {
		// Get module list object
		$module_list = freemed::module_cache();

		// Not in the list, then we just skip it
		if (!$module_list->check_for($module)) {
			return false;
		}

		// Use protected __phpwebtools array to get module name
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] as $k => $v) {
			if (strtolower($v['MODULE_CLASS']) == strtolower($module)) {
				return $v['MODULE_NAME'];
			}
		}

		// If all else fails, return false
		return false;
	} // end function freemed::module_lookup

	// Function: freemed::module_register
	//
	//	Registers module or newer version of module in FreeMED's
	//	global module registry.
	//
	// Parameters:
	//
	//	$module - Name of module (must be resolved using
	//	freemed::module_lookup, or by using MODULE_NAME).
	//
	//	$version - Version of module to register.
	//
	function module_register ($module, $version) {
		global $sql;

		// check for modules  
		if (!freemed::module_check($module, $version)) {
			// Check for preexisting module record
			$q = $sql->query("SELECT * FROM module ".
				"WHERE module_name='".
				addslashes($module)."'");
			if (!$sql->results($q)) {
				$query = $sql->query($sql->insert_query(
					'module',
					array (
						'module_name' => $module,
						'module_version' => $version
					)
				));
			} else {
				// Perform update query
				$query = $sql->query($sql->update_query(
					'module',
					array (
						'module_version' => $version
					),
					array (
						'module_name' => $module
					)
				));
			}
			return (!empty($query));
		} // end checking for module installed

		return true;
	} // end function freemed::module_register

	// Function: freemed::module_version
	//
	//	Get the current version number of a particular module in
	//	FreeMED from the module database table.
	//
	// Parameters:
	//
	//	$module - Name of module (must be resolved using
	//	freemed::module_lookup, or by using MODULE_NAME).
	//
	function module_version ($module) {
		static $_config; global $sql;

		// cache all modules  
		if (!is_array($_config)) {
			unset ($_config);
			$query = $sql->query("SELECT * FROM module");
			while ($r = $sql->fetch_array($query)) {
				extract ( $r );
				$_config["$module_name"] = $module_version;
			} // end of while results
		} // end caching modules config

		// check in cache for version
		return $_config["$module"];
	} // end function freemed::module_version

	// Function: freemed::multiple_choice
	//
	//	Create a multiple-choice widget
	//
	// Parameters:
	//
	//	$sql_query
	//
	//	$display_field - Hash of the field display format, with
	//	'##' surrounding the members of the table. (For example:
	//	'##phylname##, ##phyfname##' from a query on the physician
	//	table would print their last name and first name separated
	//	by a comma.)
	//
	//	$select_name - Name of the variable that the widget is
	//	specifying.
	//
	//	$blob_data - The actual compressed data string which contains
	//	the array of values.
	//
	//	$display_all (optional)
	//
	// Returns:
	//
	//	XHTML-compliant multiple choice widget code.
	//
	function multiple_choice ($sql_query, $display_field, $select_name,
			$blob_data, $display_all=true) {
		global $sql;
		$buffer = "";

		$brackets = "[]";
		$result = $sql->query ($sql_query); // check
		$all_selected = fm_value_in_string ($blob_data, "-1");

		$buffer .= "<select NAME=\"".$select_name."[]\" multiple SIZE=\"5\">\n";
		if ($display_all) {
			$buffer .= "<option VALUE=\"-1\" ".
			($all_selected ? "selected" : "").">".__("ALL")."</option>\n";
		}
	
		if ( $sql->results ($result) ) {
			while ($r = $sql->fetch_array ($result)) {
				if (!(strpos ($display_field, "##") === false)) {
					$displayed = ""; // set as null
					$f_split = explode ("##", $display_field);
					foreach ($f_split AS $f_k => $f_v) {
						if (!($f_k & 1) ) {
							$displayed .= $f_v;
						} else {
							$displayed .= prepare($r[$f_v]);
						}
					}
				} else { // if it is only one field
					$displayed = stripslashes($r[$display_field]);
				} // end if-else displayed loop
				$buffer .= "
				<option VALUE=\"".prepare($r['id'])."\" ".
				( (fm_value_in_string ($blob_data, $r['id'])) ? "selected" : "" ).
				">$displayed".( $debug ? " [".$r['id']."]" : "" )."</option>\n";
			} // end while
		} // end checking for results
		$buffer .= "</select>\n"; // end the select tag
		return $buffer;
	} // end function freemed::multiple_choice

	// Function: freemed::patient_box
	//
	//	Create a "patient box" with quick access to various patient
	//	functions.
	//
	// Parameters:
	//
	//	$patient_object - An object of type 'FreeMED.Patient' which
	//	encapsulates the selected patient's data.
	//
	// Returns:
	//
	//	XHTML compliant patient box widget code.
	//
	function patient_box ($patient_object) {
		// Catch to make sure it's an actual object
		if (!is_object($patient_object)) return NULL;
		
		// Make sure template functions are included
		include_once('lib/template/'.$GLOBALS['template'].'/lib.php');
	
		// empty buffer
		$buffer = "";

		// top of box
		$buffer .= "
    <div ALIGN=\"CENTER\">
    <table BORDER=\"0\" CELLSPACING=\"0\" CELLPADDING=\"5\" WIDTH=\"100%\">
     <tr CLASS=\"patientbox\"
	onClick=\"window.location='manage.php?id=".
		urlencode($patient_object->id)."'; return true;\"
      ><td VALIGN=\"CENTER\" ALIGN=\"LEFT\">
      <a HREF=\"manage.php?id=".urlencode($patient_object->id)."\"
       CLASS=\"patientbox\" NAME=\"patientboxlink\"><b>".
       $patient_object->fullName().
      "</b></a>
     </td><td ALIGN=\"CENTER\" VALIGN=\"CENTER\">
      ".( (!empty($patient_object->local_record["ptid"])) ?
          $patient_object->idNumber() : "(no id)" )."
     </td><td ALIGN=\"CENTER\" VALIGN=\"CENTER\">
	".template::patient_box_iconbar($patient_object->id)."
     </td><td VALIGN=\"CENTER\" ALIGN=\"RIGHT\">
      <font COLOR=\"#cccccc\">
       <small>".$patient_object->age()." old, DOB ".
        $patient_object->dateOfBirth()."</small>
      </font>
     </td></tr>
    </table>
    </div>
		";
  
		// return buffer
		return $buffer;
	} // end function freemed::patient_box

	// Function: freemed::patient_widget
	//
	//	Create a patient selection widget
	//
	// Parameters:
	//
	//	$varname - The name of the variable that this widget
	//	contains data for.
	//
	//	$formname (optional) - Name of the form that this widget is
	//	contained in. Defaults to "myform".
	//
	//	$submitname (optional) - The name of the submit button which
	//	is passed to the child window that is created. Defaults to
	//	"submit_action".
	//
	// Returns:
	//
	//	XHTML compliant patient selection widget code.
	//
	function patient_widget ( $varname, $formname="myform", $submitname="submit_action" ) {
		global ${$varname};

		// If it is set, show patient name, else widget
		if (${$varname} > 0) {
			$this_patient = CreateObject('FreeMED.Patient', ${$varname});	
			return "<input TYPE=\"HIDDEN\" ".
				"NAME=\"".prepare($varname)."\" ".
				"VALUE=\"".prepare(${$varname})."\"/>".
				$this_patient->fullName()." ".
				"<input class=\"button\" TYPE=\"BUTTON\" ".
				"onClick=\"patientPopup=window.open(".
				"'patient_lookup.php?varname=".
				urlencode($varname)."&submitname=".
				urlencode($submitname)."&formname=".
				urlencode($formname)."', 'patientPopup', ".
				"'width=400,height=200,menubar=no,titlebar=no'); ".
				"patientPopup.opener=self; return true;\" ".
				"VALUE=\"".__("Change")."\"/>\n".
				"<input class=\"button\" TYPE=\"BUTTON\" ".
				"onClick=\"document.".$formname.".".
					$varname.".value = ''; ".
					"document.myform.submit();\" ".
				"VALUE=\"".__("Remove")."\"/>\n";
		} else {
			return "<input TYPE=\"HIDDEN\" ".
				"NAME=\"".prepare($varname)."\"/>".
				"<input TYPE=\"BUTTON\" ".
				"onClick=\"patientPopup=window.open(".
				"'patient_lookup.php?varname=".
				urlencode($varname)."&submitname=".
				urlencode($submitname)."&formname=".
				urlencode($formname)."', 'patientPopup'); ".
				"patientPopup.opener=self; return true;\" ".
				"VALUE=\"".__("Patient Lookup")."\" ".
				"class=\"button\" />";
		}
	} // end function freemed::patient_widget

	// Function: freemed::query_to_array
	//
	//	Dumps the output of an SQL query to a multidimentional
	//	hashed array.
	//
	// Parameters:
	//
	//	$query - Text SQL query
	//
	// Returns:
	//
	//	Multidimentional hashed array.
	//
	function query_to_array ( $query ) {
		global $sql;
		unset ($this_array);

		$result = $sql->query($query);

		if (!$sql->results($result)) return array("" => "");

		$index = 0;
		while ($r = $sql->fetch_array($result)) {
			foreach ($r AS $k => $v) {
				$this_array[$index][(stripslashes($k))] =
					stripslashes($v);
			}
			$index++;
		}

		if ($index == 1) {
			return $this_array[0];
		} else {
			return $this_array;
		}
	} // end function freemed::query_to_array

	// Function freemed::race_widget
	//
	//	Create HL7 v2.3.1 compliant race widget (table 0005)
	//
	// Parameters:
	//
	//	$varname - Name of the variable which contains this data.
	//
	// Returns:
	//
	//	$widget - XHTML compliant race widget code.
	//
	function race_widget ( $varname ) {
		// HL7 v2.3.1 compliant race widget (table 0005)
		return html_form::select_widget($varname,
			array (
				__("unknown race") => '7',
				__("Hispanic, white") => '1',
				__("Hispanic, black") => '2',
				__("American Indian or Alaska Native") => '3',
				__("Black, not of Hispanic origin") => '4',
				__("Asian or Pacific Islander") => '5',
				__("White, not of Hispanic origin") => '6'
			)
		);
	} // end function freemed::race_widget

	// Function freemed::religion_widget
	//
	//	Create HL7 v2.3.1 compliant religion widget (table 0006)
	//
	// Parameters:
	//
	//	$varname - Name of the variable which contains this data.
	//
	// Returns:
	//
	//	$widget - XHTML compliant religion widget code.
	//
	function religion_widget ( $varname ) {
		// HL7 v2.3.1 compliant race widget (table 0006)
		return html_form::select_widget($varname,
			array (
				__("Catholic") => '0',
				__("Jewish") => '1',
				__("Eastern Orthodox") => '2',
				__("Baptist") => '3',
				__("Methodist") => '4',
				__("Lutheran") => '5',
				__("Presbyterian") => '6',
				__("United Church of God") => '7',
				__("Episcopalian") => '8',
				__("Adventist") => '9',
				__("Assembly of God") => '10',
				__("Brethren") => '11',
				__("Christian Scientist") => '12',
				__("Church of Christ") => '13',
				__("Church of God") => '14',
				__("Disciples of Christ") => '15',
				__("Evangelical Covenant") => '16',
				__("Friends") => '17',
				__("Jehovah's Witness") => '18',
				__("Latter-Day Saints") => '19',
				__("Islam") => '20',
				__("Nazarene") => '21',
				__("Other") => '22',
				__("Pentecostal") => '23',
				__("Protestant, Other") => '24',
				__("Protestant, No Denomenation") => '25',
				__("Reformed") => '26',
				__("Salvation Army") => '27',
				__("Unitarian; Universalist") => '28',
				__("Unknown/No preference") => '29',
				__("Native American") => '30',
				__("Buddhist") => '31'
			)
		);
	} // end function freemed::religion_widget

	// Function: freemed::secure_filename
	//
	//	Remove potentially hazardous characters from filenames
	//
	// Parameters:
	//
	//	$original - Original filename
	//
	// Returns:
	//
	//	$sanitized - Sanitized filename
	//
	function secure_filename ( $filename ) {
		// Items to remove
		$secure_these = array (
			"\\",
			".",
			"/",
			"|"
		);

		// Pass to internal variable
		$this_filename = $filename;

		// Perform replacements
		foreach ( $secure_these AS $drek => $secure_var ) {
			$this_filename = str_replace (
				"\$".$secure_var,
				"",
				$this_filename
			);
		}

		// Return secured filename
		return $this_filename;
	} // end function freemed::secure_filename

	// Function: freemed::store_image
	//
	//	Stores posted file in scanned document image store.
	//
	// Parameters:
	//
	//	$patient_id - Patient identifier from the patient table.
	//	Do not pass a patient object.
	//
	//	$varname - The variable name describing the file that was
	//	posted using the HTTP POST method.
	//
	//	$type - (optional) This is either 'identification' in the
	//	case of an identifying photograph, or the record number
	//	of this document in the scanned documents table
	//
	//	$encoding - (optional) Type of DjVu encoding. Currently
	//	'cjb2' and 'c44' encodings are supported.
	//
	// Returns:
	//
	//	Name of file if successful.
	//
	function store_image ( $patient_id=0, $varname, $type="identification", $encoding='cjb2' ) {
		global ${$varname};

		// Check for valid patient id
		if ($patient_id < 1) return false;

		// Determine extension
		$file_parts = explode (".", $_FILES[$varname]["name"]);
		$ext = $file_parts[count($file_parts)-1];

		// If there is no extension, die
		if (strlen($ext) < 3) { return false; }

		// Get temporary name
		$image = $_FILES[$varname]["tmp_name"];

		// If temp name doesn't exist, return false
		if (empty($image)) return false;

		// Create proper path
		$mkdir_command = 'mkdir -p '.PHYSICAL_LOCATION.'/'.
			dirname(
				freemed::image_filename(
					$patient_id,
					$type,
					'djvu'
				)
			);
		//print "mkdir_command = $mkdir_command<br/>\n";
		$output = `$mkdir_command`;
		//print $mkdir_output."<br/>\n";

		// Process depending on 
		switch (strtolower($ext)) {
			/*
			case "jpg":
			case "jpeg":
				// Simple JPEG handler: copy
				$name = freemed::image_filename(
					$patient_id,
					$type,
					'djvu'
				);
				copy ($image, "./".$name);
				return $name;
				break; // end handle JPEGs
			*/

			default:
				// More complex: use imagemagick
				$name = freemed::image_filename(
					$patient_id,
					$type,
					'djvu'
				);
				// Convert to PBM
				$command = "/usr/bin/convert ".
					freemed::secure_filename($image).
					" ".PHYSICAL_LOCATION."/".
					freemed::image_filename(
						$patient_id,
						$type,
						'djvu.'.
						( $encoding=='c44' ?
						'jpg' : 'pbm' )
					);
				$output = `$command`;
				//print ($output)."<br>";

				// Convert to DJVU
				switch ($encoding) {
					case 'c44':
						$ee = '/usr/bin/c44';
						break;
					case 'cjb2':
					default:
						$ee = '/usr/bin/cjb2';
						break;
				}
				$command = $ee." ".
					PHYSICAL_LOCATION."/".
					freemed::image_filename(
						$patient_id,
						$type,
						'djvu.'.
						( $encoding=='c44' ?
						'jpg' : 'pbm' )
					)." ".
					PHYSICAL_LOCATION."/".
					freemed::image_filename(
						$patient_id,
						$type,
						'djvu'
					);
				//print "command = $command<br/>\n";
				//print "<br/>".exec ($command)."<br/>\n";
				//print "<br/>".`$command`."<br/>\n";

				// Remove PBM
				unlink(PHYSICAL_LOCATION.
					freemed::image_filename(
						$patient_id,
						$type,
						'djvu.'.
						( $encoding=='c44' ?
						'jpg' : 'pbm' )
					)
				);
				return $name;
				break; // end handle others
		} // end checking by extension
	} // end function freemed::store_image

	// Function: freemed::support_djvu
	//
	//	Determines whether the current browser supports DjVu.
	//
	// Parameters:
	//
	//	$browser - PHP.browser_detect object.
	//
	// Returns:
	//
	//	Boolean, whether DjVu is supported or not.
	//
	function support_djvu ( $browser ) {
		// Assume true
		$support = true;

		return $support;
	} // end function freemed::support_djvu

	// Function: freemed::user_flag
	//
	//	Determine if a particular user flag is set for the current
	//	user.
	//
	// Parameters:
	//
	//	$flag - The flag in question. This should be something
	//	like USER_ADMIN, USER_DELETE, etc.
	//
	// Returns:
	//
	//	True if the flag is set for the current user.
	//
	function user_flag ( $flag ) {
		global $database, $sql;
		static $userlevel;

		// Extract authdata from SESSION
		$authdata = $_SESSION['authdata'];

		// Check for cached userlevel
		if (isset($userlevel)) { return ($userlevel & $flag); }

		// Check for null user
		if (($authdata["user"]<1) or (!isset($authdata["user"]))) {
			$userlevel = 0;
			return false; // if no user, return 0
		}

		if ($authdata["user"] == 1) {
			// Anything but disabled user, return true
			if ($flag == USER_DISABLED) {
				return false;
			} else {
				return true;
			}
		} else {
			$result = $sql->query("SELECT * FROM user
				WHERE id='".addslashes($authdata["user"])."'");

			// Check for improper results, return "unauthorized"
			if (!$sql->results($result) or ($sql->num_rows($result) != 1)) {
				// Set so that nothing works
				$userlevel = USER_DISABLED;
				if ($flag == USER_DISABLED) {
					return true;
				} else {
					return false;
				}
			}

			// Get results
			$r = $sql->fetch_array($result);

			// Set $userlevel (which is cached)
			$userlevel = $r["userlevel"];

			// Return the answer...
			return ($userlevel & $flag);
		} // end else loop checking for name
	} // end function user_flag

	// Function: freemed::verify_auth
	//
	//	Determines if the current user is authenticated when dealing
	//	with PHP sessions. This is not used for basic authentication.
	//
	// Returns:
	//
	//	True if the current session is authenticated.
	//
	function verify_auth ( ) {
		global $debug, $Connection, $sql;

		// Associate "SESSION" with proper session variable
		$PHP_SELF = $_SERVER['PHP_SELF'];
 
		// Do we have to check for _username?
		$check = !empty($_REQUEST['_username']);
	
		// Check for authdata array
		if (is_array($_SESSION['authdata'])) {
			// Check to see if ipaddr is set or not...
			if (!SESSION_PROTECTION) {
				return true;
			} else {
				if ( !empty($_SESSION['ipaddr']) ) {
					if ($_SESSION['ipaddr'] == $_SERVER['REMOTE_ADDR']) {
						// We're already authorized
						return true;
					} else {
						// IP address has changed, ERROR
						unset($_SESSION['ipaddr']);
						print "IP ADDRESS<BR>\n";
						return false;
					} // end checking ipaddr
				} else {
					// Force check if no ip address is present. This
					// should get around null IPs getting set by
					// accident without compromising security.
					$check = true;
				} // end if isset ipaddr
			} // end checking for SESSION_PROTECTION
		} 
		
		if ($check) {
			// Find this user
  			$result = $sql->query ("SELECT * FROM user ".
				"WHERE username = '".addslashes($_REQUEST['_username'])."'");
	
			// If the user isn't found, false
			if (!$sql->results($result)) { return false; }
	
			// Get information
			$r = $sql->fetch_array ($result);

			$login_md5=md5($_REQUEST['_password']);

			$user=$_REQUEST['_username'];

			if((LOGLEVEL<1)||(LOG_HIPAA || LOG_LOGIN))
			{
			syslog(LOG_INFO,"API.php|verify_auth login attempt $user ");
			}

			$db_pass=$r['userpassword'];		

	//		This is insecure and should not be used
	//		It is here for debugging purposes only...
	//		if(LOG_MD5||(LOGLEVEL<1)) // Md5 password Logging
	//		{
	//		syslog(LOG_INFO,"API.php|verify_auth Password entered__ $login_md5 ");
	//		}

			

	
			// Check password
			if ($login_md5 == $r['userpassword']) {
				// Set session vars
				$_SESSION['authdata'] = array (
					"username" => $_REQUEST['_username'],
					"user" => $r['id']
				);
				// Set ipaddr for SESSION_PROTECTION
				$_SESSION['ipaddr'] = $_SERVER['REMOTE_ADDR'];
	
				// Authorize
				if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){syslog(LOG_INFO,"API.php|verify_auth successful login");}		
				return true;
			} else { // check password
				// Failed password check
				unset ( $_SESSION['authdata'] );
				unset ( $_SESSION['ipaddr'] );
	                       if(((LOGLEVEL<1)||LOG_ERRORS)||(LOG_HIPAA || LOG_LOGIN)){ syslog(LOG_INFO,"API.php|verify_auth failed login");	}	
				return false;
			} // end check password
		} // end of checking for authdata array
	} // end method freemed::verify_auth

} // end namespace/class freemed

class EMRi {
	// EMRi Server information
	var $EMRi_server_host;
	var $EMRi_server_port;
	var $EMRi_server_uri;
	var $EMRi_user;
	var $EMRi_password;

	function EMRi () {
		$this->get_server_name();
		// FIXME: Kludge for fqdn
		$this->fqdn = 'test';
		// FIXME: EXTREME KLUDGE for testing purposes only!
		$this->EMRi_user = 'root';
		$this->EMRi_password = 'password';
	} // end constructor EMRi

	// TODO: finish EMRi linking functions
	function get_link_rec ( $url ) {
		die("STUB: EMRi::get_link_rec()");
	} // end method EMRi->get_link_rec

	function get_server_name () {
		// This should actually get the best EMRi server.
		// Barring that, we assign static
		$this->EMRi_server_host = 'localhost';
		$this->EMRi_server_port = '3674';
		$this->EMRi_server_uri  = '/RPC2';
	} // end method EMRi->get_server_name

	//----- Method calls for hosts and servers ---------------------------

	function host_method ($fqdn, $method, $argv) {
		// Make sure we're connected
		if (!isset($this->EMRi_server_host)) $this->get_server_name();

		// Call the proper function on the server
		return xu_rpc_http_concise(array(
			'method' => $method,
			'args'   => $argv,
			'host'   => $this->EMRi_server_host,
			'port'   => $this->EMRi_server_port,
			'uri'    => $this->EMRi_server_uri,
			'debug'  => 1,
			'output' => NULL,
			'secure' => 0, // this needs to be fixed!

			// FIXME: These should be pulled from the database
			'user'   => 'root',
			'pass'   => 'password'
		));
	} // end method EMRi->host_method

	function server_method ($method, $argv) {
		static $client;
	
		// Make sure we're connected
		if (!isset($this->EMRi_server_host)) $this->get_server_name();

		// Check for prexisting client
		if (!is_object($client)) {
			$client = CreateObject(
				'PHP.xmlrpc_client',
				$this->EMRi_server_uri, // URI
				$this->EMRi_server_host, // HOST
				$this->EMRi_server_port // PORT
			);
			$client->setDebug();
		}

		// Set proper credentials
		$client->setCredentials($this->EMRi_user, $this->EMRi_password);

		// TODO: Pre-call argument processing

		// Call with function
		$response = $client->send (
			CreateObject(
				'PHP.xmlrpcmsg',
				$method,
				$argv // arguments
			)
		);

		// Return deserialized version
		return $response->deserialize();
	} // end method EMRi->server_method

	//-------------------------------------------------------------------


	//----- Authenticate methods ----------------------------------------

	function authenticate_user ($user, $pass) {
		// Call EMRi.Authentication.user on server
		return $this->server_method(
			"EMRi.Authentication.user",
			array(
				$user,
				$pass
			)
		);
	} // end method EMRi->authenticate_user

	//----- Patient methods ---------------------------------------------

	function patient_index ($pid) {
		// If it's an array, send the array, if not,
		// send an array wrapper for the scalar value
		if (!is_array($pid)) {
			$patients = array ($pid);			
		} else {
			$patients = $pid;
		}

		foreach ($patients AS $k => $v) {
			// Process scalar
			$this_patient = freemed::get_link_rec($v,"patient");

			// Add to param
			$param[] = array (
				'fqdn'       => $this->fqdn,
				'pid'        => $this_patient['ptssn'],
				'last_name'  => $this_patient['ptlname'],
				'first_name' => $this_patient['ptfname'],
				'version'    => $this_patient['version']
			);
			
		}

		// Call the method
		return $this->server_method(
			"EMRi.Patient.index",
			array($param)
		);
	} // end method EMRi->patient_index

	function patient_search ($pid) {
		// If it's an array, send the array, if not,
		// send an array wrapper for the scalar value
		if (!is_array($pid)) {
			$param = array ("pid" => $pid);			
		} else {
			$param = $pid;
		}

		// Call the method
		$result = $this->server_method(
			"EMRi.Patient.search",
			array($param)
		);

		// For now, return the raw result
		return $result;
	} // end method EMRi->patient_search

} // end namespace/class EMRi

//------------------ NON NAMESPACE FUNCTIONS ---------------------

// Function: freemed_alternate
//
//	Create alternating texts. Used mostly for alternating
//	row displays, either as CLASS tags or as BGCOLOR tags.
//
// Parameters:
//
//	$elements - Array of elements which are to be alternated
//	between. Defaults to array ('cell', 'cell_alt').
//
// Returns:
//
//	The next element in the circular loop of the presented
//	array.
//
function freemed_alternate ($_elements) {
	static $_pos;

	if (!is_array($_elements)) {
		// By default, cell and cell_alt
		$elements = array ("cell", "cell_alt");
	} else {
		// Otherwise, pull into local scope
		$elements = $_elements;
	}

	if (!isset($_pos)) {
		// If there is no current position, set to initial state
		$_pos = 0;
	} else {
		// Otherwise increment and wrap around
		$_pos++;
		if ($_pos >= count($elements)) { $_pos = 0; }
	}

	return $elements[$_pos];
} // end function freemed_alternate

// Function: freemed_display_actionbar
//
//	Creates the ADD/RETURN TO MENU bar present in most FreeMED
//	modules.
//
// Parameters:
//
//	$page_name - (optional) Name of the current page.
//
//	$ref - (optional) Name of the referring page. If this is not
//	explicitly set, the global variable '_ref' acts as the
//	default value.
//
// Returns:
//
//	XHTML compliant actionbar widget.
//
function freemed_display_actionbar ($this_page_name="", $__ref="") {
	global $page_name, $patient, $_ref, $_pass, $module;

	$buffer = "";

	if (!empty($_ref)) $__ref = $_ref;

	if ($this_page_name=="") $this_page_name = $page_name;

	// No reference, return to homepage
	if (empty($__ref)) { $_ref="main.php"; }

	// Assume return to management if nothing else
	if ($GLOBALS["return"] == "manage") {
		$__ref = "manage.php?id=$patient";
	}

    // show the actual bar, build with page_name reference
    // and global variables
	global $globaladdcounter; $globaladdcounter++;
	global $template;

	$buffer .= "
    <table CLASS=\"reverse\" WIDTH=\"100%\" BORDER=\"0\"
     CELLSPACING=\"0\" CELLPADDING=\"3\">
    <tr CLASS=\"reverse\">
    <td ALIGN=\"LEFT\"><a HREF=\"$this_page_name?".
    	( isset($_pass) ? $_pass.'&' : '' )."module=".urlencode($module)."&".
	"action=addform".
     ( !empty($patient) ? "&patient=".urlencode($patient) : "" )."\"
	onMouseOver=\"window.status='".__("Add")."'; return true;\"
	onMouseOut=\"window.status=''; return true;\"
	><small><b>".__("ADD")."</b></small></a></td>
    <td WIDTH=\"30%\">&nbsp;</td>
    <td ALIGN=\"RIGHT\"><a HREF=\"$__ref\" CLASS=\"reverse\"
     ><small><b>".__("RETURN TO MENU")."</b></small></a></td>
    </tr></table>
  	";
	return $buffer;

} // end function freemed_display_actionbar

// Function: freemed_display_itemlist
//
//	Creates a paginated list display based on formatting data for
//	a particular result set. This should be used in conjunction
//	with <freemed::itemlist_conditions> to produce a proper
//	SQL query.
//
// Parameters:
//
//	$result - SQL query passed to the display.
//
//	$page_link - Current page name.
//
//	$control_list - List of column names and database table column
//	names, as an associative array. (Example: array (
//	__("Date") => 'procdt', __("Procedure Code") => 'proccpt' ) )
//
//	$blank_list - Array of values for the columns describing what a
//	blank entry should be displayed as.
//
//	$xref_list - (optional) Associative array describing cross
//	table references. For example, if your column 'proccpt'
//	described a CPT code, you could use 'cpt' => 'cptcode' to
//	describe the table name ('cpt') and the column to be displayed
//	name ('cptcode'), which would be determined by the value of
//	the corresponding column in $control_list.
//
//	$cur_page_var - (optional) Pagination tracking variable. The
//	default is 'this_page'.
//
//	$index_field - (optional) Currently this is unused, and should
//	be passed as '' or NULL.
//
//	$flags - (optional) Bitfield of operators, such as ITEMLIST_MOD |
//	ITEMLIST_DEL.
//
// Returns:
//
//	XHTML compliant item listing with search widgets.
//
function freemed_display_itemlist ($result, $page_link, $control_list, 
                           $blank_list, $xref_list="",
			   $cur_page_var="this_page",
			   $index_field="", $flags=-1)
{
  global $_ref, $record_name;
  global $modify_level, $delete_level, $patient, $action, $module;
  global $page_name, ${$cur_page_var}, $max_num_res;
  global $_s_field, $_s_val, $_pass, $sql;

  //echo "page name $page_name this $this->page_name module $module<BR>";
  
  if ($flags==-1) $flags=(ITEMLIST_MOD|ITEMLIST_DEL);

  // pull current page name
  if (empty ($page_link)) {
    $parts = explode("?", basename($GLOBALS["REQUEST_URI"]));
    $page_link = $parts[0];
  } // end of pull current page name

  if ( (isset($module)) AND (!empty($module)) )
  {
	// if we are in a module pull the module loader
    // name for paging
    $parts = explode("?", basename($GLOBALS["REQUEST_URI"]));
    $page_name = $parts[0];
  }

  // TODO: make sure $control_list is an array, verify the inputs, yadda yadda

  $num_pages = ceil($sql->num_rows($result)/$max_num_res);
  if (${$cur_page_var}<1 OR ${$cur_page_var}>$num_pages) ${$cur_page_var}=1;

  if (strlen(${$cur_page_var})>0) { // there's an offset
    for ($i=1;$i<=(${$cur_page_var}-1)*$max_num_res;$i++) {
      $herman = $sql->fetch_array($result); // offset the proper number of rows
    }
  }

  $buffer="";

  $buffer .= "
    <!-- Begin itemlist Table -->
    <table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"2\" BORDER=\"0\"
     ALIGN=\"CENTER\" VALIGN=\"MIDDLE\" CLASS=\"itemlistbox\">
    <tr>
     <td ALIGN=\"CENTER\">
      <big><b>".__($record_name)."</b></big>
     </td>
    </tr>".
    
   ( ((strlen($cur_page_var)>0) AND ($num_pages>1)) ? "
   <tr ALIGN=\"CENTER\"><td CLASS=\"reverse\">
    <table BORDER=\"0\" CELLPADDING=\"2\" CELLSPACING=\"0\">
     <form METHOD=\"POST\" ACTION=\"$page_name\">
    ".
    
    ((${$cur_page_var} > 1) ? "
    <tr><td>".template::link_button(
        __("Previous"),
        "$page_name?$cur_page_var=".(${$cur_page_var}-1).
        ((strlen($_s_field)>0) ? "&_s_field=$_s_field&_s_val="
        .prepare($_s_val)."" : "").
        "&module=$module&action=$action")."
    </td>
    " : "" )
    
    ."<td CLASS=\"reverse\">
     ".__("Page"). 
     fm_number_select($cur_page_var, 1, $num_pages, 1, false, true).
	" of ".$num_pages."
     <input TYPE=\"HIDDEN\" NAME=\"action\"  VALUE=\"".prepare($action)."\"/>
     <input TYPE=\"HIDDEN\" NAME=\"module\"  VALUE=\"".prepare($module)."\"/>
     <input TYPE=\"HIDDEN\" NAME=\"patient\" VALUE=\"".prepare($patient)."\"/>
     <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Go")."\"/>
    </td>".
    
    ((${$cur_page_var} < $num_pages) ? "
    <td>".template::link_button(
        __("Next"),
        "$page_name?$cur_page_var=".(${$cur_page_var}+1).
        ((strlen($_s_field)>0) ? "&_s_field=$_s_field&_s_val="
        .prepare($_s_val)."" : "").
        "&module=$module&action=$action")."
    </td></tr>
    " : "" )
    
    ."
     </form>
    </table>
   </td></tr>
    " : "" )
    
    ."<tr><td>
    ".freemed_display_actionbar($page_link)."
    </td></tr>
    <tr><td>
  ";
  // end header

  $buffer .= "
    <table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"2\" BORDER=\"0\"
     ALIGN=\"CENTER\" ALIGN=\"MIDDLE\">
    <tr>
  ";
  while (list($k,$v)=each($control_list)) {
    $buffer .= "
      <td CLASS=\"reverse\">
       $k&nbsp;
      </td>
    ";
  }
  if ($flags != 0) {
  $buffer .= "
      <td CLASS=\"reverse\">".__("Action")."</td>
	  </tr>
  ";
  } else {
  	$buffer .= "<td CLASS=\"reverse\"></td></tr>";
  }
 
  if ($sql->num_rows($result)>0) 
   while ($this_result = $sql->fetch_array($result) AND 
      ((strlen($cur_page_var)>0) ? ($on_this_page < $max_num_res) : (1)) ) {
    $on_this_page++;
    $first = true; // first item in the list has 'view' link
    $buffer .= "
    <tr CLASS=\"".freemed_alternate()."\" ".
    ( ($flags & ITEMLIST_VIEW) ?
    "onClick=\"window.location='$page_link?module=$module&patient=$patient&".
    "action=view&id=".urlencode($this_result['id'])."'; return true;\" " :
    "" ).">
    ";
    reset($control_list); // it's already each'd the arrays, 
    if (is_array($xref_list)) 
      reset($xref_list);    // but we have to do it again for the next iteration
    $field_num=0;
    while (list($k,$v)=each($control_list)) {
      $is_xref=false;
      if (is_array($xref_list)) {
        reset($xref_list);
        $xref_k = $xref_v = "";
        for ($i=0;$i<=$field_num;$i++)
          list ($xref_k, $xref_v) = each($xref_list);
        // the proper item is now in $xref_{k,v}
        if (strlen($xref_v)>1) {
          $is_xref=true;
          $xref_item=freemed::get_link_field($this_result[$v],
                                                    $xref_k,$xref_v);
          $item_text = ( (strlen($xref_item)<1) ?
                         prepare($blank_list[$field_num]) :
                         prepare($xref_item) );
        }
      } // if there are any xrefs in the table
      if (!$is_xref) { // not an xref item 
        $item_text = ( (strlen($this_result[$v])<1)?
                       prepare($blank_list[$field_num])  :
                       prepare($this_result[$v]) ); 
      }
      if ($first) {
        $first = false;
        $buffer .= "
      <td>
        <a HREF=\"$page_link?".( !isset($_pass) ? $_pass.'&' : '' ).
	"patient=$patient&action=display&id=".
	"$this_result[id]&module=$module\"
	  >$item_text</a>&nbsp;
      </td>
        ";
      } else {
        $buffer .= "
      <td>
        $item_text&nbsp;
      </td>
        ";
      }
    $field_num++;
    } // while each data field
    
    $buffer .= "
      <td>
    ";
    if ($flags & ITEMLIST_VIEW) {
      $buffer .= "
        <a HREF=\"$page_link?".( isset($_pass) ? $_pass.'&' : '' ).
	"module=$module&patient=$patient&action=view&id=".
	urlencode($this_result['id'])."\" class=\"button\">".__("VIEW")."</a>
      ";
    }
    if (freemed::user_flag(USER_DATABASE) AND 
         ($flags & ITEMLIST_MOD) AND (!$this_result['locked'])) {
      $buffer .= "
        <a HREF=\"$page_link?".( isset($_pass) ? $_pass.'&' : '' ).
	"module=$module&patient=$patient&action=modform&id=".
	urlencode($this_result['id'])."\" class=\"button\">".__("MOD")."</a>
      ";
    }
    if (freemed::user_flag(USER_DELETE) AND
         ($flags & ITEMLIST_DEL) AND (!$this_result['locked'])) {
	$buffer .= html_form::confirm_link_widget(
        	"$page_link?".( isset($_pass) ? $_pass.'&' : '' ).
		"patient=$patient&module=$module&action=delete&id=".
				urlencode($this_result['id']),
	 	__("DEL"),
		array(
			'confirm_text' =>
			__("Are you sure you want to delete this record?"),

			'text' => __("Delete"),
			'class' => 'button'
		)
	)."\n";

    }
    if (freemed::user_flag(USER_DELETE) AND
         ($flags & ITEMLIST_LOCK) AND ($this_result['locked']=='0')) {
	$buffer .= html_form::confirm_link_widget(
        	"$page_link?".( isset($_pass) ? $_pass.'&' : '' ).
		"patient=$patient&module=$module&action=lock&id=".
				urlencode($this_result['id']),
	 	__("LOCK"),
		array(
			'confirm_text' =>
			__("Are you sure you want to lock this record?"),

			'text' => __("Lock"),
			'class' => 'button'
		)
	)."\n";
    }
    
    
    $buffer .= "
      &nbsp;</td>
    </tr>
    ";
   } // while each result-row
  else { // no items to display
   $buffer .= "
    <tr CLASS=\"".freemed_alternate()."\">
     <td COLSPAN=".(count($control_list)+1)." ALIGN=\"CENTER\">
      <i>No ".__($GLOBALS["record_name"])."</i>
     </td>
    </tr>
   ";
  } // if no items to display
   
  $buffer .= "
    </table>
   </td></tr>
   <tr><td>
  ";
  
  // searchbox
 if (($num_pages>1) or !empty($_s_val)) {
  $buffer .= "
    <table WIDTH=\"100%\" CELLSPACING=\"0\" CELLPADDING=\"2\" BORDER=\"0\">
    <tr CLASS=\"reverse\">
    <form METHOD=\"POST\" ACTION=\"".prepare($page_name)."\">
     <td ALIGN=\"CENTER\">
      ".html_form::select_widget(
        "_s_field",
	$control_list
      )."
      ".__("contains")."
      <input class=\"button\" TYPE=\"HIDDEN\" NAME=\"module\"
       VALUE=\"".prepare($module)."\"/>
      <input class=\"button\" TYPE=\"HIDDEN\" NAME=\"$cur_page_var\" VALUE=\"1\"/>
      ".html_form::text_widget('_s_val', 20)."
      <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Search")."\"/>
      <input class=\"button\" TYPE=\"BUTTON\" VALUE=\"".__("Reset")."\"
       onClick=\"this.form._s_val.value=''; this.form.submit(); return true;\"/>
     </td>
    </form>
    </tr>
    </table>
   </tr></td>
   <tr><td>
  ";
 } // no searchbox for short-short lists
  // end searchbox
  
  // footer
  $buffer .= freemed_display_actionbar($page_link)
    ."</td></tr>
    </table>
    <!-- End itemlist Table -->
  ";

  return $buffer; // gotta remember this part!
}

// Function: freemed_display_facilities
//
//	Creates an XHTML facility selection widget.
//
// Parameters:
//
//	$varname - Name of the global variable containing the data for this
//	widget.
//
//	$default_load - (optional) Depreciated.
//
//	$internal_external - (optional) Exclusively internal or external
//	facilities. If this is passed at all, "0" selects internal
//	facilities and "1" selects external facilities.
//
//	$by_array - (optional) Array of facility id numbers to limit the
//	selection to.
//
// Returns:
//
//	XHTML compliant facility selection widget.
//
function freemed_display_facilities ($param="", $default_load = false,
                                     $intext="", $by_array="") {
	global $sql, $_COOKIE;

	// Check for default or passed facility
	if ($GLOBALS[$param] > 0) {
		// Only set this if $param is a legal value
		$facility = $GLOBALS[$param];
	} else {
		// Otherwise set via cookie
		$facility = ( $_SESSION['default_facility'] > 0 ?
			$_SESSION['default_facility'] :
			$_COOKIE['default_facility'] ) + 0;
	}

	$buffer = "";

	switch ($intext) {
		case "0": // internal
			$intextquery = "WHERE psrintext='0'";
			break;
		case "1": // external
			$intextquery = "WHERE psrintext='1'";
			break;
		default:
			$intextquery = "";
	}

	// Check for "by_array"
	if (is_array($by_array)) {
		$intextquery .= " AND id IN ( ".implode(",", $by_array)." ) ";
	} // end checking for by_array

	// list doctors in SELECT/OPTION tag list, and
	// leave doctor selected who is in param
	$buffer .= "<option VALUE=\"0\"".
		( ($param == 0) ? " selected" : "" ).">".__("NONE SELECTED").
		"</option>\n";
	$query = "SELECT * FROM facility ".$intextquery.
		"ORDER BY psrname,psrnote";
	$result = $sql->query ($query);
	if (!$result) return false;

	while ($row = $sql->fetch_array($result)) {
		$buffer .= "<option VALUE=\"".prepare($row[id]).
			"\" ".
			( ($row[id]==$facility) ? "selected" : "" ).">".
			prepare($row[psrname]." (".$row[psrcity].", ".
				$row[psrstate].")").
			( ($debug) ? "[".$row[psrnote]."]" :
			"" )."</option>\n";
	} // while there are more results...
	return $buffer;
} // end function freemed_display_facilities

function freemed_display_physicians ($param, $intext="") {
	die ("freemed_display_physicians - DEPRECIATED");
/*
	$buffer = "";

	// list doctors in SELECT/OPTION tag list, and
	// leave doctor selected who is in param
	$buffer .= "
		<option VALUE=\"0\">".__("NONE SELECTED")."</option>
	";
	$query = "SELECT * FROM physician ".
		( ($intext != "") ? " WHERE phyref='$intext'" : "" ).
		"ORDER BY phylname,phyfname";
	$result = $sql->query ($query);
	if (!$sql->results($result)) {
		// don't do anything...! 
	} else { // exit if no more docs
		while ($row = $sql->fetch_array($result)) {
			$buffer .= "
			<option VALUE=\"$row[id]\" ".
			( ($row[id] == $param) ? "selected" : "" ).
			">".prepare("$row[phylname], $row[phyfname]")."</option>
			"; // end of actual output
		} // while there are more results...
	}
	return $buffer;
*/
} // end function freemed_display_physicians

///////////////////////////////////////////////////
// function freemed_display_printerlist
// displays printers from the database
function freemed_display_printerlist ($param)
{
  global $sql;

  // list printers in SELECT/OPTION tag list, and
  // leave printer selected who is in param
  echo "
    <OPTION VALUE=\"0\">".__("NONE SELECTED")."
  ";
  $query = "SELECT * FROM printer ORDER BY ".
     "prnthost, prntname";
  $result = $sql->query ($query);
  if (!$result) {
    // don't do anything...! 
  } else { // exit if no more printers
    while ($row=$sql->fetch_array($result)) {
      echo "
        <OPTION VALUE=\"$row[id]\" ".
	( ($param == $row[id]) ? "SELECTED" : "" ).
        ">".prepare("$row[prnthost] $row[prntname]")."
      "; // end of actual output
    } // while there are more results...
  }
} // end function freemed_display_printerlist

// Function: freemed_display_selectbox
//
//	Create an XHTML selection box
//
// Parameters:
//
//	$result - SQL query result
//
//	$format - Format hash for the display box (result field names
//	surrounded by '##'s)
//
//	$varname - Name of the variable to store the selected data in.
//
// Returns:
//
//	XHTML compliant selection widget.
//
function freemed_display_selectbox ($result, $format, $param="") {
	global ${$param}; // so it knows to put SELECTED on properly
	global $sql; // for database connection

	static $var; // array of $result-IDs so we only go through them once
	static $count; // count of results

	if (!isset($var["$result"])) {
		if ($result) {
			$count["$result"] = $sql->num_rows($result);
			while ($var["$result"][] = $sql->fetch_array($result));
		} // non-empty result
	} // if we haven't gone through this list yet
 
	$buffer = "";
	if ($count["$result"]<1) { 
		$buffer .= __("NONE")." ".
			"<input TYPE=\"HIDDEN\" NAME=\"".prepare($param)."\" ".
			"VALUE=\"0\"/>\n";
		return $buffer; // do nothing!
	} // if no result

	$buffer .= "
		<select NAME=\"$param\">
		<option VALUE=\"0\">".__("NONE SELECTED")."</option>
	";
	
	reset($var["$result"]); // if we're caching it, we have to reset it!
	// no null values!
	while ( (list($pickle,$item) = each($var["$result"])) AND ($item[id])) {
		// START FORMAT-FETCHING
		// Odd members are variable names
		$format_array = explode("#",$format);
		while (list($index,$str) = each($format_array)) {
			// ignore the evens!
			if ( !($index & 1) ) continue;
			// can't just change $str!
			$format_array[$index] = $item[$str];
		} // while replacing each variable name
		// put it back together
		$this_format = join("", $format_array);
		// END FORMAT-FETCHING    

		$buffer .= "
		<option VALUE=\"$item[id]\" ".
		( ($item[id] == $$param) ? "selected" : "" ).
		">".prepare($this_format)."</option>\n";
	} // while fetching result
	$buffer .= "
	</select>
	";
  
	return $buffer;
} // end function freemed_display_selectbox

// Function: freemed_export_stock_data
//
//	Export FreeMED database table to a file
//
// Parameters:
//
//	$table_name - Name of the SQL table to export
//
function freemed_export_stock_data ($table_name, $file_name="") {
	global $sql, $debug;

	$physical_file = PHYSICAL_LOCATION . "/data/" . DEFAULT_LANGUAGE . 
		"/" .  $table_name . "." . DEFAULT_LANGUAGE . "." . 
		date("Ymd");

	//if (strlen ($file_name) > 2) $physical_file = $file_name;

	//if (file_exists ($physical_file)) { return false; } // fix this later

	$query = "SELECT * FROM ".addslashes($table_name)." ".
		"INTO OUTFILE '".addslashes($physical_file)."' ".
		"FIELDS TERMINATED BY ',' ".
		"OPTIONALLY ENCLOSED BY '' ".
		"ESCAPED BY '\\\\'";

	if ($debug) echo "<BR> query = \"$query\" <BR> \n";

	$result = $sql->query ($query);

	if ($debug) echo "<BR> result = \"$result\" <BR> \n";

	return $result;
} // end function freemed_export_stock_data

// function freemed_get_userlevel
//   returns user level (1-10)
//   (assumes 1 if not found, 9 if root)
function freemed_get_userlevel ( $param = "" ) {
	die("called freemed_get_userlevel: obsolete STUB");
}

// Function: freemed_import_stock_data
//
//	Import an SQL table into FreeMED
//
// Parameters:
//
//	$table_name - Name of the SQL table to import
//
function freemed_import_stock_data ($table_name) {
	global $sql;

	// Produce a physical location
	$physical_file = PHYSICAL_LOCATION . "/data/" . DEFAULT_LANGUAGE .
		"/" .  $table_name . "." . DEFAULT_LANGUAGE . ".data";

	// Die if the phile doesn't exist
	if (!file_exists($physical_file)) return false;

	// Create the query
	$query = "LOAD DATA LOCAL INFILE '".addslashes($physical_file)."' ".
		"INTO TABLE ".addslashes($table_name)." ".
		"FIELDS TERMINATED BY ','";
           
	$result = $sql->query ($query); // try doing it

	return $result; // send the results home...
} // end function freemed_import_stock_data

// function freemed_open_db
// --- simply backwards compatibility stub for freemed::connect
function freemed_open_db () {
	freemed::connect();
} // end function freemed_open_db

//---------------------------------------------------------------------------
// Time and Date Functions
//---------------------------------------------------------------------------

// Function: freemed_get_date_next
//
//	Get next valid SQL format date (YYYY-MM-DD)
//
// Parameters:
//
//	$date - Starting date
//
// Returns:
//
//	Next date.
//
function freemed_get_date_next ($cur_dt) {
	global $cur_date;

	$y = substr ($cur_dt, 0, 4); // get year
	$m = substr ($cur_dt, 5, 2); // get month
	$d = substr ($cur_dt, 8, 2); // get date

	// check for validity of given date... if not, cur_date
	if (!checkdate($m, $d, $y)) { 
		$y = substr ($cur_date, 0, 4);
		$m = substr ($cur_date, 5, 2);
		$d = substr ($cur_date, 8, 2); 
	}

	if (!checkdate($m, $d + 1, $y)) { // roll day?
		if (!checkdate($m + 1, 1, $y)) { // roll month?
			// roll year
			return date ("Y-m-d", mktime (0,0,0,1,1,$y+1));
		} else {
			// roll month
			return date ("Y-m-d", mktime (0,0,0,$m+1,1,$y));
		} // end checking roll month?
	} else { // checking roll day
		// roll day
		return date ("Y-m-d", mktime (0,0,0,$m,$d+1,$y));
	} // end checking roll day
} // end function freemed_get_date_next

// Function: freemed_get_date_prev
//
//	Get previous date in SQL format (YYYY-MM-DD)
//
// Parameters:
//
//	$date - Starting date
//
// Returns:
//
//	Previous date.
//
function freemed_get_date_prev ($cur_dt) {
	$cur_date = date ("Y-m-d");

	$y = substr ($cur_dt, 0, 4); // year
	$m = substr ($cur_dt, 5, 2); // month
	$d = substr ($cur_dt, 8, 2); // day 

	if (!checkdate ($m, $d, $y)) {
		$y = substr ($cur_date, 0, 4);
		$m = substr ($cur_date, 5, 2);
		$d = substr ($cur_date, 8, 2);
	} // if not right, use current date

	if (($d==1) AND ($m>1)) { // if first day...
		$d = 31; $m--; // roll back
		  // while day too high, decrease
		while (!checkdate ($m, $d, $y)) $d--;
		return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
	} else if (($d==1) AND ($m==1)) { 
		// roll back year
		$m=12; $y--; $d=31;
		return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
	} else { // checking for day
		// roll back day
		$d--;
		return date ("Y-m-d",mktime(0,0,0,$m,$d,$y));
	} // end checking for first day
} // end function freemed_get_date_prev

// Function: fm_date_assemble
//
//	Assemble seperate date fields into single SQL format date hash
//
// Parameters:
//
//	$varname - Name of the date variable
//
//	$array_index - (optional) Array index of $varname that should
//	contain the result data.
//
// Returns:
//
//	SQL formated date string.
//
function fm_date_assemble ($datevarname="", $array_index=-1) {
	// Check for variable name
	if ($datevarname=="")
		trigger_error ("fm_date_assemble: no variable name given",
			E_USER_ERROR);

	// Import into local scope
	global ${$datevarname."_m"}, ${$datevarname."_d"}, ${$datevarname."_y"};

	// Handle calendar js widget
	if (freemed::config_value('date_widget_type') == 'js') {
		global ${$datevarname};
		return ${$datevarname};
	}

	// Decide where they come from if they are from an array
	if ($array_index == -1) {
		$m = ${$datevarname."_m"};
		$d = ${$datevarname."_d"};
		$y = ${$datevarname."_y"};
	} else {
		$m = ${$datevarname."_m"}[$array_index];
		$d = ${$datevarname."_d"}[$array_index];
		$y = ${$datevarname."_y"}[$array_index];
	} // end checking for array index

	// Return assembled string in SQL format
	return $y."-".$m."-".$d;
} // end function fm_date_assemble

// Function: fm_date_entry
//
//	Creates XHTML-compliant date selection widget
//
// Parameters:
//
//	$varname - Variable name to contain the result data.
//
//	$pre_epoch - (optional) Whether the date selection widget should
//	contain years more than 20 in the past. Defaults to false.
//
//	$array_index - (optional) Array index for varname to determine
//	which element of the array is being used. Defaults to no array
//	index.
//
// Returns:
//
//	XHTML-compliant date selection widget.
//
function fm_date_entry ($datevarname="", $pre_epoch=false, $arrayvalue=-1) {
	if ($datevarname=="") return false;  // indicate problems

	// Determine array "suffix"
	if (($arrayvalue+0)==-1) { $suffix=""; $pos=""; }
	  else { $suffix="[]"; $pos="[$arrayvalue]"; }

	// Import into local scope present values
	global $$datevarname, ${$datevarname."_y"}, 
	  ${$datevarname."_m"}, ${$datevarname."_d"};

	// Quickly check to see if we have to replace the value
	/*
	if (empty(freemed::config_value('date_widget_type'))) {
		$GLOBALS['sql']->query(
			$GLOBALS['sql']->insert_query(
				'config',
				array(
					'c_option' => 'date_widget_type',
					'c_value' => 'js'
				)
			)
		);
	}
	*/

	// Check for use case to use special date widget
	if (freemed::config_value('date_widget_type') == 'js') {
		#static $already_js;
		if (!$already_js) {
			$buffer .= "<link rel=\"stylesheet\" type=\"text/css\" ".
				"media=\"all\" ".
				"href=\"lib/template/default/calendar-system.css\"></link>\n";
			$buffer .= "<script language=\"javascript\" ".
				"src=\"lib/template/default/calendar_stripped.js\"></script>\n";
			$buffer .= "<script language=\"javascript\" ".
				"src=\"lib/template/default/calendar-en.js\"></script>\n";
			$buffer .= "<script language=\"javascript\" ".
				"src=\"lib/template/default/calendar-setup_stripped.js\"></script>\n";
			$buffer .= "
			<script language=\"javascript\">
			var calendar = null;
			function selected (cal, date) {
				cal.sel.value = date;
			}
			function closeHandler(cal) {
				cal.hide();
			}
			function showCalendar(id) {
			var el = document.getElementById(id);
			if (calendar != null) {
				calendar.hide();
				calendar.parseDate(el.value);
			} else {
				var cal = new Calendar(true, null, selected, closeHandler);
				calendar = cal;
				cal.setRange(1900, 2070);
				calendar.create();
			}
			calendar.sel = el;
			calendar.showAtElement(el);
			return false;
			}
			</script>
			";
			$already_js = true;
		}
		$buffer .= html_form::text_widget (
			$datevarname,
			array (
				'length' => 10,
				'id' => $datevarname.'_cal'
			)
		);
		$buffer .= "
		<script type=\"text/javascript\">
		Calendar.setup({
			inputField	:	\"".$datevarname."_cal\",
			ifFormat	:	\"y-dd-mm\",
			singleClick	:	true
		});
		</script>
		<img src=\"lib/template/default/img/calendar_widget.gif\" border=\"0\" ".
			"onClick=\"return showCalendar('".$datevarname."_cal');\" />
		";
		return $buffer;
	}

	// Set months
	$months = array (
		"", // null so that 1 = Jan, not 0 = Jan
		__("Jan"),
		__("Feb"),
		__("Mar"),
		__("Apr"),
		__("May"),
		__("Jun"),
		__("Jul"),
		__("Aug"),
		__("Sep"),
		__("Oct"),
		__("Nov"),
		__("Dec")
	);

	// For brevity, import into single letter variables
	$w = ${$datevarname.$pos};
	$m = ${$datevarname."_m".$pos};
	$d = ${$datevarname."_d".$pos};
	$y = ${$datevarname."_y".$pos};

	// Determine *where the date is coming from...
	if (!empty($w)) {
		// If the whole is set... split into parts and use that
		$y = substr ($w, 0, 4);  // split year
		$m = substr ($w, 5, 2);  // split month
		$d = substr ($w, 8, 2);  // split day
	} elseif (empty($y) and empty($m) and empty($d)) {
		// If there is no whole and no parts, use current date
		$y = date ("Y")+0;
		$m = date ("m")+0;
		$d = date ("d")+0;
	} // end if not empty whole date

	// Determine what the range should be
	switch ($pre_epoch) {
		case true:
			$starting_year = (date("Y")-120);
			$ending_year   = (date("Y")+20);
			break;
		case false: default:
			$starting_year = (date("Y")-20);
			$ending_year   = (date("Y")+20);
			break;
	} // end switch for pre_epoch

	// If the dates are legacy, reasonable and out of range, accept
	if (($y>1800) AND ($y<$starting_year)) $starting_year = $y;
	if (($y>1800) AND ($y>$ending_year))   $ending_year   = $y;

	// Form the buffers, then assemble

	// Month buffer
	$buffer_m = "\t<select NAME=\"".$datevarname."_m$suffix\">\n".
		"\t\t<option VALUE=\"00\" ".
		( ($m==0) ? "SELECTED" : "" ).">".__("NONE")."</option>\n";
	for ($i=1;$i<=12;$i++) {
		$buffer_m .= "\n\t\t<option VALUE=\"".( ($i<10) ? "0" : "" ).
			"$i\" ".  ( ($i==$m) ? "SELECTED" : "" ).
			">".__($months[$i])."</option>\n";
	} // end for loop (months) 
	$buffer_m .= "\t</select>\n";

	// Day buffer
	$buffer_d = "\t<select NAME=\"".$datevarname."_d$suffix\">\n".
		"\t\t<option VALUE=\"00\" ".
		( ($d==0) ? "SELECTED" : "" ).">".__("NONE")."</option>\n";
	for ($i=1;$i<=31;$i++) {
		$buffer_d .= "\n\t\t<option VALUE=\"".( ($i<10) ? "0" : "" ).
			"$i\" ".( ($i==$d) ? "SELECTED" : "" ).">$i</option>\n";
	} // end looping for days
	$buffer_d .= "\t</select>\n";

	// Year buffer
	$buffer_y = "\t<select NAME=\"".$datevarname."_y$suffix\">\n".
		"\t\t<option VALUE=\"0000\" ".
		( ($y==0) ? "SELECTED" : "" ).">".__("NONE")."</option>\n";
	for ($i=$starting_year;$i<=$ending_year;$i++) {
		$buffer_y .= "\n\t\t<option VALUE=\"$i\" ".
			( ($i==$y) ? "SELECTED" : "" ).">$i</option>\n";
	} // end for look (years)
	$buffer_y .= "\t</select>\n";

	// now actually display the input boxes
	switch (freemed::config_value("dtfmt")) {
		case "mdy":
			return $buffer_m . " <b>-</b> ".
			$buffer_d . " <b>-</b> ".
			$buffer_y;
			break;
		case "dmy":
			return $buffer_d . " <b>-</b> ".
			$buffer_m . " <b>-</b> ".
			$buffer_y;
			break;
		case "ymd": default:
			return $buffer_y . " <b>-</b> ".
			$buffer_m . " <b>-</b> ".
			$buffer_d;
			break;
	} // end switch for dtfmt config value
} // end function fm_date_entry

// Function: fm_date_print
//
//	Create a nicely formatted date display
//
// Parameters:
//
//	$date - SQL formated date
//
//	$show_text_days - (optional) Whether or not to show the day names
//	as text names. Defaults to false.
//
// Returns:
//
//	Formatted date display.
//
function fm_date_print ($actualdate, $show_text_days=false) {
	$y  = substr ($actualdate, 0, 4);        // extract year
	$m  = substr ($actualdate, 5, 2);        // extract month
	$d  = substr ($actualdate, 8, 2);        // extract day
	$ts = mktime (0, 0, 0, $m, $d, date("Y"));      // generate timestamp

	// Return depending on configuration format
	switch (freemed::config_value("dtfmt")) {
		case "mdy":
			return date(($show_text_days ? "D" : "")." M d, ", $ts).$y;
			break;
		case "dmy":
			return date(($show_text_days ? "D" : "")."d M, ", $ts).$y;
			break;
		case "ymd": default:
			return $y.date("-m-d", $ts);
			break; 
	} // end switch
} // end function fm_date_print

// Function: fm_htmlize_array
//
//	Convert array to XHTML input type=HIDDEN tags
//
// Parameters:
//
//	$varname - Variable name to put the data in
//
//	$cur_array - Actual data to be stored
//
// Returns:
//
//	XHTML input type=HIDDEN tags
//
function fm_htmlize_array ($variable_name, $cur_array) {
	// Cache the length of the array
	$array_length = count ($cur_array);

	// If there is nothing in the array, return nothing
	if ($array_length==0) { return ""; }

	// Loop through the array
	for ($i=0; $i<$array_length; $i++)
		$buffer .= "\t<input TYPE=\"HIDDEN\" NAME=\"".
		prepare($variable_name)."[".prepare($i)."]\" ".
		"VALUE=\"".prepare($cur_array[$i])."\"/>\n";

	// Dump back the hash
	return $buffer;
} // end function fm_htmlize_array

function fm_make_string_array($string) {
	// ensure string ends in :
	if (!strpos($string,":"))
		return $string.":";
	return $string;
} // end function fm_make_string_array

function fm_join_from_array ($cur_array) {
	// If there is nothing, return nothing
	if (count($cur_array)==0) return "";

	// If it is scalar, return the value
	if (!is_array($cur_array)) return "$cur_array";

	// Otherwise compact it with ":" as the separator character
	return implode ($cur_array, ":");
} // end function fm_join_from_array 

// Function: fm_number_select
//
//	Create an XHTML compliant number selection widget
//
// Parameters:
//
//	$varname - Name of the variable to store this data in
//
//	$min - (optional) Minimum value. Defaults to 0.
//
//	$max - (optional) Maximum value. Defaults to 10.
//
//	$step - (optional) Incrementing value. Defaults to 1.
//
//	$add_zero - (optional) Prepend zeros to values under 10. Defaults
//	to false.
//
//	$submit_on_blur - (optional) Submit the form when focus on the
//	widget is lost. Defaults to false.
//
// Returns:
//
//	XHTML-compliant number selection widget
//
function fm_number_select ($varname, $min=0, $max=10, $step=1, $addz=false, $submit_on_blur = false) {
	global ${$varname}; // bring in the variable

	// Pull into local scope
	$selected = ${$varname};

	// Start header
	$buffer = "\n\t<select NAME=\"".prepare($varname)."\" ".
		( $submit_on_blur ?
		"onChange=\"this.form.submit(); return true;\"" :
		"" ).">\n";

	// Check to make sure step isn't illegal
	if ($step==0) $step = 1;

	// Check to see if parameters are legal
	if ( ($min>$max) AND ($step>=0) )  return false;
	if ( ($min<$max) AND ($step<=0) )  return false;

	for ($i=$min; $i<=$max; $i+=$step) {
		$buffer .=  "\t\t<option VALUE=\"$i\"".
			( (("$selected"=="$i") or ($selected==$i)) ?
			"selected" : "" ).
			">".( (($i<10) and ($addz)) ? "0" : "" )."$i</option>\n";
	} // end for loop

	// Footer
	$buffer .= "\t</select>\n";

  	// Return buffer
	return $buffer;
} // end function fm_number_select

function fm_phone_assemble ($phonevarname="", $array_index=-1) {
  $buffer = ""; // we use buffered output for notebook class!
  if ($phonevarname=="") return ""; // return nothing if no variable is given
  global ${$phonevarname}, ${$phonevarname."_1"},
    ${$phonevarname."_2"}, ${$phonevarname."_3"}, 
    ${$phonevarname."_4"}, ${$phonevarname."_5"};
  if ($array_index == -1) {
    $w  = ${$phonevarname};    // whole number
    $p1 = ${$phonevarname."_1"};    // part 1
    $p2 = ${$phonevarname."_2"};    // part 2
    $p3 = ${$phonevarname."_3"};    // part 3
    $p4 = ${$phonevarname."_4"};    // part 4
    $p5 = ${$phonevarname."_5"};    // part 5
  } else {
    $w  = ${$phonevarname}[$array_index];  // whole number
    $p1 = ${$phonevarname."_1"}[$array_index];  // part 1
    $p2 = ${$phonevarname."_2"}[$array_index];  // part 2
    $p3 = ${$phonevarname."_3"}[$array_index];  // part 3
    $p4 = ${$phonevarname."_4"}[$array_index];  // part 4
    $p5 = ${$phonevarname."_5"}[$array_index];  // part 5
  } // end checking for array index

  // Check for case where parts aren't set, but whole is
  $phofmt = freemed::config_value('phofmt');
  if (${$phonevarname} and !${$phonevarname.'_1'} and ($phofmt=='usa' or $phofmt=='fr')) {
    return $w;
  }
  
  switch (freemed::config_value("phofmt")) {
    case "usa":
     return $p1.$p2.$p3.$p4;        // assemble number and put it all together
    case "fr":
     return $p1.$p2.$p3.$p4.$p5;    // assemble number and put it all together
    case "unformatted":
    default:
     return $w;                     // return whole number...
  } // end switch for formatting
} // end function fm_phone_assemble

function fm_phone_entry ($phonevarname="", $array_index=-1, $ext=true) {
  if ($phonevarname=="") return false;  // indicate problems
  if (($array_index+0)==-1) { $suffix="";   }     
   else                     { $suffix="[]"; }
  $formatting = freemed::config_value("phofmt"); // get phone formatting
  global $$phonevarname, ${$phonevarname."_1"},	 // get global vars
         ${$phonevarname."_2"}, ${$phonevarname."_3"}, 
         ${$phonevarname."_4"}, ${$phonevarname."_5"}; 

  // Check to see if autoskip JS is enabled
  if (!$GLOBALS['__phpwebtools']['autoskip']) {
    // Enable autoskip
    $buffer .= "
    	<script LANGUAGE=\"JavaScript\">
	function autoskip(here, next) {
		if (here.value.length==here.getAttribute('maxlength') && here.getAttribute) {
			next.focus()
		}
	}
	</script>
    ";
    
    // Set for future reference
    $GLOBALS['__phpwebtools']['autoskip'] = 1;
  }

  if ($array_index == -1)  {
    $w = ${$phonevarname};    // whole number
  } else {
    $w = ${$phonevarname}[$array_index];  // whole number
  }

  if (!empty($w)) {
    // if phone # is not empty, split
    switch ($formatting) {
      case "usa":
       $p1 = substr($w,  0, 3); // area code
       $p2 = substr($w,  3, 3); // prefix
       $p3 = substr($w,  6, 4); // local number
       $p4 = substr($w, 10, 4); // extention
       break;
      case "fr":
       $p1 = substr($w, 0, 2); 
       $p2 = substr($w, 2, 2); 
       $p3 = substr($w, 4, 2); 
       $p4 = substr($w, 6, 2); 
       $p5 = substr($w, 8, 2); 
       break;
      case "unformatted":
      default:
       // nothing!! hahahahahahahahahahahahaha!
       break;
    } // end formatting case statement
  } else { // end if not empty whole date
    if ($array_index == -1) {
      $p1 = ${$phonevarname."_1"};    // part 1
    $p2 = ${$phonevarname."_2"};    // part 2
    $p3 = ${$phonevarname."_3"};    // part 3
    $p4 = ${$phonevarname."_4"};    // part 4
    $p5 = ${$phonevarname."_5"};    // part 5
    } else {
    $p1 = ${$phonevarname."_1"}[$array_index];  // part 1
    $p2 = ${$phonevarname."_2"}[$array_index];  // part 2
    $p3 = ${$phonevarname."_3"}[$array_index];  // part 3
    $p4 = ${$phonevarname."_4"}[$array_index];  // part 4
    $p5 = ${$phonevarname."_5"}[$array_index];  // part 5
    } // end checking for array index
  }

  // now actually display the input boxes
  switch ($formatting) {
    case "usa":
     $buffer .= "
      <b>(</b>
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_1$suffix\" SIZE=\"4\"
       MAXLENGTH=\"3\" VALUE=\"$p1\"
       onKeyup=\"autoskip(this, ".$phonevarname."_2$suffix); return true;\"
       /> <b>)</b>
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_2$suffix\" SIZE=\"4\"
       MAXLENGTH=\"3\" VALUE=\"$p2\"
       onKeyup=\"autoskip(this, ".$phonevarname."_3$suffix); return true;\"
       /> <b>-</b>
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_3$suffix\" SIZE=\"5\"
       MAXLENGTH=\"4\" VALUE=\"$p3\" ".( $ext ? "
       onKeyup=\"autoskip(this, ".$phonevarname."_4$suffix); return true;\"
       " : "" )."/>".( $ext ? " <i>ext.</i>
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_4$suffix\" SIZE=\"5\"
       MAXLENGTH=\"4\" VALUE=\"$p4\"/>" : "" ); break;
    case "fr":
     $buffer .= "
      <B>(</B>
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_1$suffix\" SIZE=\"3\"
       MAXLENGTH=\"2\" VALUE=\"$p1\"
       onKeyup=\"autoskip(this, ".$phonevarname."_2$suffix); return true;\"
       /> <b>)</b>
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_2$suffix\" SIZE=\"3\"
       MAXLENGTH=\"2\" VALUE=\"$p2\"
       onKeyup=\"autoskip(this, ".$phonevarname."_3$suffix); return true;\"
       /> 
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_3$suffix\" SIZE=\"3\"
       MAXLENGTH=\"2\" VALUE=\"$p3\"
       onKeyup=\"autoskip(this, ".$phonevarname."_4$suffix); return true;\"
       />
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_4$suffix\" SIZE=\"3\"
       MAXLENGTH=\"2\" VALUE=\"$p4\"
       onKeyup=\"autoskip(this, ".$phonevarname."_5$suffix); return true;\"
       />
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."_5$suffix\" SIZE=\"3\"
       MAXLENGTH=\"2\" VALUE=\"$p5\"
       />
     "; break;
    case "unformatted": 
    default:
     $buffer .= "
      <input TYPE=\"TEXT\" NAME=\"".$phonevarname."$suffix\" SIZE=\"15\"
       MAXLENGTH=\"16\" VALUE=\"$w\"/>
     "; break;
  } // end switch for dtfmt config value

  return $buffer;                         // we exited well!
} // end function fm_phone_entry

//---------------------------------------------------------------------------
// Variable Manipulation Functions
//---------------------------------------------------------------------------

// I am tired of trying to log stuff
// These all need an entry and log exit for log level 0
// Fred Trotter....



function fm_split_into_array ($original_string) {
	// If there is nothing to split, return nothing
	if (empty($original_string)) return "";

	// Split and return
	return explode (":", $original_string);
} // end function fm_split_into_array

function fm_value_in_array ($cur_array, $value) {
	// If there is no array, it obviously does not have the value
	if (count ($cur_array) < 0) return false;

	// Not sure about this...
	//if (!is_array ($cur_array)) return ($cur_array == $value);

	// loop through array
	for ($c=0;$c<count($cur_array);$c++)
		if ($cur_array[$c]==$value) // if there is a match...
			return true; // return true.

	// Return false if we didn't find it
	return false;
} // end function fm_split_into_array

function fm_value_in_string ($cur_string, $value) {
	// Check for ":" separator indicating hash'd array
	if ( strpos ($cur_string, ":") > 0 ) {
		// Split it out...
		$this_array = fm_split_into_array ($cur_string);
		// ... then use fm_value_in_array to return the value
		return fm_value_in_array ($this_array, $value);
	} // end checking for ":"

	// Otherwise do a simple substring match check
	if (strstr($cur_string,$value) != "") return true;

	// If it hasn't been found, return false
	return false;
} // end function fm_value_in_string

// fm_eval -- evaluate string variables (with security checks, of course)
function fm_eval ($orig_string) {
	// Import all global variables
	foreach ($GLOBALS AS $k => $v) global ${$k};

	// Transfer to internal variable
	$loc_string = $orig_string;

	// Secure the string so that kiddies don't mess anything up
	$sec_string = fm_secure ($loc_string);

	// Use eval to pull in the proper variables
	eval ("\$new_string = \"$sec_string\";");

	// Return the processed string
	return $new_string;
} // end function fm_eval

// fm_secure -- secures strings that are to be evaled by simply removing
//              all secure varaibles...
function fm_secure ($orig_string) {
	// Variables to secure
	$secure_these = array (
		"db_user",
		"db_password",
		"db_host",
		"database",
		"gifhome",
		"db_engine"
	);

	// Pass to internal variable
	$this_string = "$orig_string"; 

	// Perform replacements
	foreach ( $secure_these AS $drek => $secure_var ) {
		$this_string = str_replace (
			"\$".$secure_var,
			"",
			$this_string
		);
	}

	// Return secured string
	return $this_string;
} // end function fm_secure

//---------------------------------------------------------------------------
// Patient Coverage Functions
//---------------------------------------------------------------------------

function fm_get_active_coverage ($ptid=0) {
	global $sql;

	// Initialize results
	$result = 0;

	// If no patient ID was given, return 0
	if ($ptid == 0) return 0;

	// Form and perform query
	$query = "SELECT id FROM coverage WHERE ".
		"covpatient='".addslashes($ptid)."' ".
		"AND covstatus='".ACTIVE."'";
	$result = $sql->query($query);

	// If nothing was returned, return 0
	if (!$result) return $result;

	// Pull in id's for all pertinent records
        while ($rec = $sql->fetch_array($result)) $ins_id[] = $rec["id"];

	// If nothing was done, nothing return 0
	if (!isset($ins_id)) return 0;

	// Return the array of coverages
        return $ins_id;
} // end function fm_get_active_coverages

function fm_verify_patient_coverage($ptid=0, $coveragetype=PRIMARY) {
	global $sql, $cur_date;

	// Initialize result
	$result = 0;

	// Check for ptid, otherwise return 0
	if ($ptid == 0) return 0;
	
	// default coveragetype is primary	
	$query = "SELECT id FROM coverage WHERE ".
		"covpatient='".addslashes($ptid)."' AND ".
		"covstatus='".ACTIVE."' AND ".
		"covtype='".addslashes($coveragetype)."'";
	$result = $sql->query($query);

	// Check for results, otherwise return 0
	if (!$sql->results($result)) return 0;
		
	// Return the id
	$row = $sql->fetch_array($result);
	return $row[id];
} // end function fm_verify_patient_coverage

//---------------------------------------------------------------------------
// Time-related Functions
//---------------------------------------------------------------------------

function fm_time_entry ($timevarname="") {
  if ($timevarname=="") return false;  // indicate problems
  global $$timevarname, ${$timevarname."_h"}, 
    ${$timevarname."_m"}, ${$timevarname."_ap"};


  $w = $$timevarname;       
  $h = ${$timevarname."_h"};
  if (!empty($w))
  {
		// if timeval then extract the pieces
		// this could be first time thru since $timevarname
        // will not be saved across page invocations
	  $values = explode(":",$$timevarname);
      ${$timevarname."_h"}  = $values[0];
      ${$timevarname."_m"}  = $values[1];
      ${$timevarname."_ap"} = $values[2];
      $ap = $values[2];
     
  }
  elseif (empty($h))
  {
	  // if not timeval and not hour then
      // plug a default. we shoud have a value in $h
      // secondtime thru
	  $$timevarname = "00:00:AM";
      ${$timevarname."_h"} = "00";
	  ${$timevarname."_m"} = "00";
	  ${$timevarname."_ap"} = __("AM");
	  $ap = __("AM");
  }

  //echo ${$timevarname."_h"}."<BR>";
  //echo ${$timevarname."_m"}."<BR>";
  //echo ${$timevarname."_ap"}."<BR>";
	

  $buffer_h = fm_number_select($timevarname."_h",0,12);
  $buffer_m = fm_number_select($timevarname."_m",0,59);
  $buffer_ap = "<select NAME=\"$timevarname"."_ap"."\">".
	"<option VALUE=\"AM\" ".
		( $ap=="AM" ? "SELECTED" : "").">". __("AM")."</option>\n".
	"<option VALUE=\"PM\" ".
		( $ap=="PM" ? "SELECTED" : "").">". __("PM")."</option>\n";
   
  return $buffer_h.$buffer_m.$buffer_ap;
  
} // end fm_time_entry


function fm_time_assemble ($timevarname="") {
  if ($timevarname=="") return ""; // return nothing if no variable is given
  global ${$timevarname."_h"}, ${$timevarname."_m"}, ${$timevarname."_ap"};

    $m = ${$timevarname."_m"};
    $h = ${$timevarname."_h"};
    $ap = ${$timevarname."_ap"};
  return $h.":".$m.":".$ap;                     // return SQL format date
} // end function fm_time_assemble

//---------------------------------------------------------------------------
// Template-related Functions
//---------------------------------------------------------------------------

// Function: template_display
//
//	Display the current template
//
// Parameters:
//
//	$terminate - (optional) End script execution on termination of
//	function. Defaults to true.
//
function template_display ($terminate_on_execute=true) {
	global $display_buffer; // localize display buffer
	global $template; // localize template
	foreach ($GLOBALS AS $k => $v) global ${$k};

	if (file_exists("lib/template/".$template."/template.php")) {
		include_once ("lib/template/".$template."/template.php");
	} else { // otherwise load the default template
		include_once ("lib/template/default/template.php");
	} // end template load

	// Kill everything after this has been displayed
	if ($terminate_on_execute) die("");
} // end function template_display

//********************** END TEMPLATE SUPPORT

// Function: page_push
//
//	Push page onto global history stack.
//
function page_push () {
	global $page_title;
	$page_history = $_SESSION['page_history'];

	// Import it if it exists
	if (isset($_SESSION['page_history'])) {
		// Import
		$_page_history = $_SESSION['page_history'];

		// Check to see if this is the last item on the list...
		// ... kick out without adding.
		if (basename($_page_history[(count($_page_history))]) ==
			basename($_SERVER['PHP_SELF'])) return true;
	} // end checking for existing history

	// Add to the list of pages
	$_page_history["$page_title"] = basename($_SERVER['PHP_SELF']);

	// Reimport into SESSION
	$_SESSION['page_history'] = $_page_history;
} // end function page_push

// Function: page_pop
//
//	Pop off page from global history stack.
//
function page_pop () {
	// Return false if there is nothing in the list
	if (!isset($_SESSION['page_history'])) return false;

	// Import page_history
	$_page_history = $_SESSION['page_history'];

	// Otherwise get the last one and return it ...
	$to_return = $_page_history[(count($page_history)-1)];
	$to_return_name = $_page_history[(count($page_history_name)-1)];

	// .. then remove it from the stack
	unset($_page_history[(count($_page_history)-1)]);
	unset($_page_history_name[(count($_page_history)-1)]);

	// Reimport into SESSION
	$_SESSION['page_history'] = $_page_history;
	$_SESSION['page_history_name'] = $_page_history_name;

	// And return value (access as list(x,y) = page_pop())
	return array ($to_return, $to_return_name);
} // end function page_pop

// Function: patient_push
//
//	Push patient onto global history stack.
//
function patient_push ($patient) {
	// Import it if it exists
	if (isset($_SESSION['patient_history'])) {
		// Import
		$patient_history = $_SESSION['patient_history'];

		// Clean out null entries... and rogue arrays
		foreach ($patient_history AS $k => $v) {
			if (!$v) unset($patient_history[$k]);
			if (is_array($v)) unset($patient_history[$k]);
		} // end foreach

		// Check to see if this is the last item on the list...
		// ... kick out without adding.
		if ($patient_history[(count($patient_history))] == $patient) {
			// Reimport due to cleaning
			$_SESSION['patient_history'] = $patient_history;

			// And we don't have to add it, exit with true
			return true;
		} // end checking if we just saw them...
	} // end checking for existing history

	// Add to the list of pages
	$patient_history[] = $patient;

	// Reimport into SESSION
	$_SESSION['patient_history'] = $patient_history;
} // end function patient_push

// Function: patient_history_list
//
//	Get global history list for patients
//
// Returns:
//
//	Array of patients in global history list.
//
function patient_history_list () {
	// Return false if there is nothing in the list
	if (!isset($_SESSION['patient_history'])) return false;

	// Import patient_history
	$patient_history = $_SESSION['patient_history'];

	// Sort by alpha
	ksort($patient_history);

	// Check for no patient history
	if (count($patient_history)<1) return false;

	// Create new empty array
	unset($history);

	// Loop through array
	foreach ($patient_history AS $k => $v) {
		// Kludge to get around strange PHP crashing error on
		// $v processing by checking if it's an array.
		if (!is_array($v)) {
			// Get patient information
			$this_patient = CreateObject('FreeMED.Patient', $v);
	
			// Form Lastname, Firstname, ID list item
			$key = $this_patient->fullName() . " (".$v.")";

			// Add to new array
			$history["$key"] = $v;
		}
	} // end foreach

	// Return generated array
	return array_reverse($history);
} // end function patient_history_list

// Function: page_history_list
//
//	Get global history list for pages
//
// Returns:
//
//	Array of pages in global history list.
//
function page_history_list () {
	// Return false if there is nothing in the list
	if (!isset($_SESSION['page_history'])) return false;

	// Import patient_history
	$page_history = $_SESSION['page_history'];

	// Check for no patient history
	if (count($page_history)<1) return false;

	// Create new empty array
	unset($history);

	// Loop through array
	foreach ($page_history AS $k => $v) {
		if (!empty($k) and !empty($v) and !is_array($v)) {
			// Add to new array
			$history["$k"] = $v;
		}
	} // end foreach

	// Return generated array
	return array_reverse($history);
} // end function page_history_list

// Function: help_url
//
//	Contruct a help link from the specified page and section
//
// Parameters:
//
//	$page - (optional) Name of the page that this relates to.
//
//	$section - (optional) Subsection of the page.
//
// Returns:
//
//	Fully formed URL to the specified help page.
//
function help_url ( $page = "", $section = "" ) {
	global $language, $PHP_SELF;

	// If there's no page name, substitute in $PHP_SELF
	if ($page == "") {
		$page_name = basename($PHP_SELF);
	} else {
		$page_name = $page;
	}

	// Produce name by removing .php
	$page_name = str_replace(".php", "", $page_name);

	// Build helpfile name...
	if (empty($page_name) AND empty($section)) {
		// Default if nothing is provided
		$_help_name = "lang/$language/doc/default.$language.html";
	} elseif (!empty($page_name) AND empty($section)) {
		// If just page name, leave out section
		$_help_name = "lang/$language/doc/$page_name.$language.html";
	} elseif (!empty($page_name) AND !empty($section)) {
		// Page name and section provided
		$_help_name = "lang/$language/doc/$page_name.$section.$language.html";
	} else {
		// Should never have section with no page name
		$_help_name = "lang/$language/doc/default.$language.html";
	}

	// Check to see if it exists
	if (!file_exists($_help_name)) {
		// Try to pass it back thru with just the page if section bites
		if (!empty($section)) {
			return help_url ($page_name);
		} else {
			// If it doesn't exist, don't pass it...
			return "help.php";
		}
	} else {
		if ($section != "") {
			return "help.php?page_name=".urlencode($page_name)."&".
				"section=".urlencode($section);
		} else {
			return "help.php?page_name=".urlencode($page_name);
		}
	}
} // end function help_url

//---------------------------------------------------------------------------
// Authentication Subsystem
//---------------------------------------------------------------------------

// TODO: Upgrade basic_authentication to deal with MD5 sums, since we're no longer doing plain text.

// Function: check_basic_authentication
//
//	Check current basic authentication against users in the database.
//	This function is broken until phpwebtools is upgraded to support
//	MD5-based basic authentication verification.
//
// Returns:
//
//	Boolean value, whether user is properly authenticated.
//
function check_basic_authentication () {
	global $sql;

	// Build array of users
	$query = "SELECT username, userpassword, userlevel FROM user";
	$result = $sql->query($query);
	if ($sql->results($result)) {
		while ($r = $sql->fetch_array($result)) {
			$users[(stripslashes($r['username']))] =
				stripslashes($r['userpassword']);
		} // end looping thru results
	} // end no results

	// Call phpwebtools' basic authentication function
	return basic_authentication(PACKAGENAME, $users);
} // end function check_basic_authentication

} // end checking for __API_PHP__

?>
