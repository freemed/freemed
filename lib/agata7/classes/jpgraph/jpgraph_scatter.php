<?php 
/*=======================================================================
// File:	JPGRAPH_SCATTER.PHP
// Description: Scatter (and impuls) plot extension for JpGraph
// Created: 	2001-02-11
// Author:	Johan Persson (johanp@aditus.nu)
// Ver:		$Id: jpgraph_scatter.php,v 1.17 2002/04/15 14:23:55 aditus Exp $
//
// License:	This code is released under QPL
// Copyright (C) 2001,2002 Johan Persson
//========================================================================
*/

//===================================================
// CLASS ScatterPlot
// Description: Render X and Y plots
//===================================================
class ScatterPlot extends Plot {
    var $impuls = false;
    var $linkpoints = false, $linkpointweight=1, $linkpointcolor="black";
//---------------
// CONSTRUCTOR
    function ScatterPlot(&$datay,$datax=false) {
	if( (count($datax) != count($datay)) && is_array($datax))
	    JpGraphError::Raise("Scatterplot must have equal number of X and Y points.");
	$this->Plot($datay,$datax);
	$this->mark = new PlotMark();
	$this->mark->SetType(MARK_CIRCLE);
	$this->mark->SetColor($this->color);
    }

//---------------
// PUBLIC METHODS	
    function SetImpuls($f=true) {
	$this->impuls = $f;
    }   

    // Combine the scatter plot points with a line
    function SetLinkPoints($f=true,$lpc="black",$weight=1) {
	$this->linkpoints=$f;
	$this->linkpointcolor=$lpc;
	$this->linkpointweight=$weight;
    }

    function Stroke(&$img,&$xscale,&$yscale) {
	$ymin=$yscale->scale_abs[0];
	if( $yscale->scale[0] < 0 )
	    $yzero=$yscale->Translate(0);
	else
	    $yzero=$yscale->scale_abs[0];
	    
	$this->csimareas = '';
	for( $i=0; $i<$this->numpoints; ++$i ) {
	    if( isset($this->coords[1]) )
		$xt = $xscale->Translate($this->coords[1][$i]);
	    else
		$xt = $xscale->Translate($i);
	    $yt = $yscale->Translate($this->coords[0][$i]);	

	    $this->value->Stroke($img,$this->coords[0][$i],$xt,$yt);

	    if( $this->linkpoints && isset($yt_old) ) {
		$img->SetColor($this->linkpointcolor);
		$img->SetLineWeight($this->linkpointweight);
		$img->Line($xt_old,$yt_old,$xt,$yt);
	    }
	    if( $this->impuls ) {
		$img->SetColor($this->color);
		$img->SetLineWeight($this->weight);
		$img->Line($xt,$yzero,$xt,$yt);
	    }
	
	    if( !empty($this->csimtargets[$i]) ) {
	        $this->mark->SetCSIMTarget($this->csimtargets[$i]);
	        $this->mark->SetCSIMAlt($this->csimalts[$i]);
	        $this->mark->SetCSIMAltVal($this->coords[0][$i]);
	    }
	    $this->mark->Stroke($img,$xt,$yt);	
	    $this->csimareas .= $this->mark->GetCSIMAreas();
	    $xt_old = $xt;
	    $yt_old = $yt;
	}
	//echo $this->csimareas;
    }
	
    // Framework function
    function Legend(&$aGraph) {
	if( $this->legend != "" ) {
	    $aGraph->legend->Add($this->legend,$this->mark->fill_color);		
	}
    }	
	
} // Class
/* EOF */
?>