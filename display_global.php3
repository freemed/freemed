<?
 # file: display_global.php3

echo "<B>GLOBAL ARRAY: </B> <P>";

for (reset($GLOBALS);$key = key($GLOBALS); next($GLOBALS)) {
  echo "GLOBALS[$key] = ".$GLOBALS[$key]." <BR>";
}

?>
