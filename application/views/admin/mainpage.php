<?php
$user_id= $this->session->userdata('user_id');
if(!$user_id){
	redirect(route('admin.auth.login'));
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    
    <title>ATB Admin Portal</title>
    
   <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
   <meta name="mobile-web-app-capable" content="yes">
   <meta name="apple-mobile-web-app-capable" content="yes">
   <meta name="application-name" content="ATB Admin Portal">
   <meta name="apple-mobile-web-app-title" content="ATB Admin Portal">
   <meta name="msapplication-starturl" content="/index.html">

    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
   

</head>
<body>
    <div class="text-center">
        <br>
        <br>
        <a href='<?php echo route('admin.auth.logout');?>'> LOGIN </a> <br><br>
        <a href='<?php echo route('admin.dashboards.index', $user_id);?>'> DASHBOARD </a> <br><br>
        <a href='<?php echo route('admin.signups.index');?>'> SIGN UPS </a> <br><br>
        <!-- <a href='profile.html'> PROFILE </a> <br><br> -->
        <a href='<?php echo route('admin.business.index');?>'> BUSINESS </a> <br><br>
        <!-- <a href='business-detail.html'> BUSINESS DETAILS </a> <br><br> -->
        <a href='<?php echo route('admin.notifications.index');?>'> NOTIFICATIONS </a> <br><br>
        <a href='<?php echo route('admin.reported_post.index');?>'> REPORTED POSTS </a> <br><br>
        <!-- <a href='posts.html'> POST </a> <br><br> -->
        <!-- <a href='reported-posts.html'> REPORTED POSTS </a> <br><br> -->
        <a href='<?php echo route('admin.users.index');?>'> BLOCKED/REMOVED </a> <br><br>
        <a href='<?php echo route('admin.booking.index');?>'> BOOKINGS </a> <br><br>
        <!-- <a href='booking-detail.html'> BOOKING DETAIL </a> <br><br> -->
        <a href='<?php echo route('admin.feeds.index');?>'> FEED </a> <br><br>
        <a href='<?php echo route('admin.tickets.index');?>'> SUPPORT TICKET </a> <br><br>
       <!-- <a href='ticket-detail.html'> TICKET DETAIL </a> <br><br> -->
        <a href='<?php echo route('admin.admin.index');?>'> ADMIN </a> <br><br>
        <a href='<?php echo route('admin.chat.index');?>'> MESSAGES </a> <br><br>
        <!-- <a href='chat.html'> CHAT </a> <br><br> -->
        <a href='new-message.html'> NEW MESSAGE </a> <br><br>
        <a href='group.html'> GROUP </a> <br><br>
    </div>
    
    <div id="heightSize" style="text-align: center;"></div>

   <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
   <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
    <script>

     //   document.getElementById('heightSize').innerHTML = `${window.innerHeight}px`;

    </script>
</body>
</html>