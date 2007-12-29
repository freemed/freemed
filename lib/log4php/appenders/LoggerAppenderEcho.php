<?php
/**
 * Copyright 2004 The Apache Software Foundation.
 *
 * This software is published under the terms of the Apache Software
 * License version 2.0, a copy of which has been included with this
 * distribution in the LICENSE file.
 *
 * @package log4php
 * @subpackage appenders
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 */
require_once(LOG4PHP_DIR . '/LoggerAppenderSkeleton.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * LoggerAppenderEcho uses {@link PHP_MANUAL#echo echo} function to output events. 
 * 
 * <p>This appender requires a layout.</p>  
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage appenders
 */
class LoggerAppenderEcho extends LoggerAppenderSkeleton {

    /**
     * @access private 
     */
    var $requiresLayout = true;

    /**
     * @var boolean used internally to mark first append 
     * @access private 
     */
    var $firstAppend    = true;
    
    function activateOptions()
    {
        $this->closed = false;
        return;
    }
    
    function close()
    {
        if (!$this->firstAppend)
            echo $this->layout->getFooter();
        $this->closed = true;    
    }

    function append($event)
    {
        LoggerLog::debug("LoggerAppenderEcho::append()");
        
        if ($this->layout !== null) {
            if ($this->firstAppend) {
                echo $this->layout->getHeader();
                $this->firstAppend = false;
            }
            echo $this->layout->format($event);
        } 
    }
}

?>
