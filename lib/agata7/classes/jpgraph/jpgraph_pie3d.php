<?php
/*=======================================================================
// File:	JPGRAPH_PIE3D.PHP
// Description: 3D Pie plot extension for JpGraph
// Created: 	2001-03-24
// Author:	Johan Persson (johanp@aditus.nu)
// Ver:		$Id: jpgraph_pie3d.php,v 1.24 2002/04/08 08:06:11 aditus Exp $
//
// License:	This code is released under QPL
// Copyright (C) 2001,2002 Johan Persson
//========================================================================
*/


// Debug print
function dbgp($str) {
//    echo $str;
}

//===================================================
// CLASS PiePlot3D
// Description: Plots a 3D pie with a specified projection 
// angle between 20 and 70 degrees.
//===================================================
class PiePlot3D extends PiePlot {
    var $labelhintcolor="red",$showlabelhint=true,$labelmargin=0.30;
    var $angle=35;	
    var $edgecolor="", $edgeweight=1;
	
//---------------
// CONSTRUCTOR
    function PiePlot3d(&$data) {
	$this->radius = 0.5;
	$this->data = $data;
	$this->title = new Text("");
	$this->title->SetFont(FF_FONT1,FS_BOLD);
	$this->value = new DisplayValue();
    }

//---------------
// PUBLIC METHODS	
	
    // Should the slices be separated by a line? If color is specified as "" no line
    // will be used to separate pie slices.
    function SetEdge($aColor,$aWeight=1) {
	$this->edgecolor = $aColor;
	$this->edgeweight = $aWeight;
    }

    // Specify projection angle for 3D in degrees
    // Must be between 20 and 70 degrees
    function SetAngle($a) {
	if( $a<5 || $a>90 )
	    JpGraphError::Raise("PiePlot3D::SetAngle() 3D Pie projection angle must be between 5 and 85 degrees.");
	else
	    $this->angle = $a;
    }

    function AddSliceToCSIM($i,$xc,$yc,$height,$width,$thick,$sa,$ea) {  //Slice number, ellipse centre (x,y), height, width, start angle, end angle

	$sa *= M_PI/180;
	$ea *= M_PI/180;

	//add coordinates of the centre to the map
	$coords = "$xc, $yc";

	//add coordinates of the first point on the arc to the map
	$xp = floor($width*cos($sa)/2+$xc);
	$yp = floor($yc-$height*sin($sa)/2);
	$coords.= ", $xp, $yp";

	//If on the front half, add the thickness offset
	if ($sa >= M_PI && $sa <= 2*M_PI*1.01) {
	    $yp = $yp+$thick;
	    $coords.= ", $xp, $yp";
	}
		
	//add coordinates every 0.2 radians
	$a=$sa+0.2;
	while ($a<$ea) {
	    $xp = floor($width*cos($a)/2+$xc);
	    if ($a >= M_PI && $a <= 2*M_PI*1.01) {
		$yp = floor($yc-($height*sin($a)/2)+$thick);
	    } else {
		$yp = floor($yc-$height*sin($a)/2);
	    }
	    $coords.= ", $xp, $yp";
	    $a += 0.2;
	}
		
	//Add the last point on the arc
	$xp = floor($width*cos($ea)/2+$xc);
	$yp = floor($yc-$height*sin($ea)/2);


	if ($ea >= M_PI && $ea <= 2*M_PI*1.01) {
	    $coords.= ", $xp, ".floor($yp+$thick);
	}
	$coords.= ", $xp, $yp";
	if( !empty($this->csimalts[$i]) ) {										
	    $tmp=sprintf($this->csimalts[$i],$this->data[$i]);
	    $alt="alt=\"$tmp\" title=\"$tmp\"";
	}
	if( !empty($this->csimtargets[$i]) )
	    $this->csimareas .= "<area shape=\"poly\" coords=\"$coords\" href=\"".$this->csimtargets[$i]."\" $alt>\r\n";
    }

	
    // Distance from the pie to the labels
    function SetLabelMargin($m) {
	assert($m>0 && $m<1);
	$this->labelmargin=$m;
    }
	
    // Show a thin line from the pie to the label for a specific slice
    function ShowLabelHint($f=true) {
	$this->showlabelhint=$f;
    }
	
    // Set color of hint line to label for each slice
    function SetLabelHintColor($c) {
	$this->labelhintcolor=$c;
    }

// Normalize Angle between 0-360
    function NormAngle($a) {
	// Normalize anle to 0 to 2M_PI
	// 
	if( $a > 0 ) {
	    while($a > 360) $a -= 360;
	}
	else {
	    while($a < 0) $a += 360;
	}
	if( $a < 0 )
	    $a = 360 + $a;

	if( $a == 360 ) $a=0;
	return $a;
    }


// Draw one 3D pie slice at position ($xc,$yc) with height $z
    function Pie3DSlice($img,$xc,$yc,$w,$h,$sa,$ea,$z,$fillcolor,
    $shadow=0.65,$edgecolor="",$arccolor="") {

	dbgp( "s=$sa, e=$ea<br>\n" );

	$img->SetColor($fillcolor.":".$shadow);
	for( $i=0; $i<$z; ++$i ) {
	    $img->CakeSlice($xc,$yc+$z-$i,$w,$h,360-$ea,360-$sa,$fillcolor.":".$shadow,"",3500);
	}
	if( $edgecolor == "" )
	    $img->SetColor($fillcolor);
	else
	    $img->SetColor($edgecolor);
	$img->CakeSlice($xc,$yc+$z-$i,$w,$h,360-$ea,360-$sa,$fillcolor,$edgecolor ,2500);

    }
    
// Draw a 3D Pie
    function Pie3D($img,$data,$colors,$xc,$yc,$d,$angle,$z,
		   $shadow=0.65,$startangle=0,$edgecolor="",$edgeweight=2) {

	//---------------------------------------------------------------------------
	// As usual the algorithm get more complicated than I originally
	// envisioned. I believe that this is as simple as it is possible
	// to do it with the features I want. It's a good exercise to start
	// thinking on how to do this to convince your self that all this
	// is really needed for the general case.
	//
	// The algorithm two draw 3D pies without "real 3D" is done in
	// two steps.
	// First imagine the pie cut in half through a thought line between
	// 12'a clock and 6'a clock. It now easy to imagine that we can plot 
	// the individual slices for each half by starting with the topmost
	// pie slice and continue down to 6'a clock.
	// 
	// In the algortithm this is done in three principal steps
	// Step 1. Do the knife cut to ensure by splitting slices that extends 
	// over the cut line. This is done by splitting the original slices into
	// upto 3 subslices.
	// Step 2. Find the top slice for each half
	// Step 3. Draw the slices from top to bottom
	//
	// The thing that slightly complicates this scheme with all the
	// angle comparisons below is that we can have an arbitrary start
	// angle so we must take into account the different equivalence classes.
	// For the same reason we must walk through the angle array in a 
	// modulo fashion.
	//
	// Limitations of algorithm: 
	// * A small exploded slice which crosses the 270 degree point
	//   will get slightly nagged close to the center due to the fact that
	//   we print the slices in Z-order and that the slice left part
	//   get printed first and might get slightly nagged by a larger
	//   slice on the right side just before the right part of the small
	//   slice. Not a major problem though. 
	//---------------------------------------------------------------------------

    
	// Determine the height of the ellippse which gives an
	// indication of the inclination angle
	$h = ($angle/90.0)*$d;
	$sum = 0;
	for($i=0; $i<count($data); ++$i ) {
	    $sum += $data[$i];
	}
	
	// Special optimization
	if( $sum==0 ) return;

	// Setup the start
	$accsum = 0;
	$a = $startangle;
	$a = $this->NormAngle($a);

	// 
	// Step 1 . Split all slices that crosses 90 or 270
	//
	$idx=0;
	$adjexplode=array(); 
	for($i=0; $i<count($data); ++$i, ++$idx ) {
	    $da = $data[$i]/$sum * 360;

	    if( empty($this->explode_radius[$i]) )
		$this->explode_radius[$i]=0;

	    $la = $a + $da/2;
	    $explode = array( $xc + $this->explode_radius[$i]*cos($la*M_PI/180),
		              $yc - $this->explode_radius[$i]*sin($la*M_PI/180)*($h/1) );
	    $adjexplode[$idx] = $explode;
	    $labeldata[$i] = array($la,$explode[0],$explode[1]);
	    $originalangles[$i] = array($a,$a+$da);

	    $ne = $this->NormAngle($a+$da);
	    if( $da <= 180 ) {
		// If the slice size is <= 90 it can at maximum cut across
		// one boundary (either 90 or 270) where it needs to be split
		dbgp( "da<=180, a=$a, ne=$ne, da=$da<br>" );
		$split=-1; // no split
		if( ($da<=90 && ($a <= 90 && $ne > 90)) ||
		    (($da <= 180 && $da >90)  && (($a < 90 || $a >= 270) && $ne > 90)) ) {
		    dbgp( "&nbsp; a<=90 && ne>=90, a=$a, ne=$ne, da=$da<br>" );
		    $split = 90;
		}
		elseif( ($da<=90 && ($a <= 270 && $ne > 270)) ||
		        (($da<=180 && $da>90) && ($a >= 90 && $a < 270 && ($a+$da) > 270 )) ) {
		    dbgp( "&nbsp; a<=270 && ne>270, a=$a, ne=$ne, da=$da<br>" );
		    $split = 270;
		} 
		if( $split > 0 ) { // split in two
		    $angles[$idx] = array($a,$split);
		    $adjcolors[$idx] = $colors[$i];
		    $adjexplode[$idx] = $explode;
		    $angles[++$idx] = array($split,$ne);
		    $adjcolors[$idx] = $colors[$i];
		    $adjexplode[$idx] = $explode;
		}
		else { // no split
		    $angles[$idx] = array($a,$ne);
		    $adjcolors[$idx] = $colors[$i];
		    $adjexplode[$idx] = $explode;	
		}
	    }
	    else { 
		// da>180
		// Slice may, depending on position, cross one or two
		// bonudaries
		dbgp( "da<=180, a=$a, ne=$ne, da=$da, " );

		if( $a < 90 ) 
		    $split = 90;
		elseif( $a <= 270 )
		    $split = 270;
		else 
		    $split = 90;

		dbgp("split=$split<br>");

		$angles[$idx] = array($a,$split);
		$adjcolors[$idx] = $colors[$i];
		$adjexplode[$idx] = $explode;
		//if( $a+$da > 360-$split ) { 
		// For slices larger than 270 degrees we might cross
		// another boundary as well. This means that we must
		// split the slice further. The comparison gets a little
		// bit complicated since we must take into accound that
		// a pie might have a startangle >0 and hence a slice might
		// wrap around the 0 angle.
		// Three cases:
		//  a) Slice starts before 90 and hence gets a split=90, but 
		//     we must also check if we need to split at 270
		//  b) Slice starts after 90 but before 270 and slices
		//     crosses 90 (after a wrap around of 0)
		//  c) If start is > 270 (hence the firstr split is at 90)
		//     and the slice is so large that it goes all the way
		//     around 270.
		if( ($a < 90 && ($a+$da > 270)) ||
		    ($a > 90 && $a<=270 && ($a+$da>360+90) ) ||
		    ($a > 270 && $this->NormAngle($a+$da)>270) ) { 
		    dbgp("&nbsp; a+da > 360-$split, a=$a, da=$da<br>");
		    $angles[++$idx] = array($split,360-$split);
		    $adjcolors[$idx] = $colors[$i];
		    $adjexplode[$idx] = $explode;
		    $angles[++$idx] = array(360-$split,$ne);
		    $adjcolors[$idx] = $colors[$i];
		    $adjexplode[$idx] = $explode;
		}	
		else {
		    // Just a simple split to the previous decided
		    // angle.
		    $angles[++$idx] = array($split,$ne);
		    $adjcolors[$idx] = $colors[$i];
		    $adjexplode[$idx] = $explode;
		}
	    }
	    $a += $da;
	    $a = $this->NormAngle($a);
	}

	// Total number of slices 
	$n = count($angles);

	dbgp("<br>Splitted pie:<br>");
	for($i=0; $i<$n; ++$i) {
	    list($dbgs,$dbge) = $angles[$i];
	    dbgp("&nbsp;#$i: s=$dbgs, e=$dbge<br>");
	}

	// 
	// Step 2. Find start index (first pie that starts in upper left quadrant)
	//
	$minval = $angles[0][0];
	$min = 0;
	for( $i=0; $i<$n; ++$i ) {
	    if( $angles[$i][0] < $minval ) {
		$minval = $angles[$i][0];
		$min = $i;
	    }
	}
	$j = $min;
	$cnt = 0;
	while( $angles[$j][1] <= 90 ) {
	    $j++;
	    if( $j>=$n) {
		$j=0;
	    }
	    if( $cnt > $n ) {
		JpGraphError::Raise("Pie3D Internal error (#1). Trying to wrap twice when looking for start index");
	    }
	    ++$cnt;
	}
	$start = $j;
	dbgp( "Start index: $start<br>" );

	// 
	// Step 3. Print slices in z-order
	//
	$cnt = 0;
	while( $angles[$j][0] < 270 ) {

	    list($x,$y) = $adjexplode[$j];

	    $this->Pie3DSlice($img,$x,$y,$d,$h,$angles[$j][0],$angles[$j][1],$z,$adjcolors[$j],
	    $shadow);

	    $j++;
	    if( $j >= $n ) $j=0;
	    if( $cnt > $n ) {
		JpGraphError::Raise("Pie3D Internal Error: Z-Sorting algorithm for 3D Pies is not working properly (2). Trying to wrap twice while stroking.");
	    }
	    ++$cnt;
	}
     
	$slice_left = $n-$cnt;
	$j=$start-1;
	if($j<0) $j=$n-1;
	$cnt = 0;
	while( $cnt < $slice_left  ) {

	    list($x,$y) = $adjexplode[$j];

	    $this->Pie3DSlice($img,$x,$y,$d,$h,$angles[$j][0],$angles[$j][1],$z,$adjcolors[$j],
	    $shadow);
	    $j--;
	    if( $cnt > $n ) {
		JpGraphError::Raise("Pie3D Internal Error: Z-Sorting algorithm for 3D Pies is not working properly (2). Trying to wrap twice while stroking.");
	    }
	    if($j<0) $j=$n-1;
	    $cnt++;
	}


	// Now print possible labels and add csim
	$img->SetFont($this->value->ff,$this->value->fs);
	$margin = $img->GetFontHeight()/2;
	for($i=0; $i < count($data); ++$i ) {
	    $la = $labeldata[$i][0];
	    $x = $labeldata[$i][1] + cos($la*M_PI/180)*($d+$margin);
	    $y = $labeldata[$i][2] - sin($la*M_PI/180)*($h+$margin);
	    if( $la > 180 && $la < 360 ) $y += $z;
	    $this->StrokeLabels($data[$i],$img,$labeldata[$i][0]*M_PI/180,$x,$y);
	    
	    $this->AddSliceToCSIM($i,$labeldata[$i][1],$labeldata[$i][2],$h*2,$d*2,$z,
	                          $originalangles[$i][0],$originalangles[$i][1]);    
	}	

	// 
	// Finally add potential lines in pie
	//

	if( $edgecolor=="" ) return;

	$accsum = 0;
	$a = $startangle;
	$a = $this->NormAngle($a);

	$idx=0;
	$img->PushColor($edgecolor);
	

	$img->SetLineWeight($edgeweight);
	for($i=0; $i < count($data); ++$i, ++$idx ) {

	    $x = $xc + floor(cos($a*M_PI/180) * $d);
	    $y = $yc - floor(sin($a*M_PI/180) * $h);
	    $img->Line($xc,$yc,$x,$y);
	    
	    $da = $data[$i]/$sum * 360;

	    if( empty($this->explode_radius[$i]) )
		$this->explode_radius[$i]=0;

	    $la = $a + $da/2;
	    $explode = array( $xc + $this->explode_radius[$i]*cos($la*M_PI/180),
		              $yc - $this->explode_radius[$i]*sin($la*M_PI/180)*($h/$d) );

	    $a += $da;
	}

	$img->SetLineWeight(2);

	// Right sideline
	$img->Line($xc+$d,$yc,$xc+$d,$yc+$z);

	// Left sideline
	$img->Line($xc-$d+1,$yc,$xc-$d+1,$yc+$z);

	// Major full ellipse
	$img->Ellipse($xc,$yc+1,$d*2.01,$h*2.01);
	$img->Ellipse($xc+1,$yc,$d*2.01,$h*2.01);
	$img->Ellipse($xc,$yc,$d*2.01,$h*2.01);

	// Lower half ellipse
	$img->Arc($xc,$yc+$z,$d*2,$h*2,0,180);
	$img->Arc($xc,$yc+$z+1,$d*2,$h*2,0,180);

	$img->PopColor();
	$img->SetLineWeight(1);	
    }


    function Stroke(&$img) {

	$colors = array_keys($img->rgb->rgb_table);
   	sort($colors);	
   	
   	if( $this->setslicecolors==null ) {
	    $idx_a=$this->themearr[$this->theme];	
	    $numcolors = count($idx_a);
	    $ca = array();
	    for($i=0; $i<$numcolors; ++$i)
		$ca[$i] = $colors[$idx_a[$i]];
	}
   	else {
	    $ca = $this->setslicecolors;
	}

	$numcolors=count($ca);

        $xc = $this->posx*$img->width;
        $yc = $this->posy*$img->height;
   			
	if( $this->radius < 1 ) {
	    $width = floor($this->radius*min($img->width,$img->height));
	    // Make sure that the pie doesn't overflow the image border
	    // The 0.9 factor is simply an extra margin to leave some space
	    // between the pie an the border of the image.
	    $width = min($width,min($xc*0.9,($yc*90/$this->angle-$width/4)*0.9));
	}
	else
	    $width = $this->radius ;

	// Establish a thickness. By default the thickness is a fifth of the
	// pie slice width (=pie radius) but since the perspective depends
	// on the inclination angle we use some heuristics to make the edge
	// slightly thicker the less the angle.
	$thick = $width/5;
	$a = $this->angle;
	if( $a <= 30 ) $thick *= 1.6;
	elseif( $a <= 40 ) $thick *= 1.4;
	elseif( $a <= 50 ) $thick *= 1.2;
	elseif( $a <= 60 ) $thick *= 1.0;
	elseif( $a <= 70 ) $thick *= 0.8;
	elseif( $a <= 80 ) $thick *= 0.7;
	else $thick *= 0.6;

	if( $this->explode_all )
	    for($i=0;$i<count($this->data);++$i)
		$this->explode_radius[$i]=$this->explode_r;

	$this->Pie3D($img,$this->data, $ca, $xc, $yc, $width, $this->angle, 
	             $thick, 0.65, $this->startangle, $this->edgecolor, $this->edgeweight);

	// Adjust title position
	$this->title->Pos($xc,$yc-$img->GetFontHeight()-$this->radius,"center","bottom");
	$this->title->Stroke($img);
    }

//---------------
// PRIVATE METHODS	

    // Position the labels of each slice
    function StrokeLabels($label,$img,$a,$xp,$yp) {
	$this->value->halign="left";
	$this->value->valign="top";
	$this->value->margin=0;

	// Position the axis title. 
	// dx, dy is the offset from the top left corner of the bounding box that sorrounds the text
	// that intersects with the extension of the corresponding axis. The code looks a little
	// bit messy but this is really the only way of having a reasonable position of the
	// axis titles.
	$img->SetFont($this->value->ff,$this->value->fs,$this->value->fsize);
	$h=$img->GetTextHeight($label);
	$w=$img->GetTextWidth(sprintf($this->value->format,$label));
	while( $a > 2*M_PI ) $a -= 2*M_PI;
	if( $a>=7*M_PI/4 || $a <= M_PI/4 ) $dx=0;
	if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dx=($a-M_PI/4)*2/M_PI; 
	if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dx=1;
	if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dx=(1-($a-M_PI*5/4)*2/M_PI);
		
	if( $a>=7*M_PI/4 ) $dy=(($a-M_PI)-3*M_PI/4)*2/M_PI;
	if( $a<=M_PI/4 ) $dy=(1-$a*2/M_PI);
	if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dy=1;
	if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dy=(1-($a-3*M_PI/4)*2/M_PI);
	if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dy=0;
	
	$this->value->Stroke($img,$label,$xp-$dx*$w,$yp-$dy*$h);
    }	
} // Class

/* EOF */
?>
