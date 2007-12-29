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
 * Abstract class that defines output logs strategies.
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 */
abstract class LoggerAppender {

    /**
     * Factory
     *
     * @param string $name appender name
     * @param string $class create an instance of this appender class
     * @return LoggerAppender
     */
    public static function factory($name, $class)
    {
        $class = basename($class);
        if (!empty($class)) {
                
            if (!class_exists($class)) 
                include_once(LOG4PHP_DIR . "/appenders/{$class}.php");
            if (class_exists($class))
                return new $class($name);
        }
        return null;
    }
    
    /**
     * Singleton
     *
     * @param string $name appender name
     * @param string $class create or get a reference instance of this class
     * @return LoggerAppender 
     */
    public static function singleton($name, $class = '')
    {
        static $instances;
        
        if (!empty($name)) {
            if (!isset($instances[$name])) {
                if (!empty($class)) {
                    $appender = self::factory($name, $class);
                    if ($appender !== null) { 
                        $instances[$name] = $appender;
                        return $instances[$name];
                    }
                }
                return null;
            }
            return $instances[$name];                
        }        
        return null;        
    }
    
    /* --------------------------------------------------------------------------*/
    /* --------------------------------------------------------------------------*/
    /* --------------------------------------------------------------------------*/
    
    /**
     * Add a filter to the end of the filter list.
     *
     * @param LoggerFilter $newFilter add a new LoggerFilter
     * @abstract
     */
    abstract public function addFilter($newFilter);
    
    /**
     * Clear the list of filters by removing all the filters in it.
     * @abstract
     */
    abstract function clearFilters();

    /**
     * Return the first filter in the filter chain for this Appender. 
     * The return value may be <i>null</i> if no is filter is set.
     * @return LoggerFilter
     */
    abstract function getFilter(); 
    
    /**
     * Release any resources allocated.
     * Subclasses of {@link LoggerAppender} should implement 
     * this method to perform proper closing procedures.
     * @abstract
     */
    abstract public function close();

    /**
     * This method performs threshold checks and invokes filters before
     * delegating actual logging to the subclasses specific <i>append()</i> method.
     * @param LoggerLoggingEvent $event
     * @abstract
     */
    abstract public function doAppend($event);

    /**
     * Get the name of this appender.
     * @return string
     */
    abstract public function getName();

    /**
     * Do not use this method.
     *
     * @param object $errorHandler
     */
    abstract public function setErrorHandler($errorHandler);
    
    /**
     * Do not use this method.
     * @return object Returns the ErrorHandler for this appender.
     */
    abstract public function getErrorHandler(); 

    /**
     * Set the Layout for this appender.
     *
     * @param LoggerLayout $layout
     */
    abstract public function setLayout($layout);
    
    /**
     * Returns this appender layout.
     * @return LoggerLayout
     */
    abstract public function getLayout();

    /**
     * Set the name of this appender.
     *
     * The name is used by other components to identify this appender.
     *
     * @param string $name
     */
    abstract public function setName($name); 

    /**
     * Configurators call this method to determine if the appender
     * requires a layout. 
     *
     * <p>If this method returns <i>true</i>, meaning that layout is required, 
     * then the configurator will configure a layout using the configuration 
     * information at its disposal.  If this method returns <i>false</i>, 
     * meaning that a layout is not required, then layout configuration will be
     * skipped even if there is available layout configuration
     * information at the disposal of the configurator.</p>
     *
     * <p>In the rather exceptional case, where the appender
     * implementation admits a layout but can also work without it, then
     * the appender should return <i>true</i>.</p>
     *
     * @return boolean
     */
    abstract public function requiresLayout();

}
?>
