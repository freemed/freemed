<?php
	// $Id$
	// lic : GPL, v2

LoadObjectDependency('PHP.module');

// Class: FreeMED.BaseModule
//
//	Basic FreeMED module class. All modules in FreeMED inheirit methods
//	from this class. It extends the phpwebtools module class.
//
class BaseModule extends module {

	// override variables
	var $PACKAGE_NAME = PACKAGENAME;
	var $PACKAGE_VERSION = VERSION;
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_DESCRIPTION = "No description.";
	var $MODULE_VENDOR = "Stock Module";

	// Set package versioning information
	var $PACKAGE_VERSION = VERSION;

	// All FreeMED modules use this one loader
	var $page_name = "module_loader.php";

	// Variable: print_template
	//
	//	Sets the print template to be used for this EMR
	//	segment. Templates are given as files in lib/tex without
	//	the .tex extension. If this is not definied, FreeMED
	//	will default to using the internal renderer.
	//
	// Example:
	//
	//	$this->print_template = '_template';
	//
	var $print_template = '';

	// Method: BaseModule constructor
	function BaseModule () {
		// Call parent constructor
		$this->module();
		// Call setup
		$this->setup();
		// Load language files, if necessary
		GettextXML::textdomain(strtolower(get_class($this)));
	} // end constructor BaseModule

	// Method: BaseModule->check_vars
	function check_vars ($nullvar = "") {
		global $module;
		if (!isset($module))
		{
			 trigger_error("No Module Defined",E_ERROR);
		}
		return true;
	} // end function check_vars

	// Method: BaseModule->_header
	function _header ($nullvar = "") {
		global $display_buffer, $page_name;
		freemed::connect ();
		$page_name = __($this->MODULE_NAME);

		// Check for existance of separate "record_name"
		if (!isset($this->record_name)) {
			$this->record_name = __($this->MODULE_NAME);
		}

		// Globalize record_name and page_title
		if (page_name() == $this->page_name) {
			$GLOBALS['record_name'] = $this->record_name;
			$GLOBALS['page_title'] = $this->record_name;
		}
	} // end function _header
	function header ( ) { $this->_header(); }

	// Method: BaseModule->_footer
	function footer ($nullvar = "") {
	} // end function footer

	// Method: fax_widget
	//
	//	Callback to allow custom fax controls (for addressbook,
	//	et cetera). By default, this is disabled, and a standard
	//	text entry is used.
	//
	// Parameters:
	//
	//	$varname - Name of the variable
	//
	//	$id - Record id that is being printed
	//
	// Returns:
	//
	//	XHTML widget.
	//
	function fax_widget ( $varname, $id ) {
		return html_form::text_widget('fax_number',
			array( 'length' => '16' )
		);
	} // end method fax_widget

	// Method: printaction
	//
	//	Basic printing functionality
	//
	function printaction ( ) { $this->_print(); }

	// Method: _print
	//
	//	Provides basic printing interface
	//
	function _print ( ) {
		// Turn off the template
		$GLOBALS['__freemed']['no_template_display'] = true;

		// Check for selected printer
		if (!freemed::config_value('printnoselect') and !isset($_REQUEST['printer'])) {
			// select printer form
			global $display_buffer;
			$display_buffer .= "
			<form action=\"".$this->page_name."\" method=\"post\"
			 name=\"myform\">
			<div class=\"PrintContainer\">
			<input type=\"hidden\" name=\"module\" value=\"".prepare($_REQUEST['module'])."\"/>
			<input type=\"hidden\" name=\"type\" value=\"".prepare($_REQUEST['type'])."\"/>
			<input type=\"hidden\" name=\"action\" value=\"".prepare($_REQUEST['action'])."\"/>
			<input type=\"hidden\" name=\"id\" value=\"".prepare($_REQUEST['id'])."\"/>
			<input type=\"hidden\" name=\"patient\" value=\"".prepare($_REQUEST['patient'])."\"/>
			<table border=\"0\" width=\"98%\" cellspacing=\"0\">
			<tr class=\"PrintContainerItem\"
			 	 onMouseOver=\"this.className='PrintContainerItemSelected'; return true;\"
				 onMouseOut=\"this.className='PrintContainerItem'; return true;\">

				<td width=\"50\">
				<input type=\"radio\" 
				 name=\"print_method\"
				 value=\"printer\" checked=\"checked\"
				 id=\"print_method_printer\" /></td>
				<td
				>".__("Printer")."</td>
				<td>".freemed::printers_widget('printer')."</td>
			</tr>
			<tr class=\"PrintContainerItem\"
			 	 onMouseOver=\"this.className='PrintContainerItemSelected'; return true;\"
				 onMouseOut=\"this.className='PrintContainerItem'; return true;\">
				<td width=\"50\">
				<input type=\"radio\"
				 name=\"print_method\"
				 value=\"fax\"
				 id=\"print_method_fax\" /></td>
				<td>".__("Fax")."</td>
				<td>".$this->fax_widget('fax_number', $_REQIEST['id'])."</td>
			</tr>
			<tr class=\"PrintContainerItem\"
			 	 onMouseOver=\"this.className='PrintContainerItemSelected'; return true;\"
				 onMouseOut=\"this.className='PrintContainerItem'; return true;\">
				 <td width=\"50\">
				 <input type=\"radio\"
				  name=\"print_method\"
				  value=\"browser\"
				  id=\"print_method_browser\" /></td>
				 <td colspan=\"2\">".__("Browser-Based")."</td>
			</tr>
			</table>
			<div align=\"center\">
			<input type=\"submit\" value=\"".__("Print")."\"
			 class=\"button\" />
			</div>
			</div>
			</form>
			";
			return true;
		}

		list ($title, $heading, $physician) = $this->_TeX_Information();

		// Create TeX object for patient
		$TeX = CreateObject('FreeMED.TeX', array (
			'title' => $title,
			'heading' => $heading,
			'physician' => $physician
		));

		// Actual renderer for formatting array
		if ($this->print_template) {
			$TeX->_buffer = $TeX->RenderFromTemplate(
				$this->print_template,
				$this->_print_mapping($TeX, $_REQUEST['id'])
			);
		} else {
			$this->_RenderTex(&$TeX, $_REQUEST['id']);
		}

		global $display_buffer;

		// Get appropriate printer from user settings
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('FreeMED.User');
		}
		$printer = CreateObject('PHP.PrinterWrapper');

		$display_buffer .= __("Printing")." ... <br/>\n";

		// Figure out print method
		$_pm = $_REQUEST['print_method'];
		switch ($_pm) {
			// Handle direct to browser
			case 'browser':
			$file = $TeX->RenderToPDF(!empty($this->print_template));
			ob_start();
			readfile($file);
			$contents = ob_get_contents();
			ob_end_clean();

			Header('Content-Type: application/x-freemed-print-pdf');
			Header('Content-Length: '.strlen($contents));
			Header('Content-Disposition: inline; filename="'.mktime().'.pdf"');
			print $contents;
			//print "file = $file<br/>\n";
			die();
			break;

			case 'fax':
			$file = $TeX->RenderToPDF(!empty($this->print_template));
			$fax = CreateObject('_FreeMED.Fax', $file, array (
				'sender' => PACKAGENAME.' v'.DISPLAY_VERSION
				));
			$output = $fax->Send($_REQUEST['fax_number']);
			$display_buffer .= "<b>".$output."</b>\n";
			$GLOBALS['__freemed']['close_on_load'] = true;
			break;

			// Handle actual printer
			case 'printer': 
			if (false) {
				$display_buffer .= "<pre>\n".
					$TeX->RenderDebug().
					"</pre>\n(You must disable this to print)";
			} else {
			$TeX->SetPrinter(
				CreateObject('PHP.PrinterWrapper'),
				//$user->getManageConfig('default_printer')
				$_REQUEST['printer']
			);
			// TODO: Handle direct PDF generation and return here
			$TeX->PrintTeX(!empty($this->print_template));
			$GLOBALS['__freemed']['close_on_load'] = true;
			}
			break;

			default:
			print "print_method = ".$_pm."<br/>\n";
			break;
		}
	} // end function print

	// Method: _print_mapping
	//
	//	Callback to provide a macro mapping for TeX templating.
	//
	// Parameters:
	//
	//	$TeX - TeX rendering object of type <FreeMED.TeX>
	//
	//	$id - Record id of the target record.
	//
	// Returns:
	//
	//	Associative array containing mapping information.
	//
	function _print_mapping ( $TeX, $id ) {
		// By default superclass returns nothing
		return array ( );
	} // end method _print_mapping

	// Method: _TeX_Information
	//
	//	Callback to provide information to the TeX renderer about
	//	formatting. (Should be depreciated)
	//
	// Returns:
	//
	//	Array ( title, heading, physician )
	//
	function _TeX_Information ( ) {
		// abstract
		$rec = freemed::get_link_rec($_REQUEST['id'], $this->table_name);
		$patient = CreateObject('FreeMED.Patient', $_REQUEST['patient']);
		$user = CreateObject('FreeMED.User');
		if ($user->isPhysician()) {
			$phy = $user->getPhysician();
		} else {
			$phy = $patient->local_record['patphy'];
		}
		$physician_object = CreateObject('FreeMED.Physician', $phy);
		$title = __($this->record_name);
		$heading = $patient->fullName().' ('.$patient->local_record['ptid'].')';
		$physician = $physician_object->fullName();
		return array ($title, $heading, $physician);
	} // end method _TeX_Information

	// Method: BaseModule->setup
	//
	//	Overrides the internal phpwebtools setup method. This causes
	//	FreeMED to run either _setup() on first run, or _update()
	//	if the module has an older version installed.
	//
	function setup () {
		global $display_buffer;
		if (!freemed::module_check($this->MODULE_NAME,$this->MODULE_VERSION)) {
			// check if it is installed *AT ALL*
			if (!freemed::module_check($this->MODULE_NAME, "0.0001")) {
				// run internal setup routine
				$val = $this->_setup();
			} else {
				// run internal update routine
				$val = $this->_update();
			} // end checking to see if installed at all

			// register module
			freemed::module_register($this->MODULE_NAME, $this->MODULE_VERSION);

			return $val;
		} // end checking for module
	} // end function setup

	// _setup (in this case, wrapped in classes...)
	function _setup () { return true; }

	// _update (in this case, wrapped in classes...)
	function _update () { return true; }

	// Method: BaseModule->init
	//
	//	Initializes the module table in the database. This should
	//	only be called by the setup routines in FreeMED, otherwise
	//	it poses a major system risk.
	//
	function init($test) {
		global $sql;
	
		$result = $sql->query("DROP TABLE module"); 

		$result = $sql->query($sql->create_table_query(
			'module',
			array(
				'module_name' => SQL__VARCHAR(100),
				'module_version' => SQL__VARCHAR(50),
				'id' => SQL__SERIAL
			), array('id')
		));
		return $result;
	} // end method BaseModule->init

	//----- Internal module functions

	// Method: BaseModule->_GetAssociations
	//
	//	Get a list of associations for the current module.
	//
	// Returns:
	//
	//	Array of associations made to this module.
	//
	function _GetAssociations () {
		if (!is_array($GLOBALS['__phpwebtools']['GLOBAL_MODULES'])) {
			$modules = CreateObject(
				'PHP.module_list',
				PACKAGENAME,
				array(
					'cache_file' => 'data/cache/modules'
				)
			);
		}
		$associations = array();
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $__crap => $v) {
			$a = $v['META_INFORMATION']['__associations'];
			foreach ($a as $_k => $_v) {
				if (strtolower($_k) == strtolower($this->MODULE_CLASS)) {
					$associations[] = $_v;
				}
			}
		}
		return $associations;
	} // end method BaseModule->_GetAssociations

	// Method: BaseModule->_SetAssociation
	//
	//	Creates an association with another module.
	//
	// Parameters:
	//
	//	$with - Module name (class name) of module to associate with.
	//
	function _SetAssociation ($with) {
		$this->META_INFORMATION['__associations']["$with"] = get_class($this);
	} // end method BaseModule->_SetAssociation

	// Method: BaseModule->_SetAssociation
	//
	//	Attaches the current module to the specified system
	//	handler.
	//
	// Parameters:
	//
	//	$handler - Name of the system handler. Please note that
	//	this is case sensitive.
	//
	//	$method - Method that will be called by the specified handler.
	//	This is 'handler' by default.
	//
	function _SetHandler ($handler, $method = 'handler') {
		$this->META_INFORMATION['__handler']["$handler"] = $method;
	} // end method BaseModule->_SetHandler

} // end class BaseModule

?>
