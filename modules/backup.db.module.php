<?php
  // $Id$
  // note: backup the freemed database
  // code: fred forester fforest@netcarrier.com
  // lic : GPL, v2

if (!defined("__BACKUP_MAINTENANCE_MODULE_PHP__")) {

define (__BACKUP_MAINTENANCE_MODULE_PHP__, true);

class BackupMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME = "Backup Database";
	var $MODULE_VERSION = "0.1";


	function BackupMaintenance () {
		// run constructor
		$this->freemedMaintenanceModule();
		//$this->set_icon("img/kfloppy.gif");
	} // end constructor BackupMaintenance	

	function form()
	{
	} // end form

	function view () {
		global $sql,$_auth,$module,$STDFONT_B,$STDFONT_E;

		$file = DB_NAME.".".gmdate(Ymdhis).".txt";
		$tmpfile = "/tmp/".$file;
		$gpgfile = "/tmp/".$file.".gpg";
		$httpfile = "/bills/".$file;
		echo "file is $file<BR>";

		$passphrase = GPG_PASSPHRASE_LOCATION;
		$homedir = GPG_HOME;

		$dumpcmd = "mysqldump -c --add-drop-table --user=".DB_USER." --password=".DB_PASSWORD." ".DB_NAME." >$tmpfile";
		//$gpgcmd = "echo $passphrase | gpg --homedir=$homedir --passphrase-fd 0 --output $gpgfile  --symmetric $tmpfile";
		$gpgcmd = "gpg --homedir $homedir --batch --passphrase-fd 0 --output $gpgfile  --symmetric $tmpfile < $passphrase";
		echo "gpg $gpgcmd<BR>";

		//echo "cmd is $cmd<BR>";
		system($dumpcmd);

		if (file_exists($tmpfile))
		{
			echo "Backup completed<BR>";
			if (USE_GPG=="YES")
			{
				system($gpgcmd);
				if (file_exists($gpgfile))
				{
					echo "Encryption completed to $gpgfile<BR>";
					// NOTE you want to check for an admin user before displaying this 
					// downloadable link
					$httpfile = $httpfile.".gpg";
					echo "Wrote Encrypted Backup to <A HREF=\"$httpfile\">$httpfile</A><BR>";
					unlink($tmpfile);
				}
				else
					echo "Error - gpg failed for $gpgfile<BR>";
			}
			else
			{
					echo "Wrote Backup to <A HREF=\"$httpfile\">$httpfile</A><BR>";
				

			}
			
		}
		else
			echo "Error - dump failed for $file<BR>";
		
		echo "
			<$STDFONT_E>
			<P>
			<CENTER>
			<A HREF=\"db_maintenance.php?$_auth\"
			 ><$STDFONT_B>"._("Return to Maintenance Menu")."<$STDFONT_E></A>
			</CENTER>
			<P>
		";

		return;

	} // end function BackupMaintenance->View

} // end of class BackupMaintenance

register_module ("BackupMaintenance");

} // end of "if defined"

?>
