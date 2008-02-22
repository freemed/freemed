<?php
require_once 'PHPUnit/Framework.php';
include_once ( 'lib/freemed.php' );

class LoginTest extends PHPUnit_Framework_TestCase
{
    public function testValidateLogin()
    {
    	//Sql connection object
    	$GLOBALS['sql']=CreateObject("org.freemedsoftware.core.FreemedDb");
    	
    	//create login object 
    	$test_obj=CreateObject("org.freemedsoftware.public.Login");
 
        $this->assertEquals(true, $test_obj->Validate("admin","admin_password"));
    }
 
}

?>