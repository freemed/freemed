# $Id$
# $Author$
#
#	Quest Diagnostics HL7 interface cron fragment

# Poll every 10 minutes
00,10,20,30,40,50 *	* * *	root	/usr/share/freemed/scripts/quest_hl7/quest_hl7_poll.pl
