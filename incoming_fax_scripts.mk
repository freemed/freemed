From freemed-project-admin@kippona-com  Thu Sep 16 21:00:32 1999
Return-Path: <freemed-project-admin@kippona.com>
Received: from susquehanna.kippona.com (root@susquehanna.kippona.com [208.0.85.113])
	by univrel.pr.uconn.edu (8.8.7/8.8.7) with ESMTP id VAA30132
	for <jeff@univrel.pr.uconn.edu>; Thu, 16 Sep 1999 21:00:02 -0400
Received: from susquehanna.kippona.com (nobody@localhost [127.0.0.1])
	by susquehanna.kippona.com (8.9.0/8.9.0) with SMTP id AAA09553;
	Fri, 17 Sep 1999 00:57:26 GMT
Received: from mail.span.ch (mail.span.ch [194.51.197.241])
	by susquehanna.kippona.com (8.9.0/8.9.0) with ESMTP id AAA09529
	for <freemed-project@kippona.com>; Fri, 17 Sep 1999 00:57:01 GMT
Received: from asmodaeus.trauco (mp2-c86-p70.span.ch [195.15.86.70])
	by mail.span.ch (8.9.3/8.9.3) with ESMTP id CAA24740;
	Fri, 17 Sep 1999 02:56:34 +0200 (MET DST)
Received: from asmodaeus (IDENT:max@localhost [127.0.0.1])
	by asmodaeus.trauco (8.9.3/8.8.7) with SMTP id DAA01161;
	Fri, 17 Sep 1999 03:24:16 +0200
From: Max Klohn <amk@span.ch>
Reply-To: amk@span.ch
To: jeff b <jeff@univrel.pr.uconn.edu>
Subject: [F-p] Incoming fax scripts for freemed [long]
Date: Fri, 17 Sep 1999 03:12:54 +0200
X-Mailer: KMail [version 1.0.21]
Content-Type: text/plain
Cc: freemed-project@kippona.com, dr_gnu@stangelove.dyndns.org
MIME-Version: 1.0
Message-Id: <99091703241606.31638@asmodaeus>
Content-Transfer-Encoding: 8bit
Sender: freemed-project-admin@kippona.com
Errors-To: freemed-project-admin@kippona.com
X-Mailman-Version: 1.0b9
Precedence: bulk
List-Id: public freemed list <freemed-project.susquehanna.kippona.com>
X-BeenThere: freemed-project@susquehanna.kippona.com

Hi,
I have been working on dealing with incoming faxes (still VERY MUCH used in the
medical profession) in different ways (printing, sending them as tifs,
archiving them with MySQL...).
I see Freemed as a data concentrator for the physician, obviously it should be
able to handle this. So here is the beginning, the shell script, the module for
freemed has yet to be written...
I'd appreciate if any of you can try and further test/improve those scripts. As
part of the freemed documentation, I wrote a short digest on how to get
mgetty+sendfax receiving faxes.

Greetings to all,

Max


---------------------------------------------------------
Configuration of a Fax ---> mail, print and mysql archive
v. 0.04
September 17, 1999 Max Klohn <amk@span.ch>
This document is under the GPL

Objective: to have a functional conversion of incoming faxes into e-mail to a given user, print to a laserjet, and archiving into a Mysql/freemed database.

We will deliberately not treat the outgoing faxes aspects.

-------------------------------------------
What you need:

- a gnu/linux distribution in working order
- a mail transfer agent (mta), configured and in working order: Sendmail, Qmail, Postfix... It should at least be able to send and receive mail to local users.
- a fax-modem (NOT a winmodem), installed and recognized, let's say, on com2 (/dev/ttyS1)
- the "mgetty+sendfax" package
-the libgr conversion utilities package (for RH and similars it is in a libgr-progs-XXXX rpm

-------------------------------------------
Recommended reading:

/usr/doc/mgettyXXX

man mgetty

look also into /etc/mgetty+sendfax

-------------------------------------------
The config files:

/etc/mgetty+sendfax/dialin.config
useful for those who have caller ID service and want to allow/banish calling numbers.

/etc/mgetty+sendfax/fax.allow
users authorized to send outgoing faxes. Not our business here.

/etc/mgetty+sendfax/faxheader
header fro the outgoing faxes. Same as above.

/etc/mgetty+sendfax/mgetty.config
Here we are:

................................

# debug level for your connexions
debug 4   

# ID for your fax station 
# (usually the number you whish to have incoming faxes sent)

fax-id +66 66 666 66 66    

# use 115200 bps between computer and modem (you all have 16650 UARTS by now?) 
speed 115200  

# who to notify

notify max@telecom.pmc
                         
# specific port options

port ttyS1

# I want a lot of debug messages

  debug 8

# modem class (you can see it from the mgetty negotiation logs)

modem-type cls2        

...................................
That's all, Mgetty is a very clever piece of software and does a lot
of auto-configuration.

Further config:

-have a look at the inittab doc [man inittab]
-make a backup copy of inittab.
-add a line for mgetty:
................................................

# Launch mgetty on the fax-modem
S1:2345:respawn:/sbin/mgetty -F -x3 /dev/ttyS1    

................................................

S1: unique identificator. Chose something not already in inittab.

2345: the runlevels you want it to run on

/sbin/mgetty: 'whereis mgetty' if you've got a doubt...

-F: Fax only, no incoming data calls

/dev/ttyS1: or where your preferred fax-modem is connected.

-------------------------------------------------------
Launch all this:
as root,

/sbin/init q

... and if your system is still alive 8-9, you should see a mgetty process running now:

ps ax | grep mgetty

you should get something like:

 5716 ?        SW     0:00 [mgetty]
 6264 pts/0    S      0:00 grep mgetty  

Let's go have a look at the logs

less /var/log/mgetty.log.ttyS1


09/08 01:59:59 yS1  mgetty: experimental test release 1.1.14-Apr02
09/08 01:59:59 yS1   mgetty.c compiled at May 11 1999, 23:24:43
09/08 01:59:59 yS1   user id: 0, parent pid: 1
09/08 01:59:59 yS1   reading configuration data for port 'ttyS1'
09/08 01:59:59 yS1   reading /etc/mgetty+sendfax/mgetty.config...
09/08 01:59:59 yS1   conf lib: read: 'debug 4'
09/08 01:59:59 yS1   conf lib: read: 'fax-id 66 66 6666666'
09/08 01:59:59 yS1   conf lib: read: 'speed 115200'
09/08 01:59:59 yS1   conf lib: read: 'notify max@telecom.pmc'
09/08 01:59:59 yS1   conf lib: read: 'port ttyS1'
09/08 01:59:59 yS1   section: port ttyS1, **found**
09/08 01:59:59 yS1   conf lib: read: 'debug 8'
09/08 01:59:59 yS1   key: 'speed', type=0, flags=3, data=115200
09/08 01:59:59 yS1   key: 'switchbd', type=0, flags=1, data=0
09/08 01:59:59 yS1   key: 'direct', type=3, flags=1, data=FALSE
09/08 01:59:59 yS1   key: 'blocking', type=3, flags=1, data=FALSE
09/08 01:59:59 yS1   key: 'port-owner', type=1, flags=1, data=uucp
09/08 01:59:59 yS1   key: 'port-group', type=1, flags=1, data=uucp
09/08 01:59:59 yS1   key: 'port-mode', type=0, flags=1, data=432
09/08 01:59:59 yS1   key: 'toggle-dtr', type=3, flags=1, data=TRUE
09/08 01:59:59 yS1   key: 'toggle-dtr-waittime', type=0, flags=1, data=500
09/08 01:59:59 yS1   key: 'data-only', type=3, flags=1, data=FALSE
09/08 01:59:59 yS1   key: 'fax-only', type=3, flags=2, data=TRUE
09/08 01:59:59 yS1   key: 'modem-type', type=1, flags=1, data=auto
09/08 01:59:59 yS1   key: 'modem-quirks', type=0, flags=0, data=(empty)
09/08 01:59:59 yS1   key: 'init-chat', type=2, flags=1, data= \dATQ0V1H0 OK
ATS0=0Q0&D3&C1 OK
09/08 01:59:59 yS1   key: 'force-init-chat', type=2, flags=1, data=
\d^P^C\d\d\d+++\d\d\d^M\dATQ0V1H0 OK
09/08 01:59:59 yS1   key: 'modem-check-time', type=0, flags=1, data=3600
09/08 01:59:59 yS1   key: 'rings', type=0, flags=1, data=1
09/08 01:59:59 yS1   key: 'get-cnd-chat', type=2, flags=0, data=(empty)
09/08 01:59:59 yS1   key: 'answer-chat', type=2, flags=1, data= ATA CONNECT \c  
09/08 01:59:59 yS1   key: 'answer-chat-timeout', type=0, flags=1, data=80
09/08 01:59:59 yS1   key: 'autobauding', type=3, flags=1, data=FALSE
09/08 01:59:59 yS1   key: 'ringback', type=3, flags=1, data=FALSE
09/08 01:59:59 yS1   key: 'ringback-time', type=0, flags=1, data=30
09/08 01:59:59 yS1   key: 'ignore-carrier', type=3, flags=1, data=FALSE
09/08 01:59:59 yS1   key: 'issue-file', type=1, flags=1, data=/etc/issue
09/08 01:59:59 yS1   key: 'prompt-waittime', type=0, flags=1, data=500
09/08 01:59:59 yS1   key: 'login-prompt', type=1, flags=1, data=@ login:
09/08 01:59:59 yS1   key: 'login-time', type=0, flags=1, data=240
09/08 01:59:59 yS1   key: 'fido-send-emsi', type=3, flags=1, data=TRUE
09/08 01:59:59 yS1   key: 'fax-id', type=1, flags=3, data=41 22 3457534
09/08 01:59:59 yS1   key: 'fax-server-file', type=1, flags=0, data=(empty)
09/08 01:59:59 yS1   key: 'diskspace', type=0, flags=1, data=1024
09/08 01:59:59 yS1   key: 'notify', type=1, flags=3, data=max@telecom.pmc
09/08 01:59:59 yS1   key: 'fax-owner', type=1, flags=1, data=uucp
09/08 01:59:59 yS1   key: 'fax-group', type=1, flags=0, data=(empty)
09/08 01:59:59 yS1   key: 'fax-mode', type=0, flags=1, data=432
09/08 01:59:59 yS1   key: 'debug', type=0, flags=2, data=8
09/08 01:59:59 yS1   key: 'statistics-chat', type=2, flags=0, data=(empty)
09/08 01:59:59 yS1   key: 'statistics-file', type=1, flags=0, data=(empty)
09/08 01:59:59 yS1   key: 'gettydefs', type=1, flags=1, data=n
09/08 01:59:59 yS1   key: 'term', type=1, flags=0, data=(empty)
09/08 01:59:59 yS1  check for lockfiles
09/08 01:59:59 yS1   checklock: stat failed, no file
09/08 01:59:59 yS1  locking the line
09/08 01:59:59 yS1   makelock(ttyS1) called
09/08 01:59:59 yS1   do_makelock: lock='/var/lock/LCK..ttyS1'
09/08 01:59:59 yS1   lock made
09/08 01:59:59 yS1   tio_get_rs232_lines: status: RTS CTS DTR
09/08 01:59:59 yS1  WARNING: DSR is off - modem turned off or bad cable?
09/08 01:59:59 yS1  lowering DTR to reset Modem
09/08 02:00:00 yS1   tss: set speed to 115200 (10002)
09/08 02:00:00 yS1   tio_set_flow_control( HARD )
09/08 02:00:00 yS1   waiting for line to clear (VTIME), read:
09/08 02:00:00 yS1  send: \dATQ0V1H0[0d]
09/08 02:00:01 yS1  waiting for ``OK''
09/08 02:00:01 yS1   got: ATQ0V1H0[0d]
09/08 02:00:01 yS1    CND: ATQ0V1H0[0d][0a]OK ** found **
09/08 02:00:01 yS1  send: ATS0=0Q0&D3&C1[0d]            
09/08 02:00:01 yS1  waiting for ``OK''
09/08 02:00:01 yS1   got: [0d]
09/08 02:00:01 yS1    CND: OK[0a]ATS0=0Q0&D3&C1[0d]
09/08 02:00:01 yS1    CND: ATS0=0Q0&D3&C1[0d][0a]OK ** found **
09/08 02:00:01 yS1  mdm_send: 'ATI'
09/08 02:00:01 yS1    got:[0d][0a]ATI[0d]
09/08 02:00:01 yS1    got:[0d][0a]247[0d]
09/08 02:00:01 yS1   mdm_gis: string 1: '247'
09/08 02:00:01 yS1    got:[0a][0d][0a]OK[0d]
09/08 02:00:01 yS1   mdm_identify: string '247'
09/08 02:00:01 yS1  Multitech MT1432BA/MT1932ZDX/MT2834ZDX detected
09/08 02:00:01 yS1  mdm_send: 'ATI2'
09/08 02:00:01 yS1    got:[0a]ATI2[0d]
09/08 02:00:01 yS1    got:[0d][0a]MT2834ZDXI[0d]
09/08 02:00:02 yS1   mdm_gis: string 1: 'MT2834ZDXI'
09/08 02:00:02 yS1    got:[0a][0d][0a]OK[0d]
09/08 02:00:02 yS1  additional info: 'MT2834ZDXI'
09/08 02:00:02 yS1  modem quirks: 0002
09/08 02:00:02 yS1  mdm_send: 'AT+FCLASS=2.0'
09/08 02:00:02 yS1    got:[0a]AT+FCLASS=2.0[0d]
09/08 02:00:02 yS1   mdm_command: string 'AT+FCLASS=2.0'
09/08 02:00:02 yS1    got:[0d][0a]ERROR[0d]
09/08 02:00:02 yS1   mdm_command: string 'ERROR' -> ERROR
09/08 02:00:02 yS1  mdm_send: 'AT+FCLASS=2.0'
09/08 02:00:02 yS1    got:[0a]AT+FCLASS=2.0[0d]
09/08 02:00:02 yS1   mdm_command: string 'AT+FCLASS=2.0'
09/08 02:00:02 yS1    got:[0d][0a]ERROR[0d]
09/08 02:00:02 yS1   mdm_command: string 'ERROR' -> ERROR         
09/08 02:00:02 yS1  mdm_send: 'AT+FCLASS=2'
09/08 02:00:02 yS1    got:[0a]AT+FCLASS=2[0d]
09/08 02:00:02 yS1   mdm_command: string 'AT+FCLASS=2'
09/08 02:00:02 yS1    got:[0d][0a]OK[0d]
09/08 02:00:02 yS1   mdm_command: string 'OK' -> OK
09/08 02:00:02 yS1  mdm_send: 'AT+FAA=0;+FCR=1'
09/08 02:00:02 yS1    got:[0a]AT+FAA=0;+FCR=1[0d]
09/08 02:00:02 yS1   mdm_command: string 'AT+FAA=0;+FCR=1'
09/08 02:00:02 yS1    got:[0d][0a]OK[0d]
09/08 02:00:02 yS1   mdm_command: string 'OK' -> OK
09/08 02:00:03 yS1  mdm_send: 'AT+FBOR=0'
09/08 02:00:03 yS1    got:[0a]AT+FBOR=0[0d]
09/08 02:00:03 yS1   mdm_command: string 'AT+FBOR=0'
09/08 02:00:03 yS1    got:[0d][0a]OK[0d]
09/08 02:00:03 yS1   mdm_command: string 'OK' -> OK
09/08 02:00:03 yS1  mdm_send: 'AT+FLID="66 66 6666666"'
09/08 02:00:03 yS1    got:[0a]AT+FLID="66 66 6666666"[0d]
09/08 02:00:03 yS1   mdm_command: string 'AT+FLID="66 66 6666666"'
09/08 02:00:03 yS1    got:[0d][0a]OK[0d]
09/08 02:00:03 yS1   mdm_command: string 'OK' -> OK
09/08 02:00:03 yS1  mdm_send: 'AT+FDCC=1,5,0,2,0,0,0,0'
09/08 02:00:03 yS1    got:[0a]AT+FDCC=1,5,0,2,0,0,0,0[0d]
09/08 02:00:03 yS1   mdm_command: string 'AT+FDCC=1,5,0,2,0,0,0,0'
09/08 02:00:03 yS1    got:[0d][0a]OK[0d]
09/08 02:00:03 yS1   mdm_command: string 'OK' -> OK
09/08 02:00:03 yS1   waiting for line to clear (VTIME), read:
09/08 02:00:03 yS1   removing lock file
09/08 02:00:03 yS1  waiting...                       

-------------------------------------------------
You should now be able to have someone fax (ahem!) something to you.

After reception, you should see a new file in /var/spool/fax/incoming, somethning like:

fn7d8ad5cS1-_-123-543-67-33--_.01 

you can look at it directly using "viewfax" for example (excepted if it is froma  multitech modem: inverted bit order...). Now we must go into the fax processing thing.

After receiving a fax, mgetty looks for a script named "new_fax" and executes it.
To see where it expects "new_fax" to be:

strings /sbin/mgetty | grep new_fax  

I get the following:

/etc/mgetty+sendfax/new_fax  

OK let's put a script right there. Mine was cooked with fragments form several examples distributed with mgetty+sendfax.
You should set the execution permissions right. Please verify carefully all the parameters are consistent with your system.
...........................................................
#!/bin/sh
# /etc/mgetty+sendfax/new_fax
# ver: 19990917
#
# Originally this was a script to send mgetty's incoming faxes 
# via MIME encoded EMail; by Martin Spott (martin.spott@uni-duisburg.de)
#
# It was heavily modified by Max Klohn <amk@span.ch> 
# for simultaneous laser printing and MySQL archiving (as part of the
# freemed (http://www.freemed.org) project.
#
# This script is called when a message was recorded. 
# It gets the following arguments:
#      $1 : the hangup code
#      $2 : the remote id
#      $3 : the number of pages
#      $4... : the file names

# Place the correct EMail-adresses here !!!
USER="<fax@telecom.pmc>"
ADMIN="<root@telecom.pmc>"

# How mgetty calls us.
HANGUP_CODE="$1"
SENDER_ID="$2"
NUMBER_PAGES="$3"

# Some miscellaneous data and filenames.
TMP=/tmp
# gif scaling, set to your convenance
XSCALE=0.58
#
TIMESTAMP=`/bin/date +%Y%m%d%H%M%S`
MIME_TIFF=$TMP/TIFF_$TIMESTAMP
MIME_MAIL=$TMP/MAIL_$TIMESTAMP
SQL=$TMP/SQL_$TIMESTAMP             
ERRLOG=$TMP/SQL_ERRLOG

# The binaries we need; please check carefully !!!
BASENAME=/bin/basename
CAT=/bin/cat
CUT=/usr/bin/cut
ECHO=/bin/echo
ELM=/usr/bin/elm
G3TOPBM=/usr/bin/g32pbm
PGMTOPBM=/usr/bin/pgmtopbm
MMENCODE=/usr/bin/mmencode
PNMSCALE=/usr/bin/pnmscale
PNMTOTIFF=/usr/bin/pnmtotiff
PPMTOGIF=/usr/bin/ppmtogif
PBMTOLJ=/usr/bin/pbmtolj
RM=/bin/rm
SED=/bin/sed
SENDMAIL=/usr/sbin/sendmail
MYSQL=/usr/bin/mysql
# Set this one to where you configure freemed to find the gifs
# mine is a nfs share mounted by the fax-receiving machine 
GIFHOME=/var/package/data/fax/incoming
# SQL stuff
# Don't forget to create a user with minimal insert privileges 
# on the given table (see below for table description)
# For example:
# ------------------------------------------------
# GRANT INSERT ON freemed.infaxes to fax@localhost
# identified by 'fax' ;
# ------------------------------------------------
# If anyone has a better idea for security please feel free to improve...
# Be sure to have those right too:
SERVER=localhost
USER=fax
PASSWORD=fax
DATABASE=freemed
TABLE=infaxes
#
# Infaxes should be created by admin.php3. BTW here's the SQL if you need
# to create it from the command line:
#
# USE freemed;
# CREATE TABLE freemed.infaxes (
#    infcode	        VARCHAR(5),  
#    infsender		VARCHAR(50),
#    inftotpages		INT UNSIGNED,
#    infthispage		INT UNSIGNED,
#    inftimestamp	TIMESTAMP,
#    infimage		VARCHAR(50),
#    inforward		ENUM("no","yes") NOT NULL,		
#    infack		ENUM("no","yes") NOT NULL,
#    infptid		VARCHAR(10),
#    infphysid		VARCHAR(10),
#    id INT NOT NULL AUTO_INCREMENT,
#    PRIMARY KEY (id)
#    );
#
# Notice the hooks for a forward and a "received" ack fields, as well as
# patient id and physician id numbers (which in my opinion should be the
# official in-house numbers, and not the freemed-assigned ones
#
# This script generates gifs for freemed, mails tifs to a given user, and prints the 
# incoming faxes. You can comment out the functions you won't use.
#
# Essential lines to put into the header of a MIME mail.
HEADERLINE_1="MIME-Version: 1.0"
HEADERLINE_2="Content-Type: multipart/mixed; boundary="attachment""

# Lines to put into the header of each MIME attachment.
ATTACHMENT_HEADERLINE_1="--attachment"
ATTACHMENT_HEADERLINE_2="Content-Type: image/tiff"
ATTACHMENT_HEADERLINE_3="Content-Transfer-Encoding: base64"

# Line to close the attachment section of a MIME mail.
ATTACHMENT_ENDLINE="--attachment--"

# Now we build our MIME mailheader using commandline arguments.
$ECHO "Subject: FAX entrant de $2 avec $3 page(s)" > $MIME_MAIL
$ECHO "$HEADERLINE_1" >> $MIME_MAIL
$ECHO "$HEADERLINE_2" >> $MIME_MAIL
$ECHO "" >> $MIME_MAIL

# To handle each attachment we skip the first three arguments (those we
# already used for the header).
shift 3

# Handling of each fax page, whose names are given via commandline arguments.
# We have to cut off the absolute path via 'basename' and remove the dot
# which separates the page number. Also we add a filename extension and fit
# the result as filename into our attachment header - some mail frontends
# need this.
#
for i in $@
do
	# Let's extract the page number from the file name. Notice that
	# this will fail when the sender hasn't set the fax ID. 
	# Someone fix this...

        THISPAGE=`$BASENAME $i | $CUT -d_ -f3 | $CUT -c2-3`
	
	# Now we set the .gif filename
	
	GIFNAME=`$BASENAME $i`.gif
	
	# We use the second character in the filename to identify the
	# resolution of our incoming fax, so we can easily scale the fax for
	# display on a screen.
	
	RESOLUTION=`$BASENAME $i | $SED 's/.\(.\).*/\1/'`
	if [ "$RESOLUTION" = "n" ]
	then
		YSCALE=1.16
	else
		YSCALE=0.58
	fi
#
	# The fax is converted from G3 to PBM, it is scaled and then
	# converted to TIFF.
	# We write it into a temporary file, because 'mmencode' doesn't
	# handle standard input correctly.
	# IMPORTANT! For those who don't have Multitech modems: make damn 
	# sure to erase the -r (reverse byte order) after $G3TOPBM

	$CAT $i | $G3TOPBM -r | $PNMSCALE -xscale $XSCALE -yscale $YSCALE \
	| $PNMTOTIFF > $MIME_TIFF

	# Let's make the .gif for the database. If you don't own a Multitech
	# modem, rease the -r aftter $G3TOPBM

        $CAT $i | $G3TOPBM -r | $PNMSCALE -xscale $XSCALE -yscale $YSCALE \
        | $PPMTOGIF > $GIFHOME/$GIFNAME
	


# build the SQL command
#
$ECHO "INSERT INTO $DATABASE.$TABLE (infcode, infsender, inftotpages, infthispage, inftimestamp, infimage)" > $SQL 
$ECHO "VALUES ('$HANGUP_CODE','$SENDER_ID','$NUMBER_PAGES','$THISPAGE','$TIMESTAMP','$GIFNAME');" >> $SQL
#
# run mysql on that baby
$MYSQL -h $SERVER -u $USER -p$PASSWORD < $SQL > $ERRLOG
#
#
# printing stuff on laserjet 300 dpi
#
        if [ "$RESOLUTION" = "n" ]
           then
	$CAT $i | $G3TOPBM -r | $PNMSCALE -xscale 1.43 -yscale 2.86 | \
        $PGMTOPBM | $PBMTOLJ -resolution 300 | lpr -P${PRINTER}
           else
	$CAT $i | $G3TOPBM -r | $PNMSCALE -xysize 2479 3508 | \
        $PGMTOPBM | $PBMTOLJ -resolution 300 | lpr -P${PRINTER}
        fi
#
# end of printing stuff
#
	# Now we put the header for each attachment into our MIME mail.
	$ECHO "$ATTACHMENT_HEADERLINE_1" >> $MIME_MAIL
	$ECHO "$ATTACHMENT_HEADERLINE_2; name=\"`$BASENAME $i|cut -f1 -d\.``$BASENAME $i|cut -f2 -d \.`.TIF\"" >> $MIME_MAIL
	$ECHO "$ATTACHMENT_HEADERLINE_3" >> $MIME_MAIL
	$ECHO "" >> $MIME_MAIL
#
	# Here we do base64 encoding of out TIFF data and add the result
	# into our MIME mail as attachment.
	$MMENCODE -b $MIME_TIFF >> $MIME_MAIL
#
	# To clean up temporary TIFF data.
	$RM -f $MIME_TIFF
#
	# Each attachment has to end with a blank line (I believe).
	$ECHO "" >> $MIME_MAIL
done

# To close the attachment section of our mail.
$ECHO "$ATTACHMENT_ENDLINE" >> $MIME_MAIL

# Sending the mail.
$SENDMAIL < $MIME_MAIL $USER
#
# Run the mail queue
$SENDMAIL -q

# Cleaning up behind us (comment out if you need to debug).
$RM -f $MIME_MAIL
$RM -f $SQL 
$RM -f $ERRLOG
# That's all folks !
exit 0
..........................................
----------------------------------------------
Some ideas next:

-routing faxes according to different caller IDs or station IDs

- some OCR to extract the real destinatary of the fax. Hint: for a given station ID the header is usually always identical...

-of course: the corresponding freemed module...

----------------------------------------------
Post Faxum 1:

Incoming faxes usually stay there and pile up. Have a cron job erase them routinely, or, if you are not using the SQL option, compress and archive them somewhere else.
example of a script:
..........................................
#!/bin/sh
FILENAME=`date +%y%m%d-%H%M%S`
`/var/spool/fax/archive`
INCOMING=`/var/spool/fax/incoming`
/bin/tar cy --remove-files -f /var/spool/fax/archive/$FILENAME.tar.bz2 /var/spool/fax/incoming/*
logger -t FAX_ARCHIVE Compressed the fax files in $FILENAME
exit 0  
..........................................


----------------------------------------------
Post Faxum 2:
 Let's say you are cheap ;-) and use the same modem for outgoing connexions too. Bad surprise: neither minicom nor pppd like this. You'll have to kill mgetty before connecting.

Proposed solution (anyone has a better and safer idea?)

Write two inittabs, inittab.ppp without mgetty, and inittab.mgetty with it.
Re-init at each change: if using masqdialer, you can run those just before the connexion and after it has ended:

........................................
#!/bin/sh
/bin/cat /etc/inittab.ppp > /etc/inittab
/sbin/init q
/sbin/ifup ppp0
exit 0      
........................................
et, apres une session:

........................................
#!/bin/sh
/usr/sbin/linkdown
/bin/cat /etc/inittab.mgetty > /etc/inittab
/sbin/init q
exit 0 
........................................
---------------------------------------------------
Have Phun!

Max K.

_______________________________________________
Freemed-project maillist  -  Freemed-project@susquehanna.kippona.com
http://www.kippona.com/mailman/listinfo/freemed-project

