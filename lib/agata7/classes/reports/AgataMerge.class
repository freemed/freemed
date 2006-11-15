<?php
/***********************************************************/
/* Abstract Merge class
/* by Pablo Dall'Oglio  2001-2006
/*    Jamiel Spezia 2006 - 2006
/***********************************************************/

class AgataMerge
{
    /***********************************************************/
    /* Set the properties
    /***********************************************************/
    function SetProperties($params)
    {
        $this->agataDB      = $params[0];
        $this->agataConfig  = $params[1];
        $this->FileName     = $params[2];
        $this->CurrentQuery = $params[3];
        $this->Query        = $this->CurrentQuery->Query;
        $this->XmlArray     = $params[4];
        $this->posAction    = $params[5];
        $this->Parameters   = $params[6];
        $this->oneRecordPerPage = $params[7];
        $this->Adjustments  = CoreReport::ExtractAdjustments($this->XmlArray['Report']['DataSet']);
        for ($x=0;$x<=$this->XmlArray['Report']['Merge']['Details']['Detail1']['NumberSubSql']; $x++)
        {
            $this->SubAdjustments[$x]=CoreReport::ExtractAdjustments($this->XmlArray['Report']['Merge']['Details']['Detail1']['DataSet'.($x+1)]);
        }
    }

    /***********************************************************/
    /* Fill text parameters
    /***********************************************************/
    function fillParameters($text)
    {
        if ($this->Parameters)
        {
            foreach ($this->Parameters as $key => $content)
            {
                $text = str_replace($key, $content, $text);
            }
        }
        return $text;
    }

    /***********************************************************/
    /* Draws a Circle
    /***********************************************************/
    function Circle($x,$y,$r,$style='')
    {
        $this->Ellipse($x,$y,$r,$r,$style);
    }

    /***********************************************************/
    /* Draws a Ellipse
    /***********************************************************/
    function Ellipse($x,$y,$rx,$ry,$style='D')
    {
        if($style=='F')
            $op='f';
        elseif($style=='FD' or $style=='DF')
            $op='B';
        else
            $op='S';

        $lx=4/3*(M_SQRT2-1)*$rx;
        $ly=4/3*(M_SQRT2-1)*$ry;
        $k=$this->PDF->k;
        $h=$this->PDF->h;

        $this->PDF->_out(sprintf('%.2f %.2f m %.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x+$rx)*$k,($h-$y)*$k,
            ($x+$rx)*$k,($h-($y-$ly))*$k,
            ($x+$lx)*$k,($h-($y-$ry))*$k,
            $x*$k,($h-($y-$ry))*$k));

        $this->PDF->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x-$lx)*$k,($h-($y-$ry))*$k,
            ($x-$rx)*$k,($h-($y-$ly))*$k,
            ($x-$rx)*$k,($h-$y)*$k));
        $this->PDF->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x-$rx)*$k,($h-($y+$ly))*$k,
            ($x-$lx)*$k,($h-($y+$ry))*$k,
            $x*$k,($h-($y+$ry))*$k));
        $this->PDF->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c %s',
            ($x+$lx)*$k,($h-($y+$ry))*$k,
            ($x+$rx)*$k,($h-($y+$ly))*$k,
            ($x+$rx)*$k,($h-$y)*$k,
            $op));
    }


    /***********************************************************/
    /* Draws a Rounded Rectangle
    /***********************************************************/
	function RoundedRect($x, $y, $w, $h, $r, $style = '')
	{
		$k = $this->PDF->k;
		$hp = $this->PDF->h;
		if($style=='F')
			$op='f';
		elseif($style=='FD' or $style=='DF')
			$op='B';
		else
			$op='S';
		$MyArc = 4/3 * (sqrt(2) - 1);
		$this->PDF->_out(sprintf('%.2f %.2f m',($x+$r)*$k,($hp-$y)*$k ));
		$xc = $x+$w-$r ;
		$yc = $y+$r;
		$this->PDF->_out(sprintf('%.2f %.2f l', $xc*$k,($hp-$y)*$k ));

		$this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
		$xc = $x+$w-$r ;
		$yc = $y+$h-$r;
		$this->PDF->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-$yc)*$k));
		$this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
		$xc = $x+$r ;
		$yc = $y+$h-$r;
		$this->PDF->_out(sprintf('%.2f %.2f l',$xc*$k,($hp-($y+$h))*$k));
		$this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
		$xc = $x+$r ;
		$yc = $y+$r;
		$this->PDF->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$yc)*$k ));
		$this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
		$this->PDF->_out($op);
	}

    /***********************************************************/
    /* Draws an Arc
    /***********************************************************/
	function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
	{
		$h = $this->PDF->h;
		$this->PDF->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1*$this->PDF->k, ($h-$y1)*$this->PDF->k,
			$x2*$this->PDF->k, ($h-$y2)*$this->PDF->k, $x3*$this->PDF->k, ($h-$y3)*$this->PDF->k));
	}

    /***********************************************************/
    /* Text With Direction
    /***********************************************************/
    function TextWithDirection($x,$y,$txt,$direction='R')
    {
        $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
        if ($direction=='R')
            $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',1,0,0,1,$x*$this->PDF->k,($this->PDF->h-$y)*$this->PDF->k,$txt);
        elseif ($direction=='L')
            $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',-1,0,0,-1,$x*$this->PDF->k,($this->PDF->h-$y)*$this->PDF->k,$txt);
        elseif ($direction=='U')
            $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',0,1,-1,0,$x*$this->PDF->k,($this->PDF->h-$y)*$this->PDF->k,$txt);
        elseif ($direction=='D')
            $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',0,-1,1,0,$x*$this->PDF->k,($this->PDF->h-$y)*$this->PDF->k,$txt);
        else
            $s=sprintf('BT %.2f %.2f Td (%s) Tj ET',$x*$this->PDF->k,($this->PDF->h-$y)*$this->PDF->k,$txt);
        if ($this->PDF->ColorFlag)
            $s='q '.$this->PDF->TextColor.' '.$s.' Q';
        $this->PDF->_out($s);
    }

    /***********************************************************/
    /* TextWithRotation
    /***********************************************************/
    function TextWithRotation($x,$y,$txt,$txt_angle,$font_angle=0)
    {
        $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
    
        $font_angle+=90+$txt_angle;
        $txt_angle*=M_PI/180;
        $font_angle*=M_PI/180;
    
        $txt_dx=cos($txt_angle);
        $txt_dy=sin($txt_angle);
        $font_dx=cos($font_angle);
        $font_dy=sin($font_angle);
    
        $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',
                 $txt_dx,$txt_dy,$font_dx,$font_dy,
                 $x*$this->PDF->k,($this->PDF->h-$y)*$this->PDF->k,$txt);
        if ($this->PDF->ColorFlag)
            $s='q '.$this->PDF->TextColor.' '.$s.' Q';
        $this->PDF->_out($s);
    }

    /***********************************************************/
    /* PDF parser Method
    /***********************************************************/
    function ParseStringPdf($Line)
  	{
        $Line = " $Line";
        $pos = strpos($Line, '#');
        $commands = array('#setc', '#tab', '#sety', '#setf', '#image', '#bgimage', '#file',
                          '#pagebreak', '#rect', '#rectr', '#elip', '#frame', '#barcode', '#rota', '#line', '#setspace');
        $this->PDF->SetLeftMargin($this->LeftMargin);

        if ($pos !== false)
        {
            $line1  = substr($Line, 0, $pos);
            if (trim($line1))
            {
                @$this->PDF->Write(20, trim($line1));
                $has_1 = true;
            }
            
            if (substr($Line,$pos,5)=='#setc') // set color
            {
                $color = substr($Line,$pos+6,6);
                $is_foreground = substr($Line,$pos+5,1) == 'f';
                $this->SetColor($is_foreground, $color);
                $offset = 12;
            }
            else if (substr($Line,$pos,9)=='#setspace') // set color
            {
                $this->LineHeight = (int)substr($Line,$pos+9,3);
                $offset = 12;
            }
            else if (substr($Line,$pos,4)=='#tab') // bgimage
            {
                $this->PDF->SetX(substr($Line,$pos+4,3) + $this->LeftMargin);
                $offset = 7;
            }
            else if (substr($Line,$pos,5)=='#sety') // bgimage
            {
                $this->PDF->SetY(substr($Line,$pos+5,3) + $this->TopMargin);
                $offset = 8;
            }
            else if (substr($Line,$pos,5)=='#setf') // set font
            {
                $format = substr($Line,$pos+5,4);
                $this->SetFont($format);
                $offset = 9;
            }
            else if (substr($Line,$pos,6)=='#image') // image
            {
                $image_name = trim(substr($Line, $pos+7));
                if (file_exists($image_name))
                {
                    $size = getimagesize($image_name);
                    $this->PDF->Image($image_name, $this->PDF->GetX(), $this->PDF->GetY(), $size[0], $size[1]);
                    $this->PDF->SetY($this->PDF->GetY() + $size[1]);
                }
                $offset = strlen($Line);
            }
            else if (substr($Line,$pos,5)=='#file') // image
            {
                $file_name = trim(substr($Line, $pos+6));
                if (file_exists($file_name))
                {
                    $content = file_get_contents($file_name);
                    $this->PDF->MultiCell(0,$this->LineHeight,$content);
                }
                $offset = strlen($Line);
            }
            else if (substr($Line,$pos,8)=='#bgimage') // bgimage
            {
                $image_name = trim(substr($Line, $pos+9));
                if (file_exists($image_name))
                {
                    $size = getimagesize($image_name);
                    $this->PDF->Image($image_name, $this->PDF->GetX(), $this->PDF->GetY(), $size[0], $size[1]);
                }
                $offset = strlen($Line);
            }
            else if (substr($Line,$pos,10)=='#pagebreak') // bgimage
            {
                $Line = ''; // reset line so 'PAGEBREAK' doesn't print
                $lin = $this->PageHeight - $this->TopMargin;
                $this->page ++;
                $this->PDF->AddPage($this->Orientation);
                $offset = 10;
            }
            else if (substr($Line,$pos,6)=='#rectr') // rounded rect
            {
                $y     = $this->PDF->GetY();
                $x  = $this->PDF->GetX();
                $x1    = substr($Line, 8, 3) + $x;
                $y1    = substr($Line,12, 3);
                $width = substr($Line,16, 3);
                $height= substr($Line,20, 3);
                $fillc = rgb2int255(substr($Line,26, 7));
                $linec = rgb2int255(substr($Line,34, 7));
                $linew = substr($Line,24, 1);
                $this->PDF->SetLineWidth($linew);
                $this->PDF->SetDrawColor($linec[0], $linec[1], $linec[2]);
                $this->PDF->SetFillColor($fillc[0], $fillc[1], $fillc[2]);
                $this->RoundedRect($x1, $y1 + $y, $width, $height, 12, 'FD');
                $offset = 39;
                #rectr*380*114*434*040*2*#FFFFFF*#000000
            }
            else if (substr($Line,$pos,5)=='#rect') // rect
            {
                $y     = $this->PDF->GetY();
                $x  = $this->PDF->GetX();
                $x1    = substr($Line, 7, 3) + $x;
                $y1    = substr($Line,11, 3);
                $width = substr($Line,15, 3);
                $height= substr($Line,19, 3);
                $fillc = rgb2int255(substr($Line,25, 7));
                $linec = rgb2int255(substr($Line,33, 7));
                $linew = substr($Line,23, 1);
                $this->PDF->SetLineWidth($linew);
                $this->PDF->SetDrawColor($linec[0], $linec[1], $linec[2]);
                $this->PDF->SetFillColor($fillc[0], $fillc[1], $fillc[2]);
                $this->PDF->Rect($x1, $y1 + $y, $width, $height, 'FD');
                $offset = 39;
                #rect*380*114*434*040*2*#FFFFFF*#000000
            }
            else if (substr($Line,$pos,6)=='#frame') // cell text
            {
                $cellLine = substr($Line, $pos+11);
                $cellPos  = strpos($cellLine, '#');
                $cellWidth= substr($Line, $pos+8, 3);
                $cellAlign= substr($Line, $pos+7, 1);
                $border   = (substr($Line, $pos+6, 1) == 'B') ? 1 : 0;

                if ( !$this->oneRecordPerPage )
                {
                    if ($this->Orientation == 'landscape')
                    {
                        $limit = $this->Pages[$this->PageFormat][0];
                    }
                    else
                    {
                        $limit = $this->Pages[$this->PageFormat][1];
                    }
                    if ($this->PDF->GetY() >= $limit - $this->FooterMargin - 20 )
                    {
                        $x_ = $this->PDF->GetX();
                        $this->PDF->AddPage($this->Orientation);
                        $this->page++;
                        $this->PDF->SetX($x_);
                        $this->PDF->SetY($this->TopMargin);
                        $this->PDF->SetLeftMargin($x_);
                    }  
                }
 
                $x0 = $this->PDF->GetX();
                $y0 = $this->PDF->GetY();
                
                if ($cellPos !== false)
                {
                    $offset = $cellPos + 11;
                    $cellLine = substr($cellLine,0,$cellPos);
                }
                else
                {
                    $offset = strlen($Line);
                } 
                
                $this->PDF->MultiCell($cellWidth,$this->LineHeight,$cellLine, $border, $cellAlign, $fill);
                
                $diffY = $this->PDF->GetY() - $y0;
                $this->LastHeight = $diffY > $this->LastHeight ? $diffY : $this->LastHeight;
               
                $this->PDF->SetX($x0 + $cellWidth);
                $this->PDF->SetY($y0);
                $this->PDF->SetLeftMargin($x0 + $cellWidth);

            #frameBC200lskjfaslfsdf #tab
            }
            else if (substr($Line,$pos,5)=='#elip')
            {
                $y  = $this->PDF->GetY();
                $x  = $this->PDF->GetX();
                $x1 = substr($Line,7, 3) + $x;
                $y1 = substr($Line,11, 3);
                $rx = substr($Line,15, 3);
                $ry = substr($Line,19, 3);
                $fillc = rgb2int255(substr($Line,25, 7));
                $linec = rgb2int255(substr($Line,33, 7));
                $linew = substr($Line,23, 1);
                $this->PDF->SetLineWidth($linew);
                $this->PDF->SetDrawColor($linec[0], $linec[1], $linec[2]);
                $this->PDF->SetFillColor($fillc[0], $fillc[1], $fillc[2]);
                $this->Ellipse($x1, $y1 + $y, $rx, $ry, 'FD');
                $offset = 39;
                #ellipse*380*114*434*040*2*#FFFFFF*#000000
            }
            else if (substr($Line,$pos,8)=='#barcode')
            {
                $code_line  = substr($Line, $pos);
                $line_parts = explode('*', $code_line);

                $code       = $line_parts[1];
                $char_width = $line_parts[2];
                $bar_height = $line_parts[3];
                $print_text = $line_parts[4];

                $barcode = new pdfbarcode128($code, $char_width);
                $barcode->set_pdf_document($this->PDF);
                $width = $barcode->get_width();
                $barcode->draw_barcode($this->PDF->GetX(), $this->PDF->GetY(), $bar_height, $print_text);
                $offset = strlen(trim($Line));
                #barcode*239424*20*40*1
                #barcode*code*width*height*printtext
            }
            else if (substr($Line,$pos,5)=='#rota') // text rotations
            {
                $direction = substr($Line, $pos+5, 1);
                $degrees   = substr($Line, $pos+5, 3);
                
                #L : Left  rotation
                #U : Upper rotation
                #R : Right rotation
                #D : Down  rotation
                #G : DeGrees
                
                if (is_numeric($direction))
                {
                    $rota_text = substr($Line, $pos+8);
                    $this->TextWithRotation($this->PDF->GetX(),$this->PDF->GetY(),$rota_text,$degrees);
                }
                else
                {
                    $rota_text = substr($Line, $pos+6);
                    $this->TextWithDirection($this->PDF->GetX(),$this->PDF->GetY(),$rota_text,$direction);
                }
                
                $offset = strlen(trim($Line));
                #rotaLsldkfjasdf
                #rota045sfalssssdf
            }
            else if (substr($Line,$pos,5)=='#line') // line
            {
                $direction = substr($Line, $pos+5, 1);
                $size      = substr($Line, $pos+6, 3);
                
                if ($direction == 'H') // horizontal
                {
                    $this->PDF->Line($this->PDF->GetX(),$this->PDF->GetY(),$this->PDF->GetX() + $size, $this->PDF->GetY());
                }
                else // vertical
                {
                    $this->PDF->Line($this->PDF->GetX(),$this->PDF->GetY(),$this->PDF->GetX(), $this->PDF->GetY()  + $size);
                }
                
                #LineH100
                $offset = 9;
            }
            else
            {
                $offset = 1;
                $plus = '#';
            }
        }
        $line2  = substr($Line, $pos + $offset);
        if (ArrayEreg($commands, $line2))
        {
            $has_2 = $this->ParseStringPdf($line2);
        }
        else
        {
            if (trim($line2))
            {
                @$this->PDF->Write(20, trim($plus . $line2));
                $has_2 = true;
            }
        }
        return ($has_1 or $has_2);
    }

    /***********************************************************/
    /* Changes the report locale
    /***********************************************************/
    function SetReportLocale()
    {
        setlocale(LC_ALL, 'POSIX');
    }

    /***********************************************************/
    /* Use the old Report Locale
    /***********************************************************/
    function UnsetReportLocale()
    {
        if (OS == 'WIN')
        {
            setlocale(LC_ALL, 'english');
        }
        else
        {
            setlocale(LC_ALL, 'pt_BR');
        }
    }

    /***********************************************************/
    /* Changes the font
    /***********************************************************/
    function SetFont($font_info)
    {
        $fonts['a'] = 'Arial';
        $fonts['c'] = 'Courier';
        $fonts['t'] = 'Times';

        $font  = $fonts[substr($font_info,0,1)];
        $style = strtoupper(substr($font_info,1,1));
        $style = ($style == 'N' ? '' : $style);
        $style = ($style == 'W' ? 'BI' : $style);
        $style = ($style == 'X' ? 'BU' : $style);
        $style = ($style == 'Y' ? 'BIU': $style);
        $style = ($style == 'Z' ? 'IU' : $style);
        $size  = substr($font_info, 2, 2);

        $this->PDF->SetFont($font, $style, $size);
    }

    /***********************************************************/
    /* Changes the color
    /***********************************************************/
    function SetColor($is_foreground, $color)
    {
        $colorR = hexdec(substr($color,0,2));
        $colorG = hexdec(substr($color,2,2));
        $colorB = hexdec(substr($color,4,2));
        if ($is_foreground)
        {
            $this->PDF->SetTextColor($colorR, $colorG, $colorB);
        }
        else
        {
            $this->PDF->SetFillColor($colorR, $colorG, $colorB);
        }
    }

    function SetSubDataArray($subDataArray)
    {
        $this->subDataArray = $subDataArray;
    }
}
?>
