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
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
   <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
   <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
   <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
   <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
   <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
   

</head>
<body>
  
    <main>
        <header class="app-header container">
            <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-briefcase"></i> Business Accounts</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
        </header>
            
        <section class="notification-container container">
            <div class="notification-info">
                <span class="notification-qty"> <?php echo(count($open_reports));?></span>
                <div>
                    <span class="notification-label">Businesses <br/> pending review</span>                    
                    <span class="notification-stats attention"><i class="fa-regular fa-circle-exclamation"></i> Needs attention</span>
                </div>
            </div>
            <div class="last-notification-stats">
                <span>Last signed up <strong></strong></span>
                <span>-</span>
                <span><span class="total-qty"><?php echo(count($closed_reports));?></span> active users</span>
            </div>
        </section>


        <div class="tabs-container">
            <div class="navTabs position-relative">
                <button class="btn tablinks active" data-tab="pending-review">Pending Review</button>
                <button class="btn tablinks" data-tab="approved">Approved</button>
                <button class=" btn tablinks" data-tab="rejected">Rejected</button>
            </div>
            
            <div class="data-container tab-content-wrapper business-tabs container">
                
                <div data-tabcontent="pending-review" class="tabcontent pending-review" style="display: block;">
                   <?php for($i = 0 ; $i < count($open_reports); $i++):?>
                        <div class="data-item">
                            <div class="user-info">
                                <div class="user-icon">

                                    <img src="<?php echo $open_reports[$i]['business_logo'];?>">
                                </div>
                                <div class="user-info-content">
                                    <h2 class="user-name"><?php echo $open_reports[$i]['user']['profile']['first_name'];?> <?php echo $open_reports[$i]['user']['profile']['last_name'];?> </h2>
                                    <p class="user-username">@<?php echo $open_reports[$i]['user']['profile']['user_name'];?> </p>
                                    <p>
                                        <?php if ($open_reports[$i]['type'] == "business") { ?>
                                                has submitted the business for approval
                                                <?php } else {  ?>
                                                has submitted a service against their business
                                        <?php }   ?>
                                    </p>
                                    <a href="#" class="business-type">
                                        <i class="fa-regular fa-briefcase business-icon "></i>
                                        <?php echo $open_reports[$i]['business_name'];?> <i class="fas fa-chevron-right"></i>
                                    </a>
                                    <div class="data-info data-info-list">
                                        <div class="data-info-item date">
                                            <i class="fa-regular fa-calendar-day"></i>
                                            <span><?php echo date('d/m/Y', $open_reports[$i]['created_at']);?></span>
                                        </div>
                                        <div class="data-info-item time">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo date('H:i:s', $open_reports[$i]['created_at']);?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn-actions">
                                <a href="#" class="btn btn-outline-dark mr-10" >Message User</a>
                                <a href="<?php echo route('admin.business.detail', $open_reports[$i]['id']);?>" class="btn btn-primary">View Business Details</a>
                            </div>
                        </div>
                    <?php endfor;?>
                </div>

                <div data-tabcontent="approved" class="tabcontent approved">
                   <?php for($i = 0 ; $i < count($closed_reports); $i++):?>

                        <div class="data-item">
                            <div class="user-info">
                                <div class="user-icon">
                                    <img src="<?php echo $closed_reports[$i]['business_logo'];?>">
                                </div>
                                <div class="user-info-content">
                                    <h2 class="user-name"><?php echo $closed_reports[$i]['user']['profile']['first_name'];?> <?php echo $closed_reports[$i]['user']['profile']['last_name'];?> </h2>
                                    <p class="user-username">@<?php echo $closed_reports[$i]['user']['profile']['user_name'];?></p>
                                    <a href="#" class="business-type">
                                        <i class="fa-regular fa-briefcase business-icon "></i>
                                        <i class="fa-solid fa-circle-check"></i>
                                        <?php echo $closed_reports[$i]['business_name'];?> <i class="fa-regular fa-chevron-right"></i>
                                    </a>
                                    <div class="data-info data-info-list">
                                        <div class="data-info-item date">
                                            <i class="fa-regular fa-calendar-day"></i>
                                            <span><?php echo date('d/m/Y', $closed_reports[$i]['created_at']);?></span>
                                        </div>
                                        <div class="data-info-item time">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo date('H:i:s', $closed_reports[$i]['created_at']);?></p></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn-actions">
                                <a href="#" class="btn btn-outline-dark mr-10" >Message User</a>
                                <a href="<?php echo route('admin.business.detail', $closed_reports[$i]['id']);?>" class="btn btn-primary">View Business Details</a>
                            </div>
                        </div>
                    <?php endfor;?>
                </div>

                <div data-tabcontent="rejected" class="tabcontent rejected">
                    <?php for($i = 0 ; $i < count($ignored_reports); $i++):?>
                        <div class="data-item">
                            <div class="user-info">
                                <div class="user-icon">
                                    <img src="<?php echo $ignored_reports[$i]['business_logo'];?>">
                                </div>
                                <div class="user-info-content">
                                    <h2 class="user-name"><?php echo $ignored_reports[$i]['user']['profile']['first_name'];?> <?php echo $ignored_reports[$i]['user']['profile']['last_name'];?> </h2>
                                    <p class="user-username">@<?php echo $ignored_reports[$i]['user']['profile']['user_name'];?></p>
                                    <a href="#" class="business-type">
                                        <i class="fa-regular fa-briefcase business-icon "></i>
                                        <i class="fa-regular fa-circle-xmark"></i>
                                        <?php echo $ignored_reports[$i]['business_name'];?> <i class="fa-regular fa-chevron-right"></i>
                                    </a>
                                    <div class="data-info data-info-list">
                                        <div class="data-info-item date">
                                            <i class="fa-regular fa-calendar-day"></i>
                                            <span><?php echo date('d/m/Y', $ignored_reports[$i]['created_at']);?></span>
                                        </div>
                                        <div class="data-info-item time">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo date('H:i:s', $ignored_reports[$i]['created_at']);?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn-actions">
                                <a href="#" class="btn btn-outline-dark mr-10" >Message User</a>
                                <a href="<?php echo route('admin.business.detail', $ignored_reports[$i]['id']);?>" class="btn btn-primary">View Business Details</a>
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