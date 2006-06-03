<?php
/*
Author     : Antoine Michéa
Mail       : saturn@saturn-share.org
Web        : saturn-share.org
Program    : dashed_rect.php
License    : GPL v2
Description: Allows to draw a dashed rectangle. Parameters are:
              x1, y1 : upper left corner of the rectangle.
              x2, y2 : lower right corner of the rectangle.
              width  : dash thickness (1 by default).
              nb     : number of dashes per line (15 by default).
Date       : 2003-01-07
*/

define('FPDF_FONTPATH','font/');
require('fpdf.php');

class PDF extends FPDF
{

    /*
    Function Rect
    Author Pablo Dall'Oglio
    */
    /*function Rect($x1,$y1,$x2,$y2,$width=1,$nb=15)
    {
        $this->SetLineWidth($width);
        $this->Line($x1,$y1,$x2,$y1);
        $this->Line($x1,$y1,$x1,$y2);
        $this->Line($x2,$y1,$x2,$y2);
        $this->Line($x1,$y2,$x2,$y2);
    }*/

    function RoundedRect($x, $y, $w, $h, $r, $style = '', $angle = '1234')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' or $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2f %.2f m',($x+$r)*$k,($hp-$y)*$k ));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2f %.2f l', $xc*$k,($hp-$y)*$k ));
        if (strpos($angle, '2')===false)
            $this->_out(sprintf('%.2f %.2f l', ($x+$w)*$k,($hp-$y)*$k ));
        else
            //$this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-$yc)*$k));
        if (strpos($angle, '3')===false)
            $this->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-($y+$h))*$k));
        else
            //$this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2f %.2f l',$xc*$k,($hp-($y+$h))*$k));
        if (strpos($angle, '4')===false)
            $this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-($y+$h))*$k));
        else
            //$this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$yc)*$k ));
        if (strpos($angle, '1')===false)
        {
            $this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$y)*$k ));
            $this->_out(sprintf('%.2f %.2f l',($x+$r)*$k,($hp-$y)*$k ));
        }
        //else
            //$this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
}
/*$pdf=new PDF();
$pdf->Open();
$pdf->AddPage();
$pdf->SetDrawColor(200);
$pdf->DashedRect(40,10,165,40);
$pdf->SetFont('Arial','B',30);
$pdf->SetXY(40,10);
$pdf->Cell(125,30,'Enjoy dashes!',0,0,'C',0);
$pdf->Output();*/
$pdf=new PDF;
$pdf->Open();
$pdf->AddPage();
$pdf->SetDrawColor(0);
$pdf->SetFillColor(192);
//$pdf->RoundedRect(60, 30, 68, 46, 5, 'DF', '13');
$pdf->Rect(50, 50, 10, 10, 'FD');
$pdf->Output();

?>