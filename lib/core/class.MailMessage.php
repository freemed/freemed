<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 // 	Sascha Schumann <sascha@schumann.cx>
 // 	Tobias Ratschiller <tobias@dnet.it>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

class MailMessage { 

	// class variables
	public $parts;
	public $to;
	public $from;
	public $headers;
	public $subject;
	public $body;

	//    void mail_message ()
	//    class constructor
	public function __construct ( ) {
		$this->parts = array();
		$this->to = "";
		$this->from = "";
		$this->subject = "";
		$this->body = "";
		$this->headers = "";
	} // end function mail_message (constructor)

	//    void add_attachment(string message, [string name], [string ctype])
	//    Add an attachment to the mail object
	public function add_attachment($message, $name = "", $ctype = "application/octet-stream") {
		$this->parts[] = array (
			"ctype" => $ctype,
			"message" => $message,
			"encode" => $encode,
			"name" => $name
		);
	} // end method add_attachment

	//     void build_message(array part=
	//     Build message parts of an multipart mail
	public function build_message($part) {
		$message = $part["message"];
		$message = chunk_split(base64_encode($message));
		$encoding = "base64";
		return "Content-Type: ".$part["ctype"].
			($part["name"]?"; name = \"".$part["name"]."\"" : "").
			"\nContent-Transfer-Encoding: $encoding\n\n$message\n";
	} // end method buildmessage

	//     void build_multipart()
	//     Build a multipart mail
	public function build_multipart ( ) { 
		$boundary = "b".md5(uniqid(time()));
		$multipart = "Content-Type: multipart/mixed;".
			"boundary = $boundary\n\n".
			"This is a MIME encoded message.\n\n".
			"--$boundary";
		for($i = sizeof($this->parts)-1; $i >= 0; $i--) {
			$multipart .= "\n".$this->build_message($this->parts[$i])."--$boundary";
		}
		return $multipart.= "--\n";
	} // end method build_multipart

	//     void send()
	//     Send the mail (last class-function to be called)
	public function send( ) { 
		$mime = "";
		if (!empty($this->from)) { $mime .= "From: ".$this->from."\n"; }
		if (!empty($this->headers)) { $mime .= $this->headers."\n"; }
		if (!empty($this->body)) { $this->add_attachment($this->body, "", "text/plain"); }
		$mime .= "MIME-Version: 1.0\n".$this->build_multipart();
		mail($this->to, $this->subject, "", $mime);
	} // end function mail_message->send()

} // end class MailMessage

 /*
  * Example usage
  *
                                      
    $attachment = fread(fopen("test.jpg", "r"), filesize("test.jpg")); 

    $mail = new mail_message();
    $mail->from = "foo@bar.com";
    $mail->headers = "Errors-To: foo@bar.com";
    $mail->to = "bar@foo.com";
    $mail->subject = "Testing...";
    $mail->body = "This is just a test.\nLine 2.\n<B>html encoded?</B>\n";
    $mail->add_attachment("$attachment", "test.jpg", "image/jpeg");
    $mail->send();
                                      
  */

?>
