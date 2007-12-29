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

require_once(LOG4PHP_DIR . '/spi/LoggerFactory.php');
require_once(LOG4PHP_DIR . '/Logger.php');

/**
 * Creates instances of {@link Logger} with a given name.
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @since 0.5 
 */
class LoggerDefaultCategoryFactory extends LoggerFactory {
    
    /**
     * @param string $name
     * @return Logger
     */
    public function makeNewLoggerInstance($name)
    {
        return new Logger($name);
    }
}

?>
