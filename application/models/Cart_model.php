<?php

class Cart_model extends MY_Model
{

   public function insertNewCartProduct($user_id, $product_id, $variantId) {
	$product = $this->db->select('*')
            ->from(self::TABLE_CART)
            ->where(array('user_id' => $user_id, "product_id" => $product_id, "variant_id" => $variantId))
            ->get()
            ->result_array();
	if (count($product) > 0 ) {
		$quantity = $product[0]["quantity"];
		$this->db->where(array('user_id' => $user_id, "product_id" => $product_id, "variant_id" => $variantId));
        	$this->db->update(self::TABLE_CART, array('quantity' => ++$quantity));
		//return $quantity;
		//return $product[0]["id"]
		return array("cart_id" => $product[0]["id"], "quantity" => $quantity);
	} else {
		$this->db->insert(self::TABLE_CART,array('user_id' => $user_id, "product_id" => $product_id, "variant_id" => $variantId,  "quantity" => 1));
		$cart_id = $this->db->insert_id();
            	//return 1;
		return array("cart_id" => $cart_id, "quantity" => 1);
	}
	return -1;
   }

   public function deleteCartProduct($user_id, $product_id, $variantId, $productQty) {
       $product = $this->db->select('*')
            ->from(self::TABLE_CART)
            ->where(array('user_id' => $user_id, "product_id" => $product_id, "variant_id" => $variantId))
            ->get()
            ->result_array();
	if (count($product) > 0 ) {
		$quantity = $product[0]["quantity"];
		if (($quantity - $productQty) >= 1) {
			$this->db->where(array('user_id' => $user_id, "product_id" => $product_id, "variant_id" => $variantId));
        		
			$this->db->update(self::TABLE_CART, array('quantity' => $quantity - $productQty ));
			return true;
		} else {
			$this->db->where(array('user_id' => $user_id, "product_id" => $product_id, "variant_id" => $variantId))->delete(self::TABLE_CART);
			return true;
		}
		
	}
	return false;
   }

   public function deleteCartProducts($user_id, $product_id, $variantId) {
       $product = $this->db->select('*')
            ->from(self::TABLE_CART)
            ->where(array('user_id' => $user_id, "product_id" => $product_id, "variant_id" => $variantId))
            ->get()
            ->result_array();
	if (count($product) > 0 ) {		
		
		$this->db->where(array('user_id' => $user_id, "product_id" => $product_id))->delete(self::TABLE_CART);
		return true;		
	}
	return false;
   }

   public function getUsersCart($user_id){
      	$userCart = $this->db->select('*')
            ->from(self::TABLE_CART)
            ->where(array('user_id' => $user_id))
            ->get()
            ->result_array();

	return $userCart;
   }

}
