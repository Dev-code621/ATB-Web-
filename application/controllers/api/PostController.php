<?php
class PostController extends MY_Controller
{
	public function get_multi_group_id()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			// $multi_group = 0;

			// if ($this->Post_model->isCurrentlyUploadingMultiGroup($verifyTokenResult['id'])) {
			// 	$multi_group = $this->Post_model->getCurrentMultiGroup($verifyTokenResult['id']);
			// } else {
				$multi_group = $this->Post_model->getNextMultiGroup();
			// }

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $multi_group;
            
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}

	public function publish()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$insResult = 0;
			$status = "1";
			if ($this->input->post('type') == '3') {
				$status = '3';
			}

			if ($this->input->post('is_multi') == "1") {				
				$createPostArray = array(
						'user_id' => $verifyTokenResult['id'],
						'post_type' => $this->input->post('type'),
						'poster_profile_type' => $this->input->post('profile_type'),
						'media_type' => $this->input->post('media_type'),
						'title' => $this->input->post('title'),
						'description' => $this->input->post('description'),
						'post_brand' => $this->input->post('brand'),
						'price' => $this->input->post('price'),
						'duration' => $this->input->post('duration'),
						'category_title' => $this->input->post('category_title'),
						'post_condition' => $this->input->post('post_condition'),
						'post_tags' => $this->input->post('post_tags'),
						'post_item' => $this->input->post('item_title'),
						'post_size' => $this->input->post('size_title'),
						'payment_options' => $this->input->post('payment_options'),
						'post_location' => $this->input->post('location_id'),
						'delivery_option' => $this->input->post('delivery_option'),
						'delivery_cost' => $this->input->post('delivery_cost'),
						'deposit' => $this->input->post("deposit"),
						"is_deposit_required" => $this->input->post("is_deposit_required"),
						'lat' => $this->input->post("lat"),
						'lng' => $this->input->post("lng"),
						'is_multi' => $this->input->post('is_multi'),
						'multi_pos' => $this->input->post('multi_pos'),
						'multi_group' => $this->input->post('multi_group'),
						'service_id' => $this->input->post('service_id'),
						'product_id' => $this->input->post('product_id'),
						'insurance_id' => $this->input->post('insurance_id'),
						'qualification_id' => $this->input->post('qualification_id'),
						'cancellations' => $this->input->post('cancellations'), 
						'is_active' => $status,
						'updated_at' => time(),
						'created_at' => time()
					);
				
				if (!empty($this->input->post('scheduled'))) {
					$createPostArray['scheduled'] = $this->input->post('scheduled');
					$createPostArray['is_active'] = 5;
				}
			
				$insResult = $this->Post_model->insertNewPost($createPostArray);

			} else {
				$createPostArray = array(
						'user_id' => $verifyTokenResult['id'],
						'post_type' => $this->input->post('type'),
						'poster_profile_type' => $this->input->post('profile_type'),
						'media_type' => $this->input->post('media_type'),
						'title' => $this->input->post('title'),
						'description' => $this->input->post('description'),
						'post_brand' => $this->input->post('brand'),
						'price' => $this->input->post('price'),
						'duration' => $this->input->post('duration'),
						'category_title' => $this->input->post('category_title'),
						'post_condition' => $this->input->post('post_condition'),
						'post_tags' => $this->input->post('post_tags'),
						'post_item' => $this->input->post('item_title'),
						'post_size' => $this->input->post('size_title'),
						'payment_options' => $this->input->post('payment_options'),
						'post_location' => $this->input->post('location_id'),
						'delivery_option' => $this->input->post('delivery_option'),
						'delivery_cost' => $this->input->post('delivery_cost'),
						'deposit' => $this->input->post("deposit"),
                        "is_deposit_required" => $this->input->post("is_deposit_required"),
						'lat' => $this->input->post("lat"),
						'lng' => $this->input->post("lng"),
						'service_id' => $this->input->post('service_id'),
						'insurance_id' => $this->input->post('insurance_id'),
						'product_id' => $this->input->post('product_id'),
						'qualification_id' => $this->input->post('qualification_id'),
						'cancellations' => $this->input->post('cancellations'), 
						'poll_expiry' => $this->input->post('poll_day'),
						'is_active' => $status,
						'updated_at' => time(),
						'created_at' => time()
					);
					
				if (!empty($this->input->post('scheduled'))) {
					$createPostArray['scheduled'] = $this->input->post('scheduled');
					$createPostArray['is_active'] = 5;
				}
					
				$insResult = $this->Post_model->insertNewPost($createPostArray);
			}

			if ($insResult > 0) {
				if (!empty($_FILES)) {
					for ($fIndex = 0; $fIndex < count($_FILES['post_imgs']['name']); $fIndex++) {
						$_FILES['post_img']['name'] = $_FILES['post_imgs']['name'][$fIndex];
						$_FILES['post_img']['type'] = $_FILES['post_imgs']['type'][$fIndex];
						$_FILES['post_img']['tmp_name'] = $_FILES['post_imgs']['tmp_name'][$fIndex];
						$_FILES['post_img']['error'] = $_FILES['post_imgs']['error'][$fIndex];
						$_FILES['post_img']['size'] = $_FILES['post_imgs']['size'][$fIndex];

						$uploadFileName = $this->fileUpload('post', 'post' . time() /*$_FILES['post_img']['name']*/, 'post_img');
						$this->Post_model->insertNewImage(array('post_id' => $insResult, 'path' => $uploadFileName, 'created_at' => time()));
					}
				} else if ($this->input->post('post_img_uris') != null) {
					$uriList = $this->input->post('post_img_uris');
					$uriArray = explode(",", $uriList);

					foreach ($uriArray as $uri) {
						$this->Post_model->insertNewImage(array('post_id' => $insResult, 'path' => str_replace(' ', '', $uri), 'created_at' => time()));
					}
				}

				if ($this->input->post('type') == "4") {
					$poll_expire_day = $this->input->post('poll_day');
					//$poll_expire = time() + ($poll_expire_day * 24 * 60 * 60);
                    $poll_expire = $poll_expire_day;

					$poll_options = explode("|", $this->input->post('poll_options'));

					foreach ($poll_options as $option) {
						$this->PostPoll_model->insertNewOption(
							array(
								"post_id" => $insResult, "poll_value" => $option, "expires" => $poll_expire, 'updated_at' => time(), 'created_at' => time()
							)
						);
					}
				}

				$insertedPost = $this->Post_model->getPostInfo(array('id' => $insResult));
				
				$tagList = $this->input->post('tags');
				$tags = explode(",", $tagList);
				
				
				foreach ($tags as $tagName){
						 $tag = $this->Tag_model->getTagName($tagName);
						 if (count($tag) > 0){
						 	$this->Tag_model->insertPostTag(array(
						 		"post_id" => $insResult,
						 		"tag_id" => $tag[0]["id"],
								'created_at' => time()
						 	));
						 } else {
						 	$tagId = $this->Tag_model->insertNewTag(
								array(
									'tag' => $tagName,
									'created_at' => time()
								)
							);
							$this->Tag_model->insertPostTag(array(
						 		"post_id" => $insResult,
						 		"tag_id" => $tagId,
								'created_at' => time()
						 	));
						 }
					}
				
				foreach ($insertedPost as $key => $value){
					$tagids = $this->Tag_model->getPostTags($value['id']);
					$tags = array();
					
					foreach ($tagids as $tagid) {
						$tags[] = $this->Tag_model->getTag($tagid['tag_id']);
					}
					
					$insertedPost[$key]["tags"] = $tags;
				}

				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Successfully published";
				$retVal[self::EXTRA_FIELD_NAME] = $insertedPost[0];

				$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));

				$followers = $this->LikeInfo_model->getFollowers('0', $verifyTokenResult['id']);

				$postType = $this->input->post('type');
				foreach ($followers as $follower) {
					if ($follower['post_notifications'] == 1) {
						if ($postType == "4") {
							$this->NotificationHistory_model->insertNewNotification(
								array(
									'user_id' => $follower['follow_user_id'],
									'type' => 21,
									'related_id' => $insertedPost[0]['id'],
									'read_status' => 0,
									'send_status' => 0,
									'visible' => 1,
									'text' => " has shared a poll post",
									'name' => $users[0]['user_name'],
									'profile_image' => $users[0]['pic_url'],
									'updated_at' => time(),
									'created_at' => time()
								)
							);

						} else {
							$this->NotificationHistory_model->insertNewNotification(
								array(
									'user_id' => $follower['follow_user_id'],
									'type' => 20,
									'related_id' => $insertedPost[0]['id'],
									'read_status' => 0,
									'send_status' => 0,
									'visible' => 1,
									'text' => " has shared a new post",
									'name' => $users[0]['user_name'],
									'profile_image' => $users[0]['pic_url'],
									'updated_at' => time(),
									'created_at' => time()
								)
							);
						}
						
					}
				}

			} else {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Failed to publish";
				$retVal[self::EXTRA_FIELD_NAME] = null;
			}

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}

    // This will be used for advice only
    // for sales and service post, it will go to update the product or service directly
	public function update_content()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {             
            $post_id = $this->input->post('id');
			$this->Post_model->updatePostContent(   
				array(  'user_id' => $verifyTokenResult['id'],
                        'post_type' => $this->input->post('type'),
                        'poster_profile_type' => $this->input->post('profile_type'),
                        'media_type' => $this->input->post('media_type'),
                        'title' => $this->input->post('title'),
                        'description' => $this->input->post('description'),
                        'post_brand' => $this->input->post('brand'),
                        'price' => $this->input->post('price'),
                        'category_title' => $this->input->post('category_title'),
                        'post_condition' => $this->input->post('post_condition'),
                        'post_tags' => $this->input->post('post_tags'),
                        'post_item' => $this->input->post('item_title'),
                        'post_size' => $this->input->post('size_title'),
                        'payment_options' => $this->input->post('payment_options'),
                        'post_location' => $this->input->post('location_id'),
                        'delivery_option' => $this->input->post('delivery_option'),
                        'delivery_cost' => $this->input->post('delivery_cost'),
                        'deposit' => $this->input->post("deposit"),
                        "is_deposit_required" => $this->input->post("is_deposit_required"),
                        'lat' => $this->input->post("lat"),
                        'lng' => $this->input->post("lng"),
                        'product_id' => $this->input->post('product_id'),
                        'service_id' => $this->input->post('service_id'),
                        'insurance_id' => $this->input->post('insurance_id'),
                        'qualification_id' => $this->input->post('qualification_id'),
                        'cancellations' => $this->input->post('cancellations'), 
                        'updated_at' => time()      
					),
                array('id' => $post_id)
			);    
                                 
            if ($this->input->post('post_img_uris') != null) {
                $uriList = $this->input->post('post_img_uris');
                $uriArray = explode(",", $uriList);
                
                if (count(array_filter($uriArray, function ($k){ return $k != "data"; })) != count($uriArray)) {
                    // 1 - user replaced all images or the video
                    // 2 - user partially updadted images
                    // 3 - user changed the post type to 'Image' or 'Video' from text
                    $this->Post_model->removePostImg(array('post_id' => $post_id));
                    
                    if (!empty($_FILES)) {
                        for ($fIndex = 0; $fIndex < count($_FILES['post_imgs']['name']); $fIndex++) {
                            $_FILES['post_img']['name'] = $_FILES['post_imgs']['name'][$fIndex];
                            $_FILES['post_img']['type'] = $_FILES['post_imgs']['type'][$fIndex];
                            $_FILES['post_img']['tmp_name'] = $_FILES['post_imgs']['tmp_name'][$fIndex];
                            $_FILES['post_img']['error'] = $_FILES['post_imgs']['error'][$fIndex];
                            $_FILES['post_img']['size'] = $_FILES['post_imgs']['size'][$fIndex];

                            $uploadFileName = $this->fileUpload('post', 'post' . time(), 'post_img');
                            $dataIndex = array_search("data", $uriArray);   
                            if ($dataIndex !== false) {
                                $uriArray = array_replace($uriArray, array($dataIndex => $uploadFileName));
                            }                    
                        }
                    }
                    
                    foreach ($uriArray as $uri) {
                        if (!empty($uri)) {
                            $this->Post_model->insertNewImage(array('post_id' => $post_id, 'path' => $uri, 'created_at' => time()));
                        }
                    }
                } else {
                    // check if the user only deleted few images
                    $imagesCnt = count($this->Post_model->getPostImage(array('post_id' => $post_id)));  
                    if ($imagesCnt != count($uriArray)) {
                        $this->Post_model->removePostImg(array('post_id' => $post_id));
                        
                        foreach ($uriArray as $uri) {
                            if (!empty($uri)) {
                                $this->Post_model->insertNewImage(array('post_id' => $post_id, 'path' => $uri, 'created_at' => time()));
                            }
                        }
                    }  
                }   
                            
            } else {
                // 'Text'
                // for the case, post updated to a 'Text' post from the 'Image' or 'Video' post
                $this->Post_model->removePostImg(array('post_id' => $post_id));
            }

			$updatedPost = $this->Post_model->getPostInfo(array('id' => $this->input->post('id')));
				
			$this->Tag_model->removePostTag(array("post_id" => $this->input->post('id')));
			
			$tagList = $this->input->post('post_tags');
			$tags = explode(",", $tagList);				
			foreach ($tags as $tagId){
				 $tag = $this->Tag_model->getTag($tagId);
				 if (count($tag) > 0){
					$this->Tag_model->insertPostTag(array(
					 	"post_id" => $this->input->post('id'),
					 	"tag_id" => $tag[0]["id"],
						'created_at' => time()
					));
				 }
			}				

			foreach ($updatedPost as $key => $value){
				$tagids = $this->Tag_model->getPostTags($value['id']);
				$tags = array();
				
				foreach ($tagids as $tagid) {
					$tags[] = $this->Tag_model->getTag($tagid['tag_id']);
				}
				
				$updatedPost[$key]["tags"] = $tags;
			}
			
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Successfully updated";
			$retVal[self::EXTRA_FIELD_NAME] = $updatedPost[0];
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}

	public function countServicePosts()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$monthago = time() - 86400;

			$posts = $this->Post_model->getPostInfo(
				array(
					'user_id' => $verifyTokenResult['id'],
					'post_type' => 3,
                    'multi_pos' => 0,
					'created_at > ' => $monthago
				)
			);

			//if (count($posts) > 2) {
            // for test
            if (count($posts) > 100) {
				$retVal[self::RESULT_FIELD_NAME] = false;
			} else {
				$retVal[self::RESULT_FIELD_NAME] = true;
			}
			$retVal[self::MESSAGE_FIELD_NAME] = count($posts);
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}

	public function countSalesPosts()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$monthago = time() - 2592000;

			$posts = $this->Post_model->getPostInfo(
				array(
					'user_id' => $verifyTokenResult['id'],
					'post_type' => 2,
                    'multi_pos' => 0,
					'created_at > ' => $monthago
				)
			);

			//if (count($posts) > 2) {
            // for test
            if (count($posts) > 100) {
				$retVal[self::RESULT_FIELD_NAME] = false;
			} else {
				$retVal[self::RESULT_FIELD_NAME] = true;
			}
			$retVal[self::MESSAGE_FIELD_NAME] = count($posts);
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}

	// public function is_sold()
	// {
	// 	$verifyTokenResult = $this->verificationToken($this->input->post('token'));
	// 	$retVal = [];

	// 	if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
	// 		$updatedPost = $this->Post_model->getPostInfo(array('id' => $this->input->post('id')));

	// 		if ($updatedPost[0]['is_sold'] == 1) {
	// 			$retVal[self::RESULT_FIELD_NAME] = true;
	// 		} else {
	// 			$retVal[self::RESULT_FIELD_NAME] = false;
	// 		}
	// 	} else {
	// 		$retVal[self::RESULT_FIELD_NAME] = false;
	// 		$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
	// 		$retVal[self::EXTRA_FIELD_NAME] = null;
	// 	}

	// 	echo json_encode($retVal);
	// }

	public function is_sold() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$productId = $this->input->post('product_id');

			if (empty($productId)) {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "The product is invalid.";

			} else {
				$products = $this->Product_model->getProduct($productId);
				if (count($products) > 0) {
					$product = $products[0];

					$stockLevel = 0;
					// check if the product has variations
					$productVariants = $this->Product_model->getProductVariations(array('product_id' => $productId));
					if (count($productVariants) > 0) {
						// the product has variations
						for ($variantIndex = 0; $variantIndex < count($productVariants); $variantIndex++)  {
							$stockLevel += $productVariants[$variantIndex]['stock_level'];
						}

					} else {
						// the product does not have variations
						$stockLevel = $product['stock_level'];
					}
	
					$retVal[self::RESULT_FIELD_NAME] = true;
					$retVal[self::MESSAGE_FIELD_NAME] = $stockLevel > 0 ? "The product is available.": "The product is out of stock.";
					$retVal[self::EXTRA_FIELD_NAME] = array('is_sold' => $stockLevel > 0 ? false : true);

				} else {
					$retVal[self::RESULT_FIELD_NAME] = false;
					$retVal[self::MESSAGE_FIELD_NAME] = "Sorry, we were not able to find the product in our record.";
				}								
			}

			// $updatedPost = $this->Post_model->getPostInfo(array('id' => $this->input->post('id')));

			// if ($updatedPost[0]['is_sold'] == 1) {
			// 	$retVal[self::RESULT_FIELD_NAME] = true;
			// } else {
			// 	$retVal[self::RESULT_FIELD_NAME] = false;
			// }

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}

	public function set_sold() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$productId = $this->input->post('product_id');

			if (empty($productId)) {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "The product id is invalid.";

			} else {
				$products = $this->Product_model->getProduct($productId);
				if (count($products) > 0) {
					$updated = time();

					$product = $products[0];

					// check if the product has variations
					$productVariants = $this->Product_model->getProductVariations(array('product_id' => $productId));
					if (count($productVariants) > 0) {
						// the product has variations
						// set the main product as Sold out
						$this->Product_model->updateProduct(
							array('is_sold' => 1, 'updated_at' => $updated),				// stock_level does not used in product variations
							array('id' => $productId)
						);

						// update variants stocks
						for ($index = 0; $index < count($productVariants); $index++) {
							$this->Product_model->updateProductVariation(
								array('stock_level' => 0, 'updated_at' => $updated),
								array('id' => $productVariants[$index]['id'])
							);
						}

					} else {
						// the product does not have variations
						$this->Product_model->updateProduct(
							array(
								'is_sold' => 1, "stock_level" => 0, 'updated_at' => $updated
							),
							array('id' => $productId)
						);
					}

					// update the relevant posts
					$posts = $this->Post_model->getPostInfo(array('product_id' => $productId, 'post_type' => 2));
					for ($postIndex = 0; $postIndex < count($posts); $postIndex ++) {
						$this->Post_model->updatePostContent(
							array(
								'is_sold' => 1, 
								'updated_at' => $updated
							),
							array('id' => $posts[$postIndex]['id'])
						);
					}
	
					$retVal[self::RESULT_FIELD_NAME] = true;
					$retVal[self::MESSAGE_FIELD_NAME] = "Item sold.";

				} else {
					$retVal[self::RESULT_FIELD_NAME] = false;
					$retVal[self::MESSAGE_FIELD_NAME] = "Sorry, we were not able to find the product in our record.";
				}								
			}		

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}

	public function relist() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$productId = $this->input->post('product_id');

			if (empty($productId)) {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "The product is invalid.";

			} else {
				$products = $this->Product_model->getProduct($productId);
				if (count($products) > 0) {
					$updated = time();

					$product = $products[0];

					// check if the product has variations
					$productVariants = $this->Product_model->getProductVariations(array('product_id' => $productId));
					if (count($productVariants) > 0) {
						// the product has variations
						// re-list the main product
						$this->Product_model->updateProduct(
							array('is_sold' => 0, 'updated_at' => $updated),				// stock_level does not used in product variations
							array('id' => $productId)
						);

						// update variants stocks
						for ($index = 0; $index < count($productVariants); $index ++) {
							$this->Product_model->updateProductVariation(
								array('stock_level' => 1, 'updated_at' => $updated),
								array('id' => $productVariants[$index]['id'])
							);
						}

					} else {
						// the product does not have variations
						$this->Product_model->updateProduct(
							array(
								'is_sold' => 0, "stock_level" => 1, 'updated_at' => $updated		// set the default stock level as '1'
							),
							array('id' => $productId)
						);			
					}

					// update the relevant posts
					$posts = $this->Post_model->getPostInfo(array('product_id' => $productId, 'post_type' => 2));
					for ($postIndex = 0; $postIndex < count($posts); $postIndex ++) {
						$this->Post_model->updatePostContent(
							array(
								'is_sold' => 0, 
								'updated_at' => $updated
							),
							array('id' => $posts[$postIndex]['id'])
						);
					}
	
					$retVal[self::RESULT_FIELD_NAME] = true;
					$retVal[self::MESSAGE_FIELD_NAME] = "Item re-listed.";

				} else {
					$retVal[self::RESULT_FIELD_NAME] = false;
					$retVal[self::MESSAGE_FIELD_NAME] = "Sorry, we were not able to find the product in our record.";
				}								
			}
			
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}

	// public function set_sold()
	// {
	// 	$verifyTokenResult = $this->verificationToken($this->input->post('token'));
	// 	$retVal = [];

	// 	if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
	// 		if (!empty($this->input->post('post_id'))) {
	// 			$updateResult = $this->Post_model->updatePostContent(
	// 				array(
	// 					'is_sold' => 1
	// 				),
	// 				array('id' => $this->input->post('post_id'))
	// 			);
	// 		}
			
	// 		if (!empty($this->input->post('product_id'))) {
	// 			$updateResult = $this->Product_model->updateProduct(
	// 				array(
	// 					'is_sold' => 1, "stock_level" => 0
	// 				),
	// 				array('id' => $this->input->post('product_id'))
	// 			);
	// 		}
			
	// 		if (!empty($this->input->post('variation_id'))) {
	// 			$updateResult = $this->Product_model->updateProductVariation(
	// 				array(
	// 					"stock_level" => 0
	// 				),
	// 				array('id' => $this->input->post('variation_id'))
	// 			);
	// 		}
			

	// 		$retVal[self::RESULT_FIELD_NAME] = true;
	// 		$retVal[self::MESSAGE_FIELD_NAME] = "Successfully updated";
			
	// 	} else {
	// 		$retVal[self::RESULT_FIELD_NAME] = false;
	// 		$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
	// 		$retVal[self::EXTRA_FIELD_NAME] = null;
	// 	}

	// 	echo json_encode($retVal);
	// }

	// public function relist()
	// {
	// 	$verifyTokenResult = $this->verificationToken($this->input->post('token'));
	// 	$retVal = [];

	// 	if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
	// 		$updateResult = $this->Post_model->updatePostContent(
	// 			array(
	// 				'is_sold' => 0,
	// 				'created_at' => time()
	// 			),
	// 			array('id' => $this->input->post('postId'))
	// 		);

	// 		$retVal[self::RESULT_FIELD_NAME] = true;
	// 		$retVal[self::MESSAGE_FIELD_NAME] = "Successfully updated";

	// 	} else {
	// 		$retVal[self::RESULT_FIELD_NAME] = false;
	// 		$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
	// 		$retVal[self::EXTRA_FIELD_NAME] = null;
	// 	}

	// 	echo json_encode($retVal);
	// }

	public function add_image()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			for ($fIndex = 0; $fIndex < count($_FILES['post_imgs']['name']); $fIndex++) {
				$_FILES['post_img']['name'] = $_FILES['post_imgs']['name'][$fIndex];
				$_FILES['post_img']['type'] = $_FILES['post_imgs']['type'][$fIndex];
				$_FILES['post_img']['tmp_name'] = $_FILES['post_imgs']['tmp_name'][$fIndex];
				$_FILES['post_img']['error'] = $_FILES['post_imgs']['error'][$fIndex];
				$_FILES['post_img']['size'] = $_FILES['post_imgs']['size'][$fIndex];

				$uploadFileName = $this->fileUpload('post', 'post_' . time(), 'post_img');
				$this->Post_model->insertNewImage(array('post_id' => $this->input->post('post_id'), 'path' => $uploadFileName, 'created_at' => time()));
			}
			$insertedPost = $this->Post_model->getPostInfo(array('id' => $this->input->post('post_id')));
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Created Successfully";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function remove_image()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$imgPath = $this->Post_model->getPostImage(array('id' => $this->input->post('img_id')));
			if (count($imgPath) == 0) {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Image Requested";
			} else {
				unlink($imgPath[0]['path']);
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Post Image removed successfully.";
			}
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function remove_post()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$postContent = $this->Post_model->getPostInfo(array('id' => $this->input->post('id')));
			if (count($postContent) == 0) {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Request Post is not existed.";

			} else {
				for ($i = 0; $i < count($postContent[0]['post_imgs']); $i++) {
					$postImg = $postContent[0]['post_imgs'][$i];
					unlink($postImg['path']);
				}
				$this->Post_model->removePostImg(array('post_id' => $this->input->post('id')));
				$this->Post_model->removePostContent(array('id' => $this->input->post('id')));

				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Post removed successfully";
			}

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}

		echo json_encode($retVal);
	}

	public function search() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$categoryTitle = $this->input->post('category_title');
			$searchKey = $this->input->post('search_key');

			$postContent = array();
			if ($categoryTitle == 'My ATB') {				
				$postContent = $this->Post_model->getPosts(
					array(
						'posts.is_active' => 1, 
						'posts.multi_pos' => 0), 
					$searchKey
				);

			} else {
				$postContent = $this->Post_model->getPosts(
					array(
						'posts.category_title' => $categoryTitle, 
						'posts.is_active' => 1, 
						'posts.multi_pos' => 0), 
					$searchKey
				);
			}

			for ($i = 0; $i < count($postContent); $i++) {
				if (intval($postContent[$i]['is_multi']) == 1) {
					$multiPosts = $this->Post_model->getPostInfo(
						array(
							'posts.is_active' => 1, 
							'posts.multi_group' => $postContent[$i]['multi_group']
							)
					);

					foreach ($multiPosts as $elementKey => $element) {
						foreach ($element as $valueKey => $value) {
							if ($valueKey == 'id' && $value == $postContent[$i]['id']) {
								unset($multiPosts[$elementKey]);
							}
						}
					}

					$multiPosts = array_values($multiPosts);
					for ($x = 0; $x < count($multiPosts); $x++) {
						if (intval($multiPosts[$x]['poster_profile_type']) == 0) {
							//personal
							$userInfos = $this->User_model->getOnlyUser(array('id' => $postContent[$x]['user_id']));
							$multiPosts[$x]['profile_name'] = $userInfos[0]['user_name'];
							$multiPosts[$x]['profile_img'] = $userInfos[0]['pic_url'];

						} else {
							$businessInfos = $this->UserBusiness_model->getBusinessInfos(array('user_id' => $postContent[$x]['user_id']));
							$multiPosts[$x]['profile_name'] = $businessInfos[0]['business_profile_name'];
							$multiPosts[$x]['profile_img'] = $businessInfos[0]['business_logo'];
						}
                        
                        $product_id = $multiPosts[$x]['product_id']; 

                        if ($multiPosts[$x]['post_type'] == "2" && !empty($product_id)) {
							$products = $this->Product_model->getProduct($product_id);
							if (count($products) > 0) {
								$postContent[$i]['stock_level'] = $products[0]['stock_level'];
							}

                            $multiPosts[$x]["variations"] = $this->Product_model->getProductVariations(array('product_id' => $product_id));
                        }
					}

					$postContent[$i]["group_posts"] = $multiPosts;
				}
                
                $product_id = $postContent[$i]['product_id'];                        
                if ($postContent[$i]['post_type'] == "2" && !empty($product_id)) {
					$products = $this->Product_model->getProduct($product_id);
					if (count($products) > 0) {
						$postContent[$i]['stock_level'] = $products[0]['stock_level'];
					}
                    $postContent[$i]["variations"] = $this->Product_model->getProductVariations(array('product_id' => $product_id));
                }				

				$tagids = $this->Tag_model->getPostTags($postContent[$i]['id']);
				$tags = array();
				
				foreach ($tagids as $tagid) {
					$tags[] = $this->Tag_model->getTag($tagid['tag_id']);
				}
				
				$postContent[$i]["tags"] = $tags;

				if (intval($postContent[$i]['poster_profile_type']) == 0) {
                    //personal
					$userInfos = $this->User_model->getOnlyUser(array('id' => $postContent[$i]['user_id']));
					$postContent[$i]['profile_name'] = $userInfos[0]['user_name'];
					$postContent[$i]['profile_img'] = $userInfos[0]['pic_url'];

				} else {
					$businessInfos = $this->UserBusiness_model->getBusinessInfos(array('user_id' => $postContent[$i]['user_id']));
					$postContent[$i]['profile_name'] = $businessInfos[0]['business_profile_name'];
					$postContent[$i]['profile_img'] = $businessInfos[0]['business_logo'];
				}
			}

			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));
			$user_lat = $users[0]['latitude'];
			$user_lng = $users[0]['longitude'];
			$user_radius = $users[0]['post_search_region'];

			$searchResult = array();
			if (empty($user_radius) || floatval($user_radius) >= 80) {
				$searchResult = $postContent;

			} else {
				foreach ($postContent as $post) {
					$post_lat = $post['lat'];
					$post_lng = $post['lng'];

					if (is_null($post_lat) || is_null($post_lng)) {
						array_push($searchResult, $post);

					} else {
						$distance =  $this->vincentyGreatCircleDistance($user_lat, $user_lng, $post_lat, $post_lng);

						if ($distance <= floatval($user_radius)*1000) {
							array_push($searchResult, $post);
						}
					}
				}
			}			

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $searchResult;

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = array();
		}

		echo json_encode($retVal);
	}

	public function get_feed()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$categoryTitle = $this->input->post('category_title');
			$searchKey = $this->input->post('search_key');

			$postContent = $this->Post_model->getPostInfo(array('posts.category_title' => $categoryTitle, 'posts.is_active' => 1, 'posts.multi_pos' => 0), $searchKey);

			for ($i = 0; $i < count($postContent); $i++) {
				if (intval($postContent[$i]['is_multi']) == 1) {
					$multiPosts = $this->Post_model->getPostInfo(array('posts.is_active' => 1, 'posts.multi_group' => $postContent[$i]['multi_group']));

					foreach ($multiPosts as $elementKey => $element) {
						foreach ($element as $valueKey => $value) {
							if ($valueKey == 'id' && $value == $postContent[$i]['id']) {
								unset($multiPosts[$elementKey]);
							}
						}
					}
					$multiPosts = array_values($multiPosts);
					for ($x = 0; $x < count($multiPosts); $x++) {
						if (intval($multiPosts[$x]['poster_profile_type']) == 0) {
							//personal
							$userInfos = $this->User_model->getOnlyUser(array('id' => $postContent[$x]['user_id']));
							$multiPosts[$x]['profile_name'] = $userInfos[0]['user_name'];
							$multiPosts[$x]['profile_img'] = $userInfos[0]['pic_url'];
						} else {
							$businessInfos = $this->UserBusiness_model->getBusinessInfos(array('user_id' => $postContent[$x]['user_id']));
							$multiPosts[$x]['profile_name'] = $businessInfos[0]['business_profile_name'];
							$multiPosts[$x]['profile_img'] = $businessInfos[0]['business_logo'];
						}
                        
                        $product_id = $multiPosts[$x]['product_id'];                        
                        if ($multiPosts[$x]['post_type'] == "2" && !empty($product_id)) {
							$products = $this->Product_model->getProduct($product_id);
							if (count($products) > 0) {
								$postContent[$i]['stock_level'] = $products[0]['stock_level'];
							}
                            $multiPosts[$x]["variations"] = $this->Product_model->getProductVariations(array('product_id' => $product_id));
                        }
					}

					$postContent[$i]["group_posts"] = $multiPosts;
				}
                
                $product_id = $postContent[$i]['product_id'];                        
                if ($postContent[$i]['post_type'] == "2" && !empty($product_id)) {
					$products = $this->Product_model->getProduct($product_id);
					if (count($products) > 0) {
						$postContent[$i]['stock_level'] = $products[0]['stock_level'];
					}
                    $postContent[$i]["variations"] = $this->Product_model->getProductVariations(array('product_id' => $product_id));
                }				

				$tagids = $this->Tag_model->getPostTags($postContent[$i]['id']);
				$tags = array();
				
				foreach ($tagids as $tagid) {
					$tags[] = $this->Tag_model->getTag($tagid['tag_id']);
				}
				
				$postContent[$i]["tags"] = $tags;

				if (intval($postContent[$i]['poster_profile_type']) == 0) {
                    //personal
					$userInfos = $this->User_model->getOnlyUser(array('id' => $postContent[$i]['user_id']));
					$postContent[$i]['profile_name'] = $userInfos[0]['user_name'];
					$postContent[$i]['profile_img'] = $userInfos[0]['pic_url'];
				} else {
					$businessInfos = $this->UserBusiness_model->getBusinessInfos(array('user_id' => $postContent[$i]['user_id']));
					$postContent[$i]['profile_name'] = $businessInfos[0]['business_profile_name'];
					$postContent[$i]['profile_img'] = $businessInfos[0]['business_logo'];
				}
			}

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $postContent;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = array();
		}
		echo json_encode($retVal);
	}

	public function get_home_feed()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$searchKey = $this->input->post('search_key');

			$postContent = $this->Post_model->getPostInfo_InMobile($verifyTokenResult['id'], $searchKey);

			for ($i = 0; $i < count($postContent); $i++) {
				if (intval($postContent[$i]['is_multi']) == 1) {
					$multiPosts = $this->Post_model->getPostInfo(array('posts.is_active' => 1, 'posts.multi_group' => $postContent[$i]['multi_group']));

					foreach ($multiPosts as $elementKey => $element) {
						foreach ($element as $valueKey => $value) {
							if ($valueKey == 'id' && $value == $postContent[$i]['id']) {
								unset($multiPosts[$elementKey]);
							}
						}
					}
					$multiPosts = array_values($multiPosts);

					for ($x = 0; $x < count($multiPosts); $x++) {
						if (intval($multiPosts[$x]['poster_profile_type']) == 0) {
							//personal
							$userInfos = $this->User_model->getOnlyUser(array('id' => $postContent[$x]['user_id']));
							$multiPosts[$x]['profile_name'] = $userInfos[0]['user_name'];
							$multiPosts[$x]['profile_img'] = $userInfos[0]['pic_url'];
						} else {
							$businessInfos = $this->UserBusiness_model->getBusinessInfos(array('user_id' => $postContent[$x]['user_id']));
							if (count($businessInfos) > 0) {
								$multiPosts[$x]['profile_name'] = $businessInfos[0]['business_profile_name'];
								$multiPosts[$x]['profile_img'] = $businessInfos[0]['business_logo'];
							}
						}
                        
                        $product_id = $multiPosts[$x]['product_id'];                        
                        if ($multiPosts[$x]['post_type'] == "2" && !empty($product_id)) {
							$products = $this->Product_model->getProduct($product_id);
							if (count($products) > 0) {
								$postContent[$i]['stock_level'] = $products[0]['stock_level'];
							}
							
                            $multiPosts[$x]["variations"] = $this->Product_model->getProductVariations(array('product_id' => $product_id));
                        }
					}

					$postContent[$i]["group_posts"] = $multiPosts;
				}
                
                $product_id = $postContent[$i]['product_id'];                        
                if ($postContent[$i]['post_type'] == "2" && !empty($product_id)) {
					$products = $this->Product_model->getProduct($product_id);
					if (count($products) > 0) {
						$postContent[$i]['stock_level'] = $products[0]['stock_level'];
					}
					
                    $postContent[$i]["variations"] = $this->Product_model->getProductVariations(array('product_id' => $product_id));
                }
				
				$tagids = $this->Tag_model->getPostTags($postContent[$i]['id']);
				$tags = array();
				
				foreach ($tagids as $tagid) {
					$tags[] = $this->Tag_model->getTag($tagid['tag_id']);
				}
				
				$postContent[$i]["tags"] = $tags;

				if (intval($postContent[$i]['poster_profile_type']) == 0) {
                    //personal
					$userInfos = $this->User_model->getOnlyUser(array('id' => $postContent[$i]['user_id']));
					$postContent[$i]['profile_name'] = $userInfos[0]['user_name'];
					$postContent[$i]['profile_img'] = $userInfos[0]['pic_url'];
				} else {
					$businessInfos = $this->UserBusiness_model->getBusinessInfos(array('user_id' => $postContent[$i]['user_id']));
					if (count($businessInfos) > 0) {
						$postContent[$i]['profile_name'] = $businessInfos[0]['business_profile_name'];
						$postContent[$i]['profile_img'] = $businessInfos[0]['business_logo'];
					}
				}
			}

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $postContent;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = array();
		}
		echo json_encode($retVal);
	}

	public function get_post_detail()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$postContent = $this->Post_model->getPostDetail($this->input->post('post_id'), $verifyTokenResult['id']);  
            
            $product_id = $postContent['product_id'];                        
            if ($postContent['post_type'] == "2" && !is_null($product_id) && !empty($product_id)) {
                $postContent["variations"] = $this->Product_model->getProductVariations(array('product_id' => $product_id));
            }
			
			$tagids = $this->Tag_model->getPostTags($postContent['id']);
				$tags = array();
				
				foreach ($tagids as $tagid) {
					$tags[] = $this->Tag_model->getTag($tagid['tag_id']);
				}
				
				$postContent["tags"] = $tags;

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $postContent;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = array();
		}
		echo json_encode($retVal);
	}

	public function add_hide_comment()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$reportContent = $this->PostComment_model->insertNewHiddenComment(
				array(
					'comment_id' => $this->input->post('comment_id'),
					'user_id' => $verifyTokenResult['id'],
					'created_at' => time()
				)
			);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function delete_post_comment() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$this->PostComment_model->updateComment(
				array('status' => 0),
				array('id' => $this->input->post('comment_id'))
			);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}

		echo json_encode($retVal);
	}

	public function get_user_vote()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$vote = $this->PostPoll_model->getUsersVote($this->input->post('post_id'), $verifyTokenResult['id']);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $vote;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function add_vote()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$post_poll_id = 0;

			$postId = $this->input->post('post_id');
			$options = $this->PostPoll_model->getPollOptions($postId);
			foreach ($options as $option) {
				if ($option['poll_value'] == $this->input->post("poll_value")) {
					$post_poll_id = $option['id'];
				}
			}

			$this->PostPoll_model->insertNewPollVote(
				array(
					'post_poll_id' => $post_poll_id,
					'user_id' => $verifyTokenResult['id'],
					'created_at' => time(),
					'updated_at' => time()
				)
			);

			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));
			$posts = $this->Post_model->getPostInfo(array('id' => $postId));
			$posterId = $posts[0]['user_id'];

			if ($verifyTokenResult['id'] != $posterId) {
				$this->NotificationHistory_model->insertNewNotification(
					array(
						'user_id' => $posts[0]['user_id'],
						'type' => 22,
						'related_id' => $postId,
						'read_status' => 0,
						'send_status' => 0,
						'visible' => 1,
						'text' => " has responded to your poll",
						'name' => $users[0]['user_name'],
						'profile_image' => $users[0]['pic_url'],
						'updated_at' => time(),
						'created_at' => time()
					)
				);
			}

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}

		echo json_encode($retVal);
	}

	public function add_hide_reply()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$reportContent = $this->PostComment_model->insertNewHiddenReply(
				array(
					'reply_id' => $this->input->post('reply_id'),
					'user_id' => $verifyTokenResult['id'],
					'created_at' => time()
				)
			);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function delete_post_reply() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));

		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$reportContent = $this->PostComment_model->updateReply(
				array('status' => 0),
				array('id' => $this->input->post('reply_id'))
			);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function add_report_comment()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$reportContent = $this->PostReport_model->insertNewReport(
				array(
					'comment_id' => $this->input->post('comment_id'),
					'reporter_user_id' => $verifyTokenResult['id'],
					'reason' => $this->input->post('reason'),
					'content' => $this->input->post('content'),
					'is_active' => 0,
					'created_at' => time()
				)
			);

			$this->Post_model->updatePostContent(array('is_active' => 0), array('id' => $this->input->post('post_id')));

			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));

			$content = '
						<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;">
							<span style="color: #808080; font-size: 18px;">Thank you for contacting the ATB admin team. Someone will get back to you as soon as possible.</span>
						</p>
						<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;">
							<span style="color: #808080; font-size: 18px;"><b></b></span>							
						</p>';

			$subject = 'ATB Admin Contacted';

			$this->User_model->sendUserEmail($users[0]["user_email"], $subject, $content);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}

		echo json_encode($retVal);
	}

	public function add_report_post()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$reportContent = $this->PostReport_model->insertNewReport(
				array(
					'post_id' => $this->input->post('post_id'),
					'user_id' => $this->input->post('user_id'),
					'reporter_user_id' => $verifyTokenResult['id'],
					'reason' => $this->input->post('reason'),
					'content' => $this->input->post('content'),
					'is_active' => 0,
					'created_at' => time()
				)
			);
			$this->Post_model->updatePostContent(array('is_active' => 0), array('id' => $this->input->post('post_id')));

			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));

			$content = '
					<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;">
						<span style="color: #808080; font-size: 18px;">Thank you for contacting the ATB admin team. Someone will get back to you as soon as possible.</span>
					</p>
					<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;">
						<span style="color: #808080; font-size: 18px;"><b></b></span>
					</p>';

			$subject = 'ATB Admin Contacted';

			$this->User_model->sendUserEmail($users[0]["user_email"], $subject, $content);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}

		echo json_encode($retVal);
	}

	public function add_report() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$reporterUserId = $verifyTokenResult['id'];

			$reportContent = $this->PostReport_model->insertNewReport(
				array(
					'user_id' => $this->input->post('user_id'),
					'product_id' => $this->input->post('product_id'), 
					'service_id' => $this->input->post('service_id'),
					'post_id' => $this->input->post('post_id'),
					'comment_id' => $this->input->post('comment_id'),
					'reporter_user_id' => $reporterUserId,
					'reason' => $this->input->post('reason'),
					'content' => $this->input->post('content'),
					'is_active' => 0,
					'created_at' => time()
				)
			);

			if (!empty($this->input->post('post_id'))) {
				$this->Post_model->updatePostContent(array('is_active' => 0), array('id' => $this->input->post('post_id')));

				$users = $this->User_model->getOnlyUser(array('id' => $reporterUserId));

				$content = '
							<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;">
								<span style="color: #808080; font-size: 18px;">Thank you for contacting the ATB admin team. Someone will get back to you as soon as possible.</span>
							</p>
							<p style="font-size: 18px; line-height: 1.2; text-align: center; mso-line-height-alt: 22px; margin: 0;">
								<span style="color: #808080; font-size: 18px;"><b></b></span>							
							</p>';

				$subject = 'ATB Admin Contacted';

				$this->User_model->sendUserEmail($users[0]["user_email"], $subject, $content);

				$postId = $this->input->post('post_id');
				$posts = $this->Post_model->getPostInfo(array('id' => $postId));

				$this->NotificationHistory_model->insertNewNotification(
					array(
						'user_id' => $posts[0]['user_id'],
						'type' => 23,
						'related_id' => $posts[0]['poster_profile_type'],
						'read_status' => 0,
						'send_status' => 0,
						'visible' => 1,
						'text' => "Your post have been reported and temporarily removed pending admin review",
						'name' => "",
						'profile_image' => "",
						'updated_at' => time(),
						'created_at' => time()
					)
				);
			}	

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}

		echo json_encode($retVal);
	}

	public function add_notification_message()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$this->NotificationHistory_model->insertNewNotification(
				array(
					'user_id' => $this->input->post('user_id'),
					'type' => 5,
					'related_id' => $verifyTokenResult['id'],
					'read_status' => 0,
                    'send_status' => 0,
					'visible' => 0,
					'text' => $this->input->post('message'),
					'name' => $this->input->post('name'),
					'profile_image' => $this->input->post('profile_image'),
					'updated_at' => time(),
					'created_at' => time()
				)
			);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}
		echo json_encode($retVal);
	}

	public function get_post_comments()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$postID = $this->input->post('post_id');

			$addedComments = $this->PostComment_model->getComments(
				array(
					'post_id' => $postID, 
					'status' => 1 // only active comments
				)
			);

			for ($i = 0; $i < count($addedComments); $i++) {
				$liked = $this->PostLike_model->userLikedComment($verifyTokenResult['id'], $addedComments[$i]['id']);
				$addedComments[$i]['liked'] = $liked;
				$hidden = $this->PostComment_model->userHiddenComment($verifyTokenResult['id'], $addedComments[$i]['id']);
				$addedComments[$i]['hidden'] = $hidden;
				for ($x = 0; $x < count($addedComments[$i]['replies']); $x++) {
					$liked = $this->PostLike_model->userLikedReply($verifyTokenResult['id'], $addedComments[$i]['replies'][$x]['id']);
					$addedComments[$i]['replies'][$x]['liked'] = $liked;

					$hidden = $this->PostComment_model->userHiddenReply($verifyTokenResult['id'], $addedComments[$i]['replies'][$x]['id']);
					$addedComments[$i]['replies'][$x]['hidden'] = $hidden;
				}
			}

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $addedComments;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}
		echo json_encode($retVal);
	}

	//DEPRECATED
	public function reply_comment_post()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$reportContent = $this->PostComment_model->insertNewComment(
				array(
					'post_id' => $this->input->post('post_id'),
					'commenter_user_id' => $verifyTokenResult['id'],
					'comment' => $this->input->post('comment'),
					'level' => 1,
					'parent_user_name' => $this->input->post('parent_user_name'),
					'parent_user_id' => $this->input->post('parent_user_id'),
					'parent_comment_id' => $this->input->post('parent_comment_id'),
					'created_at' => time()
				)
			);

			$this->NotificationHistory_model->insertNewNotification(
				array(
					'user_id' => $verifyTokenResult['id'],
					'type' => 2,
					'related_id' => $this->input->post('post_id'),
					'read_status' => 0,
                    'send_status' => 0,
					'visible' => 1,
					'text' => $this->input->post('comment'),
					'name' => "",
					'profile_image' => "",
					'updated_at' => time(),
					'created_at' => time()
				)
			);

			$addedComment = $this->PostComment_model->getComments(array('id' => $reportContent));

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $addedComment[0];
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}
		echo json_encode($retVal);
	}

	public function add_comment_reply()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$replyType = "text";
			$uploadedData = "";

			if (!empty($_FILES)) {
				for ($fIndex = 0; $fIndex < count($_FILES['reply_imgs']['name']); $fIndex++) {
					if (strlen($_FILES['reply_imgs']['name'][$fIndex]) > 0) {
						$_FILES['post_img']['name'] = $_FILES['reply_imgs']['name'][$fIndex];
						$_FILES['post_img']['type'] = $_FILES['reply_imgs']['type'][$fIndex];
						$_FILES['post_img']['tmp_name'] = $_FILES['reply_imgs']['tmp_name'][$fIndex];
						$_FILES['post_img']['error'] = $_FILES['reply_imgs']['error'][$fIndex];
						$_FILES['post_img']['size'] = $_FILES['reply_imgs']['size'][$fIndex];

						$uploadFileName = $this->fileUpload('post', 'comment_' . time(), 'post_img');
						$uploadedData .= $uploadFileName . ",";

						$replyType = "image";
					}
				}
			}

			$uploadedData = rtrim($uploadedData, ',');

			$reply = $this->input->post('reply');

			$isBusiness = $this->input->post('is_business') ?? '0';

			$reportContent = $this->PostComment_model->insertNewReply(
				array(
					'comment_id' => $this->input->post('comment_id'),
					'reply_user_id' => $verifyTokenResult['id'],
					'reply' => $reply,
					'reply_type' => $replyType,
					'is_business' => $isBusiness,
					'data' => $uploadedData,
					'created_at' => time()
				)
			);

			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));
			$comments = $this->PostComment_model->getComments(array('id' => $this->input->post('comment_id')));

			$posts = $posts =  $this->Post_model->getPostInfo(array('id' => $comments[0]['post_id']));

			$notificationUserName = $isBusiness == '1' ? $users[0]['business_info']['business_name'] : $users[0]['user_name'];
			$notificationProfileImage = $isBusiness == '1' ? $users[0]['business_info']['business_logo'] : $users[0]['pic_url'];

			if (intval($posts[0]['user_id']) != intval($verifyTokenResult['id'])) {
				$this->NotificationHistory_model->insertNewNotification(
					array(
						'user_id' => $comments[0]['commenter_user_id'],
						'type' => 2,
						'related_id' => $comments[0]['post_id'],
						'read_status' => 0,
						'send_status' => 0,
						'visible' => 1,
						'text' => " has replied to your comment",
						'name' => $notificationUserName,
						'profile_image' => $notificationProfileImage,
						'updated_at' => time(),
						'created_at' => time()
					)
				);
			}			

			$addedReply = $this->PostComment_model->getReplies(array('id' => $reportContent));

			if (!empty($reply)) {
				$replies = json_decode($reply, true);

				for ($index = 0; $index < count($replies); $index++) {
					if (!empty($replies[$index]['user_id'])) {
						$this->NotificationHistory_model->insertNewNotification(
							array(
								'user_id' => $replies[$index]['user_id'],
								'type' => 30,
								'related_id' => $comments[0]['post_id'],
								'read_status' => 0,
								'send_status' => 0,
								'visible' => 1,
								'text' => " has tagged you",
								'name' => $notificationUserName,
								'profile_image' => $notificationProfileImage,
								'updated_at' => time(),
								'created_at' => time()
							)
						);
					}
				}
			}

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $addedReply[0];
			
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}
		echo json_encode($retVal);
	}

	public function add_comment_post()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$replyType = "text";
			$uploadedData = "";

			if (!empty($_FILES)) {
				for ($fIndex = 0; $fIndex < count($_FILES['comment_imgs']['name']); $fIndex++) {
					if (strlen($_FILES['comment_imgs']['name'][$fIndex]) > 0) {
						$_FILES['post_img']['name'] = $_FILES['comment_imgs']['name'][$fIndex];
						$_FILES['post_img']['type'] = $_FILES['comment_imgs']['type'][$fIndex];
						$_FILES['post_img']['tmp_name'] = $_FILES['comment_imgs']['tmp_name'][$fIndex];
						$_FILES['post_img']['error'] = $_FILES['comment_imgs']['error'][$fIndex];
						$_FILES['post_img']['size'] = $_FILES['comment_imgs']['size'][$fIndex];

						$uploadFileName = $this->fileUpload('post', 'comment_' . time(), 'post_img');
						$uploadedData .= $uploadFileName . ",";
						$replyType = "image";
					}
				}
			}
			$uploadedData = rtrim($uploadedData, ',');
			$comment = $this->input->post('comment');

			$isBusiness = $this->input->post('is_business') ?? '0';

			$reportContent = $this->PostComment_model->insertNewComment(
				array(
					'post_id' => $this->input->post('post_id'),
					'commenter_user_id' => $verifyTokenResult['id'],
					'comment' => $comment,
					'data' => $uploadedData,
					'comment_type' => $replyType,
					'is_business' => $isBusiness,
					'created_at' => time()
				)
			);

			$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));

			$notificationUserName = $isBusiness == '1' ? $users[0]['business_info']['business_name'] : $users[0]['user_name'];
			$notificationProfileImage = $isBusiness == '1' ? $users[0]['business_info']['business_logo'] : $users[0]['pic_url'];

			// $this->input->post('user_id) : poster user id
			// Do not send notifications on their own post
			if (intval($this->input->post('user_id')) != intval($verifyTokenResult['id'])) {
				$this->NotificationHistory_model->insertNewNotification(
					array(
						'user_id' => $this->input->post('user_id'),
						'type' => 1,
						'related_id' => $this->input->post('post_id'),
						'read_status' => 0,
                        'send_status' => 0,
						'visible' => 1,
						'text' => " has commented on your post",
						'name' => $notificationUserName,
						'profile_image' => $notificationProfileImage,
						'updated_at' => time(),
						'created_at' => time()
					)
				);
			}

			if (!empty($comment)) {
				$comments = json_decode($comment, true);

				for ($index = 0; $index < count($comments); $index++) {
					if (!empty($comments[$index]['user_id'])) {
						$this->NotificationHistory_model->insertNewNotification(
							array(
								'user_id' => $comments[$index]['user_id'],
								'type' => 30,
								'related_id' => $this->input->post('post_id'),
								'read_status' => 0,
								'send_status' => 0,
								'visible' => 1,
								'text' => " has tagged you",
								'name' => $notificationUserName,
								'profile_image' => $notificationProfileImage,
								'updated_at' => time(),
								'created_at' => time()
							)
						);
					}
				}
			}			

			$addedComment = $this->PostComment_model->getComments(array('id' => $reportContent));

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $addedComment[0];
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}
		echo json_encode($retVal);
	}

	public function add_like_post()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$postId = $this->input->post('post_id');

			$existLike = $this->PostLike_model->getLikes(array('post_id' => $postId, 'follower_user_id' => $verifyTokenResult['id']));
			if (count($existLike) > 0) {
				$this->PostLike_model->deleteLike(array('post_id' => $postId, 'follower_user_id' => $verifyTokenResult['id']));
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Post like removed";

			} else {
				$reportContent = $this->PostLike_model->insertNewLike(
					array(
						'post_id' => $postId,
						'follower_user_id' => $verifyTokenResult['id'],
						'created_at' => time()
					)
				);

				$users = $this->User_model->getOnlyUser(array('id' => $verifyTokenResult['id']));
				$posts =  $this->Post_model->getPostInfo(array('id' => $postId));

				if (count($posts) > 0 && count($users) > 0) {
					$this->NotificationHistory_model->insertNewNotification(
						array(
							'user_id' => $posts[0]['user_id'],
							'type' => 3,
							'related_id' => $this->input->post('post_id'),
							'read_status' => 0,
							'send_status' => 0,
							'visible' => 1,
							'text' => " has liked your post",
							'name' => $users[0]['user_name'],
							'profile_image' => $users[0]['pic_url'],
							'updated_at' => time(),
							'created_at' => time()
						)
					);
				}

				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			}
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function add_like_comment()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$postId = $this->input->post('comment_id');

			$existLike = $this->PostLike_model->getLikes(array('comment_id' => $postId, 'follower_user_id' => $verifyTokenResult['id']));
			if (count($existLike) > 0) {
				$this->PostLike_model->deleteLike(array('comment_id' => $postId, 'follower_user_id' => $verifyTokenResult['id']));
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Comment like removed";
			} else {
				$reportContent = $this->PostLike_model->insertNewLike(
					array(
						'comment_id' => $postId,
						'follower_user_id' => $verifyTokenResult['id'],
						'created_at' => time()
					)
				);
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			}
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function add_like_reply()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$postId = $this->input->post('reply_id');

			$existLike = $this->PostLike_model->getLikes(array('reply_id' => $postId, 'follower_user_id' => $verifyTokenResult['id']));
			if (count($existLike) > 0) {
				$this->PostLike_model->deleteLike(array('reply_id' => $postId, 'follower_user_id' => $verifyTokenResult['id']));
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Reply like removed";
			} else {
				$reportContent = $this->PostLike_model->insertNewLike(
					array(
						'reply_id' => $postId,
						'follower_user_id' => $verifyTokenResult['id'],
						'created_at' => time()
					)
				);
				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			}
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function delete_post()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$postId = $this->input->post('post_id');

			$setArray = array(
				'is_active' => 2,
				'status_reason' => "User deleted",
				'updated_at' => time(),
			);

			$whereArray = array('id' => $postId);

			$this->Post_model->updatePostContent($setArray, $whereArray);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}

		echo json_encode($retVal);
	}

	public function cart_add_item()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$productId = $this->input->post('product_id');
			$variantId = $this->input->post('variant_id');
		    
		    //$productQty = $this->Cart_model->insertNewCartProduct($verifyTokenResult['id'], $productId);
			$res = $this->Cart_model->insertNewCartProduct($verifyTokenResult['id'], $productId, $variantId);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "";
			$retVal[self::EXTRA_FIELD_NAME] = $res;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function cart_delete_item()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$productId = $this->input->post('product_id');
			$variantId = $this->input->post('variant_id');
			$productQty = $this->input->post('quantity');
			$productDelete = $this->Cart_model->deleteCartProduct($verifyTokenResult['id'], $productId, $variantId, $productQty);

			$retVal[self::RESULT_FIELD_NAME] = $productDelete;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function cart_delete_items()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$productId = $this->input->post('product_id');
			$variantId = $this->input->post('variant_id');

			$productDelete = $this->Cart_model->deleteCartProducts($verifyTokenResult['id'], $productId, $variantId);

			$retVal[self::RESULT_FIELD_NAME] = $productDelete;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}
		echo json_encode($retVal);
	}

	public function get_cart_products()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			$cart = $this->Cart_model->getUsersCart($verifyTokenResult['id']);

			foreach ($cart as &$product) {
				$postContent = $this->Post_model->getPostDetail($product['product_id']);
				$product["product"] = $postContent;
				
				if ($product['variant_id'] != 0 ) {
					
					$variantContent = $this->Product_model->getProductVariation($product['variant_id']);
					$product["variant"] = $variantContent;
				}
			}

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = "Success";
			$retVal[self::EXTRA_FIELD_NAME] = $cart;
		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
			$retVal[self::EXTRA_FIELD_NAME] = array();
		}
		echo json_encode($retVal);
	}

	public function sendFiles() {
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		
		$retVal = array();
		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			if (!empty($_FILES)) {
				$uploadedUrls = array();
				for ($fIndex = 0; $fIndex < count($_FILES['files']['name']); $fIndex++) {
					$_FILES['chat']['name'] = $_FILES['files']['name'][$fIndex];
					$_FILES['chat']['type'] = $_FILES['files']['type'][$fIndex];
					$_FILES['chat']['tmp_name'] = $_FILES['files']['tmp_name'][$fIndex];
					$_FILES['chat']['error'] = $_FILES['files']['error'][$fIndex];
					$_FILES['chat']['size'] = $_FILES['files']['size'][$fIndex];		

					$uploadFileName = $this->fileUpload('chat', 'chat_' . time(), 'chat');
					if (!empty($uploadFileName)) {
						array_push($uploadedUrls, $uploadFileName);
					}
				}

				$retVal[self::RESULT_FIELD_NAME] = true;
				$retVal[self::MESSAGE_FIELD_NAME] = "Uploaded Successfully";
				$retVal[self::EXTRA_FIELD_NAME] = $uploadedUrls;

			} else {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Files";
			}		

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credentials";
		}

		echo json_encode($retVal);
	}
}

