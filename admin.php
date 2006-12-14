<?php
	// $Id$
	// $Author$

$page_name = basename($GLOBALS["$PHP_SELF"]);
include_once ("lib/freemed.php");

//----- Login/authenticate
freemed::connect ();

//----- Set configuration variables
$config_vars = array (
	"gfx", // gfx option (graphics enhanced)
	"cal_ob", // calendar overbooking
	"calshr", // calendar start time
	"calehr", // calendar end time
	"hourformat", // hour format (12/24)
	"dtfmt", // date format
	"phofmt", // phone format
	"default_area_code", // default area code
	"drug_widget_type", // type of drug widget present
	"date_widget_type", // type of date widget present
	"folded", // do we fold multipage forms?
	"lock_override", // ability to override locked records
	"tooltip", // show tips?
	"fax_nocover" // remove cover page?
);

if (!freemed::acl('admin', 'menu')) {
	trigger_error(__("You don't have access to the administration menu."), E_USER_ERROR);
}

if ($action=="cfgform") {
	// Check ACLs
	if (!freemed::acl('admin', 'config')) {
		trigger_error(__("You do not have access to system configuration."));
	}

	// this is the frontend to the config
	// database.

	// Add help link for cfgform
	$menu_bar[__("Configuration Help")] = help_url("admin.php", "cfgform");

	//----- Create notebook widget
	$book = CreateObject('PHP.notebook',
		array('action'),
		NOTEBOOK_TABS_LEFT | NOTEBOOK_COMMON_BAR | NOTEBOOK_STRETCH |
			NOTEBOOK_SCROLL
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

		__("Hour Format") =>
		html_form::select_widget("hourformat",
			array (
				"12 hour"  => "12",
				"24 hour"  => "24",
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

		__("Default Area Code") =>
		html_form::text_widget('default_area_code', 3),

		__("Prescription Drug Widget") =>
		html_form::select_widget("drug_widget_type",
			array (
				__("rxlist.com Drug Listing") => 'rxlist',
				__("Combo with past drugs") => 'combobox'
			)
		),

		__("Date Widget") =>
		html_form::select_widget("date_widget_type",
			array (
				__("javascript widget") => 'js',
				__("split text entry") => 'split'
			)
		),

		__("Fold Multipage Forms?") =>
		html_form::select_widget("folded",
			array (
				__("yes") => "yes",
				__("no")  => "no"
			)
		),

		__("Show Tips?") =>
		html_form::select_widget("tooltip",
			array (
				__("no")  => "0",
				__("yes") => "1"
			)
		),


		__("Fax Cover Page") =>
		html_form::select_widget("fax_nocover",
			array (
				__("Attach Cover Page") => "0",
				__("No Cover Page")  => "1"
			)
		),

		__("Lock Record Override") =>
		freemed::multiple_choice (
			"SELECT CONCAT(username, ' (', userdescrip, ')') ".
				"AS descrip, id FROM user ORDER BY descrip",
			"descrip",
			"lock_override",
			fm_join_from_array($lock_override)
		)
		
		))
	);

	// Check for dynamic components
	if (!is_object($module_list)) {
		$module_list = freemed::module_cache();
	}

	// Loop
	foreach ($GLOBALS['__phpwebtools']['GLOBAL_MODULES'] AS $__crap => $v) {
		if (is_array($v['META_INFORMATION']['global_config'])) {
			$this_one = $v['META_INFORMATION']['global_config'];
			$this_one_finished = array();
			GettextXML::textdomain(strtolower($v['MODULE_CLASS']));
			foreach ($this_one AS $gc_k => $gc_v) {
				// If we find ::'s... (for function)
				if (!(strpos($gc_v, '::') === false)) {
					$command = '$this_one_finished["'.__($gc_k).'"] = '.$gc_v.';';
					//print "eval : ".$command."<br/>\n";
					eval($command);
				} else {
					$this_one_finished[__($gc_k)] = $gc_v;
				}
			}
			$book->add_page(
				__($v['MODULE_NAME']),
				$v['META_INFORMATION']['global_config_vars'],
				html_form::form_table($this_one_finished)
			);
		}
	}

	if ($book->is_done()) {
		$page_title = __("System Configuration");
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

} else {

  // actual menu code for admin menu goes here \/

//----- Set page title
$page_title = __("Administration Menu");

//----- Push page onto the stack
page_push();

$display_buffer .= "
  <div align=\"center\">
  <table WIDTH=\"60%\" VALIGN=\"CENTER\" ALIGN=\"CENTER\" BORDER=\"0\"
   CELLSPACING=\"2\" CELLPADDING=\"0\">
  <tr><td class=\"DataHead\">".__("Action")."</td></tr>
"; // begin standard font

$userdata = $_SESSION["authdata"];

$display_buffer .= "
 <tr><!-- <td ALIGN=\"RIGHT\">
  <a HREF=\"export.php\"
  ><img SRC=\"img/kfloppy.gif\" BORDER=\"0\" ALT=\"\"/> --></a>
 </td><td ALIGN=\"LEFT\">
  <a HREF=\"export.php\"
  >".__("Export Databases")."</a>
 </td></tr> 
 <tr><!-- <td ALIGN=\"RIGHT\">
  <a HREF=\"import.php\"
  ><img SRC=\"img/ark.gif\" BORDER=\"0\" ALT=\"\"/></a>
 </td> --><td ALIGN=\"LEFT\">
 <a HREF=\"import.php\"
 >".__("Import Databases")."</a>
 </td></tr>
 <tr><!-- <td ALIGN=\"RIGHT\">
  <a HREF=\"module_information.php\"
  ><img SRC=\"img/magnify.gif\" BORDER=\"0\" ALT=\"\"/></A>
 </td> --><td ALIGN=\"LEFT\">
 <a HREF=\"module_information.php\"
  >".__("Module Information")."</a>
 </td></tr>
 ";

//----- This is depreciated with the use of the init_wizard
//if ($userdata["user"]==1) // if we are root...
// $display_buffer .= "
//  <tr><td ALIGN=\"RIGHT\">
//   <a HREF=\"admin.php?action=reinit\"
//   ><img SRC=\"img/Gear.gif\" BORDER=\"0\" ALT=\"\"/></a>
//  </TD><TD ALIGN=\"LEFT\">
//  <a HREF=\"admin.php?action=reinit\"
//  >".__("Reinitialize Database")."</a>
//  </td></tr>
// ";

if (freemed::acl('admin', 'config')) {
$display_buffer .= "
  <tr><!-- <td ALIGN=\"RIGHT\">
   <a HREF=\"admin.php?action=cfgform\"
   ><img SRC=\"img/config.gif\" BORDER=\"0\" ALT=\"\"/></A>
  </td> --><td ALIGN=\"LEFT\">
  <a HREF=\"admin.php?action=cfgform\"
  >".__("System Configuration")."</a>
  </td></tr>
";
}

if (freemed::acl('admin', 'user')) {
//if ($userdata["user"] == 1) { // if we are root...
  $display_buffer .= "
    <tr><!-- <td ALIGN=\"RIGHT\">
     <a HREF=\"user.php?action=view\"
     ><img SRC=\"img/monalisa.gif\" BORDER=\"0\" ALT=\"\"/></a>
    </td> --><td ALIGN=\"LEFT\">
    <a HREF=\"user.php?action=view\"
     >".__("User Maintenance")."</a>
    </td></tr>
  ";
}

	// Load dynamic modules for administration via AdminMenu handler
	LoadObjectDependency('PHP.module');
	$admin_actions = freemed::module_handler('AdminMenu');
	if (is_array($admin_actions)) {
		foreach ($admin_actions as $class => $handler) {
			GettextXML::textdomain(strtolower($class));
			$title = freemed::module_get_value($class, 'MODULE_NAME');
			$icon = freemed::module_get_value($class, 'ICON');
			$display_buffer .= "
			<tr><!-- <td ALIGN=\"RIGHT\">
			<a HREF=\"module_loader.php?module=".urlencode($class).
			"\"><img src=\"".$icon."\" BORDER=\"0\" ALT=\"\"/></a>
			</td> --><td ALIGN=\"LEFT\">
			<a HREF=\"module_loader.php?module=".urlencode($class).
			"\">".__($title)."</a>
			</td></tr>
			";
		}
	} // end if is array admin_actions


	$display_buffer .= "</table>\n";
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
