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
 
require_once(LOG4PHP_DIR . '/appenders/LoggerAppenderFile.php');

/**
 * LoggerAppenderDailyFile appends log events to a file ne.
 *
 * A formatted version of the date pattern is used as to create the file name
 * using the {@link PHP_MANUAL#sprintf} function.
 * <p>Parameters are {@link $datePattern}, {@link $file}. Note that file 
 * parameter should include a '%s' identifier and should always be set 
 * before {@link $file} param.</p>
 *
 * @author Abel Gonzalez <agonzalez@lpsz.org>
 * @author Knut Urdalen <knut.urdalen@gmail.com>
 * @version $Revision$
 * @package log4php
 * @subpackage appenders
 */                      
class LoggerAppenderDailyFile extends LoggerAppenderFile {

    /**
     * Format date. 
     * It follows the {@link PHP_MANUAL#date()} formatting rules and <b>should always be set before {@link $file} param</b>.
     * @var string
     */
    public $datePattern = "Ymd";
    
    /**
    * Sets date format for the file name.
    * @param string $format a regular date() string format
    */
    public function setDatePattern($format) {
        $this->datePattern = $format;
    }
    
    /**
    * @return string returns date format for the filename
    */
    public function getDatePattern() {
        return $this->datePattern;
    }
    
    /**
    * The File property takes a string value which should be the name of the file to append to.
    * Sets and opens the file where the log output will go.
    *
    * @see LoggerAppenderFile::setFile()
    */
    public function setFile() {
        $numargs = func_num_args();
        $args    = func_get_args();
        
        if ($numargs == 1 and is_string($args[0])) {
            parent::setFile( sprintf((string)$args[0], date($this->getDatePattern())) );
        } elseif ($numargs == 2 and is_string($args[0]) and is_bool($args[1])) {
            parent::setFile( sprintf((string)$args[0], date($this->getDatePattern())), $args[1] );
        }
    } 

}

?>
