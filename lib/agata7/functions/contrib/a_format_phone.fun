<?
# function a_format_phone
# $string_column é a coluna selecionada
# $array_row é a linha atual do relatatório
# Criada por Rodrigo Carvalhaes em 02/04/2005

function a_format_phone($string_column, $array_row)
{
	$tamanho = strlen(trim($string_column));

    if ($tamanho >= 10)
    {
		$ddd     = substr($string_column,0,2);
		$phone_1 = substr($string_column,2,4);
		$phone_2 = substr($string_column,6,2);
		$phone_3 = substr($string_column,8,3);
		return "($ddd)$phone_1-$phone_2-$phone_3";
    }
    elseif ($tamanho == 9)
    {
		$ddd     = substr($string_column,0,2);
		$phone_1 = substr($string_column,2,3);
		$phone_2 = substr($string_column,5,2);
		$phone_3 = substr($string_column,7,3);
		return "($ddd)$phone_1-$phone_2-$phone_3";
	}
	elseif ($tamanho == 8)
    {
		$phone_1 = substr($string_column,0,4);
		$phone_2 = substr($string_column,4,2);
		$phone_3 = substr($string_column,6,3);
		return "$phone_1-$phone_2-$phone_3";
	}
	elseif ($tamanho == 7)
    {
		$phone_1 = substr($string_column,0,3);
		$phone_2 = substr($string_column,3,2);
		$phone_3 = substr($string_column,5,3);
		return "$phone_1-$phone_2-$phone_3";
	}
	else
	{
		return "";
	}
}
?>