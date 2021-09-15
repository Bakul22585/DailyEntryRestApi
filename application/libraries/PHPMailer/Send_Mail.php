<?php
require 'class.phpmailer.php';
require 'class.smtp.php';

function Send_Mail($to, $subject, $body,$from) {
   // echo 'hihi';die;
    // $mail = new PHPMailer();
    // $mail->IsSMTP(true);            // use SMTP
    // $mail->IsHTML(true);
    // $mail->SMTPAuth = true;                  // enable SMTP authentication
    // $mail->Host = "smtp.gmail.com"; // Amazon SES server, note "tls://" protocol
    // $mail->Port = 25;                    // set the SMTP port
    // $mail->Username = "dev5.etech@gmail.com";  // SMTP  username
    // $mail->Password = "YogeshJalodara";  // SMTP password
    // $mail->SetFrom($from, 'From Name');
    // $mail->AddReplyTo($from, 'From Name');
    // $mail->Subject = $subject;
    // $mail->MsgHTML($body);
    // $address = $to;
    // $mail->AddAddress($address, $to);
    // $mail->Send();

    $mail = new PHPMailer(); // create a new object

    $mail->IsSMTP();
    $mail->SMTPDebug  = 2;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = "tls";
    $mail->Host       = "smtp.gmail.com";
    $mail->Port       = 587;
    $mail->Username   = "dev5.etech@gmail.com";
    $mail->Password   = "YogeshJalodara";
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->AddAddress($to);
    $mail->SetFrom("dev5.etech@gmail.com", "Pillivery" . ' Support');
    $mail->AddReplyTo("dev5.etech@gmail.com", "Pillivery" . ' Support');
    $mail->IsHTML(true);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);


    $rtn = $mail->Send();

}

Send_Mail('dev5.etech@gmail.com', 'Forgot Password', 'Testing', 'dev5.etech@gmail.com');

?>
