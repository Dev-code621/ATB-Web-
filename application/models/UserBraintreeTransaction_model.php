<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/11
 * Time: 5:07 PM
 */
class UserBraintreeTransaction_model extends MY_Model
{

     public function getTransaction($id) {
       $transactions =  $this->db->select('*')
            -> from(self::TABLE_USER_BRAINTREE_TRANSACTION)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
         
        for ($i = 0; $i<count($transactions); $i++) {
        	if ($transactions[$i]["purchase_type"] == "product_variant") {
        		$transactions[$i]["variant"] = $this->Product_model->getProductVariation($transactions[$i]["target_id"]);
        	} else if ($transactions[$i]["purchase_type"] == "product") {
        		$transactions[$i]["product"] = $this->Product_model->getProduct($transactions[$i]["target_id"]);
        	} else if ($transactions[$i]["purchase_type"] == "service") {
        		$transactions[$i]["variant"] = $this->UserService_model->getServiceInfo($transactions[$i]["target_id"]);
        	} else if ($transactions[$i]["purchase_type"] == "post") {
        		$transactions[$i]["variant"] = $this->Post_model->getPostDetail($transactions[$i]["target_id"]);
        	} 
        }
                
        return $transactions;
    }
   
    public function getTransactionHistory($where  = array()) {
        $transactions = $this->db->select('*')->from(self::TABLE_USER_BRAINTREE_TRANSACTION)
                ->where($where)
                ->order_by('created_at', 'DESC')
                ->get()->result_array();
        // old      
        // for ($i = 0; $i<count($transactions); $i++) {
        // 	if ($transactions[$i]["purchase_type"] == "product_variant") {
        // 		$transactions[$i]["variant"] = $this->Product_model->getProductVariation($transactions[$i]["target_id"]);
        // 	} else if ($transactions[$i]["purchase_type"] == "product") {
        // 		$transactions[$i]["product"] = $this->Product_model->getProduct($transactions[$i]["target_id"]);
        // 	} else if ($transactions[$i]["purchase_type"] == "service") {
        // 		$transactions[$i]["variant"] = $this->UserService_model->getServiceInfo($transactions[$i]["target_id"]);
        // 	} else if ($transactions[$i]["purchase_type"] == "post") {
        // 		$transactions[$i]["variant"] = $this->Post_model->getPostDetail($transactions[$i]["target_id"]);
        // 	} 
        // }

        // new updated 19th March, 2022 by YueXi
        for ($i = 0; $i < count($transactions); $i ++) {
            $purchaseType = $transactions[$i]['purchase_type'];

            $items = array();

            if ($purchaseType == "product") {
                $items = $this->Product_model->getProduct($transactions[$i]["target_id"]);
                
            } else if ($purchaseType == "product_variant") {
                $variant = $this->Product_model->getProductVariation($transactions[$i]["target_id"]);
                if (count($variant) > 0) {
                    $items = $this->Product_model->getProduct($variant[0]["product_id"]);                    
                }

            } else if ($purchaseType == "service" || $purchaseType == "booking") {
                $items = $this->UserService_model->getServiceInfo($transactions[$i]["target_id"]);                
            }

            if (count($items) > 0) {
                $transactions[$i]['item'] = $items[0];
            }

            $from_to = $transactions[$i]['from_to'];
            if (!empty($from_to)) {
                $from_to_user = $this->User_model->getOnlyUser(array(
                    'id' => $from_to
                ));

                if (count($from_to_user)) {
                    $transactions[$i]['from_to_user'] = $from_to_user[0];
                }
            }
        }
                
        return $transactions;
    }

    public function insertNewTransaction($insArr) {
        if($this->db->insert(self::TABLE_USER_BRAINTREE_TRANSACTION, $insArr)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = -1;
        }
        return $result;
    }
	
    public function updateTransactionRecord($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_USER_BRAINTREE_TRANSACTION, $setArray);
    }
    
    public function getPurchasedProductHistory($user_id){
    	$where = array("user_id" => $user_id, "amount <" => 0);	
    	
    	$transactions = $this->db->select('*')->from(self::TABLE_USER_BRAINTREE_TRANSACTION)
    		->where($where)
    		->where("(purchase_type = 'product_variant' OR purchase_type = 'product')")
                ->order_by('created_at', 'DESC')
                ->get()->result_array();
                
        for ($i = 0; $i<count($transactions); $i++) {
        	if ($transactions[$i]["purchase_type"] == "product_variant") {
                $variant = $this->Product_model->getProductVariation($transactions[$i]["target_id"]);
        		$transactions[$i]["variant"] = $variant;
                if (count($variant) > 0) {
                    $transactions[$i]["product"] = $this->Product_model->getProduct($variant[0]["product_id"]);
                }    

        	} else if ($transactions[$i]["purchase_type"] == "product") {
                $transactions[$i]["product"] = $this->Product_model->getProduct($transactions[$i]["target_id"]);        		
        	}

            $sellerId = $transactions[$i]['from_to'];
            if (!empty($sellerId)) {
                $seller = $this->User_model->getOnlyUser(array(
                    'id' => $sellerId
                ));

                $transactions[$i]['user'] = $seller[0];
            }
            
            // else if ($transactions[$i]["purchase_type"] == "service") {
        	// 	$transactions[$i]["variant"] = $this->UserService_model->getServiceInfo($transactions[$i]["target_id"]);
        	// } else if ($transactions[$i]["purchase_type"] == "post") {
        	// 	$transactions[$i]["variant"] = $this->Post_model->getPostDetail($transactions[$i]["target_id"]);
        	// } 
        }
                
        return $transactions;
    }
    
    public function getSoldProductHistory($user_id, $is_business){
    	$where = array("user_id" => $user_id, "amount >" => 0, "is_business" => $is_business);	
    	
    	$transactions = $this->db->select('*')->from(self::TABLE_USER_BRAINTREE_TRANSACTION)
    		->where($where)
    		->where("(purchase_type = 'product_variant' OR purchase_type = 'product')")
                ->order_by('created_at', 'DESC')
                ->get()->result_array();
                
        for ($i = 0; $i<count($transactions); $i++) {
        	if ($transactions[$i]["purchase_type"] == "product_variant") {
                $variant = $this->Product_model->getProductVariation($transactions[$i]["target_id"]);
        		$transactions[$i]["variant"] = $variant;
                if (count($variant) > 0) {
                    $transactions[$i]["product"] = $this->Product_model->getProduct($variant[0]["product_id"]);    
                }
        	} else if ($transactions[$i]["purchase_type"] == "product") {
        		$transactions[$i]["product"] = $this->Product_model->getProduct($transactions[$i]["target_id"]);
        	}

            $buyerId = $transactions[$i]['from_to'];
            if (!empty($buyerId)) {
                $seller = $this->User_model->getOnlyUser(array(
                    'id' => $buyerId
                ));

                $transactions[$i]['user'] = $seller[0];
            }
            
            // else if ($transactions[$i]["purchase_type"] == "service") {
        	// 	$transactions[$i]["variant"] = $this->UserService_model->getServiceInfo($transactions[$i]["target_id"]);
        	// } else if ($transactions[$i]["purchase_type"] == "post") {
        	// 	$transactions[$i]["variant"] = $this->Post_model->getPostDetail($transactions[$i]["target_id"]);
        	// } 
        }
                
        return $transactions;
    }
}
