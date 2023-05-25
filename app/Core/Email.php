<?php namespace App\Core;


//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Model as Model;


class Email extends Model 
{

	public $SendToEmail;
	public $BodyMessage;
	public $Template;
	public $Subject;
	public $EmailParam; 
	public $Attachment; 
	public $ccEmail;

	
	public function SendEmail() {
	$mail = new PHPMailer(true);
	
		try {
			//Server settings
			$mail->SMTPDebug = 0;                                 // Enable verbose debug output
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = EMAILHOST;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = EMAILUSER;                 // SMTP username
			$mail->Password = EMAILPASS;                           // SMTP password
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;                                    // TCP port to connect to
		
			//Recipients
			$mail->setFrom('noreply@snoopi.io', 'Snoopi.io');
			//Set an alternative reply-to address
			$mail->addReplyTo('support@snoopi.io', 'Snoopi.io');
		
			#TODO: Need to have it loop multiple email addresses
			$mail->AddBCC('carlosja80@gmail.com ', 'Carlos Arias');     // Add a recipient
			
			if($this->ccEmail) {			
				foreach($this->ccEmail as $ccdemail) {
					 $mail->AddCC(trim($ccdemail));
				}
			}
			$mail->addAddress(trim($this->SendToEmail));               // Name is optional
		
			//Attachments
			if($this->Attachment) $mail->addAttachment($this->Attachment); 
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		
			//Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $this->Subject;
			$mail->Body    = $this->BodyMessage;
			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			
			$HTMLMessage = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/templates/emails/' . strtolower($this->Template) . '.html');
			foreach($this->EmailParam as $Key => $Value) { 
				$HTMLMessage = str_replace('%'. strtoupper($Key) .'%', $Value, $HTMLMessage);
			}
	
			#Example: $HTMLMessage = str_replace('%ACTIVATION%', $this->EmailParam['activation'] , $HTMLMessage);


			$mail->MsgHTML($HTMLMessage);
		
			$mail->send();
			 return true;
		} catch (Exception $e) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		}
	}


} // End Class 