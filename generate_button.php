<?php
 // $Id$
 // desc: script from px.skylar.com to generate custom buttons
 // code: Rasmus Lerdorf <rasmus@lerdorf.on.ca>

Header( "Content-type: image/gif" );
$string = implode( $argv, " " );
$im = ImageCreateFromGif( "img/button-50x30.gif" );
$orange = ImageColorAllocate($im, 220, 210, 60);
$px = ( imagesx($im) - 7.5 * strlen($string) ) / 2;
ImageString( $im, 3, $px, 8, $string, $orange );
ImageGif( $im );
ImageDestroy( $im );

?>
