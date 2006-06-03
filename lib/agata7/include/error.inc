<?php
// redefine the user error constants - PHP 4 only
define("FATAL", E_USER_ERROR);
define("ERROR", E_USER_WARNING);
define("WARNING", E_USER_NOTICE);
echo "FATAL" .  E_USER_ERROR . "\n";
echo "ERROR". E_USER_WARNING . "\n";
echo "WARNING". E_USER_NOTICE . "\n";
// set the error reporting level for this script
error_reporting(FATAL | ERROR);
// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
  switch ($errno) {
  case FATAL:
	 $erro[] = "Fatal [$errno] $errstr";
	 $erro[] = "Linha $errline";
	 $erro[] = "Arquivo $errfile";
	 new Dialog($erro, true, true); 
   //exit(1);
   break;
  case ERROR:
	 echo "[$errno] $errstr on $errfile at $errline\n";
   break;
  case WARNING:
	 echo "[$errno] $errstr on $errfile at $errline\n";
   break;
  case 2:
	 echo "[$errno] $errstr on $errfile at $errline\n";
   break;

   default:
	   #	      echo ">> [$errno] $errstr on $errfile at $errline\n";
   break;
  }
}
set_error_handler("myErrorHandler");

?> 
