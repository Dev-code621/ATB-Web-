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

                        <!-- END PAGE BAR -->
                        <!-- BEGIN PAGE TITLE-->

                        <!-- END PAGE TITLE-->
                        <!-- END PAGE HEADER-->
                        <div class="full-height-content full-height-content-scrollable">
                            <div class="full-height-content-body">
                                <div class="portlet light">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <div class="col-md-4">
                                                <img src="<?php echo $profile['pic_url'];?>"
                                                     class="img-circle" alt=""
                                                     style="width: 68px; height: 68px;"/>
                                            </div>
                                            <div class="col-md-8">   
                                                <span class="caption-subject bold">   <?php echo $profile['first_name'].' '.$profile['last_name'];?> </span>
                                                <p class="caption-helper">   Login History </p>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <a href="<?php echo route('admin.signups.detail', $profile['id']);?>" class="btn gradient-btn"> &lt Back </a>
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <table class="table table-hover" width="100%" id="tb_last_logins">
                                            <thead>
                                            <tr>
                                                <th >Date</th>
                                                <th >Time</th>
                                                <th >Location IP</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php for($i = 0 ; $i < count($login_history); $i++):?>
                                                <tr class="font-grey-gallery">
                                                    <td> <?php echo date('d-m-Y', $login_history[$i]['login_timestamp']);?> </td>
                                                    <td> <?php echo date('h:i:s a', $login_history[$i]['login_timestamp']);?> </td>
                                                    <td> <?php echo $login_history[$i]['login_ip'];?> </td>
                                                </tr>
                                            <?php endfor;?>
                                            </tbody>
                                        </table>
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
//                $('#clickmewow').click(function()
//                {
//                    $('#radio1003').attr('checked', 'checked');
//                });
            })
        </script>
    </body>

</html>
