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
  
    <main class="bgEndWhite">
      <header class="app-header container">
         <a href="javascript:history.go(-1)" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
         <h1 class="page-title"><i class="fa-duotone fa-calendar-star"></i> Bookings</h1>
         <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
      </header>
            
      <section class="notification-container container">
         <div class="notification-info">
            <span class="notification-qty"><?php echo(count($allBookings));?></span>
            <div>
               <span class="notification-label">New Bookings <br></span>                    
               <span class="notification-stats"><i class="fa-regular fa-chevron-up"></i> 3% more than last week</span>
            </div>
         </div>
      </section>

      <section class="data-container">
         <div class="container">
            <h2 class="data-container-title"><i class="fa-regular fa-calendar-clock"></i> Latest</h2>
            <?php for($i = count($allBookings)-1 ; $i >=0 ; $i--):?>

                <a href="<?php echo route('admin.booking.detail', $allBookings[$i]['id']);?>" class="data-item d-flex">
                <div class="user-info">
                    <div class="user-icon online">
                        <?php
                            $picURL = base_url()."admin_assets/img/generic-user.png";
                            if (!empty($allBookings[$i]['user'][0]["pic_url"])){
                                $picURL = $allBookings[$i]['user'][0]["pic_url"];
                            }
                        ?>
                        <img src="<?php echo  $picURL;?>" alt="User icon">
                    </div>
                    <div class="user-info-content booking-info">                        
                       <?php 
                            $username = "No User";
                            if (array_key_exists('user', $allBookings[$i])) {
                                $username = $allBookings[$i]['user'][0]["user_name"];
                            }       
                        ?>
                        <p><strong class="mr-5"><?php echo $username; ?> </strong> booked at:</p>
                        <div class="business-flex">
                            <p class="mr-10 color-blue"><strong><?php echo $allBookings[$i]['business'][0]["business_name"]; ?></strong></p>
                            <p>Service: <strong class="ml-5"><?php if(isset($allBookings[$i]["service"])){ echo $allBookings[$i]["service"][0]["title"]; } ?></strong></p>
                        </div>
                        <div class="business-flex">
                            <p class="color-success mr-10"><i class="fa-solid fa-circle mr-5"></i> <?php echo $allBookings[$i]["state"]; ?></p>
                            <p><i class="fa-solid fa-money-bill mr-5 color-blue"></i> <strong>£<?php echo $allBookings[$i]["total_cost"]; ?></strong></p>
                        </div>
                        <div class="data-info">
                            <div class="data-info-item date">
                                <i class="fa-regular fa-calendar-day"></i>
                                <span><?php echo date('d/m/Y', $allBookings[$i]['booking_datetime']);?></span>
                            </div>
                            <div class="data-info-item time">
                                <i class="fa-regular fa-clock"></i>
                                <span><?php echo date('H:i', $allBookings[$i]['booking_datetime']);?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="nav-icon"><i class="fa-regular fa-chevron-right"></i></span>
                </a>
              <?php endfor;?>

         </div>
      </section>


   

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
   <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>

</body>
</html>