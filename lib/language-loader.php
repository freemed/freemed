<?php  
 // $Id$
 // desc: language loader for freemed
 // code: Ergin Soysal, MD (soysal@pleksus.net.tr)
 //       minor mods from jeff b (jeff@univrel.pr.uconn.edu)
 // lic : GPL, v2

if (!defined ("__LANGUAGE_LOADER_PHP__")) {

define ('__LANGUAGE_LOADER_PHP__', true);

# Created to allow multilingual support
# 1. if you call a file with lang parameter:
#    a_file.php3?lang=FR
#   page will be rendered in french. Besides,
# a temporary cookie will be set to show these
# pages in french until the browser is closed.
#
# 2. Another cookie for lang can be used
#    for user preferences. If the user selects
#    a language for him(her)self, a permenant
#    cookie can be used to manage this.
#       setcookie("lang", strtoupper($selected_language),
#                  time()+3600*24*365*10, "/"); // 10 years
#
# 3. if none of above is supplied, this scipt
# tries to guess required language via REMOTE_HOST
#
#    Author:  Ergin Soysal, MD
#             Ankara University,
#             Urology Dept.
#                 Ankara/TURKEY
#    E-mail:  soysal@pleksus.net.tr
#
# Note: Calculated language filename is included automatically!
//  

// This variable also may be moved to lib/freemed.php
// used by selLang fxn. domain name vs language..
  $s_contry_langs= array(
//	  "TR"=>"TR",   #  :)
          "US"=>"EN",
	  "UK"=>"EN",
	  "CA"=>"EN",
	  "AU"=>"EN",
	  "NZ"=>"EN",
	  "FR"=>"FR",
	  "BE"=>"FR",
	  "DE"=>"DE",
	  "AT"=>"DE",
	  "IT"=>"IT",
	  "ES"=>"ES",
	  "MX"=>"ES",
	  "BR"=>"BR",
	  "PT"=>"PT"
	 );
	  
  $s_lng= strtoupper($default_language); // from lib/freemed.php
  
  function SelLang($lang)
  {
     global $s_contry_langs, $s_lng;
	 
	 $lang = $s_contry_langs[strtoupper($lang)];
	
	 if($lang)
	 {
	    $s_lng=strtoupper($lang);
		return true;
	 }
	 
	 return false;
  }

  
  if (isset($HTTP_GET_VARS["lang"])) //  file.php?lang=en
  {
     if(selLang($HTTP_GET_VARS["lang"]))
	 // set a temporary cookie, so that next pages
	 // automatically will be shown in this language
	   setcookie("templang", $s_lang);
  }
  elseif (isset($HTTP_COOKIE_VARS["templang"]))
     $s_lng= $HTTP_COOKIE_VARS["templang"];
  elseif (isset($HTTP_COOKIE_VARS["u_lang"]))
     // a persistant cookie for user preferance
     $s_lng= $HTTP_COOKIE_VARS["u_lang"];
  else // try to guess it..
  { 
     $host=getenv('REMOTE_HOST');
     $ip=getenv('REMOTE_ADDR');
     if ( (!$host) || ($host==$ip) )
        $host=@gethostbyaddr($ip);
	 if($host)
	 {
	     selLang(substr($host, strlen($host)-2, 2));
	 }
  } 
 
 
$me=$PHP_SELF;
$dir="";
 
while($w = strpos("@".$me, '/')) {
	$dir .= substr($me, 0, $w);
	$me = substr($me, $w, strlen($me));
}

// if there is no $lang variable or whatever present (error handling)
if (!isset($SESSION["language"])) {
	$s_lng = $language;
} else {
	$s_lng = $SESSION["language"];
}

  // old language loader
  //if (file_exists("lang/$s_lng/API.$s_lng.inc"))
  //  include ("lang/$s_lng/API.$s_lng.inc");
  //if (file_exists("lang/$s_lng/$me.$s_lng.inc"))
  //  include ("lang/$s_lng/$me.$s_lng.inc");

//----- Change directory to the script directory
#chdir(dirname(getenv($PATH_TRANSLATED)));

//----- Set the current language to s_lng
putenv ("LANG=".strtolower($s_lng));
putenv ("LC_ALL=".strtolower($s_lng));
putenv ("LC_CTYPE=".strtolower($s_lng));
$new_locale = setlocale(LC_ALL, $s_lng);
#if (!$new_locale) DIE("could not change to $s_lng locale");
//print "s_lng = $s_lng<BR>\n";

//----- Bind to the proper domain
bindtextdomain ("freemed", "/home/jeff/public_html/freemed/locale/");
textdomain ("freemed");
//print "bindtextdomain (".PACKAGENAME.", ./locale)<BR>\n";

} // end checking for __LANGUAGE_LOADER_PHP__

?>
