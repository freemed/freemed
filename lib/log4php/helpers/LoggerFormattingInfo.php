<?php
/**
 * Copyright 2004 The Apache Software Foundation.
 *
 * This software is published under the terms of the Apache Software
 * License version 2.0, a copy of which has been included with this
 * distribution in the LICENSE file.
 *
 * @package log4php
 * @subpackage helpers
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 */
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * This class encapsulates the information obtained when parsing
 * formatting modifiers in conversion modifiers.
 * 
 * @author  Marco Vassura
 * @package log4php
 * @subpackage spi
 * @since 0.3
 */
class LoggerFormattingInfo {

    var $min        = -1;
    var $max        = 0x7FFFFFFF;
    var $leftAlign  = false;

    /**
     * Constructor
     */
    function LoggerFormattingInfo() {}
    
    function reset()
    {
        $this->min          = -1;
        $this->max          = 0x7FFFFFFF;
        $this->leftAlign    = false;      
    }

    function dump()
    {
        LoggerLog::debug("LoggerFormattingInfo::dump() min={$this->min}, max={$this->max}, leftAlign={$this->leftAlign}");
    }
} 
?>
