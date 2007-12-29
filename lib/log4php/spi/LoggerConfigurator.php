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
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__)); 

/**
 * Special level value signifying inherited behaviour. The current
 * value of this string constant is <b>inherited</b>. 
 * {@link LOG4PHP_LOGGER_CONFIGURATOR_NULL} is a synonym.  
 */
define('LOG4PHP_LOGGER_CONFIGURATOR_INHERITED', 'inherited');

/**
 * Special level signifying inherited behaviour, same as 
 * {@link LOG4PHP_LOGGER_CONFIGURATOR_INHERITED}. 
 * The current value of this string constant is <b>null</b>. 
 */
define('LOG4PHP_LOGGER_CONFIGURATOR_NULL',      'null');

/**
 * Implemented by classes capable of configuring log4php using a URL.
 *  
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage spi  
 */
interface LoggerConfigurator {

   /**
    * Interpret a resource pointed by a <var>url</var> and configure accordingly.
    *
    * The configuration is done relative to the <var>repository</var>
    * parameter.
    *
    * @param string $url The URL to parse
    */
    public static function configure($url=null);
    
}
?>
