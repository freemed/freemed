<?php

/*
  This file is part of, or distributed with, libXMLRPC - a C library for 
  xml-encoded function calls.

  Author: Dan Libby (dan@libby.com)
  Epinions.com may be contacted at feedback@epinions-inc.com
*/

/*  
  Copyright 2001 Epinions, Inc. 

  Subject to the following 3 conditions, Epinions, Inc.  permits you, free 
  of charge, to (a) use, copy, distribute, modify, perform and display this 
  software and associated documentation files (the "Software"), and (b) 
  permit others to whom the Software is furnished to do so as well.  

  1) The above copyright notice and this permission notice shall be included 
  without modification in all copies or substantial portions of the 
  Software.  

  2) THE SOFTWARE IS PROVIDED "AS IS", WITHOUT ANY WARRANTY OR CONDITION OF 
  ANY KIND, EXPRESS, IMPLIED OR STATUTORY, INCLUDING WITHOUT LIMITATION ANY 
  IMPLIED WARRANTIES OF ACCURACY, MERCHANTABILITY, FITNESS FOR A PARTICULAR 
  PURPOSE OR NONINFRINGEMENT.  

  3) IN NO EVENT SHALL EPINIONS, INC. BE LIABLE FOR ANY DIRECT, INDIRECT, 
  SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES OR LOST PROFITS ARISING OUT 
  OF OR IN CONNECTION WITH THE SOFTWARE (HOWEVER ARISING, INCLUDING 
  NEGLIGENCE), EVEN IF EPINIONS, INC.  IS AWARE OF THE POSSIBILITY OF SUCH 
  DAMAGES.    

*/

if (!defined('__XMLRPC_EPI_UTILS_PHP__')) {

define ('__XMLRPC_EPI_UTILS_PHP__', true);

/* xmlrpc utilities (xu) 
 * author: Dan Libby (dan@libby.com)
 */

// ensure extension is loaded.
xu_load_extension();

// a function to ensure the xmlrpc extension is loaded.
// xmlrpc_epi_dir = directory where libxmlrpc.so.0 is located
// xmlrpc_php_dir = directory where xmlrpc-epi-php.so is located
function xu_load_extension($xmlrpc_php_dir="") {
   $bSuccess = true;
   putenv("LD_LIBRARY_PATH=/usr/lib/php4/apache/xmlrpc/");
   if ($xmlrpc_php_dir) {
      $xmlrpc_php_dir .= '/';
   }
   if (!extension_loaded("xmlrpc")) {
      $bSuccess = dl($xmlrpc_php_dir . "xmlrpc-epi-php.so");
   }
   return $bSuccess;
}

/* generic function to call an http server with post method */
function xu_query_http_post($request, $host, $uri, $port, $debug, 
                            $timeout, $user, $pass, $secure=false) {
   $response_buf = "";
   if ($host && $uri && $port) {
      $content_len = strlen($request);

      $fsockopen = $secure ? "fsockopen_ssl" : "fsockopen";

      dbg1("opening socket to host: $host, port: $port, uri: $uri", $debug);
      $query_fd = $fsockopen($host, $port, $errno, $errstr, 10);
      if ($query_fd) {

         $auth = "";
         if ($user) {
            $auth = "Authorization: Basic " .
                    base64_encode($user . ":" . $pass) . "\r\n";
         }

         $http_request = 
         "POST $uri HTTP/1.0\r\n" .
         "User-Agent: xmlrpc-epi-php/0.2 (PHP)\r\n" .
         "Host: $host:$port\r\n" .
         $auth .
         "Content-Type: text/xml\r\n" .
         "Content-Length: $content_len\r\n" . 
         "\r\n" .
         $request;

         dbg1("sending http request:</h3> <xmp>\n$http_request\n</xmp>", $debug);

         fputs($query_fd, $http_request, strlen($http_request));

         dbg1("receiving response...", $debug);

         while (!feof($query_fd)) {
            $line = fgets($query_fd, 4096);
            if (!$header_parsed) {
               if ($line === "\r\n" || $line === "\n") {
                  $header_parsed = 1;
               }
               dbg2("got header - $line", $debug);
            }
            else {
               $response_buf .= $line;
            }
         }

         fclose($query_fd);
      }
      else {
         dbg1("socket open failed", $debug);
      }
   }
   else {
      dbg1("missing param(s)", $debug);
   }

   dbg1("got response:</h3>. <xmp>\n$response_buf\n</xmp>\n", $debug);

   return $response_buf;
}

function xu_fault_code($code, $string) {
   return array(faultCode => $code,
                faultString => $string);
}


function find_and_decode_xml($buf, $debug) {
   if (strlen($buf)) {
      $xml_begin = substr($buf, strpos($response_buf, "<?xml"));
      if (strlen($xml_begin)) {
         $retval = xmlrpc_decode($xml_begin);
      }
      else {
         dbg1("xml start token not found", $debug);
      }
   }
   else {
      dbg1("no data", $debug);
   }
   return $retval;
}

 
/**
 * @param params   a struct containing 3 or more of these key/val pairs:
 * @param host		 remote host (required)
 * @param uri		 remote uri	 (required)
 * @param port		 remote port (required)
 * @param method   name of method to call
 * @param args	    arguments to send (parameters to remote xmlrpc server)
 * @param debug	 debug level (0 none, 1, some, 2 more)
 * @param timeout	 timeout in secs.  (0 = never)
 * @param user		 user name for authentication.  
 * @param pass		 password for authentication
 * @param secure	 secure. wether to use fsockopen_ssl. (requires special php build).
 */
function xu_rpc_http_concise($params) {
	extract($params);

	// default values
	if(!$port) {
		$port = 80;
	}
	if(!$uri) {
		$uri = '/';
	}
	if(!$output) {
		$output = array(version => "xmlrpc");
	}

   $response_buf = "";
   if ($host && $uri && $port) {
      $request_xml = xmlrpc_encode_request($method, $args, $output);
      $response_buf = xu_query_http_post($request_xml, $host, $uri, $port, $debug,
                                         $timeout, $user, $pass, $secure);

      $retval = find_and_decode_xml($response_buf, $debug);
   }
   return $retval;
}

/* call an xmlrpc method on a remote http server. legacy support. */
function xu_rpc_http($method, $args, $host, $uri="/", $port=80, $debug=false, 
                     $timeout=0, $user=false, $pass=false, $secure=false) {
	return xu_rpc_http_concise(
		array(
			method  => $method,
			args    => $args,
			host    => $host,
			uri     => $uri,
			port    => $port,
			debug   => $debug,
			timeout => $timeout,
			user    => $user,
			pass    => $pass,
			secure  => $secure
		));
}

/* call an xmlrpc method on a remote http server with curl library,
 * which supports ssl, etc.
 *
 * note that the curl php extension must be loaded/present in your
 * php build.
 */ 
/* note: this is not yet known to be working */
function xu_rpc_http_curl($method_name, $args, $url, $debug=false) { 
   if ($url) {
      $request = xmlrpc_encode_request($method_name, $args); 

      $content_len = strlen($request); 

      preg_match('/^(.*?\/\/.*?) (\/.*)/',$url,$matches); 
      $hostport = $matches[1]; 
      $uri = $matches[2]; 

      dbg1("opening curl to $url", $debug); 

      $http_request = 
      "POST ".$uri." HTTP/1.0\r\n" . 
      "User-Agent: xmlrpc-epi-php/0.2 (PHP) \r\n" . 
      "Content-Type: text/xml\r\n" . 
      "Content-Length: $content_len\r\n" . 
      "\r\n" . 
      $request; 

      dbg1("sending http request:</h3> <xmp>\n$http_request\n</xmp>", $debug); 

      $ch = curl_init(); 
      curl_setopt($ch, CURLOPT_URL, $hostport); 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_request); 
      curl_setopt($ch, CURLOPT_HEADER, 0); 
      $response_buf = curl_exec($ch); 
      curl_close($ch); 

      dbg1("got response:</h3>. <xmp>\n$response_buf\n</xmp>\n", $debug); 

      $retval = find_and_decode_xml($response_buf, $debug);
   }
   return $retval;
}

/* call curl function with same convention as xu_rpc_http.
 * for easy backwards compatibility with existing scripts.
 */
/* note: this is not yet known to be working */
function xu_rpc_http_curl_compat($method_name, $args, $host, $uri="/", $port=80, $debug=false, $start="http://") {
   $url = "$start$host:$port$uri";
   return xu_rpc_http_curl($method_name, $args, $url, $debug);
}

/* note: untested. should work. */
function xu_is_fault($arg) {
  return (is_array($arg) && isset($arg[0][faultCode]));
}

/* sets some http headers and prints xml */
function xu_server_send_http_response($xml) {
    header("Content-type: text/xml");
    header("Content-length: " . strlen($xml) );
    echo $xml;
}


function dbg($msg) {
   echo "<h3>$msg</h3>"; flush();
}
function dbg1($msg, $debug_level) {
   if ($debug_level >= 1) {
      dbg($msg);
   }
}
function dbg2($msg, $debug_level) {
   if ($debug_level >= 2) {
      dbg($msg);
   }
}

} // end if not defined __XMLRPC_EPI_UTILS_PHP__

?>
