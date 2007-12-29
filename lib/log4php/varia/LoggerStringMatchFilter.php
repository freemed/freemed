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
 * This is a very simple filter based on string matching.
 * 
 * <p>The filter admits two options {@link $stringToMatch} and
 * {@link $acceptOnMatch}. If there is a match (using {@link PHP_MANUAL#strpos}
 * between the value of the {@link $stringToMatch} option and the message 
 * of the {@link LoggerLoggingEvent},
 * then the {@link decide()} method returns {@link LOG4PHP_LOGGER_FILTER_ACCEPT} if
 * the <b>AcceptOnMatch</b> option value is true, if it is false then
 * {@link LOG4PHP_LOGGER_FILTER_DENY} is returned. If there is no match, {@link LOG4PHP_LOGGER_FILTER_NEUTRAL}
 * is returned.</p>
 *
 * @author  Marco Vassura
 * @version $Revision$
 * @package log4php
 * @subpackage varia
 * @since 0.3
 */
class LoggerStringMatchFilter extends LoggerFilter {
  
    /**
     * @var boolean
     */
    var $acceptOnMatch = true;

    /**
     * @var string
     */
    var $stringToMatch = null;
  
    /**
     * @return boolean
     */
    function getAcceptOnMatch()
    {
        return $this->acceptOnMatch;
    }
    
    /**
     * @param mixed $acceptOnMatch a boolean or a string ('true' or 'false')
     */
    function setAcceptOnMatch($acceptOnMatch)
    {
        $this->acceptOnMatch = is_bool($acceptOnMatch) ? 
            $acceptOnMatch : 
            (bool)(strtolower($acceptOnMatch) == 'true');
    }
    
    /**
     * @return string
     */
    function getStringToMatch()
    {
        return $this->stringToMatch;
    }
    
    /**
     * @param string $s the string to match
     */
    function setStringToMatch($s)
    {
        $this->stringToMatch = $s;
    }

    /**
     * @return integer a {@link LOGGER_FILTER_NEUTRAL} is there is no string match.
     */
    function decide($event)
    {
        $msg = $event->getRenderedMessage();
        
        if($msg === null or  $this->stringToMatch === null)
            return LOG4PHP_LOGGER_FILTER_NEUTRAL;
        if( strpos($msg, $this->stringToMatch) !== false ) {
            return ($this->acceptOnMatch) ? LOG4PHP_LOGGER_FILTER_ACCEPT : LOG4PHP_LOGGER_FILTER_DENY ; 
        }
        return LOG4PHP_LOGGER_FILTER_NEUTRAL;        
    }
}
?>
