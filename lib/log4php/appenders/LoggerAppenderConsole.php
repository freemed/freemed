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

/** @ignore */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 */
require_once(LOG4PHP_DIR . '/LoggerAppenderSkeleton.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');


/**
 * ConsoleAppender appends log events to STDOUT or STDERR using a layout specified by the user. 
 * 
 * <p>Optional parameter is {@link $target}. The default target is Stdout.</p>
 * <p><b>Note</b>: Use this Appender with command-line php scripts. 
 * On web scripts this appender has no effects.</p>
 * <p>This appender requires a layout.</p>  
 *
 * @author  Marco Vassura
 * @author Knut Urdalen <knut.urdalen@gmail.com>
 * @version $Revision$
 * @package log4php
 * @subpackage appender
 */
class LoggerAppenderConsole extends LoggerAppenderSkeleton {

    const STDOUT = 'php://stdout';
    const STDERR = 'php://stderr';

    /**
     * Can be 'php://stdout' or 'php://stderr'. But it's better to use keywords <b>STDOUT</b> and <b>STDERR</b> (case insensitive). 
     * Default is STDOUT
     * @var string    
     */
    protected $target = 'php://stdout';
    
    /**
     * @var boolean
     * @access private     
     */
    protected $requiresLayout = true;

    /**
     * @var mixed the resource used to open stdout/stderr
     * @access private     
     */
    protected $fp = false;

    /**
     * Set console target.
     * @param mixed $value a constant or a string
     */
    public function setTarget($value) {
        $v = trim($value);
        if ($v == self::STDOUT || strtoupper($v) == 'STDOUT') {
            $this->target = self::STDOUT;
        } elseif ($v == self::STDERR || strtoupper($v) == 'STDERR') {
            $target = self::STDERR;
        } else {
            LoggerLog::debug("Invalid target. Using '".self::STDOUT."' by default.");        
        }
    }

    public function getTarget() {
        return $this->target;
    }

    public function activateOptions() {
        $this->fp = fopen($this->getTarget(), 'w');
        if($this->fp !== false && $this->layout !== null) {
            fwrite($this->fp, $this->layout->getHeader());
        }
        $this->closed = (bool)($this->fp === false);
    }
    
    /**
     * @see LoggerAppender::close()
     */
    public function close() {
        if ($this->fp && $this->layout !== null) {
            fwrite($this->fp, $this->layout->getFooter());
                        fclose($this->fp);
        }        
        $this->closed = true;
    }

    protected function append($event) {
        if ($this->fp && $this->layout !== null) {
            fwrite($this->fp, $this->layout->format($event));
        } 
    }
}

?>
