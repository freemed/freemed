<?php
	// $Id$

  /***********************************************************/
  /* Splits SQL clauses
  /***********************************************************/
  function MyExplode($string, $pretext = false, $posAs = false)
  {
    $parentesis = 0;
    for ($n=0; $n <= strlen($string); $n++)
    {
      $char = substr($string,$n,1);
      $estoque .= $char;
      
      if ($char == '(')
        $parentesis ++;
      if ($char == ')')
        $parentesis --;
      
      if (($char == ',') && ($parentesis == 0))
      {
        $i++;
	$text = ($pretext) ? ("$pretext $i : ") : null;
	if (($posAs) && strpos($estoque,  ' as '))
	{
	  $estoques = explode(' as ', $estoque);
	  $estoque = $estoques[1];
	}
        $retorno[$i] = $text . trim(substr($estoque, 0, -1));
	$estoque = '';
      }
    }
    
    $i++;
    $text = ($pretext) ? ("$pretext $i : ") : null;
    if (strlen($estoque) >0)
      $retorno[$i] = $text . trim($estoque);
    return $retorno;
  }


  /***********************************************************/
  /* About screen
  /***********************************************************/
  function About()
  {
    $iwindow = new GtkWindow;
    $iwindow->set_title('Info');
    $iwindow->set_default_size(540, 400);
    $iwindow->set_position(GTK_WIN_POS_CENTER);

    $scrolled_win = &new GtkScrolledWindow();
    $scrolled_win->set_border_width(5);
    $scrolled_win->set_policy(GTK_POLICY_AUTOMATIC, GTK_POLICY_AUTOMATIC);

    $iwindow->add($scrolled_win);

    $HelpText = &new GtkText();
    $scrolled_win->add($HelpText);
    $scrolled_win->show();

    $strings[] = 'Agata Report 5.0';
    $strings[] = '';
    $strings[] = Trans::Translate('IT Department');
    $strings[] = '';
    $strings[] = Trans::Translate('Development by') . " Pablo Dall'Oglio";
    $strings[] = 'pablo@univates.br    -   pablo@php.net';
    $strings[] = '';
    $strings[] = 'UNIVATES - Rio Grande do Sul - Brasil';
    $strings[] = 'http://agata.codigolivre.org.br';
    $strings[] = '';
    $strings[] = Trans::Translate('See also THANKS file');
    
    $fd = fopen('THANKS', "r");
    while (!feof ($fd))
    {
      $buffer = fgets($fd, 500);
      $buffer = ereg_replace("\n", '', $buffer);
      $strings[] = $buffer;
    }
    fclose($fd);
    
    $fd = fopen('NEWS', "r");
    while (!feof ($fd))
    {
      $buffer = fgets($fd, 500);
      $buffer = ereg_replace("\n", '', $buffer);
      $strings[] = $buffer;
    }
    fclose($fd);    

    foreach ($strings as $string)
    {
      $HelpText->insert(null, null, null, "$string\n");
    }
    $HelpText->show();
    $HelpText->set_usize(364, -1);
    
    $iwindow->show();
  }


  /*******************************************************************************/
  /* Returns an simple array from a directory
  /*******************************************************************************/
  function GetSimpleDirArray($sPath, $onlydir, $filter)
  {
    $handle=opendir($sPath);
    while ($file = readdir($handle))
    {
      $nPath = "$sPath/$file";
      $is_dir = is_dir($nPath);

      if (!$onlydir)
        $is_dir = !$is_dir;

      if ($is_dir && ($file != '.') && ($file != '..'))
      {
        if ($filter)
	{
          if (strstr($file, $filter))
            $dirs[' ' . $file] = $file;
	}
	else
	{
          $dirs[' ' . $file] = $file;
	}
      }
    }
    closedir($handle);
    if ($dirs)
      ksort($dirs);
    return $dirs;
  }

  /*******************************************************************************/
  /* Generate html tag
  /*******************************************************************************/  
  function TreatFont($font, $color)
  {
    $fontset = explode('-', $font);
    $face   = $fontset[0];
    $styles = $fontset[1];
    $size   = $fontset[2];
    
    for ($n=0; $n<strlen($styles); $n++)
    {
      $style  .= '<' . substr($styles,$n,1) . '>';
      $style2 .= '</' . substr($styles,$n,1) . '>';
    }
    $result[0] = "<font color=$color face=$face size=$size> $style";
    $result[1] = "$style2 </font>";
    return $result;
  }

  /*******************************************************************************/
  /* Remove Functions from a clause
  /*******************************************************************************/   
  function RemoveFunctions($clause)
  {
    $fields = explode (',', $clause);
    foreach ($fields as $field)
    {
      $field = trim($field);
      $try1 = substr($field,0,3);
      $try2 = substr($field,0,5);
      if (($try1=='sum') || ($try1 =='avg') || ($try1=='min') || ($try1=='max') || ($try2=='count'))
      {
        return substr($return, 0, -2);
      }
      else
      {
        $return .= $field . ', ';
      }
    }
    return $return;
  }

  /*******************************************************************************/
  /* Returns the file name from a complete path
  /*******************************************************************************/  
  function GetFileName($Path)
  {
    $tmp = strrev($Path);
                                                                                                                 
    for ($n=0; $n<strlen($tmp); $n++)
    {
      if ((substr($tmp,$n,1) == '/') || (substr($tmp,$n,1) == '\\'))
      {
        $result = substr($tmp,0,$n);
	break;
      }
    }
    $result = strrev($result);
    return $result;
  }

  /*******************************************************************************/
  /* Returns a path from a complete file's location
  /*******************************************************************************/    
  function GetPath($Path)
  {
    $tmp = strrev($Path);
                                                                                                                 
    for ($n=0; $n<strlen($tmp); $n++)
    {
      if ((substr($tmp,$n,1) == '/') || (substr($tmp,$n,1) == '\\'))
      {
        $result = substr($tmp,$n);
	break;
      }
    }
    $result = strrev($result);
    return $result;
  }

  /*******************************************************************************/
  /* Returns the parameters of a query
  /*******************************************************************************/    
  function GetParameters($sql)
  {
    $paramcount=-1;
    for ( $x=1; $x<=strlen($sql); $x++ )
    {
      $caracter = substr($sql,$x,1);
      if ($caracter=='$')
      {
	$nonstop = true;
	$paramcount ++;
      }
      elseif (($caracter=="'") || ($caracter==' ') || ($caracter=='%') || ($caracter==')') || ($caracter==','))
      {
        $nonstop = false;
      }

      if ($nonstop)
	$Parameters[$paramcount] .= $caracter;
    }
    if ($Parameters)
    {
      foreach ($Parameters as $Parameter)
      {
        $Final[$Parameter] = 'x';
      }
      return array_keys($Final);;
    }
    else
    {
      return null;
    }
  }

  /*******************************************************************************/
  /* Launchs the Viewer for Report
  /*******************************************************************************/ 
  function OpenReport($FileName, $agataConfig)
  {
    $app['.txt']  = $agataConfig['app']['TxtSoft'];
    $app['.csv']  = $agataConfig['app']['SpreadSoft'];
    $app['.html'] = $agataConfig['app']['BrowserSoft'];
    $app['.ps']   = $agataConfig['app']['PsSoft'];
    $app['.pdf']  = $agataConfig['app']['PdfSoft'];
    $app['.ps']   = $agataConfig['app']['PsSoft'];
    $app['.dia']  = $agataConfig['app']['DiaSoft'];
    $app['.xml']  = $agataConfig['app']['XmlSoft'];
    
    foreach ($app as $key => $ext)
    {
      if (strstr($FileName, $key))
        $launch = $ext;
    }

    if ($launch)
    {
      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
      {
        exec("$launch $FileName");
      }
      else
      {
        exec("$launch $FileName >/dev/null &");
      }
    }
    else
    {
      Dialog::Aviso(Trans::Translate('Viewer for this file is not defined'));
    }
  }
?>
