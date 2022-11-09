<?php

/**
 * Created by PhpStorm.
 * User: zeus
 * Date: 2019/7/11
 * Time: 5:07 PM
 */
class UserTransaction_model extends MY_Model
{
   
    public function getTransactionHistory($where  = array()) {
        return $this->db->select('*')->from(self::TABLE_USER_TRANSACTION)->where($where)
                ->order_by('created_at', 'DESC')
                ->get()->result_array();
    }

    public function insertNewTransaction($insArr) {
        if($this->db->insert(self::TABLE_USER_TRANSACTION, $insArr)) {
            $result[MY_Controller::RESULT_FIELD_NAME] = $this->db->insert_id();
        }
        else {
            $result[MY_Controller::RESULT_FIELD_NAME] = -1;
        }
        return $result;
    }

    public function update($set, $where) {
        $this->db->where($where);
        $this->db->update(self::TABLE_USER_TRANSACTION, $set);
    }

    public function getPurchases($user_id) {
        $where = array(
            'user_id' => $user_id, 
            'status' => 1
        );

        $transactions = $this->db->select('*')
            ->from(self::TABLE_USER_TRANSACTION)
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

            $sellerId = $transactions[$i]['destination'];
            if (!empty($sellerId)) {
                $sellers = $this->User_model->getOnlyUser(array('id' => $sellerId));
                $transactions[$i]['user'] = $sellers[0];
            }
        }

        return $transactions;
    }

    public function getSoldItems($user_id, $is_business) {
        $where = array(
            'destination' => $user_id, 
            'status' => 1, 
            'is_business' => $is_business
        );

        $transactions = $this->db->select('*')
            ->from(self::TABLE_USER_TRANSACTION)
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

            $buyerId = $transactions[$i]['user_id'];
            if (!empty($buyerId)) {
                $buyers = $this->User_model->getOnlyUser(array('id' => $buyerId));
                $transactions[$i]['user'] = $buyers[0];
            }
        }

        return $transactions;
    }
}