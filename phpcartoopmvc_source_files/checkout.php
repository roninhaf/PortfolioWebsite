<?php 

include('app/init.php');

if (isset($_POST))
{
	// create Payment Object
	include('app/models/m_payments.php');
	$Payments = new Payments();
	
	// get item data
	$items = $Cart->get();
	
	// get details
	$details['subtotal'] = $Cart->get_total_cost();
	$details['shipping'] = 0;
	foreach ($items as $item)
	{
		$details['shipping'] += $Cart->get_shipping_cost($item['price']);
	}
	$details['shipping'] = number_format($details['shipping'], 2);
	$details['tax'] = number_format($details['subtotal'] * SHOP_TAX, 2);
	$details['total'] = number_format(
		$details['subtotal'] + $details['shipping'] + $details['tax'], 2);
		
	// send to PayPal
	$error = $Payments->create_payment($items, $details);
	if ($error != NULL)
	{
		$Template->set_alert($error, 'error');
		$Template->redirect('cart.php');
	}
}
else 
{
	$Template->redirect('cart.php');
}