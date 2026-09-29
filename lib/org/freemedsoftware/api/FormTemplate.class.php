<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2012 FreeMED Software Foundation
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

// Class: org.freemedsoftware.api.FormTemplate
//
//	Use XML templates to fill out information on PDF forms.
//
//	Task 2.8, fix round 2 (N2). This class is relay-reachable and it
//	RE-DISPATCHES, on both axes, from stored template data:
//
//	  * ProcessData()'s `object:` branch builds a class out of the template's
//	    table attribute -- CreateObject('org.freemedsoftware.core.'.$objectname)
//	    -- and then calls a method named by the template's field attribute,
//	    $obj->${method}(). Concrete string:
//	    `org.freemedsoftware.core.<objectname>.<method>`.
//	  * ProcessData()'s `module:` branch passes the template's module name and
//	    the method named after `method:` to module_function(). Concrete string:
//	    `org.freemedsoftware.module.<modulename>.<method>`.
//	  * ProcessData()'s link:type branch does the same with the template's
//	    'value' attribute and two fixed literals ('get_field', 'to_text').
//
//	Both axes are now gated on the relay's shared decision point
//	(Relay_Allowlist::refuse(), OUTER scope). The OUTER scope is deliberate and
//	is the provenance argument, not an oversight: the dispatched names come from
//	the XML template FILE this object was pointed at
//	(data/form/templates/<name>.xml), NOT from the request, so refusing them
//	unconditionally in both stages would be a NEW refusal on a path that ran
//	before this control existed -- including in the shipped log-only stage. The
//	inner never-allow scope is reserved for a re-dispatcher whose method name
//	comes from ITS caller (Multicall); see Relay_Allowlist::refuse(). A
//	never-allow namespace hit here is therefore LOGGED at LOG_WARNING and not
//	refused, and the normal allowlist decision (log-only: log and run;
//	enforcing: INVALID_CALL) still applies.
//
//	Reachability, for the record: the relay cannot name this class directly
//	(nothing in the shipped list is org.freemedsoftware.api.FormTemplate.*), but
//	the LISTED ModuleInterface.PrintToPrinter / PrintToBrowser / PrintToFax
//	reach it through <module>::RenderToPDF -> EMRModule::RenderToPDF ->
//	print_override() -> Forms::print_override() -> CreateObject('...core.FormTemplate')
//	-> OutputData() -> ProcessElement() -> ProcessData(). A GWT client can also
//	build a SupportModuleWidget("FormTemplate"). In this checkout NO XML
//	template ships at all (data/form/templates/ does not exist), so the concrete
//	strings depend on what a site deploys; the shipped log-only stage is what
//	measures them.
LoadObjectDependency('org.freemedsoftware.core.SqlIdent');
LoadObjectDependency('org.freemedsoftware.core.Relay_Allowlist');

class FormTemplate {

	var $data;
	var $elements;
	var $patient;
	var $xml_template;

	// Constructor: FormTemplate
	//
	// Parameters:
	//
	//	$template - Relative name of XML template file. This file should
	//	exist in freemedroot/data/form/templates/ as a file with the
	//	extension '.xml', so 'test' would resolve to 'test.xml'.
	//
	public function __construct ( $template = NULL ) {
		if ( $template ) { $this->Initialize( $template ); }
	} // end constructor

	// Method: Initialize
	//
	// Parameters:
	//
	//	$template - Relative name of XML template file. This file should
	//	exist in freemedroot/data/form/templates/ as a file with the
	//	extension '.xml', so 'test' would resolve to 'test.xml'.
	//
	public function Initialize ( $template ) {
		$this->xml_template = dirname(dirname(__FILE__)).'/data/form/templates/'.$template.'.xml';
		if (!file_exists($this->xml_template)) {
			trigger_error(__("Template does not exist!"), E_USER_ERROR);
		}
	} // end constructor FormTemplate

	// Method: FetchDataElement
	//
	//	Get data element value associated with the currently
	//	loaded data set.
	//
	// Parameters:
	//
	//	$key - Data element key
	//
	// Returns:
	//
	//	Value. Will return empty string if no data set is loaded.
	//
	function FetchDataElement ( $key ) {
		// Lookup the appropriate data point
		static $controls;
		if (!$controls) { $controls = $this->GetControls(); }

		// Do a quick null check for controls ...
		if (!is_array($this->elements)) { return ''; }
		if (!is_array($controls)) { return ''; }

		// Return the appropriate value
		return $this->elements[$controls[$key]['name']];
	} // end method FetchDataElement

	// Method: GetControls
	//
	//	Get list of custom controls from a template.
	//
	// Returns:
	//
	//	Multidimensional array containing controls description. Keys are
	//	the variable name.
	//
	function GetControls ( ) {
		$template = $this->GetXMLTemplate();

		// Extract pages, go one by one
		foreach ( $template->xpath('//controls/control') AS $control ) {
			unset ($c);
			foreach ( $control->attributes() AS $name => $content ) {
				$c[$name] = (string) $content;
			}
			$results[$c['variable']] = $c;
		} // end foreach control
		return $results;
	} // end method GetControls

	// Method: GetInformation
	//
	//	Retrieve information hash regarding template.
	//
	// Returns:
	//
	//	Hash.
	//
	function GetInformation ( ) {
		$template = $this->GetXMLTemplate();

		$r = $template->xpath( '//information' );
		foreach ($r[0] AS $k => $v) {
			$information[$k] = (string) $v;
		}
		return $information;
	} // end method GetInformation

	// Method: GetXMLTemplate
	//
	//	Get XML template DOM tree, with caching.
	//
	// Returns:
	//
	//	eZXML object representing XML DOM tree for the loaded template.
	//
	function GetXMLTemplate ( ) {
		static $template;
		if (!isset($template[$this->xml_template])) {
			$template[$this->xml_template] = simplexml_load_file ( $this->xml_template );
		} // end caching
		return $template[$this->xml_template];
	} // end method GetXMLTemplate

	// Method: LoadData
	//
	//	Load a form_results entry into the current set.
	//
	// Parameters:
	//
	//	$id - Row ID of form_results table entry
	//
	function LoadData ( $id ) {
		$this->data = $GLOBALS['sql']->get_link ( 'form_results', $id );
		$this->LoadPatient ( $this->data['fr_patient'] );

		// Cache all data elements
		unset($this->elements);
		// Category A (2.6b): fr_id is the form_results record id (INT UNSIGNED in
		// data/schema/mysql/form_record.sql), so it is cast instead of
		// addslashes()ed inside hand-written quotes.
		$query = sprintf( 'SELECT fr_name AS k, fr_value AS v FROM form_record WHERE fr_id = %d',
			intval( $id ) );
		$result = $GLOBALS['sql']->queryAll ( $query );
		foreach ( $result AS $r ) {
			$this->elements[stripslashes($r['k'])] = stripslashes($r['v']);
		} // end while results
	} // end method LoadData

	// Method: LoadPatient
	//
	//	Load patient information into an XML form template.
	//
	// Parameters:
	//
	//	$patient_id - Row ID of patient table row
	//
	function LoadPatient ( $patient_id ) {
		if (!$patient_id) { trigger_error(__("Must specify a patient id!"), E_USER_ERROR); }
		$this->patient = CreateObject('org.freemedsoftware.core.Patient', $patient_id);
	} // end method LoadPatient

	// Method: OutputData
	//
	//	Push data out to final XML data, composited
	//
	// Returns:
	//
	//	XML string
	//
	function OutputData ( ) {
		$output = '<'.'?xml version="1.0"?'.'>'."\n";
		$output .= '<form>'."\n";
		$template = $this->GetXMLTemplate();

		// Extract information element
		$information = $this->GetInformation();
		
		// Re-render information tags (with changes if necessary)
		$output .= '<information>'."\n";
		foreach ($information AS $k => $v) {
			$output .= "\t<$k>".htmlentities($v)."</$k>\n";
		}
		$output .= '</information>'."\n";

		// Extract pages, go one by one
		$pages = $template->xpath( "//page" );
		foreach ($pages AS $page) {
			// Extract original page id (need to translate as-is)
			$oid = $page->attributes()->oid;
			//print "<b>processing page $oid</b><br/>\n";

			$output .= '<page oid="'.$oid.'">'."\n";

			// Loop through all children elements ...
			//	d = data element
			//	e = element attributes
			foreach ($page AS $element) {
				foreach ($element->attributes() AS $name => $content) {
					$e[$name] = (string) $content;
				}
				foreach ($element AS $name => $children) {
					if ($name == 'data') {
						foreach ($children->attributes() AS $name => $content) {
							$d[$name] = (string) $content;
						}
					}
				}

				$output .= $this->ProcessElement($e, $d);
			} // end foreach children elements

			// Add page footer
			$output .= "</page>\n";
		} // end foreach page

		// Document footer
		$output .= '</form>'."\n";

		return $output;
	} // end method OutputData

	// Method: ProcessElement
	//
	//	Produce output XML from template element.
	//
	// Parameters:
	//
	//	$attr - Array of attribute values for element
	//
	//	$data - Array of data values from data element
	//
	// Returns:
	//
	//	XML formatted elements.
	//
	function ProcessElement ( $attr, $data ) {
		// Don't push the element if we don't have any data coming back and
		// it's an outline element.
		if ($attr['type'] == 'outline') {
			if ($this->ProcessData($data)) {
				$enable_output = true;
			} else {
				$enable_output = false;
			}
		} else {
			$enable_output = true;
		}

		if ($enable_output) {
			$output = '<element ';
			foreach ($attr AS $k => $v) {
				$output .= $k . '="' . htmlentities($v) . '" ';
			}
			$output .= ">\n";

			// Push data
			$output .= '<data>'.htmlentities($this->ProcessData($data))."</data>\n";
	
			$output .= "</element>\n";
			return $output;
		} else {
			return '';
		}
	} // end method ProcessElement

	// Method: ProcessData
	//
	//	Process data elements to produce appropriate data
	//
	// Parameters:
	//
	//	$data - Data array
	//
	// Returns:
	//
	//	String
	//
	function ProcessData ( $data ) {
		$cache = freemed::module_cache();

		// Handle "module:" prefix
		if (substr($data['table'], 0, 7) == 'object:') {
			$objectname = substr($data['table'], -(strlen($data['table'])-7));
			$params = explode(':', $data['field']);
			if ($params[0] == 'patient') {
				$method = ( $params[2] ? $params[2] : 'to_text' );
				// N2: the concrete string this dispatch performs, gated on the
				// relay's shared decision point (outer scope -- see the class
				// comment for the provenance argument).
				if ( ! $this->_allowlist_gate ( 'org.freemedsoftware.core.' . $objectname . '.' . $method ) ) {
					return '';
				}
				$obj = CreateObject('org.freemedsoftware.core.'.$objectname, $this->patient->local_record[$params[1]]);
				$raw = $obj->${method}();
			} else {
				syslog(LOG_INFO, get_class($this)."| could not process ${data['table']}, ${data['field']}");
				return '';
			}
		} elseif (substr($data['table'], 0, 7) == 'module:') {
			$modulename = substr($data['table'], -(strlen($data['table'])-7));
			// Deal with method: prefix on data
			if (substr($data['field'], 0, 7) == 'method:') {
				$params = explode(':', $data['field']);
				// N2: the class axis -- module_function() dispatches
				// `org.freemedsoftware.module.<modulename>.<$params[1]>`.
				if ( ! $this->_allowlist_gate ( 'org.freemedsoftware.module.' . $modulename . '.' . $params[1] ) ) {
					return '';
				}
				$raw = module_function(
					$modulename,
					$params[1],
					array (
						( $params[2] ? $params[2] : $this->patient->id )
					)
				);
			} else {
				// Load information from module
				include_once(resolve_module($modulename));
				$m = new $modulename ();

				// Run SQL query
				// Category B+C (2.6b): the module's declared table and patient
				// column are identifiers, so they go through SqlIdent::name(); a
				// refusal logs and returns no value rather than splicing the
				// token. The patient id is driver-quoted instead of
				// addslashes()ed inside hand-written quotes (Category A), and the
				// module's code-authored expressions are shape-checked before
				// they are spliced (see _SafeExpression).
				$m_table = SqlIdent::name( $m->table_name );
				$m_pfield = SqlIdent::name( $m->patient_field );
				if ( $m_table === false or $m_pfield === false ) {
					syslog( LOG_ERR, get_class($this).'::ProcessData| refusing invalid module identifier '.var_export(array($m->table_name, $m->patient_field), true) );
					return "";
				}
				// Guard the PHP 8.3 count(NULL) TypeError: modules that do not
				// declare $summary_query leave it NULL (see EMRModule::qualified_query).
				$m_cols = array();
				foreach ( ( is_array($m->summary_query) ? $m->summary_query : array() ) AS $m_col ) {
					if ( $this->_SafeExpression( $m_col ) ) {
						$m_cols[] = $m_col;
					} else {
						syslog( LOG_ERR, get_class($this).'::ProcessData| dropping unsafe summary_query expression '.var_export($m_col, true) );
					}
				}
				$m_cond = '';
				if ( $m->summary_conditional ) {
					if ( $this->_SafeExpression( $m->summary_conditional ) ) {
						$m_cond = 'AND '.$m->summary_conditional.' ';
					} else {
						syslog( LOG_ERR, get_class($this).'::ProcessData| dropping unsafe summary_conditional '.var_export($m->summary_conditional, true) );
					}
				}
				$query = sprintf( 'SELECT *%s FROM %s WHERE %s=%s %sORDER BY id DESC LIMIT 1',
					( $m_cols ? ','.join(',', $m_cols).' ' : ' ' ),
					$m_table, $m_pfield, $GLOBALS['sql']->quote( $this->patient->id ),
					$m_cond );
				$result = $GLOBALS['sql']->query($query);
				if ($GLOBALS['sql']->num_rows($result) != 1) {
					syslog(LOG_INFO, get_class($this)."| could not retrieve rows for ${data['table']}, ${data['field']}");
					return "";
				}
				$r = $GLOBALS['sql']->fetch_array($result);
				return $r[$data['field']];
			}
		} else {
			// Deal with straight abbreviations for data
			switch ($data['table']) {
				case 'patient':
					if (strpos($data['field'], ':') === false) {
						$raw = $this->patient->local_record[$data['field']];
					} else {
						list ($desc, $field) = explode(':', $data['field']);
						switch ($desc) {
							case 'method':
								$raw = $this->patient->${field}();
								break; // end method
							default:
								syslog(LOG_INFO, get_class($this)."| could not figure out syntax for ${data['table']}, ${data['field']}");
								$raw = "";
								break; // end default
						} // end switch desc
					}
					break;

				case 'control':
					$raw = $this->FetchDataElement($data['field']);
					break;

				case 'static':
					$raw = $data['field'];
					break;

				default:
					break;
			} // end switch
		}

		// Deal with output formatting
		switch ($data['type']) {
			case 'link':
				if (!$data['value']) {
					syslog(LOG_INFO, get_class($this)."| could not process ${data['table']}, ${data['field']}, ${data['value']}");
					return '';
				}
				if ( strpos($data['value'], ':') !== false ) {
					$params = explode(':', $data['value']);
					// N2: the class axis again, with the fixed literal 'get_field'.
					if ( ! $this->_allowlist_gate ( 'org.freemedsoftware.module.' . $params[0] . '.get_field' ) ) {
						return '';
					}
					return module_function($params[0], 'get_field', array($raw, $params[1]));
				} else {
					// N2: and again, with the fixed literal 'to_text'.
					if ( ! $this->_allowlist_gate ( 'org.freemedsoftware.module.' . $data['value'] . '.to_text' ) ) {
						return '';
					}
					return module_function($data['value'], 'to_text', array($raw));
				}
				break;

			case 'ssn':
				return substr($raw, 0, 3).'-'.substr($raw, 3, 2).'-'.substr($raw, 5, 4);
				break;

			case 'conditional':
				// Handle "static" type
				if ($data['table'] == 'static') { return 'X'; }

				// Handle "multiple" type
				if ($data['table'] == 'control') {
					if (!isset($this->controls)) { $this->controls = $this->GetControls(); }
					if ($this->controls[$data['field']]['type'] == 'multiple') {
						foreach (explode(',', $raw) AS $value) {
							if ($data['value'] == $value) { return 'X'; }
						}
						return '';
					}
				}

				// Handle everything else
				if ($data['value'] == $raw) { return 'X'; }
				else return '';
				break;

			case 'phone':
				return freemed::phone_display ($raw);
				break;

			case 'date':
				if (!$raw) { return ''; }
				$_date = explode('-', $raw);
				switch (freemed::config_value('dtfmt')) {
					case 'ymd':
						return $raw;
						break;
					case 'mdy': default:
						return "${_date[1]}/${_date[2]}/${_date[0]}";
						break;
				}
				// Should never get here
				return $raw;
				break;

			case 'string':
			default:
				return $raw;
				break;
		} // end data type
	} // end method ProcessData

	// Method: RenderToPDF
	//
	//	Render a template to a PDF file from a composited XML data string.
	//
	// Parameters:
	//
	//	$data - Composited XML data string
	//
	//	$output - (optional) Boolean, output to browser. Default is false,
	//	return as file name.
	//
	// Returns:
	//
	//	Optionally returns filename.
	//
	function RenderToPDF ( $data, $output = false ) {
		// Push to temporary file
		$tmp = tempnam('/tmp', 'formtemplate-');
		$fp = fopen($tmp, 'w');
		fputs($fp, $data);
		fclose($fp);

		$script = "./scripts/composite_form.pl";
		$cmd = "${script} \"${tmp}\"";

		if ($output) {
			Header('Content-type: application/x-pdf');
			Header('Content-Disposition: inline; filename="'.time().'.pdf"');
			print `${cmd}`;
			die();
		}

		// Otherwise, get this as a string
		$filename = '/tmp/form'.time().'.pdf';
		$fp = fopen($filename, 'w');
		fputs($fp, `${cmd}`);
		fclose($fp);
		return $filename;
	} // end method RenderToPDF

	// Method: _SafeExpression
	//
	//	Shape check for the code-authored SQL expressions a module declares for
	//	the summary query: the entries of `summary_query` (e.g.
	//	"DATE_FORMAT(dateof, '%m/%d/%Y') AS my_date") and `summary_conditional`
	//	(e.g. "ptsex = 'm'"). They are source-declared, not request data, and they
	//	legitimately contain format specifiers and result aliases - which is why
	//	<SqlIdent::expression()> (an `AS <alias>` tail, no `%`) cannot be used
	//	here. The check is therefore the negative one: the expression must not be
	//	able to terminate the statement or open a comment.
	//
	// Parameters:
	//
	//	$expr - Candidate expression string
	//
	// Returns:
	//
	//	boolean - true when the value is safe to splice into the statement
	//
	private function _SafeExpression ( $expr ) {
		if ( !is_string( $expr ) or trim( $expr ) == '' ) { return false; }
		return !preg_match( '/[;`\x00]|--|\/\*|#/', $expr );
	} // end method _SafeExpression

	// Method: _allowlist_gate
	//
	//	N2 (Task 2.8 fix round 2): run one INNER dispatch this class is about
	//	to perform through the relay's shared decision point, so that the
	//	allowlist -- which names relay METHOD strings -- sees the concrete
	//	string the dispatch actually performs and not only the outer method
	//	that got the request here.
	//
	//	The caller passes the concrete string it is about to dispatch; the
	//	class comment above states where each one comes from and why the scope
	//	here is the OUTER one (the names come from the template file, not from
	//	the request).
	//
	// Parameters:
	//
	//	$concrete - The concrete relay method string about to be dispatched.
	//
	// Returns:
	//
	//	Boolean. TRUE means the dispatch may proceed, FALSE means it must not
	//	(and the caller returns an empty value, which is what this class does
	//	when a data element cannot be processed).
	private function _allowlist_gate ( $concrete ) {
		// M1 degradation path: a missing class file must not fatal the request.
		if ( ! class_exists ( 'Relay_Allowlist' ) ) { return true; }
		return ! Relay_Allowlist::refuse ( $concrete );
	} // end method _allowlist_gate

} // end class FormTemplate

?>
