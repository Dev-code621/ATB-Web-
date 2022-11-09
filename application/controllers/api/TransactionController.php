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
			$transactions = $this->UserTransaction_model->getTransactions(array('user_id' => $tokenVerifyResult['id']));

			$retVal[self::RESULT_FIELD_NAME] = true;
			$retVal[self::MESSAGE_FIELD_NAME] = $transactions;

		} else {
			$retVal[self::RESULT_FIELD_NAME] = false;
			$retVal[self::MESSAGE_FIELD_NAME] = "Invalid Credential.";
		}

		echo json_encode($retVal);
	}
}
