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
                        <!-- BEGIN PAGE HEADER-->

                        <!-- BEGIN PAGE BAR -->
                        <div class="page-bar">

                        </div>
                        <!-- END PAGE BAR -->
                        <!-- BEGIN PAGE TITLE-->
                        <h1 class="page-title font-blue-hoki"> <span class="fa fa-info-circle margin-right-10  "></span> Reported Posts
                        </h1>
                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="tabbable-line tabbable-full-width">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab_open" data-toggle="tab"> Open </a>
                                </li>
                                <li>
                                    <a href="#tab_close" data-toggle="tab"> Close </a>
                                </li>
                                <li>
                                    <a href="#tab_ignored" data-toggle="tab"> Ignored </a>
                                </li>

                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab_open">
                                    <div class="row">
                                        <div class="col-md-8">
                                                <div class="panel-group accordion" id="accordion_all">
                                                <?php for($i = 0 ; $i < count($open_reports); $i++):?>
                                                    <!---BEGIN REPORT POST ITEM--->
                                                        <div class="panel item" style="display: block; overflow: hidden; padding-left: 0 !important; padding-right: 0 !important;">
                                                            <div class="panel-heading" style="cursor: hand;">
                                                                <div class="item-head" style="margin-bottom: 0px !important;" >
                                                                    <div class="row">
                                                                        <div class="col-sm-12 col-md-12" class="font-grey-cascade">
                                                                           <div style="margin-left: 20px; padding: 0 24px 0 24px; position: relative; overflow: hidden;" >
                                                                                <ul class="list-inline" style="padding-top: 24px;">
                                                                                    <li>
                                                                                        <img class="img-circle" style="width: 24px; height: 24px;" src="<?php echo base_url().$open_reports[$i]['reported_user']['profile']['pic_url'];?>">
                                                                                    </li>

                                                                                    <?php if ($open_reports[$i]['post_id'] != 0) { ?>
                                                                                    <li class="font-grey-cascade"> <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['reported_user']['profile']['id']);?>" class="btn btn-link"> @<?php echo $open_reports[$i]['reported_user']['profile']['user_name'];?> </a> has reported the  <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['post']['user'][0]['id']);?>" class="btn btn-link"> @<?php echo $open_reports[$i]['post']['user'][0]['user_name'];?> </a> post - <a href="<?php echo route('admin.signups.view_post', $open_reports[$i]['post']['id']);?>" class="btn btn-link"><?php echo $open_reports[$i]['post']["title"];?> </a></li>
                                                                                    <?php } else { ?>
                                                                                        <li class="font-grey-cascade"> <a href="<?php echo route('admin.signups.detail', $open_reports[$i]['reported_user']['profile']['id']);?>" class="btn btn-link"> @<?php echo $open_reports[$i]['reported_user']['profile']['user_name'];?> </a> has reported</li>

                                                                                    <?php } ?>
                                                                                </ul>
                                                                                <b><?php echo $open_reports[$i]['reason'];?></b> - <?php echo $open_reports[$i]['content'];?>
                                                                                <p class="font-grey-silver"> — <?php echo date('m/d/Y - h:m', $open_reports[$i]['created_at']);?> </p>
                                                                         </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="collapse<?php echo $i;?>"  style="background-color: #A6BFDE;">
                                                                <div class="row" style="padding: 12px;">
                                                                    <div class="col-sm-12 col-md-3"><a style="padding: 6px; 12px;" href="<?php echo route('admin.reported_post.ignore', $open_reports[$i]['id']);?>" class="btn btn-link btn-block font-white"> <i class="fa fa-flag"> </i> Ignore this report </a> </div>
                                                                    <div class="col-sm-12 col-md-3"> <a href="#" class="btn btn-circle blue-hoki applozic-launcher" data-mck-id="<?php echo $open_reports[$i]['reported_user']['profile']['id'];?>" data-mck-name="<?php echo $open_reports[$i]['reported_user']['profile']['first_name'].' '.$open_reports[$i]['reported_user']['profile']['last_name'];?>"><i class="fa fa-comments"></i> Message Reporter</a></div>

                                                                    <?php if ($open_reports[$i]['post_id'] != 0) { ?>
                                                                    <div class="col-sm-12 col-md-3"> <a href="#" class="btn btn-circle blue-hoki applozic-launcher" data-mck-id="<?php echo $open_reports[$i]['post']['user'][0]['id'];?>" data-mck-name="<?php echo $open_reports[$i]['post']['user'][0]['first_name'].' '.$open_reports[$i]['post']['user'][0]['last_name'];?>"><i class="fa fa-comments"></i> Message Poster</a></div>
                                                                    <div class="col-sm-12 col-md-3"><a href="<?php echo route('admin.signups.post_block_form', $open_reports[$i]['post']['id']);?>" class="btn btn-circle btn-block font-white" style="background-color: #DE8F8F;"> <i class="fa fa-trash"> </i> Delete This Post </a></div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <!---END  REPORT POST ITEM--->
                                                <?php endfor;?>
                                            </div>
                                        </div>
                                        <div class="col-md-4" style="padding-right: 80px;">
                                            <div class="item padding-tb-20" style="text-align: center;">
                                                <i class="fa fa-info-circle font-blue-hoki" style="font-size: 30px; line-height: 60px;"></i>
                                                <h1 class="font-blue-hoki bold" style="margin-top: 0px;"> <?php echo count($open_reports); ?> </h1>
                                                <p class="font-grey-silver" style="margin-top: 0px; margin-bottom: 8px;"> Post has been reported </p>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab_close">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="panel-group accordion" id="accordion_all">
                                                <?php for($i = 0 ; $i < count($closed_reports); $i++):?>
                                                    <!---BEGIN REPORT POST ITEM--->
                                                    <div class="panel item" style="display: block; overflow: hidden; padding-left: 0 !important; padding-right: 0 !important;">
                                                        <div class="panel-heading" style="cursor: hand;">
                                                            <div class="item-head" style="margin-bottom: 0px !important;" >
                                                                <div class="row">
                                                                    <div class="col-sm-12 col-md-12" class="font-grey-cascade">
                                                                        <div style="margin-left: 20px; padding: 0 24px 0 24px; position: relative; overflow: hidden;" >
                                                                            <ul class="list-inline" style="padding-top: 24px;">
                                                                                <li>
                                                                                    <img class="img-circle" style="width: 24px; height: 24px;" src="<?php echo base_url().$closed_reports[$i]['reported_user']['profile']['pic_url'];?>">
                                                                                </li>
                                                                                <?php if ($closed_reports[$i]['post_id'] != 0) { ?>
                                                                                    <li class="font-grey-cascade"> <a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['reported_user']['profile']['id']);?>" class="btn btn-link"> @<?php echo $closed_reports[$i]['reported_user']['profile']['user_name'];?> </a> has reported the  <a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['post']['user'][0]['id']);?>" class="btn btn-link"> @<?php echo $closed_reports[$i]['post']['user'][0]['user_name'];?> </a> post - <a href="<?php echo route('admin.signups.view_post', $closed_reports[$i]['post']['id']);?>" class="btn btn-link"><?php echo $closed_reports[$i]['post']["title"];?> </a></li>
                                                                                <?php } else { ?>
                                                                                    <li class="font-grey-cascade"> <a href="<?php echo route('admin.signups.detail', $closed_reports[$i]['reported_user']['profile']['id']);?>" class="btn btn-link"> @<?php echo $closed_reports[$i]['reported_user']['profile']['user_name'];?> </a> has reported</li>

                                                                                <?php } ?>
                                                                            </ul>
                                                                            <b><?php echo $closed_reports[$i]['reason'];?></b> - <?php echo $closed_reports[$i]['content'];?>
                                                                            <p class="font-grey-silver"> — <?php echo date('m/d/Y - h:m', $closed_reports[$i]['created_at']);?> </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="collapse<?php echo $i;?>"  style="background-color: #A6BFDE;">
                                                            <div class="row" style="padding: 12px;">
                                                                <div class="col-sm-12 col-md-3"> <a href="#" class="btn btn-circle blue-hoki applozic-launcher" data-mck-id="<?php echo $closed_reports[$i]['reported_user']['profile']['id'];?>" data-mck-name="<?php echo $closed_reports[$i]['reported_user']['profile']['first_name'].' '.$closed_reports[$i]['reported_user']['profile']['last_name'];?>"><i class="fa fa-comments"></i> Message Reporter</a></div>
                                                                <?php if ($closed_reports[$i]['post_id'] != 0) { ?>
                                                                <div class="col-sm-12 col-md-3"> <a href="#" class="btn btn-circle blue-hoki applozic-launcher" data-mck-id="<?php echo $closed_reports[$i]['post']['user'][0]['id'];?>" data-mck-name="<?php echo $closed_reports[$i]['post']['user'][0]['first_name'].' '.$closed_reports[$i]['post']['user'][0]['last_name'];?>"><i class="fa fa-comments"></i> Message Poster</a></div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!---END  REPORT POST ITEM--->
                                                <?php endfor;?>
                                            </div>
                                        </div>
                                        <div class="col-md-4" style="padding-right: 80px;">
                                            <div class="item padding-tb-20" style="text-align: center;">
                                                <i class="fa fa-info-circle font-blue-hoki" style="font-size: 30px; line-height: 60px;"></i>
                                                <h1 class="font-blue-hoki bold" style="margin-top: 0px;"> <?php echo count($closed_reports); ?> </h1>
                                                <p class="font-grey-silver" style="margin-top: 0px; margin-bottom: 8px;"> reports have been closed </p>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!--end tab-pane-->
                                <div class="tab-pane" id="tab_ignored">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="panel-group accordion" id="accordion_all">
                                                <?php for($i = 0 ; $i < count($ignored_reports); $i++):?>
                                                    <!---BEGIN REPORT POST ITEM--->
                                                    <div class="panel item" style="display: block; overflow: hidden; padding-left: 0 !important; padding-right: 0 !important;">
                                                        <div class="panel-heading" style="cursor: hand;">
                                                            <div class="item-head" style="margin-bottom: 0px !important;" >
                                                                <div class="row">
                                                                    <div class="col-sm-12 col-md-12" class="font-grey-cascade">
                                                                        <div style="margin-left: 20px; padding: 0 24px 0 24px; position: relative; overflow: hidden;" >
                                                                            <ul class="list-inline" style="padding-top: 24px;">
                                                                                <li>
                                                                                    <img class="img-circle" style="width: 24px; height: 24px;" src="<?php echo base_url().$ignored_reports[$i]['reported_user']['profile']['pic_url'];?>">
                                                                                </li>
                                                                                <?php if ($ignored_reports[$i]['post_id'] != 0) { ?>
                                                                                    <li class="font-grey-cascade"> <a href="<?php echo route('admin.signups.detail', $ignored_reports[$i]['reported_user']['profile']['id']);?>" class="btn btn-link"> @<?php echo $ignored_reports[$i]['reported_user']['profile']['user_name'];?> </a> has reported the  <a href="<?php echo route('admin.signups.detail', $ignored_reports[$i]['post']['user'][0]['id']);?>" class="btn btn-link"> @<?php echo $ignored_reports[$i]['post']['user'][0]['user_name'];?> </a> post - <a href="<?php echo route('admin.signups.view_post', $ignored_reports[$i]['post']['id']);?>" class="btn btn-link"><?php echo $ignored_reports[$i]['post']["title"];?> </a></li>
                                                                                <?php } else { ?>
                                                                                    <li class="font-grey-cascade"> <a href="<?php echo route('admin.signups.detail', $ignored_reports[$i]['reported_user']['profile']['id']);?>" class="btn btn-link"> @<?php echo $ignored_reports[$i]['reported_user']['profile']['user_name'];?> </a> has reported</li>

                                                                                <?php } ?>
                                                                            </ul>
                                                                            <b><?php echo $ignored_reports[$i]['reason'];?></b> - <?php echo $ignored_reports[$i]['content'];?>
                                                                            <p class="font-grey-silver"> — <?php echo date('m/d/Y - h:m', $ignored_reports[$i]['created_at']);?> </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="collapse<?php echo $i;?>"  style="background-color: #A6BFDE;">
                                                            <div class="row" style="padding: 12px;">
                                                                <div class="col-sm-12 col-md-3"> <a href="#" class="btn btn-circle blue-hoki applozic-launcher" data-mck-id="<?php echo $ignored_reports[$i]['reported_user']['profile']['id'];?>" data-mck-name="<?php echo $ignored_reports[$i]['reported_user']['profile']['first_name'].' '.$ignored_reports[$i]['reported_user']['profile']['last_name'];?>"><i class="fa fa-comments"></i> Message Reporter</a></div>
                                                                <?php if ($ignored_reports[$i]['post_id'] != 0) { ?>
                                                                <div class="col-sm-12 col-md-3"> <a href="#" class="btn btn-circle blue-hoki applozic-launcher" data-mck-id="<?php echo $ignored_reports[$i]['post']['user'][0]['id'];?>" data-mck-name="<?php echo $ignored_reports[$i]['post']['user'][0]['first_name'].' '.$ignored_reports[$i]['post']['user'][0]['last_name'];?>"><i class="fa fa-comments"></i> Message Poster</a></div>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!---END  REPORT POST ITEM--->
                                                <?php endfor;?>
                                            </div>
                                        </div>
                                        <div class="col-md-4" style="padding-right: 80px;">
                                            <div class="item padding-tb-20" style="text-align: center;">
                                                <i class="fa fa-info-circle font-blue-hoki" style="font-size: 30px; line-height: 60px;"></i>
                                                <h1 class="font-blue-hoki bold" style="margin-top: 0px;"> <?php echo count($ignored_reports); ?> </h1>
                                                <p class="font-grey-silver" style="margin-top: 0px; margin-bottom: 8px;"> reports have been ignored </p>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!--end tab-pane-->
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
//                $('#clickmewow').click(function()
//                {
//                    $('#radio1003').attr('checked', 'checked');
//                });
            })
        </script>
    </body>

</html>