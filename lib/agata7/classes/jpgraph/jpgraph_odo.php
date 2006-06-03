<?php
/*=======================================================================
// File:	JPGRAPH_ODO.PHP
// Description:	JpGraph Odometer Plot extension
// Created: 	2002-02-14
// Author:	Johan Persson (johanp@aditus.nu)
// Ver:		$Id$
//
// License:	This code is released under QPL
//
// Copyright (C) 2002 Johan Persson. All rights reserved.
//========================================================================
*/

//----------------------------------------------------------------
// The first set of defines specifies some default behavioiur
// of the odometer. You may change these values as you like.
//----------------------------------------------------------------

// Default size if no size is give
DEFINE("ODO_DEFAULT_WIDTH",300);
DEFINE("ODO_DEFAULT_HEIGHT",200);

//----------------------------------------------------------------
// You should NOT, I repeat, NOT  change any of the following 
// constants
//----------------------------------------------------------------

// Style of odometer
DEFINE("ODO_FULL",1); // Full circle
DEFINE("ODO_HALF",2); // Half circle


// Types of needles
DEFINE("NEEDLE_STYLE_SIMPLE",0);  // Straight
DEFINE("NEEDLE_STYLE_STRAIGHT",1);  // Straight
DEFINE("NEEDLE_STYLE_ENDARROW",2);  // Arrowhead
DEFINE("NEEDLE_STYLE_SMALL_TRIANGLE",3);  // Triangle small base
DEFINE("NEEDLE_STYLE_MEDIUM_TRIANGLE",4);  // Triangle wide base
DEFINE("NEEDLE_STYLE_LARGE_TRIANGLE",5);  // Triangle wide base
DEFINE("NEEDLE_STYLE_HUGE_TRIANGLE",6);  // Triangle wide base

// Arrow head styles
// NEEDLE_ARROW_<WIDTH><LENGTH>
// S = Small
// M = Medium
// L = Large
DEFINE("NEEDLE_ARROW_SS",1);  
DEFINE("NEEDLE_ARROW_SM",2);
DEFINE("NEEDLE_ARROW_SL",3);
DEFINE("NEEDLE_ARROW_MS",4);
DEFINE("NEEDLE_ARROW_MM",5);
DEFINE("NEEDLE_ARROW_ML",6);
DEFINE("NEEDLE_ARROW_LS",7);
DEFINE("NEEDLE_ARROW_LM",8);
DEFINE("NEEDLE_ARROW_LL",9);


//===================================================
// CLASS OdoGraph
// Description: Main class to handle odometer graphs
//===================================================
class OdoGraph extends Graph {
    var $iObj=array();
    var $iOdoColor = "lightblue";
    var $caption;

    function OdoGraph($aWidth=-1,$aHeight=-1,$aCachedName="",$aTimeOut=0,$aInline=true) {
	Graph::Graph($aWidth,$aHeight,$aCachedName,$aTimeOut,$aInline);		
	$this->SetColor($this->iOdoColor);
	$this->img->SetMargin(5,5,5,5);
	$this->SetMarginColor("steelblue");
	$this->caption = new Text();
	$this->caption->ParagraphAlign("center");
	$this->subcaption = new Text();
	$this->subcaption->ParagraphAlign("center");
	$this->title->SetFont(FF_FONT1,FS_BOLD);
    }

    // Add a new Odometer to the graph
    function Add($aObject) {
	if( is_array($aObject) ) {
	    for($i=0; $i<count($aObject); ++$i)
		$this->iObj[] = $aObject[$i];
	}
	else
	    $this->iObj[] = $aObject;
    }

    function Stroke($aStrokeFileName="") {
	if( $this->img->img == NULL ) {
	    // If the user didn't specify an image size no
	    // image will yet have been created so we need
	    // to find out a suitable size and create an 
	    // image.
	    $lm=5;$rm=5;$tm=5;$bm=5;			

	    $width = ODO_DEFAULT_WIDTH;
	    $height = ODO_DEFAULT_HEIGHT;
	    $this->img->CreateImgCanvas($width,$height);			
	}
	else {
	    $lm=$this->img->left_margin;
	    $rm=$this->img->right_margin;
	    $tm=$this->img->top_margin;
	    $bm=$this->img->bottom_margin;			
	}

	if( BRAND_TIMING ) $bm += 15;

	if( $this->doshadow ) $rm += $this->shadow_width;
	if( $this->doshadow ) $bm += $this->shadow_width;

	// Calculate the top margin needed for title and subtitle
	if( $this->title->t != "" ) {
	    $tm += 1.2 * $this->title->GetFontHeight($this->img);
	}
	if( $this->subtitle->t != "" ) {
	    $tm += 1.2* $this->subtitle->GetFontHeight($this->img);
	}

	// Calculate the top margin needed for caption
	if( $this->caption->t != "" ) {
	    $bm += 1.2 * $this->caption->GetTextHeight($this->img);
	}

	$this->img->SetMargin($lm,$rm+1,$tm,$bm+1);
	$this->StrokePlotArea();
	$this->StrokeTitles();

	$captiony = $this->img->height - 
	            ($this->doshadow ? $this->shadow_width : 0) - 5;
	$this->caption->Align("center","bottom");
	$this->caption->Center($this->img->left_margin,
	                       $this->img->width-$this->img->right_margin,
                               $captiony);
	$this->caption->Stroke($this->img);

	$this->img->SetMargin(0,0,0,0);
	//Stroke all meters
	$this->img->SetTranslation($lm,$tm);
	$w = $this->img->plotwidth;
	$h = $this->img->plotheight;
	$n = count($this->iObj);
	for($i=0; $i<$n; ++$i) {
	    $this->img->plotheight = $h - $bm - $tm ;
	    $this->img->plotwidth  = $w - $lm - $rm;
	    $this->iObj[$i]->Stroke($this);
	}
	$this->cache->PutAndStream($this->img,$this->cache_name,$this->inline,$aStrokeFileName);
    }
}


//===================================================
// CLASS OdoNeedle
// Description: The needle in the odometer
//===================================================

class OdoNeedle extends LineProperty {
    var $iFillColor="lightblue";
    var $iVal=0;
    var $iLength = 0.6; // Fraction of radius
    var $iStyleParameter1 = -1, $iStyleParameter2 = -1; 
    var $iShow=false;
    var $iLineWeight=1;
    var $iShadowColor="gray:0.7",$iShadow=false, $idxShadow=4, $idyShadow=4;

    function OdoNeedle() {
	$this->iArrowSize = array(
	    3,5, 3,8, 3,15,    // SS, SM, SL
	    4,7, 4,12, 5,20,    // MS, MM, ML
	    8,7, 8,14, 8,24 ); // LS, LM, LL
	$this->iWeight = 4;
	$this->iColor = "navy";
	$this->iStyle = NEEDLE_STYLE_ENDARROW;
	$this->iStyleParameter1 = NEEDLE_ARROW_MM;

    }

    function Set($aVal) {
	$this->iVal = $aVal;
    }

    function SetLineWeight($aWeight) {
	$this->iLineWeight = $aWeight;
    }

    function SetFillColor($aColor) {
	$this->iFillColor = $aColor;
    }

    function SetLength($aLen) {
	$this->iLength = $aLen;
    }
    
    function SetStyle($aStyle, $aStyleParameter1=-1, $aStyleParameter2=-1) {
	$this->iStyle = $aStyle;
	if( $aStyle==NEEDLE_STYLE_ENDARROW && $aStyleParameter1==-1 )
	    $this->iStyleParameter1 = NEEDLE_ARROW_MM;
	else
	    $this->iStyleParameter1 = $aStyleParameter1;
	$this->iStyleParameter2 = $aStyleParameter2;
    }

    function SetShadow($aShadow=true,$aColor="gray:0.7",$aDx=4,$aDy=4) {
	$this->iShadow = $aShadow;
	$this->iShadowColor = $aColor;
	$this->idxShadow = $aDx;
	$this->idyShadow = $aDy;
    }

    function Stroke($img,$aOdometer) {
	if( !$this->iShow ) return; 

	$a = $aOdometer->scale->Translate($this->iVal);
	$r = $aOdometer->iRadius*$this->iLength;
	$xc = $aOdometer->xc ;
	$yc = $aOdometer->yc ;

	// Note: The $yadj parameter in the definiton of the needle shapes below
	// is needed in order for the point of rotation to be in the middle of the
	// needle. Basicall the image class always rotates around (0,0) by
	// calling SetCenter() you can specify where the (0,0) point should 
	// be. 
	switch( $this->iStyle ) {
	    case NEEDLE_STYLE_SIMPLE: // Simple, just a rectangle
		$yadj = $this->iWeight/2;
	        $p = array($xc,$yc-$yadj,$xc+$r,$yc-$yadj,
		           $xc+$r,$yc+$this->iWeight-$yadj,$xc,$yc+$this->iWeight-$yadj);
		break;
	    case NEEDLE_STYLE_STRAIGHT: // Straight - two widths
		// Check if we should use default values?
		if( $this->iStyleParameter1 == -1 )
		    $this->iStyleParameter1 = 0.6;
		if( $this->iStyleParameter2 == -1 )
		    $this->iStyleParameter2 = 0.3;

		$yadj = $this->iWeight/2;
	        $ind = floor($this->iWeight*$this->iStyleParameter2) ;
		$p = array($xc,$yc-$yadj,
		$xc+$r*$this->iStyleParameter1,$yc-$yadj,
		$xc+$r*$this->iStyleParameter1,$yc+$ind-$yadj, $xc+$r,$yc+$ind-$yadj,
		$xc+$r,$yc+$ind+($this->iWeight-2*$ind)-$yadj,
		$xc+$r*$this->iStyleParameter1,$yc+$ind+($this->iWeight-2*$ind)-$yadj,
		$xc+$r*$this->iStyleParameter1,$yc+$this->iWeight-$yadj,
		$xc,$yc+$this->iWeight-$yadj);
	        break;

	    case NEEDLE_STYLE_ENDARROW: // With end arrow
		$arrow_width  = $this->iArrowSize[($this->iStyleParameter1-1)*2];
		$arrow_length = $this->iArrowSize[($this->iStyleParameter1-1)*2+1];
		$yadj = $arrow_width + $this->iWeight/2;

	    $r -= $arrow_length;
		$p = array($xc,$yc+$arrow_width-$yadj,$xc+$r,$yc+$arrow_width-$yadj,
		           $xc+$r,$yc-$yadj,
		           $xc+$r+$arrow_length,$yc+$arrow_width+$this->iWeight/2-$yadj,
		           $xc+$r,$yc+2*$arrow_width+$this->iWeight-$yadj,
		           $xc+$r,$yc+$arrow_width+$this->iWeight-$yadj,
		           $xc,$yc+$arrow_width+$this->iWeight-$yadj);
	    
		break;
	    case NEEDLE_STYLE_SMALL_TRIANGLE: // Triangle small width base
		$base_width = 8;
	    case NEEDLE_STYLE_MEDIUM_TRIANGLE: // Triangle medium width base
		$base_width = isset($base_width)  ? $base_width : 15 ;
	    case NEEDLE_STYLE_LARGE_TRIANGLE: // Triangle medium width base
		$base_width = isset($base_width)  ? $base_width : 25 ;
	    case NEEDLE_STYLE_HUGE_TRIANGLE: // Triangle medium width base
		$base_width = isset($base_width)  ? $base_width : 50 ;
		$yadj = $base_width/2;
	        $p = array($xc,$yc-$yadj,$xc+$r,$yc+$base_width/2-$yadj,$xc,$yc+$base_width-$yadj);
		break;

	    default:
		JpGraphError::Raise("<b>JpGraph Error:</b> Unknown needle style.");
		break;
	}

	// Move the (0,0) point ti where we want the rotation point
	
	$old_origin = $img->SetCenter($xc,$yc);
	$a = - $a * 180 / M_PI;
	$old_a = $img->SetAngle($a);

	if( $this->iShadow ) {
	    $img->PushColor($this->iShadowColor);
	    $oldt = array($img->transx,$img->transy);
	    $img->SetTranslation($oldt[0]+$this->idxShadow, $oldt[1]+$this->idyShadow);
	    $img->FilledPolygon($p);
	    $img->PopColor();
	    $img->SetTranslation($oldt[0], $oldt[1]);
	}

	$img->PushColor($this->iFillColor);
	$img->FilledPolygon($p);
	$img->PopColor();
	$img->PushColor($this->iColor);
	$img->SetLineWeight($this->iLineWeight);
	$img->Polygon($p);
	$img->PopColor();

	$img->SetCenter($old_origin[0],$old_origin[1]);
	$img->SetAngle($old_a);
    }
}

//===================================================
// CLASS OdoScale
// Description: The scale for odometer
//===================================================

class OdoScale {
    var $label=null;
    var $iMin=0,$iMax=100;
    var $iStartAngle,$iEndAngle;
    var $iMinTick=25,$iLabelInterval=1;
    var $iTickLength=0.05;  // Fraction of radius
    var $iColor = "black"; // Tickmark color
    var $iTickWeight=1;
    var $iShow=true;
    var $iFormatStr = "%d";
    var $iLabelPosition=0.8; 

    function OdoScale($aStartAngle,$aEndAngle) {
	$this->label = new Text();
	$this->iStartAngle = $aStartAngle * M_PI/180;
	$this->iEndAngle = $aEndAngle * M_PI/180;
    }

    function SetAngle($aStart,$aEnd) {
	$this->iStartAngle = $aStart * M_PI/180;
	$this->iEndAngle = $aEnd * M_PI/180;
    }

    function SetLabelFormat($aFormat) {
	$this->iFormatStr = $aFormat;
    }

    function SetTickWeight($aWeight) {
	$this->iTickWeight = $aWeight;
    }

    function SetTickColor($aColor) {
	$this->iColor = $aColor;
    }

    function SetTickLength($aLength) {
	$this->iTickLength = $aLength;
    }

    function Set($aMin,$aMax) {
	$this->iMin = $aMin;
	$this->iMax = $aMax;
    }

    function SetTicks($aMinTick,$aLabelInterval=1) {
	$this->iLabelInterval = $aLabelInterval;
	$this->iMinTick = $aMinTick;
    }

    function SetLabelPos($aPos) {
	$this->iLabelPostion = $aPos;
    }

    function Translate($aVal) {
	if( $aVal > $this->iMax || $aVal < $this->iMin )
	    JpGraphError::Raise("<b>JpGraph Error:</b> Value for odometer is outside specified scale.");
	$a = $this->iStartAngle + 
	    ($aVal-$this->iMin)/($this->iMax-$this->iMin)*($this->iEndAngle-$this->iStartAngle);
	$a = 3/2*M_PI - $a ;
	return $a < 0 ?  $a + 2*M_PI : $a ;
    }

    function Show($aShow=true) {
	$this->iShow = $aShow;
    }

    function Stroke(&$img,&$aOdometer) {
	if( !$this->iShow ) return;
	$n = ($this->iMax - $this->iMin)/$this->iMinTick;
	$r = $aOdometer->iRadius - $aOdometer->iBorderWidth;
	$tick = $this->iMin;
	$img->SetLineWeight($this->iTickWeight);
	$img->PushColor($this->iColor);
	for($i=0; $i<=$n; ++$i) {
	    $a = $this->Translate($tick);
	    if( $i % $this->iLabelInterval == 0 ) {
		$p = array($aOdometer->xc + round($r*cos($a)*0.99),
		    $aOdometer->yc - round($r*sin($a)),
		    $aOdometer->xc + round($r*(1-$this->iTickLength*1.5)*cos($a)),
		    $aOdometer->yc - round($r*(1-$this->iTickLength*1.5)*sin($a)));
		
		$lx = $aOdometer->xc + round($r*$this->iLabelPosition*cos($a));
		$ly = $aOdometer->yc - round($r*$this->iLabelPosition*sin($a));
		
		$s = sprintf($this->iFormatStr,$tick);
		$this->label->Set($s);
		if( ($i==0 || $i==$n) && $aOdometer->iStyle==ODO_HALF ) {
		    $this->label->Pos($lx,$ly-2,"center","bottom");
		}
		else
		    $this->label->Pos($lx,$ly,"center","center");
		$this->label->Stroke($img);		    
	    }
	    else {
		$p = array($aOdometer->xc + round($aOdometer->iRadius*cos($a)),
		    $aOdometer->yc - round($r*sin($a)),
		    $aOdometer->xc + round($r*(1-$this->iTickLength)*cos($a)),
		    $aOdometer->yc - round($r*(1-$this->iTickLength)*sin($a)));
	    }
	    $img->Line($p[0],$p[1],$p[2],$p[3]);
	    $tick += $this->iMinTick;
	}
	$img->PopColor();
    }
} 

//===================================================
// CLASS OdometerLabel
// Description: Text on odometer
//===================================================

class OdometerLabel extends Text {
    var $iVPos=0.2;
    function SetVPos($aPos) {
	$this->iVPos = $aPos;
    }
}

//===================================================
// CLASS Odometer
// Description: Main class to draw a odometer
//===================================================
class Odometer {
    var $scale;
    var $needle,$needle2,$needle3,$needle4;
    var $iStyle;
    var $iRadius=0.3;
    var $xc,$yc;
    var $iFillColor = "lightgray:1.15", $iColor = "navy";
    var $iBorderWidth=1;
    var $iInd, $iIndIdx=0;
    var $iCenterAreaWidth = 0;
    var $label;
    var $iBase = true, $iBaseWidth=0.12;
    var $iBaseColor1="navy",$iBaseColor2="steelblue",$iBaseColor3="white";
    var $iMargin=5;
    var $caption,$iCaptionMargin=0;

    function Odometer($aStyle=ODO_HALF) {
	// Set default position
	$this->xc = 0.5;
	if( $aStyle == ODO_FULL ) {
	    $this->scale = new OdoScale(40,320);
	    $this->yc = 0.5;
	    $this->iRadius = 0.5;
	}
	else {
	    $this->scale = new OdoScale(90,270);
	    $this->yc = 0;
	    $this->iRadius = 1;
	}
	$this->iStyle = $aStyle;

	// Only the first needle is shown by default
	$this->needle = new OdoNeedle();
	$this->needle->Show(true);

	$this->needle2 = new OdoNeedle();
	$this->needle3 = new OdoNeedle();
	$this->needle4 = new OdoNeedle();
	$this->iInd = null;
	$this->label = new OdometerLabel();
	$this->caption = new Text();
	$this->caption->ParagraphAlign("center");
	$this->caption->SetFont(FF_FONT2,FS_NORMAL);
    }

    function SetMargin($aMargin) {
	$this->iMargin = $aMargin;
    }

    function SetBase($aShowBase,$aWidth=0.1,$aColor1="navy",$aColor2="steelblue",$aColor3="white") {
	$this->iBase = $aShowBase;
	$this->iBaseColor1 = $aColor1;
	$this->iBaseColor2 = $aColor2;
	$this->iBaseColor3 = $aColor3;
	$this->iBaseWidth = $aWidth;
    }

    // Dummy method to make odometers have the same signature as the
    // layout classes since odometer is "leaf" classes in the hierarchy
    function LayoutSize() {
	return 1;
    }

    function SetCenterAreaWidth($aWidth) {
	$this->iCenterAreaWidth = $aWidth;
    }

    function SetPos($aXc,$aYc) {
	$this->xc = $aXc;
	$this->yc = $aYc;
    }

    // Set size. A value in the range 0 to 1 is interpretated as 
    // fraction of min(width,heigth) while a value > 1 is interpretated
    // as absolute size
    function SetSize($aRadius) {
	$this->iRadius = $aRadius;
    }

    function AddIndication($aStart,$aEnd,$aColor) {
	$this->iInd[$this->iIndIdx++] = array($aStart,$aEnd,$aColor);
    }

    function SetColor($aColor) {
	$this->iFillColor = $aColor;
    }

    function SetBorder($aColor,$aWidth=1) {
	$this->iColor = $aColor;
	$this->iBorderWidth = $aWidth;
    }

    function FilledCircle(&$img,$aXc,$aYc,$aRadius,$aFillColor) {
 	if( $this->iStyle == ODO_FULL ) {
	    $s = 0; $e = 360;
	}
	else {
	    $s = 180; $e = 360;
	}
	$img->PushColor($aFillColor);
	$img->FilledArc($aXc,$aYc,$aRadius*2,$aRadius*2,$s,$e);
	$img->PopColor();	
    }

    // Stroke the outline of the odometer
    function StrokeFascia($img) {
	$r = $this->iRadius;

	// If the border width > 1 we have no choice but to
	// draw to filled circles since GD 1.x at does not support
	// a width for a circle. For the special case with a border
	// of width==1 it looks aestethically better to just draw a 
	// normal circle.
	if( $this->iBorderWidth > 1 ) {
	    $this->FilledCircle($img,
	    $this->xc,$this->yc,$r,
	    $this->iColor);
	    $this->FilledCircle($img,
	    $this->xc,$this->yc,$r-$this->iBorderWidth,
	    $this->iFillColor);
	}
	else {
	    $this->FilledCircle($img,$this->xc,$this->yc,$r,$this->iFillColor);
	}
	$doarcborder = $this->iBorderWidth == 1 ;

	// Stroke colored indicator band
	$n = count($this->iInd);
	$r = $this->iRadius - ($this->iBorderWidth == 1 ? 0 : $this->iBorderWidth);
	for( $i=0; $i<$n; ++$i) {
	    $ind = $this->iInd[$i];
	    $as = 360-$this->scale->Translate($ind[0])*180/M_PI;
	    $ae = 360-$this->scale->Translate($ind[1])*180/M_PI;
	    $img->PushColor($ind[2]);
	    $img->FilledArc($this->xc,$this->yc,$r*2,$r*2,$as,$ae);
	    $img->PopColor();	
	}
        $this->FilledCircle($img,
	                    $this->xc,$this->yc,$this->iCenterAreaWidth*$this->iRadius,
	                    $this->iFillColor); 
	if( $doarcborder ) 
	    $img->Arc($this->xc,$this->yc, 2*$r, 2*$r, $this->iStyle==ODO_HALF ? 180 : 0 , 360);

	// Finally draw bottom line if ODO_HALF
	if( $this->iStyle == ODO_HALF && $this->iBorderWidth > 0 ) {
	    $img->SetLineWeight($this->iBorderWidth);
	    $img->PushColor($this->iColor);
	    $img->Line($this->xc-$this->iRadius,$this->yc,$this->xc+$this->iRadius,$this->yc);
	    $img->PopColor();
	}
    }

    function Stroke($graph) {
	$img = $graph->img;
	// Adjust center position if it's specified as fraction of plot height/width
	$adj = 0; //$graph->doshadow ? $graph->shadow_width : 0;
	$boxadj = 0; //$graph->doframe ? $graph->frame_weight : 0 ;
	$this->xc = $this->xc <= 1 ? floor($img->plotwidth * $this->xc) : 
	    $this->xc ;

	// We only do automatic adjust of the Y-coordinate if the position
	// is given as fractions
	$doautoadjust = ($this->yc < 1) ? 1 : 0 ;

	$this->yc = $this->yc <= 1 ? floor($img->plotheight * (1-$this->yc)) : 
	    $this->yc ;
	if( $this->iStyle == ODO_HALF ) {
	    $this->yc -= $this->iBorderWidth + $this->iMargin;
	    $this->iRadius = $this->iRadius <= 1 ? 
		 min(floor($this->iRadius*($img->plotwidth/2)),
		     floor($this->iRadius*$img->plotheight)) - 2*$this->iMargin : 
	         $this->iRadius;
	    $this->iRadius -= $this->iBorderWidth ; 
	}	
	else {
	    $this->iRadius = $this->iRadius <= 1 ? 
	                 floor($this->iRadius*min($img->plotwidth,$img->plotheight)) - $this->iMargin :  
	                 $this->iRadius;
	}

	// Adjust position and size for a potential odometer caption
	$capmarg = 0;	
	if( $this->caption->t != "" )
	    $capmarg = 0.8 * $this->caption->GetTextHeight($img);
	$this->yc -= $doautoadjust * $capmarg ;
	$this->iRadius -= $doautoadjust * $capmarg;
	$this->caption->Align("center","top");
	$this->caption->Stroke($img,$this->xc,$this->yc+$this->iCaptionMargin);

	$this->StrokeFascia($img);
	$this->scale->Stroke($img,$this);

	// Display the label (legend) in the middle of the plot
	if( $this->iStyle == ODO_FULL ) 
	    $this->label->Pos($this->xc, $this->yc + $this->iRadius*$this->label->iVPos,
	                      "center","bottom");
	else
	    $this->label->Pos($this->xc, $this->yc - $this->iRadius*$this->label->iVPos,
                              "center","bottom");
	$this->label->Stroke($img);

	// Stroke all needles. An odometer may have up to 4 indicator 
	// needles.
	$this->needle->Stroke($img,$this);
	$this->needle2->Stroke($img,$this);
	$this->needle3->Stroke($img,$this);
	$this->needle4->Stroke($img,$this);

	// Should the circular base of the indicator needle be displayed
	if( $this->iBase ) {
	    $r = $this->iRadius*$this->iBaseWidth;
	    $r = $r < 4 ? 4 : $r;
	    $r2 = $r > 10 ? 2 : 1 ;
	    $this->FilledCircle($img,$this->xc,$this->yc,$r,$this->iBaseColor1);
	    $this->FilledCircle($img,$this->xc,$this->yc,$r-2,$this->iBaseColor2);
	    $this->FilledCircle($img,$this->xc,$this->yc,$r2,$this->iBaseColor3);
	}
    }
}
    
//===================================================
// CLASS LayoutVert
// Description: Layout class which orders its objects
// vertically
//===================================================
class LayoutVert {
    var $iObj;

    function LayoutVert($aObjArr) {
	if( !is_array($aObjArr) )
	    $aObjArr = array($aObjArr);
	$this->iObj = $aObjArr;
    }

    function LayoutSize() {
	return count($this->iObj);
    }

    function Stroke($graph) {
	$img = $graph->img;
	$n = count($this->iObj);

	$s = 0;
	for($i=0; $i<$n; ++$i) {
	    $s += 1/$this->iObj[$i]->LayoutSize();
	}
	$d = 1/$s * $graph->img->plotheight ;

	$otx = $graph->img->transx;
	$oty = $graph->img->transy;
	$h = $graph->img->plotheight;
	$w = $graph->img->plotwidth;
	$accheight = 0;
	for($i=0; $i<$n; ++$i ) {
	    $graph->img->SetTranslation($otx,$oty+$accheight);
	    $accheight += $d / $this->iObj[$i]->LayoutSize();
	    $graph->img->plotheight = $d / $this->iObj[$i]->LayoutSize();
	    $this->iObj[$i]->Stroke($graph);
	    $graph->img->plotheight = $h;
	    $graph->img->plotwidth = $w;
	}
    }
}

//===================================================
// CLASS LayoutHor
// Description: Layout class which orders its objects
// horizontally
//===================================================
class LayoutHor {
    var $iObj;

    function LayoutHor($aObjArr) {
	if( !is_array($aObjArr) )
	    $aObjArr = array($aObjArr);
	$this->iObj = $aObjArr;
    }

    function LayoutSize() {
	return count($this->iObj);
    }

    function Stroke($graph) {
	$img = $graph->img;
	$n = count($this->iObj);


	$s = 0;
	for($i=0; $i<$n; ++$i) {
	    $s += 1/$this->iObj[$i]->LayoutSize();
	}
	$d = 1/$s * $graph->img->plotwidth ;

	$otx = $graph->img->transx;
	$oty = $graph->img->transy;
	$h = $graph->img->plotheight;
	$w = $graph->img->plotwidth;
	$accwidth = 0;
	for($i=0; $i<$n; ++$i ) {
	    $graph->img->SetTranslation($otx+$accwidth,$oty);
	    $accwidth += $d / $this->iObj[$i]->LayoutSize();
	    $graph->img->plotwidth = $d / $this->iObj[$i]->LayoutSize();
	    $this->iObj[$i]->Stroke($graph);
	    $graph->img->plotheight = $h;
	    $graph->img->plotwidth = $w;
	}
    }
}

// <EOF>
?>