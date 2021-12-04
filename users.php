<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

        <link rel="icon" type="image/png" href="assets/img/favicon.ico" />
        <link rel="stylesheet" href="https://use.typekit.net/zcc3dpi.css"> 
        <script src="https://kit.fontawesome.com/05098690c1.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <title>ATB</title>
      
    </head>

    <body>

    <?php            
       use PHPMailer\PHPMailer\PHPMailer;
       use PHPMailer\PHPMailer\SMTP;
       use PHPMailer\PHPMailer\Exception;
       
       //Load Composer's autoloader
       require 'vendor/autoload.php';
        if(array_key_exists('sendEmail',$_POST)){
            testfun($_POST["firstname"],$_POST["surname"],$_POST["email"],$_POST["instagram"]);
        }                               
        function testfun($firstname, $surname,$email, $instagram) {
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host = "smtp.gmail.com";                    //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'dpuja2071@gmail.com';                     //SMTP username
                $mail->Password   = 'pak1993621';                               //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
                $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients
                $mail->setFrom('honestdeveloper10@gmail.com', 'Mailer');
                $mail->addAddress('honestdeveloper10@gmail.com', 'Joe User');     //Add a recipient
                $mail->addAddress('honestdeveloper10@gmail.com');               //Name is optional
                $mail->addReplyTo('honestdeveloper10@gmail.com', 'Information');
                $mail->addCC('honestdeveloper10@gmail.com');
                $mail->addBCC('honestdeveloper10@gmail.com');

                //Attachments
              //  $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
              //  $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

                //Content
              //  $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Here is the subject';
                $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                $mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

        }
    ?>
        <!-- Modal -->
        <div class="modal  fade" id="userInterest" tabindex="-1" aria-labelledby="userInterestLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-gradient pb-5">
                        <div class="logo text-center">
                            <h6>Welcome to</h6>
                            <img src="assets/img/logo-white.svg" alt="ATB Fashion" class="img-fluid"> 
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="fal fa-times-circle text-white"></i>
                        </button>
                    </div>

                    
  
                    <div class="modal-body pb-0">
                        <h2 class="title small-title text-center mt-1">Please complete this short form and we'll let you know when the app is ready!</h2>
                        <form method="post">
                            <div class="form-group">
                                <label for="name">First Name</label>
                                <input type="text" class="form-control" name="firstname" id="name" aria-describedby="nameHelp" required>
                            </div>
                            <div class="form-group">
                                <label for="surname">Surname</label>
                                <input type="text" class="form-control" name= "surname" id="surname" aria-describedby="surnameHelp" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" name = "email" id="email" aria-describedby="emailHelp" required>
                            </div>
                            <div class="form-group">
                                <label for="instagramAccount">Instagram account</label>
                                <input type="text" class="form-control" name = "instagram" id="instagramAccount" aria-describedby="instagramAccountHelp">
                            </div>
                            <button type="submit" class="btn btn-block" name="sendEmail" >Send</button>
                        </form> 
                        <div class="footer mt-5 p-3 pb-5 text-center">
                            <h3>Find us in our social networks</h3>
                            <div class="social-links my-4">
                                <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                                <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                                <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div>

        <div class="fixed-elements">
            <div class="top-header user">
                <a href="#" class="logo">
                    <img src="assets/img/logo-white.svg" alt="ATB Fashion" class="img-fluid"> 
                    <p class="text-white mb-0 mt-2">APP</p>
                </a>
            </div>
        </div> 

        <section class="intro-user">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 pb-5 pb-md-0 hidden-x mb-md-n5 mb-lg-0">
                        <h2 class="text-white text-center mt-3 mb-n4">Coming Summer 2021.</h2>
                        <img src="assets/img/user-intro-screen.png" alt="" class="userIntro ml-lg-5 mb-n5">
                        <div class="btn-bottom users">
                            <a href="#" class="btn"  data-toggle="modal" data-target="#userInterest">Let me know when it's ready</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bgOnTop textLg users">
            <div class="container">
                <div class="row">
                    <div class="col-md-7 px-4 offset-md-5 col-lg-6 offset-lg-5 text-md-left text-center">
                        <h2 class="title mb-3 arrowIcon arrowUp">My ATB</h2>
                        <p>Fed up of swapping ATB groups on Facebook?</p>
                        <p>On the app you can personalise your feed to combine all existing groups that are of interest to you into one for a streamlined scrolling sesh!</p>
                        <p> Set a radius of your choice and you will only be presented with posts you wish to see within an area that’s relevant to you.</p>
                    </div>
                </div> 
            </div>
        </section>


        <section class="userPosts position-relative pt-4">
            <div class="container">
                <div class="row">
                    <div class="hidden-x pt-100 col-md-5 order-md-2">
                        <img src="assets/img/user-various-post.png" alt="" class="post-options-img">
                    </div>
                    <div class="col-md-6 offset-md-1 order-md-1 ">
                        <div class="text-container">
                            <h2 class="title text-white">Various <br> Post Options <i class="fas fa-caret-up ml-1 d-md-none"></i></h2>
                            <p>First and foremost we are, and always will be a community; the go to place to connect with like-minded people in your local area.</p>
                            <p>Do you need some advice? A recommendation for a boozy brunch or what should be the next box set on your to-watch list out of Handmaid’s Tale, Soprano’s or Gossip Girl? Chat, Buy & Sell, book a service or list a poll. We’ve got you covered!</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="triangle-Bottom user"></div>


        <section class="tripleImg position-relative pb-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 triple-screen hidden-x">
                            <img src="assets/img/user-screen1.png" alt="" >
                            <img src="assets/img/user-screen2.png" alt="" >
                            <img src="assets/img/user-screen3.png" alt="" >
                        </div>
                        <div class="col-md-5  text-center pt-lg-5">
                            <h2 class="title big px-3 mb-5 arrowIcon arrowUp mt-n5 mt-lg-3">For sale posts & in-app purchases</h2>
                            <p>You can still buy & sell your own previously loved items on the app in addition to purchasing from established small businesses who each have their individuals stores on the app. We have integrated PayPal for maximum security with no sneaky listing or transaction fees*.</p>
                            <p><i>*Paypal do deduct their own transaction fee but we will not be adding any extra.</i></p>
                        </div>
                    </div>
                </div>
                <div class="triangle-Bottom triple"></div>
            </section>
        
        
        <div class="halfCircle-Top users"></div>
       
        <section class="verifiedBusinessUser position-relative">
            <span class="circle circleOne"></span>
            <span class="circle circleTwo"></span>
            <span class="circle circleThree"></span>
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-lg-5 offset-lg-1 text-center text-md-right">
                        <img src="assets/img/iconCheck.svg" alt="">
                        <h2 class="title big px-3 mt-3 pl-md-5 ml-md-3 px-lg-0">Verified Business Status</h2>
                        <p>All services offered by our approved ATB businesses will be checked for qualifications and insurance details. Look out for the trusted ATB logo!</p>
                    </div>
                    <div class="col-md-6 col-lg-5 hidden-x">
                        <img src="assets/img/user-verifiedBusiness.png" alt="" class="userVerified">
                    </div>
                </div>
            </div>
        </section>
       

        <section class="topTriangleHalf position-relative hidden-x pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 hidden-x col-lg-6 text-lg-right">
                        <img src="assets/img/user-rating.png" alt="" class="reviewUser">
                    </div>
                    <div class="col-md-6 text-center textBg pt-5 text-white px-5 col-lg-5">
                      <h2 class="title big px-3 mb-2 arrowIcon arrowUp text-white">Reviews</h2>
                      <p>So they’re ATB approved, and what? Don’t just take the our word for it. Checkout honest client reviews before booking, due-diligence is key people!</p>
                        <p>For any service you book you’ll be asked to leave your own review post-appointment. Please pass on the love and help out fellow community members, letting them know your thoughts on the service you experienced.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="userPayment position-relative">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 hidden-x order-lg-2">
                            <img src="assets/img/user-paypal.svg" alt="" class="userPaypal">
                            <img src="assets/img/user-booking.png" alt="" class="img-fluid userBooking">
                    </div>
                    <div class="col-md-6 text-center col-lg-5 offset-lg-1 order-lg-1">
                        <h2 class="title arrowIcon arrowUp pt-lg-5">Booking System & Deposits.</h2>
                        <p>Book services direct via the app, no lengthy back and forth conversations between you and the service provider (although you can message any questions should you want to) - check availability, pay a deposit and secure your appointment..</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="circle-TopBottom position-relative">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 hidden-x">
                        <img src="assets/img/user-directory.png" class="userDirectory">
                    </div>
                    <div class="col-md-6 col-lg-4 text-center pt-5">
                        <h2 class="title arrowIcon arrowUp">ATB Directory</h2>
                        <p>We have thousands of businesses advertising on the various ATB groups, wouldn’t it be nice if we could get them all together in one smart, tidy, pretty list?</p>
                        <p>DONE!! Type in a key word of what you’re looking for and voila! You’ll be presented with businesses, starting with those nearest to you.</p>
                    </div>
                    
                </div>
            </div>
        </section>

        <section class="userPosts position-relative post-bottom pb-lg-3">
            <div class="container">
                <div class="row">
                    <div class="col-md-5 col-lg-6 hidden-x pt-100 order-md-2">
                        <img src="assets/img/user-notification.png" alt="" class="post-options-img notifications">
                    </div>
                    <div class="col-md-6 col-lg-5 offset-md-1 order-md-1">
                        <div class="text-container notification position-relative">
                            <div class="iconsNotification position-absolute">
                                <img src="assets/img/user-plus.png" alt="" width="40" class="mr-2">
                                <img src="assets/img/bell.png" alt="" width="40">
                            </div>
                            <h2 class="title text-white">Follow & <br> Notifications <i class="fas fa-caret-right ml-1 d-md-none position-relative" style="bottom:-5px"></i></h2>
                            <p class="mr-5 w60">Is there a small boutique you love but know that their stock sells out quickly?</p>
                            <p>Perhaps someone is always posting gorgeous second hand furniture because once again they're decorating another room for Instagram content.</p>
                            <p> You can now give them a follow and you’ll receive a notification every time they post something new, giving you a solid chance to snag that purchase first!"</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="users text-center px-0 py-lg-3">
            <div class="container">
                <div class="row align-items-center">

                    <div class="col-md-4">
                        <img src="assets/img/logo-white.svg" alt="" >
                    </div>
                    <div class="col-md-6 ml-auto col-lg-4 ml-lg-0 ">
                        <ul class="nav mt-4 pt-3 mb-4 mt-md-0 pt-md-0 mx-lg-5 mb-lg-0 justify-content-between px-4">
                            <li><a href="#">Business</a></li>
                            <li><a href="#">Help</a></li>
                            <li><a href="#">Sign In</a></li>
                        </ul>
                    </div>
                    <div class="col-md-6 mx-auto mt-md-3 col-lg-3">
                        
                        <div class="socialContainer text-lg-left">
                            <h4>Connect with us on</h4>
                            <ul class="nav justify-content-between px-3 mt-2">
                                <li><a href="#" target="_blank"><i class="fab fa-facebook"></i></a></li>
                                <li><a href="#" target="_blank"><i class="fab fa-twitter"></i></a></li>
                                <li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
                                <li><a href="#" target="_blank"><i class="fab fa-snapchat"></i></a></li>
                                <li><a href="#" target="_blank"><i class="fab fa-pinterest"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </footer> 

        <script src="assets/vendor/jquery-3.5.1.min.js" ></script>
        <script src="assets/vendor/bootstrap.min.js" ></script>
        <script src="assets/js/function.js"></script>
    </body>
</html>