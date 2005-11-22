<?php
	// $Id$
	// $Author$

// Class: FreeMED.FormTemplate
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
	function FormTemplate ( $template ) {
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
		if (!is_array($this->data)) { return ''; }
		return $this->elements[$key];
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
		$controls_dom =& $template->elementsByName('controls');
		foreach ($controls_dom[0]->Children AS $control) {
			unset ($c);
			foreach ($control->Attributes AS $attr) {
				$c[$attr->Name] = $attr->Content;
			}
			$results[$c['variable']] = $c;
		} // end foreach control
		return $results;
	} // end method GetControls

	// Method: GetInformation
	function GetInformation ( ) {
		$template = $this->GetXMLTemplate();

		$information_dom =& $template->elementsByName('information');
		foreach ($information_dom['0']->Children AS $i) {
			$information[$i->Name] = $i->Children[0]->Content;
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
			$template_obj = CreateObject('_FreeMED.eZXML');
			$template =& $template_obj->domTree( file_get_contents($this->xml_template), array( "TrimWhiteSpace" => true  ) );
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
		$this->data = freemed::get_link_rec ( $id, 'form_results' );
		$this->LoadPatient ( $this->data['fr_patient'] );

		// Cache all data elements
		unset($this->elements);
		$query = "SELECT fr_name AS k, fr_value AS v ".
			"FROM form_record ".
			"WHERE fr_id = '".addslashes($id)."'";
		$result = $GLOBALS['sql']->query ( $query );
		while ( $r = $GLOBALS['sql']->fetch_array ( $result ) ) {
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
		$page_dom =& $template->elementsByName('page');
		foreach ($page_dom AS $sub) {
			// Extract original page id (need to translate as-is)
			$oid = $sub->Attributes[0]->Content;
			//print "<b>processing page $oid</b><br/>\n";

			$output .= '<page oid="'.$oid.'">'."\n";

			// Loop through all children elements ...
			//	d = data element
			//	e = element attributes
			foreach ($sub->Children AS $element) {
				foreach ($element->Attributes AS $attr) {
					$e[$attr->Name] = $attr->Content;
				}
				foreach ($element->Children AS $children) {
					if ($children->Name == 'data') {
						foreach ($children->Attributes AS $attr) {
							$d[$attr->Name] = $attr->Content;
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
			case 'ssn':
				return substr($raw, 0, 3).'-'.substr($raw, 3, 2).'-'.substr($raw, 5, 4);
				break;

			case 'conditional':
				if ($data['table'] == 'static') { return 'X'; }
				if ($data['value'] == $raw) { return 'X'; }
				else return '';
				break;

			case 'phone':
				return freemed::phone_display ($raw);
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
		$filename = '/tmp/form'.mktime();
		$fp = fopen($filename, 'w');
		fputs($fp, `${cmd}`);
		fclose($fp);
		return $filename;
	} // end method RenderToPDF

} // end class FormTemplate

?>
