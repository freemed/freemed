<?
/***********************************************************/
/* Translates the message
/***********************************************************/
function _a($msg, $param1 = null, $param2 = null, $param3 = null)
{
    return Trans::Translate($msg, $param1, $param2, $param3);
}

/***********************************************************/
/* adie, used insted of die() in third part libraries
/***********************************************************/
function adie($msg)
{
    new Dialog(strip_tags($msg), true, false);
    return false;
}

/***********************************************************/
/* shortcut for var_dump
/***********************************************************/
function va($var)
{
    var_dump($var);
}

/***********************************************************/
/* Splits Conditional Highlight Formula
/***********************************************************/
function splitCondHigh($text)
{
    $pieces1 = preg_split('((if )|( = )|( then ))', $text, -1, PREG_SPLIT_NO_EMPTY);
    if (count($pieces1) == 3)
    {
        $cond_high['function'] = trim(str_replace('"', '', $pieces1[0]));
        $cond_high['result']   = trim(str_replace('"', '', $pieces1[1]));
        
        $pieces2 = explode(',', $pieces1[2]);
        foreach ($pieces2 as $fontpiece)
        {
            $fontpieces = explode('=', $fontpiece);
            $cond_high[trim($fontpieces[0])] = substr($fontpieces[1],1,-1);
        }
    }
    return $cond_high;
}

/***********************************************************/
/* Returns the numbers of a given string.
/***********************************************************/
function GetNumbers($string)
{
  return ereg_replace('[^0-9]','',$string);
}



function alltrim($string)
{
    return str_replace(' ', '', $string);
}

/***********************************************************/
/* Splits SQL clauses
/***********************************************************/
function MyExplode($string, $pretext = false, $posAs = false)
{
    $parentesis   = 0;
    $quotes       = false;
    $doublequotes = false;
    
    for ($n=0; $n <= strlen($string); $n++)
    {
        $char = substr($string,$n,1);
        $estoque .= $char;
        
        if ($char == "'")
        {
            $quotes = !$quotes;
        }
        
        if ($char == '"')
        {
            $doublequotes = !$doublequotes;
        }
        
        if ($char == '(')
        {
            $parentesis ++;
        }
        if ($char == ')')
        {
            $parentesis --;
        }
        
        if (($char == ',') and ($parentesis == 0) and ($quotes == false) and ($doublequotes == false))
        {
            $i++;
            $text = ($pretext) ? ("$pretext $i : ") : null;
            if (($posAs) and strpos($estoque,  ' as '))
            {
                $estoques = explode(' as ', $estoque);
                $estoque = $estoques[1];
            }
            elseif (($posAs) and strpos($estoque,  ' AS '))
            {
                $estoques = explode(' AS ', $estoque);
                $estoque = $estoques[1];
            }
            
            $retorno[$i] = $text . trim(substr($estoque, 0, -1));
            $estoque = '';
        }
    }
    
    $i++;
    if (strlen($estoque) >0)
    {
        $text = ($pretext) ? ("$pretext $i : ") : null;
        if (($posAs) and strpos($estoque,  ' as '))
        {
            $estoques = explode(' as ', $estoque);
            $estoque = $estoques[1];
        }
        elseif (($posAs) and strpos($estoque,  ' AS '))
        {
            $estoques = explode(' as ', $estoque);
            $estoque = $estoques[1];
        }
        
        $retorno[$i] = $text . trim($estoque);
    }

    return $retorno;
}

/***********************************************************/
/* Splits Where clauses by ' and '
/***********************************************************/
function WordExplode($separator = ' and ', $string)
{
    $parentesis = 0;
    $len = strlen($separator);
    $i = 0;
    for ($n=0; $n <= strlen($string); $n++)
    {
        $char = substr($string,$n,1);
        $estoque .= $char;
        $queue = substr($string, $n -$len, $len);

        if ((strtoupper($queue) == strtoupper($separator)) && ($parentesis == 0))
        {
            $retorno[$i] = trim(substr($estoque, 0, -$len));
            $estoque = $char;
            $i++;
        }   


        if ($char == '(')
        {
            $parentesis ++;
        }
        if ($char == ')')
        {
            $parentesis --;
        }
    }
    //$i++;
    if (strlen($estoque) >0)
    {
        $retorno[$i] = trim($estoque);
    }

    return $retorno;
}

function ArrayEreg($array, $string)
{
    foreach ($array as $value)
    {
        if (ereg($value, $string))
            return true;
    }
    return false;
}

/*******************************************************************************/
/* Returns an simple array from a directory
/*******************************************************************************/
function getSimpleDirArray($sPath, $only_files = true, $filter = '')
{
    $handle=opendir($sPath);
    
    while (($file = readdir($handle)) !== false)
    {
        $nPath   = "$sPath/$file";
        $is_dir  = is_dir($nPath);
        $addchar = ($is_dir ? ' ' : '');
        
        if ( ($file != '.') and ($file != '..') and ($file != 'CVS')
           and (substr($file, -1) != '~') and (substr($entry,0,1) != '.') )
        {
            if (($only_files and !$is_dir) or (!$only_files))
            {
                if ($filter)
                {
                    if (strstr($file, $filter))
                    {
                        $dirs[$addchar . $file] = $file;
                    }
                }
                else
                {
                    $dirs[$addchar . $file] = $file;
                }
            }
        }
    }
    closedir($handle);
    if ($dirs)
    {
        ksort($dirs);
    }
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
    for ($n=1; $n<count($fields); $n++)
    {
        $result .= "$n, ";
    }
    return substr($result, 0, -2);
}

/*******************************************************************************/
/* Returns the file name from a complete path
/*******************************************************************************/
function GetFileName($Path)
{
    $tmp = strrev($Path);
    $result = $tmp;

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
/* Remove the extension from a FileName
/*******************************************************************************/
function RemoveExtension($FileName)
{
    $file = explode('.', $FileName);
    unset($file[sizeof($file)-1]);

    return implode('.', $file);
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
function GetParameters($sql, $exclude_vars = false)
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
        {
            $Parameters[$paramcount] .= $caracter;
        }
    }
    if ($Parameters)
    {
        foreach ($Parameters as $Parameter)
        {
            if (!$exclude_vars or substr($Parameter,0,4) <> '$var')
            {
                $Final[$Parameter] = 'x';
            }
        }
        if ($Final)
        {
            return array_keys($Final);;
        }
        else
        {
            return null;
        }
    }
    else
    {
        return null;
    }
}

/*******************************************************************************/
/* Returns the functions of a expression
/*******************************************************************************/
function extractFunctions($Expression)
{
    for ( $x=0; $x<=strlen($Expression); $x++ )
    {
        $aTree = array('sum','avg','max','min');
        $aFour = array('gavg', 'gmax', 'gmin');
        $aFive = array('count');
        
        $tree = substr($Expression,$x,3);
        $four = substr($Expression,$x,4);
        $five = substr($Expression,$x,5);
        $rest = substr($Expression,$x);
        
        if (in_array($tree, $aTree))
        {
            $pos = strpos($rest, ')');
            $number = substr($rest, 4, $pos -4);
            $return[] = array($tree, $number);
        }
        else if (in_array($four, $aFour))
        {
            $pos = strpos($rest, ')');
            $number = substr($rest, 5, $pos -5);
            $return[] = array($four, $number);
        }
        else if (in_array($five, $aFive))
        {
            $pos = strpos($rest, ')');
            $number = substr($rest, 6, $pos -6);
            $return[] = array($five, $number);
        }
    }
    return $return;
}

function HandlerFile($extras = null)
{
    $Action    = $extras[0];
    $Mask      = $extras[1];
    $Path      = $extras[2];
    $Title     = $extras[3];
    $Buttons   = $extras[4];
    $Parameter = $extras[5];

    if (!is_array($Mask))
    {
        $Mask = array($Mask);
    }

    $FileSelection = &new FileDialog($Title, $Mask, $Buttons, $Path, $Action, $Parameter);
}

function FormatMonetary($number, $precision, $thousep, $decsep)
{
    $zeros = '000000000000';
    
    if (strstr($number, '.'))
    {
        $a = explode('.', $number);
    }
    else if (strstr($number, ','))
    {
        $a = explode(',', $number);
    }
    else
    {
        $a[0] = $number;
    }
    $part1 = $a[0];
    $part2 = substr($a[1],0,$precision);
    if (!$part2)
    $part2 = substr($zeros, 0, $precision);
    
    $tmp = strrev($part1);
    
    for ($n=0; $n<strlen($tmp); $n++)
    {
        if ($i==3)
        {
            $resultpart1 .= $thousep;
            $i = 0;
        }
        $i ++;
        $resultpart1 .= substr($tmp,$n,1);
    }
    $part1 = strrev($resultpart1);
    $result = $part1 . $decsep . $part2;
    return $result;
}


function rgb2int($rgb)
{
    $hex_red   = substr($rgb,1,2);
    $hex_green = substr($rgb,3,2);
    $hex_blue  = substr($rgb,5,2);
    
    $dec_red = hexdec($hex_red);
    $dec_green = hexdec($hex_green);
    $dec_blue = hexdec($hex_blue);

    $int_red = $dec_red/255;
    $int_green = $dec_green/255;
    $int_blue = $dec_blue/255;

    return array($int_red, $int_green, $int_blue);
}

function rgb2int255($rgb)
{

	$ints = rgb2int($rgb);
	$ints[0] = $ints[0] * 255;
	$ints[1] = $ints[1] * 255;
	$ints[2] = $ints[2] * 255;
	return $ints;
}

function invcolor($rgb)
{
    $int = rgb2int($rgb);
    $c_red   = (1- $int[0]) * 255;
    $c_green = (1- $int[1]) * 255;
    $c_blue  = (1- $int[2]) * 255;

    $red   = dechex($c_red);
    $green = dechex($c_green);
    $blue  = dechex($c_blue);
    
    $red   = substr('00', 0, 2-strlen($red)) . $red;
    $green = substr('00', 0, 2-strlen($green)) . $green;
    $blue  = substr('00', 0, 2-strlen($blue)) . $blue;

    return "#$red$green$blue";
}

function my_str_split($string, $count)
{
    $i = 0;
    for ($n=0; $n<=strlen($string); $n++)
    {
        $return[$i] .= substr($string, $n, 1);
        $count_chars++;
        if ($count_chars==$count)
        {
            $count_chars = 0;
            $i ++;
        }
    }
    return $return;
}

function firstword($string)
{
    $pieces = explode(' ', $string);
    return $pieces[0];
}


function vcentral($widget)
{
    $box1 = new GtkVBox;
    $box2 = new GtkVBox;
    $box = new GtkVBox;
    $box->pack_start($box1, true, true);
    $box->pack_start($widget, false, false);
    $box->pack_start($box2, true, true);

    return $box;
}

function central($widget)
{
    $box1 = new GtkHBox;
    $box2 = new GtkHBox;
    $box = new GtkHBox;
    $box->pack_start($box1, true, true);
    $box->pack_start($widget, false, false);
    $box->pack_start($box2, true, true);

    return $box;
}

function left($widget)
{
    $box2 = new GtkHBox;
    $box = new GtkHBox;
    $box->pack_start($widget, false, false);
    $box->pack_start($box2, true, true);

    return $box;
}

function right($widget)
{
    $box2 = new GtkHBox;
    $box = new GtkHBox;
    $box->pack_start($box2, true, true);
    $box->pack_start($widget, false, false);

    return $box;
}

function FirstUpper($string)
{
    return strtoupper(substr($string,0,1)) . strtolower(substr($string,1));
}

function darktxt($text, $width = -1, $height = 26, $color = null)
{
    $topbox = new gtkeventbox;
    $topbox->set_size_request($width, $height);
    $style = $topbox->style;

 
     //------------------comendado por MIGUEL CASANOVA-----------------
    //$style->bg[GTK_STATE_NORMAL] = ($color) ? $color : new GdkColor(42036, 42036, 42036);

    $style = $style->copy();
    $topbox->set_style($style);

    $topbox->add(left(new GtkLabel('  ' . $text)));
    $topbox->modify_fg(Gtk::STATE_NORMAL, GdkColor::parse('#000080'));

    return $topbox;
}

function darkbox($widget, $width, $height, $color)
{
    $topbox = new gtkeventbox;
    $topbox->set_size_request($width, $height);
    $style = $topbox->style;
    $style->bg[GTK_STATE_NORMAL] = ($color) ? $color : new GdkColor(42036, 42036, 42036);
    $style = $style->copy();
    $topbox->set_style($style);

    $topbox->add($widget);
    return $topbox;
}

function frame_title($text, $image)
{
    $box = &new GtkHBox;
    $dock1 = new GtkPixmap($image[0], $image[1]);
    $dock2 = new GtkLabel($text);
    $box->pack_start($dock1, false, false, 4);
    $box->pack_start($dock2, false, false, 4);
    $box->show_all();
    return $box;
}

function get_tables_from($string)
{
    $pieces = explode(',', $string);
    foreach ($pieces as $piece)
    {
        //$subpieces = explode('JOIN ', $piece);
        $subpieces = preg_split("/join /i", $piece);
        if ( count($subpieces) > 0 )
        {
            foreach ($subpieces as $subpiece)
            {
                if ($subpiece)
                {
                    $tables[] = firstword(trim($subpiece));
                }
            }
        }
    }
    return $tables;
}

function remove_table_from($string, $table)
{
    $stringU = trim(strtoupper($string));
    $tableU = trim(strtoupper($table));

    $joinkinds[] = 'INNER JOIN';
    $joinkinds[] = 'CROSS JOIN';
    $joinkinds[] = 'STRAIGHT JOIN';

    $joinkinds[] = 'LEFT JOIN';
    $joinkinds[] = 'LEFT OUTER JOIN';
    $joinkinds[] = 'RIGHT JOIN';
    $joinkinds[] = 'RIGHT OUTER JOIN';
    $joinkinds[] = 'FULL OUTER JOIN';

    $joinkinds[] = 'NATURAL JOIN';
    $joinkinds[] = 'NATURAL LEFT JOIN';
    $joinkinds[] = 'NATURAL RIGHT JOIN';
    $joinkinds[] = 'NATURAL OUTER JOIN';
    $joinkinds[] = 'NATURAL LEFT OUTER JOIN';
    $joinkinds[] = 'NATURAL RIGHT OUTER JOIN';
    
    $pieces = explode(',', $string);
    foreach ($pieces as $piece)
    {
        $pieceU = trim(strtoupper($piece));
        $found = false;
        foreach ($joinkinds as $joinkind)
        {
            $pos = strpos($pieceU, "{$joinkind} {$tableU}");
            if ($pos !== false)
            {
                $final .= trim(substr($piece, 0, $pos)) . ', ';
                $found = true;
                continue;
            }
            if (substr($pieceU, 0, strlen($tableU)) == $tableU)
            {
                $found = true;
                continue;
            }
        }
        if (!$found)
        {
            $final .= $piece . ', ';
        }
    }
    $final = trim($final);
    if (substr($final, -1) == ',')
    {
        $final = substr($final, 0, -1);
    }
    return $final;
}

function MySerialize($string, $text)
{
    $text = ereg_replace("\n", "\n$string:", $text);
    return "$string:$text\n\n";
}

function Cut($msg, $string)
{
    $pos = strpos($msg, $string);
    $error = substr($msg, $pos);
    return $error;
}

/***********************************************************/
/* Convert array into XML
/***********************************************************/
function Array2Xml($XmlArray, $i = 0, $ns = null)
{
    foreach ($XmlArray as $key => $content)
    {
        if (is_array($content))
        {
            $keys = array_keys($content);
            if (is_numeric($keys[0]))  // casos ['chave'][] = 'valor'
            {
                foreach ($content as $item)
                {
                    $retorno .= str_pad('', $i, ' ') . "<$key>$item</$key>\n";
                }
            }
            else
            {
                $retorno .= str_pad('', $i, ' ') . "<{$key}{$ns}>\n";
                $retorno .= Array2Xml($content, $i + 4);
                $retorno .= str_pad('', $i, ' ') . "</$key>\n\n";
            }
        }
        else
        {
            //var_dump($key);
            $retorno .= str_pad('', $i, ' ') . "<$key>";
            $content = str_replace('<', '&lt;', $content);
            $content = str_replace('>', '&gt;', $content);
            $retorno .= $content;
            $retorno .= "</$key>\n";
            if (strpos($content, "\n"))
                $retorno .= "\n";
        }
    }
    return $retorno;
}

/***********************************************************/
/* Convert XML into array
/***********************************************************/
function Xml2Array($FileName)
{
    $XmlFile = new XmlArray($FileName);
    if ($XmlFile->document)
    {
        return $XmlFile->getXmlArray();
    }
    return false;
}

function is_agata_error($mixed)
{
    if (is_object($mixed))
    {
        if (strtolower(get_class($mixed)) == 'agataerror')
        {
            return true;
        }
    }
    return false;
}


/***********************************************************/
/* Format Content according to mask
/***********************************************************/
function FormatMask($mask, $Conteudo)
{
    if (($mask) and ($Conteudo !== null))
    {
        if (substr($mask,0,1)=='@')
        {
            $Conteudo_umask = UnMask($Conteudo, $mask);
            $Conteudo = Mask(substr($mask,1), $Conteudo_umask);
        }
        else if (substr($mask,0,1)=='#')
        {
            $fl_negative = false;
            $suffix_pos = strpos($mask, 's');
            if ($suffix_pos !== false)
            {
                $suffix = substr($mask, $suffix_pos + 1);
                $mask   = substr($mask, 0, $suffix_pos);
            }
            
            $Currency   = substr($mask, 1, 2);
            $Negative   = substr($mask, 3, 1);
            $Negative_  = substr($mask, strlen($mask) -1, 1);
            $ThousandSep= substr($mask, 5, 1);
            $DecimalSep = substr($mask, 9, 1);
            $Precision  = strlen(substr($mask,10));
            
            if ($Negative != '-')
            {
                $Precision --;
            }
            if (($Negative != '-') and ($Conteudo <0 ))
            {
                $Conteudo *= (-1);
                $Conteudo = number_format($Conteudo, $Precision, $DecimalSep, $ThousandSep);
                $Conteudo = $Negative . $Conteudo . $Negative_;
            }
            else
            {
                $Conteudo = number_format($Conteudo, $Precision, $DecimalSep, $ThousandSep);
            }
            
            if ($Currency)
            {
                $Conteudo = $Currency . ' ' . $Conteudo;
            }
            if ($suffix_pos !== false)
            {
                $Conteudo = $Conteudo . $suffix;
            }
            #R$-9,999.99
        }
    }
    return $Conteudo;
}

/***********************************************************/
/* Removes Mask from Content
/***********************************************************/
function UnMask($text, $mask)
{
    $splitters = array('-', '_', '.', '/', '\\', ':', '|', '(', ')', '[', ']', '{', '}');
    if ((strpos($mask, 'Z')) === false)
    {
        $splitters = array_merge($splitters, array(' '));
    }
    
    # run over the typed text
    for ($n=0; $n <= strlen($text); $n++)
    {
        $char = substr($text, $n, 1);
        # returns if isn't a splitter
        if (!in_array($char, $splitters))
        {
            $result .= $char;
        }
    }
    return $result;
}


/***********************************************************/
/* Mask Content
/***********************************************************/
function Mask($mask, $text)
{
    $z = 0;
    
    # run over the mask chars
    for ($n=0; $n < strlen($mask); $n++)
    {
        $mask_char = substr($mask, $n, 1);
        $text_char = substr($text, $z, 1);
        
        # check when needs to concatenate a splitter
        if (in_array($mask_char, array('-', '_', '.', '/', '\\', ':', '|', '(', ')', '[', ']', '{', '}', ' ')))
        {
            //if ($z<strlen($text))
                $result .= $mask_char;
        }
        else
        {
            
            if ($mask_char == '9')
            {
                if (is_numeric($text_char))
                {
                    $result .= $text_char;
                    $z ++;
                }
            }
            else if ($mask_char == 'Z')
            {
                # 'A/C ZZZZ'
                # 'PAULO'
                //if (is_string($text_char))
                
                {
                    $result .= $text_char;
                    $z ++;
                }
            }
            else
            {
                $result .= $mask_char;
                //$z ++;
            }
        }
        
    }
    return $result;
}

function file_size($file)
{
    $sizelb = array('b','kb','Mb','Gb');
    if($size=@filesize($file))
    {
            foreach($sizelb as $lb){
                    if($size<1000){
                            $size = round($size,1)." $lb";
                            break;
                    }
                    $size = $size/1024;
            }
    }
    return $size;
}

function file_date($file)
{
    return date("Y-m-d H:i",@fileatime($file));
}

function dir_back($dir)
{
    $split = explode(bar, $dir);
    for($n=0; $n<count($split)-1; $n++)
    {
        $return .= $split[$n] . bar;
    }
    return substr($return,0,-1);
}


// ------------ lixlpixel recursive PHP functions -------------
// recursive_remove_directory( directory to delete, empty )
// expects path to directory and optional TRUE / FALSE to empty
// of course PHP has to have the rights to delete the directory
// you specify and all files and folders inside the directory
// ------------------------------------------------------------
// to use this function to totally remove a directory, write:
// recursive_remove_directory('path/to/directory/to/delete');
// to use this function to empty a directory, write:
// recursive_remove_directory('path/to/full_directory',TRUE);

function recursive_remove_directory($directory, $empty=FALSE)
{
    // if the path has a slash at the end we remove it here
    if(substr($directory,-1) == '/')
    {
        $directory = substr($directory,0,-1);
    }
 
    // if the path is not valid or is not a directory ...
    if(!file_exists($directory) || !is_dir($directory))
    {
        // ... we return false and exit the function
        return FALSE;
 
    // ... if the path is not readable
    }elseif(!is_readable($directory))
    {
        // ... we return false and exit the function
        return FALSE;
 
    // ... else if the path is readable
    }else{
 
        // we open the directory
        $handle = opendir($directory);
 
        // and scan through the items inside
        while (FALSE !== ($item = readdir($handle)))
        {
            // if the filepointer is not the current directory
            // or the parent directory
            if($item != '.' && $item != '..')
            {
                // we build the new path to delete
                $path = $directory.'/'.$item;
 
                // if the new path is a directory
                if(is_dir($path)) 
                {
                    // we call this function with the new path
                    recursive_remove_directory($path);
 
                // if the new path is a file
                }else{
                    // we remove the file
                    unlink($path);
                }
            }
        }
        // close the directory
        closedir($handle);
 
        // if the option to empty is not set to true
        if($empty == FALSE)
        {
            // try to delete the now empty directory
            if(!rmdir($directory))
            {
                // return false if not possible
                return FALSE;
            }
        }
        // return success
        return TRUE;
    }
}

// Return a array with suported databases type
function suported_databases_type()
{

    $aDBs    = array('native-pgsql'  => 'pg_connect',    'native-mysql'  => 'mysql_connect',
                        'native-oci8'   => 'OCILogon',      'native-sybase' => 'sybase_connect',
                        'native-mssql'  => 'mssql_connect', 'native-fbsql'  => 'fbsql_connect',
                        'native-ibase'  => 'ibase_connect', 'native-ifx'    => 'ifx_connect',
                        'native-sqlite' => 'sqlite_open',   'native-mysqli' => 'mysqli_connect',
                        'native-dbase' => 'dbase_open');

    $aOdbc   = array('odbc-oci8'   => 'odbc_connect', 'odbc-sybase' => 'odbc_connect',
                        'odbc-mssql'  => 'odbc_connect', 'odbc-fbsql'  => 'odbc_connect',
                        'odbc-ibase'  => 'odbc_connect', 'odbc-ifx'    => 'odbc_connect',
                        'odbc-access' => 'odbc_connect', 'odbc-db2'    => 'odbc_connect',
                        'odbc-pgsql'  => 'odbc_connect', 'odbc-mysql'  => 'odbc_connect',
                        'odbc-pervasive'  => 'odbc_connect');

    foreach ($aDBs as $driver => $function)
    {
        if (function_exists($function))
        {
            $aDB[] = $driver;
        }
    }

    foreach ($aOdbc as $driver => $function)
    {
        if (function_exists($function))
        {
            $aDB[] = $driver;
        }
    }

    return $aDB;
}

function suggestion_archive_name($source, $suggestion=null, $number=null)
{
    $withoutExtension = RemoveExtension($source);
    $extension = str_replace($withoutExtension, '', $source);
    $fileName = $withoutExtension . $suggestion . $number . $extension;

    if (file_exists($fileName))
    {
        $source = suggestion_archive_name($source, '-', ++$number);
    }
    else
    {
        $source = $fileName;
    }

    return $source;
}
?>
