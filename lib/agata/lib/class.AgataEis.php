<?php
	// $Id$

LoadObjectDependency('Agata.AgataCore');

class AgataEis extends AgataCore
{

  function AgataEis($agataConfig, $FileName, $CurrentQuery, $title, $Titlex, $Titley,
                    $Introduction, $SizeX, $SizeY, $checkData, $showValues, $RadioPs,
		    $PlottedColumns, $isPerColumns, $posAction = null)
  {
    $this->agataConfig    = $agataConfig;
    $this->FileName       = $FileName;
    $this->CurrentQuery   = $CurrentQuery;
    $this->Title          = $title;
    $this->Titlex         = $Titlex;
    $this->Titley         = $Titley;
    $this->Introduction   = $Introduction;
    $this->SizeX          = $SizeX;
    $this->SizeY          = $SizeY;
    $this->checkData      = $checkData;
    $this->showValues     = $showValues;
    $this->RadioPs        = $RadioPs;
    $this->isPerColumns   = $isPerColumns;
    $this->posAction = $posAction;

    foreach ($PlottedColumns as $PlottedColumn)
    {
      $tmp1 = explode(':', trim($PlottedColumn));
      $tmp2 = explode(' ', trim($tmp1[0]));
      $column = $tmp2[1];
      $this->PlottedColumns[$column] = 'ok';
    }

    $this->Colors = array('blue', 'orange', 'red', 'blueviolet', 'brown', 'burlywood', 'darkblue', 'darkmagenta', 'chocolate',
                         'darkolivegreen', 'darkslateblue', 'darkviolet' , 'lightcoral', 'mediumpurple', 'midnightblue',
			 'olive','orangered', 'peru', 'royalblue', 'seagreen', 'slateblue', 'springgreen', 'steelblue', 'aqua');
  }
  
  function GetData($legend = null)
  {
    if ($legend)
    {
      $tmp1 = explode(':', trim($legend));
      $tmp2 = explode(' ', trim($tmp1[0]));
      $legendcolumn = $tmp2[1];    
    }
    if ($this->isPerColumns)
      $Query = $this->CurrentQuery->InvQuery;
    else
      $Query = $this->CurrentQuery->Query;

    $ColumnNames = $this->CurrentQuery->ColumnNames;
    $count = count($Query);

    for ($n=1; $n<=$count; $n++) // loop the columns
    {
      if ($this->isPerColumns)
      {
        $chave =  $ColumnNames[$n-1];
	if ($this->PlottedColumns[$n])
        {
          $NewQuery[$chave] = $Query[$n];
        }
	$NewQueryFull[$chave] = $Query[$n];
      }
      else  // loop the lines
      {
        $chave =  $Query[$n-1][$legendcolumn];
	$colnum = 1;
	foreach ($Query[$n-1] as $column)  // subloop the columns
	{
	  if ($this->PlottedColumns[$colnum])
	  {
            $NewQuery[$chave][] = $column;
	  }
	  $NewQueryFull[$chave][] = $column;
	  $colnum ++;
	}
      }
    }
    return array($NewQuery, $NewQueryFull);
  }


  /************************************
  *  Creates Graph of Lines           *
  *************************************/
  function Lines($legend = null)
  {
    $Path = GetPath($this->FileName);
    $File = GetFileName($this->FileName);
    
    $Queries  = $this->GetData($legend);
    $matrix = $Queries[0];
    $matrixfull = $Queries[1];
    if (!$matrix)
    {
      Dialog::Aviso(Trans::Translate('There is no numeric data on query'));
      return false;
    }

    Wait::On();

    $BorderColor = $this->agataConfig['graph']['BorderColor'];
    $FontColor   = $this->agataConfig['graph']['FontColor'];

    include_once ("classes/jpgraph/jpgraph.php");
    include_once ("classes/jpgraph/jpgraph_line.php");

    // Create the graph. These two calls are always required
    $graph = CreateObject('Agata.Graph', $this->SizeX, $this->SizeY, "auto");
    $graph->SetScale("textlin");
    $i = 0;
    $count = count($this->Colors);
    foreach($matrix as $key=>$Vetor)
    {
      // Create the linear plot
      $lineplot[$i]= CreateObject('Agata.LinePlot', $Vetor);
      $lineplot[$i]->mark->SetType(MARK_CIRCLE);
      $lineplot[$i]->SetLegend($key);
      $lineplot[$i]->SetColor($this->Colors[$i]);
      $lineplot[$i]->SetWeight(2);

      if ($this->showValues)
        $lineplot[$i]->value->Show();
	
      // Add the plot to the graph
      $graph->Add($lineplot[$i]);

      $i++;
      if ($i==$count)
        $i = 0;
    }
    //$graph->SetLegends(array("Jan","Feb","Mar","Apr","May","Jun","Jul"));

    $graph->img->SetMargin(40,100,40,40);
    $graph->title->Set($this->Title);
    $graph->title->SetColor($FontColor);

    $graph->xaxis->title->Set($this->Titlex);
    $graph->yaxis->title->Set($this->Titley);
    $graph->xaxis->title->SetColor($FontColor);
    $graph->yaxis->title->SetColor($FontColor);

    $graph->title->SetFont(FF_FONT1,FS_BOLD);
    $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
    $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
    //$graph->xaxis->SetTickLabels($array_tick_);

    $graph->xaxis->SetColor($BorderColor);
    $graph->yaxis->SetColor($BorderColor);
    $graph->xaxis->SetWeight(2);
    $graph->yaxis->SetWeight(2);
    $graph->SetShadow();
    
 
    // Display the graph
    $graph->Stroke("{$Path}.{$File}.lines.png");

    $fd = fopen ($this->FileName, "w");
    fwrite($fd, "<html>\n");
    fwrite($fd, "<p align=center><font size=+2><b><center>{$this->Title}</center></b></font></p><br> \n");
    fwrite($fd, "<center>{$this->Introduction}</center><br> <br>\n");
    
    fwrite($fd, "<center><img src=\"{$Path}.{$File}.lines.png\"></center><br>\n");

    $this->HTMLTable($matrixfull, $fd);

    fwrite($fd, "</html>\n");
    fclose($fd);
    if ($this->RadioPs)
    {
      exec("./html2ps {$this->FileName} > {$this->FileName}.ps");
      OpenReport("{$this->FileName}.ps", $this->agataConfig);
    }
    else
    {
      OpenReport($this->FileName, $this->agataConfig);
    }

    if ($this->posAction)
    {
      $obj = &$this->posAction[0];
      $att = &$this->posAction[1];

      $obj->{$att}();
    }

    Wait::Off();
    return true;
  }		   
  
  /************************************
  *  Creates Graph of Bars            *
  *************************************/  
  function Bars($legend = null)
  {
    $Path = GetPath($this->FileName);
    $File = GetFileName($this->FileName);
    
    $Queries  = $this->GetData($legend);
    $matrix = $Queries[0];
    $matrixfull = $Queries[1];
    if (!$matrix)
    {
      Dialog::Aviso(Trans::Translate('There is no numeric data on query'));
      return false;
    }

    Wait::On();

    $BorderColor = $this->agataConfig['graph']['BorderColor'];
    $FontColor   = $this->agataConfig['graph']['FontColor'];    

    include_once ("classes/jpgraph/jpgraph.php");
    include_once ("classes/jpgraph/jpgraph_bar.php");

    // Create the graph. These two calls are always required
    $graph = CreateObject('Agata.Graph', $this->SizeX, $this->SizeY, "auto");
    $graph->img->SetMargin(40,80,40,40);
    $graph->SetScale("textlin");
    $graph->SetShadow();

    $i = 0;
    $count = count($this->Colors);
    foreach($matrix as $key=>$Vetor)
    {
      // Create the bar plots
      $bplot[$i] = CreateObject('Agata.BarPlot', $Vetor);
      $bplot[$i]->SetFillColor($this->Colors[$i]);

      $bplot[$i]->SetLegend($key);
      if ($this->showValues)
        $bplot[$i]->value->Show();

      $i++;
      if ($i==$count)
        $i = 0;
    }

    // Create the grouped bar plot
    $gbplot = CreateObject('Agata.GroupBarPlot', $bplot);
    //$gbplot = new AccBarPlot($bplot);
//    $gbplot->SetShadow();
    //$gbplot->value->Show();
    

    // ...and add it to the graPH
    $graph->Add($gbplot);

    $graph->title->Set($this->Title);
    $graph->xaxis->title->Set($this->TitleX);
    $graph->yaxis->title->Set($this->TitleY);
    $graph->xaxis->title->SetColor($FontColor);
    $graph->yaxis->title->SetColor($FontColor);
    $graph->xaxis->SetColor($BorderColor);
    $graph->yaxis->SetColor($BorderColor);

    $graph->title->SetFont(FF_FONT1,FS_BOLD);
    $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
    $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

    // Display the graph

    $graph->Stroke("{$Path}.{$File}.bar.png");

    $fd = fopen ($this->FileName, "w");
    fwrite($fd, "<html>\n");
    fwrite($fd, "<p align=center><font size=+2><b><center>{$this->Title}</center></b></font></p><br>");
    fwrite($fd, "<p align=center><center>{$this->Introduction}</center><br>");
    
    fwrite($fd, "<center><img src=\"{$Path}.{$File}.bar.png\"></center><br>\n");

    $this->HTMLTable($matrixfull, $fd);

    fwrite($fd, "</html>\n");
    fclose($fd);

    if ($this->RadioPS)
    {
      exec("./html2ps {$this->FileName} > {$this->FileName}.ps");
      OpenReport("{$this->FileName}.ps", $this->agataConfig);
    }
    else
    {
      OpenReport($this->FileName,  $this->agataConfig);
    }

    if ($this->posAction)
    {
      $obj = &$this->posAction[0];
      $att = &$this->posAction[1];

      $obj->{$att}();
    }

    Wait::Off();
    return;
  }



  /************************************
  *  Creates a Table with data        *
  *************************************/
  function HTMLTable($matrix, $fd)
  {
    if (!$this->checkData)
      return false;
      
    $cellspacing = $this->agataConfig['html']['CellSpacing'];
    $cellpadding = $this->agataConfig['html']['CellPadding'];
    $align = $this->agataConfig['html']['Align'];
    $width = $this->agataConfig['html']['Width'];
    $border = $this->agataConfig['html']['Border'];
    $bgcolor = $this->agataConfig['html']['BgColor'];
    
    $datafont = $this->agataConfig['html']['DataFont'];
    $datacolor = $this->agataConfig['html']['DataColor'];
    $databgcolor = $this->agataConfig['html']['DataBgColor'];
    $datafontset = TreatFont($datafont, $datacolor);
    $datafont1 = $datafontset[0];
    $datafont2 = $datafontset[1];

    $columnfont = $this->agataConfig['html']['ColumnFont'];
    $columncolor = $this->agataConfig['html']['ColumnColor'];
    $columnbgcolor = $this->agataConfig['html']['ColumnBgColor'];
    $columnfontset = TreatFont($columnfont, $columncolor);
    $columnfont1 = $columnfontset[0];
    $columnfont2 = $columnfontset[1];

    $colnum = 1;
    $maxlen = 0;
    foreach($matrix as $key=>$Vetor)
    {
      $strings[0][$colnum] = $key;
      $i = 1;
      foreach ($Vetor as $Value)
      {
        $strings[$i][$colnum] = $Value;
	$i ++;
      }
      $colnum ++;
    }

    fwrite($fd, "<CENTER>\n");
    fputs($fd, "<table cellspacing=$cellspacing cellpadding=$cellpadding align=$align width=$width border=$border bgcolor=$bgcolor>\n");

    $line = 1;
    foreach ($strings as $string)
    {
      if ($line == 1)
      {
        fputs($fd, "<tr bgcolor=$columnbgcolor>\n");
        for ($n=1; $n<$colnum; $n ++)
        {
          $Title = $string[$n];
	  fputs($fd, "<td bgcolor=$columnbgcolor align=right> $columnfont1 $Title $columnfont2</td>");
	}
        fputs($fd, "</tr>\n");
      }
      else
      {
        fputs($fd, "<tr bgcolor=$databgcolor>\n");
        for ($n=1; $n<$colnum; $n ++)
        {
	  $str = ($string[$n]) ? $string[$n] : '';
	  fwrite($fd, "  <td align=right bgcolor=$databgcolor> $datafont1 $str $datafont2</td>");
	  fwrite($fd, "  </td>\n");
        }
        fwrite($fd, "</tr>\n");
      }
      $line ++;
    }

    fwrite($fd, "</TABLE>\n");
    fwrite($fd, "</CENTER>\n");

    return true;
  }
  
/*    
    $length = $this->introduction->get_length();
    $this->introduction->delete_text(0, $length);
    $this->introduction->freeze();
    $this->introduction->thaw();
    
    while (!feof ($fd))
    {
      $buffer = fgets($fd, 5000);
      $buffer = ereg_replace("\n", '', $buffer);
      //GdkFont font, GdkColor fore, GdkColor back, string chars, [int length = -1]);
      if (substr(trim($buffer),0,4) == '&&&&')
      {
        $this->title->set_text(substr(trim($buffer),5));
      }
      elseif (substr(trim($buffer),0,4) == '%%%%')
      {
        $this->xtitle->set_text(substr(trim($buffer),5));
      }
      elseif (substr(trim($buffer),0,4) == '$$$$')
      {
        $this->ytitle->set_text(substr(trim($buffer),5));
      }
      elseif (substr(trim($buffer),0,4) == '@@@@')
      {
        $this->introduction->insert(null, null, null, substr(trim($buffer),5) . "\n");
      }      
*/
}
?>
