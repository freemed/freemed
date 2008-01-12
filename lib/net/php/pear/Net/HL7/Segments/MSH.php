<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 D.A.Dokter                                        |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: D.A.Dokter <dokter@w20e.com>                                |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'Net/HL7/Segment.php';
require_once 'Net/HL7.php';

/**
 * MSH (message header) segment class
 *
 * Usage:
 * <code>
 * $seg =& new Net_HL7_Segments_MSH();
 *
 * $seg->setField(9, "ADT^A24");
 * echo $seg->getField(1);
 * </code>
 *
 * The Net_HL7_Segments_MSH is an implementation of the
 * Net_HL7_Segment class. The MSH segment is a bit different from
 * other segments, in that the first field is the field separator
 * after the segment name. Other fields thus start counting from 2!
 * The setting for the field separator for a whole message can be
 * changed by the setField method on index 1 of the MSH for that
 * message.  The MSH segment also contains the default settings for
 * field 2, COMPONENT_SEPARATOR, REPETITION_SEPARATOR,
 * ESCAPE_CHARACTER and SUBCOMPONENT_SEPARATOR. These fields default
 * to ^, ~, \ and & respectively.
 *
 * @version    $Revision$
 * @author     D.A.Dokter <dokter@w20e.com>
 * @access     public
 * @category   Networking
 * @package    Net_HL7
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */
class Net_HL7_Segments_MSH extends Net_HL7_Segment {

    /**
     * Create an instance of the MSH segment. 
     *
     * If an array argument is provided, all fields will be filled
     * from that array. Note that for composed fields and
     * subcomponents, the array may hold subarrays and
     * subsubarrays. If the reference is not given, the MSH segment
     * will be created with the MSH 1,2,7,10 and 12 fields filled in
     * for convenience.
     */
    function Net_HL7_Segments_MSH($fields = NULL, $hl7Globals = NULL) 
    {
        parent::Net_HL7_Segment("MSH", $fields);
    
        // Only fill default fields if no fields array is given 
        //
        if (!isset($fields)) {
      
            if (!is_array($hl7Globals)) {
                $this->setField(1, '|');
                $this->setField(2, '^~\\&');
                $this->setField(7, strftime("%Y%m%d%H%M%S"));
                
                // Set ID field
                //
                $this->setField(10, $this->getField(7) . rand(10000, 99999));
                $this->setField(12, '2.2');
            }
            else {
                $this->setField(1, $hl7Globals['FIELD_SEPARATOR']);
                $this->setField(2, 
                                $hl7Globals['COMPONENT_SEPARATOR'] .
                                $hl7Globals['REPETITION_SEPARATOR'] .
                                $hl7Globals['ESCAPE_CHARACTER'] .
                                $hl7Globals['SUBCOMPONENT_SEPARATOR']
                                );
                $this->setField(7, strftime("%Y%m%d%H%M%S"));
                
                // Set ID field
                //
                $this->setField(10, $this->getField(7) . rand(10000, 99999));
                $this->setField(12, $hl7Globals['HL7_VERSION']);
            }
        }
    }


    /**
     * Set the field specified by index to value. 
     *
     * Indices start at 1, to stay with the HL7 standard. Trying to
     * set the value at index 0 has no effect. Setting the value on
     * index 1, will effectively change the value of FIELD_SEPARATOR
     * for the message containing this segment, if the value has
     * length 1; setting the field on index 2 will change the values
     * of COMPONENT_SEPARATOR, REPETITION_SEPARATOR, ESCAPE_CHARACTER
     * and SUBCOMPONENT_SEPARATOR for the message, if the string is of
     * length 4.
     * 
     * @param int Index of field
     * @param mixed Value
     * @return boolean
     * @access public
     */
    function setField($index, $value) 
    {  
        if ($index == 1) {
            if (strlen($value) != 1) {
                return false;
            }
        }
    
        if ($index == 2) {
            if (strlen($value) != 4) {
                return false;
            }
        }
    
        return parent::setField($index, $value);
    }

}

?>
