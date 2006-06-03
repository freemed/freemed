<?
# function a_to_words
# Converts a number into words.
# By Pablo Dall'Oglio 15/12/2000
# $string_column é a coluna selecionada 
# $array_row é a linha atual do relatório

function a_to_words($string_column, $array_row)
{
  $cVALOR = $string_column;

  $zeros = '000.000.000,00';
  $cVALOR = number_format($cVALOR,2);
  $cVALOR = substr($zeros,0,strlen($zeros)-strlen($cVALOR)) . $cVALOR;
  $cMOEDA_SINGULAR = ' DOLLAR';
  $cMOEDA_PLURAL = ' DOLLARS';

  $cMILHAO  = tree_extensive(substr($cVALOR,0,3));
  $cMILHAO .= ( (substr($cVALOR,0,3) > 1) ? ' MILLIONS' : '' );
  $cMILHAR  = tree_extensive(substr($cVALOR,4,3));
  $cMILHAR .= ( (substr($cVALOR,4,3) > 0) ? ' THOUSAND' : '' );
  $cUNIDAD  = tree_extensive(substr($cVALOR,8,3));
  $cUNIDAD .= ( (substr($cVALOR,8,3) == 1) ? $cMOEDA_SINGULAR : ((substr($cVALOR,8,3) >0) ? $cMOEDA_PLURAL : ''));
  $cCENTAV  = tree_extensive('0' . substr($cVALOR,12,2));
  $cCENTAV .= ((substr($cVALOR,12,2) > 0) ? ' CENTS' : '');

  $cRETURN = $cMILHAO . ((strlen(trim($cMILHAO))<>0 && strlen(trim($cMILHAR))<>0) ? ', ' : '') .
             $cMILHAR . ((strlen(trim($cMILHAR))<>0 && strlen(trim($cUNIDAD))<>0) ? ', ' : '') .
             $cUNIDAD . ((strlen(trim($cUNIDAD))<>0 && strlen(trim($cCENTAV))<>0) ? ', ' : '') .
             $cCENTAV;
  return trim($cRETURN);
}

function tree_extensive($cVALOR)
{
  $aUNID   = array('',' ONE ',' TWO ',' TREE ',' FOUR ',' FIVE ',' SIX ',' SEVEN ',' EIGHT ',' NINE ');
  $aDEZE   = array('','   ',' TWENTY ',' THIRTY ',' FORTY ',' FIFTY ',' SIXTY ',' SEVENTY ',' EIGHTY ',' NINETY ');
  $aCENT   = array('','ONE HUNDRED','TWO HUNDRED','TREE HUNDRED','FOUR HUNDRED','FIVE HUNDRED','SIX HUNDRED','SEVEN HUNDRED','EIGHT HUNDRED','NINE HUNDRED');
  $aEXC    = array(' TEN ',' ELEVEN ',' TWELVE ',' THIRTEEN ',' FOURTEEN ',' FIFTEEN ',' SIXTEEN ',' SEVENTEEN ',' EIGHTEEN ',' NINETEEN ');
  $nPOS1   = substr($cVALOR,0,1);
  $nPOS2   = substr($cVALOR,1,1);
  $nPOS3   = substr($cVALOR,2,1);
  $cCENTE  = $aCENT[($nPOS1)];
  $cDEZE   = $aDEZE[($nPOS2)];
  $cUNID   = $aUNID[($nPOS3)];

  if (substr($cVALOR,0,3) == '100')
  { $cCENTE = 'ONE HUNDRED '; }

  if (substr($cVALOR,1,1) == '1')
  {  $cDEZE = $aEXC[$nPOS3];
     $cUNID = '';
  }

  $cRESULT = $cCENTE . $cDEZE . $cUNID;
  $cRESULT = substr($cRESULT,0,strlen($cRESULT)-1);
  return $cRESULT;
}


?>
