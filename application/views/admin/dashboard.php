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
  
    <main class="has-bottomNav">
        <div class="container text-center">
            <img src="<?php echo base_url();?>admin_assets/images/logo-white.svg" alt="Logo ATB" class="img-fluid dashboardLogo">
            <div class="feed-date"><span><?php echo(date("F j, Y"));?> </span></div>
        </div>
       
            <div class="feed-navigation">
                <div class="nav-item">
                    <a href="<?php echo route('admin.signups.index');?>">
                        <i class="fa-thin fa-users-medical"></i>
                        <span>Members</span>
                        <span class="notification-icon"> <?php echo ($this->session->userdata('user_count')) ?></span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo route('admin.business.index');?>">
                        <i class="fa-thin fa-suitcase"></i>
                        <span>Business</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo route('admin.notifications.index');?>">
                        <i class="fa-thin fa-bell-on"></i>
                        <span>Notifications</span>
                        <span class="notification-icon"><?php echo ($this->session->userdata('notification_count')) ?></span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo route('admin.reported_post.index');?>">
                        <i class="fa-thin fa-info-circle"></i>
                        <span>Reported Posts</span>
                        <span class="notification-icon"><?php echo ($this->session->userdata('report_count')) ?></span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo route('admin.users.index');?>">
                        <i class="fa-thin fa-user-alt-slash"></i>
                        <span>Blocked/Removed Users</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo route('admin.booking.index');?>">
                        <i class="fa-thin fa-calendar-star"></i>
                        <span>Bookings</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo route('admin.feeds.index');?>">
                        <i class="fa-thin fa-newspaper"></i>
                        <span>Feed</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo route('admin.tickets.index');?>">
                        <i class="fa-thin fa-user-headset"></i>
                        <span>Support Tickets</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo route('admin.admin.index');?>">
                        <i class="fa-thin fa-users-cog"></i>
                        <span>Admin</span>
                    </a>
                </div>
                <div class="nav-item">

                    <a href="<?php echo route('admin.auth.logout');?>"  class="profile">
                        <span><small>Logged in as:</small> <?php echo ($this->session->userdata('user_name')) ?></span>
                        <?php
                            $picURL = base_url()."admin_assets/images/samples/profile-sample.png";
                            if (!empty($this->session->userdata('profile_pic'))){
                                $picURL = base_url().$this->session->userdata('profile_pic');
                            }
                        ?>
                        <img src= "<?php echo $picURL;?>" alt="">

                    </a>
                </div>
            </div>


        <div class="navigation">
            <nav>
                <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="active">
                    <i class="fa-light fa-gauge"></i>
                    Dashboard
                </a>
                <a href="<?php echo route('admin.chat.index');?>">
                    <i class="fa-light fa-messages"></i>
                    Messages
                </a>
                <a href="<?php echo route('admin.auth.logout');?>">
                    <i class="fa-light fa-right-from-bracket"></i>
                    Log Out
                </a>
                <a href="<?php echo route('admin.mainpages.index');?>">
                      <?php
                            $picURL = base_url()."admin_assets/images/samples/profile-sample.png";
                            if (!empty($this->session->userdata('profile_pic'))){
                                $picURL = base_url().$this->session->userdata('profile_pic');
                            }
                        ?>
                    <img src="<?php echo $picURL;?>" alt="">
                    Richard
                </a>
            </nav>
        </div>
    </main>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>

</body>
</html>