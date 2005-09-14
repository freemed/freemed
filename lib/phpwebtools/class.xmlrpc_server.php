<?php
 // $Id$

// Copyright (c) 1999,2000,2001 Edd Dumbill.
// All rights reserved.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions
// are met:
//
//    * Redistributions of source code must retain the above copyright
//      notice, this list of conditions and the following disclaimer.
//
//    * Redistributions in binary form must reproduce the above
//      copyright notice, this list of conditions and the following
//      disclaimer in the documentation and/or other materials provided
//      with the distribution.
//
//    * Neither the name of the "XML-RPC for PHP" nor the names of its
//      contributors may be used to endorse or promote products derived
//      from this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
// "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
// LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
// FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
// REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
// (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
// HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
// STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
// ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
// OF THE POSSIBILITY OF SUCH DAMAGE.

	/* $Id$ */

	// Class: PHP.xmlrpc_server
	//
	//	XML-RPC server class. Provides XML-RPC methods by
	//	optionally authenticated HTTP or HTTPS.
	//
	class xmlrpc_server
	{
		var $dmap = array();
		var $authed = True;
		var $req_array = array();
		var $resp_struct = array();
		var $auth_function = '';

		// Method: xmlrpc_server constructor
		//
		// Parameters:
		//
		//	$dispMap - (optional) Dispatch map. This is a map
		//	of XML-RPC functions to the appropriate PHP
		//	functions.
		//
		//	$serviceNow - (optional) If true, the XML-RPC
		//	server will run the <service> method immediately.
		//	Default is false.
		//
		//	$auth_function - (optional) Name of the function
		//	to be called to provide basic authentication
		//	verification. If this callback function is not
		//	provided, basic authentication will be disabled.
		//
		function xmlrpc_server($dispMap='', $serviceNow=0, $auth_function='')
		{
			// Map a function to determine authentication
			if ($auth_function != '') {
				$this->auth_function = $auth_function;
			}
		
			// dispMap is a despatch array of methods
			// mapped to function names and signatures
			// if a method
			// doesn't appear in the map then an unknown
			// method error is generated
			if($dispMap) {
				$this->dmap = $dispMap;
			} else {
				// Fake it up if we have no methods
				$this->dmap = array();
			}
			if ($serviceNow) {
				$this->service();
			}
		}

		function serializeDebug()
		{
			if ($GLOBALS['_xmlrpc_debuginfo'] != '')
			{
				return "<!-- DEBUG INFO:\n\n" . $GLOBALS['_xmlrpc_debuginfo'] . "\n-->\n";
			}
			else
			{
				return '';
			}
		}

		// Method: service
		//
		//	Performs the XML-RPC processing and serving functions.
		//
		function service()
		{
			$r = $this->parseRequest();
			$payload = '<?xml version="1.0" encoding="' . $GLOBALS['xmlrpc_defencoding'] . '"?>' . "\n"
				. $this->serializeDebug()
				. $r->serialize();
			Header("Content-type: text/xml\r\nContent-length: " . strlen($payload));
			print $payload;
		}

		/*
		add a method to the dispatch map
		*/
		function add_to_map($methodname,$function,$sig,$doc)
		{
			$this->dmap[$methodname] = array(
				'function'  => $function,
				'signature' => $sig,
				'docstring' => $doc
			);
		}

		function verifySignature($in, $sig)
		{
			for($i=0; $i<sizeof($sig); $i++)
			{
				// check each possible signature in turn
				$cursig = $sig[$i];
				if (sizeof($cursig) == $in->getNumParams()+1)
				{
					$itsOK = 1;
					for($n=0; $n<$in->getNumParams(); $n++)
					{
						$p = $in->getParam($n);
						// print "<!-- $p -->\n";
						if ($p->kindOf() == 'scalar')
						{
							$pt = $p->scalartyp();
						}
						else
						{
							$pt = $p->kindOf();
						}
						// $n+1 as first type of sig is return type
						if ($pt != $cursig[$n+1])
						{
							$itsOK  = 0;
							$pno    = $n+1;
							$wanted = $cursig[$n+1];
							$got    = $pt;
							break;
						}
					}
					if ($itsOK)
					{
						return array(1);
					}
				}
			}
			return array(0, "Wanted $wanted, got $got at param $pno)");
		}

		function reqtoarray($_req,$recursed=False)
		{
			switch(gettype($_req))
			{
				case 'object':
					if($recursed)
					{
						return $_req->getval();
					}
					else
					{
						$this->req_array = $_req->getval();
					}
					break;
				case 'array':
					@reset($_req);
					$ele = array();
					while(list($key,$val) = @each($_req))
					{
						if($recursed)
						{
							$ele[$key] = $this->reqtoarray($val,True);
						}
						else
						{
							$this->req_array[$key] = $this->reqtoarray($val,True);
						}
					}
					if($recursed)
					{
						return $ele;
					}
					break;
				case 'string':
				case 'integer':
					if($recursed)
					{
						return $_req;
					}
					else
					{
						$this->req_array[] = $_req;
					}
					break;
				default:
					break;
			}
		}

		function build_resp($_res,$recursed=False)
		{
			if (is_array($_res)) {
				@reset($_res);
				while (list($key,$val) = @each($_res))
				{
					$ele[$key] = $this->build_resp($val,True);
				}
				$this->resp_struct[] = CreateObject('PHP.xmlrpcval',$ele,'struct');
			} else {
				$_type = (is_integer($_res)?'int':gettype($_res));
				if ($recursed)
				{
					return CreateObject('PHP.xmlrpcval',$_res,$_type);
				}
				else
				{
					$this->resp_struct[] = CreateObject('PHP.xmlrpcval',$_res,$_type);
				}
			}
		}

		function serialize ($_res) {
			// Handle if this is already serialized
			if (is_object($_res) and method_exists($_res, 'serialize')) {
				return $_res;
			}
			if (is_array($_res)) {
				// If we're dealing with an array, recurse
				@reset($_res);
				while(list($key,$val) = @each($_res)) {
					$ele[$key] = $this->serialize($val);
				}
				// Determine if it's a stuct or array
				return CreateObject('PHP.xmlrpcval', $ele,
					$this->__is_struct($ele)?'struct':'array');
			} else {
				$_type = (is_integer($_res)?'int':gettype($_res));
				return CreateObject('PHP.xmlrpcval',$_res,$_type);
			}
		}

		function __is_struct ($obj) {
			if (!is_array($obj)) return false;
			foreach ($obj AS $k => $v) {
				if (!is_integer($k)) return true;
			}
			return false;
		}

		function parseRequest($data='')
		{
			if ($data == '')
			{
				$data = $GLOBALS['HTTP_RAW_POST_DATA'];
			}
			$parser = xml_parser_create($GLOBALS['xmlrpc_defencoding']);
	
			$GLOBALS['_xh'][$parser] = array();
			$GLOBALS['_xh'][$parser]['st']     = '';
			$GLOBALS['_xh'][$parser]['cm']     = 0; 
			$GLOBALS['_xh'][$parser]['isf']    = 0; 
			$GLOBALS['_xh'][$parser]['params'] = array();
			$GLOBALS['_xh'][$parser]['method'] = '';

			// decompose incoming XML into request structure
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, true);
			xml_set_element_handler($parser, 'xmlrpc_se', 'xmlrpc_ee');
			xml_set_character_data_handler($parser, 'xmlrpc_cd');
			xml_set_default_handler($parser, 'xmlrpc_dh');
			if (!xml_parse($parser, $data, 1)) {
				// return XML error as a faultCode
				$r = CreateObject('PHP.xmlrpcresp','',
					$GLOBALS['xmlrpcerrxml'] + xml_get_error_code($parser),
					sprintf('XML error: %s at line %d',
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser))
				);
				xml_parser_free($parser);
			} else {
				xml_parser_free($parser);
				$m = CreateObject('PHP.xmlrpcmsg',$GLOBALS['_xh'][$parser]['method']);
				// now add parameters in
				$plist = '';
				for($i=0; $i<sizeof($GLOBALS['_xh'][$parser]['params']); $i++)
				{
					//print "<!-- " . $GLOBALS['_xh'][$parser]['params'][$i]. "-->\n";
					$plist .= "$i - " . $GLOBALS['_xh'][$parser]['params'][$i]. " \n";
					$code = '$m->addParam(' . $GLOBALS['_xh'][$parser]['params'][$i] . ');';
					$code = ereg_replace(',,',",'',",$code);
					eval($code);
				}
				// uncomment this to really see what the server's getting!
				// TODO: recomment this next line
				//xmlrpc_debugmsg($plist);
				// now to deal with the method
				$methName  = $GLOBALS['_xh'][$parser]['method'];
				$_methName = $GLOBALS['_xh'][$parser]['method'];

				if (ereg("^system\.", $methName)) {
					$dmap = $GLOBALS['_xmlrpcs_dmap'];
					$sysCall=1;
				} else {
					$dmap = $this->dmap;
					$sysCall=0;
				}

				if (!isset($dmap[$methName]['function'])) {
					if($sysCall) {
						$r = CreateObject('PHP.xmlrpcresp',
							'',
							$GLOBALS['xmlrpcerr']['unknown_method'],
							$GLOBALS['xmlrpcstr']['unknown_method'] . ': ' . $methName
						);
						return $r;
					}
					/* phpgw mod - fetch the (bo) class methods to create the dmap */
					$method = $methName;
					list($class, $service, $methName) = explode('.',$method);

					// Allow for hidden functions and application pieces (security reasons
					if ((substr($class, 0, 1) == '_') or (substr($methName, 0, 1) == '_')) {
						$r = CreateObject('PHP.xmlrpcresp',
							'',
							$GLOBALS['xmlrpcerr']['unknown_method'],
							$GLOBALS['xmlrpcstr']['unknown_method'] . ': ' . $methName
						);
					}

					if (ereg('^service',$method)) {
						$t = 'PHP.' . $class . '.exec';
						$dmap = ExecMethod($t,array($service,'list_methods','xmlrpc'));
					} else {
						if ($this->auth_function != '') {
							eval('$this->authed = '.$this->auth_function.'();');
						}
						if (!$this->authed) {
							$r = CreateObject('PHP.xmlrpcresp',
								'',
								$GLOBALS['xmlrpcerr']['no_access'],
								$GLOBALS['xmlrpcstr']['no_access']
							);
							return $r;
						}

						$my_params = $GLOBALS['_xh'][$parser]['params'];
						if (count($my_params) > 1) {
							foreach ($my_params AS $k => $v) {
								$code = '$p = '  . $v . ';';
								$code = ereg_replace(',,',",'',",$code);
								//syslog(LOG_INFO, "code = \"$code\"");
								eval($code);
								if (is_object($p)) {
									$params[] = $p->getval();
								} else {
									$params[] = $p;
								}
							} // foreach
						} else {
							if (count($my_params) != 0)
							{
								$code = '$p = '  . $my_params[0] . ';';
								@eval($code);
								//print "params != 0, code = $code\n";
								if (is_object($p)) {
									$params = $p->getval();
								} else {
									$params = $p[0];
								}
								//print_r($params);
							} else {
								//print "params = "; print_r($params); print " (count=0)\n";
							}
						} // is_array

						/*
						$params = $GLOBALS['_xh'][$parser]['params'];
						if (count($params) != 0) {
							$code = '$p = CreateObject (\'PHP.xmlrpcval\', '.join(', ', $params).', \'array\');';
						} else {
							$code = '$p = '  . $params . ';';
						}
						//if (count($params) != 0)
						//{
							print "code : "; print_r($code); print "\n\n";
							eval($code);
							if (is_object($p)) {
								print "before getval : "; print_r($p); print "\n\n";
								$params = $p->getval();
								//$params = $p->deserialize();
								print "after getval : "; print_r($params); print "\n\n";
							}
						//}
						*/

						// _debug_array($params);
						$this->reqtoarray($params);
						//_debug_array($this->req_array);
						if (ereg('^service',$method)) {
							$res = ExecMethod('PHP.service.exec',array($service,$methName,$this->req_array));
						} else {
							// Check for unavailable function
							if (!MethodAvailable($_methName)) {
								// If not there, unknown method
								$r = CreateObject('PHP.xmlrpcresp',
									'',
									$GLOBALS['xmlrpcerr']['unknown_method'],
									$GLOBALS['xmlrpcstr']['unknown_method'] . ': ' . $methName
								);
								return $r;
							}
							if ($s != 'PHP') {
								// Handle if structure
								if ($this->__is_struct($this->req_array)) {
									// Wrap it in array, so that it's a single parameter
									$res = ExecuteMethodArray(
										$_methName,
										array ($this->req_array)
									);
								} else {	
									$res = ExecuteMethodArray($_methName, $this->req_array);
								}
								//$res = ExecMethod($s . '.' . $c . '.' . $dmap[$methName]['function'],$this->req_array);
							}
						}
						// $res = ExecMethod($method,$params); 
						// _debug_array($res);exit; 
						//$this->resp_struct = array();
						//$this->build_resp($res);
						$this->resp_struct = $this->serialize($res);
						// _debug_array($this->resp_struct);
						//@reset($this->resp_struct);

						// Check for xmlrpcresp or not
						if (!method_exists($this->resp_struct, 'faultString')) {
							$r = CreateObject('PHP.xmlrpcresp', $this->resp_struct);
						} else {
							$r = $this->resp_struct;
						}

						//CreateObject('PHP.xmlrpcval',$this->resp_struct,'struct'));
						// _debug_array($r);

						return $r;
					}

					$this->dmap = $dmap;
					/* _debug_array($this->dmap);exit; */
				}

				if (isset($dmap[$methName]['function']))
				{
					// dispatch if exists
					if (isset($dmap[$methName]['signature'])) {
						$sr = $this->verifySignature($m, $dmap[$methName]['signature'] );
					}
					if ( (!isset($dmap[$methName]['signature'])) || $sr[0]) {
						// if no signature or correct signature
						if ($sysCall)
						{
							$code = '$r=' . $dmap[$methName]['function'] . '($this, $m);';
							$code = ereg_replace(',,',",'',",$code);
							eval($code);
						} else {
							if (function_exists($dmap[$methName]['function'])) {
								$code = '$r =' . $dmap[$methName]['function'] . '($m);';
								$code = ereg_replace(',,',",'',",$code);
								eval($code);
							} else {
								/* phpgw mod - finally, execute the function call and return the values */
								$params = $GLOBALS['_xh'][$parser]['params'][0];
								$code = '$p = '  . $params . ';';
								if (count($params) != 0)
								{
									eval($code);
									$params = $p->getval();
								}

								// _debug_array($params);
								$this->reqtoarray($params);
								//_debug_array($this->req_array);
								if (ereg('^service',$method)) {
									$res = ExecMethod('PHP.service.exec',array($service,$methName,$this->req_array));
								} else {
									list($s,$c,$m) = explode('.',$_methName);
									if ($s != 'PHP') {
										// Check for structure ...
										if ($this->__is_struct($this->req_array)) {
											// Wrap in array
											$res = ExecuteMethodArray(
												$_methName,
												array($this->req_array)
											);
										} else {
											$res = ExecuteMethodArray($_methName, $this->req_array);
										}
										//$res = ExecMethod($s . '.' . $c . '.' . $dmap[$methName]['function'],$this->req_array);
									}
								}
								/* $res = ExecMethod($method,$params); */
								/* _debug_array($res);exit; */
								$this->resp_struct = array();
								$this->build_resp($res);
								/* _debug_array($this->resp_struct); */
								@reset($this->resp_struct);
								$r = CreateObject('PHP.xmlrpcresp',CreateObject('PHP.xmlrpcval',$this->resp_struct,'struct'));
								/* _debug_array($r); */
							}
						}
					} else {
						$r = CreateObject('PHP.xmlrpcresp',
							'',
							$GLOBALS['xmlrpcerr']['incorrect_params'],
							$GLOBALS['xmlrpcstr']['incorrect_params'] . ': ' . $sr[1]
						);
					}
				} else {
					// check for authenticated
					if ($this->auth_function != '') {
						eval ('$this->authed = '.$this->auth_function.';');
					}
					//if ($this->authed) print "authed"; else print "not authed";
					// else prepare error response
					if(!$this->authed) {
						$r = CreateObject('PHP.xmlrpcresp',
							CreateObject('PHP.xmlrpcval',
								'UNAUTHORIZED',
								'string'
							)
						);
					} else {

						/* phpgw mod - finally, execute the function call and return the values */
						$params = $GLOBALS['_xh'][$parser]['params'][0];
						$code = '$p = '  . $params . ';';
						if (count($params) != 0)
						{
							eval($code);
							$params = $p->getval();
						}

						// _debug_array($params);
						$this->reqtoarray($params);
						//_debug_array($this->req_array);

						list($s,$c,$m) = explode('.',$_methName);
						if ($s != 'PHP') {
							// Check for structure
							if ($this->__is_struct($this->req_array)) {
								// Wrap in array
								$res = ExecuteMethodArray(
									$s.'.'.$c.'.'.$m,
									array($this->req_array)
								);
							} else {
								$res = ExecuteMethodArray(
									$s.'.'.$c.'.'.$m,
									$this->req_array
								);
							}
							//$res = ExecMethod($s . '.' . $c . '.' . $dmap[$methName]['function'],$this->req_array);
						}
						/* $res = ExecMethod($method,$params); */
						/* _debug_array($res);exit; */
						$this->resp_struct = array();
						$this->build_resp($res);
						/* _debug_array($this->resp_struct); */
						@reset($this->resp_struct);
						$r = CreateObject('PHP.xmlrpcresp',CreateObject('PHP.xmlrpcval',$this->resp_struct,'struct'));
						/* _debug_array($r); */

						$r = CreateObject('PHP.xmlrpcresp',
							'',
							$GLOBALS['xmlrpcerr']['unknown_method'],
							$GLOBALS['xmlrpcstr']['unknown_method'] . ': ' . $methName
						);
					}
				}
			}
			return $r;
		}

		function echoInput()
		{
			// a debugging routine: just echos back the input
			// packet as a string value

			$r = CreateObject('PHP.xmlrpcresp',CreateObject('PHP.xmlrpcval',"'Aha said I: '" . $GLOBALS['HTTP_RAW_POST_DATA'],'string'));
			echo $r->serialize();
		}
	}
?>
