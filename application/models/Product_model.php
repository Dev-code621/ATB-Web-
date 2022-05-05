<?php
class Product_model extends MY_Model
{
    public function insertNewProduct($ins) {
        if($this->db->insert(self::TABLE_PRODUCT, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
    public function updateProduct($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_PRODUCT, $setArray);
    }
    
    public function getProduct($id) {
       $products =  $this->db->select('*')
            -> from(self::TABLE_PRODUCT)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
            
       for($i = 0 ; $i < count($products) ; $i++) {
            $products[$i]['post_imgs'] = $this->getPostImage(array('product_id' => $products[$i]['id']));
            $products[$i]["variations"] = $this->getProductVariations(array('product_id' => $products[$i]['id']));
        }    
        
        return $products;
    }
    
    public function getUserProduct($id, $is_business) {
        $products =  $this->db->select('*')
            -> from(self::TABLE_PRODUCT)
            ->where(array('user_id' => $id, 'poster_profile_type'=>$is_business))
            ->where('is_active !=', '99') // deleted
            ->where('is_active !=', '98') // draft
            ->get()
            ->result_array();
            
            for($i = 0 ; $i < count($products) ; $i++) {
                    $products[$i]['post_imgs'] = $this->getPostImage(array('product_id' => $products[$i]['id']));
                    $products[$i]["variations"] = $this->getProductVariations(array('product_id' => $products[$i]['id']));
        }    
        
        return $products;
    }

    public function getUserDrafts($id, $is_business) {
        $products =  $this->db->select('*')
            -> from(self::TABLE_PRODUCT)
            ->where(array(
                'user_id' => $id, 
                'poster_profile_type'=>$is_business, 
                'is_active' => 98))
            ->get()
            ->result_array();
            
            for($i = 0 ; $i < count($products) ; $i++) {
                    $products[$i]['post_imgs'] = $this->getPostImage(array('product_id' => $products[$i]['id']));
                    $products[$i]["variations"] = $this->getProductVariations(array('product_id' => $products[$i]['id']));
        }    
        
        return $products;
    }
    
    public function removeProduct($where) {
        $this->db->where($where)->delete(self::TABLE_PRODUCT);
    }
    
    public function getNextMultiGroup(){
		$group =  $this->db->select('multi_group') -> from(self::TABLE_PRODUCT)
            ->where('multi_group is NOT NULL', NULL, FALSE)
            ->order_by('multi_group', 'DESC')
			->limit(1)
            ->get()
            ->result_array();
		
		if (count($group) < 1){
			return 0;
		} 
		return $group[0]['multi_group'] + 1;
	}
	
	public function getCurrentMultiGroup($userid){
		$group =  $this->db->select('multi_group') -> from(self::TABLE_PRODUCT)
            ->where('multi_group is NOT NULL', NULL, FALSE)
			->where(array("user_id" => $userid))
            ->order_by('multi_group', 'DESC')
			->limit(1)
            ->get()
            ->result_array();
		
		if (count($group) < 1){
			return 0;
		} 
		return $group[0]['multi_group'];
	}
	
	public function isCurrentlyUploadingMultiGroup($userid){
		$groups = $this->db->select('created_at') -> from(self::TABLE_PRODUCT)
            ->where('multi_group is NOT NULL', NULL, FALSE)
			->where(array("user_id" => $userid))
            ->order_by('id', 'DESC')
			->limit(1)
            ->get()
            ->result_array();
			
		if (count($groups) < 1){
			return false;
		}  
		
		$last_update = $groups[0]['created_at'];
		if (time() - $last_update > 20) {
			return false;
		} else {
			return true;
		}
	}
	
	public function getPostImage($where) {
        return $this->db->select('*')
                            ->from(self::TABLE_PRODUCT_IMGS)
                            ->where($where)
                            ->get()
                            ->result_array();
    }
    
    public function removePostImg($where) {
        $this->db->where($where)->delete(self::TABLE_PRODUCT_IMGS);
    }
    
    public function insertNewImage($ins) {
        if($this->db->insert(self::TABLE_PRODUCT_IMGS, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
 
    public function insertNewProductAttribute($ins) {
        if($this->db->insert(self::TABLE_PRODUCT_ATTRIBUTE, $ins)) {
            return $this->db->insert_id();
        }
        else {
            return -1;
        }
    }
    
    public function updateProductAttribute($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_PRODUCT_ATTRIBUTE, $setArray);
    }
    
    public function getProductAttribute($id) {
       $Attribute =  $this->db->select('*')
            -> from(self::TABLE_PRODUCT_ATTRIBUTE)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
       
        return $Attribute;
    }
    
    public function getProductAttributes($search = array()) {
        $Attributes =  $this->db->select('*')
            -> from(self::TABLE_PRODUCT_ATTRIBUTE)
            ->where($search)
            ->get()
            ->result_array();
            
        return $Attributes;
    }
    
    public function removeProductAttribute($where) {
        $this->db->where($where)->delete(self::TABLE_PRODUCT_ATTRIBUTE);
    }
    
    public function insertNewProductVariation($variation, $attributes) {
        if($this->db->insert(self::TABLE_PRODUCT_VARIATION, $variation)) {
            $varaitionId =  $this->db->insert_id();
            
            foreach ($attributes as $attribute) {
            	$this->db->insert(self::TABLE_PRODUCT_VARIATION_ATTRIBUTE, array(
            		"variation_id" => $varaitionId,
            		"product_attribute_id" => $attribute["id"],
            		"value" => $attribute["value"],
            		"updated_at" => time(),
            		"created_at" => time()
            	));
            }
            
            return $varaitionId;
        }
        else {
            return -1;
        }
    }
    
    public function updateProductVariation($setArray, $whereArray) {
        $this->db->where($whereArray);
        $this->db->update(self::TABLE_PRODUCT_VARIATION, $setArray);
    }
    
    public function getProductVariation($id) {
       $variations =  $this->db->select('*')
            -> from(self::TABLE_PRODUCT_VARIATION)
            ->where(array('id' => $id))
            ->get()
            ->result_array();
            
        for ($i = 0; $i < count($variations); $i++) {
        	$saveAttributes = array();
        	
        	$variationAttributes = $this->db->select('*')
                -> from(self::TABLE_PRODUCT_VARIATION_ATTRIBUTE)
                ->where(array('variation_id' => $variations[$i]['id']))
                ->get()
                ->result_array();
		
            foreach ($variationAttributes as $variationAttribute){
                $productAttributes = $this->db->select('*')
                    -> from(self::TABLE_PRODUCT_ATTRIBUTE)
                    ->where(array('id' => $variationAttribute['product_attribute_id']))
                    ->get()
                    ->result_array();
                    
                foreach($productAttributes as $productAttribute) {                    
                    $saveAttributes[] = array(
                        "attribute_id" => $productAttribute['id'],
                        "attribute_title" => $productAttribute['value'],
                        "variant_attirbute_value" => $variationAttribute["value"]
                    );
                }
            }
		
		    $variations[$i]["attributes"] = $saveAttributes;
        }
       
        return $variations;
    }
    
    public function getProductVariations($search = array()) {
        $variations =  $this->db->select('*')
            -> from(self::TABLE_PRODUCT_VARIATION)
            ->where($search)
            ->get()
            ->result_array();
            
        for ($i = 0; $i < count($variations); $i++) {
        	$saveAttributes = array();
        	
        	$variationAttributes = $this->db->select('*')
		    -> from(self::TABLE_PRODUCT_VARIATION_ATTRIBUTE)
		    ->where(array('variation_id' => $variations[$i]['id']))
		    ->get()
		    ->result_array();
		
		foreach ($variationAttributes as $variationAttribute){
			$productAttributes = $this->db->select('*')
			    -> from(self::TABLE_PRODUCT_ATTRIBUTE)
			    ->where(array('id' => $variationAttribute['product_attribute_id']))
			    ->get()
			    ->result_array();
			    
			 foreach($productAttributes as $productAttribute){
			 	
				$saveAttributes[] = array(
					"attribute_id" => $productAttribute['id'],
					"attribute_title" => $productAttribute['value'],
					"variant_attirbute_value" => $variationAttribute["value"]
					);
			 }
		}
		
		$variations[$i]["attributes"] = $saveAttributes;
		
        }
            
        return $variations;
    }
    
    public function removeProductVariation($where) {
        $this->db->where($where)->delete(self::TABLE_PRODUCT_VARIATION);
    }
}
