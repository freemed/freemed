<?php 
 // $Id$
 // desc: HDML/WML hybrid class
 // code: Norbert Huffschmid
 // lic : LGPL

if (!defined ("__CLASS_HAWHAW_PHP__")) {

define ('__CLASS_HAWHAW_PHP__', true);

include_once(WEBTOOLS_ROOT.'/wap_tools.php');

// HAWHAW: HTML and WML hybrid adapted webserver
// PHP class library
// Copyright (C) 2000 Norbert Huffschmid
// Last modified: 3. June 2000
// Tested with PHP 3.0.7 and PHP 3.0.13

// miscellaneous constants
define("HAW_VERSION", "HAWHAW V3.02");
define("HAW_COPYRIGHT", "(C) Norbert Huffschmid");

// constants for markup languages
define('HAW_HTML', 1);
define('HAW_WML',  2);
define('HAW_HDML', 3);

// constant to turn on debugger mode
define("HAW_DEBUG", 0);             // non-debugging mode (standard)
// define("HAW_DEBUG", HAW_HTML);   // debug HTML
// define("HAW_DEBUG", HAW_WML);    // debug WML
// define("HAW_DEBUG", HAW_HDML);   // debug HDML


// constants for page elements
define("HAW_PLAINTEXT", 1);
define("HAW_IMAGE", 2);
define("HAW_TABLE", 3);
define("HAW_FORM", 4);
define("HAW_LINK", 5);
define("HAW_INPUT", 6);
define("HAW_CHECKBOX", 7);
define("HAW_RADIO", 8);
define("HAW_HIDDEN", 9);
define("HAW_SUBMIT", 10);

// constants for page setup
define("HAW_ALIGN_LEFT", 1);
define("HAW_ALIGN_RIGHT", 2);
define("HAW_ALIGN_CENTER", 3);
define("HAW_NOTITLE", -1);

// constants for text formatting
define("HAW_TEXTFORMAT_NORMAL", 0);
define("HAW_TEXTFORMAT_BOLD", 1);
define("HAW_TEXTFORMAT_UNDERLINE", 2);
define("HAW_TEXTFORMAT_ITALIC", 4);
define("HAW_TEXTFORMAT_BIG", 8);
define("HAW_TEXTFORMAT_SMALL", 16);

// constants for input treatment
define("HAW_INPUT_TEXT", 0);
define("HAW_INPUT_PASSWORD", 1);

// constants for radio and checkbox treatment
define("HAW_NOTCHECKED", 0);
define("HAW_CHECKED", 1);

// constants for HDML card types
define("HAW_HDML_DISPLAY", 0);
define("HAW_HDML_ENTRY", 1);
define("HAW_HDML_CHOICE", 2);
define("HAW_HDML_NODISPLAY", 3);

function HAW_specchar($input)
{
  // convert special characters like Ä, Ö, Ü, ...

  $temp = htmlspecialchars($input); // translate &"<> to HTML entities

  for ($i=0; $i<strlen($temp); $i++)
  {
    // do for each character of $temp

    if (ord(substr($temp, $i, 1)) >= 160)
      // translate character into &#...; sequence
      $output .= "&#" . ord(substr($temp, $i, 1)) . ";";
    else
      // copy character unchanged
      $output .= substr($temp, $i, 1);
  }

  return($output);
}







class HAW_hdmlcardset
{
  var $number_of_cards;
  var $card;            // array of cards
  var $title;
  var $final_action;    // action of last card
  var $defaults;        // default values of variables
  var $disable_cache;


  function HAW_hdmlcardset($title, $defaults, $disable_cache)
  {
    $this->title = $title;
    $this->defaults = $defaults;
    $this->disable_cache = $disable_cache;

    // initialize first card of cardset as DISPLAY card

    $this->card[0]["type"] = HAW_HDML_DISPLAY;

    $this->card[0]["options"] = " name=\"1\"";

    if ($title)
      $this->card[0]["options"] .= " title=\"$title\"";

    $this->number_of_cards = 1;
  }


  function add_display_content($display_content)
  {
    // enhance the display content of the current card with the received content

    // number_of_cards-1 is the index of the current card, i.e. the last card

    if ($this->card[$this->number_of_cards-1]["type"] == HAW_HDML_DISPLAY)
      // current card is display card ==> continue with content
      $this->card[$this->number_of_cards-1]["display_content"] .= $display_content;

    else
    {
      // current card is entry or choice card
      // ==> create new display card to display received content
      // ==> link current card to this new display card

      $this->card[$this->number_of_cards]["type"] = HAW_HDML_DISPLAY;

      $cardname = sprintf(" name=\"%d\"", $this->number_of_cards+1);
      $this->card[$this->number_of_cards]["options"] .= $cardname;

      if ($this->title)
        $this->card[$this->number_of_cards]["options"] .= " title=\"$this->title\"";

      $this->card[$this->number_of_cards]["display_content"] = $display_content;

      $action = sprintf("<action type=\"accept\" task=\"go\" dest=\"#%d\">\n",
                         $this->number_of_cards+1);
      $this->card[$this->number_of_cards-1]["action"] = $action;

      $this->number_of_cards++;
    }
  }


  function make_ui_card($options, $generic_content, $cardtype)
  {
    // make user interactive card (ENTRY or CHOICE card)

    if ($this->card[$this->number_of_cards-1]["type"] == HAW_HDML_DISPLAY)
    {
      // current card is display card

      // ==> make an entry/choice card out of it
      $this->card[$this->number_of_cards-1]["type"] = $cardtype;

      // append options to the already existing ones
      $this->card[$this->number_of_cards-1]["options"] .= $options;

      // append received content to the already existing one
      $this->card[$this->number_of_cards-1]["display_content"] .= $generic_content;
    }
    else
    {
      // current card is already entry or choice card
      // ==> create new entry/choice card
      // ==> link current card to this new entry/choice card

      $this->card[$this->number_of_cards]["type"] = $cardtype;

      $cardname = sprintf(" name=\"%d\"", $this->number_of_cards+1);
      $this->card[$this->number_of_cards]["options"] .= $cardname;

      if ($this->title)
        $this->card[$this->number_of_cards]["options"] .= " title=\"$this->title\"";

      $this->card[$this->number_of_cards]["options"] .= $options;

      $this->card[$this->number_of_cards]["display_content"] = $generic_content;

      $action = sprintf("<action type=\"accept\" task=\"go\" dest=\"#%d\">\n",
                         $this->number_of_cards+1);
      $this->card[$this->number_of_cards-1]["action"] = $action;

      $this->number_of_cards++;
    }
  }


  function set_final_action($action)
  {
    $this->final_action = $action;
  }


  function create_hdmldeck()
  {
    if (!HAW_DEBUG)
      header("content-type: text/x-hdml");

    if ($this->disable_cache)
      $ttl = " TTL=\"0\"";

    printf("<hdml version=\"3.0\" public=\"true\"%s>\n", $ttl);
    printf("<!-- Generated by %s %s -->\n", HAW_VERSION, HAW_COPYRIGHT);

    // create NODISPLAY card if it's necessary to initialize variables
    if ($this->defaults)
    {
      while (list($d_key, $d_val) = each($this->defaults))
        $vars .= sprintf("%s=%s&amp;", $d_val[name], $d_val[value]);

      // strip terminating '&'
      $vars = substr($vars, 0, strlen($query_string)-5);

      echo "<nodisplay>\n";
      printf("<action type=\"accept\" task=\"go\" dest=\"#1\" vars=\"%s\">\n", $vars);
      echo "</nodisplay>\n";
    }

    // set action of last card
    $this->card[$this->number_of_cards-1]["action"] = $this->final_action;

    // create all cards of card set
    $i = 0;
    while ( $i < $this->number_of_cards )
    {
      if ($this->card[$i]["type"] == HAW_HDML_DISPLAY)
        $cardtype = "display";
      elseif ($this->card[$i]["type"] == HAW_HDML_ENTRY)
        $cardtype = "entry";
      elseif ($this->card[$i]["type"] == HAW_HDML_CHOICE)
        $cardtype = "choice";

      printf("<%s%s>\n", $cardtype, $this->card[$i]["options"]);
      printf("%s", $this->card[$i]["action"]);
      printf("%s", $this->card[$i]["display_content"]);
      printf("</%s>\n", $cardtype);

      $i++;
    }

    echo "</hdml>\n";
  }
};






/**
  This class is the top level class of all HAWHAW classes. Your page should consist
  of exactly one HAW_deck object. For WML browsers one deck with one card will be
  generated. For HDML browser one deck including as much cards as necessary will
  generated. HTML browsers will receive a normal HTML page.
  <p><b>Examples:</b><p>
  $myPage = new HAW_deck();<br>
  $myPage = new HAW_deck("My WAP page");<br>
  $myPage = new HAW_deck("", HAW_ALIGN_CENTER);<br>
  ...<br>
  $myPage->set_bgcolor("blue");<br>
  ...<br>
  $myPage->add_text($myText);<br>
  ...<br>
  $myPage->create_page();
  @memo Top Level Class containing text, images, tables, forms and links.
*/
class HAW_deck
{
  var $title;
  var $alignment;
  var $timeout;
  var $red_url;
  var $disable_cache = false;
  var $ml;
  var $element;
  var $number_of_elements;
  var $number_of_forms;
  var $waphome;
  var $hdmlcardset;

  // display properties for HTML

  // page background properties
  var $bgcolor;
  var $background;

  // display (table) properties
  var $border = 8;
  var $disp_bgcolor = "#FFCC99";
  var $width  = 200;
  var $height = 200;

  // text properties
  var $size;
  var $color;
  var $face = "Arial,Times";

  /**
    Constructor
    @args (string title=HAW_NOTITLE, int alignment=HAW_ALIGN_LEFT)
    @param title (optional)<br>If a string is provided here, it will be displayed
       in the HTML title bar, respectively somewhere on the WAP display. Using a
       title you will normally have to spend one of your few lines on your WAP
       display. Consider that some WAP phones/SDK's don't display the title at all.
    @param alignment (optional)<br>Default is left. You can enter HAW_ALIGN_CENTER
       or HAW_ALIGN_RIGHT to modify the alignment of the whole page.
  */
  function HAW_deck($title=HAW_NOTITLE, $alignment=HAW_ALIGN_LEFT)
  {
    global $HTTP_USER_AGENT;
    global $HTTP_ACCEPT;
    global $HTTP_HOST;
    global $SCRIPT_NAME;

    if ($title != HAW_NOTITLE)
      $this->title = $title;

    $this->alignment = $alignment;
    $this->timeout = 0;
    $this->red_url = "";

    $this->waphome = "http://" . $HTTP_HOST . $SCRIPT_NAME;

    // determine whether HTML, WML or HDML should be generated

    if (HAW_DEBUG == 0)
    {
      // no debugging mode activated

      // check HTTP header for accepted mime types
      if (strstr(strtolower($HTTP_ACCEPT), "text/vnd.wap.wml"))
        $this->ml = HAW_WML;  // create WML
      elseif (strstr(strtolower($HTTP_ACCEPT), "hdml;version=3.0"))
        $this->ml = HAW_HDML; // create HDML
      else
      {
        if (strstr($HTTP_USER_AGENT, "Mozilla") ||
            strstr($HTTP_USER_AGENT, "MSIE") ||
            strstr($HTTP_USER_AGENT, "Explorer"))
          $this->ml = HAW_HTML;   // "normal" WEB surfer: create HTML
        else
          $this->ml = HAW_WML;    // try it with WML
      }
    }
    elseif (HAW_DEBUG == HAW_HTML)
      $this->ml = HAW_HTML;   // debugging mode: HTML
    elseif (HAW_DEBUG == HAW_WML)
      $this->ml = HAW_WML;    // debugging mode: WML
    elseif (HAW_DEBUG == HAW_HDML)
      $this->ml = HAW_HDML;   // debugging mode: HDML

    $this->number_of_elements = 0;
    $this->number_of_forms = 0;
  }


  /**
    Adds a HAW_text object to HAW_deck.
    @args (HAW_text* text_object)
    @param text_object Some HAW_text object.
    @return ---
    @see HAW_text
  */
  function add_text($text)
  {
    if (!is_object($text))
      die("invalid argument in add_text()");

    $this->element[$this->number_of_elements] = $text;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_image object to HAW_deck.
    @args (HAW_image* image_object)
    @param image_object Some HAW_image object.
    @return ---
    @see HAW_image
  */
  function add_image($image)
  {
    if (!is_object($image))
      die("invalid argument in add_image()");

    $this->element[$this->number_of_elements] = $image;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_table object to HAW_deck.
    @args (HAW_table* table_object)
    @param table_object Some HAW_table object.
    @return ---
    @see HAW_table
  */
  function add_table($table)
  {
    if (!is_object($table))
      die("invalid argument in add_table()");

    $this->element[$this->number_of_elements] = $table;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_form object to HAW_deck.
    @args (HAW_form* form_object)
    @param form_object Some HAW_form object.
    @return ---
    @see HAW_form
  */
  function add_form($form)
  {
    if (!is_object($form))
      die("invalid argument in add_form()");

    if ($this->number_of_forms > 0)
      die("only one form per deck allowed!");

    $this->element[$this->number_of_elements] = $form;

    $this->number_of_elements++;
    $this->number_of_forms++;
  }


  /**
    Adds a HAW_link object to HAW_deck.
    @args (HAW_link* link_object)
    @param link_object Some HAW_link object.
    @return ---
    @see HAW_link
  */
  function add_link($link)
  {
    if (!is_object($link))
      die("invalid argument in add_link()");

    $this->element[$this->number_of_elements] = $link;

    $this->number_of_elements++;
  }


  /**
    Redirects automatically after timeout to another URL.<br>
    Note: This feature can not be supported for HDML browsers, due to HDML's missing
    timer functionality. If you intend to serve HDML users, you should consider this
    by creating an additional link to <i>red_url</i>.
    @args (int timeout, string red_url)
    @param timeout Some timeout value in seconds.
    @param red_url Some URL.
    @return ---
  */
  function set_redirection($timeout, $red_url)
  {
    $this->timeout = $timeout;
    $this->red_url = $red_url;
  }


  /**
    Disables deck caching in the users client.<br>
    Note: Use this object function, if you intend to provide changing content under
    the same URL.
    @return ---
  */
  function disable_cache()
  {
    $this->disable_cache = true;
  }


  /**
    Sets the background color for a HTML created page. Has no effect on WML created
    pages.
    @args (string color)
    @param color See HTML specification for possible values (e.g. "#CCFFFF",
      "red", ...).
    @return ---
  */
  function set_bgcolor($bgcolor)
  {
    $this->bgcolor = $bgcolor;
  }


  /**
    Sets a wallpaper for HTML created pages. Has no effect on WML created
    pages.
    @args (string background)
    @param background e.g. "backgrnd.gif"
    @return ---
  */
  function set_background($background)
  {
    $this->background = $background;
  }


  /**
    Sets the thickness of the HTML display frame. Has no effect on WML created pages.
    @args (int border)
    @param border Thickness is pixels (default: 8)
    @return ---
  */
  function set_border($border)
  {
    $this->border = $border;
  }


  /**
    Sets the display background color for a HTML created page. Has no effect on WML
    created pages.
    @args (string disp_bgcolor)
    @param disp_bgcolor See HTML specification for possible values (e.g. "#CCFFFF",
      "red", ...).
    @return ---
  */
  function set_disp_bgcolor($disp_bgcolor)
  {
    $this->disp_bgcolor = $disp_bgcolor;
  }


  /**
    Sets the display width for a HTML created page. Has no effect on WML created
    pages.
    @args (string width)
    @param width See HTML specification for possible values (e.g. "200", "50%", ...).
    @return ---
  */
  function set_width($width)
  {
    $this->width = $width;
  }


  /**
    Sets the display height for a HTML created page. Has no effect on WML created
    pages.
    @args (string height)
    @param height See HTML specification for possible values (e.g. "200", "50%", ...).
    @return ---
  */
  function set_height($height)
  {
    $this->height = $height;
  }


  /**
    Sets the font size for all characters in a HTML created page. Has no effect on
    WML created pages.
    @args (string size)
    @param size See HTML specification for possible values (e.g. "4", "+2", ...).
    @return ---
  */
  function set_size($size)
  {
    $this->size = $size;
  }


  /**
    Sets the color fore all characters in a HTML created page. Has no effect on WML
    created pages.
    @args (string color)
    @param color See HTML specification for possible values (e.g. "#CCFFFF", "red",
       ...).
    @return ---
  */
  function set_color($color)
  {
    $this->color = $color;
  }


  /**
    Sets the font for all characters in a HTML created page. Has no effect on WML
    created pages.
    @args (string face)
    @param face See HTML specification for possible values (e.g. "Avalon",
       "Wide Latin").
    @return ---
  */
  function set_face($face)
  {
    $this->face = $face;
  }


  /**
    Sets the URL of a WAP site, a HTML-browsing user is invited to enter via WAP.
    Has no effect on WML created pages.<br>
    Note:  Below the display of a HTML-created page, a small copyright link to the
    HAWHAW information page will be created automatically by HAWHAW. The information
    page in return invites the visitor to take a look via WAP at your hybrid page.
    Therefore by default your hostname and your refering script will be part of this
    copyright link. You can modify this value, e.g. if your application directs the
    user with get-method queries across different PHP pages, but you want to make
    visible the entry page only.
    @args (string waphome)
    @param waphome Some URL.
    @return ---
  */
  function set_waphome($waphome)
  {
    $this->waphome = $waphome;
  }


  /**
    Creates the page in the according markup language. Depending on the clients
    browser type HTML, WML or HDML code is created.
    @return ---
  */
  function create_page()
  {
    if (HAW_DEBUG)
      header("content-type: text/plain");

    if ($this->ml == HAW_HTML)
    {
      // create HTML page header

      if (!HAW_DEBUG)
        header("content-type: text/html");

      echo "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">\n";
      echo "<html>\n";
      echo "<head>\n";
      echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n";
      printf("<meta name=\"GENERATOR\" content=\"%s %s\">\n",
             HAW_VERSION, HAW_COPYRIGHT);

      if ($this->timeout > 0)
        printf("<meta http-equiv=\"refresh\" content=\"%d; URL=%s\">\n", $this->timeout, $this->red_url);

      if ($this->disable_cache)
      {
        echo "<meta http-equiv=\"Cache-Control\" content=\"must-revalidate\">\n";
        echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\">\n";
        echo "<meta http-equiv=\"Cache-Control\" content=\"max-age=0\">\n";
        echo "<meta http-equiv=\"Expires\" content=\"0\">\n";
      }

      if ($this->bgcolor)
        $bgcolor = " bgcolor=\"" . $this->bgcolor . "\"";

      if ($this->background)
        $background = " background=\"" . $this->background . "\"";

      if ($this->size)
        $size = " size=\"" . $this->size . "\"";

      if ($this->color)
        $color = " color=\"" . $this->color . "\"";

      if ($this->face)
        $face = " face=\"" . $this->face . "\"";

      echo "<title>$this->title</title>\n";
      echo "</head>\n";
      printf("<body%s%s>\n", $bgcolor, $background);
      echo "<center><br>\n";
      printf("<table border=\"%d\" bgcolor=\"%s\" cellpadding=\"8\" width=\"%s\" height=\"%s\">\n",
              $this->border, $this->disp_bgcolor, $this->width, $this->height);
      echo "<tr><td valign=\"top\">\n";
      printf("<font%s%s%s>\n", $size, $color, $face);
    }
    else
    {
      // determine default values for WML and HDML form elements

      while (list($e_key, $e_val) = each($this->element))
      {
        if ($e_val->get_elementtype() == HAW_FORM)
        {
          // one (and only one!) form exists

          $form = $e_val;
          $defaults = $form->get_defaults();
        }
      }

      if ($this->ml == HAW_WML)
      {
        // create WML page header
        if (!HAW_DEBUG)
          header("content-type: text/vnd.wap.wml");

        if ($this->disable_cache)
          header("content-location: hawhawhaw"); // some phantasy name to cache!

        echo "<?xml version=\"1.0\"?>\n";
        echo "<!DOCTYPE wml PUBLIC \"-//WAPFORUM//DTD WML 1.1//EN\" \"http://www.wapforum.org/DTD/wml_1.1.xml\">\n";
        printf("<!-- Generated by %s %s -->\n", HAW_VERSION, HAW_COPYRIGHT);

        echo "<wml>\n";

        if ($this->disable_cache)
        {
          echo "<head>\n";
          echo "<meta http-equiv=\"Cache-Control\" content=\"must-revalidate\" forua=\"true\"/>\n";
          echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\" forua=\"true\"/>\n";
          echo "<meta http-equiv=\"Cache-Control\" content=\"max-age=0\" forua=\"true\"/>\n";
          echo "<meta http-equiv=\"Expires\" content=\"0\" forua=\"true\"/>\n";
          echo "<meta http-equiv=\"Pragma\" content=\"no-cache\" forua=\"true\"/>\n";
          echo "</head>\n";
        }

        if ($this->title)
          $title = " title=\"$this->title\"";

        printf("<card%s>\n", $title);

        if ($defaults)
        {
          // default values exist

          // set variables each time the card is enter in forward direction ...

          echo "<onevent type=\"onenterforward\">\n";
          echo "<refresh>\n";

          // initialize all WML variables with their default values
          while (list($d_key, $d_val) = each($defaults))
            printf("<setvar name=\"%s\" value=\"%s\"/>\n", $d_val[name], $d_val[value]);

          reset($defaults);

          echo "</refresh>\n";
          echo "</onevent>\n";

          // ... and backward direction

          echo "<onevent type=\"onenterbackward\">\n";
          echo "<refresh>\n";

          while (list($d_key, $d_val) = each($defaults))
            printf("<setvar name=\"%s\" value=\"%s\"/>\n", $d_val[name], $d_val[value]);

          echo "</refresh>\n";
          echo "</onevent>\n";
        }

        // set redirection timeout
        if ($this->timeout > 0)
        {
           echo "<onevent type=\"ontimer\">\n";
           printf("<go href=\"%s\"/>\n", HAW_specchar($this->red_url));
           echo "</onevent>\n";
           printf("<timer value=\"%d\"/>\n", $this->timeout*10);
        }

        // define <back> softkey
        echo "<do type=\"prev\" label=\"Back\">\n";
        echo "<prev/>\n";
        echo "</do>\n";
      }
      elseif ($this->ml == HAW_HDML)
      {
        // create HDML card set structure

        $this->hdmlcardset = new HAW_hdmlcardset($this->title, $defaults,
                                                 $this->disable_cache);
      }
    }

    switch ( $this->alignment )
    {
      case HAW_ALIGN_LEFT:
      {
        if ($this->ml == HAW_HTML)
          echo "<div align=\"left\">\n";
        elseif ($this->ml == HAW_WML)
          echo "<p>\n"; // left is default

        break;
      }

      case HAW_ALIGN_CENTER:
      {
        if ($this->ml == HAW_HTML)
          echo "<div align=\"center\">\n";
        elseif ($this->ml == HAW_WML)
          echo "<p align=\"center\">\n";

        break;
      }

      case HAW_ALIGN_RIGHT:
      {
        if ($this->ml == HAW_HTML)
          echo "<div align=\"right\">\n";
        elseif ($this->ml == HAW_WML)
          echo "<p align=\"right\">\n";

        break;
      }

    }

    $i = 0;
    while ( $this->element[$i] )
    {
      $page_element = $this->element[$i];
      switch ($page_element->get_elementtype())
      {
        case HAW_PLAINTEXT:
        case HAW_IMAGE:
        case HAW_TABLE:
        case HAW_FORM:
        case HAW_LINK:
        {
          $element = $this->element[$i];
          $element->create(&$this); // & operator is very important!!!
                                    // without it modifications of object properties
                                    // will not work!!!
          break;
        }
      }

      $i++;
    }

    if ($this->ml == HAW_HTML)
    {
      // create HTML page end
      echo "</font></td></tr></table>\n";

      //  ATTENTION!
      //
      //  DO NOT REMOVE THIS COPYRIGHT LINK!
      //  IF YOU DO SO, YOU ARE VIOLATING THE GNU LICENCE TERMS
      //  OF THIS SOFTWARE! YOU HAVE TO PAY NOTHING FOR THIS
      //  SOFTWARE, SO PLEASE BE SO FAIR TO ACCEPT THE RULES.
      //  IF YOU DON'T, YOUR WEBSITE WILL AT LEAST BE LISTED IN
      //  THE HAWHAW HALL OF SHAME!
      //  PLEASE REFER TO THE LIBRARY HEADER FOR MORE INFORMATION.

      printf("<a href=\"http://www.hawhaw.de/info/index.htm?host=%s\" target=\"_blank\"><font size=-1>WAP optimized by %s (C)<font size=-2><br>Click here for more info</font></font></a>\n",
              $this->waphome, HAW_VERSION);


      echo "</center>\n";
      echo "</div>\n";
      echo "</body>\n";
      echo "</html>\n";
    }
    elseif ($this->ml == HAW_WML)
    {
      // create WML page end
      echo "</p>\n";
      echo "</card>\n";
      echo "</wml>\n";
    }
    elseif ($this->ml == HAW_HDML)
    {
      // create HDML page from hdml card set structure
      $cardset = $this->hdmlcardset;
      $cardset->create_hdmldeck();
    }
  }
};






/**
  This class defines a form with various possible input elements. The input elements
  have to be defined as seperate objects and are linked to the form with a special
  "add" function. One HAW_deck object can contain only one form object.
  <p><b>Examples:</b><p>
  $myPage = new HAW_deck(...);<br>
  ...<br>
  $myForm = new HAW_form("/mynextpage.wml");<br>
  $myText = new HAW_text(...);<br>
  $myForm->add_text($myText);<br>
  $myInput = new HAW_input(...);<br>
  $myForm->add_input($myInput);<br>
  $mySubmit = new HAW_submit(...);<br>
  $myForm->add_submit($mySubmit);<br>
  ...<br>
  $myPage->add_form($myForm);<br>
  ...<br>
  $myPage->create_page();
  @see HAW_text, HAW_image, HAW_table, HAW_input, HAW_radio, HAW_checkbox,
    HAW_hidden, HAW_submit
  @memo Form object containing input fields, radio buttons, checkboxes etc.
*/
class HAW_form
{
  var $url;
  var $element;
  var $number_of_elements;


  /**
    Constructor
    @args (string url)
    @param url Address where the user input is sent to.<br>
      Note: Currently only the GET method is supported.
  */
  function HAW_form($url)
  {
    $this->url = $url;
    $this->number_of_elements = 0;
  }


  /**
    Adds a HAW_text object to HAW_form.
    @args (HAW_text* text_object)
    @param text_object Some HAW_text object.
    @return ---
    @see HAW_text
  */
  function add_text($text)
  {
    if (!is_object($text))
      die("invalid argument in add_text()");

    $this->element[$this->number_of_elements] = $text;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_image object to HAW_form.
    @args (HAW_image* image_object)
    @param image_object Some HAW_image object.
    @return ---
    @see HAW_image
  */
  function add_image($image)
  {
    if (!is_object($image))
      die("invalid argument in add_image()");

    $this->element[$this->number_of_elements] = $image;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_table object to HAW_form.
    @args (HAW_table* table_object)
    @param table_object Some HAW_table object.
    @return ---
    @see HAW_table
  */
  function add_table($table)
  {
    if (!is_object($table))
      die("invalid argument in add_table()");

    $this->element[$this->number_of_elements] = $table;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_input object to HAW_form.
    @args (HAW_input* input_object)
    @param input_object Some HAW_input object.
    @return ---
    @see HAW_input
  */
  function add_input($input)
  {
    if (!is_object($input))
      die("invalid argument in add_input()");

    $this->element[$this->number_of_elements] = $input;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_radio object to HAW_form.
    @args (HAW_radio* radio_object)
    @param radio_object Some HAW_radio object.
    @return ---
    @see HAW_radio
  */
  function add_radio($radio)
  {
    if (!is_object($radio))
      die("invalid argument in add_radio()");

    $this->element[$this->number_of_elements] = $radio;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_checkbox object to HAW_form.
    @args (HAW_checkbox* checkbox_object)
    @param checkbox_object Some HAW_checkbox object.
    @return ---
    @see HAW_checkbox
  */
  function add_checkbox($checkbox)
  {
    if (!is_object($checkbox))
      die("invalid argument in add_checkbox()");

    $this->element[$this->number_of_elements] = $checkbox;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_hidden object to HAW_form.
    @args (HAW_hidden* hidden_object)
    @param hidden_object Some HAW_hidden object.
    @return ---
    @see HAW_hidden
  */
  function add_hidden($hidden)
  {
    if (!is_object($hidden))
      die("invalid argument in add_hidden()");

    $this->element[$this->number_of_elements] = $hidden;

    $this->number_of_elements++;
  }


  /**
    Adds a HAW_submit object to HAW_form.
    @args (HAW_submit* submit_object)
    @param submit_object Some HAW_submit object.
    @return ---
    @see HAW_submit
  */
  function add_submit($submit)
  {
    if (!is_object($submit))
      die("invalid argument in add_submit()");

    $this->element[$this->number_of_elements] = $submit;

    $this->number_of_elements++;
  }

  function get_defaults()
  {
    $i = 0;
    while (list($key, $val) = each($this->element))
    {
      switch ($val->get_elementtype())
      {
        case HAW_CHECKBOX:
        {
          $checkbox = $val;

          if ($checkbox->is_checked())
          {
            $defaults[$i]["name"]  = $checkbox->get_name();
            $defaults[$i]["value"] = $checkbox->get_value();
            $i++;
          }

          break;
        }

        case HAW_RADIO:
        case HAW_HIDDEN:
        {
          $element = $val;

          $defaults[$i]["name"]  = $element->get_name();
          $defaults[$i]["value"] = $element->get_value();
          $i++;

          break;
        }
      }
    }

    return $defaults;
  }

  function get_elementtype()
  {
    return HAW_FORM;
  }

  function create($deck)
  {
    // determine all elements that have to be submitted

    $i = 0;
    while (list($key, $val) = each($this->element))
    {
      switch ($val->get_elementtype())
      {
        case HAW_INPUT:
        case HAW_CHECKBOX:
        case HAW_RADIO:
        case HAW_HIDDEN:
        {
          $element = $val;
          $getvar[$i] = $element->get_name();
          $i++;
        }
      }
    }

    if ($deck->ml == HAW_HTML)
    {
      // start tag of HTML form
      printf("<form action=\"%s\" method=\"get\">\n", $this->url);
    }
      // not necessary in WML and HDML!


    $i = 0;
    while ( $this->element[$i] )
    {
      $form_element = $this->element[$i];
      switch ($form_element->get_elementtype())
      {
        case HAW_PLAINTEXT:
        case HAW_IMAGE:
        case HAW_TABLE:
        case HAW_INPUT:
        case HAW_RADIO:
        case HAW_CHECKBOX:
        case HAW_HIDDEN:
        {
          $form_element->create(&$deck);
          break;
        }

        case HAW_SUBMIT:
        {
          $submit = $this->element[$i];
          $submit->create(&$deck, $getvar, $this->url);
          break;
        }

      }

      $i++;
    }

    if ($deck->ml == HAW_HTML)
    {
      // terminate HTML form
      echo "</form>\n";
    }
  }
};






/**
  This class allows to insert plain text into a HAW_deck, HAW_form or HAW_table object.
  <p><b>Examples:</b><p>
  $myText1 = new HAW_text("Hello WAP!");<br>
  $myText2 = new HAW_text("Welcome to HAWHAW", HAW_TEXTFORMAT_BOLD);<br>
  $myText3 = new HAW_text("Good Morning", HAW_TEXTFORMAT_BOLD | HAW_TEXTFORMAT_BIG);<br>
  <br>
  $myText3->set_br(2);<br>
  @see HAW_deck, HAW_form, HAW_row
  @memo Text object for decks, forms and tables.
*/
class HAW_text
{
  var $text;
  var $attrib;
  var $br;

  /**
    Constructor
    @args (string text, int attribute=HAW_TEXTFORMAT_NORMAL)
    @param text Whatever you want to display
    @param attribute (optional)<br>
      HAW_TEXTFORMAT_NORMAL  (default)<br>
      HAW_TEXTFORMAT_BOLD<br>
      HAW_TEXTFORMAT_UNDERLINE<br>
      HAW_TEXTFORMAT_ITALIC<br>
      HAW_TEXTFORMAT_BIG<br>
      HAW_TEXTFORMAT_SMALL
  */
  function HAW_text($text, $attrib=HAW_TEXTFORMAT_NORMAL)
  {
    $this->text = $text;
    $this->attrib = $attrib;
    $this->br = 1; // default: 1 line break after text
  }

  /**
    Sets the number of line breaks (CRLF) after text. (default: 1)
    @args (int brs)
    @param brs Some number of line breaks.
    @return ---
  */
  function set_br($br)
  {
    if (!is_int($br) || ($br < 0))
      die("invalid argument in set_br()");

    $this->br = $br;
  }

  function get_elementtype()
  {
    return HAW_PLAINTEXT;
  }

  function create($deck)
  {
    if ($deck->ml == HAW_HDML)
    {
      // HDML

      if ($deck->alignment == HAW_ALIGN_CENTER)
        // repeat alignment in HDML for each paragraph
        $deck->hdmlcardset->add_display_content("<center>\n");

      if ($deck->alignment == HAW_ALIGN_RIGHT)
        // repeat alignment in HDML for each paragraph
        $deck->hdmlcardset->add_display_content("<right>\n");

      // print text
      if ($this->text)
      {
        $content = sprintf("%s\n", HAW_specchar($this->text));
        $deck->hdmlcardset->add_display_content($content);
      }

      // create required amount of carriage return's
      for ($i=0; $i < $this->br; $i++)
        $br .= "<br>\n";

      $deck->hdmlcardset->add_display_content($br);
    }
    else
    {
      // HTML or WML

      if ($this->attrib & HAW_TEXTFORMAT_BOLD)
        echo "<b>\n";

      if ($this->attrib & HAW_TEXTFORMAT_UNDERLINE)
        echo "<u>\n";

      if ($this->attrib & HAW_TEXTFORMAT_ITALIC)
        echo "<i>\n";

      if ($this->attrib & HAW_TEXTFORMAT_BIG)
        echo "<big>\n";

      if ($this->attrib & HAW_TEXTFORMAT_SMALL)
        echo "<small>\n";

      // print text
      if ($this->text)
        printf("%s\n", HAW_specchar($this->text));


      if ($this->attrib & HAW_TEXTFORMAT_SMALL)
        echo "</small>\n";

      if ($this->attrib & HAW_TEXTFORMAT_BIG)
        echo "</big>\n";

      if ($this->attrib & HAW_TEXTFORMAT_ITALIC)
        echo "</i>\n";

      if ($this->attrib & HAW_TEXTFORMAT_UNDERLINE)
        echo "</u>\n";

      if ($this->attrib & HAW_TEXTFORMAT_BOLD)
        echo "</b>\n";

      // create required amount of carriage return's
      if ($deck->ml == HAW_HTML)
      {
        // break instruction in HTML
        $br_command = "<br clear=all>\n";
      }
      elseif ($deck->ml == HAW_WML)
      {
        // break instruction in WML
        $br_command = "<br/>\n";
      }
      for ($i=0; $i < $this->br; $i++)
        echo $br_command;
    }
  }
};






/**
  This class allows to insert bitmap images into a HAW_deck, HAW_form or HAW_table
  object.
  <p><b>Examples:</b><p>
  $myImage1 = new HAW_image("my_image.wbmp", "my_image.gif", ":-)");<br>
  $myImage2 = new HAW_image("my_image.wbmp", "my_image.gif", ":-)", "my_image.bmp");<br>
  $myImage2->set_br(1);<br>
  @see HAW_deck, HAW_form, HAW_row
  @memo Bitmap image object for decks, forms and tables.
*/
class HAW_image
{
  var $src_wbmp;
  var $src_html;
  var $alt;
  var $src_bmp;
  var $br;

  /**
    Constructor
    @args (string src_wbmp, string src_html, string alt, string src_bmp="")
    @param src_wbmp Your bitmap in WAP-conform .wbmp format.
    @param src_html Your bitmap in .gif, .jpg or any other HTML compatible format.
    @param alt Alternative text for your bitmap. Will be displayed if the client can
       display none of your graphic formats.
    @param src_bmp (optional)<br>your bitmap in monochrome .bmp format. If the
       browser signals in the HTTP request header, that he's only able to display
       image/bmp and not image/vnd.wap.wbmp (e.g. the UPSim 3.2 does so), this image
       will be sent backwards.
  */
  function HAW_image($src_wbmp, $src_html, $alt, $src_bmp="")
  {
    $this->src_wbmp = $src_wbmp;
    $this->src_html = $src_html;
    $this->alt      = $alt;
    $this->src_bmp  = $src_bmp;
    $this->br = 0; // default: no line break after image
  }

  /**
    Sets the number of line breaks (CRLF) after the image. (default: 0)
    @args (int brs)
    @param brs Some number of line breaks.
    @return ---
  */
  function set_br($br)
  {
    if (!is_int($br) || ($br < 0))
      die("invalid argument in set_br()");

    $this->br = $br;
  }

  function get_elementtype()
  {
    return HAW_IMAGE;
  }

  function create($deck)
  {
    global $HTTP_ACCEPT;

    if ($deck->ml == HAW_HDML)
    {
      // HDML

      if ($deck->alignment == HAW_ALIGN_CENTER)
        // repeat alignment in HDML for each paragraph
        $deck->hdmlcardset->add_display_content("<center>\n");

      if ($deck->alignment == HAW_ALIGN_RIGHT)
        // repeat alignment in HDML for each paragraph
        $deck->hdmlcardset->add_display_content("<right>\n");

      $content = sprintf("<img src=\"%s\" alt=\"%s\">\n",
                         $this->src_bmp, $this->alt);

      $deck->hdmlcardset->add_display_content($content);

      // create required amount of carriage return's
      for ($i=0; $i < $this->br; $i++)
        $br .= "<br>\n";

      $deck->hdmlcardset->add_display_content($br);
    }
    else
    {
      // HTML or WML

      if ($deck->ml == HAW_HTML)
      {
        printf("<img src=\"%s\" alt=\"%s\" align=\"left\">\n",
               $this->src_html, $this->alt);

        // break instruction in HTML
        $br_command = "<br clear=all>\n";
      }
      else
      {
        if (strstr(strtolower($HTTP_ACCEPT), "image/vnd.wap.wbmp"))
          // user agent is able to display .wbmp image
          printf("<img src=\"%s\" alt=\"%s\"/>\n", $this->src_wbmp, $this->alt);

        elseif (strstr(strtolower($HTTP_ACCEPT), "image/bmp") && $this->src_bmp)
          // user agent is able to display .bmp and .bmp image is available
          printf("<img src=\"%s\" alt=\"%s\"/>\n", $this->src_bmp, $this->alt);

        else
          // hope that the user agent makes the best of it!
          printf("<img src=\"%s\" alt=\"%s\"/>\n", $this->src_wbmp, $this->alt);

        // break instruction in WML
        $br_command = "<br/>\n";
      }

      // create required amount of carriage return's
      for ($i=0; $i < $this->br; $i++)
        echo $br_command;
    }
  }
};






/**
  This class allows to insert tables into a HAW_deck or HAW_form object.
  <p>Note: Not all WAP clients are able to display tables properly! HDML is not
  supporting tables at all. For HDML users the table's content will be generated
  column-by-column, respectively row-by-row, where each table cell will result in
  one separate line on the display.
  <p><b>Examples:</b><p>
  ...<br>
  $myTable = new HAW_table();<br>
  $row1 = new HAW_row();<br>
  $row1->add_column($image1);<br>
  $row1->add_column($text1);<br>
  $myTable->add_row($row1);<br>
  $row2 = new HAW_row();<br>
  $row2->add_column($image2);<br>
  $row2->add_column($text2);<br>
  $myTable->add_row($row2);<br>
  $myDeck->add_table($myTable);<br>
  ...
  @see HAW_deck, HAW_form, HAW_row
  @memo Table object for decks and forms.
*/
class HAW_table
{
  var $row;
  var $number_of_rows;

  /**
    Constructor
  */
  function HAW_table()
  {
    $this->number_of_rows = 0;
  }


  /**
    Adds a HAW_row object to HAW_table.
    @args (HAW_row* row_object)
    @param row_object Some HAW_row object.
    @return ---
  */
  function add_row($row)
  {
    if (!is_object($row))
      die("invalid argument in add_row()");

    $this->row[$this->number_of_rows] = $row;

    $this->number_of_rows++;
  }

  function get_elementtype()
  {
    return HAW_TABLE;
  }

  function create($deck)
  {
    // HDML does not support tables ==> skip all table tags for HDML

    if ($deck->ml == HAW_HTML)
    {
      // HTML
      echo "<table border>\n";
    }
    elseif ($deck->ml == HAW_WML)
    {
      // WML

      // evaluate maximum number of columns in table

      $max_columns = 0;
      for ($i = 0; $i < $this->number_of_rows; $i++)
      {
        $row = $this->row[$i];
        $columns = $row->get_number_of_columns();

        if ($columns > $max_columns)
          $max_columns = $columns;
      }

      printf("<table columns=\"%d\">\n", $max_columns);
    }

    for ($i = 0; $i < $this->number_of_rows; $i++)
    {
      $row = $this->row[$i];
      $row->create(&$deck);
    }

    //terminate table
    if ($deck->ml == HAW_HTML)
    {
      // make new line in HTML
      echo "</table><br clear=all>\n";
    }
    elseif ($deck->ml == HAW_WML)
    {
      // make new line in WML
      echo "</table><br/>\n";
    }
  }
};






/**
  This class defines the rows, a HAW_table object consists of.
  <p><b>Examples:</b><p>
  ...<br>
  $image1 = new HAW_image("my_image.wbmp", "my_image.gif", ":-)");<br>
  $text1 = new HAW_text("my text");<br>
  $row1 = new HAW_row();<br>
  $row1->add_column($image1);<br>
  $row1->add_column();<br>
  $row1->add_column($text1);<br>
  ...
  @see HAW_table, HAW_text, HAW_image
  @memo Table row object.
*/
class HAW_row
{
  var $column;
  var $number_of_columns;

  /**
    Constructor
  */
  function HAW_row()
  {
    $this->number_of_columns = 0;
  }


  /**
    Adds a cell element to a HAW_row object.
    @args (object* cell_element=NULL)
    @param cell_element (optional)<br>Can be a HAW_text object, a HAW_image object
      or the NULL pointer (default). The latter results in an empty cell element.
    @return ---
  */
  function add_column($cell_element=NULL)
  {
    $this->column[$this->number_of_columns] = $cell_element;

    if (is_object($cell_element))
    {
      if (($cell_element->get_elementtype() != HAW_PLAINTEXT) &&
          ($cell_element->get_elementtype() != HAW_IMAGE))
        die("invalid argument in add_column()");
    }

    $this->number_of_columns++;
  }

  function get_number_of_columns()
  {
     return $this->number_of_columns;
  }

  function create($deck)
  {
    // HDML does not support tables ==> skip all table tags for HDML

    if ($deck->ml != HAW_HDML)
      echo "<tr>\n";  // start of row

    for ($i = 0; $i < $this->number_of_columns; $i++)
    {
      if ($deck->ml != HAW_HDML)
        echo "<td>\n";  // start of column

      // call create function for each cellelement that is a HAWHAW object
      $column = $this->column[$i];
      if (is_object($column))
        $column->create(&$deck);

      if ($deck->ml != HAW_HDML)
        echo "</td>\n";  // end of column
    }

    if ($deck->ml != HAW_HDML)
      echo "</tr>\n";  // end of row
  }
};






/**
  This class provides a text input area in a HAW_form object.
  <p><b>Examples:</b><p>
  $myInput1 = new HAW_input("cid", "", "Customer ID");<br>
  <br>
  $myInput2 = new HAW_input("cid", "", "Customer ID", "*N");<br>
  $myInput2->set_size(6);<br>
  $myInput2->set_maxlength(6);<br>
  <br>
  $myInput3 = new HAW_input("pw", "", "Password", "*N");<br>
  $myInput3->set_size(8);<br>
  $myInput3->set_maxlength(8);<br>
  $myInput3->set_type(HAW_INPUT_PASSWORD);<br>
  $myInput3->display_format_in_HTML(true);
  @see HAW_form
  @memo Text input object for forms
*/
class HAW_input
{
  var $name;
  var $value;
  var $label;
  var $size;
  var $maxlength;
  var $type;
  var $format;
  var $display_format_in_HTML;

  /**
    Constructor
    @args (string name, string value, string label, string format="*M")
    @param name Variable in which the input is sent to the destination URL.
    @param value Initial value that will be presented in the input area.
    @param label Describes your input area on the surfer's screen/display.
    @param format (optional)<br>Input format code according to the WAP standard.
       Allows the WAP user client e.g. to input only digits and no characters. On a
       HTML generated page this format has no significance.
  */
  function HAW_input($name, $value, $label, $format="*M")
  {
    $this->name   = $name;
    $this->value  = $value;
    $this->label  = $label;
    $this->format = $format;
    $this->type   = HAW_INPUT_TEXT;
    $this->display_format_in_HTML = false;
  }

  /**
    Set size of the input area.<br>Note: Will be ignored in case of HDML output.
    @args (int size)
    @param size Number of characters fitting into the input area.
    @return ---
  */
  function set_size($size)
  {
    $this->size = $size;
  }

  /**
    Set maximum of allowed characters in the input area.<br>
    Note: Will be ignored in case of HDML output.
    @args (int maxlength)
    @param maxlength Maximum number of characters the user can enter.
    @return ---
  */
  function set_maxlength($maxlength)
  {
    $this->maxlength = $maxlength;
  }

  /**
    Set input type
    @args (int type)
    @param type Allowed values: HAW_INPUT_TEXT (default) or HAW_INPUT_PASSWORD.
    @return ---
  */
  function set_type($type)
  {
    $this->type = $type;
  }

  /**
    If you set this true, you can see next to your HTML text input area the format
    code which would have been sent to a WAP user client.
    @args (boolean display_format_in_HTML)
    @param display_format_in_HTML Allowed values: true (default) or false.
    @return ---
  */
  function display_format_in_HTML($display_format_in_HTML)
  {
    $this->display_format_in_HTML = $display_format_in_HTML;
  }

  function get_name()
  {
    return $this->name;
  }

  function get_value()
  {
    return $this->value;
  }

  function get_label()
  {
    return $this->label;
  }

  function get_size()
  {
    return $this->size;
  }

  function get_maxlength()
  {
    return $this->maxlength;
  }

  function get_type()
  {
    return $this->type;
  }

  function get_format()
  {
    return $this->format;
  }

  function display_format_in_HTML()
  {
    return $this->display_format_in_HTML;
  }

  function get_elementtype()
  {
    return HAW_INPUT;
  }

  function create($deck)
  {
    if ($this->type == HAW_INPUT_PASSWORD)
      $type = "type=\"password\"";
    else
      $type = "type=\"text\"";

    if ($this->size)
      $size = sprintf("size=\"%d\"", $this->size);

    if ($this->maxlength)
      $maxlength = sprintf("maxlength=\"%d\"", $this->maxlength);

    if ($deck->ml == HAW_HTML)
    {
      // create HTML input
      printf("%s: <input %s name=\"%s\" value=\"%s\" %s %s>",
              HAW_specchar($this->label), $type, $this->name,
              $this->value, $size, $maxlength);

      // show format information to be applied by a real WAP client
      // (has no effect in HTML)
      if ($this->display_format_in_HTML)
        printf("%s<br>\n", $this->format);
      else
        echo "<br>\n";
    }
    elseif ($deck->ml == HAW_WML)
    {
      // create WML input
      printf("%s:<input format=\"%s\" %s name=\"%s\" value=\"%s\" %s %s/>\n",
              HAW_specchar($this->label), $this->format, $type, $this->name,
              $this->value, $size, $maxlength);
    }
    elseif ($deck->ml == HAW_HDML)
    {
      // create HDML input

      $options  = " format=\"$this->format\"";
      $options .= " key=\"$this->name\"";

      if ($this->type == HAW_INPUT_PASSWORD)
        $options .= " NOECHO=\"true\"";

      if ($deck->alignment == HAW_ALIGN_CENTER)
        $display_content = "<center>\n";
      elseif ($deck->alignment == HAW_ALIGN_RIGHT)
        $display_content = "<right>\n";

      $display_content .= HAW_specchar($this->label);
      $display_content .= ":\n";

      // make user interactive entry card
      $deck->hdmlcardset->make_ui_card($options, $display_content, HAW_HDML_ENTRY);
    }
  }
};






/**
  This class provides a radio button element in a HAW_form object.
  <p><b>Examples:</b><p>
  $myRadio = new HAW_radio("country");<br>
  $myRadio->add_button("Finland", "F");<br>
  $myRadio->add_button("Germany", "G", HAW_CHECKED);<br>
  $myRadio->add_button("Sweden", "S");
  @see HAW_form
  @memo Radio button object for forms
*/
class HAW_radio
{
  var $name;
  var $value;
  var $buttons;
  var $number_of_buttons;

  /**
    Constructor
    @args (string name)
    @param name Variable in which the information about the pressed button is sent to
       the destination URL.
  */
  function HAW_radio($name)
  {
    $this->name  = $name;
    $this->number_of_buttons = 0;
  }

  function get_name()
  {
    return $this->name;
  }

  function get_value()
  {
    return $this->value;
  }

  /**
    Adds one radio button to a HAW_radio object.
    @args (string label, string value, int is_checked=HAW_NOTCHECKED)
    @param label Describes the radiobutton on the surfer's screen/display.
    @param value Value sent in the "name" variable, if this button is selected.
    @param is_checked (optional)<br>Allowed values are HAW_CHECKED or HAW_NOT_CHECKED
       (default).<br>Note: Setting to "checked" will overwrite previous "checked"
       radiobuttons of this HAW_radio object.
    @return ---
  */
  function add_button($label, $value, $is_checked=HAW_NOTCHECKED)
  {
    if (!$label || !$value)
      die("invalid argument in add_button()");

    $this->buttons[$this->number_of_buttons]["label"] = $label;
    $this->buttons[$this->number_of_buttons]["value"] = $value;

    if (!$this->value || ($is_checked == HAW_CHECKED))
      $this->value = $value;

    $this->number_of_buttons++;
  }

  function get_elementtype()
  {
    return HAW_RADIO;
  }

  function create($deck)
  {
    if ($deck->ml == HAW_HTML)
    {
      // create HTML radio

      while (list($key, $val) = each($this->buttons))
      {
        if ($val["value"] == $this->value)
          $state = "checked";
        else
          $state = "";

        printf("<input type=\"radio\" name=\"%s\" %s value=\"%s\"> %s<br>\n",
                $this->name, $state, $val["value"], HAW_specchar($val["label"]));
      }
    }
    elseif ($deck->ml == HAW_WML)
    {
      // create WML radio

      printf("<select name=\"%s\">\n", $this->name);

      while (list($key, $val) = each($this->buttons))
      {
        printf("<option value=\"%s\">%s</option>\n",
                $val["value"], HAW_specchar($val["label"]));
      }

      echo "</select>\n";
    }
    elseif ($deck->ml == HAW_HDML)
    {
      // create HDML radio

      $options = " key=\"$this->name\"";

      while (list($key, $val) = each($this->buttons))
      {
        // create one <ce> statement for each button
        $ce_area .= sprintf("<ce value=\"%s\">%s\n",
                            $val["value"], HAW_specchar($val["label"]));
      }

      // make user interactive choice card
      $deck->hdmlcardset->make_ui_card($options, $ce_area, HAW_HDML_CHOICE);
    }
  }
};






/**
  This class provides a single checkbox element in a HAW_form object.
  <p><b>Examples:</b><p>
  $myCheckbox = new HAW_checkbox("agmt", "yes", "I agree");<br>
  $myCheckbox = new HAW_checkbox("agmt", "yes", "I agree", HAW_NOTCHECKED);<br>
  $myCheckbox = new HAW_checkbox("agmt", "yes", "I agree", HAW_CHECKED);<br>
  <br>
  Note: The first and the second example are identical.
  @see HAW_form
  @memo Checkbox object for forms
*/
class HAW_checkbox
{
  var $name;
  var $value;
  var $state;

  /**
    Constructor
    @args (string name, string value, string label, int state=HAW_NOTCHECKED)
    @param name Variable in which "value" sent to the destination URL, in case that
       the box is checked.
    @param value See name.
    @param label Describes the checkbox on the surfer's screen/display.
    @param state (optional)<br>Allowed values are HAW_CHECKED or HAW_NOTCHECKED
       (default).
  */
  function HAW_checkbox($name, $value, $label, $state=HAW_NOTCHECKED)
  {
    $this->name  = $name;
    $this->value = $value;
    $this->label = $label;
    $this->state = $state;
  }

  function is_checked()
  {
    return $this->state;
  }

  function get_name()
  {
    return $this->name;
  }

  function get_value()
  {
    return $this->value;
  }

  function get_label()
  {
    return $this->label;
  }

  function get_elementtype()
  {
    return HAW_CHECKBOX;
  }

  function create($deck)
  {
    if ($deck->ml == HAW_HTML)
    {
      // create HTML checkbox

      if ($this->is_checked())
        $state = "checked";

      printf("<input type=\"checkbox\" name=\"%s\" %s value=\"%s\"> %s<br>\n",
              $this->name, $state, $this->value, HAW_specchar($this->label));
    }
    elseif ($deck->ml == HAW_WML)
    {
      // create WML checkbox
      printf("<select name=\"%s\" multiple=\"true\">\n", $this->name);
      printf("<option value=\"%s\">%s</option>\n",
             $this->value, HAW_specchar($this->label));
      printf("</select>\n");
    }
    elseif ($deck->ml == HAW_HDML)
    {
      // create HDML checkbox
      // HDML does not support the multiple option feature!
      // ==> trick: simulate checkbox by creating radio buttons [x] and [ ]

      $options = " key=\"$this->name\"";

      // create label above the radio buttons
      $cb = sprintf("%s\n", HAW_specchar($this->label));

      // create "checked" radio button
      $cb .= sprintf("<ce value=\"%s\">[x]\n", $this->value);

      // create "not checked" radio button
      $cb .= "<ce value=\"\">[ ]\n";

      // make user interactive choice card
      $deck->hdmlcardset->make_ui_card($options, $cb, HAW_HDML_CHOICE);
    }
  }
};






/**
  This class provides a "hidden" element in a HAW_form object.
  <p><b>Examples:</b><p>
  $myHiddenElement = new HAW_hidden("internal_reference", "08154711");
  @see HAW_form
  @memo Hidden object for forms
*/
class HAW_hidden
{
  var $name;
  var $value;

  /**
    Constructor
    @args (string name, string value)
    @param name Variable in which "value" sent to the destination URL.
    @param value See name.
  */
  function HAW_hidden($name, $value)
  {
    $this->name = $name;
    $this->value = $value;
  }

  function get_name()
  {
    return $this->name;
  }

  function get_value()
  {
    return $this->value;
  }

  function get_elementtype()
  {
    return HAW_HIDDEN;
  }

  function create($deck)
  {
    if ($deck->ml == HAW_HTML)
    {
      // create hidden HTML field

      printf("<input type=\"hidden\" name=\"%s\" value=\"%s\">\n",
              $this->name, $this->value);
    }
      // not necessary in WML!
  }
};






/**
  This class provides a submit button in a HAW_form object.
  <p><b>Examples:</b><p>
  $mySubmit = new HAW_submit("Submit");<br>
  $mySubmit = new HAW_submit("Submit", "user_pressed");
  @see HAW_form
  @memo Submit object for forms
*/
class HAW_submit
{
  var $label;
  var $name;

  /**
    Constructor
    @args (string label, string name="")
    @param label What's written on the button.
    @param name (optional)<br>
       Variable in which "label" is sent to the destination URL.
  */
  function HAW_submit($label, $name="")
  {
    $this->label = $label;
    $this->name = $name;
  }

  function get_name()
  {
    return $this->name;
  }

  function get_label()
  {
    return $this->label;
  }

  function get_elementtype()
  {
    return HAW_SUBMIT;
  }

  function create($deck, $getvar, $url)
  {
    if ($deck->ml == HAW_HTML)
    {
      // create submit button in HTML

      if ($this->name != "")
        $name = "name=\"" . "$this->name" ."\"";

      printf("<input type=\"submit\" %s value=\"%s\"><br>\n", $name, $this->label);

    }
    else
    {
      // determine querystring for both WML and HDML

      while (list($key, $val) = each($getvar))
        $query_string .= $val . "=$(" . $val . ")&amp;";

      if ($this->name != "")
        $query_string .= $this->name . "=" . $this->label;

      if (substr($query_string, -5) == "&amp;")
        $query_string = substr($query_string, 0, strlen($query_string)-5);

      if ($deck->ml == HAW_WML)
      {
        // create <do type="accept"> sequence in WML

        printf("<do type=\"accept\" label=\"%s\">\n", $this->label);
        printf("<go href=\"%s?%s\">\n", $url, $query_string);

        echo "</go>\n";
        echo "</do>\n";
      }
      elseif ($deck->ml == HAW_HDML)
      {
        // store info for final card in HDML card set

        $action = sprintf("<action type=\"accept\" label=\"%s\" task=\"go\" dest=\"%s?%s\">\n",
                           $this->label, $url, $query_string);
        $deck->hdmlcardset->set_final_action($action);
      }
    }
  }
};






/**
  This class provides a link in a HAW_deck object.
  <p><b>Examples:</b><p>
  $myPage = new HAW_deck(...);<br>
  ...<br>
  $myLink = new HAW_link("Continue","/mynextpage.wml");<br>
  $myPage->add_link($myLink);
  @see HAW_deck
  @memo Link object for decks
*/
class HAW_link
{
  var $label;
  var $url;

  /**
    Constructor
    @args (string label, string url)
    @param label Describes the link on the surfer's screen/display.
    @param url Next destination address.
  */
  function HAW_link($label, $url)
  {
    $this->label = $label;
    $this->url = $url;
  }

  function get_url()
  {
    return $this->url;
  }

  function get_label()
  {
    return $this->label;
  }

  function get_elementtype()
  {
    return HAW_LINK;
  }

  function create($deck)
  {
    if ($deck->ml == HAW_HTML)
      // create link in HTML
      printf("<a href=\"%s\">%s</a><br clear=all>\n",
             $this->url, HAW_specchar($this->label));

    elseif ($deck->ml == HAW_WML)
      // create link in WML
      printf("<a href=\"%s\">%s</a><br/>\n", HAW_specchar($this->url), HAW_specchar($this->label));

    elseif ($deck->ml == HAW_HDML)
    {
      // create link in HDML

      $content = sprintf("<a task=\"go\" dest=\"%s\">%s</a><br>\n",
                          HAW_specchar($this->url), HAW_specchar($this->label));

      $deck->hdmlcardset->add_display_content($content);
    }

  }
};

} // end if defined

?>
