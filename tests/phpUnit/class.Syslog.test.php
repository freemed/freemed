
<?php
require_once 'PHPUnit/Framework.php';
include_once ('lib/freemed.php'); 

class SyslogTest extends PHPUnit_Framework_TestCase
{
    public function testSyslogError()
    {
    	print("\n LOG_ERR value : ".LOG_ERR);
    	syslog(LOG_ERR,"\n FreemedTest - Logging error");
    }
 	
	public function testSyslogInfo()
    {
    	print("\n LOG_INFO value : ".LOG_INFO);
    	syslog(LOG_INFO,"\n FreemedTest - Logging info");
    }

}
?>
