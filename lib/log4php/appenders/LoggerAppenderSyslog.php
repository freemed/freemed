<?php
/**
 * log4php is a PHP port of the log4j java logging package.
 *
 * <p>This framework is based on log4j (see {@link http://jakarta.apache.org/log4j log4j} for details).</p>
 * <p>Design, strategies and part of the methods documentation are developed by log4j team 
 * (Ceki Gülcü as log4j project founder and 
 * {@link http://jakarta.apache.org/log4j/docs/contributors.html contributors}).</p>
 *
 * <p>PHP port, extensions and modifications by VxR. All rights reserved.<br>
 * For more information, please see {@link http://www.vxr.it/log4php/}.</p>
 *
 * <p>This software is published under the terms of the LGPL License
 * a copy of which has been included with this distribution in the LICENSE file.</p>
 *
 * @package log4php
 * @subpackage appenders
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
require_once(LOG4PHP_DIR . '/LoggerAppenderSkeleton.php');
require_once(LOG4PHP_DIR . '/LoggerLevel.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Log events using php {@link PHP_MANUAL#syslog} function.
 *
 * Levels are mapped as follows:
 * - <b>level &gt;= FATAL</b> to LOG_ALERT
 * - <b>FATAL &gt; level &gt;= ERROR</b> to LOG_ERR 
 * - <b>ERROR &gt; level &gt;= WARN</b> to LOG_WARNING
 * - <b>WARN  &gt; level &gt;= INFO</b> to LOG_INFO
 * - <b>INFO  &gt; level &gt;= DEBUG</b> to LOG_DEBUG
 *
 * @author VxR <vxr@vxr.it>
 * @version $Revision$
 * @package log4php
 * @subpackage appenders
 */ 
class LoggerAppenderSyslog extends LoggerAppenderSkeleton {
    
    /**
         * The ident string is added to each message. Typically the name of your application.
         *
         * @var string Ident for your application
         */
        private $_ident = "Log4PHP Syslog-Event";

    /**
     * The priority parameter value indicates the level of importance of the message.
     * It is passed on to the Syslog daemon.
     * 
     * @var int     Syslog priority
     */
    private $_priority;
    
    /**
     * The option used when generating a log message.
     * It is passed on to the Syslog daemon.
     * 
     * @var int     Syslog priority
     */
    private $_option;
    
    /**
     * The facility value indicates the source of the message.
     * It is passed on to the Syslog daemon.
     *
     * @var const int     Syslog facility
     */
    private $_facility;
    
    /**
     * If it is necessary to define logging priority in the .properties-file,
     * set this variable to "true".
     *
     * @var const int  value indicating whether the priority of the message is defined in the .properties-file
     *                 (or properties-array)
     */
    private $_overridePriority;

        /**
     * Set the ident of the syslog message.
     *
     * @param string Ident
     */
        public function setIdent($ident) {      
                $this->_ident = $ident;       
    }

    /**
     * Set the priority value for the syslog message.
     *
     * @param const int Priority
     */
        public function setPriority($priority) {      
                $this->_priority = $priority;       
    }
    
    
    /**
     * Set the facility value for the syslog message.
     *
     * @param const int Facility
     */
    public function setFacility($facility) {
                $this->_facility = $facility;
    } 
    
    /**
     * If the priority of the message to be sent can be defined by a value in the properties-file, 
     * set parameter value to "true".
     *
     * @param bool Override priority
     */
    public function setOverridePriority($overridePriority) {
                $this->_overridePriority = $overridePriority;                           
    } 
    
    /**
     * Set the option value for the syslog message.
     * This value is used as a parameter for php openlog()  
     * and passed on to the syslog daemon.
     *
     * @param string    $option
     */
    public function setOption($option) {      
                $this->_option = $option;       
    }
    
    
    public function activateOptions() {
        define_syslog_variables();
        $this->closed = false;
    }

    public function close() {
        closelog();
        $this->closed = true;
    }

    public function append($event) {

        if($this->_option == NULL){
            $this->_option = LOG_PID | LOG_CONS;
        }
        
        // Attach the process ID to the message, use the facility defined in the .properties-file
        openlog($this->_ident, $this->_option, $this->_facility);
        
        $level   = $event->getLevel();
        $message = $event->getRenderedMessage();
        
        // If the priority of a syslog message can be overridden by a value defined in the properties-file,
        // use that value, else use the one that is defined in the code.
        if($this->_overridePriority){
                        syslog($this->_priority, $message);            
        } else {
        if ($level->isGreaterOrEqual(LoggerLevel::getLevelFatal())) {
            syslog(LOG_ALERT, $message);
        } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelError())) {
            syslog(LOG_ERR, $message);        
        } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelWarn())) {
            syslog(LOG_WARNING, $message);
        } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelInfo())) {
            syslog(LOG_INFO, $message);
        } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelDebug())) {
            syslog(LOG_DEBUG, $message);
        }
    }
        closelog();
    }
}
?>
