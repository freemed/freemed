<?php
/*=======================================================================
// File: 	JPGRAPH_LINE.PHP
// Description:	Line plot extension for JpGraph
// Created: 	2001-01-08
// Author:	Johan Persson (johanp@aditus.nu)
// Ver:		$Id: jpgraph_line.php,v 1.22 2002/04/17 09:31:46 aditus Exp $
//
// License:	This code is released under QPL
// Copyright (C) 2001,2002 Johan Persson
//========================================================================
*/

// constants for the (filled) area
DEFINE("LP_AREA_FILLED", true);
DEFINE("LP_AREA_NOT_FILLED", false);
DEFINE("LP_AREA_BORDER",false);
DEFINE("LP_AREA_NO_BORDER",true);

//===================================================
// CLASS LinePlot
// Description: 
//===================================================
class LinePlot extends Plot{
    var $filled=false;
    var $fill_color;
    var $mark=null;
    var $step_style=false, $center=false;
    var $line_style=1;	// Default to solid
    var $filledAreas = array(); // array of arrays(with min,max,col,filled in them)


//---------------
// CONSTRUCTOR
    function LinePlot(&$datay,$datax=false) {
	$this->Plot($datay,$datax);
	$this->mark = new PlotMark();
	$this->mark->SetColor($this->color);
    }
//---------------
// PUBLIC METHODS	

    // Set style, filled or open
    function SetFilled($f=true) {
	$this->filled=$f;
    }
	
    function SetStyle($s) {
	$this->line_style=$s;
    }
	
    function SetStepStyle($f=true) {
	$this->step_style = $f;
    }
	
    function SetColor($c) {
	parent::SetColor($c);
	$this->mark->SetColor($this->color);
    }
	
    function SetFillColor($c,$f=true) {
	$this->fill_color=$c;
	$this->filled=$f;
    }
	
    function Legend(&$graph) {
	if( $this->legend!="" ) {
	    if( $this->filled ) {
		$graph->legend->Add($this->legend,
		$this->fill_color,$this->mark);
	    } else {
		$graph->legend->Add($this->legend,
		$this->color,$this->mark,$this->line_style);
	    }
	}	
    }
	
    function SetCenter($c=true) {
	$this->center=$c;
	}	

    function AddArea($aMin=0,$aMax=0,$aFilled=LP_AREA_NOT_FILLED,$aColor="gray9",$aBorder=LP_AREA_BORDER) {
      if($aMin > $aMax) {
	// swap
	$tmp = $aMin;
	$aMin = $aMax;
	$aMax = $tmp;
      } 
      $this->filledAreas[] = array($aMin,$aMax,$aColor,$aFilled,$aBorder);
    }
	
    // Gets called before any axis are stroked
    function PreStrokeAdjust(&$graph) {
	if( $this->center ) {
	    ++$this->numpoints;
	    $a=0.5; $b=0.5;
	} else {
	    $a=0; $b=0;
	}
	$graph->xaxis->scale->ticks->SetXLabelOffset($a);
	$graph->SetTextScaleOff($b);						
	$graph->xaxis->scale->ticks->SupressMinorTickMarks();
    }
	
    function Stroke(&$img,&$xscale,&$yscale) {
	$numpoints=count($this->coords[0]);
	if( isset($this->coords[1]) ) {
	    if( count($this->coords[1])!=$numpoints )
		JpGraphError::Raise("Number of X and Y points are not equal. Number of X-points:".count($this->coords[1])." Number of Y-points:$numpoints");
	    else
		$exist_x = true;
	}
	else 
	    $exist_x = false;

	if( $exist_x )
	    $xs=$this->coords[1][0];
	else
	    $xs=0;

	$img->SetStartPoint($xscale->Translate($xs),
	                    $yscale->Translate($this->coords[0][0]));
		
	if( $this->filled ) {
	    $cord[] = $xscale->Translate($xs);
	    $cord[] = $yscale->Translate($yscale->GetMinVal());
	}
	$xt = $xscale->Translate($xs);
	$yt = $yscale->Translate($this->coords[0][0]);
	$cord[] = $xt;
	$cord[] = $yt;
	$yt_old = $yt;

	$this->value->Stroke($img,$this->coords[0][0],$xt,$yt);

	$img->SetColor($this->color);
	$img->SetLineWeight($this->weight);	
	$img->SetLineStyle($this->line_style);
	for( $pnts=1; $pnts<$numpoints; ++$pnts) {
	    if( $exist_x ) $x=$this->coords[1][$pnts];
	    else $x=$pnts;
	    $xt = $xscale->Translate($x);
	    $yt = $yscale->Translate($this->coords[0][$pnts]);
	    
	    if( $this->step_style ) {
		$img->StyleLineTo($xt,$yt_old);
		$img->StyleLineTo($xt,$yt);

		$cord[] = $xt;
	    $cord[] = $yt_old;
	
		$cord[] = $xt;
	    $cord[] = $yt;

	    }
	    else {

		$cord[] = $xt;
	    $cord[] = $yt;
	    		    	
		$y=$this->coords[0][$pnts];
		if( is_numeric($y) || (is_string($y) && $y != "-") ) { 		 			
		    $tmp1=$this->coords[0][$pnts];
		    $tmp2=$this->coords[0][$pnts-1]; 		 			
		    if( is_numeric($tmp1)  && (is_numeric($tmp2) || $tmp2=="-" ) ) { 
			$img->StyleLineTo($xt,$yt);
		    } 
		    else {
			$img->SetStartPoint($xt,$yt);
		    }
		}
	    }
	    $yt_old = $yt;

	    $this->StrokeDataValue($img,$this->coords[0][$pnts],$xt,$yt);

	}	
	if( $this->filled ) {
	    $cord[] = $xt;
	    $cord[] = $yscale->Translate($yscale->GetMinVal());					
	    $img->SetColor($this->fill_color);	
	    $img->FilledPolygon($cord);
	    $img->SetColor($this->color);
	    $img->Polygon($cord);
	}
	
	if(!empty($this->filledAreas)) {

	  $minY = $yscale->Translate($yscale->GetMinVal());
	  $factor = ($this->step_style ? 4 : 2);

	  for($i = 0; $i < sizeof($this->filledAreas); ++$i) {
	    // go through all filled area elements ordered by insertion
	    // fill polygon array
	    $areaCoords[] = $cord[$this->filledAreas[$i][0] * $factor];
	    $areaCoords[] = $minY;

	    $areaCoords =
	      array_merge($areaCoords,
			  array_slice($cord,
				      $this->filledAreas[$i][0] * $factor,
				      ($this->filledAreas[$i][1] - $this->filledAreas[$i][0] + ($this->step_style ? 0 : 1))  * $factor));
	    $areaCoords[] = $areaCoords[sizeof($areaCoords)-2]; // last x
	    $areaCoords[] = $minY; // last y
	    
	    if($this->filledAreas[$i][3]) {
	      $img->SetColor($this->filledAreas[$i][2]);
	      $img->FilledPolygon($areaCoords);
	      $img->SetColor($this->color);
	    }
	    
	    $img->Polygon($areaCoords);
	    $areaCoords = array();
	  }
	}	

	$adjust=0;
	if( $this->filled ) $adjust=2;
	$factor = 1;
	if( $this->step_style ) $factor = 2;
	$this->csimareas="";
	for($i=$adjust; $i<count($cord)/$factor-$adjust; $i+=2) {
	  if( is_numeric($this->coords[0][($i-$adjust)/2]) ) {
	    $xt=$cord[$i*$factor];
	    $yt=$cord[$i*$factor+1];
            if( !empty($this->csimtargets[$i]) ) {
	      $this->mark->SetCSIMTarget($this->csimtargets[$i]);
	      $this->mark->SetCSIMAlt($this->csimalts[$i]);
	      $this->mark->SetCSIMAltVal($this->coords[0][$i]);
	    }
	    $this->mark->Stroke($img,$xt,$yt);	
	    $this->csimareas .= $this->mark->GetCSIMAreas();
	    
	    $this->mark->Stroke($img,$xt,$yt);
	  }
	}
    }
} // Class


//===================================================
// CLASS AccLinePlot
// Description: 
//===================================================
class AccLinePlot extends Plot {
    var $plots=null,$nbrplots=0,$numpoints=0;
//---------------
// CONSTRUCTOR
    function AccLinePlot($plots) {
        $this->plots = $plots;
	$this->nbrplots = count($plots);
	$this->numpoints = $plots[0]->numpoints;		
    }

//---------------
// PUBLIC METHODS	
    function Legend(&$graph) {
	foreach( $this->plots as $p )
	    $p->Legend($graph);
    }
	
    function Max() {
	$accymax=0;
	list($xmax,$dummy) = $this->plots[0]->Max();
	foreach($this->plots as $p) {
	    list($xm,$ym) = $p->Max();
	    $xmax = max($xmax,$xm);
	    $accymax += $ym;
	}
	return array($xmax,$accymax);
    }

    function Min() {
	list($xmin,$ymin)=$this->plots[0]->Min();
	foreach( $this->plots as $p ) {
	    list($xm,$ym)=$p->Min();
	    $xmin=Min($xmin,$xm);
	    $ymin=Min($ymin,$ym);
	}
	return array($xmin,$ymin);	
    }

    // To avoid duplicate of line drawing code here we just
    // change the y-values for each plot and then restore it
    // after we have made the stroke. We must do this copy since
    // it wouldn't be possible to create an acc line plot
    // with the same graphs, i.e AccLinePlot(array($pl,$pl,$pl));
    // since this method would have a side effect.
    function Stroke(&$img,&$xscale,&$yscale) {
	$img->SetLineWeight($this->weight);
	// Allocate array
	$coords[$this->nbrplots][$this->numpoints]=0;
	for($i=0; $i<$this->numpoints; $i++) {
	    $coords[0][$i]=$this->plots[0]->coords[0][$i]; 
	    $accy=$coords[0][$i];
	    for($j=1; $j<$this->nbrplots; ++$j ) {
		$coords[$j][$i] = $this->plots[$j]->coords[0][$i]+$accy; 
		$accy = $coords[$j][$i];
	    }
	}
	for($j=$this->nbrplots-1; $j>=0; --$j) {
	    $p=$this->plots[$j];
	    for( $i=0; $i<$this->numpoints; ++$i) {
		$tmp[$i]=$p->coords[0][$i];
		$p->coords[0][$i]=$coords[$j][$i];
	    }
	    $p->Stroke($img,$xscale,$yscale);
	    for( $i=0; $i<$this->numpoints; ++$i) 
		$p->coords[0][$i]=$tmp[$i];
	    $p->coords[0][]=$tmp;
	}
    }
} // Class


/* EOF */
?>
