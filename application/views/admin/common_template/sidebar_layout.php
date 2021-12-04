<div class="page-sidebar-wrapper">
    <div class="page-sidebar" style="padding-left:99px; background-size:cover; background-image: url('<?php echo base_url();?>admin_assets/img/sidebar_background.png')">
        <ul class="page-sidebar-menu page-header-fixed "
            data-keep-expanded="true"
            data-auto-scroll="false"
            data-slide-speed="200"  >
            <!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->

            <li class="sidebar-title">
                <img src="<?php echo base_url();?>admin_assets/img/atb_logo.svg"> </img>
            </li>

            <li class="sidebar-choose-date  ">
                <a href="javascript:;">
                    <span class="title" style="color: #A6BFDE;" > <?php echo date('D jS F, Y');?></span>
                </a>
            </li>

            <li class="nav-item start <?php if($selected_item == MENU_SIGNUPS) echo 'open';?>">
                <a href="<?php echo route('admin.signups.index');?>" class="nav-link ">

                    <span class="title">Signups</span>
                </a>
            </li>

            <li class="nav-item start <?php if($selected_item == MENU_BUSINESS) echo 'open';?>">
                <a href="<?php echo route('admin.business.index');?>" class="nav-link ">
                    <span class="title">Business</span>
                </a>
            </li>

            <li class="nav-item <?php if($selected_item == MENU_NOTIFICATIONS) echo 'open';?>">
                <a href="<?php echo route('admin.notifications.index');?>" class="nav-link ">

                    <span class="title">Notifications</span>
                    <?php if ($notifications_count > 0) {?>
                    	<span style="background-color: #DE8F8F;border-radius: 5px;padding-top: 5px;padding-right: 7px;padding-left: 7px;padding-bottom: 5px;"> <?php echo $notifications_count; ?></span>
                    <?php }?>
                     
                </a>
            </li>

            <li class="nav-item <?php if($selected_item == MENU_REPORT_POST) echo 'open';?>">
                <a href="<?php echo route('admin.reported_post.index');?>" class="nav-link ">

                    <span class="title">Reported Posts</span>
                     <?php if ($reported_count > 0) {?>
                    	<span style="background-color: #DE8F8F;border-radius: 5px;padding-top: 5px;padding-right: 7px;padding-left: 7px;padding-bottom: 5px;"> <?php echo $reported_count; ?></span>
                    <?php }?>
                </a>
            </li>


            <li class="nav-item <?php if($selected_item == MENU_BLOCKED_REMOVED) echo 'open';?>">
                <a href="<?php echo route('admin.users.index');?>" class="nav-link ">
                    <span class="title">Blocked/Removed</span>
                </a>
            </li>
            
            <li class="nav-item <?php if($selected_item == MENU_BLOCKED_REMOVED) echo 'open';?>">
                <a href="<?php echo route('admin.booking.index');?>" class="nav-link ">
                    <span class="title">Bookings</span>
                </a>
            </li>

            <li class="nav-item  <?php if($selected_item == MENU_FEEDS) echo 'open';?>">
                <a href="<?php echo route('admin.feeds.index');?>" class="nav-link ">
                    <span class="title">Feed</span>
                </a>
            </li>
            
            <li class="nav-item  <?php if($selected_item == MENU_TICKETS) echo 'open';?>">
                <a href="<?php echo route('admin.tickets.index');?>" class="nav-link ">
                    <span class="title">Tickets</span>
                </a>
            </li>
			
	    <li class="nav-item  <?php if($selected_item == MENU_ADMIN) echo 'open';?>">
                <a href="<?php echo route('admin.admin.index');?>" class="nav-link ">
                    <span class="title">Admin</span>
                </a>
            </li>
        </ul>

    </div>

    <div style="position: fixed; bottom: 0px; left: 0px; width: 359px; background-color: #00000055">
        <ul class="nav navbar-nav text-center" style="width:100%;">
            <li style="height: 90px;">
                <a class="user_avatar_bottom_fix" href="javascript:;"style="height: 100%; margin-left: 133px;">
                    
                    <div style="display: table-cell; vertical-align: top; padding-top: 8px;">
                        <span style="font-weight:bold;font-size: x-large;padding: 30px;"> <?php echo $this->session->userdata('user_name');?> </span>
                        <p style="margin-top: 10px; margin-bottom: 10px; color: #fff;"> Admin </p>
                    </div>
                </a>
            </li>
            <!-- END USER LOGIN DROPDOWN -->
            <!-- BEGIN QUICK SIDEBAR TOGGLER -->
            <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
            <li class="dropdown dropdown-quick-sidebar-toggler"  style="height: 90px;">
                <a href="<?php echo route('admin.auth.logout');?>" class="user_avatar_bottom_fix" style="margin-left:0px; padding-top: 40px;">
                    <i class="fas fa-sign-out-alt" style="font-size: x-large;"></i>
                </a>
            </li>
            <!-- END QUICK SIDEBAR TOGGLER -->
        </ul>
    </div>
</div>
