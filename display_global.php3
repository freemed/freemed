<?
 # file: display_global.php3

echo "<B>GLOBAL ARRAY: </B> <P>";

$count = 1;
for (reset($GLOBALS);$key = key($GLOBALS); next($GLOBALS)) {
  echo "$count / GLOBALS[$key] = ".$GLOBALS[$key]." <BR>";
  $count++;
}

?>
