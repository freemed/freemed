<?php
	// $Id$

LoadObjectDependency('Agata.AgataReport');

class AgataXml extends AgataReport
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
  
  function SlashTag($tag)
  {
    if (strpos($tag, ' '))
    {
      for ($n=0; $n<=strlen($tag); $n++)
      {
        if (substr($tag,$n,1)==' ')
          break;
        $chars .= substr($tag,$n,1);
      }
      $tag = $chars . '>';
    }
                                                                                                                 
    return substr($tag,0,1) . '/' . substr($tag,1);
  }
  
  function ChangeQuote($tag)
  {
    return str_replace('"', '\"', $tag);
  }

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
    
    if ($CountBreaks > 0)
    {
      $MarginBreaks = ($CountBreaks * 2) +2;
    }
    else
    {
      $MarginBreaks = 2;
    }

    foreach ($this->MaxLen as $col => $Maior)
    {
      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$col])) //aquipbreak
      {
        $Cols += $Maior;
	$TdCols ++;
      }
    }
    $TdCols += $CountBreaks;

    $ReportTag = $this->agataConfig['xml']['ReportTag'];
    $TitleTag  = $this->agataConfig['xml']['TitleTag'];
    $HeaderTag = $this->agataConfig['xml']['HeaderTag'];
    $HeaderRow = $this->agataConfig['xml']['HeaderRow'];
    $HeaderCol = $this->ChangeQuote($this->agataConfig['xml']['HeaderCol']);
    $DataTag   = $this->agataConfig['xml']['DataTag'];
    $DataRow   = $this->agataConfig['xml']['DataRow'];
    $DataCol   = $this->ChangeQuote($this->agataConfig['xml']['DataCol']);
    $FooterTag = $this->agataConfig['xml']['FooterTag'];
    $TotalRow  = $this->agataConfig['xml']['TotalRow'];
    $TotalCol  = $this->ChangeQuote($this->agataConfig['xml']['TotalCol']);
    $GroupTag  = $this->ChangeQuote($this->agataConfig['xml']['GroupTag']);
    
    $ReportTag_ = $this->SlashTag($ReportTag);
    $TitleTag_  = $this->SlashTag($TitleTag);
    $HeaderTag_ = $this->SlashTag($HeaderTag);
    $HeaderRow_ = $this->SlashTag($HeaderRow);
    $DataTag_   = $this->SlashTag($DataTag);
    $DataRow_   = $this->SlashTag($DataRow);
    $FooterTag_ = $this->SlashTag($FooterTag);
    $TotalRow_  = $this->SlashTag($TotalRow);
    $GroupTag_  = $this->SlashTag($GroupTag);


    fputs($fd, "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"no\"?>\n");
    fputs($fd, "$ReportTag\n");

    fputs($fd, "  $TitleTag $ReportName $TitleTag_\n");

    if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0']))) //aquipbreak
    {
      fputs($fd, "  $HeaderTag\n");
      fputs($fd, "    $HeaderRow\n");
      for ($z=0; $z<=count($this->Columns) -1; $z++)
      {
        $colnum = $z +1;
        $Column = trim($this->Columns[$z]);
	eval("\$var = \"$HeaderCol\";");
	fputs($fd, "      $var\n");
      }
      fputs($fd, "    $HeaderRow_\n");
      fputs($fd, "  $HeaderTag_\n");
      fputs($fd, "  $DataTag\n");
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
	
	//var_dump($this->Headers);
	if ($this->Headers)
          $ReverseHeaders = array_reverse($this->Headers, true);

	if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) //aquipbreak
        {
	  $colnum = $y;
	  $align = ($isRight) ? 'right' : 'left';
	  
          $stringline .=  $this->Replicate(' ', $MarginBreaks);
	  
	  eval("\$var = \"$DataCol\";");
	  $stringline .= "    $var\n";
	  //$stringline .= "    <col type=\"data\" align=\"right\" colnum=\"$colnum\"> $QueryCell </col>\n";
        }
      }

      if (($this->BreakMatrix) && ($break != '0'))
      {
	$chaves = array_reverse(array_keys($this->BreakMatrix));
	fputs($fd, $this->Replicate(' ', $MarginBreaks));
        fputs($fd, "$DataTag_\n");
	foreach ($chaves as $chave)
        {
	  //-----------------------------------------
	  $FinalBreak = $this->EqualizeBreak($chave);
	  //-----------------------------------------
	  if ($this->HasFormula[$chave])
	  {
	    fputs($fd, $this->Replicate(' ', $MarginHeader +2));
	    fputs($fd, "$FooterTag\n");
	    foreach ($FinalBreak as $FinalBreakLine)
	    {
	      $w = 0;
	      fputs($fd, $this->Replicate(' ', $MarginHeader +2));
              fputs($fd, "  $TotalRow\n");

	      foreach($FinalBreakLine as $content)
	      {
	        $w ++;
	        if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
                {
	          $content = trim($content);
	          if ($content)
	          {
		    $tmp = explode(':', $content);
		    $formula = trim($tmp[0]);
		    $Value = trim($tmp[1]);
		    fputs($fd, $this->Replicate(' ', $MarginHeader +2));
		    $colnum = $w;
  	            //fputs($fd, "    <col type=\"total\" formula=\"$formula\" colnum=\"$colnum\"> $Value </col>\n");
		    eval("\$var = \"$TotalCol\";");
		    fputs($fd, "    $var\n");
		  }
	        }
	      }
	      fputs($fd, $this->Replicate(' ', $MarginHeader +2));
              fputs($fd, "  $TotalRow_\n");
	    }
	    fputs($fd, $this->Replicate(' ', $MarginHeader +2));
  	    fputs($fd, "$FooterTag_\n");
	  }
          
	  // headers index is every (0, 1, 2, ...)
	  // chave may be any value (0, 2, ...)
	  if ($OpenHeaders)
	  {
	    if ($ReverseHeaders)
	    {
	      $key = $this->Association[$chave];
	      if ($chave != '0')
	      {
	        $ReverseHeader = $ReverseHeaders[$key];
	        $ReverseHeaders[$key] = null;
	      
	        $MarginHeader = ($key *2) +2;
	        fputs($fd, $this->Replicate(' ', $MarginHeader));
	        fputs($fd, "$GroupTag_\n");
	        $MarginHeader = (($key-1) *2) +2;
	      }
	    }
	  }
        }
      }

      // if break has changed.
      if (($this->Headers) && ($break != '0'))
      {
	if ($OpenHeaders)
	{
	  foreach ($ReverseHeaders as $key => $ReverseHeader)
	  {
	    if ($ReverseHeader)
	    {
	      if ((!$this->Breaks['0']) || ($key != '0'))
	      {
	        $MarginHeader = ($key *2) +2;
	        fputs($fd, $this->Replicate(' ', $MarginHeader));
	        fputs($fd, "$GroupTag_\n");
	      }
	    }
	  }
	}

	foreach ($this->Headers as $nCountBreak => $Header)
	{
	  $MarginHeader = ($nCountBreak * 2) +2;
	  $OpenHeaders = true;

	  fputs($fd, $this->Replicate(' ', $MarginHeader));
	  $GroupLabel = trim($Header);
          eval("\$var = \"$GroupTag\";");
	  fputs($fd, "$var\n");
	}
	
	fputs($fd, $this->Replicate(' ', $MarginBreaks));
	fputs($fd, "$HeaderTag\n");
	fputs($fd, $this->Replicate(' ', $MarginBreaks));
	fputs($fd, "  $HeaderRow\n");
	
	for ($z=0; $z<=count($this->Columns) -1; $z++)
        {
          $Column = trim($this->Columns[$z]);
	  if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)])) //aquipbreak
	  {	  
	    fputs($fd, $this->Replicate(' ', $MarginBreaks));
            $colnum = $z +1;
	    //fputs($fd, "    <col type=\"header\" colnum=\"$colnum\"> $Column </col>\n");
	    eval("\$var = \"$HeaderCol\";");
	    fputs($fd, "    $var\n");
	  }
        }
	fputs($fd, $this->Replicate(' ', $MarginBreaks));
	fputs($fd, "  $HeaderRow_\n");
	fputs($fd, $this->Replicate(' ', $MarginBreaks));
	fputs($fd, "$HeaderTag_\n");
	fputs($fd, $this->Replicate(' ', $MarginBreaks));
	fputs($fd, "$DataTag\n");

      }
      if ($this->ShowDataColumns)
      {
        if (trim($stringline))
        {
	  fputs($fd, $this->Replicate(' ', $MarginBreaks));
	  fputs($fd, "  $DataRow\n");
          fputs($fd, $stringline);
	  fputs($fd, $this->Replicate(' ', $MarginBreaks));
          fputs($fd, "  $DataRow_\n");
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
      fputs($fd, $this->Replicate(' ', $MarginBreaks));
      fputs($fd, "$DataTag_\n");

      foreach ($chaves as $chave)
      {
        //-----------------------------------------
        $FinalBreak = $this->EqualizeBreak($chave);
        //-----------------------------------------
	//fputs($fd, $this->Replicate(' ', $MarginBreaks));
	if (($this->HasFormula[$chave]) || ($chave =='0'))
	{
	  fputs($fd, $this->Replicate(' ', $MarginHeader +2));
          fputs($fd, "$FooterTag\n");

	  foreach ($FinalBreak as $FinalBreakLine)
	  {
	    $w = 0;
	  
	    fputs($fd, $this->Replicate(' ', $MarginHeader +2));
	    fputs($fd, "  $TotalRow\n");

	    foreach($FinalBreakLine as $content)
	    {
	      $w ++;
	      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
              {
	        $content = trim($content);
	        if ($content)
	        {
		  $tmp = explode(':', $content);
		  $formula = trim($tmp[0]);
		  $Value = trim($tmp[1]);
		  fputs($fd, $this->Replicate(' ', $MarginHeader +2));
		  $colnum = $w;
	          //fputs($fd, "    <col type=\"total\" formula=\"$formula\" colnum=\"$colnum\"> $Value </col>\n");
	          eval("\$var = \"$TotalCol\";");
		  fputs($fd, "    $var\n");
	        }
	      }
	    }
	    fputs($fd, $this->Replicate(' ', $MarginHeader +2));
 	    fputs($fd, "  $TotalRow_\n");
	  }
	  fputs($fd, $this->Replicate(' ', $MarginHeader +2));
	  fputs($fd, "$FooterTag_\n");
	}

	if ($OpenHeaders)
	{
	  if ($ReverseHeaders)
	  {
	    $key = $this->Association[$chave];
	    if ($chave != '0')
	    {
	      $ReverseHeader = $ReverseHeaders[$key];
	      $ReverseHeaders[$key] = null;
	      
	      $MarginHeader = ($key *2) +2;
	      fputs($fd, $this->Replicate(' ', $MarginHeader));
	      fputs($fd, "$GroupTag_\n");
	      $MarginHeader = (($key-1) *2) +2;
	    }
	  }
	}	
      }
    }

    // if break has changed.
    if ($this->Headers)
    {
      if ($OpenHeaders)
      {
        foreach ($ReverseHeaders as $key => $ReverseHeader)
        {
	  if ($ReverseHeader)
	  {
	    //if ((!$this->Breaks['0']) || ($key != '0'))
	    {
	      $MarginHeader = ($key *2) +2;
	      fputs($fd, $this->Replicate(' ', $MarginHeader));
	      fputs($fd, "$GroupTag_\n");
	    }
	  }
        }
      }
    }

    /******************
    END OF LAST PROCESS
    *******************/

    if (!$this->BreakMatrix)
    {
      fputs($fd, "  $DataTag_\n");
    }
    fputs($fd, "$ReportTag_\n");
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
