<?php
	// $Id$

LoadObjectDependency('Agata.AgataReport');

class AgataPdf extends AgataReport
{
  var $Query;
  var $Maior;
  var $Columns;
  var $FileName;
  var $ColumnTypes;
  
  function SetTextConfig($font, $color, $bgcolor)
  {
    $fonts = explode('-', $font);
    $this->PDF->SetFont($fonts[0], substr($fonts[1],0,1), $fonts[2]);
    
    $colorR = hexdec(substr($color,1,2));
    $colorG = hexdec(substr($color,3,2));
    $colorB = hexdec(substr($color,5,2));
    $this->PDF->SetTextColor($colorR, $colorG, $colorB);

    $bgcolorR = hexdec(substr($bgcolor,1,2));
    $bgcolorG = hexdec(substr($bgcolor,3,2));
    $bgcolorB = hexdec(substr($bgcolor,5,2));
    $this->PDF->SetFillColor($bgcolorR, $bgcolorG, $bgcolorB);
  }

  function Process($isGui = false)
  {
    setlocale(LC_ALL, 'english');
    define('FPDF_FONTPATH',dirname(dirname(__FILE__)).'/fpdf-font/');

    $ColorLines  = $this->agataConfig['pdf']['ColorLines'];
    $this->Orientation = $this->agataConfig['pdf']['Orientation'];

    if ($isGui)
    {
      $InputBox = $this->InputBox;
      $this->ReportName = $InputBox->InputEntry->get_text();
      $InputBox->Close();
    }
    else
    {
      $ReportName = $this->ReportName;
    }

    $cellspacing   = $this->agataConfig['pdf']['CellSpacing'];
    $border        = $this->agataConfig['pdf']['Border'];
    $titlefont     = $this->agataConfig['pdf']['TitleFont'];
    $titlecolor    = $this->agataConfig['pdf']['TitleColor'];
    $titlebgcolor  = $this->agataConfig['pdf']['TitleBgColor'];
    $datafont      = $this->agataConfig['pdf']['DataFont'];
    $datacolor     = $this->agataConfig['pdf']['DataColor'];
    $databgcolor   = $this->agataConfig['pdf']['DataBgColor'];
    $totalfont     = $this->agataConfig['pdf']['TotalFont'];
    $totalcolor    = $this->agataConfig['pdf']['TotalColor'];
    $totalbgcolor  = $this->agataConfig['pdf']['TotalBgColor'];
    $groupfont     = $this->agataConfig['pdf']['GroupFont'];
    $groupcolor    = $this->agataConfig['pdf']['GroupColor'];
    $groupbgcolor  = $this->agataConfig['pdf']['GroupBgColor'];
    $columnfont    = $this->agataConfig['pdf']['ColumnFont'];
    $columncolor   = $this->agataConfig['pdf']['ColumnColor'];
    $columnbgcolor = $this->agataConfig['pdf']['ColumnBgColor'];


    $FileName = $this->FileName;
    $this->PDF = CreateObject('Agata.FPDF');
    $this->PDF->SetAutoPageBreak(true);
    $this->PDF->SetMargins(5, 5, 5);
    $this->PDF->Open();
    $this->PDF->AliasNbPages();
    $this->PDF->AddPage($this->Orientation);
    $extend = 2.4;
    $lineheight = 6;

    $this->pagina = 1;
    $this->Fator = 6.5;

    if ($this->Orientation == 'Landscape')
    {
      $this->lin = -34;
      $this->limite = -565;
      $this->central_col = 420;
    }
    else
    {
      $this->lin = 820;
      $this->limite = 20;
      $this->central_col = 297;
    }

    Wait::On($isGui);

    $OffSet = 1;
    if ($this->Breaks)
    {
      $CountBreaks=count($this->Breaks);
      if ($this->Breaks['0'])
        $CountBreaks --;      
      ksort($this->Breaks);
      reset($this->Breaks);
      if ($this->ShowTotalLabel)
        $OffSet = 30;
    }
    
    $MarginBreaks = $CountBreaks * 5;
    foreach ($this->MaxLen as $col => $Maior)
    {
      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$col]))
      {
        $Cols += $Maior;
      }
    }

    $this->LineSize = ($Cols + (2* count($this->Columns))) *$this->Fator + $MarginBreaks;

    //$this->PDF->SetFont('Times','B',16);
    $this->SetTextConfig($titlefont, $titlecolor, $titlebgcolor);
    $this->PDF->Cell(0,10,$this->ReportName,1,1,'C', 1);
    $this->PDF->Ln(4);

    $this->SetTextConfig($columnfont, $columncolor, $columnbgcolor);
    if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0'])))
    {
      for ($z=0; $z<=count($this->Columns); $z++)
      {
        $Column = $this->Columns[$z];

	if ($z == count($this->Columns))
	  $this->PDF->Cell(0,$lineheight,$Column,1,0, '', 1);
	else
	  $this->PDF->Cell($this->MaxLen[$z+1]*$extend,$lineheight,$Column,1,0,'L', 1);
      }
    }
    $this->PDF->Ln($lineheight);


    for ($x=0; $x<=count($this->Query); $x++)
    {
      $QueryLine = $this->Query[$x];
      $this->col = 26;

      $this->BreakMatrix = null;
      $this->Headers = null;
      $stringline = null;
      for ($y=1; $y<=count($QueryLine); $y++)
      {
        $querycell = $QueryCell = $QueryLine[$y];
	
        $FormatedField = FormatField($this->agataDB, $this->agataConfig, $QueryCell, $this->ColumnTypes[$y - 1]);
        $QueryCell     = $FormatedField[0];
        $isRight       = $FormatedField[1];
	$this->Align[$y] = ($isRight ? 'R' : 'L');

	//------------------------------------------------------------
	list($break) = $this->ProcessBreaks($querycell, $y);
	//------------------------------------------------------------

	if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) //aquipbreak
        {
	  if ($y==count($QueryLine))
	    $stringline[] = array($y, $QueryCell, $isRight, 0);
	  else
	    $stringline[] = array($y, $QueryCell, $isRight, $this->MaxLen[$y]);
        }
      }

      $this->SetTextConfig($totalfont, $totalcolor, $totalbgcolor);
      if (($this->BreakMatrix) && ($break != '0'))
      {
	$chaves = array_reverse(array_keys($this->BreakMatrix));

	foreach ($chaves as $chave)
        {
	  //-----------------------------------------
	  $FinalBreak = $this->EqualizeBreak($chave);
	  //-----------------------------------------

	  if ($this->HasFormula[$chave])
	  {	  
	    $this->PDF->Ln(4);
	  
	    foreach ($FinalBreak as $FinalBreakLine)
	    {
	      if ($this->ShowTotalLabel)
	      {
	        if ($chave == '0')
		{
		  $this->PDF->Cell(6, 0,'',0,0);
		  $this->PDF->Cell(($MarginHeader*$extend) + $OffSet, $lineheight, 0, '(Grand Total)',0,0, '', 1);
		}
	        else
		{
		  $this->PDF->Cell(6, 0,'',0,0);
		  $this->PDF->Cell(($MarginHeader * $extend) + $OffSet, $lineheight, '(' . substr($this->Summary[$chave]['BeforeLastValue'] ,0, 11) . ')',0,0, '', 1);
		}
	      }
	      else
	      {
	        $this->PDF->Cell(($MarginHeader*$extend) + 6 + $OffSet,$lineheight,'',0,0);
	      }

	      $w = 0;
	      foreach($FinalBreakLine as $content)
	      {
	        $w ++;
	        if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
                {
	          if ($content)
	          {
		    $this->PDF->Cell((($this->MaxLen[$w]) * $extend), $lineheight, $content, 1, 0, '', 1);
	          }
		  else
		  {
		    $this->PDF->Cell((($this->MaxLen[$w]) * $extend), $lineheight, '', 0, 0);
		  }
	        }
	      }
	      $this->PDF->Ln($lineheight);
	    }
	  }
        }
      }

      
      if (($this->Headers) && ($break != '0'))
      {
	$lineodd = 0;
	$this->PDF->Ln(4);

	$this->SetTextConfig($groupfont, $groupcolor, $groupbgcolor);
	foreach ($this->Headers as $nCountBreak => $Header)
	{
	  $MarginHeader = ($nCountBreak * $extend);
	  $this->col = ($MarginHeader * $extend) + $OffSet;
	  $this->PDF->Cell($this->col, $lineheight,'',0,0);
	  $this->PDF->Cell(0, $lineheight, $Header, 1, 0, '', 1);
	  $this->PDF->Ln($lineheight);
	}

	$this->SetTextConfig($columnfont, $columncolor, $columnbgcolor);
	$this->PDF->Cell($this->col + 6, $lineheight, '',0,0);
	for ($z=0; $z<=count($this->Columns); $z++)
        {
          $Column = $this->Columns[$z];
	  if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)]))
	  {
	    if ($z==count($this->Columns))
	      $this->PDF->Cell(0, $lineheight, $Column, 1, 0, '',  1);
	    else
	      $this->PDF->Cell(($this->MaxLen[$z+1]) * $extend, $lineheight, $Column, 1, 0, '', 1);
	  }
	  
        }
	$this->PDF->Ln($lineheight);
      }

      if ($this->ShowDataColumns)
      {
        $this->SetTextConfig($datafont, $datacolor, $databgcolor);
	if ($stringline)
	{	
          if ($MarginHeader)
            $this->PDF->Cell(($MarginHeader*$extend) + 6 + $OffSet,$lineheight,'',0,0);

          $color = 1;
	  if ((($lineodd % 2) ==0) && $ColorLines)
	  {
	    $color = 0;
	  }

	  foreach ($stringline as $line)
 	  {
	    $_y = $line[0];
	    $_QueryCell = $line[1];
	    $_isRight = $line[2];
	    $_len = $line[3];

	    if (strlen($QueryCell) > 100)
            {
	      $this->PDF->MultiCell($_len*$extend,$lineheight,$_QueryCell,1,0);
            }
            else
            {
              if ($_isRight)
              {
		$this->PDF->Cell($_len*$extend,$lineheight,$_QueryCell,1,0, 'R', $color);
              }
              else
              {
		$this->PDF->Cell($_len*$extend,$lineheight,$_QueryCell,1,0,'', $color);
              }
            }
	  }
	  $this->PDF->Ln();
	  
	  $lineodd ++;
	}
      }
    }

    /**************************
    PROCESS TOTALS OF LAST LINE
    ***************************/

    //------------------------
    $this->ProcessLastBreak();
    //------------------------

    if ($this->BreakMatrix)
    {
      $chaves = array_reverse(array_keys($this->BreakMatrix));
      foreach ($chaves as $chave)
      {
        //-----------------------------------------
        $FinalBreak = $this->EqualizeBreak($chave);
        //-----------------------------------------
	  
	if (($this->HasFormula[$chave]) || ($chave =='0'))
	{
          $this->PDF->Ln(4);
	  
	  $this->SetTextConfig($totalfont, $totalcolor, $totalbgcolor);
	  foreach ($FinalBreak as $FinalBreakLine)
	  {
	    if ($this->ShowTotalLabel)
	    {
	      if ($chave == '0')
	      {
		$this->PDF->Cell(6, 0,'',0,0);
		$this->PDF->Cell(($MarginHeader*$extend) + $OffSet, $lineheight, 0, '(Grand Total)',0,0, '', 1);
	      }
	      else
	      {
		$this->PDF->Cell(6, 0,'',0,0);
		$this->PDF->Cell(($MarginHeader * $extend) + $OffSet, 0, '(' . substr($this->Summary[$chave]['BeforeLastValue'] ,0, 11) . ')',0,0, '', 1);
	       }
	    }
	    else
	    {
	      $this->PDF->Cell(($MarginHeader*$extend) + 6 + $OffSet,$lineheight,'',0,0);
	    }

            $w = 0;
	    foreach($FinalBreakLine as $content)
	    {
	      $w ++;
	      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
              {
	        if ($content)
	        {
	          $this->PDF->Cell((($this->MaxLen[$w]) * $extend), $lineheight, $content, 1, 0,  '', 1);
	        }
		else
		{
		  $this->PDF->Cell((($this->MaxLen[$w]) * $extend), $lineheight, '', 0, 0);
		}
	      }
	    }
	    $this->PDF->Ln($lineheight);
	  }
        }
      }
    }

    /******************
    END OF LAST PROCESS
    *******************/

    $this->PDF->Output($FileName);

    if ($this->posAction)
    {
      $this->ExecPosAction();
      OpenReport($FileName, $this->agataConfig);
    }

    Wait::Off($isGui);
  
    return true;
  }
}
