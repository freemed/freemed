<?php

/*

PRAX - PHP Record-oriented API for XML

Affords a database recordset-like view of an XML document
in documents which lend themselves to such interpretation.

A port of the Perl XML::RAX module by Robert Hanson 
(http://search.cpan.org/search?mode=module&query=rax)
based on the RAX API created by Sean McGrath 
(http://www.xml.com/pub/2000/04/26/rax)

Copyright (c) 2000 Rael Dornfest <rael@oreilly.com>,
All Rights Reserved.

License is granted to use or modify this software ("PRAX")
for commercial or non-commercial use provided the copyright 
of the author is preserved in any distributed or derivative 
work.

XML::RAX Copyright (c) 2000 Robert Hanson.  All rights
reserved.  This program ("XML::RAX") is free software; you 
can redistribute and/or modify it under the terms of the 
Perl "Artistic License."
(http://www.perl.com/language/misc/Artistic.html)

For a usage synopsis, see this distribution's README.txt.
Take a gander at sample.php (using sample.xml) for a 
live example.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESSED
OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.

*/


class RAX {

	function RAX () {

		$this->record_delim = '';
		$this->fields = array();
		$this->records = array();
		$this->parser;
		$this->in_rec = 0;
		$this->in_field = 0;
		$this->field_data = '';
		$this->tag_stack = array();
		$this->xml = '';
		$this->xml_file;
		$this->rax_opened = 0;
		$this->debug = 0;
		$this->version = '0.1';

	}


	function open ($xml) {

		$this->debug("open(\"$xml\")");

		if ($this->rax_opened) return 0;

		$this->xml = $xml;
		$this->rax_opened = 1;
	}


	function openfile ($filename) {

		$this->debug("openfile(\"$filename\")");

		if ($this->rax_opened) return 0;

		$fp = @fopen($filename, "r");

		if ($fp) {
			$this->xml_file = $fp;
			$this->rax_opened = 1;
			return 1;
		}

		return 0;
	}


	function startparse () {

		$this->debug("startparse()");

		$this->parser = xml_parser_create();

		xml_set_object($this->parser,&$this);
		xml_set_element_handler($this->parser,  "startElement",  "endElement");
		xml_set_character_data_handler($this->parser,  "characterData");
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);

		if (xml_parse($this->parser, '')) {
			$this->parse_started = 1;
			return 1;
		}

		return 0;
	}


	function parse () {
		
		$this->debug("parse()");

		if (!$this->rax_opened) return 0;
		if ($this->parse_done) return 0;

		if (!$this->parse_started) 
			if (!$this->startparse()) return 0;

		if ($this->xml_file) {

			$buffer = fread($this->xml_file, 4096);

			if ( $buffer )
				xml_parse( $this->parser, $buffer, feof($this->xml_file) );
			else {
				$this->parse_done = 1;
			}

		}
		else {
			xml_parse($this->parser, $this->xml, 1);
			$this->parse_done = 1;
		}

		return 1;
	}


	function startElement($parser, $name, $attrs) {
		
		$this->debug("startElement($name)");

		array_push($this->tag_stack, $name);

		if ( !$this->in_rec and !strcmp($name, $this->record_delim) ) {
			$this->in_rec = 1;
			$this->rec_lvl = sizeof($this->tag_stack);
			$this->field_lvl = $this->rec_lvl + 1;
		}
		else if ( $this->in_rec and sizeof($this->tag_stack) == $this->field_lvl ) {
			$this->in_field = 1;
		}

	}


	function endElement($parser, $name) {

		$this->debug("endElement($name)");

		array_pop($this->tag_stack);

		if ( $this->in_rec ) {

			if ( sizeof($this->tag_stack) < $this->rec_lvl ) {
				$this->in_rec = 0;
				array_push( $this->records, new RAX_Record( $this->fields ) );
				$this->fields = array();
			}
			else if ( sizeof($this->tag_stack) < $this->field_lvl ) {
				$this->in_field = 0;
				$this->fields[$name] = $this->field_data;
				$this->field_data = '';
			}

		}

	}


	function characterData ($parser, $data) {

		$this->debug("characterData($data)");

		if ( $this->in_field ) 
			$this->field_data .= $data;

	}


	function setRecord ($delim) {

		$this->debug("setRecord($delim)");

		if ($this->parse_started) return 0;

		$this->record_delim = $delim;

		return 1;
	}


	function readRecord () {

		$this->debug("readRecord()");

		while ( !sizeof($this->records) and !$this->parse_done ) $this->parse();

		return array_shift($this->records);
	}


	function debug ($msg) {
		if ($this->debug) print "$msg<br />\n";
	}

}


class RAX_Record {

	function RAX_Record ( $fields ) {

		$this->fields = $fields;

		$this->debug = 0;
	}


	function getFieldnames () {
		
		$this->debug("getFieldnames()");

		return array_keys( $this->fields );
	}


	function getField ( $field ) {
		
		$this->debug("getField($field)");

		return trim( $this->fields[$field] );
	}


	function getFields () {
		
		$this->debug("getFields()");

		return array_values( $this->fields );
	}


	function getRow () {
		
		$this->debug("getFields()");

		return $this->fields;
	}


	function debug ($msg) {
		if ($this->debug) print "$msg<br />\n";
	}

}
