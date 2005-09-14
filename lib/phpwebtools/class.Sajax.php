<?php	
	// $Id$
	// $Author$
	//
	// Originally from http://www.modernmethod.com/sajax/
	// Modified for phpwebtools by Jeff from release 0.10
	// Also relicensed under LGPL for distribution

// Class: Sajax
//
//	Provides both server and client-side methods for dealing with
//	"Ajax"-type XMLHttp asynchronous queries.
//
class Sajax {

	/*  
	 * GLOBALS AND DEFAULTS
	 *
	 */ 
	var $debug_mode = 0;
	var $export_list = array();
	var $request_type = "GET";
	var $remote_uri = "";
	var $js_has_been_shown = 0;

	// Constructor: Sajax
	//
	// Parameters:
	//
	//	$remote_uri - (optional) Specify different URI for the
	//	method to call. Defaults to the current URI.
	//	
	function Sajax( $remote_uri = NULL ) {
		$this->remote_uri = $remote_uri ? $remote_uri : $this->get_my_uri();
	}

	// Method: get_my_uri
	//
	// Returns:
	//
	//	REQUEST_URI
	//	
	function get_my_uri() {
		return $_SERVER['REQUEST_URI'];
	}

	// Method: handle_client_request
	//
	//	Handle a client request for a function.
	//
	function handle_client_request() {
		$mode = "";
		
		if (! empty($_GET["rs"])) 
			$mode = "get";
		
		if (!empty($_POST["rs"]))
			$mode = "post";
			
		if (empty($mode)) 
			return;

		if ($mode == "get") {
			// Bust cache in the head
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			// always modified
			header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
			header ("Pragma: no-cache");                          // HTTP/1.0
			$func_name = $_GET["rs"];
			if (! empty($_GET["rsargs"])) 
				$args = $_GET["rsargs"];
			else
				$args = array();
		}
		else {
			$func_name = $_POST["rs"];
			if (! empty($_POST["rsargs"])) 
				$args = $_POST["rsargs"];
			else
				$args = array();
		}
		
		if (! in_array($func_name, $this->export_list))
			echo "-:$func_name not callable";
		else {
			echo "+:";
			$result = call_user_func_array($func_name, $args);
			echo $result;
		}
		exit
;
	}

	// Method: get_common_js
	//
	// Returns:
	//
	//	A string containing the appropriate Javascript needed to
	//	run the client.
	//	
	function get_common_js() {
		$t = strtoupper($this->request_type);
		if ($t != "GET" && $t != "POST") 
			return "// Invalid type: $t.. \n\n";
		
		ob_start();
		?>
		
		// remote scripting library
		// (c) copyright 2005 modernmethod, inc
		var sajax_debug_mode = <?php echo $this->debug_mode ? "true" : "false"; ?>;
		var sajax_request_type = "<?php echo $t; ?>";
		
		function sajax_debug(text) {
			if (sajax_debug_mode)
				alert("RSD: " + text)
		}
 		function sajax_init_object() {
 			sajax_debug("sajax_init_object() called..")
 			
 			var A;
			try {
				A=new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					A=new ActiveXObject("Microsoft.XMLHTTP");
				} catch (oc) {
					A=null;
				}
			}
			if(!A && typeof XMLHttpRequest != "undefined")
				A = new XMLHttpRequest();
			if (!A)
				sajax_debug("Could not create connection object.");
			return A;
		}
		function sajax_do_call(func_name, args) {
			var i, x, n;
			var uri;
			var post_data;
			
			uri = "<?php echo $this->remote_uri; ?>";
			if (sajax_request_type == "GET") {
				if (uri.indexOf("?") == -1) 
					uri = uri + "?rs=" + encodeURI(func_name);
				else
					uri = uri + "&rs=" + encodeURI(func_name);
				for (i = 0; i < args.length-1; i++) 
					uri = uri + "&rsargs[]=" + encodeURI(args[i]);
				uri = uri + "&rsrnd=" + new Date().getTime();
				post_data = null;
			} else {
				post_data = "rs=" + encodeURI(func_name);
				for (i = 0; i < args.length-1; i++) 
					post_data = post_data + "&rsargs[]=" + encodeURI(args[i]);
			}
			
			x = sajax_init_object();
			x.open(sajax_request_type, uri, true);
			if (sajax_request_type == "POST") {
				x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
				x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			}
			x.onreadystatechange = function() {
				if (x.readyState != 4) 
					return;
				sajax_debug("received " + x.responseText);
				
				var status;
				var data;
				status = x.responseText.charAt(0);
				data = x.responseText.substring(2);
				if (status == "-") 
					alert("Error: " + data);
				else  
					args[args.length-1](data);
			}
			x.send(post_data);
			sajax_debug(func_name + " uri = " + uri + "/post = " + post_data);
			sajax_debug(func_name + " waiting..");
			delete x;
		}
		
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	// Method: show_common_js
	//
	//	Prints the common Javascript required to run the client.
	//
	// See Also:
	//	<get_common_js>	
	//
	function show_common_js() {
		print $this->get_common_js();
	}

	// Method: esc
	//
	//	Javascript-escape a value.
	//
	// Parameters:
	//
	//	$val - Original value
	//
	// Returns:
	//
	//	Value escaped for Javascript string syntax.
	//	
	function esc($val)
	{
		return str_replace('"', '\\\\"', $val);
	}

	function get_one_stub($func_name) {
		ob_start();	
		?>
		
		// wrapper for <?php echo $func_name; ?>
		
		function x_<?php echo $func_name; ?>() {
			sajax_do_call("<?php echo $func_name; ?>",
				x_<?php echo $func_name; ?>.arguments);
		}
		
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	function show_one_stub($func_name) {
		echo $this->get_one_stub($func_name);
	}

	// Method: export
	//
	//	Export a list of functions for Sajax to serve or use as
	//	a client.
	//
	// Parameters:
	//
	//	(variable) - Variable number of strings containing the
	//	PHP function names of the functions to export.
	//	
	function export() {
		$n = func_num_args();
		for ($i = 0; $i < $n; $i++) {
			$this->export_list[] = func_get_arg($i);
		}
	}

	// Method: get_javascript
	//
	//	Get the Javascript
	//
	// Returns:
	//
	//	Entire javascript for client.
	//
	function get_javascript()
	{
		$html = "";
		if (! $this->js_has_been_shown) {
			$html .= $this->get_common_js();
			$this->js_has_been_shown = 1;
		}
		foreach ($this->export_list as $func) {
			$html .= $this->get_one_stub($func);
		}
		return $html;
	}
	
	function show_javascript()
	{
		echo $this->get_javascript();
	}
}

?>
