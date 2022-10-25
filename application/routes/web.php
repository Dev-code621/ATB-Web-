<?php

    Route::group('/invite', ['namespace' => 'invite'], function() {
		Route::get('/', 'InviteController@index')->name('invite.invite.index');
		Route::get('/new', 'InviteController@new')->name('invite.invite.new');
	});

/**
 * Mobile API
 */
    Route::group('/api/post', ['namespace' => 'api'], function() {
        Route::post('/publish', 'PostController@publish')->name('api.post.publish');
        Route::post('/update_content', 'PostController@update_content')->name('api.post.update_content');
        Route::post('/add_image', 'PostController@add_image')->name('api.post.add_image');
        Route::post('/remove_image', 'PostController@remove_image')->name('api.post.remove_image');
        Route::post('/remove_post', 'PostController@remove_post')->name('api.post.remove_post');
        Route::post('/search', 'PostController@search')->name('api.post.search');
        Route::post('/get_feed', 'PostController@get_feed')->name('api.post.get_feed');
        Route::post('/get_home_feed', 'PostController@get_home_feed')->name('api.post.get_home_feed');
        Route::post('/get_post_detail', 'PostController@get_post_detail')->name('api.post.get_post_detail');
        Route::post('/add_like_post', 'PostController@add_like_post')->name('api.post.add_like_post');
        Route::post('/add_comment_post', 'PostController@add_comment_post')->name('api.post.add_comment_post');
    	Route::post('/reply_comment_post', 'PostController@reply_comment_post')->name('api.post.reply_comment_post');
        Route::post('/add_report_post', 'PostController@add_report_post')->name('api.post.add_report_post');
    	Route::post('/add_notification_message', 'PostController@add_notification_message')->name('api.post.add_notification_message');
        Route::post('/is_sold', 'PostController@is_sold')->name('api.post.is_sold');
        Route::post('/set_sold', 'PostController@set_sold')->name('api.post.set_sold');
        Route::post('/relist', 'PostController@relist')->name('api.post.relist');
        Route::post('/count_service_posts', 'PostController@countServicePosts')->name('api.post.countServicePosts');
        Route::post('/count_sales_posts', 'PostController@countSalesPosts')->name('api.post.countSalesPosts');
        Route::post('/delete_post', 'PostController@delete_post')->name('api.post.delete_post');
		
        Route::post('/get_comments', 'PostController@get_post_comments')->name('api.post.get_post_comments');
        Route::post('/add_comment_image_post', 'PostController@add_comment_image_post')->name('api.post.add_comment_image_post');
        Route::post('/add_comment_reply', 'PostController@add_comment_reply')->name('api.post.add_comment_reply');
        Route::post('/add_report_comment', 'PostController@add_report_comment')->name('api.post.add_report_comment');
        Route::post('/add_hide_comment', 'PostController@add_hide_comment')->name('api.post.add_hide_comment');
        Route::post('/add_hide_reply', 'PostController@add_hide_reply')->name('api.post.add_hide_reply');
        Route::post('/add_like_comment', 'PostController@add_like_comment')->name('api.post.add_like_comment');
        Route::post('/add_like_reply', 'PostController@add_like_reply')->name('api.post.add_like_reply');
        Route::post('/add_vote', 'PostController@add_vote')->name('api.post.add_vote');
        Route::post('/get_user_vote', 'PostController@get_user_vote')->name('api.post.get_user_vote');
        Route::post('/get_multi_group_id', 'PostController@get_multi_group_id')->name('api.post.get_multi_group_id');

        Route::post('/add_report', 'PostController@add_report')->name('api.post.add_report');

        Route::post('/add_cart', 'PostController@add_cart')->name('api.post.add_cart');
        Route::post('/cart_add_item', 'PostController@cart_add_item')->name('api.post.cart_add_item');
        Route::post('/delete_cart', 'PostController@delete_cart')->name('api.post.delete_cart');
        Route::post('/cart_delete_item', 'PostController@cart_delete_item')->name('api.post.cart_delete_item');
        Route::post('/cart_delete_items', 'PostController@cart_delete_items')->name('api.post.cart_delete_items');
        Route::post('/get_cart_products', 'PostController@get_cart_products')->name('api.post.get_cart_products');
        Route::post('/send_files', 'PostController@sendFiles')->name('api.post.sendFiles');

        // new added on 19th March, 2022
        Route::post('/delete_comment', 'PostController@delete_post_comment')->name('api.post.delete_comment');
        Route::post('/delete_reply', 'PostController@delete_post_reply')->name('api.post.delete_reply');
    });

    Route::group('/api/auth', ['namespace' => 'api'], function() {
	    Route::post('/register_stage_one', 'AuthController@register_stage_one')->name('api.auth.register_stage_one');
        Route::post('/register', 'AuthController@register')->name('api.auth.register');
        Route::post('/update_feed', 'AuthController@update_feed')->name('api.auth.update_feed');
        Route::post('/login', 'AuthController@login')->name('api.auth.login');
        Route::post('/forgot_pass_email_verification', 'AuthController@forgot_pass_email_verification')->name('api.auth.forgot_pass_email_verification');
        Route::post('/check_verification_code', 'AuthController@check_verification_code')->name('api.auth.check_verification_code');
        Route::post('/update_pass', 'AuthController@update_pass')->name('api.auth.update_pass');
        Route::post('/change_pass', 'AuthController@change_pass')->name('api.auth.change_pass');
        Route::post('/is_username_used', 'AuthController@isUserNameUsed')->name('api.auth.is_username_used');
        Route::post('/is_email_used', 'AuthController@isEmailUsed')->name('api.auth.is_email_used');
    });
	
    Route::group('/api/push', ['namespace' => 'api'], function() {
        Route::post('/cron', 'CronController@run_notification')->name('api.auth.run_notification');
        Route::post('/schedule', 'CronController@post_scheduled_posts')->name('api.auth.post_scheduled_posts');
        Route::post('/email', 'CronController@email_test')->name('api.auth.email_test');
        Route::get('/push_upgrade_business', 'CronController@push_upgrade_business')->name('api.push.push_upgrade_business');
    });
    
    Route::group('/api/business', ['namespace' => 'api'], function() {
        Route::post('/add_holiday', 'BusinessController@add_holiday')->name('api.business.add_holiday');
        Route::post('/update_holiday', 'BusinessController@update_holiday')->name('api.business.update_holiday');
        Route::post('/delete_holiday', 'BusinessController@delete_holiday')->name('api.business.delete_holiday');
        Route::post('/update_week', 'BusinessController@update_week')->name('api.business.update_week');
        Route::post('/request_rating', 'BusinessController@request_rating')->name('api.business.request_rating');
        Route::post('/request_payment', 'BusinessController@request_payment')->name('api.business.request_payment');
        Route::post('/add_disabled_slot', 'BusinessController@add_disabled_slot')->name('api.business.add_disabled_slot');
        Route::post('/update_disabled_slot', 'BusinessController@update_disabled_slot')->name('api.business.update_disabled_slot');
        Route::post('/delete_disabled_slot', 'BusinessController@delete_disabled_slot')->name('api.business.delete_disabled_slot');
        Route::post('/get_disabled_slots', 'BusinessController@get_disabled_slots')->name('api.business.get_disabled_slots');
    });
    
    Route::group('/api/booking', ['namespace' => 'api'], function() {
        Route::post('/get_bookings', 'BookingController@get_bookings')->name('api.booking.get_bookings');
		Route::post('/get_booking', 'BookingController@get_booking')->name('api.booking.get_booking');
        Route::post('/search_user', 'BookingController@search_user')->name('api.booking.search_user');
        Route::post('/create_booking', 'BookingController@create_booking')->name('api.booking.create_booking');
        Route::post('/cancel_booking', 'BookingController@cancel_booking')->name('api.booking.cancel_booking');
        Route::post('/update_booking', 'BookingController@update_booking')->name('api.booking.update_booking');
        Route::post('/create_booking_report', 'BookingController@create_booking_report')->name('api.booking.create_booking_report');
        Route::post('/set_reminder', 'BookingController@set_reminder')->name('api.booking.set_reminder');
        Route::post('/complete_booking', 'BookingController@complete_booking')->name('api.booking.complete_booking');
    });
    
    Route::group('/api/transaction', ['namespace' => 'api'], function() {
        Route::post('/get_purchases', 'TransactionController@get_purchases')->name('api.transaction.get_purchases');
        Route::post('/get_items_sold', 'TransactionController@get_items_sold')->name('api.transaction.get_items_sold');
    });

    Route::group('/api/profile', ['namespace' => 'api'], function() {
        Route::post('/update_search_region', 'ProfileController@update_search_region')->name('api.profile.update_search_region');
        Route::post('/getprofile', 'ProfileController@getprofile')->name('api.profile.getprofile');
        Route::post('/getfollower', 'ProfileController@getfollower')->name('api.profile.getfollower');
	    Route::post('/getfollow', 'ProfileController@getfollow')->name('api.profile.getfollow');
	    Route::post('/addfollow', 'ProfileController@addfollow')->name('api.profile.addfollow');
	    Route::post('/like_notifications', 'ProfileController@like_notifications')->name('api.profile.like_notifications');
	    Route::post('/has_like_notifications', 'ProfileController@has_like_notifications')->name('api.profile.has_like_notifications');
	    Route::post('/getfollowercount', 'ProfileController@getfollowercount')->name('api.profile.getfollowercount');
	    Route::post('/getfollowcount', 'ProfileController@getfollowcount')->name('api.profile.getfollowcount');
	    Route::post('/getpostcount', 'ProfileController@getpostcount')->name('api.profile.getpostcount');
	    Route::post('/deletefollower', 'ProfileController@deletefollower')->name('api.profile.deletefollower');
        Route::post('/updateprofile', 'ProfileController@updateprofile')->name('api.profile.updateprofile');
        Route::post('/updatebio', 'ProfileController@updatebio')->name('api.profile.updatebio');
        Route::post('/add_payment', 'ProfileController@add_payment')->name('api.profile.add_payment');
	    Route::post('/add_sub', 'ProfileController@add_subscription')->name('api.profile.add_subscription');
        Route::post('/get_cards', 'ProfileController@get_cards')->name('api.profile.get_cards');
        Route::post('/set_primary_card', 'ProfileController@set_primary_card')->name('api.profile.set_primary_card');
	    Route::post('/generate_ephemeral_key', 'ProfileController@generate_ephemeral_key')->name('api.profile.generate_ephemeral_key');
        
        Route::post('/create_business_account', 'ProfileController@create_business_account')->name('api.profile.create_business_account');
        
        Route::post('/add_service', 'ProfileController@add_service')->name('api.profile.add_service');
        Route::post('/update_service', 'ProfileController@update_service')->name('api.profile.update_service');
        Route::post('/delete_service', 'ProfileController@delete_service')->name('api.profile.delete_service');
        Route::post('/remove_service', 'ProfileController@remove_service')->name('api.profile.remove_service');

        Route::post('/update_business_account', 'ProfileController@update_business_account')->name('api.profile.update_business_account');
        Route::post('/read_business_account', 'ProfileController@read_business_account')->name('api.profile.read_business_account');
	    Route::post('/read_business_account_from_id', 'ProfileController@read_business_account_from_id')->name('api.profile.read_business_account_from_id');

        Route::post('/update_business_bio', 'ProfileController@update_business_bio')->name('api.profile.update_business_bio');
        Route::post('/get_user_bookmarks', 'ProfileController@getuserbookmarks')->name('api.profile.getuserbookmarks');
        Route::post('/add_user_bookmark', 'ProfileController@adduserbookmark')->name('api.profile.adduserbookmark');
        
        Route::post('/get_notifications', 'ProfileController@get_notifications')->name('api.profile.get_notifications');
        Route::post('/read_notification', 'ProfileController@read_notification')->name('api.profile.read_notification');
        
        Route::post('/addbusinessreviews', 'ProfileController@addbusinessreviews')->name('api.profile.addbusinessreviews');
        Route::post('/getbusinessreview', 'ProfileController@getbusinessreview')->name('api.profile.getbusinessreview');
 	    Route::post('/add_connect_account', 'ProfileController@add_connect_account')->name('api.profile.add_connect_account');
	    
        Route::post('/checkout', 'ProfileController@checkout')->name('api.profile.checkout');
        Route::post('/make_cash_payment', 'ProfileController@make_cash_payment')->name('api.profile.make_cash_payment');

	    Route::post('/get_transactions', 'ProfileController@getTransactions')->name('api.profile.getTransactions');
	    Route::post('/get_users_posts', 'ProfileController@get_users_posts')->name('api.profile.get_users_posts');
	    Route::post('/update_notification_token', 'ProfileController@update_notification_token')->name('api.profile.update_notification_token');

        Route::post('/add_pp_sub', 'ProfileController@add_pp_subscription')->name('api.profile.add_pp_subscription');
        Route::post('/add_apple_sub', 'ProfileController@add_apple_subscription')->name('api.profile.add_apple_subscription');

        Route::post('/get_pp_add', 'ProfileController@get_pp_address')->name('api.profile.get_pp_address');
        Route::post('/get_braintree_client_token', 'ProfileController@get_braintree_client_token')->name('api.profile.get_braintree_client_token');
        Route::post('/make_pp_pay', 'ProfileController@make_pp_payment')->name('api.profile.make_pp_payment');
        Route::post('/get_pp_transactions', 'ProfileController@get_pp_Transactions')->name('api.profile.get_pp_Transactions');

	    Route::post('/add_product', 'ProfileController@add_product')->name('api.profile.add_product');
	    Route::post('/update_product', 'ProfileController@update_product')->name('api.profile.update_product');
        Route::post('/delete_product', 'ProfileController@delete_product')->name('api.profile.delete_product');
        Route::post('/remove_product', 'ProfileController@remove_product')->name('api.profile.remove_product');

	    Route::post('/get_product', 'ProfileController@get_product')->name('api.profile.get_product');
        Route::post('/get_user_products', 'ProfileController@get_user_products')->name('api.profile.get_user_products');
        
        
	    Route::post('/get_services', 'ProfileController@get_services')->name('api.profile.get_services');
        Route::post('/get_service', 'ProfileController@get_service')->name('api.profile.get_service');
        Route::post('/get_business_items', 'ProfileController@get_business_items')->name('api.profile.get_business_items');
	
	    Route::post('/get_tags', 'ProfileController@get_tags')->name('api.profile.get_tags');
	    Route::post('/get_tag', 'ProfileController@get_tag')->name('api.profile.get_tag');
	    Route::post('/add_tag', 'ProfileController@add_tag')->name('api.profile.add_tag');
        Route::post('/delete_tag', 'ProfileController@delete_tag')->name('api.profile.delete_tag');
	    Route::post('/update_tag', 'ProfileController@update_tag')->name('api.profile.update_tag');
    
        Route::post('/add_service_file', 'ProfileController@add_service_file')->name('api.profile.add_service_file');
        Route::post('/get_service_files', 'ProfileController@get_service_files')->name('api.profile.get_service_files');
        Route::post('/update_service_file', 'ProfileController@update_service_file')->name('api.profile.update_service_file');
        Route::post('/delete_service_file', 'ProfileController@delete_service_file')->name('api.profile.delete_service_file');
	

	    Route::post('/add_social', 'ProfileController@add_social')->name('api.profile.add_social');
	    Route::post('/update_social', 'ProfileController@update_social')->name('api.profile.update_social');
	    Route::post('/get_socials', 'ProfileController@get_socials')->name('api.profile.get_socials');
        Route::post('/remove_social', 'ProfileController@remove_social')->name('api.profile.remove_social');
	    Route::post('/truncateUserSocials', 'ProfileController@truncateUserSocials')->name('api.profile.truncateUserSocials');
	    
	    Route::post('/get_multi_group_id', 'ProfileController@get_multi_group_id')->name('api.profile.get_multi_group_id');
	    
	    Route::post('/delete_variant_product', 'ProfileController@delete_variant_product')->name('api.profile.delete_variant_product');
	    Route::post('/update_variant_product', 'ProfileController@update_variant_product')->name('api.profile.update_variant_product');
	    
	    Route::post('/set_transaction_booking_id', 'ProfileController@set_transaction_booking_id')->name('api.profile.set_transaction_booking_id');	

        Route::post('/can_rate_business', 'ProfileController@canRateBusiness')->name('api.profile.can_rate_business');
        Route::post('/can_message_seller', 'ProfileController@canMessageSeller')->name('api.profile.can_message_seller');  

        // 4th May, 2022
        Route::post('/get_drafts', 'ProfileController@get_drafts')->name('api.profile.get_drafts');

        Route::post('/onboard_user', 'ProfileController@onboard_user')->name('api.profile.onboard_user');
        Route::post('/retrieve_connect_user', 'ProfileController@retrieve_connect_user')->name('api.profile.retrieve_connect_user');
        Route::post('/subscribe', 'ProfileController@subscribe')->name('api.profile.subscribe');
    });
    
    Route::group('/api/auction', ['namespace' =>'api'], function() {
        Route::post('/auctions', 'AuctionController@getAuctions')->name('api.auctions.get_auctions');
        Route::post('/placebid', 'AuctionController@placeBid')->name('api.auctions.place_bid');
        Route::post('/profilepins', 'AuctionController@getProfilePins')->name('api.auctions.get_profilepins');
        Route::get('/payment', 'AuctionController@executePayment')->name('api.auctions.payment');
        Route::post('/capture', 'AuctionController@capturePayment')->name('api.auctions.capture');
    });
    
    Route::group('/payment', ['namespace' => 'payment'], function() {
        Route::get('/success', 'PaymentController@success')->name('payment.payment.success');
        Route::get('/cancel', 'PaymentController@cancel')->name('payment.payment.cancel');

        Route::get('/onboard', 'PaymentController@onboard')->name('payment.payment.onboard');

        Route::post('/stripe_hook', 'PaymentController@stripe_hook')->name('payment.payment.stripe_hook');
    });
    
    Route::group('api/search', ['namespace' => 'api'], function() {
       Route::post('/business', 'SearchController@searchBusiness')->name('api.search.search_business'); 
       Route::post('/spotlight', 'SearchController@getSpotLight')->name('api.search.spotlight');
       Route::post('/users', 'SearchController@getAllUsers')->name('api.search.users');
    });

/****
 * Admin panel
 */
    Route::group('/', ['namespace' => 'admin'], function() {
		Route::get('/', 'Welcome@index')->name('admin.welcome.index1');
	});

    Route::group('admin/welcome', ['namespace' => 'admin'], function() {
        Route::get('/', 'Welcome@index')->name('admin.welcome.index');
    });

    Route::group('admin/auth', ['namespace' => 'admin'], function() {
        Route::get('/', 'AuthController@index')->name('admin.auth.index');
        Route::get('/login', 'AuthController@login')->name('admin.auth.login');
        Route::get('/forgot_pass', 'AuthController@forgot_pass')->name('admin.auth.forgot_pass');
        Route::post('/sendCode', 'AuthController@sendPassword')->name('admin.auth.sendPassword');

		 Route::get('/logout', 'AuthController@logout')->name('admin.auth.logout');
        Route::post('/do_login', 'AuthController@do_login') -> name('admin.auth.do_login');
    });
    Route::group('admin/dashboard', ['namespace' => 'admin'], function() {
        Route::get('/', 'DashboardController@index')->name('admin.dashboards.index');
    });
    Route::group('admin/mainpage', ['namespace' => 'admin'], function() {
        Route::get('/', 'MainPageController@index')->name('admin.mainpages.index');
    });
    Route::group('admin/signups', ['namespace' => 'admin'], function() {
        Route::get('/', 'SignUpController@index')->name('admin.signups.index');
        Route::get('/detail/{userid}', 'SignUpController@detail') -> name('admin.signups.detail');
        Route::post('/ajax_get_login_chart', 'SignUpController@ajax_get_login_chart') -> name('admin.signups.ajax_get_login_chart');
        Route::get('/login_history/{userid}', 'SignUpController@login_history') -> name('admin.signups.login_history');
        Route::get('/post_history/{userid}', 'SignUpController@post_history') -> name('admin.signups.post_history');

        Route::get('/post/{postid}', 'SignUpController@view_post') -> name('admin.signups.view_post');
        Route::get('/post_block/{postid}', 'SignUpController@post_block_form') -> name('admin.signups.post_block_form');
        Route::get('/block_post', 'SignUpController@block_post') -> name('admin.signups.block_post');

        Route::get('/post_unblock/{postid}', 'SignUpController@post_unblock_form') -> name('admin.signups.post_unblock_form');
        Route::get('/unblock_post', 'SignUpController@unblock_post') -> name('admin.signups.unblock_post');

        Route::get('/book_history/{userid}', 'SignUpController@book_history') -> name('admin.signups.book_history');
        Route::get('/report_history/{userid}', 'SignUpController@report_history') -> name('admin.signups.report_history');
        Route::get('/block/{userid}', 'SignUpController@block_form') -> name('admin.signups.block');
        Route::get('/submit_block', 'SignUpController@submit_block') -> name('admin.signups.submit_block');
        Route::get('/unblock/{userid}', 'SignUpController@unblock_form') -> name('admin.signups.unblock');
        Route::get('/submit_unblock', 'SignUpController@submit_unblock') -> name('admin.signups.submit_unblock');
    });
	
	Route::group('admin/admin', ['namespace' => 'admin'], function() {
        Route::get('/', 'AdminController@index')->name('admin.admin.index');
        Route::get('/detail/{userid}', 'AdminController@detail') -> name('admin.admin.detail');
        Route::get('/new', 'AdminController@newAdmin') -> name('admin.admin.new');
        Route::get('/create', 'AdminController@createAdmin') -> name('admin.admin.create');
        Route::get('/delete/{userid}', 'AdminController@deleteAdmin') -> name('admin.admin.delete');
        Route::post('/doUpload', 'AdminController@doUpload') -> name('admin.admin.doUpload');
        Route::post('/editAdmin', 'AdminController@editAdmin') -> name('admin.admin.editAdmin');

    });

    Route::group('admin/notifications', ['namespace' => 'admin'], function() {
        Route::get('/', 'NotificationsController@index')->name('admin.notifications.index');
        Route::get('/detail/{userid}', 'NotificationsController@detail') -> name('admin.notifications.detail');
        Route::get('/newkeyword', 'NotificationsController@newKeyword')->name('admin.notifications.newkeyword');
        Route::get('/createkeyword', 'NotificationsController@createKeyword')->name('admin.notifications.keywordcreate');
        Route::get('/deletekeyword/{keywordid}', 'NotificationsController@deleteKeyword')->name('admin.notifications.deletekeyword');

        Route::get('/action/{id}', 'NotificationsController@actionNotification')->name('admin.notifications.action');
        Route::get('/saveaction', 'NotificationsController@saveAction')->name('admin.notifications.saveaction');
        Route::post('/readnotification', 'NotificationsController@readnotification')->name('admin.notifications.readnotification');
        Route::post('/ignoreReport', 'NotificationsController@ignoreReport')->name('admin.notifications.ignoreReport');
        Route::get('/view_service/{id}', 'NotificationsController@view_service')->name('admin.notifications.view_service');
        Route::get('/unblock_service', 'NotificationsController@unblock_service') -> name('admin.notifications.unblock_service');
        Route::get('/block_service', 'NotificationsController@block_service') -> name('admin.notifications.block_service');


    });
	
	Route::group('admin/chat', ['namespace' => 'admin'], function() {
        Route::get('/', 'ChatController@index')->name('admin.chat.index');
        Route::get('/detail/{channelID}', 'ChatController@detail')->name('admin.chat.detail');
        Route::get('/newchat', 'ChatController@newchat')->name('admin.chat.newchat');
        Route::post('/sendmessage', 'ChatController@sendmessage')->name('admin.chat.sendmessage');
        Route::post('/makegroup', 'ChatController@makeGroup')->name('admin.chat.makeGroup');

    });

    Route::group('admin/reported_post', ['namespace' => 'admin'], function() {
    Route::get('/', 'ReportedPostsController@index')->name('admin.reported_post.index');
    Route::get('/ignore/{reportid}', 'ReportedPostsController@ignoreReport') -> name('admin.reported_post.ignore');
    Route::get('/commentreport/{commentid}', 'ReportedPostsController@commentReport') -> name('admin.reported_post.commentreport');


});

    Route::group('admin/business', ['namespace' => 'admin'], function() {
    Route::get('/', 'BusinessController@index')->name('admin.business.index');
    Route::get('/detail/{businessid}', 'BusinessController@businessDetail')->name('admin.business.detail');
    Route::get('/block/{businessid}', 'BusinessController@business_block_form') -> name('admin.business.block');
    Route::get('/submit_block', 'BusinessController@block_business') -> name('admin.business.submit_block');
    Route::get('/approve/{businessid}', 'BusinessController@business_approve_form') -> name('admin.business.approve');
    Route::get('/submit_approve', 'BusinessController@approve_business') -> name('admin.business.submit_approve');
	Route::get('/approve_service/{serviceid}', 'BusinessController@service_approve_form') -> name('admin.business.approve_service');
    Route::get('/submit_approve_service', 'BusinessController@approve_service') -> name('admin.business.submit_approve_service');
    Route::get('/threedot', 'BusinessController@threedot') -> name('admin.business.threedot');

	
});

    Route::group('admin/approve_post', ['namespace' => 'admin'], function() {
        Route::get('/', 'ApprovePostsController@index')->name('admin.approve_post.index');
        Route::get('/detail/{userid}', 'ApprovePostsController@detail') -> name('admin.approve_post.detail');
    });

    Route::group('admin/feeds', ['namespace' => 'admin'], function() {
        Route::get('/', 'FeedsController@index')->name('admin.feeds.index');
        Route::get('/detail/{userid}', 'FeedsController@detail') -> name('admin.feeds.detail');
        Route::get('/{search}', 'FeedsController@search')->name('admin.feeds.search');
        Route::get('/post/{userid}', 'FeedsController@userPost')->name('admin.feeds.posts');
        Route::get('/purchase/{userid}', 'FeedsController@purchase')->name('admin.feeds.purchase');

    });
    
    Route::group('admin/bookings', ['namespace' => 'admin'], function() {
        Route::get('/', 'BookingController@index')->name('admin.booking.index');
        Route::get('/detail/{bookingid}', 'BookingController@detail') -> name('admin.booking.detail');
    });

    Route::group('admin/user_manage', ['namespace' => 'admin'], function() {
        Route::get('/', 'BlockUsersController@index')->name('admin.users.index');
    });
    
    Route::group('admin/tickets', ['namespace' => 'admin'], function() {
        Route::get('/', 'TicketsController@index')->name('admin.tickets.index');
        Route::get('/detail/{ticketid}', 'TicketsController@detail') -> name('admin.tickets.detail');
        Route::get('/delete/{ticketid}', 'TicketsController@delete_form') -> name('admin.tickets.delete');
    	Route::get('/submit_delete', 'TicketsController@delete_ticket') -> name('admin.tickets.submit_delete');
    	Route::get('/reply/{ticketid}', 'TicketsController@reply_form') -> name('admin.tickets.reply');
    	Route::get('/submit_reply', 'TicketsController@reply_ticket') -> name('admin.tickets.submit_reply');
    });
    Route::group('admin/transaction', ['namespace' => 'admin'], function() {
        Route::get('/', 'TransactionHistoryController@index')->name('admin.transaction.index');
    });

    Route::set('404_override', function(){
       show_404();
    });

    Route::set('translate_uri_dashes',FALSE);
