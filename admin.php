<?php
 // $Id$
 // note: administrative functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       language support by Max Klohn (amk@span.ch)
 // lic : GPL, v2

$page_name=basename($GLOBALS["$PHP_SELF"]);
include_once ("lib/freemed.php");

//----- Login/authenticate
freemed_open_db ();

//----- Set configuration variables
$config_vars = array (
	"icd", // icd9 option
	"gfx", // gfx option (graphics enhanced)
	"cal_ob", // calendar overbooking
	"calshr", // calendar start time
	"calehr", // calendar end time
	"dtfmt", // date format
	"phofmt" // phone format
);

  // security patch...
if (!freemed::user_flag(USER_ADMIN)) {
  $page_title = _("Administration")." :: "._("ERROR");
  $display_buffer .= "
    <P>
    "._("No Admin Menu Access")."
    <P>
    <CENTER>
     <A HREF=\"main.php\"
     >"._("Return to the Main Menu")."
    </CENTER>
    <P>
  ";
  template_display();
}

if ($action=="cfgform") {

	// this is the frontend to the config
	// database.

	// Add help link for cfgform
	$menu_bar["Configuration Help"] = help_url("admin.php", "cfgform");

	//----- Pull in all configuration variables
	reset ($config_vars);
	foreach ($config_vars AS $_garbage => $v) {
		${$v} = freemed::config_value($v);
	}

	//----- Push page onto the stack
	page_push();

	$page_title = "Configuration";
	$display_buffer .= "
		<P>

		<FORM ACTION=\"".page_name()."\" METHOD=POST>
		<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"cfg\">
	";

	$display_buffer .= html_form::form_table(array(
		_("ICD Code Type") =>
    		html_form::select_widget("icd",
    			array (
				"ICD9"  => "9",
				"ICD10" => "10"
			)
		),

		_("Graphics Enhanced") =>
		html_form::select_widget("gfx",
			array (
				_("Disabled") => "0",
				_("Enabled")  => "1"
			)
		),

		_("Scheduling Start Time") =>
		html_form::select_widget("calshr",
			array (
				_("Default") => "",
				"4 am"  => "4",
				"5 am"  => "5",
				"6 am"  => "6",
				"7 am"  => "7",
				"8 am"  => "8",
				"9 am"  => "9",
				"10 am" => "10"
			)
    		),

		_("Scheduling End Time") =>
		html_form::select_widget("calehr",
			array (
				_("Default") => "",
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

		_("Calendar Overbooking") =>
		html_form::select_widget("cal_ob",
			array (
				_("Enabled")  => "enable",
				_("Disabled") => "disable"
			)
		),

		_("Date Format") =>
		html_form::select_widget("dtfmt",
			array (
				"YYYY-MM-DD" => "ymd",
				"MM-DD-YYYY" => "mdy",
				"DD-MM-YYYY" => "dmy",
				"YYYY-DD-MM" => "ydm"
			)
		),

		_("Phone Number Format") =>
		html_form::select_widget("phofmt",
			array (
				_("United States")." (XXX) XXX-XXXX" => "usa",
				_("France")." (XX) XX XX XX XX" => "fr",
				_("Unformatted")." XXXXXXXXXX" => "unformatted"
			)
		)
	));

	$display_buffer .= "
		<P>

		<CENTER>
		<INPUT TYPE=SUBMIT VALUE=\" "._("Configure")." \">
		<INPUT TYPE=RESET  VALUE=\"   "._("Reset")."   \">
		</CENTER>
		</FORM>
	";

} elseif ($action=="cfg") {

	$page_title = _("Update Config");
	$display_buffer .= "
		<P>
	";

	//----- Commit all configuration variables
	foreach ($config_vars AS $_garbage => $v) {
		$q = "UPDATE config SET ".
			"c_value='".addslashes(${$v})."' ".
			"WHERE c_option='".addslashes($v)."'";
		$query = $sql->query($q);
		if (($debug) AND ($q))
			$display_buffer .= "$config = ${$v}<BR>\n";
	}


	$display_buffer .= "
		<P>
		<CENTER><B>"._("Configuration Complete")."</B></CENTER>
		<P ALIGN=CENTER>
		<A HREF=\"".page_name()."\"
		>"._("Return To Administration Menu")."</A>
	";

} elseif ($action=="reinit") {
	$page_title = _("Reinitialize Database");
  
    // here, to prevent problems, we ask the user to check that they
    // REALLY want to...

  $display_buffer .= "\n<CENTER>\n";
  $display_buffer .= _("Are you sure you want to reinitialize the database?")."\n";
  $display_buffer .= "<BR><U><B>"._("This is an IRREVERSIBLE PROCESS!")."</B></U><BR>\n";
  $display_buffer .= "\n</CENTER>\n";

  $display_buffer .= "<BR><CENTER>\n";

  $display_buffer .= "
   <FORM ACTION=\"admin.php\" METHOD=POST>
   <INPUT TYPE=CHECKBOX NAME=\"first_time\" VALUE=\"first\">
   <I>"._("First Initialization")."</I><BR>
   <INPUT TYPE=CHECKBOX NAME=\"re_load\" VALUE=\"reload\">
   <I>"._("Reload Stock Data")."</I><BR>
   <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"reinit_sure\">
   <TABLE BORDER=0 ALIGN=CENTER><TR><TD>
   <INPUT TYPE=SUBMIT VALUE=\""._("Continue")."\">
   </FORM>

   </TD><TD>

   <FORM ACTION=\"admin.php\" METHOD=POST>
   <INPUT TYPE=SUBMIT VALUE=\""._("Cancel")."\">
   </FORM>

   </TD></TR></TABLE>
   </CENTER>
  ";

} elseif ($action=="reinit_sure") {
 // here we actually put the reinitialization (read - wiping
 // and creating the database structure again) code... so that
 // stupids don't accidentally click on it and... oops!

//  if ($first_time!="first") {
//    $display_buffer .= ""._("Erasing old database")."... ";
//    $sql-drop_db($database) OR
//      DIE("<B>"._("Error accessing SQL")."</B><BR><BR>\n");
//    $display_buffer .= "<B>"._("done")."</B><BR>\n";
//  }

//  $display_buffer .= ""._("Creating new database")."... ";
//  $sql->create_db($database) OR
//    DIE("<B>"._("Error accessing SQL")."</B><BR><BR>\n");
//  $display_buffer .= "<B>"._("done")."</B><BR>\n";

  $display_buffer .= "<UL>"._("Creating tables")."... \n";
  $display_buffer .= "$re_load\n";

  // generate test table, if debug is on
  if ($debug) {
    $result=$sql->query("DROP TABLE test");
    $result=$sql->query("CREATE TABLE test (
      name CHAR(10), other CHAR(12), phone INT,
      ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
      PRIMARY KEY (ID))");
    if ($result) { $display_buffer .= "<LI>"._("test db")." \n"; }
  } // end debug section

  // generate module table
  $result=$sql->query("DROP TABLE module"); 
  $result=$sql->query("CREATE TABLE module (
    module_name     VARCHAR(100),
    module_version  VARCHAR(50),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) { $display_buffer .= "<LI>"._("Modules")."\n"; }

  // generate physician db table
  $result=$sql->query("DROP TABLE physician");
  $result=$sql->query("CREATE TABLE physician (
    phylname     VARCHAR(52),
    phyfname     VARCHAR(50),
    phytitle     CHAR(10),
    phymname     VARCHAR(50),
    phypracname  CHAR(30),
    phyaddr1a    CHAR(30),
    phyaddr2a    CHAR(30),
    phycitya     CHAR(20),
    phystatea    CHAR(5),
    phyzipa      CHAR(10),
    phyphonea    VARCHAR(16),
    phyfaxa      VARCHAR(16),
    phyaddr1b    CHAR(30),
    phyaddr2b    CHAR(30),
    phycityb     CHAR(20),
    phystateb    CHAR(5),
    phyzipb      CHAR(10),
    phyphoneb    VARCHAR(16),
    phyfaxb      VARCHAR(16),
    phyemail     CHAR(30),
    phycellular  CHAR(10),
    phypager     CHAR(10),
    phyupin      CHAR(15),
    physsn       CHAR(9),
    phydeg1      INT UNSIGNED,
    phydeg2      INT UNSIGNED,
    phydeg3      INT UNSIGNED,
    physpe1      INT UNSIGNED,
    physpe2      INT UNSIGNED,
    physpe3      INT UNSIGNED,
    phyid1       CHAR(10),
    phystatus    INT UNSIGNED,
    phyref       ENUM(\"yes\",\"no\") NOT NULL,
    phyrefcount  INT UNSIGNED,
    phyrefamt    REAL,
    phyrefcoll   REAL,
    phychargemap TEXT,
    phyidmap     TEXT,
    phygrpprac   INT UNSIGNED,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) { $display_buffer .= "<LI>"._("Physicians")."\n"; }

  // generate icd9 code table
  $result=$sql->query("DROP TABLE icd9"); 
  $result=$sql->query("CREATE TABLE icd9 (
    icd9code     VARCHAR(6),
    icd10code    VARCHAR(7),
    icd9descrip  VARCHAR(45),
    icd10descrip VARCHAR(45),
    icdmetadesc  VARCHAR(30),
    icdng        DATE,
    icddrg       TEXT,
    icdnum       INT UNSIGNED,
    icdamt       REAL,
    icdcoll      REAL,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) { $display_buffer .= "<LI>"._("ICD Codes")."\n"; }

  // generate patient database
  $result=$sql->query("DROP TABLE patient");
  $result=$sql->query("CREATE TABLE patient (
    ptdtadd      DATE,
    ptdtmod      DATE,
    ptbal        REAL,
    ptbalfwd     REAL,
    ptunapp      REAL,
    ptdoc        VARCHAR(150),
    ptrefdoc     VARCHAR(150),
    ptpcp        VARCHAR(150),
    ptphy1       VARCHAR(150),
    ptphy2       VARCHAR(150),
    ptphy3       VARCHAR(150),
    ptphy4       VARCHAR(150),
    ptbilltype   ENUM(\"sta\",\"mon\",\"chg\") NOT NULL,
    ptbudg       REAL,
    ptlname      VARCHAR(50),
    ptfname      VARCHAR(50),
    ptmname      VARCHAR(50),
    ptaddr1      VARCHAR(45),
    ptaddr2      VARCHAR(45),
    ptcity       VARCHAR(45),
    ptstate      VARCHAR(20),
    ptzip        CHAR(10),
    ptcountry    VARCHAR(50),
    pthphone     VARCHAR(16),
    ptwphone     VARCHAR(16),
    ptfax        VARCHAR(16),
    ptemail      VARCHAR(80),
    ptsex        ENUM(\"m\",\"f\",\"t\") NOT NULL,
    ptdob        DATE,
    ptssn        VARCHAR(9),
    ptdmv        VARCHAR(15),
    ptdtlpay     DATE,
    ptamtlpay    REAL,
    ptpaytype    INT UNSIGNED,
    ptdtbill     DATE,
    ptamtbill    REAL,
    ptstatus     INT UNSIGNED,
    ptytdchg     REAL,
    ptar         REAL,
    ptextinf     TEXT,
    ptdisc       REAL,
    ptdol        DATE,
    ptdiag1      INT UNSIGNED,
    ptdiag2      INT UNSIGNED,
    ptdiag3      INT UNSIGNED,
    ptdiag4      INT UNSIGNED,
    ptid         VARCHAR(10),
    pthistbal    REAL,
    ptmarital    ENUM(\"single\",\"married\",
                      \"divorced\", \"separated\",
                      \"widowed\") NOT NULL,
    ptempl       ENUM(\"y\",\"n\") NOT NULL,
    ptemp1       INT UNSIGNED,
    ptemp2       INT UNSIGNED,
    ptguar       TEXT,
    ptrelguar    TEXT,
    ptguarstart  TEXT,
    ptguarend    TEXT,
    ptins        TEXT,
    ptinsno      TEXT,
    ptinsgrp     TEXT,
    ptinsstart   TEXT,
    ptinsend     TEXT,
    ptnextofkin  TEXT,
    ptblood      CHAR(3),
    iso          VARCHAR(15),
    ptdep        INT UNSIGNED,
    ptins1 	 INT UNSIGNED,
    ptins2  	 INT UNSIGNED,
    ptins3 	 INT UNSIGNED,
    ptreldep 	 CHAR(1),
    ptinsno1 	 VARCHAR(50),
    ptinsno2 	 VARCHAR(50),
    ptinsno3 	 VARCHAR(50),
    ptinsgrp1	 VARCHAR(50),
    ptinsgrp2	 VARCHAR(50),
    ptinsgrp3	 VARCHAR(50),
    ptdead       INT UNSIGNED,
    ptdeaddt     DATE,
    pttimestamp       TIMESTAMP(16),
    ptemritimestamp   TIMESTAMP(16),
    ptemriversion     BLOB,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) { $display_buffer .= "<LI>"._("Patients")."\n"; }

  $result=$sql->query("DROP TABLE coverage");
  $result=$sql->query("CREATE TABLE coverage (
    id           INT NOT NULL AUTO_INCREMENT,
	covdtadd     DATE,
	covdtmod     DATE,
	covpatient   INT UNSIGNED,             
	coveffdt     TEXT,
	covinsco     INT UNSIGNED,     
	covpatinsno  VARCHAR(50),
	covpatgrpno  VARCHAR(50),
	covtype      INT UNSIGNED,          
	covstatus    INT UNSIGNED,
	covrel       CHAR(2), 
	covlname     VARCHAR(50), 
	covfname     VARCHAR(50),
	covmname     CHAR(1),
	covaddr1     CHAR(25), 
	covaddr2     CHAR(25),
	covcity      CHAR(25),
	covstate     CHAR(3),
	covzip       CHAR(10),
	covdob       DATE, 
	covsex       ENUM(\"m\",\"f\",\"t\") NOT NULL,
	covinstp     INT UNSIGNED,
	covprovasgn  INT UNSIGNED,
	covbenasgn   INT UNSIGNED,
	covrelinfo   INT UNSIGNED,
	covrelinfodt INT UNSIGNED,
	covplanname  VARCHAR(33),
    PRIMARY KEY (id)    
  )");
  if ($result) { $display_buffer .= "<LI>"._("Coverage")."\n"; }
  else         { $display_buffer .= "<LI>"._("Coverage")." Failed\n"; }

  // coverage types
  $result=$sql->query("DROP TABLE covtypes");
  $result=$sql->query("CREATE TABLE covtypes (
    covtpname      VARCHAR(5),
    covtpdescrip   VARCHAR(60),
    covtpdtadd     DATE,
    covtpdtmod     DATE,
    id             INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY    (id)
  )");
  if ($result) { $display_buffer .= "<LI>"._("Insurance Coverage Types")."\n"; }
  if ($re_load) {
    if (freemed_import_stock_data("covtypes"))
      $display_buffer .= "<I>("._("Stock Insurance Coverage Types").")</I> \n ";
  }

  // queries database
  $result=$sql->query("DROP TABLE queries");
  $result=$sql->query("CREATE TABLE queries (
    qdatabase      VARCHAR(250) NOT NULL DEFAULT '',
    qquery         TEXT,
    qtitle         VARCHAR(255) NOT NULL DEFAULT '',
    id             INT(5) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY    (id),
    KEY qtitle (qtitle)
  )");
  if ($result) { $display_buffer .= "<LI>"._("Queries")."\n"; }
  if ($re_load) {
    if (freemed_import_stock_data("queries"))
      $display_buffer .= "<I>("._("Stock Queries").")</I> \n ";
  }

  // queries database
  $result=$sql->query("DROP TABLE claimtypes");
  $result=$sql->query("CREATE TABLE claimtypes (
    clmtpname      VARCHAR(5),
    clmtpdescrip   VARCHAR(60),
    clmtpdtadd     DATE,
    clmtpdtmod     DATE,
    id             INT(5) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY    (id)
  )");
  if ($result) { $display_buffer .= "<LI>"._("Insurance Claim Types")."\n"; }
  if ($re_load) {
    if (freemed_import_stock_data("claimtypes"))
      $display_buffer .= "<I>("._("Stock Insurance Claim Types").")</I> \n ";
  }

  // generate payer database 
  $result=$sql->query("DROP TABLE payer");
  $result=$sql->query("CREATE TABLE payer (
    payerinsco        INT UNSIGNED,
    payerstartdt      TEXT,
    payerenddt        TEXT,
    payerpatient      INT UNSIGNED,
    payerpatientgrp   VARCHAR(50),
    payerpatientinsno VARCHAR(50),
    payertype         INT,
    payerstatus       INT,
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
  )");
  if ($result) { $display_buffer .= "<LI>"._("Payer")."\n"; }

  // generate guarantor database 
  $result=$sql->query("DROP TABLE guarantor");
  $result=$sql->query("CREATE TABLE guarantor (
    guarpatient       INT UNSIGNED,
    guarguar          INT UNSIGNED,
    guarrel	      CHAR(1),
    guarstartdt       TEXT,
    guarenddt         TEXT,
    guarstatus        INT,
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
  )");
  if ($result) { $display_buffer .= "<LI>"._("Guarantor")."\n"; }

  // generate proc database (second generation)
  $result=$sql->query("DROP TABLE procrec");
  $result=$sql->query("CREATE TABLE procrec (
    procpatient            INT UNSIGNED NOT NULL,
    proceoc                TEXT,
    proccpt                INT UNSIGNED,
    proccptmod             INT UNSIGNED,
    procdiag1              INT UNSIGNED,
    procdiag2              INT UNSIGNED,
    procdiag3              INT UNSIGNED,
    procdiag4              INT UNSIGNED,
    proccharges            REAL,
    procunits              REAL,
    procvoucher            VARCHAR(25),
    procphysician          VARCHAR(150),
    procdt                 DATE,
    procpos                VARCHAR(150),
    proccomment            TEXT,
    procbalorig            REAL,
    procbalcurrent         REAL,
    procamtpaid            REAL,
    procbilled             INT UNSIGNED,
    procbillable           INT UNSIGNED,
    procauth               INT UNSIGNED,
    procrefdoc             VARCHAR(150),
    procrefdt              DATE,
    procamtallowed         REAL,
    procdtbilled	       TEXT,
    proccurcovid		   INT UNSIGNED,
    proccurcovtp		   INT UNSIGNED,
    proccov1			   INT UNSIGNED,
    proccov2			   INT UNSIGNED,
    proccov3			   INT UNSIGNED,
    proccov4			   INT UNSIGNED,
    proccert               INT UNSIGNED,
    procclmtp              INT UNSIGNED,
    id INT NOT NULL AUTO_INCREMENT,
    KEY (procpatient),
    PRIMARY KEY (id)
    )");
  if ($result) { $display_buffer .= "<LI>"._("Procedures")."\n"; }

  // generate facility database
  $result=$sql->query("DROP TABLE facility"); 
  $result=$sql->query("CREATE TABLE facility (
    psrname      VARCHAR(100),
    psraddr1     VARCHAR(50),
    psraddr2     VARCHAR(50),
    psrcity      VARCHAR(50),
    psrstate     CHAR(3),
    psrzip       CHAR(10),
    psrcountry   VARCHAR(50),
    psrnote      VARCHAR(40),
    psrdateentry DATE,
    psrdefphy    INT UNSIGNED,
    psrphone     VARCHAR(16),
    psrfax       VARCHAR(16),
    psremail     VARCHAR(25),
    psrein       VARCHAR(9),
    psrintext    INT UNSIGNED,
	psrpos       INT UNSIGNED,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id) )");
  if ($result) $display_buffer .= "<LI>"._("Facility")."\n"; 

  if ($re_load)
  {
  	$result=$sql->query("INSERT INTO facility VALUES (
   	'Default Facility', '', '', '', '', '', '', '', '$cur_date',
   	'', '', '', '', NULL )");
  	if ($result) $display_buffer .= "<I>("._("default facility added").")</I> \n"; 
  }

  // generate room database
  $result=$sql->query("DROP TABLE room"); 
  $result=$sql->query("CREATE TABLE room (
    roomname     CHAR(20),
    roompos      INT UNSIGNED,
    roomdescrip  CHAR(40),
    roomdefphy   INT UNSIGNED,
    roomsurgery  ENUM(\"y\", \"n\") NOT NULL,
    roombooking  ENUM(\"y\", \"n\") NOT NULL,
    roomipaddr   VARCHAR(15),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) $display_buffer .= "<LI>"._("Rooms")."\n"; 

  if ($re_load)
  {
  	$result=$sql->query("INSERT INTO room VALUES (
   	'Default Room', '1', '', '', '', '', '', NULL) ");
  	if ($result) $display_buffer .= "<I>("._("default room added").")</I> \n";
  }

  // generate degrees database
  $result=$sql->query("DROP TABLE degrees");
  $result=$sql->query("CREATE TABLE degrees (
    degdegree     CHAR(10),
    degname       VARCHAR(50),
    degdate       DATE,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) $display_buffer .= "<LI>"._("Degrees")."\n"; 
	
  if ($re_load)
  {
  	if (freemed_import_stock_data ("degrees"))
    		$display_buffer .= "<I>("._("Stock Degree Data").")</I> \n";
  }

  // generate specialties database
  $result=$sql->query("DROP TABLE specialties"); 
  $result=$sql->query("CREATE TABLE specialties (
    specname      VARCHAR(50),
    specdesc      VARCHAR(100),
    specdatestamp DATE,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Specialties")."\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data ("specialties"))
    		$display_buffer .= "<I>("._("Stock Specialties Data").")</I> \n";
  }

  // generate insurance company database
  $result=$sql->query("DROP TABLE insco"); 
  $result=$sql->query("CREATE TABLE insco (
    inscodtadd   DATE,
    inscodtmod   DATE,
    insconame    VARCHAR(50) NOT NULL,
    inscoalias   VARCHAR(30),
    inscoaddr1   VARCHAR(45),
    inscoaddr2   VARCHAR(45),
    inscocity    VARCHAR(30),
    inscostate   CHAR(3),
    inscozip     CHAR(10),
    inscophone   VARCHAR(16),
    inscofax     VARCHAR(16),
    inscocontact VARCHAR(100),
    inscoid      CHAR(20),
    inscowebsite VARCHAR(100),
    inscoemail   VARCHAR(50),
    inscogroup   INT UNSIGNED,
    inscotype    INT UNSIGNED,
    inscoassign  INT UNSIGNED,
    inscomod     TEXT,
    id INT NOT NULL AUTO_INCREMENT,
    KEY (insconame),
    PRIMARY KEY (id)    
    )");
  if ($result) $display_buffer .= "<LI>"._("Insurance Companies")."\n"; 

  if ($re_load)
  {
  	if (freemed_import_stock_data ("insco"))
    		$display_buffer .= "<I>("._("Stock Insurance Company Data").")</I> \n";
  }

  // generate insurance company groups db
  $result=$sql->query("DROP TABLE inscogroup"); 
  $result=$sql->query("CREATE TABLE inscogroup (
    inscogroup     VARCHAR(50),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) $display_buffer .= "<LI>"._("Insurance Company Groups")."\n"; 

  if ($re_load)
  {
  	if (freemed_import_stock_data ("inscogroup"))
    		$display_buffer .= "<I>("._("Stock Insurance Company Group Data").")</I> \n";
  }

  // generate CPT (procedural) codes database
  $result=$sql->query("DROP TABLE cpt");
  $result=$sql->query("CREATE TABLE cpt (
    cptcode        CHAR(7),
    cptnameint     VARCHAR(50),
    cptnameext     VARCHAR(50),
    cptgender      ENUM(\"n\", \"m\", \"f\") NOT NULL,
    cpttaxed       ENUM(\"n\", \"y\") NOT NULL,
    cpttype        INT UNSIGNED,
    cptreqcpt      TEXT,
    cptexccpt      TEXT,
    cptreqicd      TEXT,
    cptexcicd      TEXT,
    cptrelval      REAL,
    cptdeftos      INT UNSIGNED,
    cptdefstdfee   REAL,
    cptstdfee      TEXT,
    cpttos         TEXT,
    cpttosprfx     TEXT,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("CPT Codes")."\n";
		
  if ($re_load)
  {
  	if (freemed_import_stock_data ("cpt"))
    		$display_buffer .= "<I>("._("Stock CPT Code Data").")</I> \n";
  }

  // generate cpt modifier db
  $result=$sql->query("DROP TABLE cptmod");
  $result=$sql->query("CREATE TABLE cptmod (
    cptmod         CHAR(2),
    cptmoddescrip  VARCHAR(50),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("CPT Modifers")."\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data ("cptmod"))
    		$display_buffer .= "<I>("._("Stock CPT Modifier Data").")</I> \n";
  }

  // generate physician groups db
  $result=$sql->query("DROP TABLE phygroup");
  $result=$sql->query("CREATE TABLE phygroup (
    phygroupname   VARCHAR(100),
    phygroupfac    INT UNSIGNED,
    phygroupdtadd  DATE,
    phygroupdtmod  DATE,
	phygroupidmap  TEXT,
	phygroupdocs   TEXT,
	phygroupspe1   INT UNSIGNED,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Physician Groups")."\n";

  // generate user db
  $result=$sql->query("DROP TABLE user"); 
  $result=$sql->query("CREATE TABLE user (
    username       VARCHAR(16) NOT NULL,
    userpassword   VARCHAR(16) NOT NULL,
    userdescrip    VARCHAR(50),
    userlevel      INT UNSIGNED,
    usertype       ENUM (\"phy\", \"misc\") NOT NULL,
    userfac        BLOB,
    userphy        BLOB,
    userphygrp     BLOB,
    userrealphy    INT UNSIGNED,
    usermanageopt  BLOB,
    id INT(32) UNSIGNED NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id),
    UNIQUE idx_id (id),
    KEY (username),
    UNIQUE idx_username (username)
    )");
  if ($result) $display_buffer .= "<LI>"._("Users")."\n";

  if ($re_load)
  {
  	$result=$sql->query($sql->insert_query(
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
  	if ($result) $display_buffer .= "<I>[["._("Added Superuser")."]]</I> \n";
  }

  // generate scheduler table
  $result=$sql->query("DROP TABLE scheduler"); 
  $result=$sql->query("CREATE TABLE scheduler (
    caldateof         DATE,
    caltype           ENUM (\"temp\", \"pat\") NOT NULL,
    calhour           INT UNSIGNED,
    calminute         INT UNSIGNED,
    calduration       INT UNSIGNED,
    calfacility       INT UNSIGNED,
    calroom           INT UNSIGNED,
    calphysician      INT UNSIGNED,
    calpatient        INT UNSIGNED,
    calcptcode        INT UNSIGNED,
    calstatus         INT UNSIGNED,
    calprenote        VARCHAR(100),
    calpostnote       TEXT,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Scheduler")."\n";

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
  if ($result) $display_buffer .= "<LI>"._("Physician Availability Map")."\n";

  // generate insurance company groups db
  $result=$sql->query("DROP TABLE phystatus"); 
  $result=$sql->query("CREATE TABLE phystatus (
    phystatus      VARCHAR(30),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) $display_buffer .= "<LI>"._("Physician Statuses")."\n";

  // generate progress notes db (19990707)
  // * 19991228 - add iso
  $result=$sql->query("DROP TABLE pnotes"); 
  $result=$sql->query("CREATE TABLE pnotes (
    pnotesdt       DATE,
    pnotesdtadd    DATE,
    pnotesdtmod    DATE,
    pnotespat      INT UNSIGNED,
    pnotesdescrip  VARCHAR(100),
    pnotesdoc      INT UNSIGNED,
    pnoteseoc      TEXT,
    pnotes_S       TEXT,
    pnotes_O       TEXT,
    pnotes_A       TEXT,
    pnotes_P       TEXT,
    pnotes_I       TEXT,
    pnotes_E       TEXT,
    pnotes_R       TEXT,
    iso            VARCHAR(15),
    id INT NOT NULL AUTO_INCREMENT, 
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Progress Notes")."\n";

  // generate payment record db
  $result=$sql->query("DROP TABLE payrec"); 
  $result=$sql->query("CREATE TABLE payrec (
    payrecdtadd   DATE,
    payrecdtmod   DATE,
    payrecpatient INT UNSIGNED NOT NULL,
    payrecdt      DATE,
    payreccat     INT UNSIGNED,
    payrecproc    INT UNSIGNED,
    payrecsource  INT UNSIGNED,
    payreclink    INT UNSIGNED,
    payrectype    INT UNSIGNED,
    payrecnum     VARCHAR(100),
    payrecamt     REAL,
    payrecdescrip TEXT,
    payreclock    ENUM (\"unlocked\", \"locked\") NOT NULL,
    id INT NOT NULL AUTO_INCREMENT,
    KEY (payrecpatient),
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Payment Records")."\n";

  // generate formulary database
  $result=$sql->query("DROP TABLE frmlry"); 
  $result=$sql->query("CREATE TABLE frmlry (
    frmlrydtadd    DATE,
    frmlrydtmod    DATE,
    class          VARCHAR(20),
    gnrcname       VARCHAR(20),
    trdmrkname     VARCHAR(20),
    ind1           VARCHAR(50),
    ind2           VARCHAR(50),
    ind3           VARCHAR(50),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Formulary")."\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data ("frmlry"))
    		$display_buffer .= "<I>("._("Stock Formulary Data").")</I> \n";
  }

  // Rx (prescription) database
  $result=$sql->query("DROP TABLE rx"); 
  $result=$sql->query("CREATE TABLE rx (
    rxdtadd        DATE,
    rxdtmod        DATE,
    rxpatient      INT UNSIGNED,
    rxdtfrom       DATE,
    rxduration     INT UNSIGNED,
    rxdrug         INT UNSIGNED,
    rxdosage       VARCHAR (100),
    rxrefills      INT UNSIGNED,
    rxsubstitute   VARCHAR (30),
    rxmd5sum       VARCHAR (50),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Prescriptions")."\n";

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
  if ($result) $display_buffer .= "<LI>"._("Simple Reports")."\n";

  // generate call-in table/db
  $result=$sql->query("DROP TABLE callin");
  $result=$sql->query("CREATE TABLE callin (
    cilname        VARCHAR(50),
    cifname        VARCHAR(50),
    cimname        VARCHAR(50),
    cihphone       VARCHAR(16),
    ciwphone       VARCHAR(16),
    cidob          DATE,
    cicomplaint    TEXT,
    cidatestamp    DATE,
    cifacility     INT UNSIGNED,
    ciphysician    INT UNSIGNED,
    citookcall     VARCHAR(50),
    cipatient      INT UNSIGNED,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Call-Ins")."\n ";

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
  if ($result) $display_buffer .= "<LI>"._("Room Equipment")."\n ";

  if ($re_load)
  {
  	if (freemed_import_stock_data("roomequip"))
    		$display_buffer .= "<I>("._("Stock Room Equipment Data").")</I> \n ";
  }

  // generate type of service db
  $result=$sql->query("DROP TABLE tos");
  $result=$sql->query("CREATE TABLE tos (
    tosname        VARCHAR(75),
    tosdescrip     VARCHAR(200),
    tosdtadd       DATE,
    tosdtmod       DATE,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Type of Service")."\n ";

  if ($re_load)
  {
  	if (freemed_import_stock_data("tos"))
    		$display_buffer .= "<I>("._("Stock Type of Service Data").")</I> \n ";
  }

  // generate place of service db required for x12
  $result=$sql->query("DROP TABLE pos");
  $result=$sql->query("CREATE TABLE pos (
    posname        VARCHAR(75),
    posdescrip     VARCHAR(200),
    posdtadd       DATE,
    posdtmod       DATE,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Place of Service")."\n ";

  if ($re_load)
  {
  	if (freemed_import_stock_data("pos"))
    		$display_buffer .= "<I>("._("Stock Place of Service Data").")</I> \n ";
  }

  // generate internal service types db
  $result=$sql->query("DROP TABLE intservtype"); 
  $result=$sql->query("CREATE TABLE intservtype (
    intservtype    VARCHAR(50),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Internal Service Types")."\n ";

  if ($re_load)
  {
  	if (freemed_import_stock_data("intservtype"))
    		$display_buffer .= "<I>("._("Stock Internal Service Types Data").")</I> \n";
  }

  // generate patient record template (custom) db
  $result=$sql->query("DROP TABLE patrectemplate"); 
  $result=$sql->query("CREATE TABLE patrectemplate (
    prtname        VARCHAR(100),
    prtdescrip     VARCHAR(100),
    prtfname       TEXT,
    prtvar         TEXT,
    prtftype       TEXT,
    prtftypefor    TEXT,
    prtfmaxlen     TEXT,
    prtfcom        TEXT,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Patient Record Templates")."\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data("patrectemplate"))
    		$display_buffer .= "<I>("._("Stock Patient Record Template Data").")</I> \n";
  }

  // generate questionnaire template (custom) db
  $result=$sql->query("DROP TABLE qtemplate"); 
  $result=$sql->query("CREATE TABLE qtemplate (
    qname          VARCHAR(100),
    qdescrip       VARCHAR(100),
    qfname         TEXT,
    qvar           TEXT,
    qftype         TEXT,
    qftypefor      TEXT,
    qfmaxlen       TEXT,
    qftext         TEXT,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Questionnaire Templates")."\n";

  if ($re_load)
  {
  	 if (freemed_import_stock_data("qtemplate"))
    		$display_buffer .= "<I>("._("Stock Questionnaire Template Data").")</I> \n";
  } 
  // generate diagnosis family db
  $result=$sql->query("DROP TABLE diagfamily"); 
  $result=$sql->query("CREATE TABLE diagfamily (
    dfname         VARCHAR(100),
    dfdescrip      VARCHAR(100),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Diagnosis Families")."\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data("diagfamily"))
    		$display_buffer .= "<I>("._("Stock Diagnosis Family Data").")</I> \n";
  }
  // generate patient statuses
  $result=$sql->query("DROP TABLE ptstatus"); 
  $result=$sql->query("CREATE TABLE ptstatus (
    ptstatus         CHAR(3),
    ptstatusdescrip  VARCHAR(30),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Patient Statuses")."\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data("ptstatus"))
    		$display_buffer .= "<I>("._("Stock Patient Statuses Data").")</I> \n";
  }
  // generate patient record data (custom) db
  $result=$sql->query("DROP TABLE patrecdata"); 
  $result=$sql->query("CREATE TABLE patrecdata (
    prpatient      INT UNSIGNED NOT NULL,
    prtemplate     INT UNSIGNED,
    prdtadd        DATE,
    prdtmod        DATE,
    prdata         TEXT,
    id INT NOT NULL AUTO_INCREMENT,
    KEY (prpatient),
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Patient Record Data")."\n";

  // generate action log table db
  $result=$sql->query("DROP TABLE log"); 
  $result=$sql->query("CREATE TABLE log (
    datestamp      DATE,
    user           INT UNSIGNED,
    db_name        VARCHAR(20),
    rec_num        INT UNSIGNED,
    comment        TEXT,
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    )");
  if ($result) $display_buffer .= "<LI>"._("Action Log")."\n";

  // generate configuration table info
  $result=$sql->query("DROP TABLE config"); 
  $result=$sql->query("CREATE TABLE config (
    c_option       CHAR(6),
    c_value        VARCHAR(100),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)    
    )");
  if ($result) $display_buffer .= "<LI>"._("Configuration")."\n";

  if ($re_load)
  {
  	if ($sql->query("INSERT INTO config VALUES (
    	'icd', '9', NULL )"))   if ($debug)   $display_buffer .= "(ICD) \n";
  	if ($sql->query("INSERT INTO config VALUES (
    	'gfx', '1', NULL )"))   if ($debug)   $display_buffer .= "(graphics) \n";
  	if ($sql->query("INSERT INTO config VALUES (
    	'calshr', '$cal_starting_hour', NULL )")) if ($debug) $display_buffer .= "(calshr) \n";
  	if ($sql->query("INSERT INTO config VALUES (
    	'calehr', '$cal_ending_hour', NULL )")) if ($debug) $display_buffer .= "(calehr) \n";
  	if ($sql->query("INSERT INTO config VALUES (
    	'cal_ob', 'enable', NULL )")) if ($debug) $display_buffer .= "(cal_ob) \n";
  	if ($sql->query("INSERT INTO config VALUES (
    	'dtfmt', 'ymd', NULL )")) if ($debug) $display_buffer .= "(dtfmt) \n";
  	if ($sql->query("INSERT INTO config VALUES (
    	'phofmt', 'unformatted', NULL )")) if ($debug) $display_buffer .= "(phofmt) \n";
  }

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
   if ($result) $display_buffer .= "<LI>"._("Incoming Faxes")."\n";

  // generate fax sender lookup table
  $result=$sql->query("DROP TABLE infaxlut"); 
  $result=$sql->query("CREATE TABLE infaxlut (
    lutsender VARCHAR(50),
    lutname   VARCHAR(50),
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
    );");
  if ($result) $display_buffer .= "<LI>"._("Fax Sender Lookup")."\n";

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
   if ($result) $display_buffer .= "<LI>"._("Printers")."\n";

   // generate fixed form table (19991020)
  $result = $sql->query ("DROP TABLE fixedform");
  $result = $sql->query ("CREATE TABLE fixedform (
     ffname        VARCHAR(50),
     ffdescrip     VARCHAR(100),
     fftype        INT UNSIGNED,
     ffpagelength  INT UNSIGNED,
     fflinelength  INT UNSIGNED,
     ffloopnum     INT UNSIGNED,
     ffloopoffset  INT UNSIGNED,
     ffcheckchar   CHAR(1),
     ffrow         TEXT,
     ffcol         TEXT,
     fflength      TEXT,
     ffdata        TEXT,
     ffformat      TEXT,
     ffcomment     TEXT,
     id            INT NOT NULL AUTO_INCREMENT,
     PRIMARY KEY (id)
     );");
  if ($result) $display_buffer .= "<LI>"._("Fixed Forms")."\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data("fixedform"))
    		$display_buffer .= "<I>("._("Stock Fixed Forms Data").")</I> \n";
  }
  // episode of care
  $result = $sql->query ("DROP TABLE eoc"); 
  $result = $sql->query ("CREATE TABLE eoc (
     eocpatient                INT UNSIGNED NOT NULL,
     eocdescrip                VARCHAR(100),
     eocstartdate              DATE,
     eocdtlastsimilar          DATE,
     eocreferrer               INT UNSIGNED,
     eocfacility               VARCHAR(150),
     eocdiagfamily             TEXT,
     eocrelpreg                ENUM (\"no\", \"yes\") NOT NULL,
     eocrelemp                 ENUM (\"no\", \"yes\") NOT NULL,
     eocrelauto                ENUM (\"no\", \"yes\") NOT NULL,
     eocrelother               ENUM (\"no\", \"yes\") NOT NULL,
     eocrelstpr                VARCHAR(10),
     eoctype                   ENUM (\"acute\",
                                     \"chronic\",
                                     \"chronic recurrent\",
                                     \"historical\") NOT NULL,
     eocrelautoname            VARCHAR(100),
     eocrelautoaddr1           VARCHAR(100),
     eocrelautoaddr2           VARCHAR(100),
     eocrelautocity            VARCHAR(50),
     eocrelautostpr            VARCHAR(30),
     eocrelautozip             VARCHAR(10),
     eocrelautocountry         VARCHAR(100),
     eocrelautocase            VARCHAR(30),
     eocrelautorcname          VARCHAR(100),
     eocrelautorcphone         VARCHAR(16),
     eocrelempname             VARCHAR(100),
     eocrelempaddr1            VARCHAR(100),
     eocrelempaddr2            VARCHAR(100),
     eocrelempcity             VARCHAR(50),
     eocrelempstpr             VARCHAR(30),
     eocrelempzip              VARCHAR(10),
     eocrelempcountry          VARCHAR(100),
     eocrelempfile             VARCHAR(30),
     eocrelemprcname           VARCHAR(100),
     eocrelemprcphone          VARCHAR(16),
     eocrelemprcemail          VARCHAR(100),
     eocrelpregcycle           INT UNSIGNED,
     eocrelpreggravida         INT UNSIGNED,
     eocrelpregpara            INT UNSIGNED,
     eocrelpregmiscarry        INT UNSIGNED,
     eocrelpregabort           INT UNSIGNED,
     eocrelpreglastper         DATE,
     eocrelpregconfine         DATE,
     eocrelothercomment        VARCHAR(100),
     eocdistype                INT UNSIGNED,
     eocdisfromdt              DATE,
     eocdistodt                DATE,
     eocdisworkdt              DATE,
     eochosadmdt               DATE,
     eochosdischrgdt           DATE,
     eocrelautotime            CHAR(8),
     id                        INT NOT NULL AUTO_INCREMENT,
     KEY (eocpatient),
     PRIMARY KEY (id)
     );");
  if ($result) $display_buffer .= "<LI>"._("Episode of Care")."\n";

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
  if ($result) $display_buffer .= "<LI>"._("Old Reports")."\n";

  // patient image database
  $result = $sql->query ("DROP TABLE patimg");
  $result = $sql->query ("CREATE TABLE patimg (
     pipatient                 INT UNSIGNED NOT NULL,
     pilink                    INT UNSIGNED,
     pidate                    INT UNSIGNED,
     pitype                    ENUM (\"picture\", \"xray\") NOT NULL,
     pidata                    BLOB, 
     id                        INT NOT NULL AUTO_INCREMENT,
     KEY (pipatient),
     PRIMARY KEY (id)
     );");
  if ($result) $display_buffer .= "<LI>"._("Patient Images")."\n";

  // authorizations
  $result = $sql->query ("DROP TABLE authorizations"); 
  $result = $sql->query ("CREATE TABLE authorizations (
     authdtadd                 DATE,
     authdtmod                 DATE,
     authpatient               INT UNSIGNED NOT NULL,
     authdtbegin               DATE,
     authdtend                 DATE,
     authnum                   VARCHAR(25),
     authtype                  INT UNSIGNED,
     authprov                  INT UNSIGNED,
     authprovid                VARCHAR(20),
     authinsco                 INT UNSIGNED,
     authvisits                INT UNSIGNED,
     authvisitsused            INT UNSIGNED,
     authvisitsremain          INT UNSIGNED,
     authcomment               VARCHAR(100),
     id                        INT NOT NULL AUTO_INCREMENT,
     KEY (authpatient),
     PRIMARY KEY (id)
     );");
  if ($result) $display_buffer .= "<LI>"._("Authorizations")."\n";

  // certifications
  $result = $sql->query ("DROP TABLE certifications"); 
  $result = $sql->query ("CREATE TABLE certifications (
     certpatient               INT UNSIGNED,
     certtype                  INT UNSIGNED,
     certformnum               INT UNSIGNED,
     certdesc                  VARCHAR(20),
     certformdata              TEXT,
     id                        INT NOT NULL AUTO_INCREMENT,
     PRIMARY KEY (id)
     );");
  if ($result) $display_buffer .= "<LI>"._("Certifications")."\n";
  
  // insurance modifiers table
  $result = $sql->query ("DROP TABLE insmod"); 
  $result = $sql->query ("CREATE TABLE insmod (
     insmod                    VARCHAR(15),
     insmoddesc                VARCHAR(50),
     id                        INT NOT NULL AUTO_INCREMENT,
     PRIMARY KEY (id)
     );");
  if ($result) $display_buffer .= "<LI>"._("Insurance Modifiers")."\n";

  if ($re_load)
  {
  	if (freemed_import_stock_data("insmod"))
    		$display_buffer .= "<I>("._("Stock Insurance Modifiers").")</I> \n";
  }
  $display_buffer .= "</UL><B>"._("done").".</B><BR>\n";
  
  // now generate "return code" so that we can get back to the
  // admin menu... or perhaps skip that... ??

  $display_buffer .= "
    <BR><BR><CENTER>
    <A HREF=\"admin.php\">
     "._("Return to Administration Menu")."</A>
    </CENTER>
  ";

} else {

  // actual menu code for admin menu goes here \/

//----- Set page title
$page_title = _("Administration Menu");

//----- Push page onto the stack
page_push();


$display_buffer .= "
  
  <TABLE WIDTH=\"100%\" VALIGN=CENTER ALIGN=CENTER BORDER=0 CELLSPACING=2
   CELLPADDING=0>
 "; // begin standard font

$userdata = $SESSION["authdata"];

$display_buffer .= "
 <TR><TD ALIGN=RIGHT>
  <A HREF=\"export.php\"
  ><IMG SRC=\"img/kfloppy.gif\" BORDER=0 ALT=\"\"></A>
 </TD><TD ALIGN=LEFT>
  <A HREF=\"export.php\"
  >"._("Export Databases")."</A>
 </TD></TR> 
 <TR><TD ALIGN=RIGHT>
  <A HREF=\"import.php\"
  ><IMG SRC=\"img/ark.gif\" BORDER=0 ALT=\"\"></A>
 </TD><TD ALIGN=LEFT>
 <A HREF=\"import.php\"
 >"._("Import Databases")."</A>
 </TD></TR>
";  

 $display_buffer .= "
    <TR><TD ALIGN=RIGHT>
     <A HREF=\"module_information.php\"
     ><IMG SRC=\"img/magnify.gif\" BORDER=0 ALT=\"\"></A>
    </TD><TD ALIGN=LEFT>
    <A HREF=\"module_information.php\"
     >"._("Module Information")."</A>
    </TD></TR>
 ";

if ($userdata["user"]==1) // if we are root...
 $display_buffer .= "
  <TR><TD ALIGN=RIGHT>
   <A HREF=\"admin.php?action=reinit\"
   ><IMG SRC=\"img/Gear.gif\" BORDER=0 ALT=\"\"></A>
  </TD><TD ALIGN=LEFT>
  <A HREF=\"admin.php?action=reinit\"
  >"._("Reinitialize Database")."</A>
  </TD></TR>
 ";

$display_buffer .= "
  <TR><TD ALIGN=RIGHT>
   <A HREF=\"admin.php?action=cfgform\"
   ><IMG SRC=\"img/config.gif\" BORDER=0 ALT=\"\"></A>
  </TD><TD ALIGN=LEFT>
  <A HREF=\"admin.php?action=cfgform\"
  >"._("Update Config")."</A>
  </TD></TR>
";

if ($userdata["user"]==1) // if we are root...
  $display_buffer .= "
    <TR><TD ALIGN=RIGHT>
     <A HREF=\"user.php?action=view\"
     ><IMG SRC=\"img/monalisa.gif\" BORDER=0 ALT=\"\"></A>
    </TD><TD ALIGN=LEFT>
    <A HREF=\"user.php?action=view\"
     >"._("User Maintenance")."</A>
    </TD></TR>
  ";

  $display_buffer .= "
    <TR><TD ALIGN=RIGHT>
     <IMG SRC=\"img/HandPointingLeft.gif\" BORDER=0 ALT=\"\"></A>
    </TD><TD ALIGN=LEFT>
     <A HREF=\"main.php\"
     ><B>"._("Return to the Main Menu")."</B></A>
    </TD></TR>
    </TABLE>
  "; // end standard font
}

$display_buffer .= "
  <P>
  <CENTER>
  <A HREF=\"main.php\">"._("Return to the Main Menu")."</A>
  </CENTER>
"; // return to main menu tab...

//----- Display template
template_display();

?>
