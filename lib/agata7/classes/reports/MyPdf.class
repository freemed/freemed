<?php
class MyPdf extends FPDF
{

    function MyPdf($orientation, $unit, $format)
    {
        FPDF::FPDF($orientation, $unit, $format);
    }

    function SetTextConfig($font, $color, $bgcolor)
    {
        $fonts = explode('-', $font);
        $this->SetFont($fonts[0], substr($fonts[1],0,1), $fonts[2]);

        $colorR = hexdec(substr($color,1,2));
        $colorG = hexdec(substr($color,3,2));
        $colorB = hexdec(substr($color,5,2));
        $this->SetTextColor($colorR, $colorG, $colorB);
        
        $bgcolorR = hexdec(substr($bgcolor,1,2));
        $bgcolorG = hexdec(substr($bgcolor,3,2));
        $bgcolorB = hexdec(substr($bgcolor,5,2));
        $this->SetFillColor($bgcolorR, $bgcolorG, $bgcolorB);
    }

    //Page header
    function Header()
    {
        $aligns['center'] = 'C';
        $aligns['left']   = 'L';
        $aligns['right']  = 'R';

        include 'include/report_vars.inc';
        $ReportName = $this->ReportName;
        $page       = $this->PageNo();
        $pagecounting = $totalPages = '{nb}';
        
        
        if ($this->Parameters)
        {
            foreach ($this->Parameters as $key => $value)
            {
                $this->textHeader = str_replace($key, $value, $this->textHeader);
            }
        }
        
        $header = explode("\n", $this->textHeader);
        
        if (strlen(trim($this->textHeader)) >0)
        {
            $this->SetTextConfig($this->headerfont, $this->headercolor, $this->headerbgcolor);
            foreach($header as $headerline)
            {
                eval ("\$tmp = \"$headerline\";");
                if (substr($tmp,0,6) == '#image')
                {
                    $image_name = trim(substr($tmp, 7));
                    if (file_exists($image_name))
                    {
                        $size = getimagesize($image_name);
                        $width = ((int) ($size[0] / 28.344 * 100)) /10;
                        $height =((int) ($size[1] / 28.344 * 100)) /10;
                        $this->Image($image_name, $this->GetX(), $this->GetY(), $width, $height);
                        $this->SetY($this->GetY() + $height);
                    }
                }
                else
                {
                    $this->Cell(0,6,$tmp, 0, 0, $aligns[$this->alignHeader], 1);
                    $this->Ln(6);
                }
            }
            $this->Ln(6);
        }
    }

    //Page footer
    function Footer()
    {
        $aligns['center'] = 'C';
        $aligns['left']   = 'L';
        $aligns['right']  = 'R';

        include 'include/report_vars.inc';
        $ReportName = $this->ReportName;
        $page       = $this->PageNo();
        $pagecounting = $totalPages = '{nb}';
        
        if ($this->Parameters)
        {
            foreach ($this->Parameters as $key => $value)
            {
                $this->textFooter = str_replace($key, $value, $this->textFooter);
            }
        }
        
        $footer = explode("\n", $this->textFooter);
        $this->SetY(count($footer) * 6 * -1);

        if (strlen(trim($this->textFooter)) >0)
        {
            $this->SetTextConfig($this->footerfont, $this->footercolor, $this->footerbgcolor);
            foreach($footer as $footerline)
            {
                eval ("\$tmp = \"$footerline\";");
                if (substr($tmp,0,6) == '#image')
                {
                    $image_name = trim(substr($tmp, 7));
                    $size = getimagesize($image_name);
                    $width = ((int) ($size[0] / 28.344 * 100)) /10;
                    $height =((int) ($size[1] / 28.344 * 100)) /10;
                    $this->Image($image_name, $this->GetX(), $this->GetY(), $width, $height);
                    $this->SetY($this->GetY() + $height);
                }
                else
                {
                    $this->Cell(0,6,$tmp, 0, 0, $aligns[$this->alignFooter], 1);
                    $this->Ln(6);
                }
            }
            $this->Ln(6);
        }
    }
}
?>
