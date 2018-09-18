<?php

include('app/init.php');
$Template->set_data('page_class', 'home');

if (isset($_GET['id']) && is_numeric($_GET['id']))
{
	// get products from specific category
	
	$category = $Categories->get_categories($_GET['id']);
	
	// check if valid
	if ( ! empty($category))
	{
		// get category nav
		$category_nav = $Categories->create_category_nav($category['name']);
		$Template->set_data('page_nav', $category_nav);
		
		// get all products from that category
		$cat_products = $Products->create_product_table(4, $_GET['id']);
		
		if ( ! empty($cat_products))
		{
			$Template->set_data('products', $cat_products);
		}	
		else 
		{
			$Template->set_data('products', '<li>No products exist in this category!</li>');	
		}
		$Template->load('app/views/v_public_home.php', $category['name']);
	}
	else 
	{
		// if category isn't valid
		$Template->redirect(SITE_PATH);	
	}
}
else 
{
	// get all products from all categories	
	
	// get category nav
	$category_nav = $Categories->create_category_nav('home');
	$Template->set_data('page_nav', $category_nav);
	
	// get products
	$products = $Products->create_product_table();
	$Template->set_data('products', $products);
	
	$Template->load('app/views/v_public_home.php', 'Welcome!');
}