<?php
$user_id= $this->session->userdata('user_id');

if(!$user_id){
    redirect(route('admin.auth.login'));
}
?>

<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<?php echo $header_layout;?>
<!-- END HEAD -->

<body class="page-header-fixed page-sidebar-closed-hide-logo page-content-white page-sidebar-fixed">
<div class="page-wrapper">

    <!-- BEGIN CONTAINER -->
    <div class="page-container" style="margin-top: 0px;">
        <!-- BEGIN SIDEBAR -->
        <?php echo $sidebar_layout;?>
        <!-- END SIDEBAR -->

        <!-- BEGIN CONTENT -->
        <div class="page-content-wrapper">
            <!-- BEGIN CONTENT BODY -->
            <div class="page-content">
                <div class="full-height-content full-height-content-scrollable">
                    <div class="full-height-content-body">

                        <div class="portlet light">
                            <div class="portlet-title">
                                <div class="caption">
                                    <span class="caption-subject bold"><i class="far fa-file-alt"></i> Post Detail</span>
                                </div>

                                <div class="actions">
                                    <?php if ($post['is_active'] != 2) { ?>
                                        <a  href="<?php echo route('admin.signups.post_block_form', $post['id']);?>"
                                            class="btn red-flamingo"
                                            role="button"
                                            data-confirm="Are you sure you want to delete?">
                                            <i class="fas fa-ban"></i> Block this post
                                        </a>
                                    <?php } ?>

                                    <?php if ($post['is_active'] == 2) { ?>
                                        <a  href="<?php echo route('admin.signups.post_unblock_form', $post['id']);?>"
                                            class="btn"
                                            role="button"
                                            data-confirm="Are you sure you want to delete?">
                                            <i class="far fa-check-circle"></i> Unblock this post
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="row">
                                        <div class="col-sm-6">
                                            <ul class="list-inline">
                                                <li>
                                                    <img class="img-circle" style="width: 24px; height: 24px;" src="<?php echo $post['user'][0]['pic_url'];?>">
                                                </li>
                                                <li class="font-grey-cascade"> <?php echo $post['user'][0]['first_name'];?> <?php echo $post['user'][0]['last_name'];?>
                                                </li>
                                                <li class="font-grey-cascade"> <a href="<?php echo route('admin.signups.detail', $post['user'][0]['id']);?>" class="btn btn-link"> @<?php echo $post['user'][0]['user_name'];?> </a>
                                                </li>

                                            </ul>
                                            <p><b>Post Status</b>
                                            <?php
                                                            switch ($post['is_active']) {
                                                                case 0:
                                                                    echo '<span class="text-muted">Reported</span>';
                                                                    break;
                                                                case 1:
                                                                    echo '<span class="text-success">Active</span>';
                                                                    break;
                                                                case 2:
                                                                    echo '<span class="text-danger" data-toggle="tooltip" data-placement="top" title="'.$post['status_reason'].'">Blocked</span>';
                                                                    break;
                                                                case 3:
                                                                    echo '<span class="text-success">Pending Approval</span>';
                                                                    break;
                                                                case 4:
                                                                    echo '<span class="text-danger">Rejected</span>';
                                                                    break;
                                                            }
                                                            ?>
                                            </p>
                                            <p class="blog-post-desc"> <?php echo $post['description'];?> </p>
                                            <p class="blog-post-desc atb-points"><b class="atb-font-blue">Posted</b> - <?php echo
                                                human_readable_date($post['created_at']);?> </p>

                                            <p class="blog-post-desc atb-points">
                                                <b class="atb-font-blue">Post type</b> -
                                                <?php
                                                switch ($post['post_type']) {
                                                    case 1:
                                                        echo 'Advice';
                                                        break;
                                                    case 2:
                                                        echo 'Sales';
                                                        break;
                                                    case 3:
                                                        echo 'Service';
                                                        break;
                                                    case 4:
                                                        echo 'Poll';
                                                        break;


                                                }
                                                ?>
                                            </p>
                                            <?php if (!empty($post["category_title"])) { ?>
                                            <p class="blog-post-desc atb-points"><b class="atb-font-blue">Category</b> - <?php echo $post["category_title"];?></p>
                                            <?php } ?>

                                            
                                        </div>
                                       
                                        <div class="col-sm-6">
                                        <?php if(($post['media_type']) == 0) { //text only?>

                                        <?php }
                                        else if(($post['media_type']) == 1) { //image ?>
										
										<style>
										.mySlides {display:none;}
										</style>
										<?php  for ($i = 0; $i < count($post['post_imgs']); $i++) { ?>
												<img class="mySlides" src="<?php echo $post['post_imgs'][$i]['path'];?>" style="max-height: 400px;">
                                        <?php } ?>

										<button class="w3-button w3-black w3-display-left" onclick="plusDivs(-1)">&#10094;</button>
										<button class="w3-button w3-black w3-display-right" onclick="plusDivs(1)">&#10095;</button>
										
										<script>
var slideIndex = 1;
showDivs(slideIndex);

function plusDivs(n) {
  showDivs(slideIndex += n);
}

function showDivs(n) {
  var i;
  var x = document.getElementsByClassName("mySlides");
  if (n > x.length) {slideIndex = 1}
  if (n < 1) {slideIndex = x.length}
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";  
  }
  x[slideIndex-1].style.display = "block";  
}
</script>
										
										<?php } else { //video?>
                                            <video controls style="max-height: 400px;">
                                                <source src="<?php echo $post['post_imgs'][0]['path'];?>" type="video/mp4">
                                            </video>
                                        <?php }?>
                                        </div>
                                        <div class="col-sm-6">

                                            <p><b><?php echo $post['title'];?></b></p>
                                            <?php for($i = 0 ; $i < count($post['poll_options']); $i++){?>
                                                <div class="poll-option">
                                                    <?php echo $post['poll_options'][$i]["poll_value"]; ?> - <?php echo count($post['poll_options'][$i]["votes"]) ?> votes
                                                </div>
                                            <?php } ?>
                                        </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="caption-subject bold">Comments</div>
                                        <table class="table table-hover" width="100%" id="tb_comments">
                                            <thead>
                                            <tr>
                                                <th >Username</th>
                                                <th >Date</th>
                                                <th >Comment</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php for($i = 0 ; $i < count($post['comments']); $i++):?>
                                                <tr>
                                                    <td> <a href="<?php echo route('admin.signups.detail', $post['comments'][$i]['commenter_user_id']);?>" class="btn btn-link underlined margin-top-15"> <?php echo $post['comments'][$i]['user_name'];?></a> </td>
                                                    <td> <p class="padding-tb-10 font-grey-gallery">
                                                            <?php
                                                            echo $post['comments'][$i]['read_created'];?>
                                                        </p>
                                                    </td>
                                                    <td> <p class="padding-tb-10 font-grey-salt"> <?php echo $post['comments'][$i]['comment'];?> </p></td>
                                                </tr>
                                            <?php endfor;?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END CONTENT BODY -->
        </div>
        <!-- END CONTENT -->

    </div>
    <!-- END CONTAINER -->

</div>

<?php echo $footer_layout;?>
<script>
    $(document).ready(function()
    {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDTgrxK1EHyOlK8WJJjOO8KRJ0xYpy8zg0&callback=initMap">
</script>

</body>

</html>