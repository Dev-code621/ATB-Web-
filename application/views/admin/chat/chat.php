
<?php
$user_id= $this->session->userdata('user_id');
$publish_key = $this->config->item('pubnub_publish_key');
$sub_key = $this->config->item('pubnub_sub_key');

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
    <link rel="icon" type="image/png" href="images/favicon.ico" />
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
  
    <main class="has-bottomNav" id="chat-page">
        <header class="app-header container">
            <div class="d-flex">
                <a href="<?php echo route('admin.chat.index');?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
                <div class="user-icon online">
                    <img src="<?php echo  $rooms['image'];?>" alt="User icon">
                </div>
                <h1 class="page-title"> <?php echo  $rooms['title'];?></h1>
            </div>
            <a href="#" class="nav-link"><i class="fa-regular fa-bars"></i></a>
        </header>
      
        <section class="chat-container bg-white" id="chatContainer">

            <div class="messages-container container">
                <div class="messages-content">
                                    
                </div>

            </div>
            <div class="chat-bottom container">
                <form class="d-flex" action="" method="post">
                    <div class="chat-input">
                        <button class="btn emojiBtn"><i class="fa-regular fa-face-smile"></i></button>
                        <textarea type="text" placeholder="Message..." id="messageInput"></textarea>
                        <button class="btn fileBtn"><i class="fa-regular fa-paperclip"></i></button>
                    </div>
                    <button type="button" onclick="sendMessage()" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
                    <!-- <button type="submit"  class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button> -->
                </form>
            </div>
      
        </section>
    </main>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
    <script src="https://cdn.pubnub.com/sdk/javascript/pubnub.4.37.0.js"></script>
    <script src="https://cdn.pubnub.com/sdk/javascript/pubnub.4.37.0.min.js"></script>
    <script>
        var pubnub = undefined;
        $(document).ready(function()
        {    
            
            
            pubnub = new PubNub({
                publishKey : "<?php echo $publish_key ?>",
                subscribeKey : "<?php echo $sub_key ?>",
                uuid: "ADMIN"
            })

 
            pubnub.addListener({
                status: function(statusEvent) {

                    if (statusEvent.category === "PNConnectedCategory") {
                    }
                },
                message: function(msg) {
                    alert(msg)
                },
                presence: function(presenceEvent) {
                    // This is where you handle presence. Not important for now :)
                }
            })

            var channelId = '<?php echo  $rooms['channel']; ?>';

            pubnub.fetchMessages(
                {
                    channels: [channelId],
                    stringifiedTimeToken: true,
                    includeMeta: true,
                    includeMessageActions: true,
                },
                function (status, response) {
                    // handle status, response
                    var array = response.channels[channelId]
                    for(var k in array) {
                        var object = array[k];
                        var message =  object.message.msg

                        let container = document.querySelectorAll('.messages-content')[0];
                        container.innerHTML += `<div class="chat-message outcome">${message}</div>`;
                        chatTextarea.value = '';
                        chatTextarea.style.height = 20 + 'px';
                    }
                }
            );  

        })
    </script>   
    <script type="text/JavaScript">
       function sendMessage() {       

            let chatTextarea = document.getElementById('messageInput');
            if(chatTextarea !== null ) {
                chatTextarea.addEventListener('keyup', function() {
                    this.style.height = this.scrollHeight + 'px';
                });
            }
                
            // e.preventDefault();
            let msg = chatTextarea.value;
            var channelId = '<?php echo  $rooms['channel']; ?>';

            var publishPayload = {
                channel : channelId,
                message: {
                    "msg":msg,
                    "timetoken" : "0",
                    "senderId" : "0",
                    "senderName" : "ATB Admin",
                    "senderImage" :  '<?php echo base_url();?>admin_assets/logo.png',
                    "messageType" : "Text",
                }
            }

            pubnub.publish(publishPayload, function(status, response) {
                let container = document.querySelectorAll('.messages-content')[0];
                        container.innerHTML += `<div class="chat-message outcome">${msg}</div>`;
                        chatTextarea.value = '';
                        chatTextarea.style.height = 20 + 'px';
            })

            } 
    </script>

</body>
</html>