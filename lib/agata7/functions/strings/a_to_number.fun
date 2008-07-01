<?
# function a_to_number
# $string_column é a coluna selecionada 
# $array_row é a linha atual do relatório

function a_to_number($string_column, $array_row)
{
    for ($n=0; $n<=strlen($string_column); $n++)
    {
        $char = substr($string_column,$n,1);
        if (is_numeric($char))
        {
            $return .= $char;
        }
    }

    return $return;
}
?>