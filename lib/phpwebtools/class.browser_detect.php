<?php 
 // $Id$
 // desc: detect browser and platform through HTTP headers
 // code: epsilon7 for PHyX8 studios <epsilon7@asphyxia.com>
 //       slightly modified by jeff b <jeff@ourexchange.net>
 // lic : LGPL

 //Variables:
 //$bd->BROWSER   // Netscape, MSIE, Opera
 //$bd->PLATFORM  // Win95, Unix, Windows 98, Mac, PPC, etc.
 //$bd->VERSION   // MajorVersion.MinorVersion
 //$bd->MAJORVER  // Major Version (before . in version string)
 //$bd->MINORVER  // Minor Version (after . in version string)

if (!defined("__CLASS_BROWSER_DETECT_PHP__")) {

define ('__CLASS_BROWSER_DETECT_PHP__', true);

class browser_detect
{
   var $UA         =  "";
   var $BROWSER    =  "Unknown";
   var $PLATFORM   =  "Unknown";
   var $VERSION    =  "";
   var $MAJORVER   =  "";
   var $MINORVER   =  "";
   
   // fold functionality into constructor (jeff)
   function browser_detect()
   {  
   	// Check for cached copy of browser detect
	if (isset($GLOBALS['__phpwebtools']['browser_detect']))
		return $GLOBALS['__phpwebtools']['browser_detect'];

	// moved user agent test into constructor
      $this->UA  = getenv("HTTP_USER_AGENT");
      $preparens  =  "";
      $parens     =  "";
      $i = strpos($this->UA,"(");
      if ($i > 0)
      {  $preparens  = trim(substr($this->UA,0,$i));
         $parens     = substr($this->UA,$i+1,strlen($this->UA));
         $j = strpos($parens,")");
         if($j>=0)
         {  $parens = substr($parens,0,$j);
         }
      }
      else
      {  $preparens = $this->UA;
      }
      $browVer = $preparens;
      $token = trim(strtok($parens,";"));
      //echo "<BR>browsVer = $browVer, token = $token <BR>\n";
      while($token) {
        if($token=="compatible") {  
        } elseif(preg_match("/MSIE/i","$token")) {
          $browVer = $token;
        } elseif(preg_match("/Opera/i","$token")) {
          $browVer = $token;
        } elseif(preg_match("/X11/i","$token") || preg_match("/SunOS/i","$token") || preg_match("/Linux/i","$token")) {
          $this->PLATFORM   =  "Unix";
        } elseif(preg_match("/Win/i","$token")) {
          $this->PLATFORM   =  $token;
        } elseif(preg_match("/Mac/i","$token") || preg_match("/PPC/i","$token")) {
          $this->PLATFORM   =  $token;
        }
        $token = strtok(";");
      }

      // Detect Internet Explorer
      $msieIndex  =  strpos($browVer,"MSIE");
      if($msieIndex >= 0) {
        $browVer = substr($browVer,$msieIndex,strlen($browVer));
      }

      // Differentiate between Netscape and Mozilla
      if (preg_match("/Gecko/i", $this->UA)) {
        $this->GECKO = true;
      } else {
        $this->GECKO = false;
      }

      // Check for Mozilla

      $leftover   =  "";
      if(substr($browVer,0,strlen("Mozilla")) == "Mozilla")
      {  $this->BROWSER =  "Netscape";
         $leftover=substr($browVer,strlen("Mozilla")+1,strlen($browVer));
      }
      elseif(substr($browVer,0,strlen("Lynx")) == "Lynx")
      {  $this->BROWSER =  "Lynx";
         $leftover=substr($browVer,strlen("Lynx")+1,strlen($browVer));
      }
      elseif(substr($browVer,0,strlen("MSIE")) == "MSIE")
      {  $this->BROWSER =  "IE";
         $leftover=substr($browVer,strlen("MSIE")+1,strlen($browVer));
      }
      elseif(substr($browVer,0,strlen("Microsoft Internet Explorer")) == "Microsoft Internet Explorer")
      {  $this->BROWSER =  "IE";
         $leftover=substr($browVer,strlen("Microsoft Internet Explorer")+1,strlen($browVer));
      }
      elseif(substr($browVer,0,strlen("Opera")) == "Opera")
      {  $this->BROWSER =  "Opera";
         $leftover=substr($browVer,strlen("Opera")+1,strlen($browVer));
      }
      elseif(substr($browVer,0,strlen("UP.Browser")) == "UP.Browser")
      {  $this->BROWSER =  "UP.Browser";
         $leftover=substr($browVer,strlen("UP.Browser")+1,strlen($browVer));
      }

      $leftover = trim($leftover);
      $i=strpos($leftover," ");
      if($i > 0)
      {  $this->VERSION = substr($leftover,0,$i);
      }
      else
      {  $this->VERSION = $leftover;
      }

      $j = strpos($this->VERSION,".");
      if($j >= 0)
      {  $this->MAJORVER = substr($this->VERSION,0,$j);
         $this->MINORVER = substr($this->VERSION,$j+1,strlen($this->VERSION));
      }
      else
      {  $this->MAJORVER = $this->VERSION;
      }

	// Cache copy of this object
	$GLOBALS['__phpwebtools']['browser_detect'] = $this;
   } // end constructor browser_detect

   function support_javascript ($nullval = "") {
      switch ($this->BROWSER) {
         case "Netscape":
           if ($this->MAJORVER >= 4) return true;
           return false; // otherwise
           break; // Netscape
         case "IE":
           if ($this->MAJORVER >= 4) return true;
           return false;
           break; // IE
         case "Opera":
           return true; // all versions support this
           break; // Opera
         case "Mozilla":
           return true; // all versions support this
           break; // Mozilla
         case "Lynx":
           return false; // no versions (yet) support this
           break; // Lynx
         case "Konqueror":
           return true; // all Konqi versions *should* allow this
           break; // Konqueror
         default:
           return false; // assume that it doesn't work
           break; // default (anything else)
      } // end switch
   } // end function browser_detect->support_javascript()

   function support_dhtml($nullval = "") {
      switch ($this->BROWSER) {
         case "Netscape":
           if ($this->MAJORVER >= 4) return true;
           return false; // otherwise
           break; // Netscape
         case "Mozilla":
           return true; // all versions support this
           break;
         default:
           return false; // assume that it doesn't work
           break; // default (anything else)
      } // end switch
   } // end function browser_detect->support_dhtml()

   function support_css ($nullval = "") {
      switch ($this->BROWSER) {
         case "Netscape":
           if ($this->MAJORVER >= 4) return true;
           return false; // otherwise
           break; // Netscape
         case "IE":
           if ($this->MAJORVER >= 4) return true;
           return false; // otherwise
           break; // IE
         case "Mozilla":
           return true; // all versions support this
           break;
         default:
           return false; // assume that it doesn't work
           break; // default (anything else)
      } // end switch
   } // end function browser_detect->support_css()

   function support_cookies ($nullval = "") {
      switch ($this->BROWSER) {
         default:
           return true; // currently just a stub
           break;
      } // end switch
   } // end function browser_detect->support_cookies()

   function support_hdml ($nullval = "") {
      switch ($this->BROWSER) {
         case "UP.Browser":
	   return true;  // phone.com browser
	                 // known to be used on:
			 //   AudioVox 9000
         default:
           return false; // assume that it doesn't work
           break; // default (anything else)
      } // end switch
   } // end function browser_detect->support_hdml()

   function support_wap ($nullval = "") {
      return ($this->support_hdml() or $this->support_wml());
   } // end function browser_detect->support_wap()

   function support_wml ($nullval = "") {
      switch ($this->BROWSER) {
         default:
           return false; // assume that it doesn't work
           break; // default (anything else)
      } // end switch
   } // end function browser_detect->support_wml()

} // end class browser_detect

} // end if not defined

?>
