<?php
use UI\Size;
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
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
   

</head>
<body>
  
    <main class="bgEndWhite">
        <header class="app-header container">
            <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-bell-on fa-swap-opacity"></i> Notifications</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
        </header>
            
        <section class="notification-container multiple-items container">
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($newNotifications));?></span>
                <div>
                    <span class="notification-label">Alerts <br> pending <br> review</span>                    
                </div>
            </div>
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($keywords));?></span>
                <div>
                    <span class="notification-label">Keywords <br> active</span>                    
                </div>
            </div>
        </section>


            <div class="tabs-container">
                <div class="navTabs position-relative">
                    <button class="btn tablinks active" data-tab="unread-notifications">Unread Notifications</button>
                    <button class="btn tablinks" data-tab="actioned-notifications">Actioned Notifications</button>
                    <button class=" btn tablinks" data-tab="keywords-alert">Keywords to Alert</button>
                </div>
                
                <div class="data-container tab-content-wrapper container">
                    <div data-tabcontent="unread-notifications" class="tabcontent" style="display: block;">
                        <?php for($i = 0 ; $i < count($newNotifications); $i++):
                                if($newNotifications[$i]['post'] == null) continue; ?>
                            <div class="data-item d-flex">
                                <div class="user-info"> 
                                    <div class="user-icon online">
                                        <img src="<?php echo $newNotifications[$i]['user']['profile']["pic_url"];?>" alt="User icon">
                                    </div>
                                    <div class="user-info-content">
                                        <p><a href="<?php echo route('admin.signups.detail', $newNotifications[$i]['user']['profile']['id']);?>"> @<?php echo $newNotifications[$i]['user']['profile']['user_name'];?></a>
                                         has written a <a href="<?php echo route('admin.signups.view_post', $newNotifications[$i]['post']['id']);?>">
                                             <?php if($newNotifications[$i]["type"] == 0) {
                                                    echo "comment";
                                                } else {
                                                    echo "post"; }?></a> that included the keyword <a href="#"><?php echo $newNotifications[$i]['keyword'][0]["keyword"]; ?></a>.</p>
                                        <div class="data-info ">
                                            <div class="data-info-item date">
                                                <i class="fa-regular fa-calendar-day"></i>
                                                <span> <?php echo date('d/m/Y', $newNotifications[$i]['created_at']);?></span>
                                            </div>
                                            <div class="data-info-item time">
                                                <i class="fa-regular fa-clock"></i>
                                                <span><?php echo date('H:i:s', $newNotifications[$i]['created_at']);?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
                            </div>                        
                        <?php endfor;?>                        
                    </div>

                    <div data-tabcontent="actioned-notifications" class="tabcontent">
                      <?php for($i = 0 ; $i < count($oldNotifications); $i++):
                               if($oldNotifications[$i]['post'] == null) continue; ?>

                        <div class="data-item d-flex">
                            <div class="user-info"> 
                                <div class="user-icon online">
                                    <img src="<?php echo $oldNotifications[$i]['user']['profile']["pic_url"];?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                    <p><a href="<?php echo route('admin.signups.detail', $oldNotifications[$i]['user']['profile']['id']);?>">@<?php echo $oldNotifications[$i]['user']['profile']['user_name'];?></a>
                                     has written a <a href="<?php echo route('admin.signups.view_post', $oldNotifications[$i]['post']['id']);?>">
                                      <?php if($oldNotifications[$i]["type"] == 0) {
                                            echo "comment";
                                        } else {
                                            echo "post";
                                        }?></a> that included the keyword <a href="#"><?php echo $oldNotifications[$i]['keyword'][0]["keyword"]; ?></a>.</p>
                                    <div class="data-info ">
                                        <div class="data-info-item date">
                                            <i class="fa-regular fa-calendar-day"></i>
                                            <span><?php echo date('d/m/Y', $oldNotifications[$i]['created_at']);?></span>
                                        </div>
                                        <div class="data-info-item time">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo date('H:i:s', $oldNotifications[$i]['created_at']);?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
                        </div>
                      <?php endfor;?>

                    </div>
                    <div data-tabcontent="keywords-alert" class="tabcontent">
                        <?php for($i = 0 ; $i < count($keywords); $i++):?>

                            <div class="data-item d-flex">
                                <div class="user-info"> 
                                    <div class="user-icon online">
                                        <img src="<?php echo base_url();?>admin_assets/images/samples/sample_profile_4.png" alt="User icon">
                                    </div>
                                    <div class="user-info-content">
                                        <p><a href="#">@HonestDec</a> has written a <a href="#">post</a> that included the keyword <a href="#">test</a>.</p>
                                        <div class="data-info ">
                                            <div class="data-info-item date">
                                                <i class="fa-regular fa-calendar-day"></i>
                                                <span>27/07/2021</span>
                                            </div>
                                            <div class="data-info-item time">
                                                <i class="fa-regular fa-clock"></i>
                                                <span>17:07</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
                            </div>
                            <?php endfor;?>
                    </div>
                </div>
            </div>

   

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
   <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>

</body>
</html>