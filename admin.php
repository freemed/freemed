<?php
 // $Id$
 // note: administrative functions
 // code: jeff b (jeff@ourexchange.net)
 //       language support by Max Klohn (amk@span.ch)
 // lic : GPL, v2

$page_name = basename($GLOBALS["$PHP_SELF"]);
include_once ("lib/freemed.php");

//----- Login/authenticate
freemed_open_db ();

//----- Set configuration variables
$config_vars = array (
	"gfx", // gfx option (graphics enhanced)
	"cal_ob", // calendar overbooking
	"calshr", // calendar start time
	"calehr", // calendar end time
	"dtfmt", // date format
	"phofmt", // phone format
	"folded"  // do we fold multipage forms?
);

if (!freemed::user_flag(USER_ADMIN)) {
	$page_title = __("Administration")." :: ".__("ERROR");
	$display_buffer .= "
	<p/>
	<div ALIGN=\"CENTER\">".__("No Admin Menu Access")."</div>
	<p/>
	<div ALIGN=\"CENTER\">
	<a class=\"button\" HREF=\"main.php\"
	>".__("Return to the Main Menu")."</a>
	</div>
	<p/>
	";
	template_display();
}

if ($action=="cfgform") {

	// this is the frontend to the config
	// database.

	// Add help link for cfgform
	$menu_bar[__("Configuration Help")] = help_url("admin.php", "cfgform");

	//----- Create notebook widget
	$book = CreateObject('PHP.notebook',
		array('action'),
		NOTEBOOK_TABS_LEFT | NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH
	);
	$book->set_submit_name(__("Configure"));
	$book->set_refresh_name(__("Refresh"));
	$book->set_cancel_name(__("Cancel"));

	//----- Pull in all configuration variables
	if (!$book->been_here()) {
		$query = "SELECT * FROM config";
		$result = $sql->query($query);
		while ($r = $sql->fetch_array($result)) {
			extract($r);
			${$c_option} = stripslashes($c_value);
		}

		//----- Push page onto the stack
		page_push();
	}

	$page_title = "Configuration";

	// Form static portion of configuration array
	$book->add_page(
		__("Standard Config"),
		array($config_vars),
		html_form::form_table(array(

		__("Graphics Enhanced") =>
		html_form::select_widget("gfx",
			array (
				__("Disabled") => "0",
				__("Enabled")  => "1"
			)
		),

		__("Scheduling Start Time") =>
		html_form::select_widget("calshr",
			array (
				__("Default") => "",
				"4 am"  => "4",
				"5 am"  => "5",
				"6 am"  => "6",
				"7 am"  => "7",
				"8 am"  => "8",
				"9 am"  => "9",
				"10 am" => "10"
			)
    		),

		__("Scheduling End Time") =>
		html_form::select_widget("calehr",
			array (
				__("Default") => "",
				"2 pm"  => "14",
				"3 pm"  => "15",
				"4 pm"  => "16",
				"5 pm"  => "17",
				"6 pm"  => "18",
				"7 pm"  => "19",
				"8 pm"  => "20",
				"9 pm"  => "21",
				"10 pm" => "22",
				"11 pm" => "23"
			)
		),

		__("Calendar Overbooking") =>
		html_form::select_widget("cal_ob",
			array (
				__("Enabled")  => "enable",
				__("Disabled") => "disable"
			)
		),

		__("Date Format") =>
		html_form::select_widget("dtfmt",
			array (
				"YYYY-MM-DD" => "ymd",
				"MM-DD-YYYY" => "mdy",
				"DD-MM-YYYY" => "dmy",
				"YYYY-DD-MM" => "ydm"
			)
		),

		__("Phone Number Format") =>
		html_form::select_widget("phofmt",
			array (
				__("United States")." (XXX) XXX-XXXX" => "usa",
				__("France")." (XX) XX XX XX XX" => "fr",
				__("Unformatted")." XXXXXXXXXX" => "unformatted"
			)
		),

		__("Fold Multipage Forms?") =>
		html_form::select_widget("folded",
			array (
				__("yes") => "yes",
				__("no")  => "no"
			)
		)
		))
	);

	// Check for dynamic components
	if (!is_object($module_list)) {
		$module_list = CreateObject('PHP.module_list', PACKAGENAME, 'modules/');
	}
	foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $__crap => $v) {
		if (is_array($v['META_INFORMATION']['global_config'])) {
			$this_one = $v['META_INFORMATION']['global_config'];
			foreach ($this_one AS $gc_k => $gc_v) {
				// If we find ::'s... (for function)
				if (!(strpos($gc_v, '::') === false)) {
					$command = '$this_one["'.$gc_k.'"] = '.$gc_v.';';
					//print "eval : ".$command."<br/>\n";
					eval($command);
				} else {
					$this_one["$gc_k"] = $gc_v;
				}
			}
			$book->add_page(
				__($v['MODULE_NAME']),
				$v['META_INFORMATION']['global_config_vars'],
				html_form::form_table($this_one)
			);
		}
	}

	if ($book->is_done()) {
		$page_title = __("Update Config");
		$display_buffer .= "<p/>\n";

		// Check for dynamic components for config_vars
		if (!is_object($module_list)) {
			$module_list = CreateObject('PHP.module_list', PACKAGENAME, 'modules/');
		}
		foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $__crap => $v) {
			if (is_array($v['META_INFORMATION']['global_config_vars'])) {
				$config_vars = array_merge (
					$config_vars,
					$v['META_INFORMATION']['global_config_vars']
				);
			}
		}

		//----- Commit all configuration variables
		foreach ($config_vars AS $_garbage => $v) {
			$_q = "SELECT * FROM config WHERE ".
				"c_option='".addslashes($v)."'";
			$_r = $sql->query($_q);
			if (!$sql->results($_r)) {
				$q = $sql->insert_query(
					'config',
					array (
						'c_option' => $v,
						'c_value' => ${$v}
					)
				);
			} else {
				$q = $sql->update_query(
					'config',
					array ('c_value' => ${$v}),
					array('c_option' => $v)
				);
			}
			$query = $sql->query($q);
			if (($debug) AND ($q))
				$display_buffer .= "$v = ${$v}<br/>\n";
			}


		$display_buffer .= "
			<p/>
			<div ALIGN=\"CENTER\"><b>".__("Configuration Complete")."</b></div>
			<p/><div ALIGN=\"CENTER\">
			<a class=\"button\" HREF=\"".page_name()."\"
			>".__("Return To Administration Menu")."</a>
			</div>
		";
	} elseif ($book->is_cancelled()) {
		Header("Location: ".page_name());
		die();
	} else {
		$display_buffer .= $book->display();
	}

} elseif ($action=="reinit") {
	$page_title = __("Reinitialize Database");
  
    // here, to prevent problems, we ask the user to check that they
    // REALLY want to...

  $display_buffer .= "\n<div ALIGN=\"CENTER\">\n";
  $display_buffer .= __("Are you sure you want to reinitialize the database?")."\n";
  $display_buffer .= "<br/><u><b>".__("This is an IRREVERSIBLE PROCESS!")."</b></u><br/>\n";
  $display_buffer .= "\n</div>\n";

  $display_buffer .= "<br/><div ALIGN=\"CENTER\">\n";

  $display_buffer .= "
   <form ACTION=\"admin.php\" METHOD=\"POST\">
   <input TYPE=\"CHECKBOX\" NAME=\"first_time\" VALUE=\"first\"/>
   <i>".__("First Initialization")."</i><br/>
   <input TYPE=\"CHECKBOX\" NAME=\"re_load\" VALUE=\"reload\"/>
   <i>".__("Reload Stock Data")."</i><br/>
   <input TYPE=\"HIDDEN\" NAME=\"action\" VALUE=\"reinit_sure\"/>
   <table BORDER=\"0\" ALIGN=\"CENTER\"><tr><td>
   <input class=\"button\" TYPE=\"SUBMIT\" VALUE=\"".__("Continue")."\"/>
   </form>

   </td><td>

   <form ACTION=\"admin.php\" METHOD=\"POST\">
   <input class=\"button\" name=\"submit\" TYPE=\"SUBMIT\" VALUE=\"".__("Cancel")."\"/>
   </form>

   </td></tr></table>
   </div>
  ";

} elseif ($action=="reinit_sure") {
	// here we actually put the reinitialization (read - wiping
	// and creating the database structure again) code... so that
	// stupids don't accidentally click on it and... oops!

	// Notice that all of the possible essential table definitions
	// (except for module) have been moved out of admin. This is
	// to increase ease of upgrade, etc. They are in hidden
	// modules.

	$display_buffer .= "<ul>".__("Creating tables")."... \n";
	$display_buffer .= "$re_load\n";

	// generate test table, if debug is on
	if ($debug) {
		$result=$sql->query("DROP TABLE test");
		$result=$sql->query($sql->create_table_query(
			'test',
			array(
				'name' => SQL_CHAR(10),
				'other' => SQL_CHAR(12),
				'phone' => SQL_INT(0),
				'id' => SQL_SERIAL
			)
		));
		if ($result) { $display_buffer .= "<li>".__("test db")."</li>\n"; }
	} // end debug section

	// Generate module table
	$result=$sql->query("DROP TABLE module"); 
	$result=$sql->query($sql->create_table_query(
		'module',
		array(
			'module_name' => SQL_VARCHAR(100),
			'module_version' => SQL_VARCHAR(50),
			'id' => SQL_SERIAL
		)
	));
	if ($result) { $display_buffer .= "<li>".__("Modules")."</li>\n"; }

	// Generate user db
	$result=$sql->query("DROP TABLE user"); 
	$result=$sql->query($sql->create_table_query(
		'user',
		array(
			'username' => SQL_NOT_NULL(SQL_VARCHAR(16)),
			'userpassword' => SQL_NOT_NULL(SQL_VARCHAR(16)),
			'userdescrip' => SQL_VARCHAR(50),
			'userlevel' => SQL_INT_UNSIGNED(0),
			'usertype' => SQL_ENUM (array("phy", "misc")),
			'userfac' => SQL_BLOB,
			'userphy' => SQL_BLOB,
			'userphygrp' => SQL_BLOB,
			'userrealphy' => SQL_INT_UNSIGNED(0),
			'usermanageopt' => SQL_BLOB,
			'id' => SQL_SERIAL
		), array ('id', 'username')
	));
	/*
		eventually wrapper should handle...
		PRIMARY KEY (id),
		UNIQUE idx_id (id),
		KEY (username),
		UNIQUE idx_username (username)
	*/
	if ($result) $display_buffer .= "<li>".__("Users")."</li>\n";

	$result = $sql->query($sql->insert_query(
		"user",
		array (
    			"username" => "root",
			"userpassword" => DB_PASSWORD,
			"userdescrip" => "Superuser",
			"userlevel" => USER_ROOT,
			"usertype" => "misc",
			"userfac" => "-1",
			"userphy" => "-1",
			"userphygrp" => "-1",
			"userrealphy" => "0",
			"usermanageopt" => ""
    		)
    	));
  	if ($result) $display_buffer .= "<li><i>[[".
		__("Added Superuser")."]]</i></li>\n";

/**********************************************************************

  // generate physician availability map
  $result=$sql->query("DROP TABLE phyavailmap");
  $result=$sql->query("CREATE TABLE phyavailmap (
    pamdatefrom      DATE,
    pamdateto        DATE,
    pamtimefromhour  INT UNSIGNED,
    pamtimefrommin   INT UNSIGNED,
    pamtimetohour    INT UNSIGNED,
    pamtimetomin     INT UNSIGNED,
    pamphysician     INT UNSIGNED,
    pamcomment       VARCHAR(75),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<li>".__("Physician Availability Map")."</li>\n";

  // generate room equipment inventory db
  $result=$sql->query("DROP TABLE roomequip"); 
  $result=$sql->query("CREATE TABLE roomequip (
    reqname         VARCHAR(100),
    reqdescrip      TEXT,
    reqdateadd      DATE,
    reqdatemod      DATE,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<li>".__("Room Equipment")."</li>\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data("roomequip"))
    		$display_buffer .= "<I>(".__("Stock Room Equipment Data").")</I> \n ";
  }
*/

/**********************************************************************
 May need this later, but for now, don't use
 **********************************************************************
	// generate action log table db
	$result=$sql->query("DROP TABLE log"); 
	$result=$sql->query($sql->create_table_query(
		'log',
		array (
			'datestamp' => SQL_DATE,
			'user' => SQL_INT_UNSIGNED(0),
			'db_name' => SQL_VARCHAR(20),
			'rec_num' => SQL_INT_UNSIGNED(0),
			'comment' => SQL_TEXT,
			'id' => SQL_SERIAL
		), array ('id')
	));
	if ($result) $display_buffer .= "<li>".__("Action Log")."</li>\n";

**********************************************************************/

	// generate configuration table info
	$result=$sql->query("DROP TABLE config"); 
	$result=$sql->query($sql->create_table_query(
		'config',
		array (
			'c_option' => SQL_CHAR(6),
			'c_value' => SQL_VARCHAR(100),
			'id' => SQL_SERIAL
		), array ('id')
	));
	if ($result) $display_buffer .= "<li>".__("Configuration")."</li>\n";

	if ($re_load)
	{
		$stock_config = array (
			'gfx' => '1',
			'calshr' => $cal_starting_hour,
			'calehr' => $cal_ending_hour,
			'cal_ob' => 'enable',
			'dtfmt' => 'ymd',
			'phofmt' => 'unformatted',
			'folded' => 'yes'
		);
		foreach ($stock_config AS $key => $val) {
			if (!is_integer($key)) {
				$result = $sql->query($sql->insert_query(
					'config',
					array (
						'c_option' => $key,
						'c_value' => $val
					)
				));
			}
		}
	}

/********************************************************************
 ***** Fax tables... need to be reenabled
 ********************************************************************

  // generate incoming faxes table
  $result=$sql->query("DROP TABLE infaxes"); 
  $result=$sql->query("CREATE TABLE infaxes (
    infcode	  VARCHAR(5),  
    infsender	  VARCHAR(50),
    inftotpages	  INT UNSIGNED,
    infthispage	  INT UNSIGNED,
    inftimestamp  TIMESTAMP,
    infimage	  VARCHAR(50),
    inforward	  ENUM(\"no\",\"yes\") NOT NULL,		
    infack	  ENUM(\"no\",\"yes\") NOT NULL,
    infptid	  VARCHAR(10),
    infphysid	  VARCHAR(10),
    id            INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    );");
   if ($result) $display_buffer .= "<li>".__("Incoming Faxes")."</li>\n";

  // generate fax sender lookup table
  $result=$sql->query("DROP TABLE infaxlut"); 
  $result=$sql->query("CREATE TABLE infaxlut (
    lutsender VARCHAR(50),
    lutname   VARCHAR(50),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    );");
  if ($result) $display_buffer .= "<li>".__("Fax Sender Lookup")."</li>\n";

 *******************************************************************
 Printer table
 *******************************************************************

   // generate printers table (19991008)
   $result = $sql->query ("DROP TABLE printer"); 
   $result = $sql->query ("CREATE TABLE printer (
     prntname   VARCHAR(50),
     prnthost   VARCHAR(50),
     prntaclvl  ENUM(\"9\",\"8\",\"7\",\"6\",\"5\",\"4\",\"3\",\"2\",\"1\",\"0\") NOT NULL,
     prntdesc   VARCHAR(100),
     id         INT NOT NULL AUTO_INCREMENT,
     PRIMARY KEY (id)
     );");
   if ($result) $display_buffer .= "<li>".__("Printers")."</li>\n";

 ********************************************************************
 ***** Simple reports ... support needs to be added again
 ********************************************************************

  // generate simple reports table
  $result=$sql->query("DROP TABLE simplereport");
  $result=$sql->query("CREATE TABLE simplereport (
    sr_label       VARCHAR(50),
    sr_type        INT UNSIGNED,
    sr_text        TEXT,
    sr_textf       TEXT,
    sr_textcm      TEXT,
    sr_textcf      TEXT,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<li>".__("Simple Reports")."</li>\n";

  // old reports
  $result = $sql->query ("DROP TABLE oldreports"); 
  $result = $sql->query ("CREATE TABLE oldreports (
     oldrep_timestamp          DATE,
     oldrep_label              VARCHAR(50),
     oldrep_type               INT UNSIGNED,
     oldrep_sender             INT UNSIGNED,
     oldrep_delivery           VARCHAR(20),
     oldrep_author             INT UNSIGNED,
     oldrep_dateline           VARCHAR(100),
     oldrep_header1            VARCHAR(100),
     oldrep_header2            VARCHAR(100),
     oldrep_header3            VARCHAR(100),
     oldrep_header4            VARCHAR(100),
     oldrep_header5            VARCHAR(100),
     oldrep_header6            VARCHAR(100),
     oldrep_header7            VARCHAR(100),
     oldrep_dest1              VARCHAR(100),
     oldrep_dest2              VARCHAR(100),
     oldrep_dest3              VARCHAR(100),
     oldrep_dest4              VARCHAR(100),
     oldrep_signature1         VARCHAR(100),
     oldrep_signature2         VARCHAR(100),
     oldrep_text               TEXT,
     id                        INT NOT NULL AUTO_INCREMENT,
     PRIMARY KEY (id)
     );");
  if ($result) $display_buffer .= "<li>".__("Old Reports")."</li>\n";

********************************************************************/

	// Initial module load
	$modules = CreateObject('PHP.module_list', PACKAGENAME);

	// now generate "return code" so that we can get back to the
	// admin menu... or perhaps skip that... ??
	$display_buffer .= "</ul><b>".__("done").".</b><br/>\n";
  
	$display_buffer .= "
	<p/>
	<div ALIGN=\"CENTER\">
	".template::link_button(
		__("Return to Administration Menu"),
		"admin.php"
	)."
	</div>
	";

} else {

  // actual menu code for admin menu goes here \/

//----- Set page title
$page_title = __("Administration Menu");

//----- Push page onto the stack
page_push();

$display_buffer .= "
  <table WIDTH=\"100%\" VALIGN=\"CENTER\" ALIGN=\"CENTER\" BORDER=\"0\"
   CELLSPACING=\"2\" CELLPADDING=\"0\">
"; // begin standard font

$userdata = $_SESSION["authdata"];

$display_buffer .= "
 <tr><td ALIGN=\"RIGHT\">
  <a HREF=\"export.php\"
  ><img SRC=\"img/kfloppy.gif\" BORDER=\"0\" ALT=\"\"/></a>
 </td><td ALIGN=\"LEFT\">
  <a HREF=\"export.php\"
  >".__("Export Databases")."</a>
 </td></tr> 
 <tr><td ALIGN=\"RIGHT\">
  <a HREF=\"import.php\"
  ><img SRC=\"img/ark.gif\" BORDER=\"0\" ALT=\"\"/></a>
 </td><td ALIGN=\"LEFT\">
 <a HREF=\"import.php\"
 >".__("Import Databases")."</a>
 </td></tr>
 <tr><td ALIGN=\"RIGHT\">
  <a HREF=\"module_information.php\"
  ><img SRC=\"img/magnify.gif\" BORDER=\"0\" ALT=\"\"/></A>
 </td><td ALIGN=\"LEFT\">
 <a HREF=\"module_information.php\"
  >".__("Module Information")."</a>
 </td></tr>
 ";

if ($userdata["user"]==1) // if we are root...
 $display_buffer .= "
  <tr><td ALIGN=\"RIGHT\">
   <a HREF=\"admin.php?action=reinit\"
   ><img SRC=\"img/Gear.gif\" BORDER=\"0\" ALT=\"\"/></a>
  </TD><TD ALIGN=\"LEFT\">
  <a HREF=\"admin.php?action=reinit\"
  >".__("Reinitialize Database")."</a>
  </td></tr>
 ";

$display_buffer .= "
  <tr><td ALIGN=\"RIGHT\">
   <a HREF=\"admin.php?action=cfgform\"
   ><img SRC=\"img/config.gif\" BORDER=\"0\" ALT=\"\"/></A>
  </td><td ALIGN=\"LEFT\">
  <a HREF=\"admin.php?action=cfgform\"
  >".__("Update Config")."</a>
  </td></tr>
";

if ($userdata["user"] == 1) { // if we are root...
  $display_buffer .= "
    <tr><td ALIGN=\"RIGHT\">
     <a HREF=\"user.php?action=view\"
     ><img SRC=\"img/monalisa.gif\" BORDER=\"0\" ALT=\"\"/></a>
    </td><td ALIGN=\"LEFT\">
    <a HREF=\"user.php?action=view\"
     >".__("User Maintenance")."</a>
    </td></tr>
  ";
}

  $display_buffer .= "
    <tr><td ALIGN=\"RIGHT\">
     <img SRC=\"img/HandPointingLeft.gif\" BORDER=\"0\" ALT=\"\"/></a>
    </td><td ALIGN=\"LEFT\">
     <a HREF=\"main.php\"
     ><b>".__("Return to the Main Menu")."</b></a>
    </td></tr>
    </table>
  "; // end standard font
}

$display_buffer .= "
	<p/>
	<div ALIGN=\"CENTER\">
	<a class=\"button\" HREF=\"main.php\">".__("Return to the Main Menu")."</a>
	</div>
"; // return to main menu tab...

//----- Display template
template_display();

?>
