<?php
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

require_once(GWTPHP_DIR.'/rpc/impl/AbstractSerializationStreamReader.class.php');
//require_once(GWTPHP_DIR.'/lang/TypeSignatures.class.php');
require_once(GWTPHP_DIR.'/rpc/impl/SerializabilityUtil.class.php');

define('CHAR_UFFFF', "\xEF\xBF\xBF");

final class ServerSerializationStreamReader extends AbstractSerializationStreamReader{

	/**
	 *
	 * @var array of strings
	 */
	private $tokenList;
	/**
	 *
	 * @var int
	 */
	private $tokenListIndex;

	/**
	 *
	 * @var array of strings
	 */
	private $stringTable;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * 
	 * @var MappedClassLoader
	 */
	private $mappedClassLoader;



	/**
	 * 
	 * @var SerializationPolicyProvider
	 */
	private $serializationPolicyProvider;

	/**
	 * 
	 * @var SerializationPolicy
	 */
	private $serializationPolicy;



	public function __construct(MappedClassLoader $mappedClassLoader,
	SerializationPolicyProvider $serializationPolicyProvider)
	{
		$this->mappedClassLoader = $mappedClassLoader;
		$this->serializationPolicyProvider = $serializationPolicyProvider;
		$this->serializationPolicy = RPC::getDefaultSerializationPolicy();

		$this->logger = LoggerManager::getLogger('gwtphp.rpc.impl.ServerSerializationStreamReader');
	}

	public function prepareToRead($encodedTocens) {
		$this->tokenList = array();
		$this->tokenListIndex = 0;
		$this->stringTable = null;


		$idx =0;
		$nextIdx = 0;

		while (false != ($nextIdx = strpos ( $encodedTocens, CHAR_UFFFF, $idx))) {
			$current = substr($encodedTocens,$idx,$nextIdx-$idx);
			$this->tokenList[] =($current);
			$idx = $nextIdx + 3;
		}

		$this->logger->info($this->tokenList);
		parent::prepareToRead();

		$this->logger->info("Version " . $this->getVersion());
		$this->logger->info("Flags " . $this->getFlags());

		$this->deserializeStringTable();

		$this->logger->info($this->stringTable);


		// If this stream encodes resource file information, read it and get a
		// SerializationPolicy
		if ($this->hasSerializationPolicyInfo()) {
			$moduleBaseURL = $this->readString();
			$strongName = $this->readString();
			$this->logger->info('ModuleBaseURL '.$moduleBaseURL);
			$this->logger->info('StrongName '.$strongName);

// not implemented yet			
//			if ($this->serializationPolicyProvidero !== null) {
//				$this->serializationPolicy = $this->serializationPolicyProvider->getSerializationPolicy($moduleBaseURL, $strongName);
//
//				if ($this->serializationPolicy === null) {
//					throw new NullPointerException(
//					"serializationPolicyProvider.getSerializationPolicy()");
//				}
//			}
		}

	}

	/**	 *
	 * @return void
	 */
	private function deserializeStringTable() {
		$typeNameCount = $this->readInt(); // array lenght - ignored in php
		$this->stringTable = array();

		for ($typeNameIndex = 0; $typeNameIndex < $typeNameCount; ++$typeNameIndex) {
			$this->stringTable[$typeNameIndex] = $this->extract();
		}
	}

	/**
 * 
 * @throws SerializationException
 * @param MappedClass $type  the type to deserialize
 * @return Object
 */
	public function deserializeValue(MappedClass $type) {
		$this->logger->info(print_r($type,true));

		switch ($type->getSignature()) {
			case TypeSignatures::$BOOLEAN:
				return $this->readBoolean();
			case TypeSignatures::$BYTE:
				return $this->readByte();
			case TypeSignatures::$CHAR:
				return $this->readChar();
			case TypeSignatures::$DOUBLE:
				return $this->readDouble();
			case TypeSignatures::$FLOAT:
				return $this->readFloat();
			case TypeSignatures::$INT:
				return $this->readInt();
			case TypeSignatures::$LONG:
				return $this->readLong();
			case TypeSignatures::$SHORT:
				return $this->readShort();
			case "java.lang.String":
				return $this->readString();
			default:
				return $this->readObject();
		}

	}

	/**
	 * 
	 *
	 * @return string
	 */
	private function extract() {
		return $this->tokenList[$this->tokenListIndex++];
	}

	/**
	 * @throws SerializationException
	 * @return boolean
	 */
	public function readBoolean() {
		return (boolean) $this->extract();
	}

	/**
	 * @throws SerializationException
	 * @return byte
	 */
	public function readByte() {
		return $this->readInt();
	}

	/**
	 * @throws SerializationException
	 * @return char
	 */
	public function readChar() {
		return $this->readInt();
	}


	/**
	 * @throws SerializationException
	 * @return double
	 */
	function readDouble() {
		$_ = $this->extract();
		switch ( $_) {
			case 'NaN' : return NAN;
			case 'Infinity' : return INF;
			case '-Infinity': return -INF;
			default: return doubleval ($_);
		}
		//return doubleval(($_ == 'NaN' ) ? NAN : $_);
	}

	/**
	 * @throws SerializationException
	 * @return float
	 */
	public function readFloat() {
		$_ = $this->extract();
		switch ( $_) {
			case 'NaN' : return NAN;
			case 'Infinity' : return INF;
			case '-Infinity': return -INF;
			default: return floatval ($_);
		}
		//return floatval(($_ == 'NaN' ) ? NAN : $_);
	}

	/**
	 * @throws SerializationException
	 * @return int
	 */
	public function readInt() {
		return intval($this->extract());
	}

	/**
	 * @throws SerializationException
	 * @return float
	 */
	function readLong() {
		return  floatval($this->extract());
	}


	/**
	 * @throws SerializationException
	 * @return short
	 */
	function readShort() {
		return $this->readInt();
	}


	/**
	 * @throws SerializationException
	 * @return string
	 */
	public function readString() {
		return $this->getString($this->readInt());
	}

	/**
	 *Deserialize an object with the given type signature.
	 * 
	 * @throws SerializationException
	 * @param string $typeSignature  the type signature to deserialize
	 * @return Object the deserialized object
	 */
	protected function deserialize($typeSignature) {
		$this->logger->info("deserialize :".$typeSignature);

		$serializedInstRef = SerializabilityUtil::decodeSerializedInstanceReference($typeSignature);

		$this->logger->info("serializedInstRef : ".$serializedInstRef->getName()." ".$serializedInstRef->getSignature());
		/*MappedClass*/
		$instanceClass = $this->mappedClassLoader->loadMappedClass($serializedInstRef->getName());
		$instanceClass->setCRC($serializedInstRef->getSignature());
		$this->serializationPolicy->validateDeserialize($instanceClass); // {90%}

		$this->validateTypeVersions($instanceClass, $serializedInstRef); // {cut}
		// Class customSerializer = SerializabilityUtil.hasCustomFieldSerializer(instanceClass);
		// instance = instantiate(customSerializer, instanceClass);
		// rememberDecodedObject(instance);
		$customSerializer = SerializabilityUtil::hasCustomFieldSerializer($instanceClass); // {100%}
		$instance = $this->instantiate($customSerializer,$instanceClass); // {100%}
		$this->rememberDecodedObject($instance);


		$this->deserializeImpl($customSerializer, $instanceClass, $instance);

		return $instance;
		//$instance = $customSerializer->instantiate($this);
		//$instance = $this->deserializeImpl($customSerializer, $serializedInstRef->getName());
		//$instance = $this->deserializeImpl($customSerializer, $serializedInstRef->getName(), $instance);

		//return $instance;


	}
	/**
	 *
	 * @param ReflectionClass $customSerializer
	 * @param MappedClass $instanceClass
	 * @return Object
	 * @throws InstantiationException
	 * @throws IllegalAccessException
	 * @throws IllegalArgumentException
	 * @throws InvocationTargetException
	 */
	private function instantiate(ReflectionClass $customSerializer=null, MappedClass $instanceClass)
	{
		if ($customSerializer != null) {
			try {
				/*ReflectionMethod*/
				$instantiate = $customSerializer->getMethod('instantiate');//, SerializationStreamReader.class);
				return $instantiate->invoke(null, $this);
			} catch (ReflectionException $ex) {
				// purposely ignored
			}
		}

		if ($instanceClass->isArray()) {
			$length = $this->readInt();
			/*MappedClass*/
			$componentType = $instanceClass->getComponentType();
			require_once(GWTPHP_DIR.'/util/ArrayUtil.class.php');
			return ArrayUtil::initArrayWithSize($length);//Array.newInstance(componentType, length);
		} else {
			return $instanceClass->newInstance();
		}
	}

	private function validateTypeVersions (MappedClass $mappedClass, SerializedInstanceReference $serializedInstRef) {
		// TODO: implement this method (when add some tool to create automaticly maping between php and java classes,
		// we will be able to process CRC for java classes)
	}


	private function deserializeImpl(ReflectionClass $customSerializer=null, MappedClass $instanceClass, $instance)
	//,      Object $instance)
	//throws NoSuchMethodException, IllegalArgumentException,
	// IllegalAccessException, InvocationTargetException,
	// SerializationException, ClassNotFoundException
	{
		if ($customSerializer != null) {
			return $this->deserializeWithCustomFieldDeserializer($customSerializer, $instanceClass,
			$instance);
		} else {
			return $this->deserializeWithDefaultFieldDeserializer($instanceClass, $instance);
		}
	}

	private function deserializeWithCustomFieldDeserializer(ReflectionClass $customSerializer,
	MappedClass $instanceClass,$instance)
	// throws ClassNotFoundException, NoSuchMethodException, IllegalAccessException, InvocationTargetException
	{
		//	return $customSerializer->deserialize()
		//	throw new Exception('Unsuporter operation exception');
		if ($instanceClass->isArray()) {
			/*MappedClass*/
			$componentType = $instanceClass->getComponentType();
			if (!$componentType->isPrimitive()) {
				$instanceClass = array();//Class.forName("[Ljava.lang.Object;");
			}
		}
		/*MappedMethod*/
		$deserialize = $customSerializer->getMethod("deserialize");
		//SerializationStreamReader.class, instanceClass);
		$deserialize->invoke(null, $this, $instance);
	}
	
	/**
	 * 
	 *
	 * @param MappedClass $instanceClass
	 * @param Object $instance
	 */
	// TODO: exceptions catch and rethrow
	private function deserializeWithDefaultFieldDeserializer(MappedClass $instanceClass,$instance) {
		/*MappedField[]*/ $declFields = $instanceClass->getDeclaredFields();
		/*MappedField[]*/  $serializableFields = SerializabilityUtil::applyFieldSerializationPolicy($declFields);
		
		foreach ($serializableFields as $declField) {
			$value = $this->deserializeValue($declField->getType());
			
			$propName = $declField->getName();
			$rClass = $instanceClass->getReflectionClass();
			if ($rClass == null) {
				throw new ClassNotFoundException('MappedClass: '.$instanceClass->getSignature().' do not contains ReflectionClass infomration');
			}
			
			if (!$rClass->hasProperty($propName)) {
				throw new SerializationException('MappedClass: '.$instanceClass->getSignature().' do not contains property: '.$propName.' Did you mapped all properties?');
			}
			
			$rProperty = $rClass->getProperty($propName);
			if ($rProperty->isPublic()) {
				$rProperty->setValue($instance,$value);
			} else { // not public access to property, we try invoke setter method
				$propNameSetter = 'set'. strtoupper($propName[0]). substr($propName, 1,strlen($propName));
				if (!$rClass->hasMethod($propNameSetter)) {
					throw new SerializationException('MappedClass: '.$instanceClass->getSignature().' do not contains setter method for private property: '.$propName.'. Mapped object should be in pojo style?');	
				}
				$rMethod = $rClass->getMethod($propNameSetter) ;
				if ($rMethod->isPublic()) {	
					$rMethod->invoke($instance,$value);
				}
				else {
					throw new SerializationException('MappedClass: '.$instanceClass->getSignature().' do not contains public setter method for private property: '.$propName.'. Mapped object should be in pojo style?');	
				
				}
			}

			
			/*
				Object value = deserializeValue(declField.getType());

				boolean isAccessible = declField.isAccessible();
				boolean needsAccessOverride = !isAccessible
				  && !Modifier.isPublic(declField.getModifiers());
				if (needsAccessOverride) {
				// Override access restrictions
				declField.setAccessible(true);
				}
				
				declField.set(instance, value);
				
				if (needsAccessOverride) {
				// Restore access restrictions
				declField.setAccessible(isAccessible);
				}
			*/
		}
		
		$superClass = $instanceClass->getSuperclass();
		if ($superClass!= null && $this->getSerializationPolicy()->shouldDeserializeFields($superClass)) {
			$this->deserializeImpl(SerializabilityUtil::hasCustomFieldSerializer($superClass),$superClass,$instance);
		}
		/*
		Class<?> superClass = instanceClass.getSuperclass();
	    if (serializationPolicy.shouldDeserializeFields(superClass)) {
	      deserializeImpl(SerializabilityUtil.hasCustomFieldSerializer(superClass),
	          superClass, instance);
	    }
		*/
	}
	
	
	
	/**
   * Gets a string out of the string table.
   * 
   * @param int $index the index of the string to get
   * @return string
   */
	protected function getString($index) {
		if ($index == 0) {
			return null;
		}
		$this->logger->info("getString($index) where sizeof(this->stringTable)=".sizeof($this->stringTable));
		if($index > sizeof($this->stringTable)) throw new Exception('$index > sizeof($this->stringTable');
		// index is 1-based
		assert ($index > 0);
		assert ($index <= sizeof($this->stringTable));
		return (string) $this->stringTable[$index - 1];
	}



	/**
	 *
	 * @param MappedClassLoader $mappedClassLoader
	 * @return void
	 */
	public function setMappedClassLoader(MappedClassLoader $mappedClassLoader) {
		$this->mappedClassLoader = $mappedClassLoader;
	}
	/**
	 *
	 * @return MappedClassLoader
	 */
	public function getMappedClassLoader() {
		return $this->mappedClassLoader;
	}
	/**
	 *
	 * @param SerializationPolicyProvider $serializationPolicyProvider
	 * @return void
	 */
	public function setSerializationPolicyProvider($serializationPolicyProvider) {
		$this->serializationPolicyProvider = $serializationPolicyProvider;
	}
	/**
	 *
	 * @return SerializationPolicyProvider
	 */
	public function getSerializationPolicyProvider() {
		return $this->serializationPolicyProvider;
	}

	/**
	 *
	 * @param SerializationPolicy $serializationPolicy
	 * @return void
	 */
	public function setSerializationPolicy($serializationPolicy) {
		$this->serializationPolicy = $serializationPolicy;
	}
	/**
	 *
	 * @return SerializationPolicy
	 */
	public function getSerializationPolicy() {
		return $this->serializationPolicy;
	}



}

?>
