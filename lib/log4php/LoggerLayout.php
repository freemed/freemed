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
 * Extend this abstract class to create your own log layout format.
 *  
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @abstract
 */
abstract class LoggerLayout {

    /**
     * Creates LoggerLayout instances with the given class name.
     *
     * @param string $class
     * @return LoggerLayout
     */
    public static function factory($class)
    {
        if (!empty($class)) {
            $class = basename($class);
            if (!class_exists($class))
                include_once(LOG4PHP_DIR . "/layouts/{$class}.php");
            if (class_exists($class))
                return new $class();
        }
        return null;
    }

    /**
     * Override this method
     */
    abstract function activateOptions();

    /**
     * Override this method to create your own layout format.
     *
     * @param LoggerLoggingEvent
     * @return string
     */
    public function format($event)
    {
        return $event->getRenderedMessage();
    } 
    
    /**
     * Returns the content type output by this layout.
     * @return string
     */
    public function getContentType()
    {
        return "text/plain";
    } 
            
    /**
     * Returns the footer for the layout format.
     * @return string
     */
    public function getFooter()
    {
        return null;
    } 

    /**
     * Returns the header for the layout format.
     * @return string
     */
    public function getHeader()
    {
        return null;
    }
}
?>
