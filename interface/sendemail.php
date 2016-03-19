<?php
/*
  sendemail.php

  EXPECTS:
    act     - switch value 
    tid     - template id
    uid     - user id
    leadid  - lead id
    to      - recipient
*/
include "../dbconnect.php";

session_start();

phplog("_SESSION: " . print_r($_SESSION, true));

$bcid = $_SESSION['bcid'];

include "phpfunctions.php";
require_once "../classes/domparser.php";

if (featurecheck($bcid,'email') == false)
{
	echo "Feature not Supported.  Contact your system Administrator";
	exit;
}
include_once('Mail.php');
include_once('Mail/mime.php');
require '../phpmailer/PHPMailerAutoload.php';

$act = $_REQUEST['act'];

if ($act == 'sendemail')
{
    phplog("_REQUEST: " . print_r($_REQUEST, true));

		$tid = $_REQUEST['tid'];
		$uid = $_REQUEST['uid'];
    $leadid = $_REQUEST['leadid'];
    $email_to = $_REQUEST['to'];

		$res = mysql_query("SELECT * FROM templates WHERE templateid = '$tid'");
		$row = mysql_fetch_array($res);

    phplog("TEMPLATE ROW: " . print_r($row, true));
    
    $sigs = getdatatable("signatures where sigid= ".$row['sigid'],"sigid");
    $sig = $sigs[$row['sigid']];

		$email_from = $row['emailfrom'];
		$attachments = split(",",$row['attachments']);
    $recipients = $email_to;
		$email_subject = $row['template_subject'];
		$email_message1 = $row['template_body'];

    $em = str_get_html(rawurldecode($email_message1));
    $ict = 0;

    foreach ($em->find("div#signature img") as $img)
    {
      $ipath = $img->src;
      $cid = "img-".$ict;
      $em->find("div#signature img",$ict)->src = "cid:$cid";
      $iatt[$cid] = $ipath;
      $ict++;
    }
    $email_message = (string)$em;
//$message = new Mail_mime();
    $message = new PHPMailer();
    $message->isSMTP();
    $message->SMTPAuth = true;
    $message->Host = $row['mailserver'];
		$message->Port = $row['mailport'];

    if ($row['mailencryption'] != 'none')
    {
      $message->SMTPSecure = $row['mailencryption'];
    }
    $message->Username = $row['mailuser'];
		$message->Password = $row['mailpass'];
 		//$message->setHTMLBody(rawurldecode($email_message));
    $message->isHTML(true);  
    $message->SMTPDebug = $row['debug'];
    $message->Timeout=30;
    $message->Body = $email_message.'<p>Powered by BlueCloud.</p>';

 		foreach ($attachments as $attachment)
		{
  		$message->addAttachment("../attachments/".$attachment);
		}

    foreach ($iatt as $cid=>$ipath)
    {
      $parts = explode(".",$ipath);
      foreach ($parts as $p)
      {
          $ext = $p;
      }
      
      $temp = tempnam("tmp", "tmp");
      $tn = md5($temp);
      $tempf = "../attachments/".substr($tn,1,8).".".$ext;
      file_put_contents($tempf, fopen("$ipath", 'r'));
      $message->addEmbeddedImage($tempf, $cid);
    }
		//$body = $message->get();
 		//$htmlemail = "text/html"; 
		if (strlen($row['replyto']) > 1)
		{
			$rp = $row['replyto'];
		}
		else 
      $rp = $email_from;

		//$extraheaders = array("From"=>$email_from, "Subject"=>$email_subject,"Reply-To"=>$rp, "To"=>$email_to);
    $message->addAddress($email_to);
    $message->addReplyTo($rp);
    $message->Subject = $email_subject;
    $message->From = $email_from;
    $fromname = $row['emailfromname'];
    if (strlen($fromname) < 1)
    {
        $parts = explode("@",$email_from);
        $fromname = $parts[0];
    }

    $message->FromName = $fromname;

		if (strlen($row['emailcc']) > 1)
		{
			//$extraheaders["Cc"] = $row['emailcc'];
      //$recipients .= ",".$row['emailcc'];
      $message->addCC($row['emailcc']);
                                
		}

    if (strlen($row['emailbcc']) > 1)
		{
			/*$extraheaders["Bcc"] = $row['emailbcc'];
      $recipients .= ",".$row['emailbcc'];*/
      $message->addBCC($row['emailbcc']);
		}
 		//$headers = $message->headers($extraheaders);
		//$a = $mail->send($recipients, $headers, $body);
               //var_dump($message->ErrorInfo);

    phplog("Sending email ... ");

		if ($message->send())
		{
      $_status = "Sent";

      phplog("SUCCESS!");
      echo "Email Sent";
		}
		else 
    {
      $_status = $message->ErrorInfo;

      phplog("FAILED!");
			echo "Email Failed: " . $message->ErrorInfo;
		}
    $_qry = "INSERT into email_log set userid = '$uid', mailto= '$email_to', timesent = unix_timestamp(), projectid = '".$row['projectid']."', leadid = '$leadid', status = '$_status', templateid = '$tid'";

    phplog("QUERY: " . $_qry);

    mysql_query($_qry);

    phplog("NEW EMAIL_LOG RECORD: " . mysql_insert_id());
		/*
		$headers = 'From: '.$email_from."\r\n".
		'Reply-To: '.$email_from."\r\n" .
		'X-Mailer: PHP/' . phpversion();
		@mail($email_to, $email_subject, $email_message, $headers);  */
    unlink($tempf);
}