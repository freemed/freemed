<?php
  // $Id$
  // note: backup the freemed database
  // code: fred forester fforest@netcarrier.com
  // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class BackupMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Backup Database";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.1";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function BackupMaintenance () {
		// run constructor
		$this->MaintenanceModule();
		//$this->set_icon("img/kfloppy.gif");
	} // end constructor BackupMaintenance	

	function form() { } // end form

	function view () {
		global $display_buffer;
		global $sql,$module;

		$file = DB_NAME.".".gmdate(Ymdhis).".txt";
		$tmpfile = "/tmp/".$file;
		$gpgfile = "/tmp/".$file.".gpg";
		$httpfile = "/bills/".$file;
		$display_buffer .= "file is $file<BR>";

		$passphrase = GPG_PASSPHRASE_LOCATION;
		$homedir = GPG_HOME;

		$dumpcmd = "mysqldump -c --add-drop-table --user=".DB_USER." --password=".DB_PASSWORD." ".DB_NAME." >$tmpfile";
		//$gpgcmd = "$display_buffer .= $passphrase | gpg --homedir=$homedir --passphrase-fd 0 --output $gpgfile  --symmetric $tmpfile";
		$gpgcmd = "gpg --homedir $homedir --batch --passphrase-fd 0 --output $gpgfile  --symmetric $tmpfile < $passphrase";
		$display_buffer .= "gpg $gpgcmd<BR>";

		//$display_buffer .= "cmd is $cmd<BR>";
		system($dumpcmd);

		if (file_exists($tmpfile))
		{
			$display_buffer .= "Backup completed<BR>";
			if (USE_GPG)
			{
				system($gpgcmd);
				if (file_exists($gpgfile))
				{
					$display_buffer .= "Encryption completed to $gpgfile<BR>";
					// NOTE you want to check for an admin user before displaying this 
					// downloadable link
					$httpfile = $httpfile.".gpg";
					$display_buffer .= "Wrote Encrypted Backup to <A HREF=\"$httpfile\">$httpfile</A><BR>";
					unlink($tmpfile);
				}
				else
					$display_buffer .= "Error - gpg failed for $gpgfile<BR>";
			}
			else
			{
					$display_buffer .= "Wrote Backup to <A HREF=\"$httpfile\">$httpfile</A><BR>";
				

			}
			
		}
		else
			$display_buffer .= "Error - dump failed for $file<BR>";
		
		$display_buffer .= "
			<P>
			<CENTER>
			<A HREF=\"db_maintenance.php\"
			 >".__("Return to Maintenance Menu")."</A>
			</CENTER>
			<P>
		";

		return;

	} // end function BackupMaintenance->View

} // end of class BackupMaintenance

register_module ("BackupMaintenance");

?>
