<?php
  # file: image-handler.php3
  # desc: serves up images from base directories specified in /etc/image.conf
  # code: jeff b <jeff@univrel.pr.uconn.edu>
  # lic : GPL, v2

  include ("lib/freemed.php");
  $config_file = "./image.conf";

    // check if the configuration file exists
  if (!file_exists($config_file)) 
    DIE( "
      ERROR! $config_file MISSING!
    " );
 
    // open config file
  $f_config = fopen ($config_file, "r");

    // check to see if it opened properly
  if (!$f_config)
    DIE ("
      Could not open $config_file!
    ");

    // loop for config file, and load all directories and names
    // to dir_aliases array
  $counter=0;
  while ($f_line = fgets ($f_config, 255)) {
    if (!strpos ($f_line, "#")) {
        // after checking for a # in the line, parse that puppy
      $counter++;
      $magic = explode (":", $f_line);
      $dir_aliases[trim($magic[0])] = trim($magic[1]);
      $actual_aliases[$counter] = trim($magic[0]);
    } // if there isn't a # in the line .. end of loop 
  } // end while file loop 

    // be a good doobie, and close the config file
  $f_closehandle = fclose ($f_config);

    // now, check for a valid alias
  $valid_alias=false;
  for ($i=0;$i<=count($actual_aliases);$i++) {
    if ((strtolower($actual_aliases[$i]) == strtolower($loc)))
      $valid_alias=true;

  }
    // if not, error!
  if (!$valid_alias)
    DIE("
      Invalid location \"$loc\".
    ");

    // check for validity of location... even through links!
  if ( !is_dir($dir_aliases[$loc]) OR
       ( is_link($dir_aliases[$loc]) AND
        !is_dir(readlink($dir_aliases[$loc])) ))
    DIE("
      Directory pointed to by \"$loc\" does not exist.
    ");

    // now, derive entire file name
  $full_filename = $dir_aliases[$loc] . "/" . $image;

    // check for file
  if (!is_file ($full_filename))
    DIE("
      \"$full_filename\" does not exist on this system.
    ");

    // get the magic on the file
  $f_magic_full = `file $full_filename`;
  $f_magic_full_array = explode (":", $f_magic_full);
  $f_magic = $f_magic_full_array [1];

    // figure out the MIME type
  if (strpos ($f_magic, "GIF image data"))
    $mimetype="image/gif";
  elseif (strpos ($f_magic, "JPEG image data"))
    $mimetype="image/jpeg";
  elseif (strpos ($f_magic, "X pixmap image text"))
    $mimetype="image/x-xpixmap";
  elseif (strpos ($f_magic, "PNG image data"))
    $mimetype="image/png";
  elseif (strpos ($f_magic, "TIFF image data"))
    $mimetype="image/tiff";
  else DIE ("
      Unsupported image format!
    ");

    // now, show header and file
  Header ("Content-Type: $mimetype");
  $f_junkstuff = readfile ($full_filename);
?>
