<?php
  // $Id$
  // note: backup the freemed database
  // code: fred forester fforest@netcarrier.com
  // lic : GPL, v2

LoadObjectDependency('FreeMED.MaintenanceModule');

class BackupMaintenance extends MaintenanceModule {

	var $MODULE_NAME = "Backup Database";
	var $MODULE_AUTHOR = "Fred Forester (fforest@netcarrier.com)";
	var $MODULE_VERSION = "0.2";
	var $MODULE_FILE = __FILE__;

	var $PACKAGE_MINIMUM_VERSION = '0.6.0';

	function BackupMaintenance () {
		// Path in configuration
		$this->_SetMetaInformation('global_config_vars', array (
			'bupath'
		));
		$this->_SetMetaInformation('global_config', array (
			__("Backup Path") =>
			'html_form::text_widget("bupath", 30, 100)'
		));
		
		// Run Constructor
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
		$display_buffer .= "file is $file<br/>\n";

		$passphrase = GPG_PASSPHRASE_LOCATION;
		$homedir = GPG_HOME;

		$dumpcmd = "mysqldump -c --add-drop-table --user=".DB_USER." --password=".DB_PASSWORD." ".DB_NAME." >$tmpfile";
		//$gpgcmd = "$display_buffer .= $passphrase | gpg --homedir=$homedir --passphrase-fd 0 --output $gpgfile  --symmetric $tmpfile";
		$gpgcmd = "gpg --homedir $homedir --batch --passphrase-fd 0 --output $gpgfile  --symmetric $tmpfile < $passphrase";
		$display_buffer .= "gpg $gpgcmd<br/>\n";

		//$display_buffer .= "cmd is $cmd<br/>\n";
		system($dumpcmd);

		if (file_exists($tmpfile))
		{
			$display_buffer .= __("Backup completed")."<br/>\n";
			if (USE_GPG)
			{
				system($gpgcmd);
				if (file_exists($gpgfile))
				{
					$display_buffer .= __("Encryption completed to")." $gpgfile<br/>\n";
					// NOTE you want to check for an admin user before displaying this 
					// downloadable link
					$httpfile = $httpfile.".gpg";
					$display_buffer .= __("Wrote Encrypted Backup to ")."<a HREF=\"$httpfile\">$httpfile</a><br/>\n";
					unlink($tmpfile);
				}
				else
					$display_buffer .= "Error - gpg failed for $gpgfile<br/>\n";
			}
			else
			{
				$display_buffer .= __("Wrote Backup to ")."<a HREF=\"$httpfile\">$httpfile</a><br/>\n";
				
			}
		}
		else
			$display_buffer .= "Error - dump failed for $file<br/>\n";
		
		$display_buffer .= "
			<p/>
			<div align=\"CENTER\">
			<a class=\"button\" HREF=\"db_maintenance.php\"
			 >".__("Return to Maintenance Menu")."</a>
			</div>
			<p/>
		";

		return;

	} // end function BackupMaintenance->View

} // end of class BackupMaintenance

register_module ("BackupMaintenance");

?>
