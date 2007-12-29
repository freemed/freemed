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
 */
require_once(LOG4PHP_DIR . '/Logger.php');
require_once(LOG4PHP_DIR . '/LoggerLevel.php');

/**
 * The root logger.
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @see Logger
 */
class LoggerRoot extends Logger {

    /**
     * @var string name of logger 
     */
    protected $name   = 'root';

    /**
     * @var object must be null for LoggerRoot
     */
    protected $parent = null;
    

    /**
     * Constructor
     *
     * @param integer $level initial log level
     */
    public function __construct($level = null)
    {
        parent::__construct($this->name);
        if ($level == null)
            $level = LoggerLevel::getLevelAll();
        $this->setLevel($level);
    } 
    
    /**
     * @return LoggerLevel the level
     */
    public function getChainedLevel()
    {
        return $this->level;
    } 
    
    /**
     * Setting a null value to the level of the root category may have catastrophic results.
     * @param LoggerLevel $level
     */
    public function setLevel($level)
    {
        if ($level != null) {
        $this->level = $level;
    }    
    }    
 
    /**
     * Please use setLevel() instead.
     * @param LoggerLevel $level
     * @deprecated
     */
    public function setPriority($level)
    {
        $this->setLevel($level); 
    }
    
    /**
     * Always returns false.
     * Because LoggerRoot has no parents, it returns false.
     * @param Logger $parent
     * @return boolean
     */
    public function setParent($parent)
    {
        return false;
    }  
}
?>
