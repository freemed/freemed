<?php
	// $Id$

class AgataError
{
  function CutError($msg)
  {
    $pos = strpos($msg, 'ERROR:');
    $error = substr($msg, $pos);
    return $error;
  }
  
  function ShowError($msg, $isGui)
  {
    if ($isGui)
    {
      $tmp = new Dialog;
      $tmp->Aviso($this->CutError($msg));
    }
    else
    {
      echo $this->CutError($msg) . "\n";
    }
  }
}

?>
