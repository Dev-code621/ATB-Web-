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
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#A6BFDE" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#A6BFDE" media="(prefers-color-scheme: dark)">
    
    <title>ATB Admin Portal</title>
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
            <h1 class="page-title"><i class="fa-duotone fa-circle-info fa-swap-opacity"></i> Reported Posts</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
        </header>
        
        <!-- The "open" class comes from the tab, to define wich notification data to show first. -->
        <section class="notification-container container open" id="reportedPostNotification">
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($open_reports));?></span>
                <div>
                    <span class="notification-label">Posts have been reported</span>                    
                    <span class="notification-stats"><i class="fa-regular fa-chevron-up"></i> 3% more than last week</span>
                </div>
            </div>
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($closed_reports));?></span>
                <div>
                    <span class="notification-label">Reports have been closed</span>                    
                    <span class="notification-stats"><i class="fa-regular fa-chevron-up"></i> 3% more than last week</span>
                </div>
            </div>
            <div class="notification-info">
                <span class="notification-qty"><?php echo(count($ignored_reports));?></span>
                <div>
                    <span class="notification-label">Reports have been ignored</span>                    
                    <span class="notification-stats"><i class="fa-regular fa-chevron-up"></i> 3% more than last week</span>
                </div>
            </div>
        </section>


            <div class="tabs-container" id="reportedPostsTab">
                <div class="navTabs position-relative">
                    <button class="btn tablinks active" data-tab="open">Open</button>
                    <button class="btn tablinks" data-tab="closed">Closed</button>
                    <button class=" btn tablinks" data-tab="ignored">Ignored</button>
                </div>
                
                <div class="data-container tab-content-wrapper container">                   
                    <div data-tabcontent="open" class="tabcontent" style="display: block;">
                      <?php for($i = 0 ; $i < count($open_reports); $i++):?>
                        <div class="data-item">
                            <div class="user-info"> 
                                <div class="user-icon online">
                                    <img src="<?php echo $open_reports[$i]['reported_user']['profile']['pic_url'];?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                    <p><a href="<?php echo route('admin.signups.detail', $open_reports[$i]['reported_user']['profile']['id']);?>"> 
                                       @<?php echo $open_reports[$i]['reported_user']['profile']['user_name'];?></a> has reported 
                                       <?php if ($open_reports[$i]['post_id'] != 0) { ?>
                                                            the  <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['post']['user'][0]['id']);?>" > @<?php echo $open_reports[$i]['post']['user'][0]['user_name'];?> </a> post - <a href="<?php echo route('admin.signups.view_post', $open_reports[$i]['post']['id']);?>" ><?php echo $open_reports[$i]['post']["title"];?> </a>
                                                            <?php } else if ($open_reports[$i]['user_id'] != 0) { ?>
                                                            the user <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['user']['profile']['id']);?>"> @<?php echo $open_reports[$i]['user']['profile']['user_name'];?> </a>
                                                            <?php } ?>
                                    
                                    </p>
                                    <p><i class="fa-solid fa-quote-left"></i><?php echo $open_reports[$i]['content'];?></p>
                                    <div class="data-info ">
                                        <div class="data-info-item">
                                            <i class="fa-regular fa-calendar-day"></i>
                                            <span><?php echo date('d/m/Y', $open_reports[$i]['created_at']);?> </span>
                                        </div>
                                        <div class="data-info-item">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo date('H:i:s', $open_reports[$i]['created_at']);?></span>
                                        </div>
                                    </div>
                                    <?php 
                                    if($open_reports[$i]['post_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.signups.view_post', $open_reports[$i]['post_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View Post</a>
                                    <?php } elseif($open_reports[$i]['user_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['user_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View User</a>
                                    <?php } elseif($open_reports[$i]['comment_id'] != 0) { ?>
                                        <a href="<?php echo route('admin.reported_post.commentreport', $open_reports[$i]['comment_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View Comment</a>
                                            
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                        <?php endfor;?>
                        
                    </div>

                    <div data-tabcontent="closed" class="tabcontent">
                        <?php for($i = 0 ; $i < count($closed_reports); $i++):?>
                            <div class="data-item">
                                <div class="user-info"> 
                                    <div class="user-icon online">
                                        <img src="<?php echo $closed_reports[$i]['reported_user']['profile']['pic_url'];?>" alt="User icon">
                                    </div>
                                    <div class="user-info-content">
                                        <p><a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['reported_user']['profile']['id']);?>"> 
                                        @<?php echo $closed_reports[$i]['reported_user']['profile']['user_name'];?></a> has reported 
                                        <?php if ($closed_reports[$i]['post_id'] != 0) { ?>
                                                                the  <a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['post']['user'][0]['id']);?>" > @<?php echo $closed_reports[$i]['post']['user'][0]['user_name'];?> </a> post - <a href="<?php echo route('admin.signups.view_post', $closed_reports[$i]['post']['id']);?>" ><?php echo $closed_reports[$i]['post']["title"];?> </a>
                                                                <?php } else if ($closed_reports[$i]['user_id'] != 0) { ?>
                                                                the user <a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['user']['profile']['id']);?>"> @<?php echo $closed_reports[$i]['user']['profile']['user_name'];?> </a>
                                                                <?php } ?>
                                        
                                        </p>
                                        <p><i class="fa-solid fa-quote-left"></i><?php echo $closed_reports[$i]['content'];?></p>
                                        <div class="data-info ">
                                            <div class="data-info-item">
                                                <i class="fa-regular fa-calendar-day"></i>
                                                <span><?php echo date('d/m/Y', $closed_reports[$i]['created_at']);?> </span>
                                            </div>
                                            <div class="data-info-item">
                                                <i class="fa-regular fa-clock"></i>
                                                <span><?php echo date('H:i:s', $closed_reports[$i]['created_at']);?></span>
                                            </div>
                                        </div>
                                        <?php if($closed_reports[$i]['post_id'] != 0) { ?>
                                            <a href="<?php echo route('admin.signups.view_post', $closed_reports[$i]['post_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View Post</a>
                                        <?php } elseif($closed_reports[$i]['user_id'] != 0) { ?>
                                            <a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['user_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View User</a>

                                        <?php }?>
                                    </div>
                                </div>
                            </div>
                         <?php endfor;?>
                    </div>
                    <div data-tabcontent="ignored" class="tabcontent">
                    <?php for($i = 0 ; $i < count($ignored_reports); $i++):?>
                            <div class="data-item">
                                <div class="user-info"> 
                                    <div class="user-icon online">
                                        <img src="<?php echo $ignored_reports[$i]['reported_user']['profile']['pic_url'];?>" alt="User icon">
                                    </div>
                                    <div class="user-info-content">
                                        <p><a href="<?php echo route('admin.signups.detail', $ignored_reports[$i]['reported_user']['profile']['id']);?>"> 
                                        @<?php echo $ignored_reports[$i]['reported_user']['profile']['user_name'];?></a> has reported 
                                        <?php if ($ignored_reports[$i]['post_id'] != 0) { ?>
                                                                the  <a href="<?php echo route('admin.signups.detail', $ignored_reports[$i]['post']['user'][0]['id']);?>" > @<?php echo $ignored_reports[$i]['post']['user'][0]['user_name'];?> </a> post - <a href="<?php echo route('admin.signups.view_post', $ignored_reports[$i]['post']['id']);?>" ><?php echo $ignored_reports[$i]['post']["title"];?> </a>
                                                                <?php } else if ($closed_reports[$i]['user_id'] != 0) { ?>
                                                                the user <a href="<?php echo route('admin.signups.detail', $ignored_reports[$i]['user']['profile']['id']);?>"> @<?php echo $ignored_reports[$i]['user']['profile']['user_name'];?> </a>
                                                                <?php } ?>
                                        
                                        </p>
                                        <p><i class="fa-solid fa-quote-left"></i><?php echo $ignored_reports[$i]['content'];?></p>
                                        <div class="data-info ">
                                            <div class="data-info-item">
                                                <i class="fa-regular fa-calendar-day"></i>
                                                <span><?php echo date('d/m/Y', $ignored_reports[$i]['created_at']);?> </span>
                                            </div>
                                            <div class="data-info-item">
                                                <i class="fa-regular fa-clock"></i>
                                                <span><?php echo date('H:i:s', $ignored_reports[$i]['created_at']);?></span>
                                            </div>
                                        </div>
                                        <?php if($ignored_reports[$i]['post_id'] != 0) { ?>
                                            <a href="<?php echo route('admin.signups.view_post', $ignored_reports[$i]['post_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View Post</a>
                                        <?php } elseif($ignored_reports[$i]['user_id'] != 0) { ?>
                                            <a href="<?php echo route('admin.signups.detail', $ignored_reports[$i]['user_id']);?>" class="btn btn-sm btn-primary"><i class="fa-regular fa-flag"></i> View User</a>

                                        <?php }?>
                                    </div>
                                </div>
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