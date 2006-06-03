<?php
/*=======================================================================
// File: 	JPGRAPH_LOG.PHP
// Description:	Log scale plot extension for JpGraph
// Created: 	2001-01-08
// Author:	Johan Persson (johanp@aditus.nu)
// Ver:		$Id: jpgraph_log.php,v 1.12 2002/03/31 22:22:52 aditus Exp $
//
// License:	This code is released under QPL
// Copyright (C) 2001,2002 Johan Persson
//========================================================================
*/

//===================================================
// CLASS LogScale
// Description: Logarithmic scale between world and screen
//===================================================
class LogScale extends LinearScale {
//---------------
// CONSTRUCTOR

    // Log scale is specified using the log of min and max
    function LogScale($min,$max,$type="y") {
	$this->LinearScale($min,$max,$type);
	$this->ticks = new LogTicks();
    }

//----------------
// PUBLIC METHODS	

    // Translate between world and screen
    function	Translate($a) {
	if( $a < 0 ) {
	    JpGraphError::Raise("Negative data values can not be used in a log scale.");
	    exit(1);
	}
	if( $a==0 ) $a=1;
	$a=log10($a);
	return floor($this->off + ($a*1.0 - $this->scale[0]) * $this->scale_factor); 
    }

    // Relative translate (don't include offset) usefull when we just want
    // to know the relative position (in pixels) on the axis	
    function RelTranslate($a) {
	if( $a==0 ) $a=1;
	$a=log10($a);
	return ($a*1.0 - $this->scale[0]) * $this->scale_factor; 
    }
		
    // Use bcpow() for increased precision
    function GetMinVal() {
	if( function_exists("bcpow") )
	    return round(bcpow(10,$this->scale[0],15),14);
	else
	    return round(pow(10,$this->scale[0]));
    }
	
    function GetMaxVal() {
	if( function_exists("bcpow") )
	    return round(bcpow(10,$this->scale[1],15),14);
	else
	    return round(pow(10,$this->scale[1]));
    }
	
    // Logarithmic autoscaling is much simplier since we just
    // set the min and max to logs of the min and max values.
    // Note that for log autoscale the "maxstep" the fourth argument
    // isn't used. This is just included to give the method the same
    // signature as the linear counterpart.
    function AutoScale(&$img,$min,$max,$dummy) {
	if( $min==0 ) $min=1;
	assert($max>0);		
	$smin = floor(log10($min));
	$smax = ceil(log10($max));
	$this->Update($img,$smin,$smax);					
    }
//---------------
// PRIVATE METHODS	
} // Class

//===================================================
// CLASS LogTicks
// Description: 
//===================================================
class LogTicks extends Ticks{
//---------------
// CONSTRUCTOR
    function LogTicks() {
    }
//---------------
// PUBLIC METHODS	
    function IsSpecified() {
	return true;
    }
	
    // For log scale it's meaningless to speak about a major step
    // We just return -1 to make the framework happy (specifically
    // StrokeLabels() )
    function GetMajor() {
	return -1;
    }


    function SetXLabelOffset($dummy) {
	// For log scales we dont care about XLabel offset
    }

    // Draw ticks on image "img" using scale "scale". The axis absolute
    // position in the image is specified in pos, i.e. for an x-axis
    // it specifies the absolute y-coord and for Y-ticks it specified the
    // absolute x-position.
    function Stroke(&$img,&$scale,$pos) {
	$start = $scale->GetMinVal();
	$limit = $scale->GetMaxVal();
	$nextMajor = 10*$start;
	$step = $nextMajor / 10.0;
		
		
	$img->SetLineWeight($this->weight);			
		
	if( $scale->type == "y" ) {
	    // member direction specified if the ticks should be on
	    // left or right side.
	    $a=$pos + $this->direction*$this->GetMinTickAbsSize();
	    $a2=$pos + $this->direction*$this->GetMajTickAbsSize();	
			
	    $count=1; 
	    $this->maj_ticks_pos[0]=$scale->Translate($start);
	    $this->maj_ticklabels_pos[0]=$scale->Translate($start);
	    if( $this->supress_first )
		$this->maj_ticks_label[0]="";
	    else
		$this->maj_ticks_label[0]=$start;	
	    $i=1;
	    for($y=$start; $y<=$limit; $y+=$step,++$count  ) {
		$ys=$scale->Translate($y);	
		$this->ticks_pos[]=$ys;
		$this->ticklabels_pos[]=$ys;
		if( $count % 10 == 0 ) {
		    if( $this->majcolor!="" ) $img->PushColor($this->majcolor);
		    $img->Line($pos,$ys,$a2,$ys);
		    if( $this->majcolor!="" ) $img->PopColor();
		    $this->maj_ticks_pos[$i]=$ys;
		    $this->maj_ticklabels_pos[$i]=$ys;
		    $this->maj_ticks_label[$i]=$nextMajor;	
		    ++$i;						
		    $nextMajor *= 10;
		    $step *= 10;	
		    $count=1; 				
		}
		else {
		    if( $this->mincolor!="" ) $img->PushColor($this->mincolor);
		    $img->Line($pos,$ys,$a,$ys);		
		    if( $this->mincolor!="" ) $img->PopCOlor();
		}
	    }		
	}
	else {
	    $a=$pos - $this->direction*$this->GetMinTickAbsSize();
	    $a2=$pos - $this->direction*$this->GetMajTickAbsSize();	
	    $count=1; 
	    $this->maj_ticks_pos[0]=$scale->Translate($start);
	    $this->maj_ticklabels_pos[0]=$scale->Translate($start);
	    if( $this->supress_first )
		$this->maj_ticks_label[0]="";
	    else
		$this->maj_ticks_label[0]=$start;	
	    $i=1;			
	    for($x=$start; $x<=$limit; $x+=$step,++$count  ) {
		$xs=$scale->Translate($x);	
		$this->ticks_pos[]=$xs;
		$this->ticklabels_pos[]=$xs;
		if( $count % 10 == 0 ) {
		    $img->Line($xs,$pos,$xs,$a2);
		    $this->maj_ticks_pos[$i]=$xs;
		    $this->maj_ticklabels_pos[$i]=$xs;
		    $this->maj_ticks_label[$i]=$nextMajor;	
		    ++$i;								
		    $nextMajor *= 10;
		    $step *= 10;	
		    $count=1; 				
		}
		else
		    $img->Line($xs,$pos,$xs,$a);		
	    }		
	}
	return true;
    }
} // Class
/* EOF */
?>