<?php
	// $Id$

/*
	Criado por Vilson Gartner
	Alterado em 13/05/2002 - Joel Leon - incluída função rshow
	em fevereiro de 2003 xandehs incluiu a funcao de justificar
*/

class postscript
{

    var $fp;
    var $filename;
    var $string="";
    var $page=1;
    var $acentos="";

    #- startup the whole thing = Aqui tudo inicia
    function postscript($fname = "", $author="PSLib", $title="Generated with PSLib",  $orientation="Portrait")
    {
        #- A text string was requested: file name to create
        if($fname)
        {
            if(! $this->fp = fopen($fname,"w")) return(0);
        }
        
        $this->string .= "%!PS-Adobe-3.0 \n";
        $this->string .= '%%Creator: ' . $author . "\n";
        $this->string .= '%%CreationDate: ' . date("d/m/Y, H:i") . "\n";
        $this->string .= '%%Title: ' . $title . "\n";
        $this->string .= "%%PageOrder: Ascend \n";
        $this->string .= '%%Orientation: ' . $orientation . "\n";
        $this->string .= "%%EndComments \n";
        $this->string .= "%%BeginProlog \n";
        $this->string .= "%%BeginResource: definicoes \n";
        //* Comment this to disable support for international character encoding (or remove file acentos.ps)
        // Para nao ter suporte a acentuacao comente este trecho(ou retire o arquivo acentos.ps).
        if (file_exists(dirname(dirname(__FILE__)).'/accents.tpl'))
        {
             if($f = join('',file(dirname(dirname(__FILE__)).'/accents.tpl'))) $this->string .= $f;
        }
        //*/
        $this->string .= "%%EndResource \n";
        $this->string .= "%%EndProlog \n";

        return(1);
    }


    #- Begin new page = Inicia uma nova pagina
    function begin_page($page)
    {
        $this->string.= "%%Page: " . $page . ' ' . $page . "\n";
        return(1);
    }

    
    #- End page = Finaliza pagina
    function end_page()
    {
        $this->string .= "showpage \n";
        return(1);
    }


    #- Close the postscript file = Fecha o arquivo postscript 
    function close()
    {
        $this->string .= "showpage \n";
        if($this->fp)
          {
           fwrite($this->fp,$this->string);
           fclose($this->fp);
          }

        return($this->string);
   }

    #- Draw a line = Desenha uma linha
    function line($xcoord_from=0, $ycoord_from=0, $xcoord_to=0, $ycoord_to=0, $linewidth=0)
    {
        if(!$xcoord_from || !$ycoord_to || !$xcoord_to || !$ycoord_to || !$linewidth) return(0);
        
        $this->string .= $linewidth . " setlinewidth  \n";
        $this->string .= $xcoord_from . ' ' . $ycoord_from  . " moveto \n";
        $this->string .= $xcoord_to . ' ' . $ycoord_to  . " lineto \n";
        $this->string .= "stroke \n";
        
        return(1);
    }


    #- Move to coordinates = Move para as coordenadas
    function moveto($xcoord, $ycoord)
    {
        if(empty($xcoord) || empty($ycoord)) return(0);
        
        $this->string .= $xcoord . ' ' . $ycoord . " moveto \n";
        
        return(1);
    }


    #- Move to coordinates and change the font = Move para as coordenadas e muda a fonte
    function moveto_font($xcoord, $ycoord, $font_name, $font_size)
    {
        if(!$xcoord || !$ycoord || !$font_name || !$font_size) return(0);
        
        $this->string .= $xcoord . ' ' . $ycoord . " moveto \n";
        $this->string .= '/' . $font_name . ' findfont ' . $font_size . " scalefont setfont \n";
        
        return(1);
    }


    #-Insert a PS file/image (remember to delete the information in the top of the file (source))
    #-Insere um arquivo/imagem PS (lembre-se de remover a informaçao no inicio daquele arquivo)
    function open_ps($ps_file="")
    {
        if(!$ps_file) return(0);

        if($f = join('',file($ps_file)))
          $this->string .= $f;
        else
          return(0);

        return(1);
    }


    #- Draw a rectangle = Desenha um retangulo
    function rect($xcoord_from, $ycoord_from, $xcoord_to, $ycoord_to, $linewidth)
    {
        if(!$xcoord_from || !$ycoord_from || !$xcoord_to || !$ycoord_to || !$linewidth) return(0);

          $this->string .= $linewidth . " setlinewidth  \n";
          $this->string .= "newpath \n";
          $this->string .= $xcoord_from . ' ' . $ycoord_from  . " moveto \n";
          $this->string .= $xcoord_to . ' ' . $ycoord_from  . " lineto \n";
          $this->string .= $xcoord_to . ' ' . $ycoord_to  . " lineto \n";
          $this->string .= $xcoord_from . " " . $ycoord_to  . " lineto \n";
          $this->string .= "closepath \n";
          $this->string .= "stroke \n";

          return(1);
    }


    #- Draw and shade a rectangle = Desenha um retangulo e preenche
    function rect_fill($xcoord_from, $ycoord_from, $xcoord_to, $ycoord_to, $linewidth, $darkness)
    {

        if(!$xcoord_from || !$ycoord_from || !$xcoord_to || !$ycoord_to || !$linewidth || !$darkness) return(0);

          $this->string .= "newpath \n";
          $this->string .= $linewidth . " setlinewidth  \n";
          $this->string .= $xcoord_from . ' ' . $ycoord_from  . " moveto \n";
          $this->string .= $xcoord_to . ' ' . $ycoord_from  . " lineto \n";
          $this->string .= $xcoord_to . ' ' . $ycoord_to  . " lineto \n";
          $this->string .= $xcoord_from . ' ' . $ycoord_to  . " lineto \n";
          $this->string .= "closepath \n";
          $this->string .= "gsave \n";
          $this->string .= $darkness . " setgray  \n";
          $this->string .= "fill \n";
          $this->string .= "grestore \n";
          $this->string .= "stroke \n";

          return(1);
    }


   #- Set rotation, use 0 or 360 to end rotation = Muda a rotacao do texto, passe 0 ou 360 para finalizar a rotacao 
   function rotate($degrees)
   {
       if(!$degrees) return(0);

       if(($degrees == '0') or ($degrees == '360'))
           $this->string .= "grestore \n";
       else
       {
           $this->string .= "gsave \n";
           $this->string .= $degrees . " rotate \n";
        }

      return(1);
   }


   #- Set the font to show = Muda a fonte
   function set_font($font_name, $font_size)
   {
       if(!$font_name || !$font_size) return(0);

       $this->string .=  '/' . $font_name . ' findfont ' . $font_size . " scalefont setfont \n";
       
       return(1);
   }


   #- Showsome text at the current coordinates (use 'moveto' to set coordinates)
   #- Escreve o texto na posicao atual (utilize 'moveto' para mudar a posicao)
   function show($text)
   {
       if(!$text) return(0);

       $this->string .=  '(' . $text  . ") show \n";

       return(1);
   }


    # - Show text at the x-y coordinates, right-aligned
    # - Escreve o texto na posicao x-y, alinhado pela direita
    #   	Dica obtida de uma classe em Perl chamada Postscript.pm,
    #		que é parte do projeto coloredChromosomes, 
    #		http://mhg.uni-bochum.de/cc/index.htm
    function rshow($text, $xcoord, $ycoord, $font_name, $font_size) {
        if(!$text || !$xcoord || !$ycoord || !$font_name || !$font_size) return(0);
        $this->set_font($font_name, $font_size);
	$this->string .= sprintf("(%s) dup stringwidth pop %u exch sub %d moveto show\n", $text, $xcoord, $ycoord);
	return(1);
    }



   #- Evaluate the text and show it at the current coordinates
   #- Processa o texto e o escreve na posicao atual
   function show_eval($text)
   {
       if(!$text) return(0);
       
       eval("\$text = \"$text\";");
       $this->string .=  '(' . $text  . ") show \n";
       
       return(1);
   }


   #- Show some text at specific coordinates 
   #- Escreve o texto na coordenada informada
   function show_xy($text, $xcoord, $ycoord)
   {

       if(!$text || !$xcoord || !$ycoord) return(0);
       
       $this->moveto($xcoord, $ycoord);
       $this->show($text);

     return(1);

   }

  function align_center($text, $xcoord, $ycoord, $font_name, $font_size )
  {
    $this->string .= '/' . $font_name . ' findfont ' . $font_size . " scalefont setfont \n";
    $this->string .= $xcoord . ' ' . $ycoord . " moveto \n";
    $this->string .= '(' . $text . ') dup stringwidth pop 2 div ' . $xcoord . ' exch sub ' . $ycoord . " moveto show \n";
  }

   #- Show some text at specific coordinates with font settings
   #- Mostra o texto na coordenada informa com a fonte especifica
   function show_xy_font($text, $xcoord, $ycoord, $font_name, $font_size)
   {
       if(!$text || !$xcoord || !$ycoord || !$font_name || !$font_size) return(0);

       $this->set_font($font_name, $font_size);
       $this->show_xy($text, $xcoord, $ycoord);

       return(1);
   }
   
   function align_justify($file, $xinit, $yinit, $maxwidth, $lineheight, $paragraph, $bottommargin, $text, $font_name, $font_size)
   {
   
     $ps_txt  = "/$font_name findfont $font_size scalefont setfont\n" . 
              "\n" . 
              "% ************************************************************** %\n" .
              "%  parameters to be passed to the PS iterpreter.                 %\n" .
              "% ************************************************************** %\n" .
              "\n" .
              "% maximum width per line %\n" .
              "/maxwidth $maxwidth def\n" .
              "\n" .
              "% bottom margin %\n" .
              "/bottommargin $bottommargin def\n" .
              "\n" .
              "% line heigth %\n" .
              "/lineheight $lineheight def\n" .
              "\n" .
              "% paragraph spacing %\n" .
              "/paragraph $paragraph def\n" .
              "\n" .
              "% initial x and y coords\n" .
              "/xinit $xinit def\n" .
              "/yinit $yinit def\n" .
              "\n" .
              "% text to be worked out %\n" .
              "/text ($text\n) def\n" .
              "\n" .
              "% ********************************************************** %\n" .
              "% the space ASCII code %\n" .
              "/space 32 def\n" .
              "\n" .
              "% <int> <string> countchar <int> %\n" .
              "% count occurences of <int> in %\n" .
              "% string <string>. %\n" .
              "/countchar {\n" .
              "   0\n" .
              "   exch\n" .
              "   {\n" .
              "      2 index\n" .
              "      eq\n" .
              "      {\n" .
              "         1 add\n" .
              "      } if\n" .
              "   } forall\n" .
              "   exch pop\n" .
              "} bind def\n" .
              "\n" .
              "% <string> <n> lcut <string> %\n" .
              "% cut <n> chars from the left %\n" .
              "% of <string> %\n" .
              "/lcut {\n" .
              "   dup\n" .
              "   2 index\n" .
              "   length sub neg\n" .
              "   getinterval\n" .
              "} bind def\n" .
              "\n" .
              "% <string> <n> rcut <string> %\n" .
              "% cut <n> chars from the right %\n" .
              "% of <string> %\n" .
              "/rcut {\n" .
              "   1 index\n" .
              "   length sub neg\n" .
              "   0 exch\n" .
              "   getinterval\n" .
              "} bind def\n" .
              "\n" .
              "% <string> rwordcut <string> %\n" .
              "% cut the rightmost word of string %\n" .
              "/rwordcut {\n" .
              "   {\n" .
              "      dup\n" .
              "      dup length\n" .
              "      % exit if string is zero-length  %\n" .
              "      dup 0 eq { pop pop exit } if\n" .
              "      \n" .
              "      1 sub\n" .
              "      get\n" .
              "      space eq\n" .
              "      {\n" .
              "         1 rcut\n" .
              "         exit\n" .
              "      }\n" .
              "      {\n" .
              "         1 rcut\n" .
              "      } ifelse\n" .
              "   } loop\n" .
              "} bind def\n" .
              "\n" .
              "/ltrim {\n" .
              "   {\n" .
              "      dup\n" .
              "      \n" .
              "      % control string length %\n" .
              "      dup length\n" .
              "      0 eq\n" .
              "      { pop exit } if\n" .
              "      \n" .
              "      0 get\n" .
              "      space ne\n" .
              "      {\n" .
              "         exit\n" .
              "      }\n" .
              "      {\n" .
              "         1 lcut\n" .
              "      } ifelse\n" .
              "   } loop\n" .
              "   \n" .
              "} bind def\n" .
              "\n" .
              "/rtrim {\n" .
              "   {\n" .
              "      dup\n" .
              "\n" .
              "      % control string length %\n" .
              "      dup length\n" .
              "      0 eq\n" .
              "      { pop exit } if\n" .
              "\n" .
              "      dup length 1 sub get\n" .
              "      space ne\n" .
              "      {\n" .
              "         exit\n" .
              "      }\n" .
              "      {\n" .
              "         1 rcut\n" .
              "      } ifelse\n" .
              "   } loop\n" .
              "} bind def\n" .
              "\n" .
              "% <int> <string> adjustandshow <string> %\n" .
              "% show the string inside <int> width %\n" .
              "/adjustandshow {\n" .
              "   dup \n" .
              "   0 get\n" .
              "   9 eq\n" .
              "   {\n" .
              "      paragraph 0 rmoveto\n" .
              "   } if\n" .
              "   % how much more pixels do we need? %\n" .
              "   dup stringwidth pop\n" .
              "   2 index % max width %\n" .
              "\n" .
              "   sub neg\n" .
              "   % how many spaces in string? %\n" .
              "   1 index space exch countchar\n" .
              "   % are there spaces to calculate width? %\n" .
              "   dup 0 gt\n" .
              "   { % yes, so \n" .
              "      2 index 10 exch countchar % is there any \\n in string? %\n" .
              "      0 gt\n" .
              "      { % yes, so show the text without adjusting width %\n" .
              "         pop pop\n" .
              "         dup show\n" .
              "      }\n" .
              "      { % calculate and show %\n" .
              "        % how many pixels per space? %\n" .
              "         div 0 space 3 index widthshow\n" .
              "      } ifelse\n" .
              "   }\n" .
              "   { % no spaces, then show only %\n" .
              "      pop pop\n" .
              "      dup show\n" .
              "   } ifelse\n" .
              "   exch pop\n" .
              "   % move to next line or next page %\n" .
              "   currentpoint lineheight sub exch pop xinit exch moveto\n" .
              //"   currentpoint exch pop\n" .
              //"   bottommargin lt\n" .
              //"   { % bottom margin reached %\n" .
              //"      showpage\n" .
              //"      xinit yinit moveto\n" .
              //"   } if\n" .
              "} bind def\n" .
              "\n" .
              "% <int> <string> getmaxwidth <int> %\n" .
              "%   %\n" .
              "/getmaxwidth {\n" .
              "  0\n" .
              "  get\n" .
              "  9 eq\n" .
              "  {\n" .
              "     maxwidth\n" .
              "     paragraph\n" .
              "     sub\n" .
              "  }\n" .
              "  {\n" .
              "     maxwidth\n" .
              "  } ifelse\n" .
              "} bind def\n" .
              "\n" .
              "% <string> justify -- %\n" .
              "% display <string> justified, starting %\n" .
              "% at cursor position. %\n" .
              "/justify {\n" .
              "   dup\n" .
              "   {\n" .
              "      dup\n" .
              "      stringwidth pop\n" .
              "      1 index\n" .
              "      getmaxwidth\n" .
              "\n" .
              "      gt\n" .
              "      {\n" .
              "         rwordcut\n" .
              "      }\n" .
              "      {\n" .
              "         dup\n" .
              "         getmaxwidth \n" .
              "         exch\n" .
              "        \n" .
              "         adjustandshow\n" .
              "\n" .
              "         length lcut\n" .
              "         ltrim rtrim\n" .
              "         % exit when the string is empty %\n" .
              "         dup length 0 eq\n" .
              "         {\n" .
              "            pop\n" .
              "            exit\n" .
              "         } if\n" .
              "         dup\n" .
              "      } ifelse\n" .
              "   } loop\n" .
              "} bind def\n" .
              "\n" .
              "% <string1> <string2> concat <string3> %\n" .
              "% Concatenates <string1> and <string2>, %\n" .
              "% returning <string1><string2> as result %\n" .
              "/strconcat {\n" .
              "   dup length % string1 size %\n" .
              "   2 index length % string2 size %\n" .
              "   add\n" .
              "   string dup % allocate new string %\n" .
              "   % add first word %\n" .
              "   0\n" .
              "   4 index\n" .
              "   putinterval \n" .
              "   % add second word %\n" .
              "   dup\n" .
              "   3 index length\n" .
              "   3 index\n" .
              "   putinterval\n" .
              "   % clear stack %\n" .
              "   exch pop\n" .
              "   exch pop\n" .
              "} bind def\n" .
              "\n" .
              "% main script %\n" .
              "\n" .
              "xinit yinit moveto\n" .
              "text\n" .
              "\n" .
              "{\n" .
              "   (\\n) search\n" .
              "   {\n" .
              "      (\\n) strconcat\n" .
              "      justify\n" .
              "      pop\n" .
              "   }\n" .
              "   {\n" .
              "      (\\n) strconcat\n" .
              "      justify\n" .
              "      currentpoint lineheight add exch pop xinit exch moveto\n" .
              "      exit\n" .
              "   } ifelse\n" .
              "} loop\n";

      fwrite($file, $ps_txt);
   }   
} #- end class

?>
