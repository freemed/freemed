<?php
	// $Id$

LoadObjectDependency('Agata.AgataCore');

/***********************************************************/
/* Classe que cria Merge Form                              */
/* Linguagem PHP-GTK                                       */
/* Autor: Pablo Dall'Oglio                                 */
/* Última ateração em 15 Agosto 2003 por Pablo             */
/***********************************************************/
class AgataMerge extends AgataCore
{

/*  $HelpLines[] = "\n     " . Trans::Translate('Here you may write any text. When you want to insert a variable from a SQL query, please click on the ComboBox above the text area you are typing - the field you have selected will be inserted on the current cursor position in the text area.')."\n";
    $this->SubSQL->insert($a, null, null, Trans::Translate('Type here the sub query, a select that will use any variable from a SQL main query ($table_field).') . "\n");
    Trans::Translate('Type here the sub query, a select that will use any variable from a SQL main query ($table_field).')), false, false);
*/

  function AgataMerge($agataDB, $agataConfig, $FileName, $CurrentQuery, $posAction, $LeftMargin, $TopMargin, $Spacing, $Paging)
  {
    $this->agataDB = $agataDB;
    $this->agataConfig = $agataConfig;
    $this->FileName = $FileName;
    $this->CurrentQuery = $CurrentQuery;
    $this->Query = $this->CurrentQuery->Query;
    $this->posAction = $posAction;
    $this->LeftMargin = $LeftMargin;
    $this->TopMargin = $TopMargin;
    $this->Spacing = $Spacing;
    $this->Paging = $Paging;

    $this->PageHeight = 792; // Letter=792, A4=840 (inches * 72)
    $this->PageWidth  = 612; // Letter=612, A4=?   (inches * 72)
  }

  function MergePS($textMerge, $SubQueries)
  {
    // Patch to deal with multiple sub-queries (jeff@ourexchange.net)
    if (is_array($SubQueries)) {
      $_SubQueries = $SubQueries;
    } else {
      // Legacy non-multiple support
      $_SubQueries = array ($SubQueries);
    }
    $current_subquery = 0;
  
    $PsSoft = $this->agataConfig['app']['PsSoft'];
    $LineLen = $this->agataConfig['ps']['PsLineLen'];
    
    $Is_SubSQL = false;

    $fan06 = "font/Arial findfont 6 scalefont setfont\n";
    $fan08 = "font/Arial findfont 8 scalefont setfont\n";
    $fan10 = "font/Arial findfont 10 scalefont setfont\n";
    $fan12 = "font/Arial findfont 12 scalefont setfont\n";
    $fan14 = "font/Arial findfont 14 scalefont setfont\n";
    $fan16 = "font/Arial findfont 16 scalefont setfont\n";
    $fan18 = "font/Arial findfont 18 scalefont setfont\n";

    $fab08 = "font/Arial-bold findfont 8 scalefont setfont\n";
    $fab10 = "font/Arial-bold findfont 10 scalefont setfont\n";
    $fab12 = "font/Arial-bold findfont 12 scalefont setfont\n";
    $fab14 = "font/Arial-bold findfont 14 scalefont setfont\n";
    $fab16 = "font/Arial-bold findfont 16 scalefont setfont\n";
    $fab18 = "font/Arial-bold findfont 18 scalefont setfont\n";

    $ftn08  = "font/Times findfont 8 scalefont setfont\n";
    $ftn10  = "font/Times findfont 10 scalefont setfont\n";
    $ftn12  = "font/Times findfont 12 scalefont setfont\n";
    $ftn14  = "font/Times findfont 14 scalefont setfont\n";
    $ftn16  = "font/Times findfont 16 scalefont setfont\n";
    $ftn18  = "font/Times findfont 18 scalefont setfont\n";

    $ftb08  = "font/Times-bold findfont 8 scalefont setfont\n";
    $ftb10  = "font/Times-bold findfont 10 scalefont setfont\n";
    $ftb12  = "font/Times-bold findfont 12 scalefont setfont\n";
    $ftb14  = "font/Times-bold findfont 14 scalefont setfont\n";
    $ftb16  = "font/Times-bold findfont 16 scalefont setfont\n";
    $ftb18  = "font/Times-bold findfont 18 scalefont setfont\n";

    $fcn08  = "font/Courier findfont 8 scalefont setfont\n";
    $fcn10  = "font/Courier findfont 10 scalefont setfont\n";
    $fcn12  = "font/Courier findfont 12 scalefont setfont\n";
    $fcn14  = "font/Courier findfont 14 scalefont setfont\n";
    $fcn16  = "font/Courier findfont 16 scalefont setfont\n";
    $fcn18  = "font/Courier findfont 18 scalefont setfont\n";

    $fcb08  = "font/Courier-bold findfont 8 scalefont setfont\n";
    $fcb10  = "font/Courier-bold findfont 10 scalefont setfont\n";
    $fcb12  = "font/Courier-bold findfont 12 scalefont setfont\n";
    $fcb14  = "font/Courier-bold findfont 14 scalefont setfont\n";
    $fcb16  = "font/Courier-bold findfont 16 scalefont setfont\n";
    $fcb18  = "font/Courier-bold findfont 18 scalefont setfont\n";

    $line1 = "1 setlinewidth  \n {$this->LeftMargin} \$lin moveto \n $LineLen \$lin lineto \n stroke \n";
    $line2 = "2 setlinewidth  \n {$this->LeftMargin} \$lin moveto \n $LineLen \$lin lineto \n stroke \n";
    $line3 = "3 setlinewidth  \n {$this->LeftMargin} \$lin moveto \n $LineLen \$lin lineto \n stroke \n";

    $pagebreak = "PAGEBREAK\n";

    $copy  = "\251"; // Copyright
    $s14   = "\274"; // 1/4
    $s12   = "\275"; // 1/2
    $s34   = "\276"; // 3/4

    $sup1  = "\271"; // 1 sobrescrito
    $sup2  = "\262"; // 2 sobrescrito
    $sup3  = "\263"; // 3 sobrescrito
    $supo  = "\272"; // o sobrescrito
    $supa  = "\252"; // a sobrescrito

    $para   = "\247"; // Paragrafo
    $iesp   = "\277"; // Interrogação Espanhol
    $mame   = "\261"; // Mais ou Menos
    $reco   = "\256"; // Registrado
   
    $year = date('Y');
    $month = date('m');
    $day = date('d');
    LoadObjectDependency('Agata.Trans');
    $monthname = Trans::Translate(trim(date('F')));
    $weekday = Trans::Translate(trim(date('l')));

    $Lines = explode("\n", trim($textMerge));
    $diff = number_format($this->Spacing, 2, ',', '');

    $myAcentos = fopen(dirname(dirname(__FILE__)).'/accents.tpl',"r");
    while(!feof($myAcentos))
    {
      $linha = fgets($myAcentos, 700);
      $acentos = $acentos . $linha;
    }
    fclose($myAcentos);

    Wait::On();

    global $fd;
    $fd = fopen ($this->FileName, "w");
    $TX = "/sem {gsave  /Arial-Bold findfont 10 scalefont setfont (texto negrito) show /Arial-Bold findfont 10 scalefont setfont grestore} def";

    fwrite($fd, "%!PS-Adobe-3.0 \n");
    fwrite($fd, "%%%Creator: Agata Report \n");
    fwrite($fd, "%%Title: " . $this->FileName . "\n");

    fwrite($fd, $acentos . "\n");
    fwrite($fd, "/cm {26 mul} def  \n $TX");
    fwrite($fd, "/Arial findfont 10 scalefont setfont \n");
    $page = 0;

    for ($x=0; $x<count($this->Query); $x++)
    {
      $QueryLine = $this->Query[$x];

      for ($y=1; $y<=count($QueryLine); $y++)
      {
        $querycell = $QueryCell = $QueryLine[$y];

        $MyVar = '$var' . $y;
        eval ("$MyVar = \"$querycell\";");
      }

      // Início de Página
      $page ++;
      fwrite($fd, '%%Page: ' . $page . ' ' . $page . "\n");

      if ($this->Paging)
      {
        fwrite($fd, "520 814 moveto \n");
        fwrite($fd, '(' . Trans::Translate('Page') . ": $page ) show \n ");
      }

      $lin = $this->PageHeight - $this->TopMargin;

      fwrite($fd, "{$this->LeftMargin} $lin moveto \n ");
      $lineN = 0;
      foreach ($Lines as $Line)
      {
        if ((strlen($Lines)>0) && (!$Is_SubSQL))
        {
          $lineN ++;
          eval ("\$Line = \"$Line\";");
        }

        // Decide whether we need to skip pages here
	if ($lin <= $this->TopMargin or substr($Line, 0, 9)=='PAGEBREAK') {
	  // End page
	  $Line = ''; // reset line so 'PAGEBREAK' doesn't print
          fwrite($fd, "showpage \n");
          $page ++;
          fwrite($fd, '%%Page: ' . $page . ' ' . $page . "\n");
          $lin = $this->PageHeight - $this->TopMargin;
        }
      
	if (substr($Line, 0, 2) == "//") //COMENTARIO
        {
          fwrite($fd, "%" . $Line . "\n");
        }
        elseif (substr($Line, 0, 4) == "font") //FONTE
        {
          fwrite($fd, strstr($Line,"/") . "\n");
        }
        elseif (substr($Line, 2, 7) == "setline") //LINHA
        {
          $aux = $lin;
          $lin = $lin + (8/10);
          $lin = number_format($lin, 2, '.', '');
          eval ("\$Line = \"$Line\";");
          $lin = $aux;
          fwrite($fd, $Line . "\n");
	  $lin -= $diff;
        }
        elseif (substr($Line, 0, 10) == '>>>SUB-SQL')
        {
	  // Patch to deal with multiple sub-queries
          $textSubQuery = $_SubQueries[$current_subquery];
          $current_subquery = $current_subquery + 1;  
	  
          $Is_SubSQL = true;

          if (substr($textSubQuery, 0, 2) != '--')
          {
            eval ("\$sql2 = \"$textSubQuery\";");

	    $conn = CreateObject('Agata.Connection');
            if ($conn->Open($this->agataDB))
	    {
              $Subquery = $conn->CreateQuery($sql2);
              $Subresult = $Subquery->result;
              $SubProcessed = $Subquery->result;
	    }

            if (!$SubProcessed)
            {
              return false;
            }
	    $SubColCount = $Subquery->GetColumnCount();
	    $conn->Close();
          }
        }
        elseif (substr($Line, 0, 10) == '<<<SUB-SQL')
        {
          $Is_SubSQL = false;
	  for ($i=0; $i<=$SubColCount; $i++) {
            unset(${'subfield'.$i});
	  }
        }
        elseif (eregi('#set', $Line))
	{
	  $this->Format($fd, $lin, $Line);
	  $lin -= $diff;
	}
        elseif (ereg('#tab', $Line) && (!$Is_SubSQL))
        {
	  $this->Tab($fd, $lin, $Line);
	  $lin -= $diff;
        }
        else
        {
          if ($Is_SubSQL)
          {
            $i = 0;
            if ($Subresult)
              while($Subrow=$Subresult->fetchRow())
              {
                for ($Subcol=1; $Subcol<=$SubColCount; $Subcol++)
                {
                  $SubConteudo = trim($Subrow[$Subcol-1]);
                  $SubMyVar = '$subfield' . $Subcol;
                  eval ("$SubMyVar = \"$SubConteudo\";"); //cada pagina
                }
                $i ++;

		eval ("\$Line_ = \"$Line\";");
		
                // Decide whether we need to skip pages here
                if ($lin <= $this->TopMargin or substr($Line, 0, 9)=='PAGEBREAK') {
                  // End page
                  fwrite($fd, "showpage \n");
                  $page ++;
                  fwrite($fd, '%%Page: ' . $page . ' ' . $page . "\n");
                  $lin = $this->PageHeight - $this->TopMargin;
                }
      
		if (strpos($Line_, '#set') > 0)
		{
		  $this->Format($fd, $lin, $Line_);
		  $lin -= $diff;
		}
		elseif ((strpos($Line_, '#tab') > 0) || (substr($Line_,0,1) =='#'))
		{
		  $this->Tab($fd, $lin, $Line_);
		  $lin -= $diff;
		}
		else
		{
                  $aux = $lin;
                
                  $lin = number_format($lin, 2, '.', '');
                  fwrite($fd, "{$this->LeftMargin}  $lin moveto \n ");
                  fwrite($fd, '(' . $Line_  . ") show \n ");
                  $lin = $aux;
                  $lin -= $diff;
		}
              }

          }
          else
          {
            $aux = $lin;
            $lin = number_format($lin, 2, '.', '');
            fwrite($fd, "{$this->LeftMargin}  $lin moveto \n ");
	    fwrite($fd, '(' . $Line  . ") show \n ");

	    //align_justify($fd, 5, $lin, 400, 15, 20, 0, $Line, 'Arial', 12);

	    $lin = $aux;
            $lin -= $diff;
          }
        }
      }

      fwrite($fd, "showpage \n"); // grava no arquivo PS

      // Patch to enable multiple sub queries...
      // Have to reset at the end of a page, otherwise there are
      // issues, as it will continue to increment
      $current_subquery = 0;
    }
    fclose($fd);
    Wait::Off();

    if ($this->posAction)
    {
      // If this isn't in here, it borks on no GTK GUI
      OpenReport($this->FileName, $this->agataConfig);
      $obj = &$this->posAction[0];
      $att = &$this->posAction[1];
                                                                                                                 
      $obj->{$att}();
    }

    return true;
  }
  
  function Format($fd, $lin, $Line)
  {
    if (ereg('#set', $Line))
    {
      $pos = strpos($Line, '#set');
      $endpos = strpos($Line, ' ', $pos);
      if (($endpos < $pos) or ($endpos < 1)) $endpos = strlen($Line);
      $format = trim(substr($Line, $pos+4, ($endpos-$pos)-4));// take out "#'
      $line1 = trim(substr($Line, 0, $pos));
      $line2 = trim(substr($Line, $endpos));
      //print "format: line = $Line\n";
      //print "format: line1 = $line1, line2 = $line2, pos = $pos, endpos = $endpos, format = $format\n";

      if ($line1)
        fwrite($fd, "($line1) show \n ");

      // Magic lines
      fwrite($fd, $this->FormatRewriteCallback($format, $lin));
      //print "  - control($format) = ".$control[$format]."\n";
      
      if (($line2) and ereg('#tab', $line2))
      {
        if (ereg('#set', $line2) and (strpos($line2, '#set') < strpos($line2, '#tab')))
	{
	  //print "format: calling format on line fragment $line2\n";
          $this->Format($fd, $lin, $line2);
	}
	else
	{
          //print "format: calling tab on fragment $line2 \n";
          $this->Tab($fd, $lin, $line2);
	}
      }	
      else
      {
        fwrite($fd, "($line2) show \n ");
      }
      
        //fwrite($fd, "($line2) show \n ");
    }
  }


  function Tab($fd, $lin, $Line)
  {
    if (ereg('#tab', $Line))
    {
      $pos = strpos($Line,  '#tab');
      $line1 = trim(substr($Line, 0, $pos));
      $line2 = trim(substr($Line, $pos+7));
      //print "tab: line1 = $line1, line2 = $line2, format = $format\n";

      if ($line1)
        fwrite($fd, "($line1) show \n ");
      fwrite($fd, substr($Line, $pos+4, 3) . " $lin moveto \n ");
     
     //print "line2 = $line2, pos(#set) = ".strpos($line2,'#set').", pos(#tab) = ".strpos($line2, '#tab')."\n";
      if (($line2) and ereg('#set', $line2))
      {
	//print "tab: calling format on line fragment $line2\n";
        $this->Format($fd, $lin, $line2);
      }
      elseif (($line2) && ((strpos($line2, '#tab') > 0) || (substr($line2,0,1) =='#')))
      {
	//print "tab: calling tab on line fragment $line2\n";
        $this->Tab($fd, $lin, $line2);
      }
      else
      {
        fwrite($fd, "($line2) show \n ");
      }
      
        //fwrite($fd, "($line2) show \n ");
    }
  }


  function HelpSubSQL()
  {
    require_once('HelpText.class');

    $HelpLines[] = ' 1 - ' . Trans::Translate('You must insert a SubQuerySession').";\n";
    $HelpLines[] = ' 2 - ' . Trans::Translate('Insert $subfield1, $subfield2 and so on, all in one line').";\n";
    $HelpLines[] = ' 3 - ' . Trans::Translate('this variables above will be converted into SubQuery results').";\n";
    $HelpLines[] = ' 4 - ' . Trans::Translate('This line with SubQuery variables will be replicated according with SubSql results').";\n";
    $HelpLines[] = ' 5 - ' . Trans::Translate('Insert EndOfSubQuery').";\n";

    HelpWindow::HelpText($HelpLines, Trans::Translate('HowTo: SubQueries'));
  }

  function FormatRewriteCallback ( $format, $lin ) {
    // Do a basic lookup for font strings
    $font_lookup = array (
    'fan06' => "/Arial findfont 6 scalefont setfont\n",
    'fan08' => "/Arial findfont 8 scalefont setfont\n",
    'fan10' => "/Arial findfont 10 scalefont setfont\n",
    'fan12' => "/Arial findfont 12 scalefont setfont\n",
    'fan14' => "/Arial findfont 14 scalefont setfont\n",
    'fan16' => "/Arial findfont 16 scalefont setfont\n",
    'fan18' => "/Arial findfont 18 scalefont setfont\n",

    'fab08' => "/Arial-bold findfont 8 scalefont setfont\n",
    'fab10' => "/Arial-bold findfont 10 scalefont setfont\n",
    'fab12' => "/Arial-bold findfont 12 scalefont setfont\n",
    'fab14' => "/Arial-bold findfont 14 scalefont setfont\n",
    'fab16' => "/Arial-bold findfont 16 scalefont setfont\n",
    'fab18' => "/Arial-bold findfont 18 scalefont setfont\n",

    'ftn08'  => "/Times findfont 8 scalefont setfont\n",
    'ftn10'  => "/Times findfont 10 scalefont setfont\n",
    'ftn12'  => "/Times findfont 12 scalefont setfont\n",
    'ftn14'  => "/Times findfont 14 scalefont setfont\n",
    'ftn16'  => "/Times findfont 16 scalefont setfont\n",
    'ftn18'  => "/Times findfont 18 scalefont setfont\n",

    'ftb08'  => "/Times-bold findfont 8 scalefont setfont\n",
    'ftb10'  => "/Times-bold findfont 10 scalefont setfont\n",
    'ftb12'  => "/Times-bold findfont 12 scalefont setfont\n",
    'ftb14'  => "/Times-bold findfont 14 scalefont setfont\n",
    'ftb16'  => "/Times-bold findfont 16 scalefont setfont\n",
    'ftb18'  => "/Times-bold findfont 18 scalefont setfont\n",

    'fcn08'  => "/Courier findfont 8 scalefont setfont\n",
    'fcn10'  => "/Courier findfont 10 scalefont setfont\n",
    'fcn12'  => "/Courier findfont 12 scalefont setfont\n",
    'fcn14'  => "/Courier findfont 14 scalefont setfont\n",
    'fcn16'  => "/Courier findfont 16 scalefont setfont\n",
    'fcn18'  => "/Courier findfont 18 scalefont setfont\n",

    'fcb08'  => "/Courier-bold findfont 8 scalefont setfont\n",
    'fcb10'  => "/Courier-bold findfont 10 scalefont setfont\n",
    'fcb12'  => "/Courier-bold findfont 12 scalefont setfont\n",
    'fcb14'  => "/Courier-bold findfont 14 scalefont setfont\n",
    'fcb16'  => "/Courier-bold findfont 16 scalefont setfont\n",
    'fcb18'  => "/Courier-bold findfont 18 scalefont setfont\n",
    );
    foreach ($font_lookup AS $k => $v) {
      if ($k == $format) { return $v; }
    }
    // Additional callback rules go here
    if (substr($format, 0, 5) == 'image') {
      // Handle images (EPS format only for now)
      // format is
      //   #setimage:name=something.eps&tab=20&scalex=1&scaley=1&type=eps
      // defaults are
      //   line = (current line)
      //   tab = 0
      //   scalex = 1
      //   scaley = 1
      //   type = eps
      $scalex = $scaley = 1; $tab = 0; $type = 'eps'; $line = $lin;
      $params = explode('&', substr($format, -(strlen($format)-6)));
      foreach ($params as $param) {
        list ($k, $v) = explode('=', $param);
	${$k} = $v;
      }
      // Set translation, etc parameters
      $buffer .= "%% Image loading code\n".
        "gsave %% start level x\n".
	"/showpage{}\n".
	"/erasepage()\n".
	"/copypage{}\n".
	($tab + 0)." ".($line + 0)." translate\n".
	($xscale + 0)." ".($yscale + 0)." scale\n".
	"\n";
      // Perform actual image import
      switch (strtolower($type)) {
        case 'eps':
	// For EPS, simply read image into file
	$fp = fopen(dirname(dirname(__FILE__)).'/images/'.$name, 'r');
	if (!$fp) {
          print "FAILED TO OPEN $name\n";
	  return '';
	}
	while (!feof($fp)) {
          $buffer .= fgets ($fp, 4096);
	}
	fclose($fp);
	break;
      }

      // End of image loading code
      $buffer .= "\n%% Restore state\ngrestore\n\n";

      // Send back image loading code
      return $buffer;
    }

    // Otherwise, if not defined, return nothing
    return '';
  } // end method FormatRewriteCallback

}

?>
