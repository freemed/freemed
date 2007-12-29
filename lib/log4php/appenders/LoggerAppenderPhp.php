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
require_once(LOG4PHP_DIR . '/LoggerLevel.php');
require_once(LOG4PHP_DIR . '/LoggerLog.php');

/**
 * Log events using php {@link PHP_MANUAL#trigger_error} function and a {@link LoggerLayoutTTCC} default layout.
 *
 * <p>Levels are mapped as follows:</p>
 * - <b>level &lt; WARN</b> mapped to E_USER_NOTICE
 * - <b>WARN &lt;= level &lt; ERROR</b> mapped to E_USER_WARNING
 * - <b>level &gt;= ERROR</b> mapped to E_USER_ERROR  
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage appenders
 */ 
class LoggerAppenderPhp extends LoggerAppenderSkeleton {

    
    public function activateOptions() {
        $this->layout = LoggerLayout::factory('LoggerLayoutTTCC');
        $this->closed = false;
    }

    public function close() {
        $this->closed = true;
    }

    public function append($event) {
        if ($this->layout !== null) {
            LoggerLog::debug("LoggerAppenderPhp::append()");
            $level = $event->getLevel();
            if ($level->isGreaterOrEqual(LoggerLevel::getLevelError())) {
                trigger_error($this->layout->format($event), E_USER_ERROR);
            } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelWarn())) {
                trigger_error($this->layout->format($event), E_USER_WARNING);
            } else {
                trigger_error($this->layout->format($event), E_USER_NOTICE);
            }
        }
    }
}
?>
