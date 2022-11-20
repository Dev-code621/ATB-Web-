<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="icon" type="image/png" href="assets/img/favicon.ico" />
        <link rel="stylesheet" href="https://use.typekit.net/zcc3dpi.css"> 
        <script src="https://kit.fontawesome.com/05098690c1.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <title>ATB</title>
        <style>
            .dropdown-body {
                text-align: right;
            }
            .dropbtn {
                background: #A6BFDE;
            color: white;
            padding: 16px;
            font-size: 16px;
            border: none;
            top: 10px;
            right: 10px;
            }

            .dropdown {
            position: relative;
            display: inline-block;
            background: #A6BFDE;
            min-width: 160px;
            }

            .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f1f1f1;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            }

            .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            }

            .dropdown-content a:hover {background-color: #ddd;}

            .dropdown:hover .dropdown-content {display: block;}

            .dropdown:hover .dropbtn {background-color: #3e8e41;}
            </style>
    </head>

    <body>
        
        <div class="index-content" style="background: #A6BFDE; ">
         <div class="dropdown-body">
            <div class="dropdown" >
                    <button class="dropbtn">T&Cs</button>
                    <div class="dropdown-content">
                        <a href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1" target="_blank">User agreement</a>
                        <a href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486" target="_blank">Privacy Policy</a>
                        <a href="https://app.termly.io/document/cookie-policy/de313fa7-ef48-4619-86d2-0daad3679b40" target="_blank"> Cookie Policy</a>                         
                        <a href="https://app.termly.io/document/eula/c8f66d8d-c546-452f-bcf4-1c28815043dd" target="_blank">EULA</a>
                        <a href="https://app.termly.io/document/disclaimer/c3c5eb9f-6576-4402-b294-11a7ac0704c1" target="_blank"> Disclaimer</a>
                    </div>
                </div>
        </div>
            
            <div class="logo">
                <h6>Welcome to</h6>
                <img src="assets/img/logo-white.svg">
            </div>
            
            <div id="sliders" class="carousel slide carousel-fade" data-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleCaptions" data-slide-to="1"></li>
                    <li data-target="#carouselExampleCaptions" data-slide-to="2"></li>
                    <li data-target="#carouselExampleCaptions" data-slide-to="3"></li>
                    <li data-target="#carouselExampleCaptions" data-slide-to="4"></li>
                </ol>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="assets/img/events.png" alt="...">
                    </div>
                    <div class="carousel-item">
                        <img src="assets/img/beauty.png" alt="...">
                    </div>
                    <div class="carousel-item">
                        <img src="assets/img/home.png" alt="...">
                    </div>
                    <div class="carousel-item">
                        <img src="assets/img/fashion.png" alt="...">
                    </div>
                    <div class="carousel-item">
                        <img src="assets/img/kids.png" alt="...">
                    </div>
                </div>
            </div>
         
            <div class="navigation py-3 px-4">
                <h2 class="small-title text-uppercase text-center mb-3 font-weight-bold">How do you plan on using the ATB app?</h2>
                <a href="#" class="btn" id="openNav" style="z-index: 10">To promote my business</a>
                <a href="users.php" class="btn dark">
                    For General Use
                    <span>(I am not a business)</span>
                </a>
            </div>
           
            <div class="navigation-options">
                <div class="header position-relative p-4">
                    <div class="logo">
                        <img src="assets/img/logo-white.svg" alt="">
                        <p class="mb-0 text-white">Business</p>
                    </div>
                    <span class="close" id="closeNav">
                        <i class="fal fa-times-circle text-white"></i>
                    </span>
                </div>
                <div class="px-4">
                    <h3 class="small-title font-weight-bold my-3 text-center">Select the group most relevant to your Business</h3>
                    <a href="fashion.php" class="btn"><img src="assets/img/fashion.svg">Fashion</a>
                    <a href="hair.php" class="btn"><img src="assets/img/beauty.svg">Hair, Beauty & Well Being</a>
                    <a href="event.php" class="btn"><img src="assets/img/party.svg">Parties & Events</a>
                    <a href="home.php" class="btn"><img src="assets/img/home.svg">Home & Garden</a>
                    <a href="mum-babies.php" class="btn"><img src="assets/img/kids.svg">Kids</a>
                </div>
            </div>
        </div>
        <script src="assets/vendor/jquery-3.5.1.min.js" ></script>
        <script src="assets/vendor/bootstrap.min.js" ></script>
        <script src="assets/js/function.js"></script>
    </body>
</html>