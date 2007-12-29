<?php
/**
 * Copyright 2004 The Apache Software Foundation.
 *
 * This software is published under the terms of the Apache Software
 * License version 2.0, a copy of which has been included with this
 * distribution in the LICENSE file.
 *
 * @package log4php
 * @subpackage varia
 */

/**
 * @ignore 
 */
if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__) . '/..');
 
/**
 */
require_once(LOG4PHP_DIR . '/spi/LoggerFilter.php');

/**
 * This filter drops all logging events. 
 * 
 * <p>You can add this filter to the end of a filter chain to
 * switch from the default "accept all unless instructed otherwise"
 * filtering behaviour to a "deny all unless instructed otherwise"
 * behaviour.</p>
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage varia
 * @since 0.3
 */
class LoggerDenyAllFilter extends LoggerFilter {

  /**
   * Always returns the integer constant {@link LOG4PHP_LOGGER_FILTER_DENY}
   * regardless of the {@link LoggerLoggingEvent} parameter.
   * 
   * @param LoggerLoggingEvent $event The {@link LoggerLoggingEvent} to filter.
   * @return LOG4PHP_LOGGER_FILTER_DENY Always returns {@link LOG4PHP_LOGGER_FILTER_DENY}
   */
  function decide($event)
  {
    return LOG4PHP_LOGGER_FILTER_DENY;
  }
}
?>
