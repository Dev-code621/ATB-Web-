<?php

/**
 * Created by PhpStorm.
 * User: rock
 * Date: 23/7/2017
 * Time: 12:49 AM
 */
class MY_Model extends CI_Model
{
    /**
     * MY_Model constructor.
     */
    const DB_RESULT_SUCCESS = true;
    const DB_RESULT_FAILED = false;
    const TABLE_USER_INFO = "users";
    const TABLE_USER_FEEDS = "user_feeds";
    const TABLE_LOGIN_HISTORY = "login_history";
    const TABLE_BUSINESS_INFO = "user_extend_infos";
    const TABLE_ACCOUNT_SETTINGS = "user_account_settings";
    const TABLE_LIKE_INFOS = "like_infos";
    const TABLE_NOTIFICATION_LIST = "notification_history";
    const TABLE_PAYMENT_CARD_LIST = "payment_cards";
    const TABLE_SERVICE_INFO_LIST = "user_service_infos";
    const TABLE_FORGOT_PASS_LIST = "forgot_pass_requests";
    const TABLE_POST_LIST = "posts";
    const TABLE_POST_IMGS = "post_imgs";
    const TABLE_POST_COMMENT = "post_comment";
    const TABLE_POST_LIKE = "post_like";
    const TABLE_POST_REPORT = "post_reports";
    const TABLE_USER_BOOKMARK = "user_bookmark";
    const TABLE_USER_REVIEW = "user_review";
    const TABLE_USER_TRANSACTION = "user_transaction";
    const TABLE_USER_BRAINTREE = "braintree";
    const TABLE_USER_BRAINTREE_TRANSACTION = "braintree_transaction";
    const TABLE_ADMIN = "admin";
    const TABLE_ADMIN_ALERTS = "admin_alerts";
    const TABLE_NOTIFICATION_KEYWORDS = "notification_keywords";
    const TABLE_POST_REPLY = "post_reply";
    const POST_COMMENT_HIDDEN = "post_comment_hidden";
    const POST_REPLY_HIDDEN = "post_reply_hidden";
    const POST_POLL = "post_poll";
    const POST_POLL_VOTE = "post_poll_vote";
    const TABLE_CART = "cart";
    const TABLE_SERVICE_FILE = "user_service_files";
    const TABLE_USER_SOCIAL = "user_socials";
    const TABLE_PRODUCT = "product";
    const TABLE_TAGS = "tags";
    const TABLE_SERVICE_TAGS = "service_tags";
    const TABLE_PRODUCT_TAGS = "product_tags";
    const TABLE_POST_TAGS = "post_tags";
    const TABLE_PRODUCT_IMGS = "product_imgs";
    const TABLE_SERVICE_IMGS = "service_imgs";
    const TABLE_ATTRIBUTE = "attribute";
    const TABLE_PRODUCT_ATTRIBUTE = "product_attribute";
    const TABLE_PRODUCT_VARIATION = "product_variation";
    const TABLE_PRODUCT_VARIATION_ATTRIBUTE = "product_variation_attribute";
    const TABLE_USER_BUSINESS_WEEK = "user_business_week";
    const TABLE_USER_BUSINESS_HOLIDAY = "user_business_holiday";	
    const TABLE_SERVICE_BOOKING = "service_bookings";	
    const TABLE_SERVICE_BOOKING_REPORT = "service_booking_reports";
    const TABLE_USER_BUSINESS_DISABLED_SLOTS = "user_business_disabled_slots";
    const TABLE_AUCTIONS = "auctions";
    const TABLE_USER_TAGS = "user_tags";
		
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
}
