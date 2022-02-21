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
         <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
         <h1 class="page-title"><i class="fa-duotone fa-calendar-star"></i> Transaction</h1>
         <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass"></i></a>
      </header>
            
      <section class="notification-container container">
         <div class="notification-info">
            <span class="notification-qty"><?php echo(count($alltransaction));?></span>
            <div>
               <span class="notification-label">All Transaction <br></span>                    
               <!-- <span class="notification-stats"><i class="fa-regular fa-chevron-up"></i> 3% more than last week</span> -->
            </div>
         </div>
      </section>

      <section class="data-container">
         <div class="container">
            <h2 class="data-container-title"><i class="fa-regular fa-calendar-clock"></i> Latest</h2>
            <table class="transactions" cellspacing="0" cellpadding="0" style="width: 100%;">
                        <thead>
                            <tr>
                                <!-- <th scope="col"><i class="fa-regular fa-receipt"></i> ID</th> -->
                                <th scope="col"><i class="fa-regular fa-sterling-sign"></i> Amount</th>
                                <th scope="col"><i class="fa-regular fa-calendar"></i> Date</th>
                                <th scope="col"><i class="fa-regular fa-calendar"></i> Transaction Type</th>

                            </tr>
                        </thead>    
                        <tbody>
                        <?php for($i = count($alltransaction)-1 ; $i >=0 ; $i--):?>
                
                            <tr>
                                    <!-- <td><?php echo $alltransaction[$i]['transaction_id'];?></td> -->
                                    <td><?php echo  $alltransaction[$i]['amount'];?></td>
                                    <td><?php echo date('Y-m-d h:i:s', $alltransaction[$i]['created_at']);?> </td>
                                    <td><?php echo $alltransaction[$i]['transaction_type'];?></td>

                                 </tr>
                        <?php endfor;?>
               
                     </tbody>
                    </table>
            

         </div>
      </section>


   

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
   <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>

</body>
</html>