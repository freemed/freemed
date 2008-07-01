<?php
/* class OOParser
 *
 */
class OOParser
{
    private $buffer;
    private $page_break;
    private $break_style;
    private $repeat_header = true;
    private $repeat_footer = true;

    /* Constructor Method
     *
     */
    function __construct($source, $target, $data)
    {
        require_once 'pclzip.lib.php';
        include_once '/agata/include/util.inc';
        $this->buffer = array();
        
        $this->break_style = '<style:style style:name="AgataPageBreak" style:family="paragraph" style:parent-style-name="Standard">' .
                             '<style:properties fo:break-before="page"/>' .
                             '</style:style>';
        
        $this->page_break = '<text:p text:style-name="AgataPageBreak"/>';
        
        define(temp, '/tmp');
        define("bar", '/');
        $prefix = temp . bar . RemoveExtension($source);
        
        $zip      = new PclZip($source);
        
        if (($list = $zip->listContent()) == 0)
        {
            adie("Error : ".$zip->errorInfo(true));
        }
        
        recursive_remove_directory($prefix);
        if ($zip->extract(PCLZIP_OPT_PATH, $prefix) == 0)
        {
            adie("Error : ".$zip->errorInfo(true));
        }
        
        $content= file_get_contents($prefix . '/content.xml');
        
        # break xml tags
        $array_content = preg_split ('/(<(?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+>)/', trim ($content), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        //print_r($array_content);
        $section = 'start';
        echo "antes for\n";
        foreach ($array_content as $line)
        {
            // <text:section text:style-name="Sect1" text:name="header">
            if (substr(trim($line), 0, 13) == '<text:section')
            {
                $pieces = explode('text:name="', $line);
                $section = substr($pieces[1], 0, -2);
            }
            else if (substr(trim($line), 0, 14) == '</office:body>')
            {
                $section = 'end';
            }
            
            if ($line == '</office:automatic-styles>')
            {
                $line = $this->break_style . $line;
            }
            
            $this->buffer[$section][] = $line;
            //echo $section . "\n";
        }
        echo "depois for\n";
        print_r($this->buffer);
        

        
        $output = implode('', $this->buffer['start']);
        
        $break  = false;
        
        foreach ($data as $line)
        {
            $sub = array();
            $sub[] = array('01/01/2005', 'abertura');
            $sub[] = array('02/01/2005', 'Software Livre'); 
            $sub[] = array('03/01/2005', 'PHP-GTK'); 
            $sub[] = array('04/01/2005', 'Agata Report');
            $sub[] = array('05/01/2005', 'encerramento');
            $sub[] = array('06/01/2005', 'encerramento2');
            $sub[] = array('07/01/2005', 'encerramento3');
            $sub[] = array('08/01/2005', 'encerramento4');
            $sub[] = array('09/01/2005', 'encerramento5');
            $sub[] = array('10/01/2005', 'encerramento6');
            $sub[] = array('11/01/2005', 'encerramento7');
            $sub[] = array('12/01/2005', 'encerramento8');
            
            $output .= $this->printSection('header',  $line, $break);
            $output .= $this->printSection('details', $line, $sub);
            $output .= $this->printSection('footer',  $line);
            //$output .= $page_break;
            $break = true;
            while ($this->rest)
            {
                if ($this->repeat_header)
                {
                    $output .= $this->printSection('header',  $line, $break);
                }
                else
                {
                    $output .= $this->page_break;
                }
                $output .= $this->printSection('details', $line, $this->rest);
                if ($this->repeat_footer)
                {
                    $output .= $this->printSection('footer',  $line);
                }
            }
        }
        
        $output .= implode('', $this->buffer['end']);
        echo strlen($output);
        echo "\n";
        file_put_contents($prefix . '/content.xml', $output);
        
        $zip2 = new PclZip($target);
        foreach ($list as $file)
        {
            $zip2->add($prefix . '/' . $file['filename'], PCLZIP_OPT_REMOVE_PATH, $prefix);
            echo "Adding " . $file['filename'] . "\n";
        }
    }

    /* method printSection
     *
     */
    function printSection($section, $data, $plus = false)
    {
        $output = '';
        
        if ($section == 'details')
        {
            $this->rest = null;
            # details pre-processing
            $sub = $plus;
            $row = 1;
            foreach ($sub as $line)
            {
                $col = 1;
                foreach ($line as $cell)
                {
                    $replace[$row]['$subfield' . $col] = $cell;
                    $col ++;
                }
                $row ++;
            }
            # /details pre-processing
        }

        $line    = 0;
        $sub_row = 0;
        $process = false;
        foreach ($this->buffer[$section] as $text_line)
        {
            $i = 1;
            foreach ($data as $cell)
            {
                $text_line = str_replace('$var' . $i, utf8_encode($cell), $text_line);
                $i ++;
            }
            
            # Quebra a página, colocando o estilo de PageBreak
            # Na primeira linha de cada 'header'
            if (($line == 1) and ($section == 'header') and ($plus == true))
            {
                //<text:p text:style-name="Standard">
                $begin = '<text:p text:style-name=';
                if (substr($text_line, 0, strlen($begin)) == $begin)
                {
                    # Faz o split para separar a expressao.
                    $pattern = '/(<text:p text:style-name=".*")/';
                    $pieces = preg_split($pattern, trim ($string), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                    $text_line = '<text:p text:style-name="AgataPageBreak">' . $pieces[1];
                }
                else
                {
                    $text_line .= $this->page_break;
                }
            }
            
            if ($section == 'details')
            {
                # Fim do cabeçalho, começa a contar as linhas dos detalhes
                if (substr($text_line, 0, 26) == '</table:table-header-rows>')
                {
                    $process = true;
                }
                else if (substr($text_line, 0, 14) == '</table:table>')
                {
                    $process = false;
                    $sub_row ++;
                    
                    for ($i = $sub_row; $i <= count($replace); $i ++)
                    {
                        $this->rest[] = $replace[$i];
                    }
                }
                
                if ($process)
                {
                    if (substr($text_line, 0, 17) == '<table:table-row>')
                    {
                        $sub_row ++;
                    }
                    if ($replace[$sub_row])
                    {
                        # Substituir os detalhes
                        foreach ($replace[$sub_row] as $this_text => $that_text)
                        {
                            $text_line = str_replace($this_text, $that_text, $text_line);
                        }
                    }
                    else
                    {
                        # Limpar ultimas linhas, quando já passou do eof
                        foreach ($replace[1] as $this_text => $that_text)
                        {
                            $text_line = str_replace($this_text, '', $text_line);
                        }
                    }
                }
            }
            
            $output .= $text_line;
            $line ++;
        }
        return $output;
    }
}
$data = array();

$data[] = array(1, 'Pablo',     'Rua do Conceição',     'Curitiba-PR',       '12.345-678');
$data[] = array(2, 'João',      'Rua do São Cristóvão', 'São Paulo-SP',      '23.456-789');
$data[] = array(3, 'Maria',     'Rua da Bromélia',      'Porto Alegre-RS',   '34.567-890');
$data[] = array(4, 'Carlos',    'Rua do Fonseca',       'Florianópolis-SC',  '45.678-901');

new OOParser('teste.sxw', 'teste2.sxw', $data);
?> 
