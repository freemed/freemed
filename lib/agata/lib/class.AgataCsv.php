<?php
	// $Id$

LoadObjectDependency('Agata.AgataReport');

class AgataCsv extends AgataReport
{
  var $Query;
  var $Maior;
  var $Columns;
  var $FileName;
  var $ColumnTypes;

  function Multi($Char, $x)
  {
    for ($n=1; $n<=$x; $n++)
    {
      $result .= $Char;
    }
    return $result;
  }

  function Process($isGui = false)
  {
    $default     = $this->agataConfig['defaultConfiguration'];
    //$BrowserSoft = $this->agataConfig[$default]['BrowserSoft'];
    $SpreadSoft = $this->agataConfig[$default]['SpreadSoft'];
    $Delimiter   = $this->agataConfig['general']['Delimiter'];
    
    if ($isGui)
    {
      $InputBox = $this->InputBox;
      $ReportName = $InputBox->InputEntry->get_text();
      $InputBox->Close();
    }
    else
    {
      $ReportName = $this->ReportName;
    }

    $FunctionNames = array('count' => 'Count', 'sum' => 'Sum', 'avg' => 'Average', 'min' => 'Minimal', 'max' => 'Maximal');
    $FileName = $this->FileName;

    $fd = @fopen($FileName, "w");
    if (!$fd)
    {
      if ($isGui)
        Dialog::Aviso(Trans::Translate('File Error'));
      return false;
    }
    Wait::On($isGui);

    if ($this->Breaks)
    {
      $CountBreaks=count($this->Breaks);
      if ($this->Breaks['0'])
        $CountBreaks --;
      
      ksort($this->Breaks);
      reset($this->Breaks);
    }
    
    $MarginBreaks = $CountBreaks * 5;
    foreach ($this->MaxLen as $col => $Maior)
    {
      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$col])) //aquipbreak
      {
        $Cols += $Maior;
	$TdCols ++;
      }
    }
    $TdCols += $CountBreaks;

    fputs($fd, $this->Multi($Delimiter, $TdCols) . $ReportName. "\n");
    

    if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0']))) //aquipbreak
    {
      for ($z=0; $z<=count($this->Columns) -1; $z++)
      {
        $Column = $this->Columns[$z];
        fputs($fd, trim($Column) . $Delimiter);
      }
      fputs($fd, "\n");
    }

    for ($x=0; $x<=count($this->Query); $x++)
    {
      $QueryLine = $this->Query[$x];

      $this->BreakMatrix = null;
      $this->Headers = null;
      $stringline = '';
      for ($y=1; $y<=count($QueryLine); $y++)
      {
        $querycell = $QueryCell = $QueryLine[$y];
	
        $FormatedField = FormatField($this->agataDB, $this->agataConfig, $QueryCell, $this->ColumnTypes[$y - 1]);
        $QueryCell     = $FormatedField[0];
        $isRight       = $FormatedField[1];

	//------------------------------------------------------------
	list($break) = $this->ProcessBreaks($querycell, $y);
	//------------------------------------------------------------	

	if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y]))
        {
	  if ($isRight)
          {
            $stringline .= "$QueryCell{$Delimiter}";
          }
          else
          {
            $stringline .= "$QueryCell{$Delimiter}";
          }
        }
      }

      if (($this->BreakMatrix) && ($break != '0'))
      {
	$chaves = array_reverse(array_keys($this->BreakMatrix));

	foreach ($chaves as $chave)
        {
	  //-----------------------------------------
	  $FinalBreak = $this->EqualizeBreak($chave);
	  //-----------------------------------------

	  foreach ($FinalBreak as $FinalBreakLine)
	  {
	    $w = 0;
	    //fputs($fd, $this->Replicate(' ', $MarginBreaks));
	    
	    fputs($fd, "\n");
	    if ($this->ShowTotalLabel)
	    {
	      if ($chave == '0')
	        fputs($fd, "(Grand Total){$Delimiter}");
	      else
	        fputs($fd, "({$this->Summary[$chave]['BeforeLastValue']}){$Delimiter}");
	      
	      fputs($fd, $this->Multi($Delimiter, $CountBreaks -1));
	    }
	    else
	    {
	      fputs($fd, $this->Multi($Delimiter, $CountBreaks));
	    }

	    foreach($FinalBreakLine as $content)
	    {
	      $w ++;
	      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
              {
	        if ($content)
	        {
  	          fputs($fd, "$content{$Delimiter}");
	        }
	        else
	        {
  	          fputs($fd, "$Delimiter");
	        }
	      }
	    }
  	    //fputs($fd, "\n");
	  }
        }
      }

      if (($this->Headers) && ($break != '0'))
      {
	foreach ($this->Headers as $nCountBreak => $Header)
	{
	  $MarginHeader = $nCountBreak * 5;
	  
	  fputs($fd, "\n");
	  fputs($fd, $this->Multi($Delimiter, $nCountBreak));
	  $resto = $TdCols - $nCountBreak;
	  $Header = trim($Header);
	  fputs($fd, "$Header{$Delimiter}");
	}

	fputs($fd, "\n");
        fputs($fd, $this->Multi($Delimiter, $CountBreaks));
	
	for ($z=0; $z<=count($this->Columns) -1; $z++)
        {
          $Column = $this->Columns[$z];
	  if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)])) //aquipbreak
	  {	  
            fputs($fd, "$Column{$Delimiter}");
	  }
        }
	//fputs($fd, "\n");

      }
      if ($this->ShowDataColumns)
      {
        if (trim($stringline))
        {
	  fputs($fd, "\n");
          fputs($fd, $this->Multi($Delimiter, $CountBreaks));
          fputs($fd, $stringline);
          //fputs($fd, "\n");
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

	foreach ($FinalBreak as $FinalBreakLine)
	{
	  $w = 0;
	  fputs($fd, "\n");
          if ($this->ShowTotalLabel)
	  {
	    if ($chave == '0')
	      fputs($fd, "(Grand Total){$Delimiter}");
            else	  
	      fputs($fd, "({$this->Summary[$chave]['BeforeLastValue']}){$Delimiter}");
	    fputs($fd, $this->Multi($Delimiter, $CountBreaks -1));
	  }
	  else
	  {
	    fputs($fd, $this->Multi($Delimiter, $CountBreaks));
	  }
	    
	  foreach($FinalBreakLine as $content)
	  {
	    $w ++;
	    if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
            {
	      if ($content)
	      {
  	        fputs($fd, "$content{$Delimiter}");
	      }
	      else
	      {
  	        fputs($fd, "$Delimiter");
	      }
	    }
	  }
	  //fputs($fd, "\n");
	}
      }
    }


    /******************
    END OF LAST PROCESS
    *******************/


    fclose($fd);
    if ($this->posAction)
    {
      $this->ExecPosAction();
      OpenReport($FileName, $this->agataConfig); 
    }
    Wait::Off($isGui);
  
    return true;
  }
}
