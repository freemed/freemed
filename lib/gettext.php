<?php
/**
 * $Id$
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Alternate to the system's built-in gettext.
 * relies on .po files (can't read .mo easily).
 * Uses the session for caching (speed increase)
 * Possible use in other PHP scripts?  The only SM-specific thing is
 *   $fm_language, I think
 */

global $gettext_php_domain, $gettext_php_dir, $gettext_php_loaded,
 $gettext_php_translateStrings, $gettext_php_loaded_language,
 $gettext_php_short_circuit;

if (! isset($gettext_php_loaded)) {
    $gettext_php_loaded = false;
    session_register('gettext_php_loaded');
}
if (! isset($gettext_php_domain)) {
    $gettext_php_domain = '';
    session_register('gettext_php_domain');
}
if (! isset($gettext_php_dir)) {
    $gettext_php_dir = '';
    session_register('gettext_php_dir');
}
if (! isset($gettext_php_translateStrings)) {
    $gettext_php_translateStrings = array();
    session_register('gettext_php_translateStrings');
}
if (! isset($gettext_php_loaded_language)) {
    $gettext_php_loaded_language = '';
    session_register('gettext_php_loaded_language');
}
if (! isset($gettext_php_short_circuit)) {
    $gettext_php_short_circuit = false;
    session_register('gettext_php_short_circuit');
}

function gettext_php_load_strings() {
    global $language, $gettext_php_translateStrings,
        $gettext_php_domain, $gettext_php_dir, $gettext_php_loaded,
        $gettext_php_loaded_language, $gettext_php_short_circuit;
    
    /*
     * $language gives 'en' for English, 'de' for German,
     * etc.  I didn't wanna use getenv or similar, but you easily could
     * change my code to do that. 
     */
    
    $gettext_php_translateStrings = array();
    
    $gettext_php_short_circuit = false;  /* initialization */
    
    $filename = $gettext_php_dir;
    if (substr($filename, -1) != '/')
        $filename .= '/';
    $filename .= $language . '/LC_MESSAGES/' . 
        $gettext_php_domain . '.po';
    
    $file = @fopen($filename, 'r');
    if ($file == false) {
        /* Uh-ho -- we can't load the file.  Just fake it.  :-)
           This is also for English, which doesn't use translations */
        $gettext_php_loaded = true;
        $gettext_php_loaded_language = $language;
        /* Avoid fuzzy matching when we didn't load strings */
        $gettext_php_short_circuit = true;  
        return;
    }
    
    $key = '';
    $SkipRead = false;
    while (! feof($file)) {
        if (! $SkipRead) {
            $line = trim(fgets($file, 4096));
        } else {
            $SkipRead = false;
        }
        
        if (ereg('^msgid "(.*)"$', $line, $match)) {
            if ($match[1] == '') {
                /*
                 * Potential multi-line
                 * msgid ""
                 * "string string "
                 * "string string"
                 */
                $key = '';
                $line = trim(fgets($file, 4096));
                while (ereg('^[ ]*"(.*)"[ ]*$', $line, $match)) {
                    $key .= $match[1];
                    $line = trim(fgets($file, 4096));
                }
                $SkipRead = true;
            } else {
                /* msgid "string string" */
                $key = $match[1];
            }
        } elseif (ereg('^msgstr "(.*)"$', $line, $match)) {
            if ($match[1] == '') {
                /*
                 * Potential multi-line
                 * msgstr ""
                 * "string string "
                 * "string string"
                 */
                $gettext_php_translateStrings[$key] = '';
                $line = trim(fgets($file, 4096));
                while (ereg('^[ ]*"(.*)"[ ]*$', $line, $match)) {
                    $gettext_php_translateStrings[$key] .= $match[1];
                    $line = trim(fgets($file, 4096));
                }
                $SkipRead = true;
            } else {
                /* msgstr "string string" */
                $gettext_php_translateStrings[$key] = $match[1];
            }
            $gettext_php_translateStrings[$key] =
                stripslashes($gettext_php_translateStrings[$key]);
            /* If there is no translation, just use the untranslated string */
            if ($gettext_php_translateStrings[$key] == '') {
                $gettext_php_translateStrings[$key] = $key;
            }
            $key = '';
        }
    }
    fclose($file);
    
    $gettext_php_loaded = true;
    $gettext_php_loaded_language = $language;
}

function _($str) {
    global $gettext_php_loaded, $gettext_php_translateStrings, 
        $language, $gettext_php_loaded_language,
        $gettext_php_short_circuit;
    
    if (! $gettext_php_loaded || 
        $gettext_php_loaded_language != $language) {
        gettext_php_load_strings();
    }
    
    /* Try finding the exact string */
    if (isset($gettext_php_translateStrings[$str])) {
        return $gettext_php_translateStrings[$str];
    }
    
    /* See if we should short-circuit */
    if ($gettext_php_short_circuit) {
        $gettext_php_translateStrings[$str] = $str;
        return $str;
    }
    
    /* Look for a string that is very close to the one we want
       Very computationally expensive */
    $oldPercent = 0;
    $oldStr = '';
    $newPercent = 0;
    foreach ($gettext_php_translateStrings as $k => $v) {
        similar_text($str, $k, $newPercent);
        if ($newPercent > $oldPercent) {
            $oldStr = $v;
            $oldPercent = $newPercent;
        }
    }
    /* Require 80% match or better
       Adjust to suit your needs */
    if ($oldPercent > 80) {
        /* Remember this so we don't need to search again */
        $gettext_php_translateStrings[$str] = $oldStr;
        return $oldStr;
    }
    
    /* Remember this so we don't need to search again */
    $gettext_php_translateStrings[$str] = $str;
    return $str;
}

function bindtextdomain($name, $dir) {
    global $gettext_php_domain, $gettext_php_dir, $gettext_php_loaded;
    
    if ($gettext_php_domain != $name) {
        $gettext_php_domain = $name;
        $gettext_php_loaded = false;
    }
    if ($gettext_php_dir != $dir) {
        $gettext_php_dir = $dir;
        $gettext_php_loaded = false;
    }
    
    return $dir;
}

function textdomain($name = false) {
    global $gettext_php_domain, $gettext_php_loaded;
    
    if ($name != false && $gettext_php_domain != $name) {
        $gettext_php_domain = $name;
        $gettext_php_loaded = false;
    }
    return $gettext_php_domain;
}

?>
