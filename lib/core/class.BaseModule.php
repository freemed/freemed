<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.Module');

// Class: org.freemedsoftware.core.BaseModule
//
//	Basic FreeMED module class. All modules in FreeMED inheirit methods
//	from this class. It extends the phpwebtools module class.
//
class BaseModule extends Module {

	// override variables
	var $PACKAGE_NAME = PACKAGENAME;
	var $MODULE_AUTHOR = "jeff b (jeff@ourexchange.net)";
	var $MODULE_DESCRIPTION = "NO DESCRIPTION";
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
	public function __construct ( ) {
		// Call parent constructor
		parent::__construct ( );
		// Load language files, if necessary
		T_textdomain( strtolower( get_class( $this ) ) );
		// Push acl information, if there is any
		if ($this->acl) { $this->_SetMetaInformation('acl', $this->acl); }
	} // end constructor BaseModule

	// Method: _print
	//
	//	Provides basic printing interface
	//
	function _print ( ) {
		// Turn off the template

		// Deal with faxstatus
		if (isset($_REQUEST['faxstatus'])) {
			$fax = CreateObject('org.freemedsoftware.core.Fax', '/dev/null');
			$status = $fax->State($_REQUEST['faxstatus']);
			$display_buffer .= "<b>".$output."</b>\n";
			if ($status == 1) {
				$display_buffer .= "<div align=\"center\"><b>".__("Fax sent successfully.")."</b></div>\n";
				$display_buffer .= "<div align=\"center\"><a onClick=\"javascript:close();\" class=\"button\">".__("Close")."</div>\n";
			} else {
				$display_buffer .= "<b>".__("Fax is attempting to send: ")."</b>".$status."\n";
				$GLOBALS['__freemed']['automatic_refresh'] = 10;
			}
			return true;
			break;
		}

		// Handle render
		if ($render = $this->print_override($_REQUEST['id'])) {
			// Handle this elsewhere
		} else {
			// Create TeX object for patient
			$TeX = CreateObject( 'org.freemedsoftware.core.TeX' );

			// Actual renderer for formatting array
			if ($this->table_name) {
				if ($this->summary_query) {
					// If this is an EMR module with additional
					// fields, import them
					$query = "SELECT *".
						( (count($this->summary_query)>0) ? 
						",".join(",", $this->summary_query)." " : " " ).
						"FROM ".$this->table_name." ".
						"WHERE id='".addslashes($_REQUEST['id'])."'";
						$rec = $GLOBALS['sql']->queryRow( $query );
				} else {
					$rec = $GLOBALS['sql']->get_link($this->table_name, $_REQUEST['id']);
				} // end checking for summary_query
			} else {
				$rec = array ( 'id' => $_REQUEST['id'] );
			}

			// Check for overridden template
			if (file_exists('lib/tex/'.freemed::secure_filename($_REQUEST['print_template']).'.tex')) {
				$this_template = freemed::secure_filename($_REQUEST['print_template']);
			} else {
				$this_template = $this->print_template;
			}

			$TeX->_buffer = $TeX->RenderFromTemplate(
				$this_template,
				$rec
			);
		}

		// Get appropriate printer from user settings
		global $this_user;
		if (!is_object($this_user)) {
			$this_user = CreateObject('org.freemedsoftware.api.User');
		}
		$printer = CreateObject('PHP.PrinterWrapper');

		$display_buffer .= __("Printing")." ... <br/>\n";

		// Figure out print method
		$_pm = $_REQUEST['print_method'];
		switch ($_pm) {
			// Handle direct to browser
			case 'browser':
			if ($render) {
				$_file = $render;
			} else {
				$_file = $TeX->RenderToPDF(!empty($this->print_template));
			}
			if ($_REQUEST['attachment']) {
				// Render second PDF
				$parts = explode('|', $_REQUEST['attachment']);

				// Composite ...
				$comp = CreateObject('org.freemedsoftware.core.MultiplePDF');
				$comp->Add( $_file );
				$comp->Add( module_function($parts[0], '_RenderToPDF', array($parts[1])) );
				$file = $comp->Composite();
			} else {
				$file = $_file;
			}
			ob_start();
			readfile($file);
			$contents = ob_get_contents();
			ob_end_clean();

			Header('Content-Type: application/x-freemed-print-pdf');
			Header('Content-Length: '.strlen($contents));
			Header('Content-Disposition: inline; filename="'.mktime().'.pdf"');
			print $contents;
			flush();
			//print "file = $file<br/>\n";
			unlink($file);
			die();
			break;

			case 'fax':
			if ($render) {
				$_file = $render;
			} else {
				$_file = $TeX->RenderToPDF(!empty($this->print_template));
			}
			// Handle attachments
			if ($_REQUEST['attachment']) {
				// Render second PDF
				$parts = explode('|', $_REQUEST['attachment']);

				// Composite ...
				$comp = CreateObject('org.freemedsoftware.core.MultiplePDF');
				$comp->Add( $_file );
				$comp->Add( module_function($parts[0], '_RenderToPDF', array($parts[1])) );
				$file = $comp->Composite();
			} else {
				// Pass through ...
				$file = $_file;
			}
			$fax = CreateObject('org.freemedsoftware.core.Fax', 
				$file, 
				array (
					'sender' => $this_user->user_descrip,
					'comments' => __("HIPPA Compliance Notice: This transmission contains confidential medical information which is protected by the patient/physician privilege. The enclosed message is being communicated to the intended recipient for the purposes of facilitating healthcare. If you have received this transmission in error, please notify the sender immediately, return the fax message and delete the message from your system.")
				)
			);
			//print ($_REQUEST['fax_number']);
			$output = $fax->Send($_REQUEST['fax_number']);
			//$display_buffer .= "<b>".$output."</b>\n";
			// TODO : Descrip call back
			if ($this->patient_field) {
				$_r = $GLOBALS['sql']->get_link( $this->table_name, $_REQUEST['id'] );
				$_p = CreateObject('org.freemedsoftware.core.Patient', $_r[$this->patient_field]);
				$descrip = $this->record_name.' for '.$_p->fullName();

				$this_user->setFaxInQueue(
					$output,
					$_r[$this->patient_field],
					$_REQUEST['fax_number'],
					get_class($this),
					$_REQUEST['id']
				);
				if ($_REQUEST['attachment']) {
					$parts = explode('|', $_REQUEST['attachment']);
					$this_user->setFaxInQueue(
						$output,
						$_r[$this->patient_field],
						$_REQUEST['fax_number'],
						$parts[0],
						$parts[1]
					);
				}
			}
			$display_buffer .= "<b>".__("Refreshing")."... </b>\n";
			//$GLOBALS['refresh'] = $this->page_name."?".
			//	"module=".urlencode($_REQUEST['module'])."&".
			//	"type=".urlencode($_REQUEST['type'])."&".
			//	"action=print&".
			//	"faxstatus=".urlencode($output);
			$GLOBALS['__freemed']['close_on_load'] = true;
			break;

			// Handle actual printer
			case 'printer': 
			if ($render) {
				$_p = CreateObject('PHP.PrinterWrapper');
				$_p->PrintFile($_REQUEST['printer'], $render);
				unlink($render);
			} else {
				// DEBUG:
				//$display_buffer .= "<pre>\n".
				//	$TeX->RenderDebug().
				//	"</pre>\n(You must disable this to print)";
				//template_display();
				$TeX->SetPrinter(
					CreateObject('PHP.PrinterWrapper'),
					//$user->getManageConfig('default_printer')
					$_REQUEST['printer']
				);
				// TODO: Handle direct PDF generation and return here
				$TeX->PrintTeX(1, !empty($this->print_template));
			}
			$GLOBALS['__freemed']['close_on_load'] = true;
			break;

			default:
			print "print_method = ".$_pm."<br/>\n";
			break;
		}
	} // end function print

	// Method: setup
	//
	//	Overrides the internal phpwebtools setup method. This causes
	//	FreeMED to run either _setup() on first run, or _update()
	//	if the module has an older version installed.
	//
	function setup () {
		if (!freemed::module_check($this->MODULE_UID, $this->MODULE_VERSION)) {
			// check if it is installed *AT ALL*
			if (!freemed::module_check($this->MODULE_UID, "0.0001")) {
				// run internal setup routine
				$val = $this->_setup();
			} else {
				// run internal update routine
				$val = $this->_update();
			} // end checking to see if installed at all

			// register module
			freemed::module_register($this->MODULE_UID, $this->MODULE_VERSION);

			return $val;
		} // end checking for module
	} // end function setup

	// _setup (in this case, wrapped in classes...)
	public function _setup () { return true; }

	// _update (in this case, wrapped in classes...)
	public function _update () { }

	//----- Internal module functions

	// Method: print_override
	//
	//	Use this to replace the default printing behavior of the
	//	system.
	//
	// Parameters:
	//
	//	$id - Record ID to be "printed" as a PDF.
	//
	// Returns:
	//
	//	Filename of PDF file containing render.
	//
	function print_override ( $id ) {
		return false; // STUB, so we don't use it most of the time
	} // end method print_override

	// Method: BaseModule->_GetAssociations
	//
	//	Get a list of associations for the current module.
	//
	// Returns:
	//
	//	Array of associations made to this module.
	//
	function _GetAssociations () {
		$index = freemed::module_cache();
		$associations = array();
		foreach ( $index AS $module ) {
			$a = $module['META_INFORMATION']['__associations'];
			foreach ($a as $_k => $_v) {
				if (strtolower($_k) == strtolower($this->MODULE_CLASS)) {
					$associations[] = $_v;
				}
			}
		}
		return $associations;
	} // end method _GetAssociations

	// Method: BaseModule->_SetAssociation
	//
	//	Creates an association with another module.
	//
	// Parameters:
	//
	//	$with - Module name (class name) of module to associate with.
	//
	protected function _SetAssociation ( $with ) {
		$this->META_INFORMATION['__associations_list'][] = $with;
		$this->META_INFORMATION['__associations']["$with"] = get_class($this);
	} // end method BaseModule->_SetAssociation

	// Method: BaseModule->_SetHandler
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
	protected function _SetHandler ($handler, $method = 'handler') {
		$this->META_INFORMATION['__handler']["$handler"] = $method;
	} // end method BaseModule->_SetHandler

} // end class BaseModule

?>
