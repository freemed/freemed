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

class Net_HL7_Segment {

    var $_fields;

    /**
     * Create an instance of this segment. A segment may be created with just
     * a name or a name and a reference to an array of field values. If the
     * name is not given, no segment is created. The segment name should be
     * three characters long, and upper case. If it isn't, no segment is
     * created, and undef is returned.  If a reference to an array is given,
     * all fields will be filled from that array. Note that for composed
     * fields and subcomponents, the array may hold subarrays and
     * subsubarrays. Repeated fields can not be supported the same way, since
     * we can't distinguish between composed fields and repeated fields.
     *
     * Example: <code>
     *
     * $seg =& new Net_HL7_Segment("PID");
     *
     * $seg->setField(3, "12345678");
     * echo $seg->getField(1);
     * </code>
     *
     * @version    0.10
     * @author     D.A.Dokter <dokter@w20e.com>
     * @access     public
     * @category   Networking
     * @package    Net_HL7
     * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
     * @param mixed Name of the segment
     * @param array Fields for segment
     */
    function Net_HL7_Segment($name, $fields = array()) 
    {  
        // Is the name 3 upper case characters?
        //
        if ((!$name) || (strlen($name) != 3) || (strtoupper($name) != $name)) {
            trigger_error("Name should be 3 characters, uppercase", E_USER_ERROR);
        }

        $this->_fields = array();

        $this->_fields[0] = $name;

        if (is_array($fields)) {

            for ($i = 0; $i < count($fields); $i++) {
	
                $this->setField($i + 1, $fields[$i]);
            }
        }
    }


    /**
     * Set the field specified by index to value, and return some true value
     * if the operation succeeded. Indices start at 1, to stay with the HL7
     * standard. Trying to set the value at index 0 has no effect.  The value
     * may also be a reference to an array (that may itself contain arrays)
     * to support composed fields (and subcomponents).
     * 
     * To set a field to the HL7 null value, instead of omitting a field, can
     * be achieved with the _Net_HL7_NULL type, like:
     * <code>
     *   $segment->setField(8, $_Net_HL7_NULL);
     * </code>
     * This will render the field as the double quote ("").
     * If values are not provided at all, the method will just return.
     *
     * @param int Index to set
     * @param mixed Value for field
     * @return boolean
     * @access public
     */
    function setField($index, $value= "") 
    {
        if (!($index && $value)) {
            return false;
        }
    
        // Fill in the blanks...
        for ($i = count($this->_fields); $i < $index; $i++) {
            $this->_fields[$i] = "";
        }

        $this->_fields[$index] = $value;
    
        return true;
    }


    /**
     * Get the field at index. If the field is a composed field, you might
     * ask for the result to be an array like so:
     * <code>
     * $subfields = $seg->getField(9)
     * </code>
     * otherwise the thing returned will be a reference to an array.
     *
     * @param int Index of field
     * @return mixed The value of the field
     * @access public
     */
    function getField($index) 
    {
        return $this->_fields[$index];
    }


    /**
     * Get the number of fields for this segment, not including the name
     *
     * @return int number of fields
     * @access public
     */
    function size() 
    {
        return count($this->_fields) - 1;
    }


    /**
     * Get the fields in the specified range, or all if nothing specified. If
     * only the 'from' value is provided, all fields from this index till the
     * end of the segment will be returned.
     *
     * @param int Start range at this index
     * @param int Stop range at this index
     * @return array List of fields
     * @access public
     */
    function getFields($from = 0, $to = 0) 
    {
        if (!$to) {
            $to = count($this->_fields);
        }

        return array_slice($this->_fields, $from, $to - $from + 1);
    }    


    /**
     * Get the name of the segment. This is basically the value at index 0
     * 
     * @return mixed Name of segment
     * @access public
     */
    function getName() 
    {
        return $this->_fields[0];
    }
}
?>
