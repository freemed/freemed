<?php
/**
 * Copyright 2004 The Apache Software Foundation.
 *
 * This software is published under the terms of the Apache Software
 * License version 2.0, a copy of which has been included with this
 * distribution in the LICENSE file.
 *
 * @package log4php
 * @subpackage spi
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');

/**
 * When location information is not available the constant
 * <i>NA</i> is returned. Current value of this string
 * constant is <b>?</b>.  
 */
define('LOG4PHP_LOGGER_LOCATION_INFO_NA',  'NA');

/**
 * The internal representation of caller location information.
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage spi
 * @since 0.3
 */
class LoggerLocationInfo {

    /**
    * @var string Caller's line number.
    */
    protected $lineNumber = null;
    
    /**
    * @var string Caller's file name.
    */
    protected $fileName = null;
    
    /**
    * @var string Caller's fully qualified class name.
    */
    protected $className = null;
    
    /**
    * @var string Caller's method name.
    */
    protected $methodName = null;
    
    /**
    * @var string 
    */
    protected $fullInfo = null;

    /**
     * Instantiate location information based on a {@link PHP_MANUAL#debug_backtrace}.
     *
     * @param array $trace
     * @param mixed $caller
     */
    public function __construct($trace, $fqcn = null)
    {
        $this->lineNumber   = isset($trace['line']) ? $trace['line'] : null;
        $this->fileName     = isset($trace['file']) ? $trace['file'] : null;
        $this->className    = isset($trace['class']) ? $trace['class'] : null;
        $this->methodName   = isset($trace['function']) ? $trace['function'] : null;
        
        $this->fullInfo = $this->getClassName() . '.' . $this->getMethodName() . 
                          '(' . $this->getFileName() . ':' . $this->getLineNumber() . ')';
    }

    public function getClassName()
    {
        return ($this->className === null) ? LOG4PHP_LOGGER_LOCATION_INFO_NA : $this->className; 
    }

    /**
     *  Return the file name of the caller.
     *  <p>This information is not always available.
     */
        public function getFileName()
    {
        return ($this->fileName === null) ? LOG4PHP_LOGGER_LOCATION_INFO_NA : $this->fileName; 
    }

    /**
     *  Returns the line number of the caller.
     *  <p>This information is not always available.
     */
    public function getLineNumber()
    {
        return ($this->lineNumber === null) ? LOG4PHP_LOGGER_LOCATION_INFO_NA : $this->lineNumber; 
    }

    /**
     *  Returns the method name of the caller.
     */
    public function getMethodName()
    {
        return ($this->methodName === null) ? LOG4PHP_LOGGER_LOCATION_INFO_NA : $this->methodName; 
    }
}
?>
