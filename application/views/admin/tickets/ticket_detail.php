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
    <meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: dark)">
    
    <title>ATB Admin Portal</title>
    <link rel="icon" type="image/png" href="<?php echo base_url();?>admin_assets/images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
   

</head>
<body>
        <?php
                $updatedDate = date_create($ticket['updated_at']);
                $createdDate = date_create($ticket['created_at']);
            ?>
    <main class="ticket-detail bg-white minPaddingTop">
        <div class="container">
            <header>
                <a href="<?php echo route('admin.tickets.index');?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
                <div>
                    <h1 class="page-title">  <?php echo $ticket['title'];?></h1>
                    <div>
                        <span class="post-tag"><i class="fa-solid fa-ticket-simple"></i> Ticket Number</span> 
                        <span class="post-info"><?php echo $ticket['id'];?></span></span>
                    </div>
                </div>
            </header>
        </div>


        <section class="ticket-detail-container">
            <div class="container">
                <div class="ticket-detail-content">
                    <div class="data-info-item open">
                        <i class="fa-solid fa-ticket-simple"></i>
                        <span><?php echo $ticket['state']["name"];?></span>
                    </div>
                    <h4 class="title-sm">Ticket Opened at</h4>
                    <div class="data-info-item">
                        <i class="fa-regular fa-calendar-day"></i>
                        <span><?php echo date_format($createdDate, 'd/m/Y');?></span>
                        <i class="fa-regular fa-clock"></i>
                        <span><?php echo date_format($createdDate, 'H:i:s');?></span>
                    </div>
                    <h4 class="title-sm">Ticket last Updated at</h4>
                    <div class="data-info-item ">
                        <i class="fa-regular fa-calendar-day"></i>
                        <span> <?php echo date_format($updatedDate, 'd/m/Y');?> </span>
                        <i class="fa-regular fa-clock"></i>
                        <span> <?php echo date_format($updatedDate, 'H:i:s');?></span>
                    </div>
                </div>
                <div class="ticket-detail-content">

                    <h4 class="title-sm">Opened By</h4>
                    <p> <?php echo $ticket['user']['firstname'].' '.$ticket['user']['lastname'];?></p>
                    <a href="mailto:" class="user-mail"><i class="fa-solid fa-at"></i>  <?php echo $ticket['user']['email'];?></a>
                    
                    <h4 class="title-sm">Possible ATB User(s)</h4>
                    <?php foreach ( $userDetails as $user) { ?>
                        <a href="<?php echo route('admin.signups.detail', $user['id']);?>" class="btn btn-primary btn-sm btn-inline"><i class="fa-solid fa-circle-user"></i>  <?php echo $user["user_name"];?> <i class="fa-regular fa-chevron-right"></i></a>
                    <?php } ?>
                </div>
                    

                <div class="replies">
                    <h3>Replies</h3>
                    <?php foreach($ticket["articles"] as $article) { ?>
                        <div class="reply-content">
                            <div>
                                <p><strong>From</strong> <?php echo $article["from"]; ?></p>
                            </div>
                            <div>
                                <p><strong>Subject</strong> <p><?php echo $article["subject"]; ?></p></p>
                            </div>
                            <div>
                                <p><strong>Text</strong></p>
                                <p><?php echo $article["body"]; ?></p> 
                            </div>
                            <div>
                                <p><strong>Sent</strong></p>
                                <div class="data-info-item border-bottom">
                                    <i class="fa-regular fa-calendar-day"></i>
                                    <span><?php echo date_format(date_create($article['created_at']), 'd/m/Y');?></span>
                                    <i class="fa-regular fa-clock"></i>
                                    <span><?php echo date_format(date_create($article['created_at']), 'H:i:s');?></span>
                                </div>
                            </div>

                        </div>
                     <?php } ?>
                        

                </div>
               
            </div>
       
        </section>

        <div class="btn-footer">
            <a href="#" class="btn btn-outline-danger" data-modal="closeTicketModal"><i class="fa-solid fa-circle-xmark"></i>Close Ticket</a>
            <a href="#" class="btn btn-primary" data-modal="replyTicketModal"><i class="fa-solid fa-reply"></i>Reply to ticket</a>
        </div>
        
        <div class="modal" id="closeTicketModal">
            <div class="closeModal" data-close="closeTicketModal"><i class="fa-regular fa-circle-xmark"></i></div>
            <div>
                <div class="iconTitle centered"><i class="fa-solid fa-circle-xmark mr-5"></i></div>
                <h3 class="title">Testing this is still functioning</h3>
                <a href="#" class="post-tag"><i class="fa-solid fa-ticket-simple"></i> Ticket Number</a> 
                <span class="post-info">73010</span></span>
            </div>
            <form action="<?php echo route('admin.tickets.submit_delete');?>" method="get" enctype="multipart/form-data">
                <input type="hidden" id="ticketid" name="ticketid" value="<?php echo $ticket['id'];?>">
                <a href="#" class="btn btn-outline-danger btn-inline mt-15"><i class="fa-solid fa-circle-xmark mr-5"></i> Close Ticket</a>
            </form>
        </div>
            
        <div class="modal" id="replyTicketModal" action="<?php echo route('admin.tickets.submit_reply');?>" method="get" enctype="multipart/form-data">
            <div class="closeModal" data-close="replyTicketModal"><i class="fa-regular fa-circle-xmark"></i></div>
            <div class="text-center">
                <div class="iconTitle"><i class="fa-solid fa-reply color-blue"></i></div>
                <h3 class="mb-0">REPLY TO TICKET</h3>
                <h4>Reply</h4>
            </div>
            <form>

                <textarea rows="5" id="reply_text"  name = "reply_text" placeholder="Please input message"></textarea>
                <input type="hidden" id="ticketid" name="ticketid" value="<?php echo $ticket['id'];?>">

                <button type="submit"  class="btn btn-primary"><i class="fa-solid fa-reply"></i> Reply</button>
            </form>
        </div>

       

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>

</body>
</html>