

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#f7f7f7" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#f7f7f7" media="(prefers-color-scheme: dark)">
    
    <title>ATB Admin Portal</title>
    <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
</head>

<body>
  
    <main id="login">
        <div class="container text-center">
            <img src="<?php echo base_url();?>admin_assets/images/logo-blue.svg" alt="Logo ATB">
            <h1>Admin Control</h1>
            <form m class="login-form" action="<?php echo route('admin.auth.do_login');?>" method="post">
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" placeholder=" " />
                    <span>email</span>
                </div>
                <div id="password-input" class="input-wrapper">
                    <input type="password" id="password" name="pass"  placeholder=" " />
                    <span>password</span>
                    <i class="fas fa-eye"></i>
                </div>
                <a href="<?php echo route('admin.auth.forgot_pass');?>">Forgot Password?</a>
                <button type="submit" class="btn btn-primary">Log In</button>
            </form>
        </div>
    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>

</body>
</html>
