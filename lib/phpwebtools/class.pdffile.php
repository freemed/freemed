<?php
	// $Id$
	// This is sync'd to phppdflib v2.4

/*
   php pdf generation library
   Copyright (C) Potential Technologies 2002 - 2003
   http://www.potentialtech.com

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

   Id: phppdflib.class.php,v 2.5 2003/07/05 21:33:07 wmoran Exp
*/

class pdffile
{
    /* $objects is an array that stores the objects
     * that will become the pdf when ->generate()
     * is called.
     * The layout of ->objects does not directly
     * mimic the pdf format, although it is similar.
     * nextoid always holds the next available oid ( oid is short for object id )
     */
    var $objects, $nextoid;

    /* xreftable is an array containing data to
     * create the xref section (PDF calls it a referance table)
     */
    var $xreftable, $nextobj;

    /* These arrays allow quick translation between
     * pdflib OIDs and the final PDF OIDs (OID stands for object ids)
     */
    var $libtopdf, $pdftolib;

    // Errors
    var $ermsg = array(), $erno = array();

    var $builddata;         // Various data required during the pdf build
    var $nextpage;          // Tracks the next page number
    var $widths, $needsset; // Store the font width arrays here
    var $default;           // Default values for objects
    var $x, $chart, $template;	// extension class is instantiated here if requested
    /* Constructor function: is automatically called when the
     * object is created.  Used to set up the environment
     */
    function pdffile()
    {
        /* Per spec, obj 0 should always have a generation
         * number of 65535 and is always free
         */
        $this->xreftable[0]["gennum"] = 65535;
        $this->xreftable[0]["offset"] = 0;
        $this->xreftable[0]["free"] = "f";

        // Object #1 will always be the Document Catalog
        $this->xreftable[1]["gennum"] = 0;
        $this->xreftable[1]["free"] = "n";

        // Object #2 will always be the root pagenode
        $this->xreftable[2]["gennum"] = 0;
        $this->xreftable[2]["free"] = "n";
        $this->pdftolib[2] = 1;
        $this->libtopdf[1] = 2;

        // Object #3 is always the resource library
        $this->xreftable[3]["gennum"] = 0;
        $this->xreftable[3]["free"] = "n";

        /* nextoid starts at 2 because all
         * drawing functions return either the
         * object ID or FALSE on error, so we can't
         * return an OID of 0, because it equates
         * to false and error checking would think
         * the procedure failed
         */
        $this->nextoid = 2;
        $this->nextobj = 3;

        // Pages start at 0
        $this->nextpage = 0;

        // Font width tables are not set unless they are needed
        $this->needsset = true;

        // Set all the default values
        $t['pagesize'] = 'letter';
        $t['font'] = 'Helvetica';
        $t['height'] = 12;
        $t['align'] = 'left';
        $t['width'] = 1;
        $t['rotation'] = 0;
        $t['scale'] = 1;
        $t['strokecolor'] = $this->get_color('black');
        $t['fillcolor'] = $this->get_color('black');
        $t['margin-left'] = $t['margin-right'] = $t['margin-top'] = $t['margin-bottom'] =72;
        $t['tmode'] = 0; // Text: fill
        $t['smode'] = 1; // Shapes: stroke
        $this->default = $t;
    }

/******************************************************
 * These functions are the public ones, they are the  *
 * way that the user will actually enter the data     *
 * that will become the pdf                           *
 ******************************************************/

    function set_default($setting, $value)
    {
        switch ($setting) {
        case 'margin' :
            $this->default['margin-left'] = $value;
            $this->default['margin-right'] = $value;
            $this->default['margin-top'] = $value;
            $this->default['margin-bottom'] = $value;
            break;

        case 'mode' :
            $this->default['tmode'] = $this->default['smode'] = $value;
            break;

        default :
            $this->default[$setting] = $value;
        }
        return true;
    }
    
    function draw_rectangle($top, $left, $bottom, $right, $parent, $attrib = array())
    {
        if ($this->objects[$parent]["type"] != "page") {
            $this->_push_std_error(6001);
            return false;
        }
        $o = $this->_addnewoid();
        $attrib = $this->_resolve_param($attrib, false);
        $this->_resolve_colors($n, $attrib);
        $this->objects[$o] = $n;
        $this->objects[$o]["width"] = $attrib["width"];
        $this->objects[$o]["type"] = "rectangle";
        $this->_adjust_margin($left, $top, $parent);
        $this->_adjust_margin($right, $bottom, $parent);
        $this->objects[$o]["top"] = $top;
        $this->objects[$o]["left"] = $left;
        $this->objects[$o]["bottom"] = $bottom;
        $this->objects[$o]["right"] = $right;
        $this->objects[$o]["parent"] = $parent;
        $this->objects[$o]["mode"] = $this->_resolve_mode($attrib, 'smode');
        return $o;
    }

    function draw_circle($x, $y, $r, $parent, $attrib = array())
    {
        if ($this->objects[$parent]["type"] != "page") {
            $this->_push_std_error(6001);
            return false;
        }
        $o = $this->_addnewoid();
        $attrib = $this->_resolve_param($attrib, false);
        $this->_resolve_colors($n, $attrib);
        $n['width'] = $attrib['width'];
        $this->_adjust_margin($x, $y, $parent);
        $n['x'] = $x;
        $n['y'] = $y;
        $n['radius'] = $r;
        $n['type'] = 'circle';
        $n['parent'] = $parent;
        $n['mode'] = $this->_resolve_mode($attrib, 'smode');
        $this->objects[$o] = $n;
        return $o;
    }

    function draw_line($x, $y, $parent, $attrib = array())
    {
        if ($this->objects[$parent]["type"] != "page") {
            $this->_push_std_error(6001);
            return false;
        }
        if (count($x) != count($y)) {
            $this->_push_error(6002, "X & Y variables must have equal number of elements");
            return false;
        }
        $o = $this->_addnewoid();
        $attrib = $this->_resolve_param($attrib, false);
        $this->_resolve_colors($n, $attrib);
        $this->objects[$o] = $n;
        @$this->objects[$o]["width"] = $attrib["width"];
        $this->objects[$o]['mode'] = $this->_resolve_mode($attrib, 'smode');
        $this->objects[$o]["type"] = "line";
        foreach ($x as $key => $value) {
            if (isset($x[$key]) && isset($y[$key])) {
                $this->_adjust_margin($x[$key], $y[$key], $parent);
            }
        }
        $this->objects[$o]["x"] = $x;
        $this->objects[$o]["y"] = $y;
        $this->objects[$o]["parent"] = $parent;
        return $o;
    }

    // draw text
    function draw_text($left, $bottom, $text, $parent, $attrib = array())
    {
        if ($this->objects[$parent]["type"] != "page") {
            $this->_push_std_error(6001);
            return false;
        }
        $attrib = $this->_resolve_param($attrib);
        // Validate the font
        if (!($n["font"] = $this->_use_font($attrib))) {
            // Couldn't find/add the font
            $this->_push_error(6003, "Font was not found");
            return false;
        }
        if (isset($attrib["rotation"])) {
            $n["rotation"] = $attrib["rotation"];
        }
        $n['mode'] = $this->_resolve_mode($attrib, 'tmode');
        if (isset($attrib["height"]) && $attrib["height"] > 0) {
           $n["height"] = $attrib["height"];
        }
        $this->_resolve_colors($n, $attrib);
        $n["type"] = "texts";
        $this->_adjust_margin($left, $bottom, $parent);
        $n["left"] = $left;
        $n["bottom"] = $bottom;
        $n["text"] = $text;
        $n["parent"] = $parent;

        $o = $this->_addnewoid();
        $this->objects[$o] = $n;
        return $o;
    }

    function new_page($size = null)
    {
        if (is_null($size)) {
            $size = $this->default['pagesize'];
        }
        switch ($size) {
        case "letter" :
            $o = $this->_addnewoid();
            $this->objects[$o]["height"] = 792;
            $this->objects[$o]["width"] = 612;
            break;

        case "legal" :
            $o = $this->_addnewoid();
            $this->objects[$o]["height"] = 1008;
            $this->objects[$o]["width"] = 612;
            break;

        case "executive" :
            $o = $this->_addnewoid();
            $this->objects[$o]["height"] = 720;
            $this->objects[$o]["width"] = 540;
            break;

        case "tabloid" :
            $o = $this->_addnewoid();
            $this->objects[$o]["height"] = 1224;
            $this->objects[$o]["width"] = 792;
            break;

        case "a3" :
            $o = $this->_addnewoid();
            $this->objects[$o]["height"] = 1188;
            $this->objects[$o]["width"] = 842;
            break;

        case "a4" :
            $o = $this->_addnewoid();
            $this->objects[$o]["height"] = 842;
            $this->objects[$o]["width"] = 595;
            break;

        case "a5" :
            $o = $this->_addnewoid();
            $this->objects[$o]["height"] = 598;
            $this->objects[$o]["width"] = 418;
            break;

        default :
            if (preg_match("/in/",$size)) {
                $o = $this->_addnewoid();
                $size = substr($size, 0, strlen($size) - 2);
                $dims = split("x",$size);
                $this->objects[$o]["height"] = ($dims[1] * 72);
                $this->objects[$o]["width"] = ($dims[0] * 72);
            } else {
                if (preg_match("/cm/",$size)) {
                    $o = $this->_addnewoid();
                    $size = substr($size, 0, strlen($size) - 2);
                    $dims = split("x",$size);
                    $this->objects[$o]["height"] = ($dims[1] * 28.346);
                    $this->objects[$o]["width"] = ($dims[0] * 28.346);
                } else {
                    $this->_push_error(6004, "Could not deciper page size description: $size");
                    return false;
                }

            }
        }
        $this->objects[$o]['type'] = 'page';
        $this->objects[$o]['parent'] = 1;
        $this->objects[$o]['number'] = $this->nextpage;
        $this->nextpage ++;
        foreach (array('margin-left', 'margin-right', 'margin-top', 'margin-bottom') as $margin) {
	        $this->objects[$o][$margin] = $this->default[$margin];
        }
        return $o;
    }

    function swap_pages($p1, $p2)
    {
        if ($this->objects[$p1]["type"] != "page" ||
            $this->objects[$p2]["type"] != "page") {
                $this->_push_std_error(6001);
                return false;
        }
        $temp = $this->objects[$p1]["number"];
        $this->objects[$p1]["number"] = $this->objects[$p2]["number"];
        $this->objects[$p2]["number"] = $temp;
        return true;
    }

    function move_page_before($page, $infrontof)
    {
        if ($this->objects[$page]["type"] != "page" ||
            $this->objects[$infrontof]["type"] != "page") {
                $this->_push_std_error(6001);
                return false;
        }
        if ($page == $infrontof) {
            $this->_push_error(6005, "You're trying to swap a page with itself");
            return false;
        }
        $target = $this->objects[$infrontof]["number"];
        $leaving = $this->objects[$page]["number"];
        foreach ($this->objects as $id => $o) {
            if ($o["type"] == "page") {
                if ($target < $leaving) {
                    if ($o["number"] >= $target && $o["number"] < $leaving) {
                        $this->objects[$id]["number"]++;
                    }
                } else {
                    if ($o["number"] < $target && $o["number"] > $leaving) {
                        $this->objects[$id]["number"]--;
                    }
                }
            }
        }
        if ($target < $leaving) {
            $this->objects[$page]["number"] = $target;
        } else {
            $this->objects[$page]["number"] = $target - 1;
        }
        return true;
    }

    function new_font($identifier)
    {
        $n["type"] = "font";

        switch ($identifier) {
        /* The "standard" Type 1 fonts
         * These are "guaranteed" to be available
         * to the viewer application and don't
         * need embedded
         */
        case "Courier":
        case "Courier-Bold":
        case "Courier-Oblique":
        case "Courier-BoldOblique":
        case "Helvetica":
        case "Helvetica-Bold":
        case "Helvetica-Oblique":
        case "Helvetica-BoldOblique":
        case "Times-Roman":
        case "Times-Bold":
        case "Times-Italic":
        case "Times-BoldItalic":
        case "Symbol":
        case "ZapfDingbats":
            $o = $this->_addnewoid();
            $this->builddata["fonts"][$o] = $identifier;
            $n["subtype"] = "Type1";
            $n["basefont"] = $identifier;
            break;

        default:
            if ($this->objects[$identifier]["type"] != "fontembed") {
                $this->_push_error(6006, "Object must be of type 'fontembed'");
                return false;
            } else {
                // Not ready yet
                $this->_push_error(6007, "Feature not implemented yet");
                return false;
            }
        }
        $this->objects[$o] = $n;
        return $o;
    }

    function generate($clevel = 9)
    {
        // Validate the compression level
        if (!$clevel) {
            $this->builddata["compress"] = false;
        } else {
            if ($clevel < 10) {
                $this->builddata["compress"] = $clevel;
            } else {
                $this->builddata["compress"] = 9;
            }
        }
        /* Preprocess objects to see if they can
         * be combined into a single stream
         * We scan through each page, and create
         * a multistream object out of all viable
         * child objects
         */
        $temparray = $this->objects;
        foreach ($this->objects as $oid => $def) {
            if ( $def["type"] == "page" ) {
                unset($temp);
                $temp['data'] = "";
                reset($temparray);
                while ( list ($liboid, $obj) = each($temparray) ) {
                    if (isset($obj["parent"]) && $obj["parent"] == $oid) {
                        switch ($obj["type"]) {
                        case "texts" :
                            $temp["data"] .= $this->_make_text($liboid);
                            $this->objects[$liboid]["type"] = "null";
                            $this->objects[$liboid]["parent"] = -1;
                            break;

                        case "rectangle" :
                            $temp["data"] .= $this->_make_rect($liboid);
                            $this->objects[$liboid]["type"] = "null";
                            $this->objects[$liboid]["parent"] = -1;
                            break;

                        case "iplace" :
                            $temp["data"] .= $this->_place_raw_image($liboid);
                            $this->objects[$liboid]["type"] = "null";
                            $this->objects[$liboid]["parent"] = -1;
                            break;

                        case "line" :
                            $temp["data"] .= $this->_make_line($liboid);
                            $this->objects[$liboid]["type"] = "null";
                            $this->objects[$liboid]["parent"] = -1;
                            break;

                        case "circle" :
                            $temp["data"] .= $this->_make_circle($liboid);
                            $this->objects[$liboid]["type"] = "null";
                            $this->objects[$liboid]["parent"] = -1;
                            break;
                        }
                    }
                }
                if (strlen($temp["data"]) > 0) {
                    // this line takes the next available oid
                    $o = $this->_addnewoid();
                    $temp["type"] = "mstream";
                    $temp["parent"] = $oid;
                    $this->objects[$o] = $temp;
                }
            }
        }
        unset($temparray);

        // Generate a list of PDF object IDs to
        // use and map them to phppdflib IDs
        foreach ( $this->objects as $oid => $properties ) {
            if ( $this->_becomes_object( $properties["type"] ) ) {
                $o = $this->_addtoxreftable(0,0);
                $this->libtopdf[$oid] = $o;
                $this->pdftolib[$o] = $oid;
            }
        }

        /* First characters represent the version
         * of the PDF spec to conform to.
         * The PDF spec recommends that the next
         * four bytes be a comment containing four
         * non-ASCII characters, to convince
         * (for example) ftp programs that this is
         * a binary file
         */
        $os = "%PDF-1.3%\xe2\xe3\xcf\xd3\x0a";

        // Create the Document Catalog
        $carray["Type"] = "/Catalog";
        $carray["Pages"] = "2 0 R";
        $temp = $this->_makedictionary($carray);
        $temp = "1 0 obj" . $temp . "endobj\x0a";
        $this->xreftable[1]["offset"] = strlen($os);
        $os .= $temp;

        // Create the root page node
        unset($carray);
        $kids = $this->_order_pages(2);
        $this->xreftable[2]["offset"] = strlen($os);
        $os .= "2 0 " . $this->_makepagenode($kids, "" ) . "\x0a";

        /* Create a resource dictionary for the entire
         * PDF file.  This may not be the most efficient
         * way to store it, but it makes the code simple.
         * At some point, we should analyze performance
         * and see if it's worth splitting the resource
         * dictionary up
         */
        unset($temp);
        unset($carray);
        if (isset($this->builddata["fonts"]) && count($this->builddata["fonts"]) > 0) {
            foreach ($this->builddata["fonts"] as $id => $base) {
                $ta["F$id"] = $this->libtopdf[$id] . " 0 R";
            }
            $temp["Font"] = $this->_makedictionary($ta);
        }
        reset($this->objects);
        while (list($id, $obj) = each($this->objects)) {
            if ($obj["type"] == "image") {
                $xol["Img$id"] = $this->libtopdf[$id] . " 0 R";
            }
        }
        if ( isset($xol) && count($xol) > 0 ) {
            $temp["XObject"] = $this->_makedictionary($xol);
        }
        $this->xreftable[3]["offset"] = strlen($os);
        $os .= "3 0 obj";
        if (isset($temp)) {
            $os .= $this->_makedictionary($temp);
        } else {
            $os .= '<<>>';
        }
        $os .= " endobj\x0a";

        // Go through and add the rest of the objects
        foreach ( $this->pdftolib as $pdfoid => $liboid ) {
            if ($pdfoid < 4) {
                continue;
            }
            // Set the location of the start
            $this->xreftable[$pdfoid]["offset"] = strlen($os);
            switch ( $this->objects[$liboid]["type"] ) {
            case "page":
                $kids = $this->_get_kids($pdfoid);
                $os .= $pdfoid . " 0 ";
                $os .= $this->_makepage($this->objects[$liboid]["parent"],
                                        $kids, $liboid);
                break;

            case "rectangle":
                $os .= $pdfoid . " 0 obj";
                $os .= $this->_streamify($this->_make_rect($liboid));
                $os .= " endobj";
                break;

            case "line":
                $os .= $pdfoid . " 0 obj";
                $os .= $this->_streamify($this->_make_line($liboid));
                $os .= " endobj";
                break;

            case "circle":
                $os .= $pdfoid . " 0 obj";
                $os .= $this->_streamify($this->_make_circle($liboid));
                $os .= " endobj";
                break;

            case "texts":
                $os .= $pdfoid . " 0 obj";
                $temp = $this->_make_text($liboid);
                $os .= $this->_streamify($temp) . " endobj";
                break;

            case "mstream":
                $os .= $pdfoid . " 0 obj" .
                       $this->_streamify(trim($this->objects[$liboid]["data"])) .
                       " endobj";
                break;

            case "image":
                $os .= $pdfoid . " 0 obj";
                $os .= $this->_make_raw_image($liboid);
                $os .= " endobj";
                break;

            case "iplace":
                $os .= $pdfoid . " 0 obj";
                $os .= $this->_streamify($this->_place_raw_image($liboid));
                $os .= " endobj";
                break;

            case "font" :
                $os .= $pdfoid . " 0 obj";
                unset ( $temp );
                $temp["Type"] = "/Font";
                $temp["Subtype"] = "/" . $this->objects[$liboid]["subtype"];
                $temp["BaseFont"] = "/" . $this->objects[$liboid]["basefont"];
                $temp["Encoding"] = "/WinAnsiEncoding";
                $temp["Name"] = "/F$liboid";
                $os .= $this->_makedictionary($temp);
                $os .= " endobj";
                break;
            }
            $os .= "\x0a";
        }

        // Create an Info entry
        $info = $this->_addtoxreftable(0,0);
        $this->xreftable[$info]["offset"] = strlen($os);
        unset($temp);
        $temp["Producer"] =
            $this->_stringify("phppdflib http://www.potentialtech.com/ppl.php");
        $os .= $info . " 0 obj" . $this->_makedictionary($temp) . " endobj\x0a";

        // Create the xref table
        $this->builddata["startxref"] = strlen($os);
        $os .= "xref\x0a0 " . (string)($this->nextobj + 1) . "\x0a";
        for ( $i = 0; $i <= $this->nextobj; $i ++ ) {
            $os .= sprintf("%010u %05u %s \x0a", $this->xreftable[$i]["offset"],
                           $this->xreftable[$i]["gennum"],
                           $this->xreftable[$i]["free"]);
        }

        // Create document trailer
        $os .= "trailer\x0a";
        unset($temp);
        $temp["Size"] = $this->nextobj + 1;
        $temp["Root"] = "1 0 R";
        $temp["Info"] = $info . " 0 R";
        $os .= $this->_makedictionary($temp);
        $os .= "\x0astartxref\x0a";
        $os .= $this->builddata["startxref"] . "\x0a";

        // Required end of file marker
        $os .= "%%EOF\x0a";

        return $os;
    }

    function png_embed($data)
    {
        // Sanity, make sure this is a png
        if (substr($data, 0, 8) != "\137PNG\x0d\x0a\x1a\x0d") {
            $this->_push_std_error(6011);
            return false;
        }
        
    }

    function jfif_embed($data)
    {
        /* Sanity check: Check magic numbers to see if
         * this is really a JFIF stream
         */
        if ( substr($data, 0, 4) != "\xff\xd8\xff\xe0" ||
             substr($data, 6, 4) != "JFIF" ) {
            // This is not in JFIF format
            $this->_push_std_error(6008);
            return false;
        }

        /* Now we'll scan through all the markers in the
         * JFIF and extract whatever data we need from them
         * We're not being terribly anal about validating
         * the structure of the JFIF, so a corrupt stream
         * could have very unpredictable results
         */
        // Default values
        $pos = 0;
        $size = strlen($data);

        while ( $pos < $size ) {
            $marker = substr($data, $pos + 1, 1);
            // Just skip these markers
            if ($marker == "\xd8" || $marker == "\xd9" || $marker == "\x01") {
                $pos += 2;
                continue;
            }
            if ($marker == "\xff") {
                $pos ++;
                continue;
            }

            switch ($marker) {
            // Start of frame
            // Baseline
            case "\xc0":
            // Extended sequential
            case "\xc1":
            // Differential sequential
            case "\xc5":
            // Progressive
            case "\xc2":
            // differential progressive
            case "\xc6":
            // Lossless
            case "\xc3":
            // differential lossless
            case "\xc7":
            // Arithmetic encoded
            case "\xc9":
            case "\xca":
            case "\xcb":
            case "\xcd":
            case "\xce":
            case "\xcf":
                $precision = $this->_int_val(substr($data, $pos + 4, 1));
                $height = $this->_int_val(substr($data, $pos + 5, 2));
                $width = $this->_int_val(substr($data, $pos + 7, 2));
                $numcomp = $this->_int_val(substr($data, $pos + 9, 1));
                if ( $numcomp != 3 && $numcomp != 1 ) {
                    // Abort if we aren't encoded as B&W or YCbCr
                    $this->_push_std_error(6008);
                    return false;
                }
                $pos += 2 + $this->_int_val(substr($data, $pos + 2, 2));
                break 2;
            }

            /* All marker identifications continue the
             * loop, thus if we got here, we need to skip
             * this marker as we don't understand it.
             */
            $pos += 2 + $this->_int_val(substr($data, $pos + 2, 2));
        }
        $cspace = $numcomp == 1 ? "/DeviceGray" : "/DeviceRGB";
        return $this->image_raw_embed($data,
                                      $cspace,
                                      $precision,
                                      $height,
                                      $width,
                                      "/DCTDecode");
    }

    function image_raw_embed($data, $cspace, $bpc, $height, $width, $filter = "")
    {
        $o = $this->_addnewoid();
        $t["data"] = $data;
        $t["colorspace"] = $cspace;
        $t["bpc"] = $bpc;
        $t["type"] = "image";
        $t["height"] = $height;
        $t["width"] = $width;
        $t["filter"] = $filter;
        $this->objects[$o] = $t;
        return $o;
    }

    function get_image_size($id)
    {
        if ($this->objects[$id]['type'] != 'image') {
            $this->_push_std_error(6009);
            return false;
        }
        $r['width'] = $this->objects[$id]['width'];
        $r['height'] = $this->objects[$id]['height'];
        return $r;
    }

    function image_place($oid, $bottom, $left, $parent, $param = array())
    {
        if ($this->objects[$oid]["type"] != "image") {
            $this->_push_std_error(6009);
            return false;
        }
        if ($this->objects[$parent]["type"] != "page") {
            $this->_push_std_error(6001);
            return false;
        }

        $o = $this->_addnewoid();
        $param = $this->_resolve_param($param, false);
        $t["type"] = "iplace";
        $this->_adjust_margin($left, $bottom, $parent);
        $t["bottom"] = $bottom;
        $t["left"] = $left;
        $t["parent"] = $parent;
        // find out what the image size should be
        $width = $this->objects[$oid]["width"];
        $height = $this->objects[$oid]["height"];
        $scale = $param['scale'];
        if (is_array($scale)) {
            $t["xscale"] = $scale["x"] * $width;
            $t["yscale"] = $scale["y"] * $height;
        } else {
            $t["xscale"] = $scale * $width;
            $t["yscale"] = $scale * $height;
        }
        $t["rotation"] = $param['rotation'];
        $t["image"] = $oid;
        $this->objects[$o] = $t;
        return $o;
    }

    function strlen($string , $params = false, $tabwidth = 4)
    {
        if ($this->needsset) {
            require_once(dirname(__FILE__) . '/pdffile_strlen_tables.php');
        }
        if (empty($params["font"])) {
            $font = $this->default['font'];
        } else {
            $font = $params["font"];
            switch ($font) {
                case "Times-Roman" :
                    $font = "Times";
                    break;
                case "Helvetica-Oblique" :
                    $font = "Helvetica";
                    break;
                case "Helvetica-BoldOblique" :
                    $font = "Helvetica-Bold";
                    break;
                case "ZapfDingbats" :
                    $font = "Dingbats";
                    break;
            }
        }
        if ($params["height"] == 0) {
            $size = $this->default['height'];
        } else {
            $size = $params["height"];
        }
        $tab = '';
        for ($i = 0; $i < $tabwidth; $i++) {
        	$tab .= ' ';
        }
        $string = str_replace(chr(9), $tab, $string);
        if (substr($font, 0, 7) == "Courier") {
            // Courier is a fixed-width font
            $width = strlen($string) * 600;
        } else {
            $width = 0;
            $len = strlen($string);
            for ($i = 0; $i < $len; $i++) {
                $width += $this->widths[$font][ord($string{$i})];
            }
        }
        // We now have the string width in font units
        return $width * $size / 1000;
    }

    function wrap_line(&$text, $width, $param = array())
    {
        $maxchars = (int)(1.1 * $width / $this->strlen("i", $param));
        $words = explode(" ", substr($text, 0, $maxchars));
        if ($this->strlen($words[0]) >= $width) {
            $this->_push_error(3001, "Single token too long for allowed space");
            $final = $words[0];
        } else {
            $space = $this->strlen(" ", $param);
            $len = 0;
            $word = 0;
            $final = "";
            while ($len < $width) {
                if ($word >= count($words)) {
                    break;
                }
                $temp = $this->strlen($words[$word], $param);
                if ( ($len + $temp) <= $width) {
                    $final .= $words[$word] . " ";
                    $word ++;
                }
                $len += $space + $temp;
            }
        }
        $text = substr($text, strlen($final));
        return $final;
    }

    function word_wrap($words, $width, $param = array())
    {
        // break $words into an array separated by manual paragraph break character
        $paragraph = explode("\n", $words);
        // find the width of 1 space in this font
        $swidth = $this->strlen( " " , $param );
        // uses each element of $paragraph array and splits it at spaces
        for ($lc = 0; $lc < count($paragraph); $lc++){
            while (strlen($paragraph[$lc]) > 0) {
                $returnarray[] = $this->wrap_line($paragraph[$lc], $width, $param);
            }
        }
        return $returnarray;
    }

    function draw_one_paragraph($top, $left, $bottom, $right, $text, $page, $param = array())
    {
        $param = $this->_resolve_param($param);
        $height = 1.1 * $param['height'];
        $width = $right - $left;
        while ($top > $bottom) {
            if (strlen($text) < 1) {
                break;
            }
            $top -= $height;
            if ($top >= $bottom) {
                $line = $this->wrap_line($text, $width, $param);
                switch ($param['align']) {
                case 'right' :
                    $line = trim($line);
                    $l = $right - $this->strlen($line, $param);
                    break;

                case 'center' :
                    $line = trim($line);
                    $l = $left + (($width - $this->strlen($line, $param)) / 2);
                    break;
                
                default :
                    $l = $left;
                }
                $this->draw_text($l, $top, $line, $page, $param);
            } else {
                $top += $height;
                break;
            }
        }
        if (strlen($text) > 0) {
            return $text;
        } else {
            return $top;
        }
    }

    function draw_paragraph($top, $left, $bottom, $right, $text, $page, $param = array())
    {
        $paras = split("\n", $text);
        for ($i = 0; $i < count($paras); $i++) {
            $over = $this->draw_one_paragraph($top,
                                              $left,
                                              $bottom,
                                              $right,
                                              $paras[$i],
                                              $page,
                                              $param);
            if (is_string($over)) {
                break;
            }
            $top = $over;
        }
        $rv = $over;
        if ($i < count($paras)) {
            for ($x = $i + 1; $x < count($paras); $x++) {
                $rv .= "\n" . $paras[$x];
            }
        }
        return $rv;
    }

    function error_array()
    {
        $rv = array();
        while (count($this->ermsg) > 0) {
            $this->pop_error($num, $msg);
            $rv[] = "Error $num: $msg";
        }
        return $rv;
    }

    function pop_error(&$num, &$msg)
    {
        $num = array_pop($this->erno);
        $msg = array_pop($this->ermsg);
        if (is_null($num)) {
        	return false;
        } else {
	        return $num;
        }
    }

    function enable($name)
    {
    	$name = strtolower($name);
        @include_once(dirname(__FILE__) . "/${name}.class.php");
        $this->x[$name] = new $name;
        $this->x[$name]->pdf = &$this;
        switch ($name) {
        case 'chart' :
        case 'template' :
	        $this->$name = &$this->x[$name];
            break;
        }
    }

    function get_color($desc)
    {

        $r = array();
        switch (strtolower($desc)) {
        case 'black' :
            $r['red'] = $r['blue'] = $r['green'] = 0;
            break;

        case 'white' :
            $r['red'] = $r['blue'] = $r['green'] = 1;
            break;
            
        case 'red' :
            $r['red'] = 1;
            $r['blue'] = $r['green'] = 0;
            break;
            
        case 'blue' :
            $r['blue'] = 1;
            $r['red'] = $r['green'] = 0;
            break;

        case 'green' :
            $r['green'] = 1;
            $r['blue'] = $r['red'] = 0;
            break;

        default :
        	if (substr($desc, 0, 1) == '#') {
            	// Parse out a hex triplet
                $v = substr($desc, 1, 2);
                $r['red'] = eval("return ord(\"\\x$v\");") / 255;
                $v = substr($desc, 3, 2);
                $r['green'] = eval("return ord(\"\\x$v\");") / 255;
                $v = substr($desc, 5, 2);
                $r['blue'] = eval("return ord(\"\\x$v\");") / 255;
            } else {
            	// Error condition?
                $this->_push_error(6012, "Unparsable color identifier: $desc");
                $r = false;
            }
        }
        return $r;
    }

/******************************************************
 * These functions are internally used by the library *
 * and shouldn't really be called by a user of        *
 * phppdflib                                          *
 ******************************************************/

    function _resolve_mode($attrib, $mode)
    {
        $rmode = $attrib[$mode];
        if ($rmode != 0) {
            $r = $rmode;
        } else {
            switch ($rmode) {
            case "fill":
                $r = 0;
                break;

            case "stroke":
                $r = 1;
                break;

            case "fill+stroke":
                $r = 2;
                break;
            }
        }
        return $r;
    }

    function _adjust_margin(&$x, &$y, $page)
    {
        $x += $this->objects[$page]['margin-left'];
        $y += $this->objects[$page]['margin-bottom'];
    }

    function _resolve_param($param, $text = true)
    {
        $rv = $this->default;
        if (is_array($param)) {
            if (isset($param['mode'])) {
                $param['tmode'] = $param['smode'] = $param['mode'];
            }
            foreach ($param as $key => $value) {
                $rv[$key] = $value;
            }
        }
        return $rv;
    }
    
    function _push_error($num, $msg)
    {
        array_push($this->erno, $num);
        array_push($this->ermsg, $msg);
    }

    function _push_std_error($num)
    {
        switch ($num) {
            case 6001 : $m = "Object must be of type 'page'"; break;
            case 6008 : $m = "Data stream not recognized as JFIF"; break;
            case 6009 : $m = "Object must be of type 'image'"; break;
            case 6011 : $m = "Data stream not recognized as PNG"; break;
            default : $m = "_push_std_error() called with invalid error number: $num"; break;
        }
        $this->_push_error($num, $m);
    }

    function _resolve_colors(&$n, $attrib)
    {
        $temp = array('red','green','blue');
        foreach ($temp as $colcomp) {
            if (isset($attrib['fillcolor'][$colcomp])) {
                $n['fillcolor'][$colcomp] = $attrib['fillcolor'][$colcomp];
            }
            if (isset($attrib['strokecolor'][$colcomp])) {
                $n['strokecolor'][$colcomp] = $attrib['strokecolor'][$colcomp];
            }
        }
    }

    /* Check to see if a requested font is already in the
     * list, if not add it.  Either way, return the libid
     * of the font
     */
    function _use_font($id)
    {
        if (!isset($id['font'])) {
            $id['font'] = $this->default['font'];
        }
        if ( isset($this->builddata["fonts"]) && count($this->builddata["fonts"]) > 0 ) {
            foreach ($this->builddata["fonts"] as $libid => $name) {
                if ($name == $id['font']) {
                    return $libid;
                }
            }
        }
        /* The font isn't in the table, so we add it
         * and return it's ID
         */
        return $this->new_font($id['font']);
    }

    /* Convert a big-endian byte stream into an integer */
    function _int_val($string)
    {
        $r = 0;
        for ($i = 0; $i < strlen($string); $i ++ ) {
            $r += ord($string{$i}) * pow(256, strlen($string) - $i -1);
        }
        return $r;
    }

    function _make_raw_image($liboid)
    {
        $s["Type"] = "/XObject";
        $s["Subtype"] = "/Image";
        $s["Width"] = $this->objects[$liboid]["width"];
        $s["Height"] = $this->objects[$liboid]["height"];
        $s["ColorSpace"] = $this->objects[$liboid]["colorspace"];
        $s["BitsPerComponent"] = $this->objects[$liboid]["bpc"];
        if (strlen($this->objects[$liboid]["filter"]) > 0) {
            $s["Filter"] = $this->objects[$liboid]["filter"];
        }
        return $this->_streamify($this->objects[$liboid]["data"], $s);
    }

    function _place_raw_image($liboid)
    {
        $xscale = $this->objects[$liboid]["xscale"];
        $yscale = $this->objects[$liboid]["yscale"];
        $angle = $this->objects[$liboid]["rotation"];
        $temp = "q 1 0 0 1 " .
                $this->objects[$liboid]["left"] . " " .
                $this->objects[$liboid]["bottom"] . " cm ";
        if ($angle != 0) {
            $temp .= $this->_rotate($angle) . " cm ";
        }
        if ($xscale != 1 || $yscale != 1) {
            $temp .= "$xscale 0 0 $yscale 0 0 cm ";
        }
        $temp .= "/Img" . $this->objects[$liboid]["image"] .
                 " Do Q\x0a";
        return $temp;
    }

    function _rotate($angle)
    {
        $a = deg2rad($angle);
        $cos = cos($a);
        $sin = sin($a);
        $r = sprintf("%1\$01.6f %2\$01.6f %3\$01.6f %1\$01.6f 0 0", $cos, $sin, -$sin);
        return $r;
    }

    function _get_operator($liboid)
    {
        switch ($this->objects[$liboid]['mode']) {
        case 0 : return "f"; break;
        case 1 : return "S"; break;
        case 2 : return "b"; break;
        }
    }

    function _make_line($liboid)
    {
        $gstate = "";
        if ( $colortest = $this->_colorset($liboid) ) {
            $gstate .= $colortest . " ";
        }
        if ( isset($this->objects[$liboid]["width"]) && $this->objects[$liboid]["width"] != 1 ) {
            $gstate .= $this->objects[$liboid]["width"] . " w ";
        }
        $firstpoint = true;
        $temp = "";
        foreach ($this->objects[$liboid]["x"] as $pointid => $x) {
            $y = $this->objects[$liboid]["y"][$pointid];
            $temp .= $x . " " . $y . " ";
            if ($firstpoint) {
                $temp .= "m ";
                $firstpoint = false;
            } else {
                $temp .= "l ";
            }
        }
        $temp .= $this->_get_operator($liboid);
        if ( strlen($gstate) > 0 ) {
            $temp = "q " . $gstate . $temp . " Q";
        }
        return $temp . "\x0a";
    }

    function _make_rect($liboid)
    {
        $gstate = "";
        if ( $colortest = $this->_colorset($liboid) ) {
            $gstate .= $colortest . " ";
        }
        if ( isset($this->objects[$liboid]["width"]) && $this->objects[$liboid]["width"] != 1 ) {
            $gstate .= $this->objects[$liboid]["width"] . " w ";
        }
        $temp = $this->objects[$liboid]["left"] . " ";
        $temp .= $this->objects[$liboid]["bottom"];
        $temp .= " " . ( $this->objects[$liboid]["right"]
                 - $this->objects[$liboid]["left"] );
        $temp .= " " . ( $this->objects[$liboid]["top"]
                 - $this->objects[$liboid]["bottom"] );
        $temp .= ' re ';
        $temp .= $this->_get_operator($liboid);
        if ( strlen($gstate) > 0 ) {
            $temp = "q " . $gstate . $temp . " Q";
        }
        return $temp . "\x0a";
    }

    function _make_circle($liboid)
    {
        $gstate = "";
        if ( $colortest = $this->_colorset($liboid) ) {
            $gstate .= $colortest . " ";
        }
        if ( isset($this->objects[$liboid]["width"]) && $this->objects[$liboid]["width"] != 1 ) {
            $gstate .= $this->objects[$liboid]["width"] . " w ";
        }
        $r = $this->objects[$liboid]['radius'];
        $x = $this->objects[$liboid]['x'];
        $y = $this->objects[$liboid]['y'];
        $ql = $x - $r;
        $pt = $y + $r * 1.33333;
        $qr = $x + $r;
        $pb = $y - $r * 1.33333;
        $temp = "$ql $y m ";
        $temp .= "$ql $pt $qr $pt $qr $y c ";
        $temp .= "$qr $pb $ql $pb $ql $y c ";
        $temp .= $this->_get_operator($liboid);
        if ( strlen($gstate) > 0 ) {
            $temp = "q " . $gstate . $temp . " Q";
        }
        return $temp . "\x0a";
    }

    function _make_text($liboid)
    {
        $statechange = ""; $locateinbt = true;
        $statechange = $this->_colorset($liboid);
        if (isset($this->objects[$liboid]["rotation"]) && $this->objects[$liboid]["rotation"] != 0) {
            $statechange .= "1 0 0 1 " .
                            $this->objects[$liboid]["left"] . " " .
                            $this->objects[$liboid]["bottom"] . " cm " .
                            $this->_rotate($this->objects[$liboid]["rotation"]) .
                            " cm ";
            $locateinbt = false;
        }
        $temp = "BT ";
        if ($this->objects[$liboid]["mode"] != 0) {
            $temp .= $this->objects[$liboid]["mode"] .
                            " Tr ";
            // Adjust stroke width
            $statechange .= $this->objects[$liboid]["height"] / 35 . " w ";
        }
        $temp .= "/F" . $this->objects[$liboid]["font"] . " ";
        $temp .= $this->objects[$liboid]["height"];
        $temp .= " Tf ";
        if ($locateinbt) {
            $temp .= $this->objects[$liboid]["left"] . " " .
                     $this->objects[$liboid]["bottom"];
        } else {
            $temp .= "0 0";
        }
        $temp .= " Td ";
        $temp .= $this->_stringify($this->objects[$liboid]["text"]);
        $temp .= " Tj ";
        $temp .= "ET";
        if (strlen($statechange) > 0) {
            $temp = "q " . $statechange . $temp . " Q";
        }
        return $temp . "\x0a";
    }

    function _colorset($libid)
    {
        $red = isset($this->objects[$libid]['fillcolor']["red"]) ? (float)$this->objects[$libid]['fillcolor']["red"] : 0;
        $green = isset($this->objects[$libid]['fillcolor']["green"]) ? (float)$this->objects[$libid]['fillcolor']["green"] : 0;
        $blue = isset($this->objects[$libid]['fillcolor']["blue"]) ? (float)$this->objects[$libid]['fillcolor']["blue"] : 0;
        if (($red > 0) || ($green > 0) || ($blue > 0)) {
            $r = $red . " " . $green . " " . $blue;
            $r .= " rg ";
        } else {
            $r = "";
        }
        $red = isset($this->objects[$libid]['strokecolor']["red"]) ? (float)$this->objects[$libid]['strokecolor']["red"] : 0;
        $green = isset($this->objects[$libid]['strokecolor']["green"]) ? (float)$this->objects[$libid]['strokecolor']["green"] : 0;
        $blue = isset($this->objects[$libid]['strokecolor']["blue"]) ? (float)$this->objects[$libid]['strokecolor']["blue"] : 0;
        if (($red > 0) || ($green > 0) || ($blue > 0) ) {
            $r .= $red . " " . $green . " " . $blue;
            $r .= " RG ";
        }
        return $r;
    }

    /* Used to determine what pdflib objects need converted
     * to actual PDF objects.
     */
    function _becomes_object($object)
    {
        if ($object == "null") {
            return false;
        }
        return true;
    }

    /* builds an array of child objects */
    function _get_kids($pdfid)
    {
        $libid = $this->pdftolib[$pdfid];
        foreach( $this->objects as $obid => $object ) {
            if (isset($object["parent"]) && $object["parent"] == $libid) {
                $kids[] = $this->libtopdf[$obid] . " 0 R";
            }
        }
        return $kids;
    }

    /* builds an array of pages, in order */
    function _order_pages($pdfid)
    {
        $libid = $this->pdftolib[$pdfid];
        foreach( $this->objects as $obid => $object ) {
            if (isset($object["parent"]) && $object["parent"] == $libid) {
                $kids[$object["number"]] = $this->libtopdf[$obid] . " 0 R";
            }
        }
        ksort($kids);
        return $kids;
    }

    /* simple helper function to return the current oid
     * and increment it by one
     */
    function _addnewoid()
    {
        $o = $this->nextoid;
        $this->nextoid++;
        return $o;
    }

    /* The xreftable will contain a list of all the
     * objects in the pdf file and the number of bytes
     * from the beginning of the file that the object
     * occurs. Each time we add an object, we call this
     * to record it's location, then call ->_genxreftable()
     * to generate the table from array
     */
    function _addtoxreftable($offset, $gennum)
    {
        $this->nextobj ++;
        $this->xreftable[$this->nextobj]["offset"] = $offset;
        $this->xreftable[$this->nextobj]["gennum"] = $gennum;
        $this->xreftable[$this->nextobj]["free"] = "n";
        return $this->nextobj;
    }

    /* Returns a properly formatted pdf dictionary
     * containing entries specified by
     * the array $entries
     */
    function _makedictionary($entries)
    {
        $rs = "<<\x0a";
        if (isset($entries) && count($entries) > 0) {
            foreach ($entries as $key => $value) {
                $rs .= "/" . $key . " " . $value . "\x0a";
            }
        }
        $rs .= ">>";
        return $rs;
    }

    /* returns a properly formatted pdf array */
    function _makearray($entries)
    {
        $rs = "[";
        if ( is_array($entries) ) {
            foreach ($entries as $entry) {
                $rs .= $entry . " ";
            }
        } else {
            $rs .= $entries;
        }
        $rs = rtrim($rs) . "]";
        return $rs;
    }

    /* Returns a properly formatted string, with any
     * special characters escaped
     */
    function _stringify($string)
    {
        // Escape potentially problematic characters
        $string = preg_replace("-\\\\-","\\\\\\\\",$string);
        $bad = array ("-\(-", "-\)-" );
        $good = array ("\\(", "\\)" );
        $string = preg_replace($bad,$good,$string);
        return "(" . rtrim($string) . ")";
    }

    function _streamify($data, $sarray = array())
    {
        /* zlib compression is a compile time option
         * for php, thus we need to make sure it's
         * available before using it.
         */
        if ( function_exists('gzcompress') && $this->builddata["compress"] ) {
            // For now, we don't compress if already using a filter
            if ( !isset($sarray["Filter"]) || strlen($sarray["Filter"]) == 0 ) {
                $sarray["Filter"] = "/FlateDecode";
            } else {
                $sarray['Filter'] = "[/FlateDecode " . $sarray['Filter'] . "]";
            }
            $data = gzcompress($data, $this->builddata["compress"]);
        }
        $sarray["Length"] = strlen($data);
        $os = $this->_makedictionary($sarray);
        $os .= "stream\x0a" . $data . "\x0aendstream";
        return $os;
    }

    /* Returns a properly formatted page node
     * page nodes with 0 kids are not created
     */
    function _makepagenode($kids, $addtlopts = false)
    {
        $parray["Type"] = "/Pages";
        if ( isset($kids) AND count($kids) > 0 ) {
            // Array of child objects
            $parray["Kids"] = $this->_makearray($kids);
            // Number of pages
            $parray["Count"] = count($kids);
        } else {
            // No kids is an error condition
            $this->_push_error(600, "Pagenode has no children");
            return false;
        }
        if ( is_array($addtlopts) ) {
            foreach ( $addtlopts as $key => $value ) {
                $parray[$key] = $value;
            }
        }

        /* The resource dictionary is always object 3
         */
        $parray["Resources"] = "3 0 R";

        $os = $this->_makedictionary($parray);
        $os = "obj" . $os . "endobj";
        return $os;
    }

    function _makepage($parent, $contents, $liboid)
    {
        $parray["Type"] = "/Page";
        $parray["Parent"] = $this->libtopdf[$parent] . " 0 R";
        $parray["Contents"] = $this->_makearray($contents);
        $parray["MediaBox"] = "[0 0 "
                            . $this->objects[$liboid]["width"] . " "
                            . $this->objects[$liboid]["height"] . "]";
        $os = $this->_makedictionary($parray);
        $os = "obj" . $os . "endobj";
        return $os;
    }

}
?>
