<?php
	// $Id$

LoadObjectDependency('Agata.AgataReport');

class AgataPs extends AgataReport
{
  var $Query;
  var $Maior;
  var $Columns;
  var $FileName;
  var $ColumnTypes;

  function Process($isGui = false)
  {
    $linecol     = $this->agataConfig['ps']['PsLineLen'];
    $ColorLines  = $this->agataConfig['ps']['ColorLines'];
    $this->Orientation = $this->agataConfig['ps']['Orientation'];

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
    
    $FileName = $this->FileName;

    $this->PS = CreateObject('Agata.postscript', $FileName, 'Agata Report (http://agata.codigolivre.org.br)', "$this->ReportName", $this->Orientation);

    $this->pagina = 1;
    $this->Fator = 6.5;
    $this->PS->begin_page($this->pagina);

    if ($this->Orientation == 'Landscape')
    {
      $this->PS->rotate(90);
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
        $OffSet = 60;
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
    
    $this->PS->align_center($this->ReportName, (int)(($this->LineSize+26) /2), $this->lin, 'Arial-Bold', 16 );
    $this->lin -= 10;
    $this->PS->line(26, $this->lin -5, $this->LineSize, $this->lin -5, 1);
      

    $this->lin -= 23;
    $this->col = 26;    
    
    if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0'])))
    {
      for ($z=0; $z<=count($this->Columns); $z++)
      {
        $Column = $this->Columns[$z];
        $this->PS->show_xy_font($Column, $this->col, $this->lin, 'Arial-Bold', 10);
        $this->col += (($this->MaxLen[$z+1]) * $this->Fator);
      }

      $this->PS->line(26, $this->lin -5, $this->LineSize, $this->lin -5, 1);
      $this->lin -= 15;
    }

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

	//------------------------------------------------------------
	list($break) = $this->ProcessBreaks($querycell, $y);
	//------------------------------------------------------------

	if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) //aquipbreak
        {
          $stringline[] = array($this->lin, $this->col, $y, $QueryCell, $isRight);
	  
	  $this->col += (($this->MaxLen[$y]) * $this->Fator);
        }
      }
      
      if ($currlin && $currlin < $this->lin)
      {
        $this->lin = $currlin - 10;
      }
      else
      {
        $this->lin -= 10;
      }
      
      $this->TestBreakPage();

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
	    $this->col = 26 + ($MarginHeader * 5) + $OffSet;
	    $this->PS->line($this->col, $this->lin, $this->LineSize, $this->lin, 1);
	    $this->lin -=10;
	  
	    $this->TestBreakPage();
	  
	    foreach ($FinalBreak as $FinalBreakLine)
	    {
	      if ($this->ShowTotalLabel)
	      {
	        if ($chave == '0')
	          $this->PS->show_xy_font('(Grand Total)', 5, $this->lin, 'Courier', 10);
	        else
 	          $this->PS->show_xy_font(' (' . substr($this->Summary[$chave]['BeforeLastValue'] ,0, 11) . ')', 5, $this->lin, 'Courier', 10);
	      }
	  
	      $this->TestBreakPage();

	      $w = 0;
	      $this->col =  26 + ($MarginHeader * 5) + $OffSet;

	      foreach($FinalBreakLine as $content)
	      {
	        $w ++;
	        if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
                {
	          if ($content)
	          {
 		    $this->PS->show_xy_font($content, $this->col, $this->lin, 'Courier', 10);
	          }
		  $this->col += (($this->MaxLen[$w]) * $this->Fator);
	        }
	      }
	      $this->lin -=10;
	    }
	  }
        }
      }

      $this->TestBreakPage();
      
      
      if (($this->Headers) && ($break != '0'))
      {
	$lineodd = 0;
	$this->lin -=10;
	$this->TestBreakPage(40);
	foreach ($this->Headers as $nCountBreak => $Header)
	{
	  $MarginHeader = ($nCountBreak * 5) +1;
	  $this->col = ($MarginHeader * 5) +1;
          $this->PS->show_xy_font($Header, $this->col, $this->lin, 'Helvetica-bold', 10);

	  //$this->PS->line($this->col, $this->lin -5, (strlen($Header) * $this->Fator) + $this->col, $this->lin -5, 1);
	  $this->lin -=16;
	}
	$this->TestBreakPage();
	
	$this->col = 26 + ($MarginHeader * 5) + $OffSet;
	
	for ($z=0; $z<=count($this->Columns); $z++)
        {
          $Column = $this->Columns[$z];
	  if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)]))
	  {	  
	    $this->PS->show_xy_font($Column, $this->col, $this->lin, 'Courier', 10);
	    $this->col += (($this->MaxLen[$z+1]) * $this->Fator);
	  }
	  
        }
	$this->col = 26 + ($MarginHeader * 5) + $OffSet;
	$this->PS->line($this->col, $this->lin -5, $this->LineSize, $this->lin -5, 1);
	$this->lin -=20;
      }

      $this->TestBreakPage();

      if ($this->ShowDataColumns)
      {
	if ($stringline)
	{	
          if ((($lineodd % 2) ==0) && $ColorLines)
	  {
	    $_col = $stringline[0][1] + ($MarginHeader * 5) + $OffSet;
            $this->PS->rect_fill($_col, $this->lin-2, $this->LineSize, $this->lin +8, "0.1", "0.9");
	  }

	  foreach ($stringline as $line)
 	  {
	    $_col = $line[1] + ($MarginHeader * 5) + $OffSet;
	    $_y = $line[2];
	    $_QueryCell = $line[3];
	    $_isRight = $line[4];
	  
	    if(strlen($QueryCell) > 100)
            {
              $_QueryCell = wordwrap($_QueryCell, 100, "\n");
              $QueryArr = split("\n", $_QueryCell);
              $currlin = $this->lin;
              foreach($QueryArr as $myQueryCell)
              {
                $this->PS->show_xy_font($myQueryCell, $_col, $currlin, 'Courier', 10);
                $_currlin -= 10;
              }
            }
            else
            {
              if ($_isRight)
              {
                $this->PS->rshow($_QueryCell, $_col + (strlen($this->Columns[$_y -1]) * $this->Fator), $this->lin, 'Courier', 10);
              }
              else
              {
                $this->PS->show_xy_font($_QueryCell, $_col, $this->lin, 'Courier', 10);
              }
            }		
	  }
	  $lineodd ++;
	}
      }
      
      $this->TestBreakPage();
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
	  //$this->PS->line($MarginBreaks *5, $lin, $this->LineSize, $lin, 1);
	  $this->col = 26 + ($MarginHeader * 5) + $OffSet;
	  $this->PS->line($this->col, $this->lin, $this->LineSize, $this->lin, 1);	
	  $this->lin -=10;
	
          foreach ($FinalBreak as $FinalBreakLine)
          {
	    if ($this->ShowTotalLabel)
	    {
	      if ($chave == '0')
	        $this->PS->show_xy_font('(Grand Total)', 5, $this->lin, 'Courier', 10);
	      else
	        $this->PS->show_xy_font(' (' . substr($this->Summary[$chave]['LastValue'] ,0, 11) . ')', 5, $this->lin, 'Courier', 10);
	    }

	    $w = 0;
	    $this->col = 26 + ($MarginHeader * 5) + $OffSet;
	  
	    foreach($FinalBreakLine as $content)
	    {
	      $w ++;
	      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
              {
 	        if ($content)
	        {
		  $this->PS->show_xy_font($content, $this->col, $this->lin, 'Courier', 10);
	        }
	        $this->col += (($this->MaxLen[$w]) * $this->Fator);
	      }
	    }
            $this->lin -=10;
	  }
        }
      }
    }

    /******************
    END OF LAST PROCESS
    *******************/


   
    $this->PS->end_page();
    $this->PS->close();    
    if ($this->posAction)
    {
      $this->ExecPosAction();
      OpenReport($FileName, $this->agataConfig); 
    }
    Wait::Off($isGui);
  
    return true;
  }

  function TestBreakPage($less = 0)
  {
    if (($this->lin - $less ) < $this->limite)
    {
      $this->PS->end_page();
      $this->col = 26;
      $this->pagina ++;
      $this->PS->begin_page($this->pagina);

      if ($this->Orientation == 'Landscape')
      {
        $this->PS->rotate(90);
        $this->lin = -34;
      }
      else
      {
        $this->lin = 820;
      }
	
      $this->PS->align_center($this->ReportName, (int)(($this->LineSize+26) /2), $this->lin, 'Arial-Bold', 16 );
      $this->lin -= 10;
      $this->PS->line(26, $this->lin -5, $this->LineSize, $this->lin -5, 1);
      $this->lin -= 23;
      $this->col = 26;	

      if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0'])))
      {
        for ($z=0; $z<=count($this->Columns); $z++)
        {
          $Column = $this->Columns[$z];
          $this->PS->show_xy_font($Column, $this->col, $this->lin, 'Arial-Bold', 10);
          $this->col += (($this->MaxLen[$z+1]) * $this->Fator);
        }

        //$this->PS->line(6, $lin -5, $linecol, $lin -5, 1);
        $this->PS->line(26, $this->lin -5, $this->LineSize, $this->lin -5, 1);
        $this->lin -= 15;
      }
    }
  }
}
