<?php
    // $Id$
    // $Author$
    // code: fred trotter (ftrotter@synseer.com)
    // lic: GPL, v2

class FreeMEDSelfTest {

	function FreeMEDSelfTest () {
		// Check for skipping this entire thing
		if (file_exists('./.healthy')) {
			// Healthy installation, skipping
			return true;
		} else {
			$this->SelfTest();
			$touched = touch ('./.healthy');
			if (!$touched) {
				die(
				__("FreeMED was unable to create a file to record the healthy status of the system.")."<br/>\n".
				__("The FreeMED directory should be owned by the user that the webserver is running as...")."<br/>\n".
				__("Usually this is 'apache'. You can also fix this by giving universal write access to the home directory of FreeMED. ")."<br/>\n".
				__("But that is not advisable from a security standpoint. ")."<br/>\n"

				);
			}
		}
	} // end constructor FreeMEDSelfTest

	function SelfTest () {

    // This file has no purpose except to catch the most commone installation 
    // and configuration problems and deal with them in a way that is 
    // freemed centric. This should also be the point at which in the 
    // intial configuration wizard takes place. Viewing lack of initialiation
    // as just another exception case....
    // what I wouldnt do for try/catch...

    //TODO 
    // Seperate error messages into template based html files...
    // Create html error pages with links to web solutions (ie. www.php.net)
    // Make the process database neutral...
    // Add a phase to verify that php-mysql has been installed some kind of "function exist" thing...
    // What needs to be done to verify gettext functionality?? anything??
    
   
// Phase 0 verification of php correctness
// taken care of by an "error" index.html file
// which should never be seen if apache/php/php-mysql are all correct...

//Phase I - Database connectivity
// First we need to make an attempt to handshake with the database.
// If that doesnt work then we either give helpful error messages or we 
// use a Wizard to set things up

// We want to interpret the errors that we get for the user. 
// that means we need to turn the normal error handling off for a littlewhile
// Note to Jeff: maybe we should have a "developer mode" where this doesnt happen...


error_reporting(1);

// This makes the source of my syslogs "freemed" !!
openlog("freemed", LOG_PID | LOG_PERROR, LOG_LOCAL0);

// lets load the values with which we will connect to the database
// user password host etc... these can be found here...
syslog(LOG_INFO,"class.FreeMEDSelfTest.php|Running Self Test... about to load settings.php"); 
include_once ("lib/settings.php");
if(LOG_LEVEL==0){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|settings.php loaded");}


// then lets begin to connect based on these values
// Jeff I think that we should just open a help html file here???
// Yes that is the best solution I think...

//$print_dbh = DB_HOST;
//$print_dbu = DB_USER;
//$print_dbp = DB_PASSWORD;
//print ($print_dbh.$print_dbu.$print_dbp);


//$link = mysql_connect("localhost","root","password")
// for testing...

if(LOG_LEVEL==0){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|Attempting to connect to MySQL");}
//if(LOG_LEVEL==0){syslog(LOG_INFO,"using ".DB_HOST." ".DB_USER." ".DB_PASSWORD);}

//because I am supressing error messages
// I need to verify that mysql_connect exists...
// if it doesnt then I am in trouble...

if(!function_exists('mysql_connect'))
{
	die(" Hello Intrepid User <br/><br/>".
	__("It seems that you are trying to use a version of php that is not capable of accessing mysql.")."<br/>\n".
	__("Check to make sure that you have a php-mysql module installed or compiled in.")."<br/>\n".
	__("If you have the module you need to make sure you are using it by adding in into php.ini")."<br/>\n"
	);
	
}


$link = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD)
        or die(
		__("FreeMED cannot seem to connect to the database.")."<br/>\n".
                __("There are many reasons that this could happen.")."<br/>\n".
		__("The debug procedure is as follows:")."<br/><br/>\n".
		"1. ".__("Is MySQL installed? This needs to happen before anything else.")."<br/>\n".
		__("If you are using Redhat Linux then you need to install the MySQL RPMs.")."<br/>\n".
		__("Debian GNU/Linux users should type ")." ".
			"<b>apt-get update; apt-get install mysql-server</b><br/><br/>\n".
    		"2. ".__("There must be a database user who has complete access to the database that FreeMED will use.")." ".
		__("By default this database should be named 'freemed'.")."<br/>\n".
		__("This database must me accessable by this user.")."<br/><br/>\n".

		"3. ".__("In order to connect to the database three things need to set correctly: the database user, the database password, and the database location.")."<br/>\n".
		__("These values are configured in ")."<b>lib/settings.php</b>.<br/>\n".
		__("If you installed FreeMED in the default location then you need to edit")." <b>/usr/share/freemed/lib/settings.php</b>.<br/><br/>\n".
                "4. ".__("In order to connect, the MySQL database must be running.")."<br/>\n".
		__("On Redhat you should type")."<br/>\n".
		"<b>service mysqld start</b><br>\n".
		__("and then hit the enter key. You should see an 'OK'.").
		"<br/>\n"
	);
if(LOG_LEVEL==0){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|Connected.");}
// Phase II Select database Check...
// Has the freemed database been created?
// if so then this command will succeed.             
if(LOG_LEVEL==0){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|Attempting to select DB=".DB_NAME." ....");}
mysql_select_db(DB_NAME) or die(
                __("FreeMED could not select the database.")."<br/>\n".
		__("If you are just installing FreeMED, and you are using the MySQL database, then you need to type:")."<br/>\n".
		"<b>mysqladmin -u root -p create freemed</b><br/>".
		__("and then hit return.")." ".
		__("You will prompted for the root password to the database, and the FreeMED database will be created.")."<br/>\n".
         	__("If you still are seeing this then you might need to check your database permisions!")."<br/>\n".
		__("Make sure that the user configured in lib/settings.php has access to the FreeMED database.")."<br/>\n"
		);

if(LOG_LEVEL==0){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|Selected.");}

// Phase III - Verification of intialization
// Instead of using "root" as the default account name lets switch
// to "admin" this does not conflict with the namespace of unix/sql or windows...
// We will define "Uninitialized" as meaning that the "admin" account
// is not in place... if it is not then we will enter into the initalize wizard.
// Thus an unintialized database will never show the login screen (less user confusion)
// 
$query = ("SELECT username FROM user WHERE id='1'");
if(LOG_LEVEL==0){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|Checking existence of user admin.");}
if(LOG_LEVEL==0||LOG_SQL){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|".$query);}
if(!($result = @mysql_query($query)))
{// if we didnt get anything then...
   // include_once("init_wizard.php"); 

if(LOG_ERROR){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|user admin select failure. Database not init()ed creating admin...");}
   header("Refresh: 0;url=init_wizard.php?action=login");

}
if(LOG_LEVEL==0){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|admin exists");}



//if
//include_once("init_wizard.php");




// if we get here we can be sure that the system works acceptably well
// we can assume that the user table exists and is safe
// which means we can remove the kludge from the authenticate file,
// allowing me to build out proper authentication...
							
if(LOG_LEVEL==0){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|closing test link");}
mysql_close($link);

if(LOG_LEVEL<=99){syslog(LOG_INFO,"class.FreeMEDSelfTest.php|Self Test Passed, returning to login");}

	} // end function Check()

} // end class FreeMEDSelfTest

?>
