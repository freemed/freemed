<?
# function a_to_palabra
# Exibe um número por extenso.
# By Pablo Dall'Oglio 16/11/2004
# $string_column é a coluna selecionada 
# $array_row é a linha atual do relatório

function a_to_palabra($string_column, $array_row)
{
  $cVALOR = ereg_replace(",", "\.", $string_column);

  $zeros = '000.000.000,00';
  $cVALOR = number_format($cVALOR,2);
  $cVALOR = substr($zeros,0,strlen($zeros)-strlen($cVALOR)) . $cVALOR;
  $cMOEDA_SINGULAR = ' REAL';
  $cMOEDA_PLURAL = ' REAIS';

  $cMILHAO  = tres_extrenso_palabra(substr($cVALOR,0,3));
  $cMILHAO .= ( (substr($cVALOR,0,3) > 1) ? ' MILLONES' : '' );
  $cMILHAR  = tres_extrenso_palabra(substr($cVALOR,4,3));
  $cMILHAR .= ( (substr($cVALOR,4,3) > 0) ? ' MIL' : '' );
  $cUNIDAD  = tres_extrenso_palabra(substr($cVALOR,8,3));
  $cUNIDAD .= ( (substr($cVALOR,8,3) == 1) ? $cMOEDA_SINGULAR : ((substr($cVALOR,8,3) >0) ? $cMOEDA_PLURAL : ''));
  $cCENTAV  = tres_extrenso_palabra('0' . substr($cVALOR,12,2));
  $cCENTAV .= ((substr($cVALOR,12,2) > 0) ? ' CENTAVOS' : '');

  $cRETURN = $cMILHAO . ((strlen(trim($cMILHAO))<>0 && strlen(trim($cMILHAR))<>0) ? ', ' : '') .
             $cMILHAR . ((strlen(trim($cMILHAR))<>0 && strlen(trim($cUNIDAD))<>0) ? ', ' : '') .
             $cUNIDAD . ((strlen(trim($cUNIDAD))<>0 && strlen(trim($cCENTAV))<>0) ? ', ' : '') .
             $cCENTAV;
  return trim($cRETURN);
}

function tres_extrenso_palabra($cVALOR)
{
  $aUNID   = array('',' UNA ',' DOS ',' TRES ',' CUATRO ',' CINCO ',' SEIS ',' SIETE ',' OCHO ',' NUEVE ');
  $aDEZE   = array('','   ',' VEINTE ',' TREINTA ',' CUARENTA ',' CINCUENTA ',' SESENTA ',' SETENTA ',' OCHENTA ',' NOVENTA ');
  $aCENT   = array('','CIEN','DOSCIENTAS','TRESCIENTAS','CUATROCIENTAS','QUINIENTAS','SEISCIENTAS','SETECIENTAS','OCHOCIENTAS','NOVECIENTAS');
  $aEXC    = array(' DIEZ ',' ONCE ',' DOCE ',' TRECE ',' CATORCE ',' QUINCE ',' DIECISEIS ',' DIECISIETE ',' DIECIOCHO ',' DIECINUEVE ');
  $nPOS1   = substr($cVALOR,0,1);
  $nPOS2   = substr($cVALOR,1,1);
  $nPOS3   = substr($cVALOR,2,1);
  $cCENTE  = $aCENT[($nPOS1)];
  $cDEZE   = $aDEZE[($nPOS2)];
  $cUNID   = $aUNID[($nPOS3)];

  if (substr($cVALOR,0,3) == '100')
  { $cCENTE = 'CIEN '; }

  if (substr($cVALOR,1,1) == '1')
  {  $cDEZE = $aEXC[$nPOS3];
     $cUNID = '';
  }

  $cRESULT = $cCENTE . $cDEZE . $cUNID;
  $cRESULT = substr($cRESULT,0,strlen($cRESULT)-1);
  return $cRESULT;
}


?>
