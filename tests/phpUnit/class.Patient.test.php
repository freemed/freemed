<?php

require_once 'PHPUnit/Framework.php';
include_once ( 'lib/freemed.php' );

 class PatientTest extends PHPUnit_Framework_TestCase
 {
 		
	
		
		
	public function __construct()
 		{
 			//create test dependencies:
 			//  - database object
 			//  - access control list object
 			$GLOBALS['sql']=CreateObject("org.freemedsoftware.core.FreemedDb");
 			$GLOBALS['acl']=CreateObject('org.freemedsoftware.acl.gacl', 
				array (
					// Database information from FreeMED
					'db_type' => 'mysql', // hardcoded for now
					'db_host' => DB_HOST,
					'db_user' => DB_USER,
					'db_password' => DB_PASSWORD,
					'db_name' => DB_NAME,
					'db_table_prefix' => 'acl_',
					// 	Caching and security settings
					'caching' => true,
					'force_cache_expire' => true,
					'cache_expire_time' => 600
				)
			);
 		}
 			

 	//adds a test patient
 	function testAddPatient()
 	{	
 		//test data
 		$test_patient= array (
 			"ptsalut"=>"Mr",
 			"ptsalut_selected"=>"Mr",				//title
 			"ptlname"=>"LastName-Test1",			//last name
 			"ptfname"=>"FirstName-Test1",			//first name
 			"ptmname"=>"MiddleName-Test1",			//middle name
 			"ptsuffix"=>"Jr",						//
 			"ptsuffix_selected"=>"Jr",				//suffix
 			"ptsex"=>"m",							//
 			"ptsex_selected"=>"Male",				//gender
 			"ptdob"=>"2008-02-18",					//date of birth
 			"ptid"=>"PatientPracticeID-Test1",		//patient practice id
 			"undefined"=>"0",
 			"undefined_selected"=>"",
 			"ptprefcontact"=>"home",				//
 			"ptprefcontact_selected"=>"Home",		//prefered contact
 			"pthphone"=>"123456789",				//home phone
 			"ptwphone"=>"123456789",				//work phone
 			"ptfax"=>"123456789",					//fax phone
 			"ptmphone"=>"123456789",				//mobile phone
 			"ptemail"=>"test@e.mail",				//email
 			"ptmarital"=>"single",					//
 			"ptmarital_selected"=>"Single",			//marital status
 			"ptempl"=>"y",							//
 			"ptempl_selected"=>"Yes",				//employment status
 			"ptssn"=>"123456789",					//social security number
 			"ptdmv"=>"drvlic",						//driver licence
 			"ptdoc"=>"0",							//in house provider
 			"ptref"=>"0",							//refering provider
 			"ptpcp"=>"0"							//primary care provider
		);
		
		$duplicate_patient=array(
 			"ptlname"=>"LastName-Test1",			//first name
 			"ptfname"=>"FirstName-Test1",			//last name
 			"ptmname"=>"MiddleName-Test1",			//middle name
 			"ptsuffix"=>"Jr",						//suffix
 			"ptdob"=>"2008-02-18"					//date of birth
		);
		
		$test_addresses=array(
			array(
				"type"=>"H",						//address type
				"relate"=>"S",						//relation to patient
				"line1"=>"AddressLine1-Test1.1",	//address line 1
				"line2"=>"AddressLine2-Test1.1",	//address line 2
				"csz"=>"Arad, RO 2900 Romania",		//city state zip
				"active"=>1,						//active or not
				"id"=>0								//active or not
			),
			array(
				"type"=>"H",						//address type	
				"relate"=>"P",						//relation to patient
				"line1"=>"AddressLine1-Test1.2",	//address line 1
				"line2"=>"AddressLine2-Test1.2",	//address line 2
				"csz"=>"Timisoara, RO 2700 Romania",//city state zip
				"active"=>0,						//active or not
				"id"=>0 							//0 means new record
			)
		);
		
		
		//create required objects
 		$p=CreateObject("org.freemedsoftware.module.PatientModule");
 		$pi=CreateObject("org.freemedsoftware.api.PatientInterface");
 		
 		//check for duplicates
 		$this->assertEquals(false,$pi->CheckForDuplicatePatient($duplicate_patient));
 		
 		//add new patient
 		$patient_id=$p->add($test_patient);
 		$this->assertNotEquals(false,$patient_id);
 		
 		//add addresses for new patient
 		$setAddr=$p->SetAddresses($patient_id,$test_addresses);
 		$this->assertEquals(true,$setAddr);	
 		
 		//get addresses from database
 		$db_addresses=$p->GetAddresses($patient_id);
 		//test result length with test data length
 		$this->assertEquals(2,sizeof($db_addresses));
 		//test result values with test data values
 		for ($i=0;$i<sizeof($db_addresses);$i++)
 		{
 			$this->assertEquals($test_addresses[$i]['type'],$db_addresses[$i]['type']);
 			$this->assertEquals($test_addresses[$i]['relate'],$db_addresses[$i]['relate']);
 			$this->assertEquals($test_addresses[$i]['line1'],$db_addresses[$i]['line1']);
 			$this->assertEquals($test_addresses[$i]['line2'],$db_addresses[$i]['line2']);
 			//split city, state, zip value
 			$this->assertNotEquals(0,preg_match("/([^,]+), ([A-Z]{2}) ([A-Z0-9\-]+) ([A-Za-z\.\-\ ]+)/", $test_addresses[$i]['csz'], $reg));
 			//assert for city state zip
 			$this->assertEquals($reg[1],$db_addresses[$i]['city']);
 			$this->assertEquals($reg[2],$db_addresses[$i]['stpr']);
 			$this->assertEquals($reg[3],$db_addresses[$i]['postal']);
 			$this->assertEquals($reg[4],$db_addresses[$i]['country']);
 		}
 		//verify patient record with test values
 		$db_patient=$p->GetRecord($patient_id);
 		$this->assertEquals($test_patient['ptsalut'],$db_patient['ptsalut']);
 		$this->assertEquals($test_patient['ptlname'],$db_patient['ptlname']);
 		$this->assertEquals($test_patient['ptfname'],$db_patient['ptfname']);
 		$this->assertEquals($test_patient['ptmname'],$db_patient['ptmname']);
 		$this->assertEquals($test_patient['ptsuffix'],$db_patient['ptsuffix']);
 		$this->assertEquals($test_patient['ptsex'],$db_patient['ptsex']);
 		$this->assertEquals($test_patient['ptdob'],$db_patient['ptdob']);
 		$this->assertEquals(substr($test_patient['ptid'],0,10),$db_patient['ptid']);
 		$this->assertEquals($test_patient['ptprefcontact'],$db_patient['ptprefcontact']);
 		$this->assertEquals($test_patient['pthphone'],$db_patient['pthphone']);
 		$this->assertEquals($test_patient['ptwphone'],$db_patient['ptwphone']);
 		$this->assertEquals($test_patient['ptfax'],$db_patient['ptfax']);
 		$this->assertEquals($test_patient['ptmphone'],$db_patient['ptmphone']);
 		$this->assertEquals($test_patient['ptemail'],$db_patient['ptemail']);
 		$this->assertEquals($test_patient['ptmarital'],$db_patient['ptmarital']);
 		$this->assertEquals($test_patient['ptssn'],$db_patient['ptssn']);
 		$this->assertEquals($test_patient['ptempl'],$db_patient['ptempl']);
 		$this->assertEquals($test_patient['ptdmv'],$db_patient['ptdmv']);
 		$this->assertEquals($test_patient['ptdoc'],$db_patient['ptdoc']);
		//$this->assertEquals($test_patient['ptref'],$db_patient['ptref']);
		$this->assertEquals($test_patient['ptpcp'],$db_patient['ptpcp']);
 	}
 }
?>