#!/usr/bin/env php
<?php
// +------------------------------------------------------------------------+
// | wsdl2php                                                               |
// +------------------------------------------------------------------------+
// | Copyright (C) 2005 Knut Urdalen <knut.urdalen@gmail.com>               |
// +------------------------------------------------------------------------+
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS    |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT      |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR  |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT   |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,  |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT       | 
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,  |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY  |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT    |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE  |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.   |
// +------------------------------------------------------------------------+
// | This software is licensed under the LGPL license. For more information |
// | see http://wsdl2php.sf.net                                             |
// +------------------------------------------------------------------------+

ini_set('soap.wsdl_cache_enabled', 0); // disable WSDL cache

if( $_SERVER['argc'] != 2 ) {
  die("usage: wsdl2php <wsdl-file>\n");
}

$wsdl = $_SERVER['argv'][1];

print "Analyzing WSDL";

try {
  $client = new SoapClient($wsdl);
} catch(SoapFault $e) {
  die($e);
}
print ".";
$dom = DOMDocument::load($wsdl);
print ".";

// get documentation
$nodes = $dom->getElementsByTagName('documentation');
$doc = array('service' => '',
	     'operations' => array());
foreach($nodes as $node) {
  if( $node->parentNode->localName == 'service' ) {
    $doc['service'] = trim($node->parentNode->nodeValue);
  } else if( $node->parentNode->localName == 'operation' ) {
    $operation = $node->parentNode->getAttribute('name');
    //$parameterOrder = $node->parentNode->getAttribute('parameterOrder');
    $doc['operations'][$operation] = trim($node->nodeValue);
  }
}
print ".";

// get targetNamespace
$targetNamespace = '';
$nodes = $dom->getElementsByTagName('definitions');
foreach($nodes as $node) {
  $targetNamespace = $node->getAttribute('targetNamespace');
}
print ".";

// declare service
$service = array('class' => $dom->getElementsByTagNameNS('*', 'service')->item(0)->getAttribute('name'),
		 'wsdl' => $wsdl,
		 'doc' => $doc['service'],
		 'functions' => array());
print ".";

// PHP keywords - can not be used as constants, class names or function names!
$reserved_keywords = array('and', 'or', 'xor', 'as', 'break', 'case', 'cfunction', 'class', 'continue', 'declare', 'const', 'default', 'do', 'else', 'elseif', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'extends', 'for', 'foreach', 'function', 'global', 'if', 'new', 'old_function', 'static', 'switch', 'use', 'var', 'while', 'array', 'die', 'echo', 'empty', 'exit', 'include', 'include_once', 'isset', 'list', 'print', 'require', 'require_once', 'return', 'unset', '__file__', '__line__', '__function__', '__class__', 'abstract', 'private', 'public', 'protected', 'throw', 'try');

// ensure legal class name (I don't think using . and whitespaces is allowed in terms of the SOAP standard, should check this out and may throw and exception instead...)
$service['class'] = str_replace(' ', '_', $service['class']);
$service['class'] = str_replace('.', '_', $service['class']);
$service['class'] = str_replace('-', '_', $service['class']);

if(in_array(strtolower($service['class']), $reserved_keywords)) {
  $service['class'] .= 'Service';
}

// verify that the name of the service is named as a defined class
if(class_exists($service['class'])) {
  throw new Exception("Class '".$service['class']."' already exists");
}

/*if(function_exists($service['class'])) {
  throw new Exception("Class '".$service['class']."' can't be used, a function with that name already exists");
}*/

// get operations
$operations = $client->__getFunctions();
foreach($operations as $operation) {

  /*
   This is broken, need to handle
   GetAllByBGName_Response_t GetAllByBGName(string $Name)
   list(int $pcode, string $city, string $area, string $adm_center) GetByBGName(string $Name)

   finding the last '(' should be ok
   */
  //list($call, $params) = explode('(', $operation); // broken
  
  //if($call == 'list') { // a list is returned
  //}
  
  /*$call = array();
  preg_match('/^(list\(.*\)) (.*)\((.*)\)$/', $operation, $call);
  if(sizeof($call) == 3) { // found list()
    
  } else {
    preg_match('/^(.*) (.*)\((.*)\)$/', $operation, $call);
    if(sizeof($call) == 3) {
      
    }
  }*/

  $matches = array();
  if(preg_match('/^(\w[\w\d_]*) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $operation, $matches)) {
    $returns = $matches[1];
    $call = $matches[2];
    $params = $matches[3];
  } else if(preg_match('/^(list\([\w\$\d,_ ]*\)) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/', $operation, $matches)) {
    $returns = $matches[1];
    $call = $matches[2];
    $params = $matches[3];
  } else { // invalid function call
    throw new Exception('Invalid function call: '.$function);
  }

  $params = explode(', ', $params);

  $paramsArr = array();
  foreach($params as $param) {
    $paramsArr[] = explode(' ', $param);
  }
  //  $call = explode(' ', $call);
  $function = array('name' => $call,
		    'method' => $call,
		    'return' => $returns,
		    'doc' => isset($doc['operations'][$call])?$doc['operations'][$call]:'',
		    'params' => $paramsArr);

  // ensure legal function name
  if(in_array(strtolower($function['method']), $reserved_keywords)) {
    $function['name'] = '_'.$function['method'];
  }

  // ensure that the method we are adding has not the same name as the constructor
  if(strtolower($service['class']) == strtolower($function['method'])) {
    $function['name'] = '_'.$function['method'];
  }

  // ensure that there's no method that already exists with this name
  // this is most likely a Soap vs HttpGet vs HttpPost problem in WSDL
  // I assume for now that Soap is the one listed first and just skip the rest
  // this should be improved by actually verifying that it's a Soap operation that's in the WSDL file
  // QUICK FIX: just skip function if it already exists
  $add = true;
  foreach($service['functions'] as $func) {
    if($func['name'] == $function['name']) {
      $add = false;
    }
  }
  if($add) {
    $service['functions'][] = $function;
  }
  print ".";
}

$types = $client->__getTypes();

$primitive_types = array('string', 'int', 'long', 'float', 'boolean', 'dateTime', 'double', 'short', 'UNKNOWN', 'base64Binary', 'decimal', 'ArrayOfInt', 'ArrayOfFloat', 'ArrayOfString', 'decimal', 'hexBinary'); // TODO: dateTime is special, maybe use PEAR::Date or similar
$service['types'] = array();
foreach($types as $type) {
  $parts = explode("\n", $type);
  $class = explode(" ", $parts[0]);
  $class = $class[1];
  
  if( substr($class, -2, 2) == '[]' ) { // array skipping
    continue;
  }

  if( substr($class, 0, 7) == 'ArrayOf' ) { // skip 'ArrayOf*' types (from MS.NET, Axis etc.)
    continue;
  }


  $members = array();
  for($i=1; $i<count($parts)-1; $i++) {
    $parts[$i] = trim($parts[$i]);
    list($type, $member) = explode(" ", substr($parts[$i], 0, strlen($parts[$i])-1) );

    // check syntax
    if(preg_match('/^$\w[\w\d_]*$/', $member)) {
      throw new Exception('illegal syntax for member variable: '.$member);
      continue;
    }

    // IMPORTANT: Need to filter out namespace on member if presented
    if(strpos($member, ':')) { // keep the last part
      list($tmp, $member) = explode(':', $member);
    }

    // OBS: Skip member if already presented (this shouldn't happen, but I've actually seen it in a WSDL-file)
    // "It's better to be safe than sorry" (ref Morten Harket) 
    $add = true;
    foreach($members as $mem) {
      if($mem['member'] == $member) {
	$add = false;
      }
    }
    if($add) {
      $members[] = array('member' => $member, 'type' => $type);
    }
  }

  // gather enumeration values
  $values = array();
  if(count($members) == 0) {
    $values = checkForEnum($dom, $class);
  }

  $service['types'][] = array('class' => $class, 'members' => $members, 'values' => $values);
  print ".";
}
print "done\n";

print "Generating code...";
$code = "";

// add types
foreach($service['types'] as $type) {
  //  $code .= "/**\n";
  //  $code .= " * ".(isset($type['doc'])?$type['doc']:'')."\n";
  //  $code .= " * \n";
  //  $code .= " * @package\n";
  //  $code .= " * @copyright\n";
  //  $code .= " */\n";

  // add enumeration values
  $code .= "class ".$type['class']." {\n";
  foreach($type['values'] as $value) {
    $code .= "  const ".generatePHPSymbol($value)." = '$value';\n";
  }
  
  // add member variables
  foreach($type['members'] as $member) {
    //$code .= "  /* ".$member['type']." */\n";
    $code .= "  public \$".$member['member']."; // ".$member['type']."\n";
  }
  $code .= "}\n\n";

  /*  print "Writing ".$type['class'].".php...";
  $filename = $type['class'].".php";
  $fp = fopen($filename, 'w');
  fwrite($fp, "<?php\n".$code."?>\n");
  fclose($fp);
  print "ok\n";*/
}

// add service

// page level docblock
//$code .= "/**\n";
//$code .= " * ".$service['class']." class file\n";
//$code .= " * \n";
//$code .= " * @author    {author}\n";
//$code .= " * @copyright {copyright}\n";
//$code .= " * @package   {package}\n";
//$code .= " */\n\n";


// require types
//foreach($service['types'] as $type) {
//  $code .= "/**\n";
//  $code .= " * ".$type['class']." class\n";
//  $code .= " */\n";
//  $code .= "require_once '".$type['class'].".php';\n";
//}

$code .= "\n";

// class level docblock
$code .= "/**\n";
$code .= " * ".$service['class']." class\n";
$code .= " * \n";
$code .= parse_doc(" * ", $service['doc']);
$code .= " * \n";
$code .= " * @author    {author}\n";
$code .= " * @copyright {copyright}\n";
$code .= " * @package   {package}\n";
$code .= " */\n";
$code .= "class ".$service['class']." extends SoapClient {\n\n";

// add classmap
$code .= "  private static \$classmap = array(\n";
foreach($service['types'] as $type) {
  $code .= "                                    '".$type['class']."' => '".$type['class']."',\n";
}
$code .= "                                   );\n\n";
$code .= "  public function ".$service['class']."(\$wsdl = \"".$service['wsdl']."\", \$options = array()) {\n";

// initialize classmap (merge)
$code .= "    foreach(self::\$classmap as \$key => \$value) {\n";
$code .= "      if(!isset(\$options['classmap'][\$key])) {\n";
$code .= "        \$options['classmap'][\$key] = \$value;\n";
$code .= "      }\n";
$code .= "    }\n";
$code .= "    parent::__construct(\$wsdl, \$options);\n";
$code .= "  }\n\n";

foreach($service['functions'] as $function) {
  $code .= "  /**\n";
  $code .= parse_doc("   * ", $function['doc']);
  $code .= "   *\n";

  $signature = array(); // used for function signature
  $para = array(); // just variable names
  if(count($function['params']) > 0) {
    foreach($function['params'] as $param) {
      $code .= "   * @param ".(isset($param[0])?$param[0]:'')." ".(isset($param[1])?$param[1]:'')."\n";
      /*$typehint = false;
      foreach($service['types'] as $type) {
	if($type['class'] == $param[0]) {
	  $typehint = true;
	}
      }
      $signature[] = ($typehint) ? implode(' ', $param) : $param[1];*/
      $signature[] = (in_array($param[0], $primitive_types) or substr($param[0], 0, 7) == 'ArrayOf') ? $param[1] : implode(' ', $param);
      $para[] = $param[1];
    }
  }
  $code .= "   * @return ".$function['return']."\n";
  $code .= "   */\n";
  $code .= "  public function ".$function['name']."(".implode(', ', $signature).") {\n";
  //  $code .= "    return \$this->client->".$function['name']."(".implode(', ', $para).");\n";
  $code .= "    return \$this->__soapCall('".$function['method']."', array(";
  $params = array();
  if(count($signature) > 0) { // add arguments
    foreach($signature as $param) {
      if(strpos($param, ' ')) { // slice 
	$param = array_pop(explode(' ', $param));
      }
      $params[] = $param;
    }
    //$code .= "\n      ";
    $code .= implode(", ", $params);
    //$code .= "\n      ),\n";
  }
  $code .= "), ";
  //$code .= implode(', ', $signature)."),\n";
  $code .= "      array(\n";
  $code .= "            'uri' => '".$targetNamespace."',\n";
  $code .= "            'soapaction' => ''\n";
  $code .= "           )\n";
  $code .= "      );\n";
  $code .= "  }\n\n";
}
$code .= "}\n\n";
print "done\n";

print "Writing ".$service['class'].".php...";
$fp = fopen($service['class'].".php", 'w');
fwrite($fp, "<?php\n".$code."?>\n");
fclose($fp);
print "done\n";

function parse_doc($prefix, $doc) {
  $code = "";
  $words = split(' ', $doc);
  $line = $prefix;
  foreach($words as $word) {
    $line .= $word.' ';
    if( strlen($line) > 90 ) { // new line
      $code .= $line."\n";
      $line = $prefix;
    }
  }
  $code .= $line."\n";
  return $code;
}

/**
 * Look for enumeration
 * 
 * @param DOM $dom
 * @param string $class
 * @return array
 */
function checkForEnum(&$dom, $class) {
  $values = array();
  
  $node = findType($dom, $class);
  if(!$node) {
    return $values;
  }
  
  $value_list = $node->getElementsByTagName('enumeration');
  if($value_list->length == 0) {
    return $values;
  }

  for($i=0; $i<$value_list->length; $i++) {
    $values[] = $value_list->item($i)->attributes->getNamedItem('value')->nodeValue;
  }
  return $values;
}

/**
 * Look for a type
 * 
 * @param DOM $dom
 * @param string $class
 * @return DOMNode
 */
function findType(&$dom, $class) {
  $types_node  = $dom->getElementsByTagName('types')->item(0);
  $schema_list = $types_node->getElementsByTagName('schema');
  
  for ($i=0; $i<$schema_list->length; $i++) {
    $children = $schema_list->item($i)->childNodes;
    for ($j=0; $j<$children->length; $j++) {
      $node = $children->item($j);
      if ($node instanceof DOMElement &&
	  $node->hasAttributes() &&
	  $node->attributes->getNamedItem('name')->nodeValue == $class) {
	return $node;
      }
    }
  }
  return null;
}

function generatePHPSymbol($s) {
  global $reserved_keywords;
  
  if(!preg_match('/^[A-Za-z_]/', $s)) {
    $s = 'value_'.$s;
  }
  if(in_array(strtolower($s), $reserved_keywords)) {
    $s = '_'.$s;
  }
  return preg_replace('/[-.\s]/', '_', $s);
}


?>
