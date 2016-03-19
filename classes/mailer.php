<?php

//PEAR include
include('Mail.php');
include('Mail/mime.php');
include_once '../phpmailer/PHPMailerAutoload.php';
class Mailer {

    public $mail_from = '';
    public $mail_to = '';
    public $subject = '';
    public $message = '';
    public $file = '';
    public $format = '';
    public $mailer = NULL;
    

    public function __construct() {
        $message = new PHPMailer();
        $message->isSMTP();
                $message->SMTPAuth = true;
                $message->Host = 'smtp.netregistry.net';
		$message->Port = '465';
                $message->SMTPSecure = 'ssl';
                $message->Username = 'noreply@bluecloudaustralia.com.au';
		// $message->Password = 'p0stoff1cem@n!';
		$message->Password = 'pa55w0rd';
                 $message->SMTPDebug =1;
 		//$message->setHTMLBody(rawurldecode($email_message));
                $message->isHTML(true);
                $this->mailer = $message;
    }
    public function fromName($name)
    {
        $this->mailer->FromName = $name;
    }
    public function attach_file() {//check if there is something to attach
        if (!empty($this->file) && !empty($this->format)) {

            $this->mailer->addAttachment($this->file, $this->format);
            return TRUE;
        } else {
            return FALSE;
        }
    }
    public static function emailpass($emailto,$pass)
    {
        $emailer = new Mailer();
        $message = '<h1>Welcome to BlueCloud!</h1><br>You may use your email as userlogin and '.$pass.' as your password';
        $emailer->set_mail("noreply@bluecloudaustralia.com.au",$emailto,"Welcome to BlueCloud",$message);
        $emailer->mailer->FromName = 'BlueCloud Australia';
        return $emailer->send_mail();
    }
    public function set_mail($mail_from, $mail_to, $subject, $message, $file, $format) {
        $this->mail_from = $mail_from;
        $this->mail_to = $mail_to;
        $this->subject = $subject;
        $this->message = $message;
        $this->file = $file;
        $this->format = $format;
    }

    public function send_mail() {
        $this->mailer->Body = $this->message;
        $this->attach_file();
        $this->mailer->addAddress($this->mail_to);
        $this->mailer->addReplyTo($this->mail_from);
        $this->mailer->Subject = $this->subject;
        $this->mailer->From = $this->mail_from;
        if ($this->mailer->send())
        {
            return true;
        }
        else return $this->mailer->ErrorInfo;
    }

}

?>
