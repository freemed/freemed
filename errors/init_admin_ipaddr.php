<?php

print "
Hello Intrepid User!<BR>
            In order to access the freemed intialization, your web client must come from the <BR>
	    host defined in lib/settings.php. Normally that means that you must be on the same <BR>
            host that FreeMed is running on! (localhost). You must either change your host, or <BR>
   	    change the value found in settings.php to match your host!<BR><BR>

            SECURITY NOTE!!! The default is to limit access to this function to localhost. <BR>
            It is wise to leave this default alone. This function is capable of destroying the <BR>
            entire database and the value in settings.php will control future access to this function <BR>
            So if possible, go sit at the freemed box to do this intial configuration...<BR>";

?>
