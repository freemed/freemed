<?php
	// $Id$

/***********************************************************/
/* Classe que cria Caixas de Aguarde                       */
/* Linguagem PHP-GTK                                       */
/* Autor: Pablo Dall'Oglio                                 */
/* Última ateração em 26 Setembro 2001 por Pablo           */
/***********************************************************/


/***********************************************************/
/* Classe Utilitária
/***********************************************************/
class Wait
{
  function On($isGui = true)
  {
    if ($isGui)
    {
      if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN')
      {
        exec("php wait.php >/dev/null &");
      }
      else
      {
        if (PHP_OS != 'WINNT')
        {
          exec("wait >NULL &");
        }
      }
    }
  }

  function Off($isGui = true)
  {
    if ($isGui)
    {
      if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN')
      {
        exec("for i in `ps ax|grep wait.php |grep -v \"grep\"|awk -F\" \" '{printf  $1\"\\n\"   }'`; do kill -9 \$i; done");
      }
      else
      {
        if (PHP_OS != 'WINNT')
        {
          exec("pv > processes.pid");
          $pid = -1;
          $fd = fopen ('processes.pid', "r");
          while (!feof ($fd))
          {
            $buffer = trim(fgets($fd, 500));
            if ($buffer!='')
            {
              $Linha = explode(".EXE", trim($buffer));
              if (trim($Linha[0]) == 'PHP')
              {
                if ((trim($Linha[0]) > $pid) || ($pid == -1))
  	          $pid = trim($Linha[1]);
              }
            }
          }
          fclose($fd);
          exec("kill $pid");
        }
      }
    }
  }
}
