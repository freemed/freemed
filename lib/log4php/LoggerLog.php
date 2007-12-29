<?php
/**
 * Copyright 2004 The Apache Software Foundation.
 *
 * This software is published under the terms of the Apache Software
 * License version 2.0, a copy of which has been included with this
 * distribution in the LICENSE file.
 * 
 * @package log4php
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__)); 

/**
 * Helper class for internal logging
 *
 * <p>It uses php {@link PHP_MANUAL#trigger_error trigger_error()} function
 * to output messages.</p>
 * <p>You need to recode methods to output messages in a different way.</p> 
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 */
class LoggerLog {

        protected static $debug = false;

    /**
     * Log if debug is enabled.
     *
     * Log using php {@link PHP_MANUAL#trigger_error trigger_error()} function 
     * with E_USER_NOTICE level by default.
     *
     * @param string $message log message
     * @param integer $errLevel level to log
     * @static
     */
    public static function log($message, $errLevel = E_USER_NOTICE)
    {
        if (LoggerLog::internalDebugging())
            trigger_error($message, $errLevel);
    }
    
    public static function internalDebugging($value = null)
    {
        if (is_bool($value))
            self::$debug = $value;
        return self::$debug;
    }
    
    /**
     * Report a debug message. 
     *
     * @param string $message log message
     * @static
     * @since 0.3
     */
    public static function debug($message)
    {
        LoggerLog::log($message, E_USER_NOTICE);
    }
    
    /**
     * Report an error message. 
     *
     * @param string $message log message
     * @static
     * @since 0.3
     */
    public static function error($message)
    {
        trigger_error($message, E_USER_ERROR);
    }
    
    /**
     * Report a warning message. 
     *
     * @param string $message log message
     * @static
     * @since 0.3
     */
    public static function warn($message)
    {
        trigger_error($message, E_USER_WARNING);
    }

}
?>
