<?
# function weekday
# $string_column é a coluna selecionada 
# $array_row é a linha atual do relatório

function weekday($string_column, $array_row)
{
	return date('W', $string_column);
}
?>