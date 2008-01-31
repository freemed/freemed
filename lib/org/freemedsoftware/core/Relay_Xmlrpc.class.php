<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //      Dan Libby <dan@libby.com>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2008 FreeMED Software Foundation
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

LoadObjectDependency('org.freemedsoftware.core.Relay');

if (!defined('xmlrpcI4')) {
	define( 'xmlrpcI4', 'i4' );
	define( 'xmlrpcInt', 'int' );
	define( 'xmlrpcBoolean', 'boolean' );
	define( 'xmlrpcDouble', 'double' );
	define( 'xmlrpcString', 'string' );
	define( 'xmlrpcDateTime', 'dateTime.iso8601' );
	define( 'xmlrpcBase64','base64' );
	define( 'xmlrpcArray','array' );
	define( 'xmlrpcStruct', 'struct' );
}

// Class: org.freemedsoftware.core.Relay_Xmlrpc
//
//	XML-RPC data relay methods.
//
class Relay_Xmlrpc extends Relay {

	protected $xmlrpcTypes = array (
		xmlrpcI4       => 1,
		xmlrpcInt      => 1,
		xmlrpcBoolean  => 1,
		xmlrpcString   => 1,
		xmlrpcDouble   => 1,
		xmlrpcDateTime => 1,
		xmlrpcBase64   => 1,
		xmlrpcArray    => 2,
		xmlrpcStruct   => 3
	);

	protected $xmlEntities = array (
		'amp'  => '&',
		'quot' => '"',
		'lt'   => '<',
		'gt'   => '>',
		'apos' => "'"
	);

	protected $xmlrpcerr = array (
		'unknown_method'     => 1,
		'invalid_return'     => 2,
		'incorrect_params'   => 3,
		'introspect_unknown' => 4,
		'http_error'         => 5,
		'no_data'            => 6,
		'no_ssl'             => 7,
		'curl_fail'          => 8,
		'no_access'          => 9
	);

	protected $xmlrpcstr = array (
		'unknown_method'     => 'Unknown method',
		'invalid_return'     => 'Invalid return payload: enabling debugging to examine incoming payload',
		'incorrect_params'   => 'Incorrect parameters passed to method',
		'introspect_unknown' => "Can't introspect: method unknown",
		'http_error'         => "Didn't receive 200 OK from remote server.",
		'no_data'            => 'No data received from server.',
		'no_ssl'             => 'No SSL support compiled in.',
		'curl_fail'          => 'CURL error',
		'no_access'          => 'Access denied'
	);

	protected $xmlrpc_defencoding = 'UTF-8';

	// let user errors start at 800
	protected $xmlrpcerruser = 800; 
	// let XML parse errors start at 100
	protected $xmlrpcerrxml = 100;

	protected $_xh;
	protected $parser;

	// formulate backslashes for escaping regexp
	protected $xmlrpc_backslash = "\x5C\x5C"; // chr(92) . chr(92);

	// Method: deserialize_request
	//
	//	Deserialize the incoming request
	//
	// Parameters:
	//
	//	$request - Request, as received by the relay
	//
	// Returns:
	//	Array containing:
	//	* method
	//	* params (always an array)
	//
	public function deserialize_request ( $data ) {
		// Switch to XML-RPC error handler
		error_reporting( );
		set_error_handler( 'xmlrpc_error_handler' );

		$this->parser = xml_parser_create( $this->xmlrpc_defencoding );
	
		$this->_xh = array();
		$this->_xh['st']     = '';
		$this->_xh['cm']     = 0; 
		$this->_xh['isf']    = 0; 
		$this->_xh['params'] = array();
		$this->_xh['method'] = '';

		// decompose incoming XML into request structure
		xml_set_object( $this->parser, $this );
		xml_parser_set_option( $this->parser, XML_OPTION_CASE_FOLDING, true );
		xml_set_element_handler( $this->parser, 'xmlrpc_se', 'xmlrpc_ee' );
		xml_set_character_data_handler( $this->parser, 'xmlrpc_cd' );
		xml_set_default_handler( $this->parser, 'xmlrpc_dh' );
		if (!xml_parse( $this->parser, $data, 1 )) {
			// return XML error as a faultCode
			$r = CreateObject('org.freemedsoftware.core.xmlrpcresp','',
				$this->xmlrpcerrxml + xml_get_error_code($this->parser),
				sprintf('XML error: %s at line %d',
				xml_error_string(xml_get_error_code($this->parser)),
				xml_get_current_line_number($this->parser))
			);
			xml_parser_free($this->parser);
		} else {
			xml_parser_free($this->parser);
			$method = $this->_xh['method'];
			$rawparams = CreateObject( 'org.freemedsoftware.core.xmlrpcval' );
			foreach ($this->_xh['params'] AS $_p) {
				eval('$p[] = '.$_p.';');
			}
			$rawparams->addArray ( $p );
			$params = $this->xmlrpc_php_decode ( $rawparams );
			return array (
				'method' => $method,
				'params' => $params
			);
		}
	} // end method deserialize_request

	// Method: serialize_response
	//
	//	Serialize the outgoing response back to the client
	//
	// Parameters:
	//
	//	$response - Response to be serialized
	//
	// Returns:
	//
	//	Serialized data string
	//
	public function serialize_response ( $response ) {
		$msg = CreateObject( 'org.freemedsoftware.core.xmlrpcresp', $this->xmlrpc_php_encode( $response ) );
		return $msg->serialize( );
	} // end public function serialize_response

	protected function xmlrpc_se( $parser, $name, $attrs ) {
		switch($name) {
			case 'STRUCT':
			case 'ARRAY':
				$this->_xh['st'] .= 'array(';
				$this->_xh['cm']++;
				// this last line turns quoting off
				// this means if we get an empty array we'll 
				// simply get a bit of whitespace in the eval
				$this->_xh['qt']=0;
				break;
			case 'NAME':
				$this->_xh['st'] .= "'";
				$this->_xh['ac'] = '';
				break;
			case 'FAULT':
				$this->_xh['isf'] = 1;
				break;
			case 'PARAM':
				$this->_xh['st'] = '';
				break;
			case 'VALUE':
				$this->_xh['st'] .= " CreateObject('org.freemedsoftware.core.xmlrpcval',"; 
				$this->_xh['vt']  = xmlrpcString;
				$this->_xh['ac']  = '';
				$this->_xh['qt']  = 0;
				$this->_xh['lv']  = 1;
				// look for a value: if this is still 1 by the
				// time we reach the first data segment then the type is string
				// by implication and we need to add in a quote
				break;
			case 'I4':
			case 'INT':
			case 'STRING':
			case 'BOOLEAN':
			case 'DOUBLE':
			case 'DATETIME.ISO8601':
			case 'BASE64':
				$this->_xh['ac']=''; // reset the accumulator

				if ($name=='DATETIME.ISO8601' || $name=='STRING') {
					$this->_xh['qt']=1;
					if ($name=='DATETIME.ISO8601') {
						$this->_xh['vt']=xmlrpcDateTime;
					}
				} elseif($name=='BASE64') {
					$this->_xh['qt']=2;
				} else {
					// No quoting is required here -- but
					// at the end of the element we must check
					// for data format errors.
					$this->_xh['qt']=0;
				}
				break;
			case 'MEMBER':
				$this->_xh['ac']='';
				break;
			default:
				break;
		}

		if ($name!='VALUE') {
			$this->_xh['lv']=0;
		}
	}

	protected function xmlrpc_ee($parser, $name) {
		switch($name) {
			case 'STRUCT':
			case 'ARRAY':
				if ($this->_xh['cm'] && substr($this->_xh['st'], -1) ==',') {
					$this->_xh['st']=substr($this->_xh['st'],0,-1);
				}
				$this->_xh['st'] .= ')';
				$this->_xh['vt'] = strtolower($name);
				$this->_xh['cm']--;
				break;
			case 'NAME':
				$this->_xh['st'] .= $this->_xh['ac'] . "' => ";
				break;
			case 'BOOLEAN':
				// special case here: we translate boolean 1 or 0 into PHP
				// constants true or false
				if ($this->_xh['ac']=='1') {
					$this->_xh['ac']='True';
				} else {
					$this->_xh['ac']='false';
				}
				$this->_xh['vt']=strtolower($name);
				// Drop through intentionally.
			case 'I4':
			case 'INT':
			case 'STRING':
			case 'DOUBLE':
			case 'DATETIME.ISO8601':
			case 'BASE64':
				if ($this->_xh['qt']==1)
				{
					// we use double quotes rather than single so backslashification works OK
					$this->_xh['st'].='"'. $this->_xh['ac'] . '"'; 
				}
				elseif ($this->_xh['qt']==2)
				{
					$this->_xh['st'].="base64_decode('". $this->_xh['ac'] . "')"; 
				}
				elseif ($name=='BOOLEAN')
				{
					$this->_xh['st'].=$this->_xh['ac'];
				}
				else
				{
					// we have an I4, INT or a DOUBLE
					// we must check that only 0123456789-.<space> are characters here
					if (!ereg("^\-?[0123456789 \t\.]+$", $this->_xh['ac']))
					{
						// TODO: find a better way of throwing an error
						// than this!
						error_log('XML-RPC: non numeric value received in INT or DOUBLE');
						$this->_xh['st'].='ERROR_NON_NUMERIC_FOUND';
					}
					else
					{
						// it's ok, add it on
						$this->_xh['st'].=$this->_xh['ac'];
					}
				}
				$this->_xh['ac'] = '';
				$this->_xh['qt'] = 0;
				$this->_xh['lv'] = 3; // indicate we've found a value
				break;
			case 'VALUE':
				// deal with a string value
				if (strlen($this->_xh['ac'])>0 &&
					$this->_xh['vt']==xmlrpcString)
				{
					$this->_xh['st'].='"'. $this->_xh['ac'] . '"'; 
				}
				// This if() detects if no scalar was inside <VALUE></VALUE>
				// and pads an empty ''.
				if($this->_xh['st'][strlen($this->_xh['st'])-1] == '(')
				{
					$this->_xh['st'].= '""';
				}
				$this->_xh['st'].=", '" . $this->_xh['vt'] . "')";
				if ($this->_xh['cm'])
				{
					$this->_xh['st'].=',';
				}
				break;
			case 'MEMBER':
				$this->_xh['ac']='';
				$this->_xh['qt']=0;
				break;
			case 'DATA':
				$this->_xh['ac']='';
				$this->_xh['qt']=0;
				break;
			case 'PARAM':
				$this->_xh['params'][]=$this->_xh['st'];
				break;
			case 'METHODNAME':
				$this->_xh['method']=ereg_replace("^[\n\r\t ]+", '', $this->_xh['ac']);
				break;
			case 'BOOLEAN':
				// special case here: we translate boolean 1 or 0 into PHP
				// constants true or false
				if ($this->_xh['ac']=='1') 
				{
					$this->_xh['ac']='True';
				}
				else
				{
					$this->_xh['ac']='false';
				}
				$this->_xh['vt']=strtolower($name);
				break;
			default:
				break;
		}
		// if it's a valid type name, set the type
		if (isset($this->xmlrpcTypes[strtolower($name)]))
		{
			$this->_xh['vt']=strtolower($name);
		}
	}

	function xmlrpc_cd($parser, $data)
	{
		//if (ereg("^[\n\r \t]+$", $data)) return;
		// print "adding [${data}]\n";

		if ($this->_xh['lv']!=3)
		{
			// 'lookforvalue==3' means that we've found an entire value
			// and should discard any further character data
			if ($this->_xh['lv']==1)
			{
				// if we've found text and we're just in a <value> then
				// turn quoting on, as this will be a string
				$this->_xh['qt']=1; 
				// and say we've found a value
				$this->_xh['lv']=2; 
			}
			$this->_xh['ac'].= addslashes(
				str_replace(chr(92),$this->xmlrpc_backslash, $data));
		}
	}

	function xmlrpc_dh($parser, $data)
	{
		if (substr($data, 0, 1) == '&' && substr($data, -1, 1) == ';')
		{
			if ($this->_xh['lv']==1)
			{
				$this->_xh['qt']=1; 
				$this->_xh['lv']=2; 
			}
			$this->_xh['ac'].=str_replace('$', '\$',
				str_replace('"', '\"', 
				str_replace(chr(92),$this->xmlrpc_backslash, $data)));
		}
	}

	// date helpers
	function iso8601_encode($timet, $utc=0)
	{
		// return an ISO8601 encoded string
		// really, timezones ought to be supported
		// but the XML-RPC spec says:
		//
		// "Don't assume a timezone. It should be specified by the server in its
		// documentation what assumptions it makes about timezones."
		// 
		// these routines always assume localtime unless 
		// $utc is set to 1, in which case UTC is assumed
		// and an adjustment for locale is made when encoding
		if (!$utc)
		{
			$t=strftime('%Y%m%dT%H:%M:%S', $timet);
		}
		else
		{
			if (function_exists('gmstrftime')) 
			{
				// gmstrftime doesn't exist in some versions
				// of PHP
				$t=gmstrftime('%Y%m%dT%H:%M:%S', $timet);
			}
			else
			{
				$t=strftime('%Y%m%dT%H:%M:%S', $timet-date('Z'));
			}
		}
		return $t;
	}

	function iso8601_decode($idate, $utc=0)
	{
		// return a timet in the localtime, or UTC
		$t=0;
		if (ereg("([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})",$idate, $regs))
		{
			if ($utc)
			{
				$t=gmmktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
			}
			else
			{
				$t=mktime($regs[4], $regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
			}
		} 
		return $t;
	}

	/****************************************************************
	* xmlrpc_php_decode takes a message in PHP xmlrpc object format *
	* and tranlates it into native PHP types.                       *
	*                                                               *
	* author: Dan Libby (dan@libby.com)                             *
	****************************************************************/
	protected function xmlrpc_php_decode($xmlrpc_val)
	{
		$kind = @$xmlrpc_val->kindOf();

		if($kind == 'scalar')
		{
			return $xmlrpc_val->scalarval();
		}
		elseif($kind == 'array')
		{
			$size = $xmlrpc_val->arraysize();
			$arr = array();

			for($i = 0; $i < $size; $i++)
			{
				$arr[]=$this->xmlrpc_php_decode($xmlrpc_val->arraymem($i));
			}
			return $arr; 
		}
		elseif($kind == 'struct')
		{
			$xmlrpc_val->structreset();
			$arr = array();

			while(list($key,$value)=$xmlrpc_val->structeach())
			{
				$arr[$key] = $this->xmlrpc_php_decode($value);
			}
			return $arr;
		}
	}

	/****************************************************************
	* xmlrpc_php_encode takes native php types and encodes them into*
	* xmlrpc PHP object format.                                     *
	* BUG: All sequential arrays are turned into structs.  I don't  *
	* know of a good way to determine if an array is sequential     *
	* only.                                                         *
	*                                                               *
	* feature creep -- could support more types via optional type   *
	* argument.                                                     *
	*                                                               *
	* author: Dan Libby (dan@libby.com)                             *
	****************************************************************/
	protected function xmlrpc_php_encode($php_val)
	{
		$type = gettype($php_val);
		$xmlrpc_val = CreateObject('org.freemedsoftware.core.xmlrpcval');

		switch($type)
		{
			case 'array':
			case 'object':
				$arr = array();
				while (list($k,$v) = each($php_val))
				{
					$arr[$k] = $this->xmlrpc_php_encode($v);
				}
				$xmlrpc_val->addStruct($arr);
				break;
			case 'integer':
				$xmlrpc_val->addScalar($php_val, xmlrpcInt);
				break;
			case 'double':
				$xmlrpc_val->addScalar($php_val, xmlrpcDouble);
				break;
			case 'string':
				$xmlrpc_val->addScalar($php_val, xmlrpcString);
				break;
			// <G_Giunta_2001-02-29>
			// Add support for encoding/decoding of booleans, since they are supported in PHP
			case 'boolean':
				$xmlrpc_val->addScalar($php_val, xmlrpcBoolean);
				break;
			// </G_Giunta_2001-02-29>
			case 'unknown type':
			default:
				$xmlrpc_val = false;
				break;
		}
		return $xmlrpc_val;
	}

} // end class Relay_Xmlrpc

function xmlrpc_error_handler ( $no, $str, $file, $line, $context ) {
	switch ($no) {
		case E_USER_ERROR:
			// Application error
		case E_ERROR:
		case E_PARSE:
		case E_COMPILE_ERROR:
		case E_CORE_ERROR:
			$fault = CreateObject('org.freemedsoftware.core.xmlrpcresp', '', 'No data received from server.', 6 );
			print $fault->serialize();
			die();
		break;

		default: break;
	}
} // end function xmlrpc_error_handler

?>
