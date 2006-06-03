<?
# function a_to_palavra
# Exibe um número por extenso.
# By Pablo Dall'Oglio 15/12/2000
# $string_column é a coluna selecionada 
# $array_row é a linha atual do relatório

function a_to_palavra($string_column, $array_row)
{
  $cVALOR = ereg_replace(",", "\.", $string_column);

  $zeros = '000.000.000,00';
  $cVALOR = number_format($cVALOR,2);
  $cVALOR = substr($zeros,0,strlen($zeros)-strlen($cVALOR)) . $cVALOR;
  $cMOEDA_SINGULAR = ' REAL';
  $cMOEDA_PLURAL = ' REAIS';

  $cMILHAO  = tres_extrenso_palavra(substr($cVALOR,0,3));
  $cMILHAO .= ( (substr($cVALOR,0,3) > 1) ? ' MILHOES' : '' );
  $cMILHAR  = tres_extrenso_palavra(substr($cVALOR,4,3));
  $cMILHAR .= ( (substr($cVALOR,4,3) > 0) ? ' MIL' : '' );
  $cUNIDAD  = tres_extrenso_palavra(substr($cVALOR,8,3));
  $cUNIDAD .= ( (substr($cVALOR,8,3) == 1) ? $cMOEDA_SINGULAR : ((substr($cVALOR,8,3) >0) ? $cMOEDA_PLURAL : ''));
  $cCENTAV  = tres_extrenso_palavra('0' . substr($cVALOR,12,2));
  $cCENTAV .= ((substr($cVALOR,12,2) > 0) ? ' CENTAVOS' : '');

  $cRETURN = $cMILHAO . ((strlen(trim($cMILHAO))<>0 && strlen(trim($cMILHAR))<>0) ? ', ' : '') .
             $cMILHAR . ((strlen(trim($cMILHAR))<>0 && strlen(trim($cUNIDAD))<>0) ? ', ' : '') .
             $cUNIDAD . ((strlen(trim($cUNIDAD))<>0 && strlen(trim($cCENTAV))<>0) ? ', ' : '') .
             $cCENTAV;
  return trim($cRETURN);
}

function tres_extrenso_palavra($cVALOR)
{
  $aUNID   = array('',' UM ',' DOIS ',' TRES ',' QUATRO ',' CINCO ',' SEIS ',' SETE ',' OITO ',' NOVE ');
  $aDEZE   = array('','   ',' VINTE E',' TRINTA E',' QUARENTA E',' CINQUENTA E',' SESSENTA E',' SETENTA E',' OITENTA E',' NOVENTA E ');
  $aCENT   = array('','CENTO E','DUZENTOS E','TREZENTOS E','QUATROCENTOS E','QUINHENTOS E','SEISCENTOS E','SETECENTOS E','OITOCENTOS E','NOVECENTOS E');
  $aEXC    = array(' DEZ ',' ONZE ',' DOZE ',' TREZE ',' QUATORZE ',' QUINZE ',' DESESSEIS ',' DESESSETE ',' DEZOITO ',' DESENOVE ');
  $nPOS1   = substr($cVALOR,0,1);
  $nPOS2   = substr($cVALOR,1,1);
  $nPOS3   = substr($cVALOR,2,1);
  $cCENTE  = $aCENT[($nPOS1)];
  $cDEZE   = $aDEZE[($nPOS2)];
  $cUNID   = $aUNID[($nPOS3)];

  if (substr($cVALOR,0,3) == '100')
  { $cCENTE = 'CEM '; }

  if (substr($cVALOR,1,1) == '1')
  {  $cDEZE = $aEXC[$nPOS3];
     $cUNID = '';
  }

  $cRESULT = $cCENTE . $cDEZE . $cUNID;
  $cRESULT = substr($cRESULT,0,strlen($cRESULT)-1);
  return $cRESULT;
}


?>
