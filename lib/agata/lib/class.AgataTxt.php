<?php
	// $Id$

LoadObjectDependency('Agata.AgataReport');

class AgataTxt extends AgataReport
{
  var $Query;
  var $Maior;
  var $Columns;
  var $FileName;
  var $ColumnTypes;

  function Process($isGui = false)
  {
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
    
    if ($CountBreaks > 0)
    {
      $MarginBreaks = ($CountBreaks * 5);
      if ($this->ShowTotalLabel)
        $MarginBreaks += 10;
    }
    else
    {
      $MarginBreks = 0;
    }
    foreach ($this->MaxLen as $col => $Maior)
    {
      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$col])) //aquipbreak
      {
        $Cols += $Maior;
      }
    }

    fputs($fd, $this->Replicate('-', $MarginBreaks + $Cols + (2* count($this->Columns))) . "\n" );
    fputs($fd, $this->FormatString($ReportName, $MarginBreaks + $Cols, 'center') . "\n");
    fputs($fd, $this->Replicate('-', $MarginBreaks + $Cols + (2* count($this->Columns))) . "\n\n" );

    if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0']))) //aquipbreak
    {
      //fputs($fd, ' ');
      for ($z=0; $z<=count($this->Columns); $z++)
      {
        $Column = $this->Columns[$z];
        fputs($fd, $this->FormatString($Column, $this->MaxLen[$z+1] +2));
      }
      fputs($fd, "\n" . $this->Replicate('-', $MarginBreaks + $Cols + (2* count($this->Columns))) . "\n" );
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

	if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) //aquipbreak
        {
	  if ($isRight)
          {
            $stringline .= $this->FormatString($QueryCell, $this->MaxLen[$y] +2, 'right');
          }
          else
          {
            $stringline .= $this->FormatString($QueryCell, $this->MaxLen[$y] +2);
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
	  
	  if ($this->HasFormula[$chave])
	  {	
	    fputs($fd, $this->Replicate(' ', $MarginBreaks));
            fputs($fd, $this->Replicate('-', $Cols + (2* count($this->Columns))) . "\n");
	    
	    foreach ($FinalBreak as $FinalBreakLine)
	    {
	      $w = 0;
	      if ($this->ShowTotalLabel)
	      {
	        if ($chave == '0')
 	          fputs($fd, ' (Grand Total)');
	        else
	          fputs($fd, ' (' . substr($this->Summary[$chave]['BeforeLastValue'] ,0, 11) . ')');
  	        fputs($fd, $this->Replicate(' ', $MarginBreaks -14));
	      }
	      else
	      {
	        fputs($fd, $this->Replicate(' ', $MarginBreaks));
	      }

	      //fputs($fd, $this->Replicate(' ', $MarginBreaks));
	      foreach($FinalBreakLine as $content)
	      {
	        $w ++;
	        if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) //aquipbreak
                {
	          if ($content)
	          {
  	            fputs($fd, $content);
	          }
	          else
	          {
  	            fputs($fd, $this->FormatString(' ', $this->MaxLen[$w] +2, 'right'));
	          }	      
	        }
	      }
	      fputs($fd,  "\n");
	    }
	  }
        }
      }

      if (($this->Headers) && ($break != '0'))
      {
	fputs($fd, "\n");
	foreach ($this->Headers as $nCountBreak => $Header)
	{
	  $MarginHeader = $nCountBreak * 5;
	  
	  fputs($fd, "\n");
	  fputs($fd, $this->Replicate(' ', $MarginHeader));
	  fputs($fd, "$Header\n");
	  fputs($fd, $this->Replicate(' ', $MarginHeader));
	  fputs($fd, $this->Replicate('=', strlen(trim($Header))) . "\n\n");
	}

	fputs($fd, $this->Replicate(' ', $MarginBreaks));
	fputs($fd, $this->Replicate('-', $Cols + (2* count($this->Columns))) . "\n");
	
        fputs($fd, $this->Replicate(' ', $MarginBreaks));
	for ($z=0; $z<=count($this->Columns); $z++)
        {
          $Column = $this->Columns[$z];
	  if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)])) //aquipbreak
	  {	  
            fputs($fd, $this->FormatString($Column, $this->MaxLen[$z+1] +2));
	  }
        }
	fputs($fd, "\n" . $this->Replicate(' ', $MarginBreaks));
        fputs($fd, $this->Replicate('-', $Cols + (2* count($this->Columns))) . "\n" );
      }

      if ($this->ShowDataColumns)
      {
        fputs($fd, $this->Replicate(' ', $MarginBreaks) . $stringline);
        fputs($fd, "\n" );
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
	  fputs($fd, $this->Replicate(' ', $MarginBreaks));
          fputs($fd, $this->Replicate('-', $Cols + (2* count($this->Columns))) . "\n");

	  foreach ($FinalBreak as $FinalBreakLine)
          {
	    $w = 0;
            if ($this->ShowTotalLabel)
	    {
	      if ($chave == '0')
	        fputs($fd, ' (Grand Total)');
	      else
	        fputs($fd, ' (' . substr($this->Summary[$chave]['LastValue'] ,0, 11) . ')');
	      fputs($fd, $this->Replicate(' ', $MarginBreaks -14));
	    }
	    else
	    {
	      fputs($fd, $this->Replicate(' ', $MarginBreaks));
	    }

	    //fputs($fd, $this->Replicate(' ', $MarginBreaks));
	    foreach($FinalBreakLine as $content)
	    {
	      $w ++;
	      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))  //aquipbreak
              {
 	        if ($content)
	        {
   	          fputs($fd, $content);
	        }
	        else
	        {
  	          fputs($fd, $this->FormatString(' ', $this->MaxLen[$w] +2, 'right'));
	        }
	      }
	    }
	    fputs($fd,  "\n");
	  }
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
