<?php
 # file: admin.php3
 # note: administrative functions
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 #       language support by Max Klohn (amk@span.ch)
 # lic : GPL
 # ver : 19991112

 $page_name="admin.php3"; // for help, later
 include "global.var.inc";
 include "freemed-functions.inc"; // include generic functions

 // 19990701 -- enable direct chaining
 SetCookie ("_ref", $page_name, time()+$_cookie_expire);

 // freemed_open_db ($user, $pw);
 freemed_open_db ($LoginCookie);
 freemed_display_html_top ();
 freemed_display_banner ();

  // security patch...
if (freemed_get_userlevel($LoginCookie)<9) {
  echo "
    <$HEADERFONT_B>
    $No_admin_menu_access
    <$HEADERFONT_E>
  ";
  freemed_display_html_bottom ();
  DIE("");
}

if ($action=="cfgform") {

  // this is the frontend to the config
  // database.

  // as of 19990521, the only option implemented,
  // partially, is the ICD9/10 option.

    // icd9 option
  $c_result = fdb_query("SELECT * FROM ".
    "config WHERE (c_option='icd')");
  $c_r = fdb_fetch_array($c_result);
  $icd = $c_r["c_value"];
  if ($icd=="10") {
    $_icd_10 = "$SELECTED";
  } else {
    $_icd_9  = "$SELECTED";
  } // default is icd9

    // gfx option (graphics enhanced)
  $c_result = fdb_query("SELECT * FROM ".
    "config WHERE (c_option='gfx')");
  $c_r = fdb_fetch_array($c_result);
  $gfx = $c_r["c_value"];
  if ($gfx=="1") {
    $_gfx_1 = "$SELECTED";
  } else {
    $_gfx_9  = "$SELECTED";
  } // default is disabled

  $cal_ob = freemed_config_value ("cal_ob");
  switch ($cal_ob) {
    case "enable":  $_cal_ob_e = "$SELECTED"; break;
    case "disable":
           default: $_cal_ob_d = "$SELECTED"; break;
  }

  $calshr = freemed_config_value ("calshr"); // get starting time
  switch ($calshr) {
    case  "4": $_cal_s4  = "$SELECTED"; break;
    case  "5": $_cal_s5  = "$SELECTED"; break;
    case  "6": $_cal_s6  = "$SELECTED"; break;
    case  "7": $_cal_s7  = "$SELECTED"; break;
    case  "8": $_cal_s8  = "$SELECTED"; break;
    case  "9": $_cal_s9  = "$SELECTED"; break;
    case "10": $_cal_s10 = "$SELECTED"; break;
    case "11": $_cal_s11 = "$SELECTED"; break;
    case "12": $_cal_s12 = "$SELECTED"; break;
    default  : $_cal_sd  = "$SELECTED"; break;
  } // end starting time switch

  $calehr = freemed_config_value ("calehr"); // get ending time
  switch ($calehr) {
    case "14": $_cal_e14 = "$SELECTED"; break;
    case "15": $_cal_e15 = "$SELECTED"; break;
    case "16": $_cal_e16 = "$SELECTED"; break;
    case "17": $_cal_e17 = "$SELECTED"; break;
    case "18": $_cal_e18 = "$SELECTED"; break;
    case "19": $_cal_e19 = "$SELECTED"; break;
    case "20": $_cal_e20 = "$SELECTED"; break;
    default:   $_cal_ed  = "$SELECTED"; break;
  } // end ending time switch

  $dtfmt = freemed_config_value ("dtfmt"); // get date format
  switch ($dtfmt) {
    case "mdy":   $_dtfmt_mdy = "SELECTED"; break;
    case "ydm":   $_dtfmt_ydm = "SELECTED"; break;
    case "dmy":   $_dtfmt_dmy = "SELECTED"; break;
    case "ymd":
    default:      $_dtfmt_ymd = "SELECTED"; break;
  } // end date format switch

  $phofmt = freemed_config_value ("phofmt"); // get phone format
  switch ($phofmt) {
    case "usa":           $_phofmt_us = "SELECTED"; break;
    case "unformatted":
    default:              $_phofmt_uf = "SELECTED"; break;
  } // end phone format switch

  freemed_display_box_top ("$packagename Configuration", $page_name);
  echo "
    <P>

    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"cfg\">

    <$STDFONT_B>$Icd_code_type :<$STDFONT_E>
    <SELECT NAME=\"icd\">
     <OPTION VALUE=\"9\"  $_icd_9 > 9
     <OPTION VALUE=\"10\" $_icd_10>10
    </SELECT>

    <BR>

    <$STDFONT_B>$Graphics_enhanced :<$STDFONT_E>
    <SELECT NAME=\"gfx\">
     <OPTION VALUE=\"0\" $_gfx_0>$Disabled
     <OPTION VALUE=\"1\" $_gfx_1>$Enabled
    </SELECT>
    <BR>

    <$STDFONT_B>$Scheduling_start_time<$STDFONT_E>
    <SELECT NAME=\"calshr\">
     <OPTION VALUE=\"\"  $_cal_sd>$DEFAULT
     <OPTION VALUE=\"4\" $_cal_s4>4 am
     <OPTION VALUE=\"5\" $_cal_s5>5 am
     <OPTION VALUE=\"6\" $_cal_s6>6 am
     <OPTION VALUE=\"7\" $_cal_s7>7 am
     <OPTION VALUE=\"8\" $_cal_s8>8 am
     <OPTION VALUE=\"9\" $_cal_s9>9 am
     <OPTION VALUE=\"10\" $_cal_s10>10 am
    </SELECT>
    <BR>

    <$STDFONT_B>$Scheduling_end_time<$STDFONT_E>
    <SELECT NAME=\"calehr\">
     <OPTION VALUE=\"\"  $_cal_ed>$DEFAULT
     <OPTION VALUE=\"14\" $_cal_e14>2 pm
     <OPTION VALUE=\"15\" $_cal_e15>3 pm
     <OPTION VALUE=\"16\" $_cal_e16>4 pm
     <OPTION VALUE=\"17\" $_cal_e17>5 pm
     <OPTION VALUE=\"18\" $_cal_e18>6 pm
     <OPTION VALUE=\"19\" $_cal_e19>7 pm
     <OPTION VALUE=\"20\" $_cal_e20>8 pm
    </SELECT>
    <BR>

    <$STDFONT_B>$Calendar_overbooking<$STDFONT_E>
    <SELECT NAME=\"cal_ob\">
     <OPTION VALUE=\"enable\"  $_cal_ob_e>$Enabled
     <OPTION VALUE=\"disable\" $_cal_ob_d>$Disabled
    </SELECT>
    <BR>

    <$STDFONT_B>Date Format<$STDFONT_E>
    <SELECT NAME=\"dtfmt\">
     <OPTION VALUE=\"ymd\"  $_dtfmt_ymd>YYYY-MM-DD
     <OPTION VALUE=\"mdy\"  $_dtfmt_mdy>MM-DD-YYYY
     <OPTION VALUE=\"dmy\"  $_dtfmt_dmy>DD-MM-YYYY
     <OPTION VALUE=\"ydm\"  $_dtfmt_ydm>YYYY-DD-MM
    </SELECT>
    <P>

    <$STDFONT_B>Phone Number Format<$STDFONT_E>
    <SELECT NAME=\"phofmt\">
     <OPTION VALUE=\"usa\"         $_phofmt_us>United States (XXX) XXX-XXXX
     <OPTION VALUE=\"unformatted\" $_phofmt_uf>Unformatted XXXXXXXXXXXXXXXX
    </SELECT>

    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Configure \">
    <INPUT TYPE=RESET  VALUE=\"   $Reset   \">
    </CENTER>
    </FORM>
  ";
  freemed_display_box_bottom ();

} elseif ($action=="cfg") {

  freemed_display_box_top ("$packagename $Update_config", $page_name);
  echo "
    <P>
  ";

  $q = fdb_query("UPDATE config SET
    c_value='$icd' WHERE c_option='icd'");
  if (($debug) AND ($q)) echo "ICD = $icd<BR>\n";

  $q = fdb_query("UPDATE config SET
    c_value='$gfx' WHERE c_option='gfx'");
  if (($debug) AND ($q)) echo "gfx = $gfx<BR>\n";

  $q = fdb_query("UPDATE config SET
    c_value='$calshr' WHERE c_option='calshr'");
  if (($debug) AND ($q)) echo "calshr = $calshr<BR>\n";

  $q = fdb_query("UPDATE config SET
    c_value='$calehr' WHERE c_option='calehr'");
  if (($debug) AND ($q)) echo "calehr = $calehr<BR>\n";

  $q = fdb_query("UPDATE config SET
    c_value='$cal_ob' WHERE c_option='cal_ob'");
  if (($debug) AND ($q)) echo "cal_ob = $cal_ob<BR>\n";

  $q = fdb_query("UPDATE config SET
    c_value='$dtfmt' WHERE c_option='dtfmt'");
  if (($debug) AND ($q)) echo "dtfmt = $dtfmt<BR>\n";

  $q = fdb_query("UPDATE config SET
    c_value='$phofmt' WHERE c_option='phofmt'");
  if (($debug) AND ($q)) echo "phofmt = $phofmt<BR>\n";

  echo "
    <P>
    <CENTER><B>$Configuration_complete</B></CENTER>
  ";
  freemed_display_box_bottom ();
  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     >$Return_adm_menu</A>
    </CENTER>
  ";

} elseif ($action=="reinit") {
  freemed_display_box_top ("$Reinitialize_database", $page_name);
  
    # here, to prevent problems, we ask the user to check that they
    # REALLY want to...

  echo "$Are_you_sure  $Reinitialize_database ?\n";
  echo "<BR><U><B>$Irreversable_process</B></U><BR>\n";

  echo "<BR><CENTER>\n";

  echo "
   <FORM ACTION=\"$page_name\" METHOD=POST>
   <INPUT TYPE=CHECKBOX NAME=\"first_time\" VALUE=\"first\">
   <I>$First_initialization</I><BR>
   <INPUT TYPE=HIDDEN NAME=action VALUE=\"reinit_sure\">
   <TABLE BORDER=0 ALIGN=CENTER><TR><TD>
   <INPUT TYPE=SUBMIT VALUE=\"  $Continue  \">
   </FORM>

   </TD><TD>

   <FORM ACTION=\"$page_name\" METHOD=POST>
   <INPUT TYPE=SUBMIT VALUE=\"   $Cancel   \">
   </FORM>

   </TD></TR></TABLE>
   </CENTER>
  ";

  freemed_display_box_bottom ();

} elseif ($action=="reinit_sure") {
  # here we actually put the reinitialization (read - wiping
  # and creating the database structure again) code... so that
  # stupids don't accidentally click on it and... oops!

//  if ($first_time!="first") {
//    echo "<$STDFONT_B>$Erasing_old_db ... ";
//    fdb_drop_db($database) OR
//      DIE("<B>$Error_accessing_sql</B><$STDFONT_E><BR><BR>\n");
//   echo "<B>$Done</B><$STDFONT_E><BR>\n";
//  }

//  echo "<$STDFONT_B>$Creating_new_db ... ";
//  fdb_create_db($database) OR
//    DIE("<B>$Error_accessing_sql</B><$STDFONT_E><BR><BR>\n");
//  echo "<B>$Done</B><$STDFONT_E><BR>\n";
//
//  echo "<$STDFONT_B><UL>$Creating_tables ... \n";

  // generate test table, if debug is on
  if ($debug) {
    $result=fdb_query("CREATE TABLE test (name CHAR(10), other CHAR(12), phone INT, id SERIAL, PRIMARY KEY (ID))");
    if ($result) { echo "<LI>test db \n"; }
  } // end debug section

  // generate physician db table
  $result=fdb_query("CREATE TABLE physician (
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
    phydeg1      INT,
    phydeg2      INT,
    phydeg3      INT,
    physpe1      INT,
    physpe2      INT,
    physpe3      INT,
    phyid1       CHAR(10),
    phystatus    INT,
    phyrefcount  INT,
    phyrefamt    REAL,
    phyrefcoll   REAL,
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) { echo "<LI>physician db \n"; }

  // generate icd9 code table
  $result=fdb_query("CREATE TABLE icd9 (
    icd9code     VARCHAR(6),
    icd10code    VARCHAR(7),
    icd9descrip  VARCHAR(45),
    icd10descrip VARCHAR(45),
    icdmetadesc  VARCHAR(30),
    icdng        DATE,
    icddrg       VARCHAR(45),
    icdnum       INT,
    icdamt       REAL,
    icdcoll      REAL,
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) { echo "<LI>icd9 code db \n"; }

  // generate patient database
  $result=fdb_query("CREATE TABLE patient (
    ptdtadd      DATE,
    ptdtmod      DATE,
    ptbal        REAL,
    ptbalfwd     REAL,
    ptunapp      REAL,
    ptrefdoc     INT,
    ptpcp        INT,
    ptphy1       INT,
    ptphy2       INT,
    ptphy3       INT,
    ptphy4       INT,
//    ptbilltype   ENUM(\"sta\",\"mon\",\"chg\") NOT NULL,
    ptbudg       REAL,
    ptdoc        INT,
    ptlname      VARCHAR(50),
    ptfname      VARCHAR(50),
    ptmname      VARCHAR(50),
    ptaddr1      VARCHAR(45),
    ptaddr2      VARCHAR(45),
    ptcity       VARCHAR(45),
    ptstate      CHAR(2),
    ptzip        CHAR(10),
    ptcountry    VARCHAR(50),
    pthphone     VARCHAR(16),
    ptwphone     VARCHAR(16),
    ptfax        VARCHAR(16),
    ptemail      VARCHAR(80),
//    ptsex        ENUM(\"m\",\"f\",\"t\") NOT NULL,
    ptdob        DATE,
    ptssn        VARCHAR(9),
    ptdmv        VARCHAR(15),
    ptdtlpay     DATE,
    ptamtlpay    REAL,
    ptpaytype    INT,
    ptdtbill     DATE,
    ptamtbill    REAL,
    ptstatus     INT,
    ptytdchg     REAL,
    ptar         REAL,
    ptextinf     TEXT,
    ptdisc       REAL,
    ptdol        DATE,
    ptdiag1      INT,
    ptdiag2      INT,
    ptdiag3      INT,
    ptdiag4      INT,
    ptid         VARCHAR(10),
    pthistbal    REAL,
//    ptmarital    ENUM(\"single\",\"married\",
//                      \"divorced\", \"separated\") NOT NULL,
//    ptempl       ENUM(\"y\",\"n\") NOT NULL,
    ptemp1       INT,
    ptemp2       INT,
    ptdep        INT,
    ptins1       INT,
    ptins2       INT,
    ptins3       INT,
    ptnextofkin  TEXT,
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) { echo "<LI>patient db \n"; }

  // generate procedure table
  // CURRENTLY NOT SET UP as of 19990525
  $result=fdb_query("CREATE TABLE proc (
    procacct       INT,
    procacctdep    INT,
    procincident   INT,
    procdtmod      DATE,
    procchg        REAL,
    procrcp        REAL,
    procadj        REAL,
    proccode       INT,
    procmod1       INT,
    procmod2       INT,
    procmod3       INT,
    procdx1        INT,
    procdx2        INT,
    procdx3        INT,
    procdx4        INT,
    proccom        VARCHAR(45),
    procprov       INT,
    procpos        INT,
    proctos        INT,
    procstatus     INT,
    procdept       INT,
    procvouch      INT,
    procpriins     INT,
    procsecins     INT,
    procunits      REAL,
//    proctype       ENUM(\"normal\", \"finance\",
//      \"other\", \"contract\") NOT NULL,
    procdate       DATE,
    procdtpost     DATE,
    procdtpostrep  DATE,
    procdtinsbill  DATE,
    procdtinsbill2 DATE,
//    procassign     ENUM(\"yes\", \"no\",
//      \"estimate\") NOT NULL,
    proctax        CHAR(5),
    procaprov      REAL,
//    procemc        ENUM(\"valid\", \"passed_prebill\",
//      \"no_emc\", \"emc_billed\") NOT NULL,
    procloc        INT,
    procclmnum     INT,
    procmin        REAL,
    procplan       INT,
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) { echo "<LI>procedure db \n"; }

  // generate facility database
  $result=fdb_query("CREATE TABLE facility (
    psrname      CHAR(25),
    psraddr1     CHAR(25),
    psraddr2     CHAR(25),
    psrcity      CHAR(15),
    psrstate     CHAR(3),
    psrzip       CHAR(10),
    psrcountry   VARCHAR(50),
    psrnote      VARCHAR(40),
    psrdateentry DATE,
    psrdefphy    INT,
    psrphone     VARCHAR(16),
    psrfax       VARCHAR(16),
    psremail     CHAR(25),
    id SERIAL,
    PRIMARY KEY (id) )");
  if ($result) echo "<LI>facility db \n"; 
  $result=fdb_query("INSERT INTO facility VALUES (
   'Default Facility', '', '', '', '', '', '', '', '$cur_date',
   '', '', '', '')");
  if ($result) echo "<I>(default facility added)</I> \n"; 

  // generate room database
  $result=fdb_query("CREATE TABLE room (
    roomname     CHAR(20),
    roompos      INT,
    roomdescrip  CHAR(40),
    roomdefphy   INT,
//    roomsurgery  ENUM(\"y\", \"n\") NOT NULL,
//    roombooking  ENUM(\"y\", \"n\") NOT NULL,
    roomipaddr   VARCHAR(15),
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) echo "<LI>room db \n"; 
//  $result=fdb_query("INSERT INTO room VALUES (
//   'Default Room', '1', '', '', '', '', '') ");
    $result=fdb_query("INSERT INTO room VALUES (
     'Default Room', '1', '', '', '') ");
  if ($result) echo "<I>(default room added)</I> \n";

  // generate degrees database
  $result=fdb_query("CREATE TABLE degrees (
    degdegree     CHAR(10),
    degname       VARCHAR(50),
    degdate       DATE,
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) echo "<LI>degrees db \n"; 
//  if (freemed_import_stock_data ("degrees"))
//    echo "<I>(degrees data)</I> \n";

  // generate specialties database
  $result=fdb_query("CREATE TABLE specialties (
    specname      VARCHAR(50),
    specdesc      VARCHAR(100),
    specdatestamp DATE,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>specialties db \n";
//  if (freemed_import_stock_data ("specialties"))
//    echo "<I>(specialties data)</I> \n";

  // generate insurance company database
  $result=fdb_query("CREATE TABLE insco (
    inscodtadd   DATE,
    inscodtmod   DATE,
    insconame    VARCHAR(50),
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
    inscogroup   INT,
    inscotype    INT,
    inscoassign  INT,
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) echo "<LI>insurance company db \n"; 
  if (freemed_import_stock_data ("insco"))
    echo "<I>(insurance company data)</I> \n";

  // generate insurance company groups db
  $result=fdb_query("CREATE TABLE inscogroup (
    inscogroup     VARCHAR(50),
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) echo "<LI>insurance co groups db \n"; 
  if (freemed_import_stock_data ("inscogroup"))
    echo "<I>(insurance company group data)</I> \n";

  // generate cpt codes db, finally (19990625)
  $result=fdb_query("CREATE TABLE cpt (
    cptcode        CHAR(7),
    cptnameint     VARCHAR(50),
    cptnameext     VARCHAR(50),
    cptrelvalue    REAL,
//    cptgender      ENUM (\"n\", \"m\", \"f\") NOT NULL,
    cpttos         INT,
    cptdtadd       DATE,
    cptdtmod       DATE,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>cpt codes db \n";
  if (freemed_import_stock_data ("cpt"))
    echo "<I>(cpt data)</I> \n";

  // generate cpt modifier db (19990605)
  $result=fdb_query("CREATE TABLE cptmod (
    cptmod         CHAR(2),
    cptmoddescrip  VARCHAR(50),
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>cpt modifers db \n";
  if (freemed_import_stock_data ("cptmod"))
    echo "<I>(cpt modifier data)</I> \n";

  // generate physician groups db (19990625)
  $result=fdb_query("CREATE TABLE phygroup (
    phygroupname   VARCHAR(100),
    phygroupfac    INT,
    phygroupdtadd  DATE,
    phygroupdtmod  DATE,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>phygroup db \n";

  // generate user db
  $result=fdb_query("CREATE TABLE users (
    username       VARCHAR(16) NOT NULL,
    userpassword   VARCHAR(16) NOT NULL,
    userdescrip    VARCHAR(50),
    userlevel      INT,
//    usertype       ENUM (\"phy\", \"misc\") NOT NULL,
//    userfac        BLOB,
//    userphy        BLOB,
//    userphygrp     BLOB,
//    userrealphy    INT,
    id SERIAL,
    PRIMARY KEY (id)
//    UNIQUE idx_id (id),
//    KEY (username),
//    UNIQUE idx_username (username)
    )");
  if ($result) echo "<LI>user db \n";

//  $result=fdb_query("INSERT INTO users VALUES (
//    'root', '$db_password', 'Superuser', '9', '', '-1', '-1', '-1',
//    '')");
    $result=fdb_query("INSERT INTO users VALUES (
      'root', '$db_password', 'Superuser')");
  if ($result) echo "<I>[[added superuser]]</I> \n";

  // generate scheduler table
  $result=fdb_query("CREATE TABLE scheduler (
    caldateof         DATE,
//    caltype           ENUM (\"temp\", \"pat\") NOT NULL,
    calhour           INT,
    calminute         INT,
    calduration       INT,
    calfacility       INT,
    calroom           INT,
    calphysician      INT,
    calpatient        INT,
    calcptcode        INT,
    calstatus         INT,
    calprenote        VARCHAR(100),
    calpostnote       TEXT,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>scheduler db \n";

  // generate physician availability map
  $result=fdb_query("CREATE TABLE phyavailmap (
    pamdatefrom      DATE,
    pamdateto        DATE,
    pamtimefromhour  INT,
    pamtimefrommin   INT,
    pamtimetohour    INT,
    pamtimetomin     INT,
    pamphysician     INT,
    pamcomment       VARCHAR(75),
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>physician availability map db \n";

  // generate insurance company groups db
  $result=fdb_query("CREATE TABLE phystatus (
    phystatus      VARCHAR(30),
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) echo "<LI>insurance co groups db \n";

  // generate progress notes db (19990707)
  $result=fdb_query("CREATE TABLE pnotes (
    pnotesdt       DATE,
    pnotesdtadd    DATE,
    pnotesdtmod    DATE,
    pnotespat      INT,
    pnotes_S       TEXT,
    pnotes_O       TEXT,
    pnotes_A       TEXT,
    pnotes_P       TEXT,
    pnotes_I       TEXT,
    pnotes_E       TEXT,
    pnotes_R       TEXT,
    id SERIAL, 
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>progress notes db \n";

  // generate payment record db (19990709)
  $result=fdb_query("CREATE TABLE payrec (
    payrecdtadd   DATE,
    payrecdtmod   DATE,
    payrecpatient INT,
    payrecdt      DATE,
    payrecsource  INT,
    payreclink    INT,
    payrectype    INT,
    payrecnum     VARCHAR(100),
    payrecamt     REAL,
    payrecdescrip TEXT,
    id SERIAL, 
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>payment record db \n";

  // generate formulary database (19990714)
  // date mod/add added 19990719 - jeff b
  $result=fdb_query("CREATE TABLE frmlry (
    frmlrydtadd    DATE,
    frmlrydtmod    DATE,
    class          VARCHAR(20),
    gnrcname       VARCHAR(20),
    trdmrkname     VARCHAR(20),
    ind1           VARCHAR(50),
    ind2           VARCHAR(50),
    ind3           VARCHAR(50),
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>formulary db \n";
  if (freemed_import_stock_data ("frmlry"))
    echo "<I>(formulary data)</I> \n";

  // Rx (prescription) database (19990723)
  $result=fdb_query("CREATE TABLE rx (
    rxdtadd        DATE,
    rxdtmod        DATE,
    rxpatient      INT,
    rxdtfrom       DATE,
    rxduration     INT,
    rxdrug         INT,
    rxdosage       VARCHAR (100),
    rxrefills      INT,
    rxsubstitute   VARCHAR (30),
    rxmd5sum       VARCHAR (50),
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>prescription db \n";

  // generate simple reports table (19990810)
  $result=fdb_query("CREATE TABLE simplereport (
    sr_label       VARCHAR(50),
    sr_type        INT,
    sr_text        TEXT,
    sr_textf       TEXT,
    sr_textcm      TEXT,
    sr_textcf      TEXT,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>simple reports db \n";

  // generate call-in table/db (19990824)
  $result=fdb_query("CREATE TABLE callin (
    cilname        VARCHAR(50),
    cifname        VARCHAR(50),
    cimname        VARCHAR(50),
    cihphone       VARCHAR(16),
    ciwphone       VARCHAR(16),
    cidob          DATE,
    cicomplaint    TEXT,
    cidatestamp    DATE,
    cifacility     INT,
    ciphysician    INT,
    citookcall     VARCHAR(50),
    cipatient      INT,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>call-in db \n ";

  // generate room equipment inventory db
  $result=fdb_query("CREATE TABLE roomequip (
    rename         VARCHAR(100),
    redescrip      TEXT,
    redateadd      DATE,
    redatemod      DATE,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>room equipment db \n ";
  if (freemed_import_stock_data("roomequip"))
    echo "<I>(room equipment data)</I> \n ";

  // generate type of service db (19990922)
  $result=fdb_query("CREATE TABLE tos (
    tosname        VARCHAR(75),
    tosdescrip     VARCHAR(200),
    tosdtadd       DATE,
    tosdtmod       DATE,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>type of service (TOS) db \n ";
  if (freemed_import_stock_data("tos"))
    echo "<I>(type of service data)</I> \n ";

  // generate patient record template (custom) db
  $result=fdb_query("CREATE TABLE patrectemplate (
    prtname        VARCHAR(100),
    prtdescrip     VARCHAR(100),
    prtfname       TEXT,
    prtvar         TEXT,
    prtftype       TEXT,
    prtftypefor    TEXT,
    prtfmaxlen     TEXT,
    prtfcom        TEXT,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>patient record template db \n";
  if (freemed_import_stock_data("patrectemplate"))
    echo "<I>(patient record template data)</I> \n";
 
  // generate diagnosis family db (19991029)
  $result=fdb_query("CREATE TABLE diagfamily (
    dfname         VARCHAR(100),
    dfdescrip      VARCHAR(100),
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>daignosis family db \n";
  if (freemed_import_stock_data("diagfamily"))
    echo "<I>(diagnosis family data)</I> \n";

  // generate patient record data (custom) db
  $result=fdb_query("CREATE TABLE patrecdata (
    prpatient      INT,
    prtemplate     INT,
    prdtadd        DATE,
    prdtmod        DATE,
    prdata         TEXT,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>patient record data db \n";

  // generate action log table db
  $result=fdb_query("CREATE TABLE log (
    datestamp      DATE,
    users           INT,
    db_name        VARCHAR(20),
    rec_num        INT,
    comment        TEXT,
    id SERIAL,
    PRIMARY KEY (id)
    )");
  if ($result) echo "<LI>action log db \n";

  // generate configuration table info (updated 19991007)
  $result=fdb_query("CREATE TABLE config (
    c_option       CHAR(6),
    c_value        VARCHAR(100),
    id SERIAL,
    PRIMARY KEY (id)    
    )");
  if ($result) echo "<LI>config db \n";

  if (fdb_query("INSERT INTO config VALUES (
    'icd', '9')"))   if ($debug)   echo "(ICD) \n";
  if (fdb_query("INSERT INTO config VALUES (
    'gfx', '0')"))   if ($debug)   echo "(graphics) \n";
  if (fdb_query("INSERT INTO config VALUES (
    'calshr', '$cal_starting_hour')")) if ($debug) echo "(calshr) \n";
  if (fdb_query("INSERT INTO config VALUES (
    'calehr', '$cal_ending_hour')")) if ($debug) echo "(calehr) \n";
  if (fdb_query("INSERT INTO config VALUES (
    'cal_ob', 'enable')")) if ($debug) echo "(cal_ob) \n";
  if (fdb_query("INSERT INTO config VALUES (
    'dtfmt', 'ymd')")) if ($debug) echo "(dtfmt) \n";
  if (fdb_query("INSERT INTO config VALUES (
    'phofmt', 'unformatted')")) if ($debug) echo "(phofmt) \n";

  // generate incoming faxes table (19990919)
  $result=fdb_query("CREATE TABLE infaxes (
    infcode	  VARCHAR(5),  
    infsender	  VARCHAR(50),
    inftotpages	  INT,
    infthispage	  INT,
    inftimestamp  TIMESTAMP,
    infimage	  VARCHAR(50),
//    inforward	  ENUM(\"no\",\"yes\") NOT NULL,		
//    infack	  ENUM(\"no\",\"yes\") NOT NULL,
    infptid	  VARCHAR(10),
    infphysid	  VARCHAR(10),
    id            SERIAL,
    PRIMARY KEY (id)
    );");
   if ($result) echo "<LI>incoming faxes db \n";

  // generate fax sender lookup table (19990924)
  $result=fdb_query("CREATE TABLE infaxlut (
    lutsender VARCHAR(50),
    lutname   VARCHAR(50),
    id SERIAL,
    PRIMARY KEY (id)
    );");
  if ($result) echo "<LI>fax sender lookup db \n";

   // generate printers table (19991008)
   $result = fdb_query ("CREATE TABLE printer (
     prntname   VARCHAR(50),
     prnthost   VARCHAR(50),
//     prntaclvl  ENUM(\"9\",\"8\",\"7\",\"6\",\"5\",\"4\",\"3\",\"2\",\"1\",\"0\") NOT NULL,
     prntdesc   VARCHAR(100),
     id         SERIAL,
     PRIMARY KEY (id)
     );");
   if ($result) echo "<LI>printers db \n";

   // generate fixed form table (19991020)
  $result = fdb_query ("CREATE TABLE fixedform (
     ffname        VARCHAR(50),
     ffdescrip     VARCHAR(100),
     ffpagelength  INT,
     fflinelength  INT,
     ffloopnum     INT,
     ffloopoffset  INT,
     ffcheckchar   CHAR(1),
     ffrow         TEXT,
     ffcol         TEXT,
     fflength      TEXT,
     ffdata        TEXT,
     ffformat      TEXT,
     ffcomment     TEXT,
     id            SERIAL,
     PRIMARY KEY (id)
     );");
  if ($result) echo "<LI>fixed forms db \n";
  if (freemed_import_stock_data("fixedform"))
    echo "<I>(fixed forms data)</I> \n";

  $result = fdb_query ("CREATE TABLE eoc (
     eocpatient                INT,
     eocdescrip                VARCHAR(100),
     eocstartdate              DATE,
     eocdtlastsimilar          DATE,
     eocreferrer               INT,
     eocfacility               INT,
     eocdiagfamily             TEXT,
//     eocrelpreg                ENUM (\"no\", \"yes\") NOT NULL,
//     eocrelemp                 ENUM (\"no\", \"yes\") NOT NULL,
//     eocrelauto                ENUM (\"no\", \"yes\") NOT NULL,
//     eocrelother               ENUM (\"no\", \"yes\") NOT NULL,
     eocrelstpr                VARCHAR(10),
//     eoctype                   ENUM (\"acute\",
//                                     \"chronic\",
//                                    \"chronic recurrent\",
//                                     \"historical\") NOT NULL,
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
     eocrelpregcycle           INT,
     eocrelpreggravida         INT,
     eocrelpregpara            INT,
     eocrelpregmiscarry        INT,
     eocrelpregabort           INT,
     eocrelpreglastper         DATE,
     eocrelpregconfine         DATE,
     eocrelothercomment        VARCHAR(100),
     id                        SERIAL,
     PRIMARY KEY (id)
     );");
  if ($result) echo "<LI>episode of care db \n";

  $result = fdb_query ("CREATE TABLE oldreports (
     oldrep_timestamp          DATE,
     oldrep_label              VARCHAR(50),
     oldrep_type               INT,
     oldrep_sender             INT,
     oldrep_delivery           VARCHAR(20),
     oldrep_author             INT,
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
     id                        SERIAL,
     PRIMARY KEY (id)
     );");
  if ($result) echo "<LI>old reports db \n";

  $result = fdb_query ("CREATE TABLE patimg (
     pipatient                 INT,
     pilink                    INT,
     pidate                    INT,
//     pitype                    ENUM (\"picture\", \"xray\") NOT NULL,
//     pidata                    BLOB, 
     id                        SERIAL,
     PRIMARY KEY (id)
     );");
  if ($result) echo "<LI>patient images db \n";

  echo "</UL><B>done</B><$STDFONT_E><BR>\n";
  
  // now generate "return code" so that we can get back to the
  // admin menu... or perhaps skip that... ??

  echo "
    <BR><BR><CENTER>
    <A HREF=\"$page_name?$_auth\">
     <$STDFONT_B>$Return_adm_menu<$STDFONT_E></A>
    </CENTER>
  ";

} else {

  // actual menu code for admin menu goes here \/

freemed_display_box_top("$package_name $Administration_menu", $_ref,
$page_name);

echo "
  <$STDFONT_B>
  <TABLE WIDTH=100% VALIGN=CENTER ALIGN=CENTER BORDER=0 CELLSPACING=2
   CELLPADDING=0>
 "; // begin standard font

$_userdata = explode (":", $LoginCookie);
if ($_userdata[0]==1) // if we are root...
 echo "
  <TR><TD ALIGN=RIGHT BGCOLOR=#dddddd>
   <A HREF=\"$page_name?$_auth&action=reinit\"
   ><IMG SRC=\"img/Gear.gif\" BORDER=0 ALT=\"[*]\"></A>
  </TD><TD ALIGN=LEFT>
  <A HREF=\"$page_name?$_auth&action=reinit\">$Reinitialize_database</A>
  </TD></TR>
 ";

echo "
  <TR><TD ALIGN=RIGHT BGCOLOR=#dddddd>
   <A HREF=\"$page_name?$_auth&action=cfgform\"
   ><IMG SRC=\"img/config.gif\" BORDER=0 ALT=\"[*]\"></A>
  </TD><TD ALIGN=LEFT>
  <A HREF=\"$page_name?$_auth&action=cfgform\">$Update_config</A>
  </TD></TR>
";

if ($_userdata[0]==1)  // if we are root...
  echo "
    <TR><TD ALIGN=RIGHT BGCOLOR=#dddddd>
     <A HREF=\"user.php3?$_auth&action=view\"
     ><IMG SRC=\"img/monalisa.gif\" BORDER=0 ALT=\"[*]\"></A>
    </TD><TD ALIGN=LEFT>
    <A HREF=\"user.php3?$_auth&action=view\"
     >$User_maintenance</A>
    </TD></TR>
  ";

  echo "
    <TR><TD ALIGN=RIGHT BGCOLOR=#dddddd>
     <A HREF=\"main.php3?$_auth\"
     ><IMG SRC=\"img/HandPointingLeft.gif\" BORDER=0 ALT=\"[*]\"></A>
    </TD><TD ALIGN=LEFT>
     <A HREF=\"main.php3?$_auth\"><B>$Return_main_menu</B></A>
    </TD></TR>
    </TABLE><$STDFONT_E>
  "; // end standard font

  freemed_display_box_bottom ();
}

freemed_close_db(); // close up database

echo "
  <P>
  <$STDFONT_B>
  <CENTER>
  <A HREF=\"main.php3?$_auth\">$Return_main_menu</A>
  </CENTER>
  <$STDFONT_E>
"; // return to main menu tab...

freemed_display_html_bottom (); // ending of document...
?>
