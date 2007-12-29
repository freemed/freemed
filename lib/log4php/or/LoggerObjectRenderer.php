<?php
/**
 * Copyright 2004 The Apache Software Foundation.
 *
 * This software is published under the terms of the Apache Software
 * License version 2.0, a copy of which has been included with this
 * distribution in the LICENSE file.
 *
 * @package log4php
 * @subpackage or
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 * Subclass this abstract class in order to render objects as strings.
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage or
 * @abstract
 * @since 0.3
 */
abstract class LoggerObjectRenderer {

    /**
     * @param string $class classname
     * @return LoggerObjectRenderer create LoggerObjectRenderer instances
     */
    public static function factory($class) {
        if (!empty($class)) {
            $class = basename($class);
            include_once LOG4PHP_DIR."/or/{$class}.php";
            if (class_exists($class)) {
                return new $class();
            }
        }
        return null;
    }

    /**
     * Render the entity passed as parameter as a String.
     * @param mixed $o entity to render
     * @return string
     */
    abstract public function doRender($o);
}
?>
