<?
# function price_negative
# $string_column é a coluna selecionada 
# $array_row é a linha atual do relatório

function a_price_negative($string_column, $array_row)
{
	if ($string_column <0)
	{
		return '1';
	}
	else
	{
		return '0';
	}
}
?>