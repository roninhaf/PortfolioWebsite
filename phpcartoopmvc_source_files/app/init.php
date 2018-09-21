<?php

/*
	INIT
	Basic configuration settings
*/

// connect to database
$server = 'localhost';
$user = 'hafidfe_root';
$pass = 'Imsoluckytohavemy2daughters';
$db = 'hafidfe_my_shop';
$Database = new mysqli($server, $user, $pass, $db);

// error reporting
mysqli_report(MYSQLI_REPORT_ERROR);
ini_set('display_errors', 1);

// set up constants
define('SITE_NAME', 'My Online Store');
define('SITE_PATH', 'https://hafidfeghouli.com/phpcartoopmvc_source_files/');
define('IMAGE_PATH', 'https://hafidfeghouli.com/phpcartoopmvc_source_files/resources/images/');

define('SHOP_TAX', '0.0875');

define('PAYPAL_MODE', 'sandbox'); // either sandbox or live
define('PAYPAL_CURRENCY', 'USD'); 
define('PAYPAL_DEVID', 'AdvVwzKq1BBqQhxmdWNZomgj-j2QEie_QtIZLuTY7YzhzZ2gbYwwEmGKh16672ZExbk-HpaiA6AZ8-PS'); 
define('PAYPAL_DEVSECRET', 'EJGkoxuIeSF-Vnyb3JBx_wDus-BWJhncUPC4y4VJp3XwtoYgxTOHkPYKq3W05ZklFeqozr1z_7vsmZwL'); 
define('PAYPAL_LIVEID', ''); 
define('PAYPAL_LIVESECRET', ''); 

// include objects
include('app/models/m_template.php');
include('app/models/m_categories.php');
include('app/models/m_products.php');
include('app/models/m_cart.php');

// create objects
$Template = new Template();
$Categories = new Categories();
$Products = new Products();
$Cart = new Cart();


session_start();


// global
$Template->set_data('cart_total_items', $Cart->get_total_items());
$Template->set_data('cart_total_cost', $Cart->get_total_cost());


