<?php
	// $Id$
	// $Author$
	// code: fred trotter (ftrotter@synseer.com)
	// lic: GPL, v2

include_once("lib/freemed.php");
$GLOBALS['__freemed']['no_menu_bar'] = true;

// Catch direct calls 
if (!$action) { die(__("The initialization wizard cannot be called directly.")); }

   // this is the begining of a wizard init system.
   // this is the most important page to secure because access to it
   // will allow a user to destroy the database.
   // As a result there will be two security functions added in 

   //1. IP based authentication with the default set to localhost.
   //2. The database user and password must be regiven.

   // 1. Means that by default it will be impossible to initalize the database
   // from any other hosts. Most people will be installing on a local X config so this will be easy
   // but for those ssh fans this wizard will need to be lynx friendly
   // however if an end-user wants to make it less secure and allow "anywhere" configuration
   // that will be allowed, but the defaults will still be secure

   // 2. Means that the person using the web interface has access to the database , and settings.php
   // effectivley demonstrating that the web interface is actually a lower level of interface for this 
   // user. He is using the wizard for convience and he is not a web hacker using it to escalate privileges

   // TODO 
   // 1. Add code to enforce timed login!!

$page_name = "init_wizard.php";   

// IP based authentication check

if(0!=strcmp($_SERVER['REMOTE_ADDR'],INIT_ADDR)){	
	print __("In order to access the freemed intialization, your web client must come from the host defined in lib/settings.php.");
	print "<br/>\n";
	print __("Normally that means that you must be on the same host that FreeMED is running on (localhost).");
	print "<br/>\n";
	print __("You must either change your host, or change the value found in settings.php to match your host.");
	print "<br/>\n";
	print __("SECURITY NOTE: The default is to limit access to this function to localhost. It is wise to leave this default alone.")." ";
	print __("This function is capable of destroying the entire database and the value in settings.php will control future access to this function.")." ";
	print __("So if possible, go sit at the FreeMED box to do this intial configuration.");
	print "<br/>\n";
	die(__("FreeMED expects the intial setup to be done from the localhost. Dying because your IP is not in lib/settings.php as INIT_ADDR."));
}

if ($action=="login") {     
    global $display_buffer;
    $display_buffer .= "
<div ALIGN=\"LEFT\">
	
	".__("This is the the wizard to setup the admin account.")."<br/>
	".__("Before creating that account you must prove that you have the appropriate level of access.")."
	".__("Please provide the database user name and password, as found in settings.php.")."

</div>

<p/>
<form ACTION=\"init_wizard.php\" METHOD=\"POST\">
<table WIDTH=\"100%\" BORDER=\"0\" CELLPADDING=\"2\">
<tr><td ALIGN=\"RIGHT\">
      <input type=\"hidden\" name=\"action\" value=\"auth\"/>
       ".__("Database Username")." :
      </TD><TD ALIGN=\"LEFT\">
      <input TYPE=\"TEXT\" NAME=\"_username\" LENGTH=\"20\" MAXLENGTH=\"32\"/>
      </td>
</tr>
<tr><td ALIGN=\"RIGHT\">
        ".__("Database Password")." :
        </td>
         <td>
             <input TYPE=\"PASSWORD\" NAME=\"_password\" LENGTH=\"20\" MAXLENGTH=\"32\"/>
         </td>
</tr> 
<tr><td ALIGN=\"RIGHT\">
        ".__("Admin Account Password")." :
        </td>
         <td>
             <input TYPE=\"PASSWORD\" NAME=\"_adminpassword1\" LENGTH=\"20\" MAXLENGTH=\"32\"/>
         </td>
</tr> 
<tr><td ALIGN=\"RIGHT\">
        ".__("Confirm Admin Account Password")." :
        </td>
         <td>
             <input TYPE=\"PASSWORD\" NAME=\"_adminpassword2\" LENGTH=\"20\" MAXLENGTH=\"32\"/>
         </td>
</tr> 
</table>
<div ALIGN=\"CENTER\">
  <input TYPE=\"SUBMIT\" VALUE=\"".__("Sign In")."\" CLASS=\"button\" />
  <input TYPE=\"RESET\"  VALUE=\"".__("Clear")."\" CLASS=\"button\" />
</div>
</form>
";
	
// drop to the page...  
template_display();
}

if ($action=="auth") {

	//lets display the banner!!

	// Lets check IP addresses again, otherwise people will 
	// Try to go directly to this page!!

	if(0!=strcmp($_SERVER['REMOTE_ADDR'],INIT_ADDR)){	
		die(__("Page Not Accessible from your IP Address")."<br/>");
	}

	// time constraint psuedo code
	// if (you attempted to login less than a minute ago)
	// {
	// die (" you have to wait 1 min between logins");
	// set database lastlogintime = now;
	// }


	if((0!==(strcmp(DB_USER,$_REQUEST['_username']))) or
	(0!==(strcmp(DB_PASSWORD,$_REQUEST['_password'])))) {
		// impose a time penalty here...
		// something like 30 sec for the first...
		// 1 min for two or more...
		// or hell just 1 min...		

		// set database lastlogintime = now;
		die( __("Incorrect user/password combination")." 1");

	}

	if(0!==(strcmp($_REQUEST['_adminpassword1'],$_REQUEST['_adminpassword2'])))
	{
		die( "admin passwords to not match");
		// no time setting here, if they know the database password
		// then this is just an honest mistake!!
	}
	
	// Here I enter the new admin account into the database!!
	$display_buffer .= __("Database Password Accepted")."... <br/>\n";

	// These should eventually be connected to a die() command!!
	$this_user = CreateObject('FreeMED.User');
	$md5_pass = md5($_REQUEST['_adminpassword1']);
	$this_user->init($md5_pass);

	$display_buffer .= "
<div ALIGN=\"LEFT\">	
".__("User table created.")."<br/>
".__("Admin password set.")."<br/>
	";

	// First, remove module cache, since this will cause FreeMED to not build its
	// tables appropriately:
	@unlink('data/cache/modules');

	// Create module table
	$this_module = CreateObject('FreeMED.BaseModule');
	$this_module->init();
	$display_buffer .= __("Dynamic modules database initialized.")."<br/>\n";

	// Create config table
	$this_config = CreateObject('FreeMED.GeneralConfig');
	$this_config->init();
	$display_buffer .= __("FreeMED configuration database initialized.")."<br/>\n";

	// Load all modules
	$this_ml = freemed::module_cache();
	$display_buffer .= __("All modules initialized.")."<br/>\n";

	$display_buffer .= "
	".__("You will now be returned to the the login prompt.")."<br/>
	".__("You can login using:")."<br/><br/>
	username=admin <br/>
	password=WHAT_YOU_JUST_ENTERED <br/><br/>
	replace WHAT_YOU_JUST_ENTERED with the admin password that you just created <BR>
	</div>
	<br/>
	<div align=\"center\">
	<a href=\"index.php\" class=\"button\">".__("Return to Login")."</a>
	</div>";
  	header("Refresh: 30;url=index.php");	
	template_display();
}

?>
