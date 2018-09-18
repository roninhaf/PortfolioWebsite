<?php

	include('app/init.php');
	$Template->set_data('page_class', 'product');
	
	if (isset($_GET['id']) && is_numeric($_GET['id']))
	{
		// show product
		
		$product = $Products->get($_GET['id']);
		
		if ( ! empty($product))
		{
			// pass product data to view
			$Template->set_data('prod_id', $_GET['id']);
			$Template->set_data('prod_name', $product['name']);
			$Template->set_data('prod_description', $product['description']);
			$Template->set_data('prod_price', $product['price']);
			$Template->set_data('prod_image', IMAGE_PATH . $product['image']);
		
			// create category nav
			$category_nav = $Categories->create_category_nav($product['category_name']);
			$Template->set_data('page_nav', $category_nav);
			
			// display view
			$Template->load('app/views/v_public_product.php', $product['name']);
		}
		else 
		{
			// error
			$Template->redirect(SITE_PATH);
		}
	}
	else
	{
		// error
		$Template->redirect(SITE_PATH);
	}