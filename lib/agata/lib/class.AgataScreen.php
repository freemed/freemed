<?php
	// $Id$

LoadObjectDependency('Agata.AgataReport');

class AgataScreen extends AgataReport
{
  var $Query;
  var $Maior;
  var $Columns;
  var $ColumnTypes;
  function Process($isGui = false)
  {
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
      $MarginBreaks = ($CountBreaks * 5) +10;
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
    
    for ($z=0; $z<=count($this->Columns) -1; $z++)
    {
      $Column = trim($this->Columns[$z]);
      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[($z +1)])) //aquipbreak
      {	  
	$TreeColumns[] = $Column;
	$EmptyColumns[] = '';
      }
    }

    if ((!$this->Breaks) || ((count($this->Breaks)==1) && ($this->Breaks['0']))) //aquipbreak
    {
      // usar clist
      for ($z=0; $z<=count($this->Columns); $z++)
      {
        $Column = $this->Columns[$z];
      }
      $TreeListColumns = array_merge(array(''),$TreeColumns);
      $TreeView = false;
      $Window = new GtkWindow;
      $Window->set_uposition(20,20);
      $Window->set_default_size(740,540);
      $Scroll = new GtkScrolledWindow;
      $Window->add($Scroll);

      $this->TreeList = new Listing($TreeListColumns);
      $this->TreeList->set_column_justification(0, GTK_JUSTIFY_LEFT);
      $this->TreeList->set_column_width(0, 200);
      $Scroll->add($this->TreeList);
    }
    else
    {
      $TreeListColumns = array_merge(Trans::Translate('Node'),$TreeColumns);
      $TreeView = true;
      $TdCols += $CountBreaks;
      $Window = new GtkWindow;
      $Window->set_uposition(20,20);
      $Window->set_default_size(740,540);
      $Scroll = new GtkScrolledWindow;
      $Window->add($Scroll);
      $this->TreeList = new Tree($TreeListColumns);
      $this->TreeList->set_column_justification(0, GTK_JUSTIFY_LEFT);
      $this->TreeList->set_column_width(0, 200);
      $Scroll->add($this->TreeList);
    }

    for ($x=0; $x<=count($this->Query); $x++)
    {
      $QueryLine = $this->Query[$x];

      $this->BreakMatrix = null;
      $this->Headers = null;
      $stringline = null;
      $stringline[] = '';

      for ($y=1; $y<=count($QueryLine); $y++)
      {
        $querycell = $QueryCell = $QueryLine[$y];
	
        $FormatedField = FormatField($this->agataDB, $this->agataConfig, $QueryCell, $this->ColumnTypes[$y - 1]);
        $QueryCell     = $FormatedField[0];
        $isRight       = $FormatedField[1];
	if ($isRight)
	{
	  $this->TreeList->set_column_justification($y - (($this->ShowBreakColumns) ? 0 : $CountBreaks), GTK_JUSTIFY_RIGHT);
	}

	//------------------------------------------------------------
	list($break) = $this->ProcessBreaks($querycell, $y);
	//------------------------------------------------------------

	if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && !$this->Breaks[$y])) //aquipbreak
        {
	  $stringline[] = $QueryCell;
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
	      $totalline = null;
	      $withcontent = false;

	      if ($this->ShowTotalLabel)
	      {
	        if ($chave == '0')
		  $totalline[] = ' (Grand Total)';
	        else
		  $totalline[] = ' (' . $this->Summary[$chave]['BeforeLastValue']  . ')';
	      }
	      else
	      {
		$totalline[] = '';
	      }

	      foreach($FinalBreakLine as $content)
	      {
	        $w ++;
	        if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w]))) //aquipbreak
                {
	          if ($content)
	          {
		    $totalline[] = trim($content);
		    $withcontent = true;
	          }
	          else
	          {
		    $totalline[] = null;
	          }
	        }
	      }
	      if ($withcontent)
	      {
		$bg = &new GdkColor(51400, 57054, 63993);
	        $style = null;
		$style = &new GtkStyle;
                $style->base[GTK_STATE_NORMAL] = $bg;
	        $node = $this->TreeList->AppendLineItems($this->Nodes[$this->Association[$chave]], $totalline, $this->Pixmaps['Field'], $style);	      
                $fg = &new GdkColor(51400, 0, 0);
                $style->fg[GTK_STATE_NORMAL] = $fg;
	      }
	    }
	  }
        }
      }

      if (($this->Headers) && ($break != '0'))
      {
	foreach ($this->Headers as $nCountBreak => $Header)
	{
	  $this->Nodes[$nCountBreak] = $this->TreeList->AppendSubTree(array_merge(array($Header), $EmptyColumns), $this->Pixmaps['Folder1'], $this->Nodes[$nCountBreak -1]);
	  $lastnode = $this->Nodes[$nCountBreak];
	}
      }

      if ($this->ShowDataColumns)
      {
	if ($TreeView)
	{
	  $this->TreeList->AppendLineItems($lastnode, $stringline, $this->Pixmaps['Field']);
	}
	else
	{
	  $this->TreeList->AppendLineItems($stringline);
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
	    $totalline = null;
	    $withcontent = false;

            if ($this->ShowTotalLabel)
	    {
	      if ($chave == '0')
		$totalline[] = ' (Grand Total)';
	      else
                $totalline[] = ' (' . $this->Summary[$chave]['BeforeLastValue']  . ')';	    
	    }
	    else
	    {
	      $totalline[] = null;
	    }

	    foreach($FinalBreakLine as $content)
	    {
	      $w ++;
	      if (($this->ShowBreakColumns) || (!$this->ShowBreakColumns && (!$this->Breaks[$w])))  //aquipbreak
              {
 	        if ($content)
	        {
		  $totalline[] = trim($content);
                  $withcontent = true;		
	        }
	        else
	        {
		  $totalline[] = null;
	        }
	      }
	    }
	    if ($withcontent)
	    {
	      $bg = &new GdkColor(51400, 57054, 63993);
	      $style = null;
	      $style = &new GtkStyle;
              $style->base[GTK_STATE_NORMAL] = $bg;
	      if ($TreeView)
	      {	      
                $node = $this->TreeList->AppendLineItems($this->Nodes[$this->Association[$chave]], $totalline, $this->Pixmaps['Field'], $style);
	      }
	      else
	      {
	        $node = $this->TreeList->AppendLineItems($totalline, $style);
	      }
              $fg = &new GdkColor(51400, 0, 0);
              $style->fg[GTK_STATE_NORMAL] = $fg;
	    }	    
	  }
        }
      }
    }

    /******************
    END OF LAST PROCESS
    *******************/


    $this->TreeList->columns_autosize();
    $Window->show_all();

    Wait::Off($isGui);
  
    return true;
  }
}
