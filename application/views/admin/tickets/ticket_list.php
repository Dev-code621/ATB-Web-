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
  
    <main class="bgEndWhite feed-page">
        <header class="app-header container">
            <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-user-headset"></i> Support Tickets</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
        </header>

        <section class="data-container container support-container">
          <?php for($i = 0 ; $i < count($tickets); $i++):?>
            <div class="data-item d-flex">
                <div class="user-info-content">
                    <span class="post-tag"><i class="fa-solid fa-ticket-simple"></i> Ticket Number</span> 
                    <span class="post-info"><?php echo $tickets[$i]['number'];?></span>
                    <div class="post-content">
                        <p><?php echo $tickets[$i]['title'];?></p>
                    </div>
                    <div class="data-info data-info-list">
                        <div class="data-info-item">
                            <i class="fa-solid fa-circle-user"></i>
                            <span><a href="mailto:"><?php echo $tickets[$i]['user']["login"];?></a></span>
                        </div>
                        <div class="data-info-item open">
                            <i class="fa-solid fa-ticket-simple"></i>
                            <span><?php echo $tickets[$i]['state']["name"];?></span>
                        </div>
                    </div>
                    <div class="data-info">
                        <div class="data-info-item">
                            <i class="fa-regular fa-calendar-day"></i>
                            <?php $date = date_create($tickets[$i]['updated_at']);?>
                            <span><?php echo date_format($date, 'd/m/Y');?></span>
                        </div>
                        <div class="data-info-item time">
                            <i class="fa-regular fa-clock"></i>
                            <span><?php echo date_format($date, 'H:i:s');?></span>
                        </div>
                    </div>
                </div>
                <a href="<?php echo route('admin.tickets.detail', $tickets[$i]['id']);?>" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
            </div>    
            <?php endfor;?>

        </section>
    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
   <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>

</body>
</html>