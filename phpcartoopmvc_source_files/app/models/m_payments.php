<?php

/*
	Payments Class
	Handle all tasks related to payments
*/

require('app/vendor/autoload.php');

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;

class Payments
{
	private $api_context;

	function __construct() {
		$this->api_context = $this->get_api_context();
	}
	
	
	/*
		Getters and Setters
	*/
	public function get_api_context()
	{
		if (PAYPAL_MODE == 'sandbox')
		{
			$apiContext = new ApiContext(
				new OAuthTokenCredential(
					PAYPAL_DEVID,
					PAYPAL_DEVSECRET
				)
			);
		}
		else 
		{
			$apiContext = new ApiContext(
				new OAuthTokenCredential(
					PAYPAL_LIVEID,
					PAYPAL_LIVESECRET
				)
			);
		}

		$apiContext->setConfig(
			array(
				'mode' => PAYPAL_MODE,
				'http.ConnectionTimeOut' => 30,
				'log.LogEnabled' => true,
				'log.FileName' => 'app/PayPal.log',
				'log.LogLevel' => 'FINE'
			)
		);

		return $apiContext;
	}
		
		
	/**
	 * Creates PayPal payment: step 2/3
	 *
	 * @access	public
	 * @param	
	 * @return	error string
	 */	
	 public function create_payment($items_array, $details_array)
	 {
		$payer = new Payer();
		$payer->setPaymentMethod("paypal");
		
		// set items
		$i = 0;
		foreach ($items_array as $item)
		{
			$items[$i] = new Item();
			$items[$i]
				->setName($item['name'])
				->setCurrency(PAYPAL_CURRENCY)
				->setQuantity($item['quantity'])
				->setPrice($item['price']);
			$i++;
		}
		$itemList = new ItemList();
		$itemList->setItems($items);
		
		// set details
		$details = new Details();
		$details
			->setShipping($details_array['shipping'])
			->setTax($details_array['tax'])
			->setSubtotal($details_array['subtotal']);
		
		// set amount
		$amount = new Amount();
		$amount
			->setCurrency(PAYPAL_CURRENCY)
			->setTotal($details_array['total'])
			->setDetails($details);
		
		// create transaction 
		$transaction = new Transaction();
		$transaction
			->setAmount($amount)
			->setItemList($itemList)
			->setDescription("");
		
		// create url
		$redirectUrls = new RedirectUrls();
		$redirectUrls
			->setReturnUrl(SITE_PATH . "success.php")
			->setCancelUrl(SITE_PATH . "cart.php");
		
		// create payment
		$payment = new Payment();
		$payment->setIntent("sale")
			->setPayer($payer)
			->setRedirectUrls($redirectUrls)
			->setTransactions(array($transaction));
		
		try {
			$payment->create($this->api_context);
		} catch (PayPal\Exception\PPConnectionException $ex) {
			return $ex->getMessage();
		}
		
		// get redirect url
		foreach($payment->getLinks() as $link) {
			if($link->getRel() == 'approval_url') {
				$redirectUrl = $link->getHref();
				break;
			}
		}
		
		// redirect
		$_SESSION['payment_id'] = $payment->getId();
		if(isset($redirectUrl)) {
			header("Location: $redirectUrl");
			exit;
		}
				 
	 }
	 
	 
	 /**
	  * Executes PayPal payment: step 4/5
	  *
	  * @access	public
	  * @param	string, string
	  * @return	result object
	  */	
	  public function execute_payment($payer_id, $payment_id)
	  {
		$payment = Payment::get($payment_id, $this->api_context);

		$execution = new PaymentExecution();
		$execution->setPayerId($payer_id);
		$result = $payment->execute($execution, $this->api_context);
		
		return $result;
	  }
}