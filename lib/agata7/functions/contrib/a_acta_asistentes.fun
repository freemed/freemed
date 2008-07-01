<?
# function a_acta_asistentes
# $string_column is the selected column 
# $array_row is the current tuple of the reportfunction a_acta_asistentes($string_column, $array_row)

function a_acta_asistentes($string_column, $array_row)
{
try
{

	include_once "/opt/agata/classes/core/AgataConnection.class";

	$conn = new AgataConnection;

	  $agataDB['user']= "postgres";
	  $agataDB['pass']="postgres";
	  $agataDB['name']="db_fmoweb";
	  $agataDB['host']="170.100.1.220";
	  $agataDB['type']="native-pgsql";


	if ($conn->Open($agataDB))
	{
		$sql="select c_idasistente_ac,datb_nombre,sch_rpsdatos.sn_tcarg.carg_descri from sch_sicau.tbl_sicau_asistentes_ac INNER JOIN sch_rpsdatos.sn_tdatbas on (sch_sicau.tbl_sicau_asistentes_ac.c_idasistente_ac = sch_rpsdatos.sn_tdatbas.datb_nrotrab) 
	INNER JOIN sch_rpsdatos.sn_tcarg on (sch_rpsdatos.sn_tdatbas.datb_carg = sch_rpsdatos.sn_tcarg.carg_carg) where c_nref=$string_column and m_estatus_asistente_ac='A'";





	$i=0;
	$strings='</text:p>';

		$Results = $conn->db->Query($sql);
			while ($Row = $conn->db->FetchRow($Results))
            {
				$saltolinea=' ';
				if ($i==0) $saltolinea=' ';
                $strings = $strings .  '<text:p text:style-name="P1">' . trim($Row[1]).' /'.trim($Row[2]  ."</text:p>" );
				$i++;
            }
	}
	else
	{
		$strings="fallo";
	}
	$strings=$strings.'<text:p text:style-name="P1">';

	$conn->close();
	
	return $strings;
}
catch(Exception $e)
{
	return $e;

}

}
?>