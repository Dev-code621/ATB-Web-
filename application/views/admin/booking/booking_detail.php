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
    <meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: dark)">
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
    <title>ATB Admin Portal</title>
    <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
   

</head>
<body>
  
    <main class="posts-page booking-detail minPaddingTop bg-white">
        <div class="container">
            <a href="javascript:history.go(-1)" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title ml-20 mb-30"> Booking Detail</h1>
            <header>
                <div class="user-info">
                    <div class="user-icon online">
                        <img src="<?php echo $booking[0]["business"][0]["business_info"]['business_logo'];?>" alt="User icon">
                    </div>
                    <div class="user-info-content">
                        <h2 class="user-name"><?php echo $booking[0]["service"][0]['title'];?></h2>
                        <a href="#" class="user-username"> @<?php echo $booking[0]["business"][0]["business_info"]['business_name'];?></a>
                    </div>
                </div>
                <a href="<?php echo route('admin.business.detail', $booking[0]['business'][0]['id']);?>" class="nav-link-big"><i class="fa-solid fa-message-arrow-up-right"></i></a>
            </header>
        </div>


        <section class="data-container mt-30 bg-gray">
            <div class="container">
                <div class="business-info-content">
                    <h3 class="business-subtitle"><i class="fa-regular fa-briefcase"></i>Business</h3>
                    <p><?php echo $booking[0]['user'][0]['first_name'];?> <?php echo $booking[0]['user'][0]['last_name'];?> <a href="#"> @<?php echo $booking[0]['user'][0]['user_name'];?> </a></p>
                </div>
                <div class="business-info-content">
                    <h3 class="business-subtitle"><i class="fa-regular fa-flower-tulip"></i>Service</h3>
                    <p>Nails</p>
                </div>
                <div class="business-info-content">
                    <h3 class="business-subtitle"><i class="fa-regular fa-check"></i>Status</h3>
                    <p> <?php echo $booking[0]["state"]?></p>
                </div>
                <div class="business-info-content">
                    <div class="post-description">
                        <div class="content">
                            <span>Total Cost</span>
                            <strong>£<?php echo $booking[0]["total_cost"];?></strong>
                        </div>
                        <div class="content">
                            <span>Remaining</span>
                            <strong>
                                £<?php
                                $remaining = $booking[0]["total_cost"];
                                
                                for($i = 0 ; $i < count($booking[0]['transactions']); $i++):
                                    $amount = $booking[0]['transactions'][$i]['amount'];
                                    if ($amount < 0){
                                        $remaining = $remaining + $amount;
                                    }
                                
                                endfor;
                                echo $remaining;
                                ?>
                            </strong>
                        </div>
                        <div class="content full">
                            <span>Booked For</span>
                            <div>
                                <strong><i class="fa-regular fa-calendar"></i> <?php echo date('Y-m-d ',$booking[0]["booking_datetime"]);?></strong>
                                <strong><i class="fa-regular fa-clock ml-5"></i> <?php echo date('h:i:s ',$booking[0]["booking_datetime"]);?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <div>
                    <h3 class="page-title ml-20">Transactions</h3>
                    <table class="transactions" cellspacing="0" cellpadding="0" style="width: 100%;">
                        <thead>
                            <tr>
                                <th scope="col"><i class="fa-regular fa-receipt"></i> ID</th>
                                <th scope="col"><i class="fa-regular fa-sterling-sign"></i> Amount</th>
                                <th scope="col"><i class="fa-regular fa-calendar"></i> Date</th>
                            </tr>
                        </thead>    
                        <tbody>
                            
                            <?php for($i = 0 ; $i < count($booking[0]['transactions']); $i++):?>
                                <tr>
                                    <td><?php echo $booking[0]['transactions'][$i]['transaction_id'];?></td>
                                    <td><?php echo $booking[0]['transactions'][$i]['amount'];?></td>
                                    <td><?php echo date('Y-m-d h:i:s',$booking[0]['transactions'][$i]['created_at']);?> </td>
                                 </tr>
                               
                            <?php endfor;?>
                        </tbody>
                    </table>
                </div>
                
                
                
         
        </section>


            

       

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>

</body>
</html>