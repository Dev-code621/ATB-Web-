<?php
$user_id= $this->session->userdata('user_id');

if(!$user_id){
	redirect(route('admin.auth.login'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <meta name="theme-color" content="#A6BFDE" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#A6BFDE" media="(prefers-color-scheme: dark)">
    
    <title>ATB Admin Portal</title>
    <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
</head>
<body>
  
    <main class="has-bottomNav" id="messages-page">
        <header class="app-header container">
            <h1 class="page-title"><i class="fa-duotone fa-messages"></i> Messages</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass fa-swipe-opacity"></i></a>
        </header>
      
        <section class="messages-list container bg-white">
        
            <a href="chat.html" class="contact-item new-message">
                <div class="user-icon online">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                </div>
                <div class="message-info">
                    <h2 class="user-name">Phoenix</h2>
                    <p>Offensive language ⚠️</p>
                </div>
                <span class="message-date">
                    Apr 14
                </span>
            </a>
            <a href="chat.html" class="contact-item new-message">
                <div class="user-icon online">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                </div>
                <div class="message-info">
                    <h2 class="user-name">Mercy</h2>
                    <p>nm, can you please tell me testing testing testing</p>
                </div>
                <span class="message-date">
                    Apr 14
                </span>
            </a>
            <a href="chat.html" class="contact-item">
                <div class="user-icon offline">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                </div>
                <div class="message-info">
                    <h2 class="user-name">Tripp</h2>
                    <p>Thanks…</p>
                </div>
                <span class="message-date">
                    Apr 14
                </span>
            </a>
            <a href="chat.html" class="contact-item">
                <div class="user-icon offline">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                </div>
                <div class="message-info">
                    <h2 class="user-name">Ruby</h2>
                    <p>Thanks…</p>
                </div>
                <span class="message-date">
                    Apr 14
                </span>
            </a>
            <a href="chat.html" class="contact-item">
                <div class="user-icon online">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                </div>
                <div class="message-info">
                    <h2 class="user-name">Leo</h2>
                    <p>Thanks…</p>
                </div>
                <span class="message-date">
                    Apr 14
                </span>
            </a>
            <a href="chat.html" class="contact-item">
                <div class="user-icon">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                </div>
                <div class="message-info">
                    <h2 class="user-name">Kelly</h2>
                    <p>Thanks…</p>
                </div>
                <span class="message-date">
                    Apr 14
                </span>
            </a>
            <a href="chat.html" class="contact-item">
                <div class="user-icon">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                </div>
                <div class="message-info">
                    <h2 class="user-name">Kelly</h2>
                    <p>Thanks…</p>
                </div>
                <span class="message-date">
                    Apr 14
                </span>
            </a>
            <a href="chat.html" class="contact-item">
                <div class="user-icon">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                </div>
                <div class="message-info">
                    <h2 class="user-name">Kelly</h2>
                    <p>Thanks…</p>
                </div>
                <span class="message-date">
                    Apr 14
                </span>
            </a>
        </section>
        
        <a href="new-message.html" class="new-group">
            <i class="fa-solid fa-plus"></i>
        </a>



        <div class="navigation">
            <nav>
                <a href="<?php echo route('admin.dashboards.index', $user_id);?>" >
                    <i class="fa-light fa-gauge"></i>
                    Dashboard
                </a>
                <a href="<?php echo route('admin.chat.index');?>" class="active">
                    <i class="fa-light fa-messages"></i>
                    Messages
                </a>
                <a href="<?php echo route('admin.auth.logout');?>">
                    <i class="fa-light fa-right-from-bracket"></i>
                    Log Out
                </a>
                <a href="<?php echo route('admin.mainpages.index');?>">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/profile-sample.png" alt="">
                    Richard
                </a>
            </nav>
        </div>
    </main>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>

</body>
</html>