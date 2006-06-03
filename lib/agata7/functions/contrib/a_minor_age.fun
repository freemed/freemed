<?
# function a_minor_age
# $string_column é a coluna selecionada 
# $array_row é a linha atual do relatório

function a_minor_age($string_column, $array_row)
{
	if ($string_column < 21)
	{
		return 1;
	}
	return 0;
}
?>