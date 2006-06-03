<?php
 // $Id$
 // $Author$
 //
 // Most of the code (or the ideas therein) were taken from the
 // phpgroupware project, at http://www.phpgroupware.org/. They
 // have done an *excellent* job.

function CreateApplicationMap($map) {
	// Catch any attempts to not pass an array
	if (!is_array($map)) return false;

	// Check to see if we have a map to add to (or not)
	if (isset($GLOBALS['__phpwebtools']['app_map'])) {
		$GLOBALS['__phpwebtools']['app_map'] = array_merge($GLOBALS['__phpwebtools']['app_map'], $map);
	} else {
		$GLOBALS['__phpwebtools']['app_map'] = $map;
	}
} // end function CreateApplicationMap

function CreateObject($class,
		$p1 = '_UNDEF_',
		$p2 = '_UNDEF_',
		$p3 = '_UNDEF_',
		$p4 = '_UNDEF_',
		$p5 = '_UNDEF_',
		$p6 = '_UNDEF_',
		$p7 = '_UNDEF_',
		$p8 = '_UNDEF_',
		$p9 = '_UNDEF_',
		$p10 = '_UNDEF_',
		$p11 = '_UNDEF_',
		$p12 = '_UNDEF_',
		$p13 = '_UNDEF_',
		$p14 = '_UNDEF_',
		$p15 = '_UNDEF_',
		$p16 = '_UNDEF_') {
	list($appname, $classname) = explode('.', $class);

	if (!isset($GLOBALS['__phpwebtools']['included_classes']["$classname"]) or
			!$GLOBALS['__phpwebtools']['included_classes']["$classname"]) {

		$path = ResolveObjectPath($class);

		// Check for existing class, then include proper file
		if (@file_exists($path)) {
			if (!class_exists($classname)) { 
				include_once($path); 
			}
			$GLOBALS['__phpwebtools']['included_classes']["$classname"] = True;
		} else {
			$GLOBALS['__phpwebtools']['included_classes']["$classname"] = False;
		} // end of including

	}

	if ($GLOBALS['__phpwebtools']['included_classes']["$classname"]) {
		if (($p1 == '_UNDEF_') and ($p1 != 1)) {
			eval(' $obj = new '.$classname.';');
		} else {
			$input = array ($p1,$p2,$p3,$p4,
					$p5,$p6,$p7,$p8,
					$p9,$p10,$p11,$p12,
					$p13,$p14,$p15,$p16);
			$i = 1;
			$code = '$obj = new '.$classname.'(';
			while (list($x, $test) = each ($input)) {
				if (($test=='_UNDEF_' && $test != 1) || $i == 17) {
					break;
				} else {
					$code .= '$p'.$i.',';
				}
				$i++;	
			}
			// Remove trailing ',' and finish
			$code = substr($code,0,-1) . ');';

			// Evaluate
			eval($code);
		}
	}
	return $obj;
} // end function CreateObject

function ExecuteMethod($class,
		$p1 = '_UNDEF_',
		$p2 = '_UNDEF_',
		$p3 = '_UNDEF_',
		$p4 = '_UNDEF_',
		$p5 = '_UNDEF_',
		$p6 = '_UNDEF_',
		$p7 = '_UNDEF_',
		$p8 = '_UNDEF_',
		$p9 = '_UNDEF_',
		$p10 = '_UNDEF_',
		$p11 = '_UNDEF_',
		$p12 = '_UNDEF_',
		$p13 = '_UNDEF_',
		$p14 = '_UNDEF_',
		$p15 = '_UNDEF_',
		$p16 = '_UNDEF_') {
	list($appname, $classname, $method) = explode('.', $class);

	if (!isset($GLOBALS['__phpwebtools']['included_classes'][$classname]) or
			!$GLOBALS['__phpwebtools']['included_classes'][$classname]) {

		$path = ResolveObjectPath($class);

		// Check for existing class, then include proper file
		if (@file_exists($path)) {
			if (!class_exists($classname)) { include($path); }
			$GLOBALS['__phpwebtools']['included_classes'][$classname] = True;
		} else {
			$GLOBALS['__phpwebtools']['included_classes'][$classname] = False;
		} // end of including

	}

	if ($GLOBALS['__phpwebtools']['included_classes'][$classname]) {
		if (($p1 == '_UNDEF_') and ($p1 != 1)) {
			//print(' $obj = '.$classname.'::'.$method.';');
			eval(' $obj = '.$classname.'::'.$method.';');
		} else {
			$input = array ($p1,$p2,$p3,$p4,
					$p5,$p6,$p7,$p8,
					$p9,$p10,$p11,$p12,
					$p13,$p14,$p15,$p16);
			$i = 1;
			$code = '$obj = '.$classname.'::'.$method.'(';
			while (list($x, $test) = each ($input)) {
				if (($test=='_UNDEF_' && $test != 1) || $i == 17) {
					break;
				} else {
					$code .= '$p'.$i.',';
				}
				$i++;	
			}
			// Remove trailing ',' and finish
			$code = substr($code,0,-1) . ');';

			// Evaluate
			//print($code."\n");
			eval($code);
		}
	}
	return $obj;
} // end function ExecuteMethod

function ExecuteMethodArray($class, $params) {
	list($appname, $classname, $method) = explode('.', $class);

	if (!isset($GLOBALS['__phpwebtools']['included_classes'][$classname]) or
			!$GLOBALS['__phpwebtools']['included_classes'][$classname]) {

		$path = ResolveObjectPath($class);

		// Check for existing class, then include proper file
		if (@file_exists($path)) {
			include_once($path);		
			$GLOBALS['__phpwebtools']['included_classes'][$classname] = True;
		} else {
			$GLOBALS['__phpwebtools']['included_classes'][$classname] = False;
		} // end of including

	}

	if ($GLOBALS['__phpwebtools']['included_classes'][$classname]) {
		$cmd = '$obj = '.$classname.'::'.$method.'(';
		if (count($params)>0) {
			for ($i=0;$i<count($params);$i++) {
				$cmd .= '$params['.$i.'],';
			}
			$cmd = substr($cmd, 0, -1);
		}
		$cmd .= ');';
		#$fp = @fopen("/tmp/mylog", "w+");
		#@fprintf($fp, "DEBUG, command = $cmd\n");
		#@fclose($fp);
		eval($cmd);
	}
	return $obj;

} // end function ExecuteMethodArray

function LoadObjectDependency($object) {
	$path = ResolveObjectPath($object);
	list ($__garbage, $classname) = explode('.', $object);

	// Check for existing class, then include proper file
	if (@file_exists($path)) {
		//print "including path ($path)<BR>\n";
		//print "<br/>";print_r($GLOBALS['__phpwebtools']['included_classes'])."<br/>\n";
		include_once($path);
		$GLOBALS['__phpwebtools']['included_classes'][$classname] = True;
	} else {
		$GLOBALS['__phpwebtools']['included_classes'][$classname] = False;
	} // end of including
} // end function LoadObjectDependency

function MethodAvailable($class) {
	list($appname, $classname, $method) = explode('.', $class);

	if (!isset($GLOBALS['__phpwebtools']['included_classes'][$classname]) or
			!$GLOBALS['__phpwebtools']['included_classes'][$classname]) {

		$path = ResolveObjectPath($class);

		// Check for existing class, then include proper file
		if (@file_exists($path)) {
			include_once($path);		
			$GLOBALS['__phpwebtools']['included_classes'][$classname] = True;
		} else {
			$GLOBALS['__phpwebtools']['included_classes'][$classname] = False;
		} // end of including

	}

	// Check if class exists
	if (!class_exists($classname)) {
		return false;
	}
	foreach(get_class_methods($classname) as $__garbage => $v) {
		if (strtolower($v) == strtolower($method)) {
			return true;
		}
	}
	// Otherwise, return false
} // end function MethodAvailable

function ResolveObjectPath($object) {
	list($appname, $classname) = explode('.', $object);
	switch($appname) {
		case 'PHP':
		$path = WEBTOOLS_ROOT.'/class.'.$classname.'.php';
		break;

		case 'PEAR':
		// Resolve path name. PEAR packages use
		// _ as a path delimeter....
		ini_set('include_path', ini_get('include_path').':'.dirname(dirname(__FILE__)).'/pear');
		$my_class = str_replace('_','/',$classname);
		$path = dirname(dirname(__FILE__)).'/pear/'.$my_class.'.php';
		return $path;
		break;

		default:
		// Check in 'map'
		if (is_array($GLOBALS['__phpwebtools']['app_map'])) {
			foreach ($GLOBALS['__phpwebtools']['app_map'] AS $k => $v) {
				if ($k == $appname) {
					$path = str_replace('*', $classname, $v);
					break;
				}
			}
		}

		// Otherwise, use default
		if (!isset($path)) {
			$path = 'lib/class.'.$classname.'.php';
		}
		break;
	} // end switch for appname

	return $path;
} // end function ResolveObjectPath

?>
