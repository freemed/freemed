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

require_once(LOG4PHP_DIR . '/spi/LoggerConfigurator.php');
require_once(LOG4PHP_DIR . '/layouts/LoggerLayoutTTCC.php');
require_once(LOG4PHP_DIR . '/appenders/LoggerAppenderConsole.php');
require_once(LOG4PHP_DIR . '/LoggerManager.php');

/**
 * Use this class to quickly configure the package.
 *
 * <p>For file based configuration see {@link LoggerPropertyConfigurator}. 
 * <p>For XML based configuration see {@link LoggerDOMConfigurator}.
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 */
class LoggerBasicConfigurator implements LoggerConfigurator {

    /**
     * Add a {@link LoggerAppenderConsole} that uses 
     * the {@link LoggerLayoutTTCC} to the root category.
     * 
     * @param string $url not used here
     */
    public static function configure($url = null)
    {
        $root = LoggerManager::getRootLogger();
        $appender = new LoggerAppenderConsole('A1');
        $appender->setLayout( new LoggerLayoutTTCC() );
        $root->addAppender($appender);
    }

    /**
     * Reset the default hierarchy to its default. 
     * It is equivalent to
     * <code>
     * LoggerManager::resetConfiguration();
     * </code>
     *
     * @see LoggerHierarchy::resetConfiguration()
     * @static
     */
    public static function resetConfiguration()
    {
        LoggerManager::resetConfiguration();
    }
}
?>
