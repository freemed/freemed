--TEST--
Net_SMTP: Basic Functionality
--FILE--
<?php

require_once 'SMTP.php';
require_once 'config.php';

if (! ($smtp = new Net_SMTP(TEST_HOSTNAME, TEST_PORT, TEST_LOCALHOST))) {
	die("Unable to instantiate Net_SMTP object\n");
}

if (PEAR::isError($e = $smtp->connect())) {
	die($e->getMessage() . "\n");
}

if (PEAR::isError($smtp->mailFrom(TEST_FROM))) {
	die('Unable to set sender to <' . TEST_FROM . ">\n");
}

if (PEAR::isError($res = $smtp->rcptTo(TEST_TO))) {
	die('Unable to add recipient <' . TEST_TO . '>: ' .
		$res->getMessage() . "\n");
}

if (PEAR::isError($smtp->data(TEST_SUBJECT . "\r\n" . TEST_BODY))) {
	die("Unable to send data\n");
}

$smtp->disconnect();

echo 'Success!';

--EXPECT--
Success!
