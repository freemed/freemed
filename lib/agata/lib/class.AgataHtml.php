<?php
	// $Id$

LoadObjectDependency('Agata.AgataReport');

class AgataHtml extends AgataReport
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

    $cellspacing = $this->agataConfig['html']['CellSpacing'];
    $cellpadding = $this->agataConfig['html']['CellPadding'];
    $align = $this->agataConfig['html']['Align'];
    $width = $this->agataConfig['html']['Width'];
    $border = $this->agataConfig['html']['Border'];    
    $bgcolor = $this->agataConfig['html']['BgColor'];
    
    $titlefont = $this->agataConfig['html']['TitleFont'];
    $titlecolor = $this->agataConfig['html']['TitleColor'];
    $titlebgcolor = $this->agataConfig['html']['TitleBgColor'];
    $titlefontset = TreatFont($titlefont, $titlecolor);
    $titlefont1 = $titlefontset[0];
    $titlefont2 = $titlefontset[1];

    $datafont = $this->agataConfig['html']['DataFont'];
    $datacolor = $this->agataConfig['html']['DataColor'];
    $databgcolor = $this->agataConfig['html']['DataBgColor'];
    $datafontset = TreatFont($datafont, $datacolor);
    $datafont1 = $datafontset[0];
    $datafont2 = $datafontset[1];

    $totalfont = $this->agataConfig['html']['TotalFont'];
    $totalcolor = $this->agataConfig['html']['TotalColor'];
    $totalbgcolor = $this->agataConfig['html']['TotalBgColor'];
    $totalfontset = TreatFont($totalfont, $totalcolor);
    $totalfont1 = $totalfontset[0];
    $totalfont2 = $totalfontset[1];
    
    $groupfont = $this->agataConfig['html']['GroupFont'];
    $groupcolor = $this->agataConfig['html']['GroupColor'];
    $groupbgcolor = $this->agataConfig['html']['GroupBgColor'];
    $groupfontset = TreatFont($groupfont, $groupcolor);
    $groupfont1 = $groupfontset[0];
    $groupfont2 = $groupfontset[1];

    $columnfont = $this->agataConfig['html']['ColumnFont'];
    $columncolor = $this->agataConfig['html']['ColumnColor'];
    $columnbgcolor = $this->agataConfig['html']['ColumnBgColor'];
    $columnfontset = TreatFont($columnfont, $columncolor);
    $columnfont1 = $columnfontset[0];
    $columnfont2 = $columnfontset[1];

    fputs($fd, "<html>\n");
    fputs($fd, "<body>\n");
    fputs($fd, "<table cellspacing=$cellspacing cellpadding=$cellpadding align=$align width=$width border=$border bgcolor=$bgcolor>\n");
    fputs($fd, "<thead>\n");
    fputs($fd, " <tr>\n");
    fputs($fd, "  <th align=center bgcolor=$titlebgcolor colspan=$TdCols>\n");
    fputs($fd, "  $titlefont1 $ReportName $titlefont2");
    fputs($fd, "  </th>\n");
    fputs($fd, " </tr>\n");
    fputs($fd, "</thead>\n");
    fputs($fd, "<tbody>\n");

    if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0']))) //aquipbreak
    {
      fputs($fd, "<tr bgcolor=$columnbgcolor>\n");
      for ($z=0; $z<=count($this->Columns) -1; $z++)
      {
        $Column = $this->Columns[$z];
	fputs($fd, "<td bgcolor=$columnbgcolor> $columnfont1");
        fputs($fd, trim($Column));
	fputs($fd, "$columnfont2</td>");
      }
      fputs($fd, "</tr>\n");
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
            $stringline .= "<td align=right bgcolor=$databgcolor> $datafont1 $QueryCell $datafont2</td>";
          }
          else
          {
            $stringline .= "<td align=left bgcolor=$databgcolor> $datafont1 $QueryCell $datafont2 </td>";
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
	    foreach ($FinalBreak as $FinalBreakLine)
	    {
	      $w = 0;
	      //fputs($fd, $this->Replicate(' ', $MarginBreaks));
	      
	      fputs($fd, "<tr bgcolor=$totalbgcolor>\n");
	      if ($this->ShowTotalLabel)
	      {
	        if ($chave == '0')
	          fputs($fd, "<td bgcolor=$bgcolor>&nbsp; (Grand Total)</td>");
	        else
	          fputs($fd, "<td bgcolor=$bgcolor>&nbsp; ({$this->Summary[$chave]['BeforeLastValue']})</td>");
	      
	        fputs($fd, $this->Multi("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks -1));
	      }
	      else
	      {
	         fputs($fd, $this->Multi("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks));
	      }

	      foreach($FinalBreakLine as $content)
	      {
	        $w ++;
	        if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
                {
	          if ($content)
	          {
  	            fputs($fd, "<td bgcolor=$totalbgcolor> $totalfont1 $content $totalfont2</td>\n");
	          }
	          else
	          {
  	            fputs($fd, "<td bgcolor=$totalbgcolor>&nbsp;</td>\n");
	          }
	        }
	      }
  	      fputs($fd, "</tr>\n");
	    }
	  }
        }
      }

      if (($this->Headers) && ($break != '0'))
      {
	foreach ($this->Headers as $nCountBreak => $Header)
	{
	  $MarginHeader = $nCountBreak * 5;
	  
	  fputs($fd, "<tr bgcolor=$groupbgcolor>\n");
	  fputs($fd, $this->Multi("<td bgcolor=$groupbgcolor>&nbsp;</td>", $nCountBreak));
	  $resto = $TdCols - $nCountBreak;
	  $Header = trim($Header);
	  fputs($fd, "<td  bgcolor=$groupbgcolor colspan=$resto> $groupfont1 $Header $groupfont2 </td>");
	  fputs($fd, "</tr>\n");
	}

	fputs($fd, "<tr bgcolor=$columnbgcolor>\n");
        fputs($fd, $this->Multi("<td bgcolor=$bgcolor>&nbsp;</td>", $CountBreaks));
	
	for ($z=0; $z<=count($this->Columns) -1; $z++)
        {
          $Column = $this->Columns[$z];
	  if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)])) //aquipbreak
	  {	  
            fputs($fd, "<td bgcolor=$columnbgcolor> $columnfont1 $Column $columnfont2</td>\n");
	  }
        }
	fputs($fd, "</tr>\n");

      }
      if ($this->ShowDataColumns)
      {
        if (trim($stringline))
        {
	  fputs($fd, "<tr bgcolor=$databgcolor>\n");
          fputs($fd, $this->Multi("<td bgcolor=$bgcolor>&nbsp;</td>", $CountBreaks));
          fputs($fd, $stringline);
          fputs($fd, "</tr>\n");
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
	  foreach ($FinalBreak as $FinalBreakLine)
	  {
	    $w = 0;

	    fputs($fd, "<tr bgcolor=$totalbgcolor>\n");
            if ($this->ShowTotalLabel)
	    {
	      if ($chave == '0')
	        fputs($fd, "<td bgcolor=$bgcolor>&nbsp; (Grand Total)</td>");
              else	  
	        fputs($fd, "<td bgcolor=$bgcolor>&nbsp; ({$this->Summary[$chave]['LastValue']})</td>");
	      fputs($fd, $this->Multi("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks -1));
	    }
	    else
	    {
	      fputs($fd, $this->Multi("<td bgcolor=$bgcolor>&nbsp; </td>", $CountBreaks));
	    }
	    
	    foreach($FinalBreakLine as $content)
	    {
	      $w ++;
	      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))
              {
	        if ($content)
	        {
  	          fputs($fd, "<td bgcolor=$totalbgcolor aligh=left> $totalfont1 $content $totalfont2 </td>\n");
	        }
	        else
	        {
  	          fputs($fd, "<td bgcolor=$totalbgcolor aligh=right>&nbsp;</td>\n");
	        }
	      }
	    }
	    fputs($fd, "</tr>\n");
	  }
	}
      }
    }


    /******************
    END OF LAST PROCESS
    *******************/


    fputs($fd, "</tbody>\n");
    fputs($fd, "</table>\n");
    fputs($fd, "</body>\n");
    fputs($fd, "</html>\n");
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
