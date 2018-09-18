<?php

/*
	Cart Class
	Handle all tasks related to showing or modifying the number of items in cart
	
	The cart keeps track of user selected items using a session variable, $_SESSION['cart']. 
	The session variable holds an array that contains the ids and the number selected of 
	the products in the cart.
	
	$_SESSION['cart']['product_id'] = num of specific item in cart
*/

class Cart
{
	function __construct() {}
	
	
	/*
		Getters and Setters
	*/
	
	/**
	 * Return an array of all product info for items in the cart
	 *
	 * @access	public
	 * @param	
	 * @return	array, null
	 */
	public function get()
	{
		if (isset($_SESSION['cart']))
		{
			// get all product ids of items in cart
			$ids = $this->get_ids();
			
			// use list of ids to get product info from database
			global $Products;
			return $Products->get($ids);
		}
		return NULL;
	}
	
	/**
	 * Return an array of all product ids in cart
	 *
	 * @access	public
	 * @param	
	 * @return	array, null
	 */
	public function get_ids()
	{
		if (isset($_SESSION['cart']))
		{
			return array_keys($_SESSION['cart']);
		}
		return NULL;
	}
	
	/**
	 * Adds item to the cart
	 *
	 * @access	public
	 * @param	int, int
	 * @return	null
	 */
	 public function add($id, $num = 1)
	 {
	 	// setup or retrieve cart
	 	$cart = array();
	 	if (isset($_SESSION['cart']))
	 	{
	 		$cart = $_SESSION['cart'];
	 	}
	 	
	 	// check to see if item is already in cart
	 	if (isset($cart[$id]))
	 	{
	 		// if item is in cart
	 		$cart[$id] = $cart[$id] + $num;
	 	}
	 	else 
	 	{
	 		// if item is not in cart	
	 		$cart[$id] = $num;
	 	}
	 	$_SESSION['cart'] = $cart;
	 }
	
	/**
	 * Update the quantity of a specific item in the cart
	 *
	 * @access	public
	 * @param	int, int
	 * @return	NULL
	 */ 
	public function update($id, $num) 
	{
		if ($num == 0)
	    {
	        unset($_SESSION['cart'][$id]);
	        if (empty($_SESSION['cart'])) { unset($_SESSION['cart']); } //add
	    }
	    else 
	    {
	        $_SESSION['cart'][$id] = $num; 
	    }
	}
	
	/**
	 * Empties all items from cart
	 *
	 * @access	public
	 * @param	
	 * @return	null
	 */
	public function empty_cart()
	{
		unset($_SESSION['cart']);
	}
	
	/**
	 * Retrun total number of all items in cart
	 *
	 * @access	public
	 * @param	
	 * @return	int
	 */
	public function get_total_items()
	{
		$num = 0;
		
		if (isset($_SESSION['cart']))
		{
			foreach($_SESSION['cart'] as $item)
			{
				$num = $num + $item;
			}
		}
		return $num;
	}
	
	/**
	 * Return total cost of all items in cart
	 *
	 * @access	public
	 * @param	
	 * @return	int
	 */
	public function get_total_cost()
	{
		$num = '0.00';
		if (isset($_SESSION['cart']))
		{
			// if items to display
			
			// get product ids
			$ids = $this->get_ids();
			
			// get product prices
			global $Products;
			$prices = $Products->get_prices($ids);
			
			// loop through, adding the cost of each item x the number of the item in the cart to $num each time
			if ($prices != NULL)
			{
				foreach($prices as $price)
				{
					$num += doubleval($price['price'] * $_SESSION['cart'][$price['id']]);
				}
			}
		}
		return $num;
	}
	
	/**
	 * Return shipping cost based on cost of items
	 *
	 * @access	public
	 * @param	double
	 * @return	double
	 */
	public function get_shipping_cost($total)
	{
		if ($total > 200)
		{
			return 40.0;
		}
		else if ($total > 50)
		{
			return 15.0;
		}
		else if ($total > 10)
		{
			return 4.0;
		}
		else 
		{
			return 2.0;	
		}
	}
	
	
	
	/*
		Create page parts
	*/
	/**
	 * Return a string, containing list items for each product in cart
	 *
	 * @access	public
	 * @param	
	 * @return	array
	 */
	public function create_cart()
	{
		// get products currently in cart
		$products = $this->get();
		
		$data = '';
		$total = 0;
		
		$data .= '<li class="header_row"><div class="col1">Product Name:</div><div class="col2">Quantity:</div><div class="col3">Product Price:</div><div class="col4">Total Price:</div></li>';
		
		if ($products != '')
		{
			// products to display
			$line = 1;
			$shipping = 0;
			
			foreach($products as $product)
			{
				$item_shipping = $this->get_shipping_cost($product['price']) * $_SESSION['cart'][$product['id']];
				$shipping += $item_shipping;
			
				// create new item in cart
				$data .= '<li';
				if ($line % 2 == 0)
				{
					$data .= ' class="alt"';
				}
				$data .= '><div class="col1">' . $product['name'] . '</div>';
				$data .= '<div class="col2"><input name="product' . $product['id'] .'" value="'. $_SESSION['cart'][$product['id']] .'"></div>';
				$data .= '<div class="col3">$' . $product['price'] . '</div>';
				$data .= '<div class="col4">$' . $product['price'] * $_SESSION['cart'][$product['id']] . '</div></li>';
				
				$total += $product['price'] * $_SESSION['cart'][$product['id']];
				$line++;
			}
			
			// add subtotal row
			$data .= '<li class="subtotal_row"><div class="col1">Subtotal:</div><div class="col2">$' . $total . '</div></li>';
			
			// shipping
			$data .= '<li class="shipping_row"><div class="col1">Shipping Cost:</div><div class="col2">$' . number_format($shipping, 2) . '</div></li>';
			
			// taxes
			if (SHOP_TAX > 0)
			{
				$data .= '<li class="taxes_row"><div class="col1">Tax (' . (SHOP_TAX * 100) . '%):</div><div class="col2">$' . number_format(SHOP_TAX * $total, 2) . '</div></li>';
			}
			
			// add total row
			$data .= '<li class="total_row"><div class="col1">Total:</div><div class="col2">$' . number_format((SHOP_TAX * $total) + $total + $shipping, 2) . '</div></li>';
		}
		else 
		{
			// no products to display
			$data .= '<li><strong>No items in the cart!</strong></li>';
			
			// add subtotal row
			$data .= '<li class="subtotal_row"><div class="col1">Subtotal:</div><div class="col2">$0.00</div></li>';
			
			// shipping
			$data .= '<li class="shipping_row"><div class="col1">Shipping Cost:</div><div class="col2">$0.00</div></li>';
			
			// taxes
			if (SHOP_TAX > 0)
			{
				$data .= '<li class="taxes_row"><div class="col1">Tax (' . (SHOP_TAX * 100) . '%):</div><div class="col2">$0.00</div></li>';
			}
			
			// add total row
			$data .= '<li class="total_row"><div class="col1">Total:</div><div class="col2">$0.00</div></li>';
		}
		
		return $data;
	}
	
}