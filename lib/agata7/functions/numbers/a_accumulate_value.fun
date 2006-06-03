<?
# function a_accumulate_value
# $string_column is the selected column 
# $array_row is the current tuple of the report
# $array_row is the previous tuple of the report
# $row_num is the current row number of the report 
# $col_num is the current column number of the report 

function a_accumulate_value($string_column, $array_row, $array_last_row, $row_num, $col_num)
{
	return $string_column + $array_last_row[$col_num];
}
?>