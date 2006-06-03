<?
# function yyyymmdd2ddmmaaaa
# $string_column é a coluna selecionada 
# $array_row é a linha atual do relatório

function yyyymmdd2ddmmaaaa($string_column, $array_row)
{
	$year = substr($string_column,0,4);
	$month= substr($string_column,4,2);
	$day  = substr($string_column,6,2);
	return "$day/$month/$year";
}
?>