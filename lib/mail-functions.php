<?php
 // $Id$
 // desc: functions/classes to allow inline mailing
 // code: Sascha Schumann <sascha@schumann.cx>,
 //       Tobias Ratschiller <tobias@dnet.it>,
 //       jeff b <jeff@univrel.pr.uconn.edu>
 // lic : GPL, v2

if (!defined ("__MAIL_FUNCTIONS_PHP__")) {

define (__MAIL_FUNCTIONS_PHP__, true);

 // Class mail_message
 // formerly: Class mime_mail
 // Original implementation by Sascha Schumann <sascha@schumann.cx>
 // Modified by Tobias Ratschiller <tobias@dnet.it>:
 //     - General code clean-up
 //     - separate body- and from-property
 //     - killed some mostly un-necessary stuff
 // Modified by jeff b <jeff@univrel.pr.uconn.edu>
 //     - class name change
 //     - more clean-up
 //     - modifications for use in freemed
 
class mail_message { 

 // class variables

 var $parts;
 var $to;
 var $from;
 var $headers;
 var $subject;
 var $body;

  //    void mail_message ()
  //    class constructor

 function mail_message () {
   $this->parts = array();
   $this->to = "";
   $this->from = "";
   $this->subject = "";
   $this->body = "";
   $this->headers = "";
 } // end function mail_message (constructor)

  //    void add_attachment(string message, [string name], [string ctype])
  //    Add an attachment to the mail object

 function add_attachment($message, $name = "",
                         $ctype = "application/octet-stream") {
   $this->parts[] = array (
                          "ctype" => $ctype,
                          "message" => $message,
                          "encode" => $encode,
                          "name" => $name
                          );
 } // end function mail_message->add_attachment()

 //     void build_message(array part=
 //     Build message parts of an multipart mail

 function build_message($part) {
   $message = $part["message"];
   $message = chunk_split(base64_encode($message));
   $encoding = "base64";
   return "Content-Type: ".$part["ctype"].
          ($part["name"]?"; name = \"".$part["name"]."\"" : "").
          "\nContent-Transfer-Encoding: $encoding\n\n$message\n";
 } // end function mail_message->buildmessage()

 //     void build_multipart()
 //     Build a multipart mail

 function build_multipart() { 
   $boundary = "b".md5(uniqid(time()));
   $multipart = "Content-Type: multipart/mixed;".
                "boundary = $boundary\n\n".
                "This is a MIME encoded message.\n\n".
                "--$boundary";
   for($i = sizeof($this->parts)-1; $i >= 0; $i--) 
     $multipart .= "\n".$this->build_message($this->parts[$i])."--$boundary";
   return $multipart.= "--\n";
 } // end function mail_message->build_multipart

 //     void send()
 //     Send the mail (last class-function to be called)

 function send() { 
   $mime = "";
   if (!empty($this->from))
     $mime .= "From: ".$this->from."\n";
   if (!empty($this->headers))
     $mime .= $this->headers."\n";
    
   if (!empty($this->body))
     $this->add_attachment($this->body, "", "text/plain");   

   $mime .= "MIME-Version: 1.0\n".$this->build_multipart();
   mail($this->to, $this->subject, "", $mime);
 } // end function mail_message->send()

}; // end of class mail_message

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

} // end checking for __MAIL_FUNCTIONS_PHP__

?>
