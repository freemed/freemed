<?php
	// $Id$

LoadObjectDependency('Agata.AgataCore');

/***********************************************************/
/* Label Adress tool                                       */
/* Autor: Pablo Dall'Oglio                                 */
/* Última ateração em 15 Agosto 2003 por Pablo             */
/***********************************************************/
class AgataLabel extends AgataCore
{
  function AgataLabel($agataDB, $agataConfig, $FileName, $CurrentQuery, $posAction, $LeftMargin, $TopMargin, $Spacing, $Paging)
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
  }

    //$HelpLines[] = Trans::Translate('Here you may write any text. When you want to insert a variable from a SQL query, please click on the ComboBox above the text area you are typing - the field you have selected will be inserted on the current cursor position in the text area.');
    //$Hbox->pack_start($this->MakeFrameShadow(Trans::Translate('Please, Insert Lines and Fonts in separated lines')));
    //$Hbox->pack_start($this->MakeFrameShadow(Trans::Translate('It does not control break lines automatically')));    

  function LabelPS($textLabel)
  {
    $PsSoft = $this->agataConfig['app']['PsSoft'];
    $LineLen = $this->agataConfig['ps']['PsLineLen'];

    $fan08 = "font/Arial findfont 8 scalefont setfont\n";
    $fan10 = "font/Arial findfont 10 scalefont setfont\n";
    $fan12 = "font/Arial findfont 12 scalefont setfont\n";
    $fan14 = "font/Arial findfont 14 scalefont setfont\n";
    $fan16 = "font/Arial findfont 16 scalefont setfont\n";
    $fan18 = "font/Arial findfont 18 scalefont setfont\n";

    $fab10 = "font/Arial-bold findfont 10 scalefont setfont\n";
    $fab12 = "font/Arial-bold findfont 12 scalefont setfont\n";
    $fab14 = "font/Arial-bold findfont 14 scalefont setfont\n";
    $fab16 = "font/Arial-bold findfont 16 scalefont setfont\n";
    $fab18 = "font/Arial-bold findfont 18 scalefont setfont\n";

    $ftn10  = "font/Times findfont 10 scalefont setfont\n";
    $ftn12  = "font/Times findfont 12 scalefont setfont\n";
    $ftn14  = "font/Times findfont 14 scalefont setfont\n";
    $ftn16  = "font/Times findfont 16 scalefont setfont\n";
    $ftn18  = "font/Times findfont 18 scalefont setfont\n";

    $ftb10  = "font/Times-bold findfont 10 scalefont setfont\n";
    $ftb12  = "font/Times-bold findfont 12 scalefont setfont\n";
    $ftb14  = "font/Times-bold findfont 14 scalefont setfont\n";
    $ftb16  = "font/Times-bold findfont 16 scalefont setfont\n";
    $ftb18  = "font/Times-bold findfont 18 scalefont setfont\n";

    $fcn10  = "font/Courier findfont 10 scalefont setfont\n";
    $fcn12  = "font/Courier findfont 12 scalefont setfont\n";
    $fcn14  = "font/Courier findfont 14 scalefont setfont\n";
    $fcn16  = "font/Courier findfont 16 scalefont setfont\n";
    $fcn18  = "font/Courier findfont 18 scalefont setfont\n";

    $fcb10  = "font/Courier-bold findfont 10 scalefont setfont\n";
    $fcb12  = "font/Courier-bold findfont 12 scalefont setfont\n";
    $fcb14  = "font/Courier-bold findfont 14 scalefont setfont\n";
    $fcb16  = "font/Courier-bold findfont 16 scalefont setfont\n";
    $fcb18  = "font/Courier-bold findfont 18 scalefont setfont\n";

    $line1 = "1 setlinewidth  \n {$this->LeftMargin} \$lin moveto \n $LineLen \$lin lineto \n stroke \n";
    $line2 = "2 setlinewidth  \n {$this->LeftMargin} \$lin moveto \n $LineLen \$lin lineto \n stroke \n";
    $line3 = "3 setlinewidth  \n {$this->LeftMargin} \$lin moveto \n $LineLen \$lin lineto \n stroke \n";    

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
    $monthname = Trans::Translate(trim(date('F')));
    $weekday = Trans::Translate(trim(date('l')));

    $diff = number_format($this->Spacing, 2, ',', '');
    $Lines = explode("\n", trim($textLabel));

    $myAcentos = fopen(dirname(dirname(__FILE__)).'/accents.tpl',"r");
    while(!feof($myAcentos))
    {
      $linha = fgets($myAcentos, 700);
      $acentos = $acentos . $linha;
    }
    fclose($myAcentos);

    $fd = fopen ($this->FileName, "w");
    $TX = "/sem {gsave  /Arial-Bold findfont 10 scalefont setfont (texto negrito) show /Arial-Bold findfont 10 scalefont setfont grestore} def";

    fwrite($fd, "%!PS-Adobe-3.0 \n");
    fwrite($fd, "%%%Creator: SAGU \n");
    fwrite($fd, "%%Title: " . $this->FileName . "\n");

    fwrite($fd, $acentos . "\n");
    fwrite($fd, "/cm {26 mul} def  \n $TX");
    fwrite($fd, "/Arial findfont 10 scalefont setfont \n");

    $page = 0;

    $Column1  = 10 + $this->LeftMargin;
    $Column2  = $Column1 + 300;
    $lin_     = 850 - $this->TopMargin;
    $Column   = $Column1;
    $lin = $lin_;
    $LabelNumber = 0;

    $page ++;
    fwrite($fd, '%%Page: ' . $page . ' ' . $page . "\n");

    for ($x=0; $x<=count($this->Query); $x++)
    {
      $QueryLine = $this->Query[$x];
      $LabelNumber ++;

      for ($y=1; $y<=count($QueryLine); $y++)
      {
        $querycell = $QueryCell = $QueryLine[$y];

        $MyVar = '$var' . $y;
        eval ("$MyVar = \"$querycell\";");
      }

      // Início de Página
      if($LabelNumber == 11)
      {
        if ($Column == $Column1)
        {
	  $Column=$Column2;
	}
	else
	{
	  $page ++;
	  fwrite($fd, "showpage \n"); // grava no arquivo PS
	  fwrite($fd, '%%Page: ' . $page . ' ' . $page . "\n");
	  $Column=$Column1;
	}
	$LabelNumber = 1;
	$lin = $lin_;
      }      
      
      fwrite($fd, "$Column $lin moveto \n ");
      $lineN = 0;
      foreach ($Lines as $Line)
      {
        if (strlen($Lines)>0)
        {
          $lineN ++;
          eval ("\$Line = \"$Line\";");
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
          //echo $Line . "$lin \n";
          $aux = $lin;
          $lin = $lin + (8/10);
          $lin = number_format($lin, 2, '.', '');
          eval ("\$Line = \"$Line\";");
          //echo $Line . "$lin \n";
          $lin = $aux;
          fwrite($fd, $Line . "\n");
        }
        elseif ((strpos($Line, '#tab') > 0) || (substr($Line,0,1) =='#'))
        {
	  $this->Tab($fd, $lin, $Line);
	  $lin -= $diff;
        }	
        else
        {
          $aux = $lin;
          $lin = number_format($lin, 2, '.', '');
          fwrite($fd, "$Column  $lin moveto \n ");
          fwrite($fd, '(' . $Line  . ") show \n ");
          $lin = $aux;
          $lin -= $diff;
        }
      }
      
      $lin = $lin_ - ($LabelNumber * 72);
    }
    fwrite($fd, "showpage \n"); // grava no arquivo PS
    fclose($fd);

    OpenReport($this->FileName, $this->agataConfig);

    if ($this->posAction)
    {
      $obj = &$this->posAction[0];
      $att = &$this->posAction[1];
                                                                                                                 
      $obj->{$att}();
    }

    return true;
  }
  
  function Tab($fd, $lin, $Line)
  {
    if ((strpos($Line, '#tab') > 0) || (substr($Line,0,1) =='#'))
    {
      $pos = strpos($Line,  '#tab');
      $line1 = trim(substr($Line, 0, $pos));
      $line2 = trim(substr($Line, $pos+7));

      if ($line1)
        fwrite($fd, "($line1) show \n ");
      fwrite($fd, substr($Line, $pos+4, 3) . " $lin moveto \n ");
      
      if (($line2) && ((strpos($line2, '#tab') > 0) || (substr($line2,0,1) =='#')))
        $this->Tab($fd, $lin, $line2);
      else
      {
        fwrite($fd, "($line2) show \n ");
      }
      
        //fwrite($fd, "($line2) show \n ");
    }
  }  
}
