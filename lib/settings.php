<?php
 // $Id$
 // $Author$

define ('INSTALLATION', "Stock Freemed Install"); // installation name
define ('DB_HOST', "localhost"); // database (SQL) host location
define ('DB_NAME', "freemed"); // database name
define ('DB_USER', "root"); // SQL server username
define ('DB_PASSWORD', "password");		// SQL server password
define ('PHYSICAL_LOCATION', "/usr/share/freemed");
define ('PATID_PREFIX', "PAT"); // used to generate internal practice ID
define ('BUG_TRACKER', false); // set bug tracker on or off
define ('TEMPLATE', "default");	// set default template
define ('HOST', 'localhost'); // host name for this system
define ('BASE_URL', '/freemed'); // offset (i.e. http://here/package)
define ('HTTP', 'http'); // http for normal, https for SSL
define ('SESSION_PROTECTION', true); // strong session protection?
$default_language="EN"; // default language

    // GPG settings
    //
    // customize if you are using the db backup maintenance module with
    // pgp. for keyring, you need to as root create /home/nobody,
    // chown nobody:nobody /home/nobody
    // su nobody
    // export HOME=/home/nobody; cd $HOME
    // use GPG to encrypt a file, run it twice
    // you should now have /home/nobody/.gpg

define ('USE_GPG', false);	// encrypt backups? (true/false)
define ('GPG_PASSPHRASE_LOCATION', PHYSICAL_LOCATION.'/lib/gpg_phrase.php');
define ('GPG_HOME', "/home/nobody");

?>
