<?php
/**
 * Copyright 2004 The Apache Software Foundation.
 *
 * This software is published under the terms of the Apache Software
 * License version 2.0, a copy of which has been included with this
 * distribution in the LICENSE file.
 *
 * @package log4php
 * @subpackage layouts
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');

if (!defined('LOG4PHP_LINE_SEP')) {
    if (substr(php_uname(), 0, 7) == "Windows") { 
        define('LOG4PHP_LINE_SEP', "\r\n");
    } else {
        /**
         * @ignore
         */
        define('LOG4PHP_LINE_SEP', "\n");
    }
}

 
/**
 */
require_once(LOG4PHP_DIR . '/LoggerLayout.php');

/**
 * A simple layout.
 *
 * Returns the log statement in a format consisting of the
 * <b>level</b>, followed by " - " and then the <b>message</b>. 
 * For example, 
 * <samp> INFO - "A message" </samp>
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage layouts
 */  
class LoggerLayoutSimple extends LoggerLayout {
    
    /**
     * Constructor
     */
    function LoggerLayoutSimple()
    {
        return;
    }

    function activateOptions() 
    {
        return;
    }

    /**
     * Returns the log statement in a format consisting of the
     * <b>level</b>, followed by " - " and then the
     * <b>message</b>. For example, 
     * <samp> INFO - "A message" </samp>
     *
     * @param LoggerLoggingEvent $event
     * @return string
     */
    function format($event)
    {
        $level = $event->getLevel();
        return $level->toString() . ' - ' . $event->getRenderedMessage(). LOG4PHP_LINE_SEP;
    }
}
?>
