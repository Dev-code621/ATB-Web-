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
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
    <meta name="theme-color" content="#A6BFDE" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#A6BFDE" media="(prefers-color-scheme: dark)">
    
    <title>ATB Admin Portal</title>
    <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
   

</head>
<body>
  
    <main class="bgEndWhite">
        <header class="app-header container">
            <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-user-large-slash"></i> Blocked/Removed</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
        </header>
            
        <section class="notification-container multiple-items scrollable container">
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($blockUsers));?></span>
                <span class="notification-label">Blocked Users</span>                    
            </div>
            <!-- <div class="notification-info">
                <span class="notification-qty"><?php echo(count($removedUsers));?></span>
                <span class="notification-label">Removed Users</span>                    
            </div>
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($frozenUsers));?></span>
                <span class="notification-label">Frozen Users</span>                    
            </div> -->
        </section>


        <div class="tabs-container">
            <div class="navTabs position-relative">
                <button class="btn tablinks active" data-tab="all">All</button>
                <button class="btn tablinks" data-tab="blocked">Blocked</button>
                <!-- <button class=" btn tablinks" data-tab="removed">Removed</button>
                <button class=" btn tablinks" data-tab="freezed">Freezed Accounts</button> -->
            </div>
            
            <div class="data-container blocked-data tab-content-wrapper container">
                <div data-tabcontent="all" class="tabcontent" style="display: block;">
                
                    <?php foreach($users as $user):?>
                        <div class="data-item">
                            <div class="user-info"> 
                                <div class="user-icon">
                                    <img src="<?php echo $user['pic_url']?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                    <h2 class="user-name"><?php echo $user['user_name'];?></h2>
                                    <p class="user-mail"><?php echo $user['user_email'];?></p>
                                    <p><i class="fa-solid fa-quote-left"></i> <?php echo $user['status_reason'];?></p>                                
                                    <?php
                                        switch ($user['status']) {
                                            case 0:
                                                echo '<span class="tag blocked">inactive<i class="fa-regular fa-ban"></i></span>'  ;                                   'blocked';

                                                break;
                                            case 1:
                                                echo '<span class="tag blocked"><i class="fa-regular fa-ban"></i> Blocked</span>'  ;                                   'blocked';
                                                break;
                                            case 2:
                                                echo '<span class="tag freezed"><i class="fa-regular fa-ban"></i> Freezed</span>';
                                                break;
                                            case 3:
                                                echo 'normal';
                                                break;
                                            case 4:
                                                echo '<span class="tag removed"><i class="fa-regular fa-circle-minus"></i> Removed</span>';
                                                break;
                                        }
                                        ?>
                                
                                </div>
                            </div>
                        </div>
                   <?php endforeach;?>
                </div>
                <div data-tabcontent="blocked" class="tabcontent">
                  <?php foreach($blockUsers as $user):?>
                    <div class="data-item">
                        <div class="user-info"> 
                            <div class="user-icon">
                               <img src="<?php echo $user['pic_url']?>" alt="User icon">                               
                            </div>
                            <div class="user-info-content">
                                <h2 class="user-name"><?php echo $user['user_name'];?></h2>
                                <p class="user-mail"><?php echo $user['user_email'];?></p>
                                <p><i class="fa-solid fa-quote-left"></i> <?php echo $user['status_reason'];?></p>
                                <span class="tag blocked"><i class="fa-regular fa-ban"></i> Blocked</span>
                            </div>
                        </div>
                    </div>
                   <?php endforeach;?>

                </div>
                <div data-tabcontent="removed" class="tabcontent">
                    <?php foreach($removedUsers as $user):?>
                        <div class="data-item">
                            <div class="user-info"> 
                                <div class="user-icon">
                                    <img src="<?php echo $user['pic_url']?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                    <h2 class="user-name"><?php echo $user['user_name'];?></h2>
                                    <p class="user-mail"><?php echo $user['user_email'];?></p>
                                    <p><i class="fa-solid fa-quote-left"></i> <?php echo $user['status_reason'];?></p>
                                    <span class="tag removed"><i class="fa-regular fa-circle-minus"></i> Removed</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;?>

                </div>
                <div data-tabcontent="freezed" class="tabcontent">
                   <?php foreach($frozenUsers as $user):?>
                        <div class="data-item">
                            <div class="user-info"> 
                                <div class="user-icon">
                                    <img src="<?php echo $user['pic_url']?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                    <h2 class="user-name"><?php echo $user['user_name'];?></h2>
                                    <p class="user-mail"><?php echo $user['user_email'];?></p>
                                    <p><i class="fa-solid fa-quote-left"></i> <?php echo $user['status_reason'];?></p>
                                    <span class="tag freezed"><i class="fa-regular fa-ban"></i> Freezed</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach;?>
                </div>
            </div>
        </div>
    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>

</body>
</html>