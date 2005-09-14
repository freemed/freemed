<?php  
 // $Id$
 // desc: language loader
 // code: Ergin Soysal, MD (soysal@pleksus.net.tr)
 //       minor mods from jeff b (jeff@ourexchange.net)
 // lic : LGPL

if (!defined ("__LANGUAGE_TOOLS_PHP__")) {

define ('__LANGUAGE_TOOLS_PHP__', true);

function detect_language ($default_language) {
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
  # Note - Calculated language filename is included automatically!

  global $HTTP_GET_VARS, $HTTP_COOKIE_VARS;

  // This variable also may be moved to global.var.inc
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
	  
  $s_lng = strtoupper($default_language); // from global.var.inc
  
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

  
  if ($HTTP_GET_VARS["lang"]) //  file.php?lang=en
  {
     if(strtoupper($s_country_langs[strtoupper($HTTP_GET_VARS["lang"])])) 
	 // set a temporary cookie, so that next pages
	 // automatically will be shown in this language
	   setcookie("templang", $s_lang);
  }
  //elseif ($HTTP_COOKIE_VARS["templang"])
  //   $s_lng= $HTTP_COOKIE_VARS["templang"];
  //elseif ($HTTP_COOKIE_VARS["u_lang"])
  //   // a persistant cookie for user preferance
  //   $s_lng= $HTTP_COOKIE_VARS["u_lang"];
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
 
  // if there is no $lang variable or whatever present (error handling)
  if (strlen($s_lng) != 2)  $s_lng = $default_language;

  return $s_lng;
} // end function detect_language


function set_language ($package, $language, $locale_dir="/usr/share/locale") {
  // gettext bindings
  putenv ("LANG=".(
    (strlen($language)!=5) ?
    strtolower($language)  :
    strtolower(substr($language,0,2))."_".strtoupper(substr($language,-2))
  ));
  bindtextdomain ($package, $locale_dir);
  textdomain ($package);
}

} // end checking for __LANGUAGE_TOOLS_PHP__

?>
