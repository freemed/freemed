<?php
 // $Id$
 // $Author$
 // lic : GPL

 // obligatory initialization garbage
$page_name = "patient_image_handler.php";
include ("lib/freemed.php");
define ('RESIZE', 800);

 // authenticate user cookie
freemed_open_db ();

//----- Clean all variables
$patient = freemed::secure_filename($patient);
$id      = freemed::secure_filename($id     );

//----- Assemble proper file name
$imagefilename = "img/store/".$patient.".".$id.".djvu";

//----- Use browser detect to determine what kind of image this should be
$browser = CreateObject('PHP.browser_detect');
$type = "djvu";
if (!freemed::support_djvu($browser)) { $type = "jpeg"; }

switch ($type) {
	case "jpeg":
	// Create temporary file name
	$tempname = tempnam("/tmp", "fmjpeg");

	//----- Load image or form error string if unloadable
	$image = @ImageCreateFromJpeg($imagefilename);
	if (!$image) {
		$im = ImageCreate(150, 30);
		$bgc = ImageColorAllocate($im, 255, 255, 255);
		$tc  = ImageColorAllocate($im, 0, 0, 0);
		ImageFilledRectangle($im, 0, 0, 150, 30, $bgc);
		ImageString($im, 1, 5, 5, "Error loading $id", $tc);
	} else {
		// Check to see if it's over 600 pixels wide
		if ((ImageSX($image) > RESIZE) and !$no_resize) {
			$old = $image;
			$image = @ImageCreate(RESIZE, ((int) RESIZE * (ImageSY($old) / ImageSX($old) )));
			ImageCopyResized($image, $old, 0, 0, 0, 0,
				RESIZE, ((int) RESIZE * (ImageSY($old) / ImageSX($old) )),
				ImageSX($old), ImageSY($old)
			);
		}
	}

	// display header for content type
	Header ("Content-Type: image/jpeg");

	// display the actual image data
	ImageJpeg($image, '', 20);
	ImageDestroy($image);
	break; // end jpeg

	case "djvu": default:
	Header ("Content-Type: image/x.djvu");
	readfile($imagefilename);
} // end switch for image type

?>
