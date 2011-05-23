<?php
 // $Id$
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2011 FreeMED Software Foundation
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

// Class: org.freemedsoftware.core.GraphNormalize
//
//	Normalizing graph for trending data points over time
//
class GraphNormalize {

	var $set_count;
	var $data;

	var $graph;
	var $interval;
	var $ca;
	var $font;
	var $color;
	var $title;
	var $image_size;
	var $canvas_size;
	var $canvas_offset;

	// Constructor: GraphNormalize
	//
	// Parameters:
	//
	//	$title - Title for graph
	//
	//	$options - (optional) Associative array
	//		* dashed_grid - (bool) Set to true to have a dashed-style grid
	//		* interval - Number of divisions in the graph
	//
	function GraphNormalize ( $title, $options = NULL ) {
		$this->options = $options;
		$this->interval = $this->options['interval'] ? $this->options['interval'] : 10;

		// TODO: Move these to friendly options
		$this->image_size = array (600, 400);
		$this->canvas_offset = array ( 25, 50, 25, 25 );
		$this->canvas_size = array (
			$this->image_size[0] - ($this->canvas_offset[0] + $this->canvas_offset[2]),
			$this->image_size[1] - ($this->canvas_offset[1] + $this->canvas_offset[3])
		);

		// Actual creation stuff
		$this->graph = ImageCreate( $this->image_size[0], $this->image_size[1] );
		$this->color['background'] = ImageColorAllocate( $this->graph, 0xff, 0xff, 0xff );
		$this->color['line'] = ImageColorAllocate( $this->graph, 0x60, 0x60, 0x60 );
		$this->color['border'] = ImageColorAllocate( $this->graph, 0x00, 0x00, 0x00 );
		$this->color['text'] = ImageColorAllocate( $this->graph, 0x00, 0x00, 0x00 );
		$this->color['grid'] = ImageColorAllocate( $this->graph, 0xC0, 0xC0, 0xC0 );
		$this->color['canvas'] = ImageColorAllocate( $this->graph, 0xE0, 0xE0, 0xE0 );
		$this->color['legendbox'] = ImageColorAllocate( $this->graph, 0xD0, 0xD0, 0xF0 );
		$this->color['titlebox'] = ImageColorAllocate( $this->graph, 0xD0, 0xF0, 0xF0 );
		$this->color['data'] = array(
			ImageColorAllocate( $this->graph, 0xFF, 0x00, 0x00 ),
			ImageColorAllocate( $this->graph, 0x00, 0xCC, 0x00 ),
			ImageColorAllocate( $this->graph, 0x00, 0x00, 0xFF ),
			ImageColorAllocate( $this->graph, 0xFF, 0xFF, 0x00 ),
			ImageColorAllocate( $this->graph, 0x00, 0xFF, 0xFF ),
			ImageColorAllocate( $this->graph, 0xFF, 0x00, 0xFF ),
		);

		$this->font = 'lib/template/default/Vera.ttf';
		$this->title = $title;

		$this->set_count = 0;

		// Define canvas, so we don't go crazy
		$this->ca = array(
			$this->canvas_offset[0], // left
			$this->canvas_offset[1], // top
			($this->canvas_size[0] - $this->canvas_offset[2]) - 1, // right
			($this->canvas_size[1] - $this->canvas_offset[3]) - 1 // bottom
		);
	} // end constructor

	// Method: DrawCanvas
	//
	//	Method to draw the canvas background. Should be called before
	//	<DrawData>.
	//
	function DrawCanvas ( ) {
		// Define canvas, so we don't go crazy
		$ca = $this->ca;

		ImageFill( $this->graph, 0, 0, $this->canvas_size[0] - 1, $this->canvas_size[1] - 1, $this->color['background'] );
		ImageFilledRectangle( $this->graph, $ca[0], $ca[1], $ca[2], $ca[3], $this->color['canvas'] );
		// Border lines
		ImageLine( $this->graph, $ca[0], $ca[1], $ca[0], $ca[3], $this->color['border'] );
		ImageLine( $this->graph, $ca[2], $ca[1], $ca[2], $ca[3], $this->color['border'] );
		ImageLine( $this->graph, $ca[0], $ca[1], $ca[2], $ca[1], $this->color['border'] );
		ImageLine( $this->graph, $ca[0], $ca[3], $ca[2], $ca[3], $this->color['border'] );

		// Draw 10ths horizonal and vertical
		$step = array ( ($ca[2] - $ca[0])/$this->interval, ($ca[3] - $ca[1])/$this->interval );
		for($i=$ca[0]+$step[0]; $i<=($ca[2]-$step[0]); $i+=$step[0]) {
			if ($this->options['dashed_grid']) {
				$this->_DashedLine( $i, $ca[1]+1, $i, $ca[3]-1, $this->color['grid'], $this->color['background'] );
			} else {
				ImageLine( $this->graph, $i, $ca[1]+1, $i, $ca[3]-1, $this->color['grid'] );
			}
		}
		for($i=$ca[1]+$step[1]; $i<=($ca[3]-$step[1]); $i+=$step[1]) {
			if ($this->options['dashed_grid']) {
				$this->_DashedLine( $ca[0]+1, $i, $ca[2]-1, $i, $this->color['grid'], $this->color['background'] );
			} else {
				ImageLine( $this->graph, $ca[0]+1, $i, $ca[2]-1, $i, $this->color['grid'] );
			}
		}
	} // end method DrawCanvas

	// Method: DrawData
	//
	//	Draws the actual points on the graph.
	//
	function DrawData ( ) {
		if ($this->set_count == 0) { return false; }

		// Create scaling
		$ca = $this->ca;
		$this->GetDataLimits($minx, $miny, $maxx, $maxy);
		$range = array ( $maxx - $minx, $maxy - $miny );
		$ca_range = array ( ($ca[2] - $ca[0])+1, ($ca[3] - $ca[1])+1 );
		if ($range[0] == 0 or $range[1] == 0) { die("Divide by 0 imminent!"); }
		$scaling = array ( $ca_range[0] / $range[0], $ca_range[1] / $range[1] );

		// Loop for each data set
		$count = 0;
		foreach ($this->data AS $d) {
			unset($points);
			foreach ($d AS $v) {
				$points[] = array (
					($v[0] * $scaling[0]) + ($ca[0] + 1),
					$ca[3] - ($v[1] * $scaling[1])
				);
			}
			$first = 1;
			foreach ($points AS $p) {
				if ($first) {
					$lp = $p;
					$first = 0;
				} else {
					ImageLine( $this->graph, $lp[0], $lp[1], $p[0], $p[1], $this->color['data'][$count]);
					$lp = $p;
				}

				// Put nice circle around the point
				ImageArc( $this->graph, $p[0], $p[1], 5, 5, 0, 359, $this->color['line'] );
			}

			// Increment set counter
			$count++;
		}

		// Show min/max for x/y
		$fontsize = 6;
		ImageTtfText( $this->graph, $fontsize, 60, $ca[0]+4, $ca[3]+20, $this->color['text'], $this->font, $minx );
		ImageTtfText( $this->graph, $fontsize, 0, $ca[0]-25, $ca[1]+4, $this->color['text'], $this->font, $maxy );
		//ImageTtfText( $this->graph, $fontsize, 60, $ca[2]+4, $ca[3]+20, $this->color['text'], $this->font, $maxx );
		ImageTtfText( $this->graph, $fontsize, 0, $ca[0]-25, $ca[3]+4, $this->color['text'], $this->font, $miny );

		// Show "steps" (5 of 'em)
		$interval = $this->interval;
		$step = array ( ($ca[2] - $ca[0])/$interval, ($ca[3] - $ca[1])/$interval );
		$count = 0;
		for($i=$ca[0]+$step[0]; $i<$ca[2]; $i+=$step[0]) {
			$count++;
			$show = ($minx + (($range[0] / $interval) * $count));
			ImageTtfText( $this->graph, $fontsize, 60, $i+4, $ca[3]+20, $this->color['text'], $this->font, ceil($show) );
		}
		$count = 0;
		for($i=$ca[1]+$step[1]; $i<$ca[3]; $i+=$step[1]) {
			$count++;
			$show = ( $maxy - (($range[1] / $interval) * $count ));
			ImageTtfText( $this->graph, $fontsize, 0, $ca[0]-25, $i+4, $this->color['text'], $this->font, ceil($show) );
		}
	} // end method DrawData

	// Method: DrawLegend
	//
	//	Internal method to draw the "legend" box.
	//
	function DrawLegend ( ) {
		if (!is_array($this->data)) { return true; }

		$fontsize = 7;
		$spacing = 5;

		foreach ($this->data AS $k => $v) {
			$bbox = ImageTtfBBox( $fontsize, 0, $this->font, $k );
			$width = $bbox[2] - $bbox[0];
			$height = $bbox[3] - $bbox[7];
			if ($width > $mwidth) { $mwidth = $width; }
			$mheight += $height + $spacing;
		}

		// Figure out size
		$w = $mwidth + ($spacing * 2);
		$h = $mheight + ($spacing * 2);

		// Adjust the actual canvas for drawing the graph
		$this->ca[2] -= ( $w - ($spacing * 2) );

		// Derive box coordinates
		$x1 = $this->canvas_size[0];
		$x0 = $x1 - $w;
		$y0 = $this->ca[1];
		$y1 = $y0 + $h;

		// Draw nice box
		$this->_RoundedBox( $x0, $y0, $x1, $y1, 2, $this->color['line'] );
		$this->_RoundedBox( $x0+1, $y0+1, $x1-1, $y1-1, 2, $this->color['legendbox'] );

		// Push out the names
		$x = $x0 + $spacing; $y = $y0 + $spacing; $count = 0;
		ImageTtfText( $this->graph, $fontsize, 0, $x, $y + $spacing, $this->color['text'], $this->font, __("Legend:") );
		$y += $spacing;
		foreach ( $this->data AS $k => $v ) {
			$y += $height; // hack?
			ImageTtfText( $this->graph, $fontsize, 0, $x, $y + $spacing, $this->color['data'][$count], $this->font, $k );
			$count++;
		}
	} // end method DrawLegend

	// Method: DrawTitle
	//
	//	Draw graph title in nice box
	//
	// Parameters:
	//
	//	$title - Title text
	//
	function DrawTitle( $title ) {
		// Figure out where this goes
		$spacing = 10;
		$fontsize = 16;
		$x0 = $spacing;
		$x1 = $this->canvas_size[0] - $spacing;
		$y0 = $spacing;
		$y1 = $this->canvas_offset[1] - $spacing;

		// Fake a border by +1/-1...
		$this->_RoundedBox( $x0, $y0, $x1, $y1, $spacing, $this->color['line'] );
		$this->_RoundedBox( $x0+1, $y0+1, $x1-1, $y1-1, $spacing, $this->color['titlebox'] );

		// Determine text positioning
		$bbox = ImageTtfBBox( $fontsize, 0, $this->font, $title );
		$width = $bbox[2] - $bbox[0];
		$height = $bbox[3] - $bbox[7];
		ImageTtfText( $this->graph, $fontsize, 0, ($this->canvas_size[0] / 2) - ($width / 2), (($y1 - $y0) / 2) + ($height / 2) + $spacing, $this->color['text'], $this->font, $title );
	} // end method DrawTitle

	// Method: DataSet
	//
	//	Add data set
	//
	// Parameters:
	//
	//	$name - Name of data set
	//
	//	$values - Array of (key, val)
	//
	function DataSet ( $name, $values ) {
		$this->set_count++;
		$this->data[$name] = $values;
	} // end method DataSet

	// Method: GetDataLimits
	//
	//	Determine numeric boundaries of input data for normalization
	//
	// Parameters:
	//
	//	&$minx - Minimum X value by ref
	//
	//	&$miny - Minimum Y value by ref
	//
	//	&$maxx - Maximum X value by ref
	//
	//	&$maxy - Maximum Y value by ref
	//
	function GetDataLimits ( &$minx, &$miny, &$maxx, &$maxy ) {
		if ($this->set_count == 0) { return false; }
		$minx = $maxx = $miny = $maxy = 0;
		foreach ($this->data AS $d) {
			foreach ($d AS $v) {
				if ($v[0] <= $minx) { $minx = $v[0]; }
				if ($v[0] >= $maxx) { $maxx = $v[0]; }
				if ($v[1] <= $miny) { $miny = $v[1]; }
				if ($v[1] >= $maxy) { $maxy = $v[1]; }
			}
		}

		// Adjust by 10ths, so that we don't push the graph edges
		$maxx += ($maxx / 10);
		$maxy += ($maxy / 10);
	} // end method GetDataLimits

	// Method: Process
	//
	//	Master method to create the graph image in memory
	//
	function Process ( ) {
		$this->DrawTitle( $this->title );
		$this->DrawLegend();
		$this->DrawCanvas();
		$this->DrawData();
	} // end method Process

	// Method: OutputJPEG
	//
	//	Output graph as JPEG and exit
	//
	function OutputJPEG ( ) {
		Header("Content-type: image/jpeg");
		ImageJPEG( $this->graph );
		ImageDestroy( $this->graph );
		die();
	} // end method OutputJPEG

	// Method: OutputPNG
	//
	//	Output graph as PNG and exit
	//
	function OutputPNG ( ) {
		Header("Content-type: image/png");
		ImagePNG( $this->graph );
		ImageDestroy( $this->graph );
		die();
	} // end method OutputPNG

	// Method: _DashedLine
	//
	//	Internal method to produce "dashed" line style
	//
	// Parameters:
	//
	//	$x0 - Starting X coord
	//
	//	$y0 - Starting Y coord
	//
	//	$x1 - Ending X coord
	//
	//	$y1 - Ending Y coord
	//
	//	$fg - GD color reference for foreground color
	//
	//	$bg - GD color reference for background color
	//
	function _DashedLine($x0, $y0, $x1, $y1, $fg, $bg) { 
		$st = array($fg, $fg, $fg, $fg, $bg, $bg, $bg, $bg); 
		ImageSetStyle( $this->graph, $st );
		ImageLine( $this->graph, $x0, $y0, $x1, $y1, IMG_COLOR_STYLED );
	} // end method _DashedLine

	// Method: _RoundedBox
	//
	//	Internal method to draw a "rounded" box
	//
	// Parameters:
	//
	//	$x1 - Upper left corner, X
	//
	//	$y1 - Upper left corner, Y
	//
	//	$x2 - Lower right corner, X
	//
	//	$y2 - Lower right corner, Y
	//
	//	$radius - Radius of rounded part of box
	//
	//	$color - GD color reference for box
	//
	function _RoundedBox ( $x1, $y1, $x2, $y2, $radius, $color ) {
		// draw rectangle without corners
		imagefilledrectangle($this->graph, $x1+$radius, $y1, $x2-$radius, $y2, $color);
		imagefilledrectangle($this->graph, $x1, $y1+$radius, $x2, $y2-$radius, $color);
		// draw circled corners
		imagefilledellipse($this->graph, $x1+$radius, $y1+$radius, $radius*2, $radius*2, $color);
		imagefilledellipse($this->graph, $x2-$radius, $y1+$radius, $radius*2, $radius*2, $color);
		imagefilledellipse($this->graph, $x1+$radius, $y2-$radius, $radius*2, $radius*2, $color);
		imagefilledellipse($this->graph, $x2-$radius, $y2-$radius, $radius*2, $radius*2, $color);
	} // end method _RoundedBox

} // end class GraphNormalize

?>
