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
 
require_once(LOG4PHP_DIR . '/LoggerAppenderSkeleton.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Appends log events to mail using php function {@link PHP_MANUAL#mail}.
 *
 * <p>Parameters are {@link $from}, {@link $to}, {@link $subject}.</p>
 * <p>This appender requires a layout.</p>
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage appenders
 */
class LoggerAppenderMail extends LoggerAppenderSkeleton {

    /**
     * @var string 'from' field
     */
    var $from = null;

    /**
     * @var string 'subject' field
     */
    var $subject = 'Log4php Report';
    
    /**
     * @var string 'to' field
     */
    var $to = null;

    /**
     * @var string used to create mail body
     * @access private
     */
    var $body = '';
    
    /**
     * Constructor.
     *
     * @param string $name appender name
     */
    public function __construct($name) {
        parent::__construct($name);
                $this->requiresLayout = true;
    }

    public function activateOptions() {
        $this->closed = false;
    }
    
    public function close() {
        $from = $this->from;
        $to = $this->to;

        if (!empty($this->body) and $from !== null and $to !== null and $this->layout !== null) {
                        $subject = $this->subject;
            LoggerLog::debug("LoggerAppenderMail::close() sending mail from=[{$from}] to=[{$to}] subject=[{$subject}]");
            mail(
                $to, $subject, 
                $this->layout->getHeader() . $this->body . $this->layout->getFooter(),
                "From: {$from}\r\n"
            );
        }
        $this->closed = true;
    }
    
    /**
     * @return string
     */
    function getFrom()
    {
        return $this->from;
    }
    
    /**
     * @return string
     */
    function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    function getTo()
    {
        return $this->to;
    }
    
    function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    function setTo($to)
    {
        $this->to = $to;
    }

    function setFrom($from)
    {
        $this->from = $from;
    }  

    function append($event)
    {
        if ($this->layout !== null)
            $this->body .= $this->layout->format($event);
    }
}
?>
