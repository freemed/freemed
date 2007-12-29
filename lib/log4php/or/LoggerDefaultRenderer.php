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
 */
require_once LOG4PHP_DIR.'/or/LoggerObjectRenderer.php';

/**
 * The default Renderer renders objects by type casting
 *
 * @author  Marco Vassura
 * @package log4php
 * @subpackage or
 * @since 0.3
 */
class LoggerDefaultRenderer extends LoggerObjectRenderer{
  
    /**
     * Render objects by type casting
     *
     * @param mixed $o the object to render
     * @return string
     */
    public function doRender($o) {
        return var_export($o, true);
    }
}
?>
