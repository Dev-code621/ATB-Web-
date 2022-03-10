
<?php
$user_id= $this->session->userdata('user_id');
$user_name= $this->session->userdata('user_name');
$profile_pic= $this->session->userdata('profile_pic');
if(empty($profile_pic)){
    $profile_pic = base_url()."admin_assets/logo.png";
} else{
    $profile_pic = base_url(). $profile_pic;
}

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
                <a href="javascript:history.go(-1)" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
                <div class="user-icon online">
                    <img src="<?php echo  $rooms['image'];?>" alt="User icon">
                </div>
                <h1 class="page-title"> <?php echo  $rooms['title'];?></h1>
            </div>
            <!-- <a href="#" class="nav-link"><i class="fa-regular fa-bars"></i></a> -->
        </header>
      
        <section class="chat-container bg-white" id="chatContainer">

            <div class="messages-container container" id= "div_contain">
                <div class="messages-content" id = "div_list">
                                    
                </div>

            </div>
            <div class="chat-bottom container">
                <form class="d-flex" action="" method="post">
                    <div class="chat-input">
                        <!-- <button class="btn emojiBtn"><i class="fa-regular fa-face-smile"></i></button> -->
                        <textarea type="text" placeholder="Message..." id="messageInput"></textarea>
                        <!-- <button class="btn fileBtn"><i class="fa-regular fa-paperclip"></i></button> -->
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
        var user_id = '1';
        var user_name = '<?php echo  $user_name; ?>';
        var profile_pic = '<?php echo  $profile_pic; ?>';
        var channelId = '<?php echo  $rooms['channel']; ?>';
        var pageCount = 0;
        var messageModels = [];
        $(document).ready(function()
        {    
            
            
            pubnub = new PubNub({
                publishKey : "<?php echo $publish_key ?>",
                subscribeKey : "<?php echo $sub_key ?>",
                uuid: user_id + "#ADMIN"
            })

          
            pubnub.addListener({
                status: function(statusEvent) {

                    if (statusEvent.category === "PNConnectedCategory") {
                    }
                    
                },
                message: function(object) {                         
                    var result = pubnub.objects.setMemberships({                        
                        channel:channelId,
                        channels: [ {
                            id:channelId,
                            custom: { "lastReadTimetoken": object.timetoken } 
                        }],                    
                    },                   
                    );
                    
                    // var result = pubnub.objects.getMemberships({
                    //     include: {
                    //         customFields: true
                    //     }
                    // },
                    // function (status, response) {
                    //     console.log(channelId, JSON.stringify(response));

                    // }
                    // );

            
                    if(channelId.localeCompare(object.channel)  == 0 ){
                 
                        try {

                            var message =  object.message.text
                            var sender = object.message.sender;
                            // alert(sender.id.localeCompare("ADMIN_" + user_id));
                            var div_pic = "";
                            if(object.message.messageType.localeCompare("Image") == 0){
                                div_pic =  '<img src="' + message + '" alt="" style = " border-radius: 5%; width :200px; height:200px;">';
                                message = "";
                            }
                            if(sender.id.localeCompare("ADMIN_" + user_id) == 0){
                                let container = document.querySelectorAll('.messages-content')[0];
                                container.innerHTML += `<div class="chat-message outcome">${div_pic}${message}</div>`;
                                chatTextarea.value = '';
                                chatTextarea.style.height = 20 + 'px';
                            
                            }else{
                                let container = document.querySelectorAll('.messages-content')[0];
                                container.innerHTML += `
                                    <div class="messages-row-content" >
                                        <div class="column" >
                                            <img src="${sender.imageUrl}" alt="" style = " border-radius: 50%; width :40px; height:40px;">
                                        </div>
                                        <div class="column" >
                                            <div class="chat-message income">${div_pic}${message}</div>
                                        </div>
                                    </div>`;
                                chatTextarea.value = '';
                                chatTextarea.style.height = 20 + 'px';
                            }             

                        } catch (error) {
                          console.error(error);
                      
                        }
                    }
                
                },
                presence: function(presenceEvent) {         

                }
            })
            featchMessages(pageCount);
            pubnub.subscribe({
                     channels: [channelId]
            });
            $("#div_contain").scroll(function()
            {
                var div = $(this);
                // console.log(pageCount ,div[0].scrollHeight  ,div.scrollTop(),div.height());
                if (div[0].scrollHeight + div.scrollTop()-40 == div.height() )
                {
                    featchMessages(pageCount++);

                    // alert("Reached the bottom!");
                }
                else if(div.scrollTop() == 0)
                {
                    // alert("Reached the top!");
                }
            });
            
            
        });        
        function featchMessages(page){
            var messageTime = null;
            if(messageModels.length>0){
                 messageTime = messageModels[0].timetoken;
                // alert(JSON.stringify(messageModels[0]))

            }
            pubnub.fetchMessages(
                {
                    channels: [channelId],
                    stringifiedTimeToken: true,
                    includeMeta: true,
                    count:25,
                    start:messageTime,
                    includeMessageActions: true,
                },
                function (status, response) {
                    // handle status, response
                    var array = response.channels[channelId]
                    Array.prototype.push.apply(array, messageModels);
                    messageModels = array;
                    $('#div_list').empty();
                    for(var k in array) {
                        var object = array[k];
                        var message =  object.message.text
                       // alert(JSON.stringify(object.message));
                       try {
                            var sender = object.message.sender;
                            // alert(sender.id.localeCompare("ADMIN_" + user_id));
                            var div_pic = "";
                            if(object.message.messageType.localeCompare("Image") == 0){
                                div_pic =  '<img src="' + message + '" alt="" style = " border-radius: 5%; width :200px; height:200px;">';
                                message = "";
                            }
                            let container = document.querySelectorAll('.messages-content')[0];
                            if(sender.id.localeCompare("ADMIN_" + user_id) == 0){
                                container.innerHTML += `<div class="chat-message outcome">${div_pic}${message}</div>`;
                                chatTextarea.value = '';
                                chatTextarea.style.height = 20 + 'px';
                            
                            }else{
                                container.innerHTML += `
                                    <div class="messages-row-content" >
                                        <div class="column" >
                                            <img src="${sender.imageUrl}" alt="" style = " border-radius: 50%; width :40px; height:40px;">
                                        </div>
                                        <div class="column" >
                                            <div class="chat-message income">${div_pic}${message}</div>
                                        </div>
                                    </div>`;
                                chatTextarea.value = '';
                                chatTextarea.style.height = 20 + 'px';
                            }             

                        } catch (error) {
                          console.error(error);
                      
                        }

                    }
                }
            );  
        }

        function sendMessage() {       
            
            let chatTextarea = document.getElementById('messageInput');
            if(chatTextarea !== null ) {
                chatTextarea.addEventListener('keyup', function() {
                    this.style.height = this.scrollHeight + 'px';
                });
            }
                
            // e.preventDefault();
            let msg = chatTextarea.value;
            if (msg === "") {
                alert("Please input message");
                return;
            }
            var channelId = '<?php echo  $rooms['channel']; ?>';
            var messageType = "Text"
            var sender = new Object();
                sender.id = "ADMIN_" + user_id;
                sender.name  = user_name;
                sender.imageUrl = profile_pic;
            var publishPayload = {
                channel : channelId,
                message: {
                    "text":msg,
                    "messageType" : messageType,
                    "sender" :  sender                 
                }
            }

            pubnub.publish(publishPayload, function(status, response) {
                // let container = document.querySelectorAll('.messages-content')[0];
                //         container.innerHTML += `<div class="chat-message outcome">${msg}</div>`;
                //         chatTextarea.value = '';
                //         chatTextarea.style.height = 20 + 'px';
            })

            } 
    </script>   
  

</body>
</html>