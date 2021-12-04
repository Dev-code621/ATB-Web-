<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
      
    </head>
</html>
<?php            
       use PHPMailer\PHPMailer\PHPMailer;
       use PHPMailer\PHPMailer\SMTP;
       use PHPMailer\PHPMailer\Exception;
       
       //Load Composer's autoloader
       require 'vendor/autoload.php';
               
       
    function sendEmail($firstname, $surname,$email, $title,$body,$altbody) {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output :SMTP::DEBUG_SERVER // off : SMTP::DEBUG_OFF
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = "smtp.gmail.com";                             //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'dpuja2071@gmail.com';                     //SMTP username
            $mail->Password   = 'qsaianeggvdurgmy';                               //SMTP password
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('noreply@myatb.co.uk', 'ATB');
            $mail->addAddress($email, $firstname + ' ' + $surname);     //Add a recipient
            $mail->addAddress($email);               //Name is optional
            //$mail->addReplyTo('honestdeveloper10@gmail.com', 'Information');
            //$mail->addCC('honestdeveloper10@gmail.com');
            //$mail->addBCC('honestdeveloper10@gmail.com');

            //Attachments
            //  $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //  $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $title;
            $mail->Body    = $body;
            $mail->AltBody = $altbody;
            $mail->send();
             //echo 'Message has been sent';
             //echo '<script type="text/javascript">toastr.success("Email sent Successfully!")</script>';

        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }
    ?>