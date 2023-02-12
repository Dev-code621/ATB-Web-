<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

        <link rel="icon" type="image/png" href="assets/img/favicon.ico" />
        <link rel="stylesheet" href="https://use.typekit.net/zcc3dpi.css"> 
        <link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <title>ATB</title>
		<link href="assets/css/all.css" rel="stylesheet">

<style>
		
#mySidenav.sidenav {
  width: 320px;
  display: block !important;
  transform: translateX(320px);
	
	position: fixed;z-index: 99;background: #707070;height: 100vh;color: white;right: 0;padding: 30px;
}

#mySidenav.sidenav.not-logged-in {
  width: 320px;
  display: block !important;
  transform: translateX(100vw);
}

#mySidenav.sidenav.open {
    transform: translateX(0);
}
	
	
.sidenav a:hover {
  color: #f1f1f1;
}

.sidenav .closebtn {
  position: absolute;
  top: 35px;
  right: 25px;
  font-size: 32px;
  margin-left: 0px;
  padding: 0;
  background: rgba(0, 0, 0, 0.1);
  width: 35px;
  height: 35px;
  line-height: 27px;
  text-align: center;
  border-radius: 4px;
	color: white;
}

.sidenav .nav-profile {
  padding: 0 0px 20px;
  display: flex;
}


.sidenav .nav-profile h2 {
  font-size: 27px;
  color: #fff;
  font-weight: 900;
  padding: 0;
  /*background: red; test*/
  width: calc(100vw - 60vw);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidenav .nav-profile a.logout {
  font-size: 15px;
  color: #3fc59d;
  padding: 0;
}

.sidenav .navbar-nav .nav-link, .sidenav .navbar-nav .nav-link i{
  color: white;
}

.sidenav .navbar-nav .nav-link i {
 
}

.sidenav .navbar-nav.nav-light .nav-link {
  font-weight: 300;
  opacity: 0.8;
  font-size: 19px;
}

@media screen and (max-height: 450px) {
  .sidenav {
    padding-top: 15px;
  }

  .sidenav a {
    font-size: 18px;
  }
}

.sidenav .navbar-nav .login a {
  color: #465b61;
  margin: 0;
  margin-left: 20px;
}

.sidenav .navbar-nav .login.logged a.nav-link.dropdown-toggle {
  padding: 0 !important;
  font-size: 21px !important;
}

.sidenav .navbar-nav .login.logged a {
  background: none;
  color: white;
  text-align: left !important; /* padding: 0px !important; */
}

.sidenav hr {
  border: 1px solid rgba(255, 255, 255, 0.38);
}

</style>
    </head>

    <body>
        <div id="mySidenav" class="sidenav">
            <div class="nav-profile">
                <img src="assets/img/logo-white.svg">
                <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
            </div>
            <div style="height: 100vh;display: flex; align-content: end;">
                <ul class="navbar-nav mr-auto" style="height: 250px; /* background: blue; */ /* bottom: 0; */ /* position: absolute; */ align-content: end; display: flex; width: 100%; margin-top: calc(100vh - 370px);">
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="https://app.termly.io/document/terms-of-use-for-online-marketplace/cbadd502-052f-40a2-8eae-30b1bb3ae9b1">User agreement <i class="fal fa-arrow-up-right-from-square ml-auto"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="https://app.termly.io/document/privacy-policy/a5b8733a-4988-42d7-8771-e23e311ab486">Privacy Policy <i class="fal fa-arrow-up-right-from-square ml-auto"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="https://app.termly.io/document/cookie-policy/de313fa7-ef48-4619-86d2-0daad3679b40">Cookie Policy <i class="fal fa-arrow-up-right-from-square ml-auto"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="https://app.termly.io/document/eula/c8f66d8d-c546-452f-bcf4-1c28815043dd">EULA <i class="fal fa-arrow-up-right-from-square ml-auto"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex" href="https://app.termly.io/document/disclaimer/c3c5eb9f-6576-4402-b294-11a7ac0704c1">Disclaimer <i class="fal fa-arrow-up-right-from-square ml-auto"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="index-content" style="background: #A6BFDE; ">         
            
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