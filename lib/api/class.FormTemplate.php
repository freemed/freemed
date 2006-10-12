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

// Class: org.freemedsoftware.api.FormTemplate
//
//	Use XML templates to fill out information on PDF forms.
//
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
		if (!isset($template)) {
			$template = simplexml_load_file ( $this->xml_template );
		} // end caching
		return $template;
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
		$query = "SELECT fr_name AS k, fr_value AS v ".
			"FROM form_record ".
			"WHERE fr_id = '".addslashes($id)."'";
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
		$this->patient = CreateObject('_FreeMED.Patient', $patient_id);
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
				$obj = CreateObject('_FreeMED.'.$objectname, $this->patient->local_record[$params[1]]);
				$method = ( $params[2] ? $params[2] : 'to_text' );
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
				$query = "SELECT *".
					( (count($m->summary_query)>0) ? 
					",".join(",", $m->summary_query)." " : " " ).
					"FROM ".$m->table_name." ".
					"WHERE ".$m->patient_field."='".addslashes($this->patient->id)."' ".
					($m->summary_conditional ? 'AND '.$m->summary_conditional.' ' : '' ).
					"ORDER BY id DESC LIMIT 1";
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
					return module_function($params[0], 'get_field', array($raw, $params[1]));
				} else {
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
			Header('Content-Disposition: inline; filename="'.mktime().'.pdf"');
			print `${cmd}`;
			die();
		}

		// Otherwise, get this as a string
		$filename = '/tmp/form'.mktime().'.pdf';
		$fp = fopen($filename, 'w');
		fputs($fp, `${cmd}`);
		fclose($fp);
		return $filename;
	} // end method RenderToPDF

} // end class FormTemplate

?>
