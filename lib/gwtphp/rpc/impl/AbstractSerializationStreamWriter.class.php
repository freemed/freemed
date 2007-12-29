<?PHP
/*
 * GWTPHP is a port to PHP of the GWT RPC package.
 * 
 * <p>This framework is based on GWT (see {@link http://code.google.com/webtoolkit/ gwt-webtoolkit} for details).</p>
 * <p>Design, strategies and part of the methods documentation are developed by Google Team  </p>
 * 
 * <p>PHP port, extensions and modifications by Rafal M.Malinowski. All rights reserved.<br>
 * For more information, please see {@link http://gwtphp.sourceforge.com/}.</p>
 * 
 * 
 * <p>Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at</p>
 * 
 * {@link http://www.apache.org/licenses/LICENSE-2.0 http://www.apache.org/licenses/LICENSE-2.0}
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/**
 * @package gwtphp.rpc.impl
 */

require_once(GWTPHP_DIR.'/rpc/impl/AbstractSerializationStream.class.php');
require_once(GWTPHP_DIR.'/rpc/SerializationStreamWriter.class.php');

 abstract class AbstractSerializationStreamWriter extends
    AbstractSerializationStream implements SerializationStreamWriter {
    	
    	/**
    	 * Enter description here...
    	 * @return string
    	 */
  public abstract function toString();

  /**
   * 
   * @param boolean $fieldValue
   */
  public function writeBoolean($fieldValue) {
    $this->append($fieldValue ? "1" : "0");
  }

    /**
   * 
   * @param byte $fieldValue
   */
  public function writeByte($fieldValue) {
    $this->append((string)$fieldValue);
  }
  /**
   * 
   * @param char $fieldValue
   */
  public function writeChar($fieldValue) {
    // just use an int, it's more foolproof
    $this->append((string)((int) $fieldValue));
  }
  /**
   * 
   * @param double $fieldValue
   */
  public function writeDouble($fieldValue) {  	
    $this->writeFloat($fieldValue);  	
  }
  /**
   * 
   * @param float $fieldValue
   */
  public function writeFloat($fieldValue) {  	
  	if (is_nan($fieldValue)) {
  		$this->append('NaN');
  	} else if (is_infinite($fieldValue)) {
  		if ($fieldValue < 0)
  			$this->append('-Infinity');
  		else 
  			$this->append('Infinity');
  	}
  	else {
  		//php float notation is:      1.7014117E+38 
  		//java float notation expect: 1.7014117E38 
  		//but this difference is acceptable
    	//$this->append(str_replace('+','',strval($fieldValue)));
    	$this->append((string)$fieldValue);
  	}
  }
  /**
   * 
   * @param int $fieldValue
   */
  public function writeInt($fieldValue) {
    $this->append(number_format($fieldValue,0,'.',''));
//    $this->append((string)$fieldValue);
  }
  /**
   * 
   * @param long $fieldValue
   */
  public function writeLong($fieldValue) {
    //$this->append((string)$fieldValue);
    $this->append(number_format($fieldValue,0,'.',''));
  }

  /**
   * 
   * @param Object $instance
   * @throws SerializationException
   */
  public function writeObject($instance, MappedClass $type = null) {
  	 
		
    if ($instance === null) {
      // write a null string
      $this->writeString(null);
      return;
    }
    /*int*/
    $objIndex = $this->getIndexForObject($instance);
    if ($objIndex >= 0) {
      // We've already encoded this object, make a backref
      // Transform 0-based to negative 1-based
      $this->writeInt(-($objIndex + 1));
      return;
    }

    $this->saveIndexForObject($instance);

    // Serialize the type signature
    /*String*/ 
   // $typeSignature = $this->getObjectTypeSignature($instance);
   // TODO: implement calculation of signature
   if ($type === null) {
			$type = GWTPHPContext::getMappedClassLoader()->findMappedClassByObject($instance);
		}
		
   $typeSignature = $type->getSignature().'/'.$type->getCRC();
   //$typeSignature = 'java.lang.Integer/3438268394';//$type->getCRC();
    $this->writeString($typeSignature);
    // Now serialize the rest of the object
    $this->serialize($instance, $typeSignature,$type);
    
  }

  /**
   * @param short $fieldValue 
   */
  public function writeShort($fieldValue) {
    $this->append((string)$fieldValue);
  }

  /**
   * @param string $fieldValue 
   */
  public function writeString($value) {
    $this->writeInt($this->addString($value));
  }

  /**
   * Add a string to the string table and return its index.
   * 
   * @param string $string the string to add
   * @return int the index to the string
   */
  protected abstract function addString($string);

  /**
   * Append a token to the underlying output buffer.
   * 
   * @param string token the token to append
   */
  protected abstract function append($token);

  /**
   * Get the index for an object that may have previously been saved via
   * {@link #saveIndexForObject(Object)}.
   * 
   * @param Object instance the object to save
   * @return int the index associated with this object, or -1 if this object hasn't
   *         been seen before
   */
 protected abstract function  getIndexForObject($instance);

  /**
   * Compute and return the type signature for an object.
   * 
   * @param Object instance the instance to inspect
   * @return String the type signature of the instance
   */
  protected abstract function getObjectTypeSignature($instance);

  /**
   * Remember this object as having been seen before.
   * 
   * @param Object instance the object to remember
   */
  protected abstract function saveIndexForObject($instance);

  /**
   * Serialize an object into the stream.
   * 
   * @param Object instance the object to serialize
   * @param String typeSignature the type signature of the object
   * @throws SerializationException
   */
  protected abstract function serialize($instance, $typeSignature,MappedClass $type);

    	
    	
    	
    }

?>