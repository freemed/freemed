<?php
 // $Id$
 // desc: script from px.skylar.com to generate custom buttons
 // code: Rasmus Lerdorf <rasmus@lerdorf.on.ca>
 // (http://www.phpbuilder.com/columns/rasmus19990124.php3)

Header( "Content-type: image/png" );

// Set size
if (!isset($size)) $size = 11;
$my_size = @ImageTTFBBox($size, 0, "./img/font.ttf", $text);
$dx = abs($my_size[2] - $my_size[0]);
$dy = abs($my_size[5] - $my_size[3]);
$xpad = 5;
$ypad = 5;

$im = ImageCreate($dx+$xpad, $dy+$ypad);
$blue  = ImageColorAllocate($im, 0x2c, 0x6D, 0xAF);
$black = ImageColorAllocate($im, 0x00, 0x00, 0x00);
$white = ImageColorAllocate($im, 0xFF, 0xFF, 0xFF);

ImageRectangle($im, 0, 0, $dx+$xpad-1, $dy+$ypad-1, $black);
ImageRectangle($im, 0, 0, $dx+$xpad, $dy+$ypad, $white);

@ImageTTFText($im, $size, 0, (int)($xpad/2)+1, $dy+(int)($ypad/2), $black,
	"./img/font.ttf", $text);

@ImageTTFText($im, $size, 0, (int)($xpad/2), $dy+(int)($ypad/2)-1, $white,
	"./img/font.ttf", $text);

// Show and destroy image
ImagePng( $im );
ImageDestroy( $im );

?>
