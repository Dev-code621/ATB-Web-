<?php

class TransactionController extends MY_Controller
{
	public function get_purchases()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {			
			/** using braintree */
			// $purchases = $this->UserBraintreeTransaction_model->getPurchasedProductHistory($verifyTokenResult['id']);
			
			/** using stripe */
			$purchases = $this->UserTransaction_model->getPurchases($verifyTokenResult['id']);
			
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $purchases;

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}
	
	public function get_items_sold()
	{
		$verifyTokenResult = $this->verificationToken($this->input->post('token'));
		$retVal = [];

		if ($verifyTokenResult[self::RESULT_FIELD_NAME]) {
			/** using braintree */		
			// $purchases = $this->UserBraintreeTransaction_model->getSoldProductHistory($verifyTokenResult['id'], $this->input->post('is_business'));
			$purchases = $this->UserTransaction_model->getSoldItems($verifyTokenResult['id'], $this->input->post('is_business'));
			
			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $purchases;

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			$retVal[self::EXTRA_FIELD_NAME] = null;
		}

		echo json_encode($retVal);
	}

	public function get_transactions() {
		$tokenVerifyResult = $this->verificationToken($this->input->post('token'));
		$retVal = array();

		if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
			$transactions = $this->UserTransaction_model->getTransactions($tokenVerifyResult['id']);

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $transactions;

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}

	public function view_express() {
		$token = $this->input->post('token');

		$retVal = array();

		if (!is_null($token) && !empty($token)) {
			$tokenVerifyResult = $this->verificationToken($token);

			if ($tokenVerifyResult[self::RESULT_FIELD_NAME]) {
				$users = $this->User_model->getOnlyUser(array('id' => $tokenVerifyResult['id']));
	
				if (count($users)) {
					$user = $users[0];

					$account = $user['stripe_connect_account'];
					if (!is_null($account) && !empty($account)) {
						require_once('application/libraries/stripe-php/init.php');
						\Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));
	
						try {
							$loginLink = \Stripe\Account::createLoginLink($account);
							
							$retVal[self::RESULT_FIELD_NAME] = true;
							$retVal[self::MESSAGE_FIELD_NAME] = "Login";
							$retVal[self::EXTRA_FIELD_NAME] = $loginLink->url;
	
							echo json_encode($retVal);
							return;
	
						} catch (Exception $ex) {
							// $retVal[self::RESULT_FIELD_NAME] = false;
							// $retVal[self::MESSAGE_FIELD_NAME] = $ex->getMessage();
						}
	
						// continue execution
						// continue to create an account link
						try {
							// set this on when SSL issue is fixed
							$baseUrl = base_url();
				
							// test
							$baseUrl = "https://test.myatb.co.uk/";
							$accountLink = \Stripe\AccountLink::create([
								'account' => $account, 
								'refresh_url' => $baseUrl.'payment/onboard?action=re-auth&token='.$token,
								'return_url' => $baseUrl.'payment/onboard?action=return',
								'type' => 'account_onboarding'
							]);

							$retVal[self::RESULT_FIELD_NAME] = true;
							$retVal[self::MESSAGE_FIELD_NAME] = "Account";
							$retVal[self::EXTRA_FIELD_NAME] = $accountLink->url;

							echo json_encode($retVal);
							return;
				
						} catch (Exception $ex) {
							$retVal[self::RESULT_FIELD_NAME] = false;
							$retVal[self::MESSAGE_FIELD_NAME] = $ex->getMessage();
						}
	
					} else {
						$retVal[self::RESULT_FIELD_NAME] = false;
						$retVal[self::MESSAGE_FIELD_NAME] = "The public access has been denied.";
					}
	
				} else {
					$retVal[self::RESULT_FIELD_NAME] = false;
					$retVal[self::MESSAGE_FIELD_NAME] = "We were not able to find you in our user record.";
				}
	
			} else {
				$retVal[self::RESULT_FIELD_NAME] = false;
				$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
			}

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Authorization is required.";
		}

		echo json_encode($retVal);
	}
}
