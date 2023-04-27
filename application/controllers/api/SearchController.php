<?php
class SearchController extends MY_Controller {
    
    public function __construct(){

        parent::__construct();
        $this->load->model('UserReview_model');
    }
    
    public function searchBusiness() {        
        $verifyTokenResult = $this->verificationToken($this->input->post('token'));
        
        $retVal = [];
        if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {            
            $search_user = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));
            
            $search_user_lat = $search_user[0]['latitude'];
            $search_user_lng = $search_user[0]['longitude'];      
            $search_user_radius = $search_user[0]['post_search_region'];      
            
            $category = $this->input->post('category');
            
            $where = array();
            $where['category'] = $category;
            $where['type'] = '1';       // Top Spot
            $where['status'] = '2';     // active auctions
            
            $tag = "";

            $pinnedTopSpots = array();    // position 1, 2, 3
            $topSpots = array();          // position 4, 5
            $search_result = array();

            if (!is_null($this->input->post('tags'))) {
                $tag = $this->input->post('tags');
            }
            /*
            for ($position = 0; $position < 5; $position++) {
                $where['position'] = $position;
                    
                $pointAuctions = $this->Auction_model->getPinPointAuctions($where, $tag);
                if (count($pointAuctions) > 0) {
                    $users = $this->User_model->getOnlyUser(array('id' => $pointAuctions[0]['user_id']));
                    
                    if (count($users) > 0) {                        
                        $user_lat = $users[0]['latitude'];
                        $user_lng = $users[0]['longitude'];
                        
                        $users[0]['distance'] = $this->vincentyGreatCircleDistance($search_user_lat, $search_user_lng, $user_lat, $user_lng);
                        
                        $business_info = $this->UserBusiness_model->getBusinessInfo($users[0]['id']);
                        if(count($business_info) > 0) {
                            $business_id = $business_info[0]['id'];
                            $reviews = $this->UserReview_model->getReviews(array('business_id' => $business_id));
                            
                            $review_count = count($reviews);
                            if ($review_count > 0) {
                                $users[0]['business_info']['reviews'] = $review_count;
                                $rating_sum = 0;
                                foreach($reviews as $review) {
                                    $rating_sum += $review['rating'];
                                }
                                
                                $users[0]['business_info']['rating'] = $rating_sum/(float)$review_count;
                                
                            } else {
                                $users[0]['business_info']['reviews'] = 0;
                                $users[0]['business_info']['rating'] = 0;
                            }
                        }
                        
                        if ($position < 3) {
                            array_push($pinnedTopSpots, $users[0]);
                            
                        } else {
                            array_push($topSpots, $users[0]);
                        }                        
                    }
                }                         
            } */
            
            // $allPins = array_merge($pinnedTopSpots, $topSpots);            
            // $tag_users = $this->UserTag_model->getUsers($tag);

            $tag_users = $this->User_model->getUsers(
                array(
                    'users.account_type' => 1, 
                    'users.status' => 3,
                    'user_extend_infos.approved' => 1
                ), 
                $category,
                $tag
            );

            if (count($tag_users) > 0) {
                foreach($tag_users as $tag_user) {
                    $userId = $tag_user['id'];
                    
                    // if (!$this->isUserExist($userId, $allPins)) {
                        $user_lat = $tag_user['latitude'];
                        $user_lng = $tag_user['longitude'];
                        
                        $distance = $this->vincentyGreatCircleDistance(
                            $search_user_lat, $search_user_lng, 
                            $user_lat, $user_lng);

                        /*
                        Temporarily remove the distance to see all businesses
                        if (floatval($search_user_radius) < 80 && $distance > floatval($search_user_radius)*1000) {
                            continue;
                        }

                        */

                        $tag_user['distance'] = $distance;                        
                        
                        $business_info = $this->UserBusiness_model->getBusinessInfo($tag_user['id']);
                        if(count($business_info) > 0) {
                            $business_id = $business_info[0]['id'];
                            $reviews = $this->UserReview_model->getReviews(array('business_id' => $business_id));
                            
                            $review_count = count($reviews);
                            if ($review_count > 0) {
                                $tag_user['business_info']['reviews'] = $review_count;
                                $rating_sum = 0;
                                foreach($reviews as $review) {
                                    $rating_sum += $review['rating'];
                                }
                                
                                $tag_user['business_info']['rating'] = $rating_sum/(float)$review_count;
                                
                            } else {
                                $tag_user['business_info']['reviews'] = 0;
                                $tag_user['business_info']['rating'] = 0;
                            }
                        }
                        
                        array_push($search_result, $tag_user);
                    // }                    
                }                
            }

            usort($search_result, function($a, $b) {
                if ($a['distance'] == $b['distance']) {
                    return $a['business_info']['rating'] < $b['business_info']['rating'];
                    
                } else {
                    return $a['distance'] > $b['distance'];
                }            
            });

            $result_array = array_merge($topSpots, $search_result);
            
            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::EXTRA_FIELD_NAME]['pins'] = $pinnedTopSpots;
            $retVal[self::EXTRA_FIELD_NAME]['search_result'] = $result_array;
            
        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
            $retVal[self::EXTRA_FIELD_NAME] = null;
        }
        
        echo json_encode($retVal);
    }
    
    private function isUserExist($userId, $list = array()) {
        if (count($list) > 0 ) {
            foreach ($list as $user) {
                if ($user['id'] == $userId) {
                    return true;
                }
            }
            
            return false;
            
        } else {
            return false;
        }
    }

    public function getSpotLight() {
        $verifyTokenResult = $this->verificationToken($this->input->post('token'));
        
        $retVal = [];
        if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
            $search_user = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));
            
            $search_user_lat = $search_user[0]['latitude'];
            $search_user_lng = $search_user[0]['longitude'];      
            $search_user_radius = $search_user[0]['post_search_region'];      
            
            $category = $this->input->post('category');

            $search_result = array();

            $foundUsers = $this->User_model->getUsers(
                array(
                    'users.account_type' => 1, 
                    'users.status' => 3,
                    'user_extend_infos.approved' => 1
                ), 
                $category,
                ""
            );

            if (count($foundUsers) > 0) {
                foreach($foundUsers as $foundUser) {
                    // $userId = $foundUser['id'];
                    
                    // $user_lat = $foundUser['latitude'];
                    // $user_lng = $foundUser['longitude'];
                    
                    // $distance = $this->vincentyGreatCircleDistance($search_user_lat, $search_user_lng, 
                    //                                                 $user_lat, $user_lng);

                    // if (floatval($search_user_radius) < 80 && $distance > floatval($search_user_radius)*1000) {
                    //     continue;
                    // }

                    // $foundUser['distance'] = $distance;                        
                    
                    $business_info = $this->UserBusiness_model->getBusinessInfo($foundUser['id']);
                    if(count($business_info) > 0) {
                        $business_id = $business_info[0]['id'];
                        $reviews = $this->UserReview_model->getReviews(array('business_id' => $business_id));
                        
                        $review_count = count($reviews);
                        if ($review_count > 0) {
                            $foundUser['business_info']['reviews'] = $review_count;
                            $rating_sum = 0;
                            foreach($reviews as $review) {
                                $rating_sum += $review['rating'];
                            }
                            
                            $foundUser['business_info']['rating'] = $rating_sum/(float)$review_count;
                            
                        } else {
                            $foundUser['business_info']['reviews'] = 0;
                            $foundUser['business_info']['rating'] = 0;
                        }
                        
                        array_push($search_result, $foundUser);
                    }                    
                }                
            }

            // usort($search_result, function($lhs, $rhs) {
            //     if ($lhs['distance'] == $rhs['distance']) {
            //         return $lhs['business_info']['rating'] < $rhs['business_info']['rating'];
                    
            //     } else {
            //         return $lhs['distance'] > $rhs['distance'];
            //     }            
            // });

            $page = 1;
            $per_page = 6;

            if (!empty($this->input->post('page'))) {
                $page = $this->input->post('page');
            }
            
            if (!empty($this->input->post('per_page'))) {
                $per_page = $this->input->post('per_page');
            }

            $result_array = array();
            $result_array['page'] = $page;
            $result_array['per_page'] = $per_page;
            $result_array['total_rows'] = count($search_result);

            $paginatedResult = array();
            if (count($search_result)) {
                $offset = ($page - 1)*$per_page;
                $paginatedResult = array_slice($search_result, $offset, $per_page);
            } 
            $result_array['spotlight'] = $paginatedResult;

            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::EXTRA_FIELD_NAME] = $result_array;

        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
            $retVal[self::EXTRA_FIELD_NAME] = null;
        }

        echo json_encode($retVal);        
    }

    public function getAllUsers() {
        $verifyTokenResult = $this->verificationToken($this->input->post('token'));
        
        $retVal = [];
        if ($verifyTokenResult[self::RESULT_FIELD_NAME]) { 
            $result_array = $this->User_model->getAllUsers(
                                array(
                                    'status' => 3
                                ));

            $retVal[self::RESULT_FIELD_NAME] = true;
            $retVal[self::EXTRA_FIELD_NAME] = $result_array;

        } else {
            $retVal[self::RESULT_FIELD_NAME] = false;
            $retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
            $retVal[self::EXTRA_FIELD_NAME] = null;
        }

        echo json_encode($retVal);
    }
}