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
    <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="manifest" href="<?php echo base_url();?>admin_assets/manifest.json">
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
    <script src="https://cdn.pubnub.com/sdk/javascript/pubnub.4.37.0.js"></script>

</head>
<body>
  
    <main class="has-bottomNav" id="messages-page">
        <header class="app-header container">
            <h1 class="page-title"><i class="fa-duotone fa-messages"></i> Messages</h1>
            <a href="#" class="nav-link"><i class="fa-regular fa-magnifying-glass fa-swipe-opacity"></i></a>
        </header>
      
        <section class="messages-list container bg-white">
         <?php for($i = 0 ; $i < count($rooms); $i++):?>
                <a href="<?php echo route('admin.chat.detail', $rooms[$i]['channel']);?>" class="contact-item new-message">
                    <div class="<?php 
                        if( $rooms[$i]['online'] == 1)
                            {echo "user-icon online";}
                        else if( $rooms[$i]['online'] == 0)
                           { echo "user-icon offline";}
                        else if( $rooms[$i]['online'] == -1)
                           { echo "user-icon";}
                        ?>">
                        <img src="<?php echo  $rooms[$i]['image'];?>" alt="User icon">
                    </div>
                    <div class="message-info">
                        <h2 class="user-name">  <?php echo  $rooms[$i]['title'];?></h2>
                        <p><?php echo  $rooms[$i]['last_message'];?></p>
                    </div>
                    <span class="message-date">
                        Apr 14
                    </span>
                </a>         
            <?php endfor;?>              
 
        </section>
        
        <a href="<?php echo route('admin.chat.newchat', $user_id);?>" class="new-group">
            <i class="fa-solid fa-plus"></i>
        </a>



        <div class="navigation">
            <nav>
                <a href="<?php echo route('admin.dashboards.index', $user_id);?>" >
                    <i class="fa-light fa-gauge"></i>
                    Dashboard
                </a>
                <a href="<?php echo route('admin.chat.index');?>" class="active">
                    <i class="fa-light fa-messages"></i>
                    Messages
                </a>
                <a href="<?php echo route('admin.auth.logout');?>">
                    <i class="fa-light fa-right-from-bracket"></i>
                    Log Out
                </a>
                <a href="<?php echo route('admin.mainpages.index');?>">
                    <img src="<?php echo base_url();?>admin_assets/images/samples/profile-sample.png" alt="">
                    Richard
                </a>
            </nav>
        </div>
    </main>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
    <script>
        function letsGo() {
            // Update this block with your publish/subscribe keys
            pubnub = new PubNub({
                publishKey : "pub-c-f93545e5-80db-4b7c-be40-d9c5b524383b",
                subscribeKey : "sub-c-5b6e32b8-5a80-11ec-a2d9-0639f9732331",
                uuid: "ATBADMIN"
            })
            function publishSampleMessage() {
                console.log("Publish to a channel 'hello_world'");
                // With the right payload, you can publish a message, add a reaction to a message,
                // send a push notification, or send a small payload called a signal.
                var publishPayload = {
                    channel : "hello_world",
                    message: {
                        title: "greeting",
                        description: "This is my first message!"
                    }
                }
                pubnub.publish(publishPayload, function(status, response) {
                    console.log(status, response);
                })
            }

            pubnub.addListener({
                status: function(statusEvent) {
                    if (statusEvent.category === "PNConnectedCategory") {
                        publishSampleMessage();
                    }
                },
                message: function(msg) {
                    console.log(msg.message.title);
                    console.log(msg.message.description);
                },
                presence: function(presenceEvent) {
                    // This is where you handle presence. Not important for now :)
                }
            })
            console.log("Subscribing...");

            pubnub.subscribe({
                channels: ['hello_world']
            });
            };
    </script>
</body>
</html>