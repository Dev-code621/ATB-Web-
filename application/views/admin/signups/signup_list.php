<?php
use UI\Size;
$user_id= $this->session->userdata('user_id');

if(!$user_id){
	redirect(route('admin.auth.login'));
}
$array = $users;
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
    <link rel="icon" type="image/png" href="images/favicon.ico" />
    <link rel="stylesheet" href="https://use.typekit.net/led0usk.css">
    <script src="https://kit.fontawesome.com/cfcaed50c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/main.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url();?>admin_assets/css/search.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

</head>
<body>
  
    <main>
        <header class="app-header container">
            <a href="<?php echo route('admin.dashboards.index', $user_id);?>" class="nav-link goBack"><i class="fa-regular fa-chevron-left"></i></a>
            <h1 class="page-title"><i class="fa-duotone fa-users-medical"></i>Last Members</h1>

            <!-- <a href="#" class="nav-link" ><i class="fa-regular fa-magnifying-glass"></i></a> -->
            <div class="search-box">            
                <button onclick = "search()" class="btn-search"><i class="fas fa-search"></i></button>
                <input onchange ="search()" type="text" class="input-search" placeholder="Search" id = "search"> 
            </div>
        </header>
        <section class="notification-container container">
            <div class="notification-info">
                <span class="notification-qty" id = "count"><?php echo(count($array));?></span>
                <div>
                    <span class="notification-label">New <br> Members</span>                    
                    <span class="notification-stats"><i class="fa-regular fa-chevron-up"></i> 3% more than last week</span>
                </div>
            </div>
            <!-- <div class="last-notification-stats">
                <span>Last signed up <strong>All users</strong></span>
                <span>-</span>
                <span><span class="total-qty"><?php count($array) ?></span> active users</span>
            </div> -->
        </section>

        <section class="data-container">
            <div class="container" id="item">
                <?php for($i = 0 ; $i < count($array); $i++):?>
                    <div class="data-item d-flex">
                        <div class="item-container">
                            <div class="user-info">
                               
                                <div class=  "<?php
                                    if(abs($array[$i]['online'] - time()) < 1800){
                                        echo 'user-icon online';
                                    }else{
                                        echo 'user-icon offline';
                                    }
                                    ?>">
                                    <?php
                                        $picURL = base_url()."admin_assets/img/generic-user.png";
                                        if (!empty($array[$i]['pic_url'])){
                                            $picURL = $array[$i]['pic_url'];
                                        }
                                    ?>
                                    <img src="<?php echo $picURL;?>" alt="User icon">
                                </div>
                                <div class="user-info-content">
                                    <h2 class="user-name">
                                       <?php
                                            if (!empty($array[$i]['user_name'])){
                                                echo $array[$i]['user_name'];
                                            } else {
                                                echo "Unknown Name";
                                            }
                                        ?>
                                    </h2>
                                    <p class="user-mail"><?php echo $array[$i]['user_email'];?></p>
                                </div>
                            </div>
                            <div class="data-info">
                                <div class="data-info-item date">
                                    <i class="fa-regular fa-calendar-day"></i>
                                    <span><?php echo date('d/m/Y', $array[$i]['created_at']);?></span>
                                </div>
                                <div class="data-info-item timeline">
                                    <i class="fa-regular fa-clock-rotate-left"></i>
                                    <span>
                                        <?php
                                            echo date('H:i', $array[$i]['created_at']);
                                            ?>
                                        </span>
                                </div>
                                <div class="data-info-item user-type">
                                    <i class="fa-regular fa-circle-user"></i>
                                    <span>
                                     <?php
                                        switch ($array[$i]['status']) {
                                            case 0:
                                                echo 'inactive';
                                                break;
                                            case 1:
                                                echo 'block';
                                                break;
                                            case 2:
                                                echo 'frozen';
                                                break;
                                            case 3:
                                                echo 'normal';
                                                break;
                                            case 4:
                                                echo 'removed';
                                                break;
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="data-info-item time">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>
                                        <?php
                                            echo $array[$i]['last_login_timestamp'];
                                            ?>
                                        </span>
                                </div>
                                <div class="data-info-item">
                                    <i class="fa-regular fa-signs-post"></i>
                                    <span><?php echo $array[$i]['post_count'];?> </p></span>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo route('admin.signups.detail', $array[$i]['id']);?>" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>
                    </div>
                <?php endfor;?>

            
            </div>

            <!-- <div class="ajax-loading">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                <p>Loading more results</p>
            </div> -->

        </section>

    </main>
    <script src="<?php echo base_url();?>admin_assets/js/config.js"></script>
    <script src="<?php echo base_url();?>admin_assets/js/main.js"></script>
    <script>
        var users = <?php echo json_encode($users);?>;
        function getbaseurl(){
            return <?php echo "'".base_url()."'"?>;
        }
        function search() {
             var query = document.getElementById("search").value;
             document.getElementById("search").value = "";
             $('#item').empty();
            var i=0;
            var filtered_users;
            for(i=0; i<users.length; i++){
                if(users[i].user_name.toLowerCase().includes(query.toLowerCase())){
                    var online_status = 'user-icon online';
                    if(Math.abs(users[i].online - new Date().getTime()) >= 1800){
                        online_status = 'user-icon offline';
                    }

                    var image_url = getbaseurl()+"admin_assets/img/generic-user.png";;
                    if(users[i].pic_url != null && users[i].pic_url.length > 5){
                        image_url = users[i].pic_url;
                    }

                    var status = 'inactive';
                    if(users[i].status == 1){
                        status = 'block';
                    }
                    if(users[i].status == 2){
                        status = 'frozen';
                    }
                    if(users[i].status == 3){
                        status = 'normal';
                    }
                    if(users[i].status == 4){
                        status = 'removed';
                    }


                    var item = '<div class="data-item d-flex">'+
                                    '<div class="item-container">'+
                                        '<div class="user-info">'+                                        
                                            '<div class=  "'+online_status+'">'+
                                                '<img src="'+image_url+'" alt="User icon">'+
                                            '</div>'+
                                            '<div class="user-info-content">'+
                                                '<h2 class="user-name">'+users[i].user_name+
                                                '</h2>'+
                                                '<p class="user-mail">'+users[i].user_email+'</p>'+
                                            '</div>'+
                                        '</div>'+
                                        '<div class="data-info">'+
                                            '<div class="data-info-item date">'+
                                                '<i class="fa-regular fa-calendar-day"></i>'+
                                                '<span>'+ new Date(users[i].created_at*1000).toLocaleDateString() +'</span>'+
                                            '</div>'+
                                            '<div class="data-info-item timeline">'+
                                                '<i class="fa-regular fa-clock-rotate-left"></i>'+   
                                                '<span>'+ new Date(users[i].created_at*1000).toLocaleTimeString() +'</span>'+
                                              
                                            '</div>'+
                                            '<div class="data-info-item user-type">'+
                                                '<i class="fa-regular fa-circle-user"></i>'+
                                                '<span>'+status+
                                                '</span>'+
                                            '</div>'+
                                            '<div class="data-info-item time">'+
                                                '<i class="fa-regular fa-clock"></i>'+
                                                '<span>'+users[i].last_login_timestamp+
                                                '</span>'+
                                            '</div>'+
                                            '<div class="data-info-item">'+
                                                '<i class="fa-regular fa-signs-post"></i>'+
                                                '<span>'+users[i].post_count+ '</p></span>'+
                                            '</div>'+
                                        '</div>'+
                                    '</div>'+
                                    '<a href="'+getbaseurl()+'admin/signups/detail/'+users[i].id+'" class="nav-icon"><i class="fa-regular fa-chevron-right"></i></a>'+
                                '</div>';
                    $('#item').append(item);
                }
            }
             
                          
            
        }
</script>
</body>
</html>
