<?php

/*
	Products Class
	Handles all tasks related to retrieving and displaying products
*/

class Products
{
	private $Database;
	private $db_table = 'products';
	
	function __construct()
	{
		global $Database;
		$this->Database = $Database;
	}
	
	/*
		Getters / Setters
	*/
	
	/**
	 * Retrieve product information from database
	 *
	 * @access	public
	 * @param	int (optional)
	 * @return	array
	 */
	public function get($id = NULL)
	{
		$data = array();
		
		if (is_array($id))
		{
			// get products based on array of ids
			$items = '';
			foreach ($id as $item)
			{
				if ($items != '') 
				{ 
					$items .= ','; 
				}
				$items .= $item;
			}
			
			if ($result = $this->Database->query("SELECT id, name, description, price, image FROM $this->db_table WHERE id IN ($items) ORDER BY name"))
			{
				if ($result->num_rows > 0)
				{
					while ($row = $result->fetch_array())
					{
						$data[] = array(
							'id' => $row['id'],
							'name' => $row['name'],
							'description' => $row['description'],
							'price' => $row['price'],
							'image' => $row['image'],
							'quantity' => $_SESSION['cart'][ $row['id'] ]
							);
					}
				}
			}
		}
		else if ($id != NULL)
		{
			// get one specific product
			if ($stmt = $this->Database->prepare("SELECT
				$this->db_table.id,
				$this->db_table.name,
				$this->db_table.description,
				$this->db_table.price,
				$this->db_table.image,
				categories.name AS category_name
				FROM $this->db_table, categories
				WHERE $this->db_table.id = ? AND $this->db_table.category_id = categories.id"))
			{
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$stmt->store_result();
				$stmt->bind_result($prod_id, $prod_name, $prod_description, $prod_price, $prod_image, $cat_name);
				$stmt->fetch();
				
				if ($stmt->num_rows > 0)
				{
					$data = array('id' => $prod_id, 'name' => $prod_name, 'description' => $prod_description, 'price' => $prod_price, 'image' => $prod_image, 'category_name' => $cat_name);
				}			
				$stmt->close();
			}
		}
		else 
		{
			// get all products
			if ($result = $this->Database->query("SELECT * FROM " . $this->db_table . " ORDER BY name"))
			{
				if ($result->num_rows > 0)
				{
					while ($row = $result->fetch_array())
					{
						$data[] = array(
							'id' => $row['id'],
							'name' => $row['name'],
							'price' => $row['price'],
							'image' => $row['image']
						);
					}
				}
			}
		}
		return $data;
	}
	
	/**
	 * Retrieve product information for all products in specific category
	 *
	 * @access	public
	 * @param	int
	 * @return	string
	 */
	public function get_in_category($id)
	{
		$data = array();
		if ($stmt = $this->Database->prepare("SELECT id, name, price, image FROM " . $this->db_table . " WHERE category_id = ? ORDER BY name"))
		{
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->store_result();
			
			$stmt->bind_result($prod_id, $prod_name, $prod_price, $prod_image);
			while ($stmt->fetch())
			{
				$data[] = array(
					'id' => $prod_id,
					'name' => $prod_name,
					'price' => $prod_price,
					'image' => $prod_image);
			}
			$stmt->close();
		}
		return $data;
	}	
	
	/**
	 * Return an array of price info for specified ids
	 *
	 * @access	public
	 * @param	array
	 * @return	array
	 */
	public function get_prices($ids)
	{
		$data = array();
		
		// create comma separated list of ids
		$items = '';
		foreach($ids as $id)
		{
			if ($items != '') 
			{
				$items .= ',';
			}
			$items .= $id;
		}
		
		// get multiple product info based on list of ids
		if ($result = $this->Database->query("SELECT id, price FROM $this->db_table WHERE id IN ($items) ORDER BY name"))
		{
			if ($result->num_rows > 0)
			{
				while ($row = $result->fetch_array())
				{
					$data[] = array(
						'id' => $row['id'],
						'price' => $row['price']
						);
				}
			}
		}
		return $data;
	}
	
	/**
	 * Checks to ensure that product exists
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */
	public function product_exists($id)
	{
		if ($stmt = $this->Database->prepare("SELECT id FROM $this->db_table WHERE id = ?"))
		{
			$stmt->bind_param('i', $id);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($id);
			$stmt->fetch();
			
			if ($stmt->num_rows > 0)
			{
				$stmt->close();
				return TRUE;
			}
			$stmt->close();
			return FALSE;
		}
	}
	
	
	
	
	/*
		Creation of page elements
	*/
	
	/**
	 * Create product table using info from database
	 *
	 * @access	public
	 * @param	int (optional), int (optional)
	 * @return	string
	 */
	public function create_product_table($cols = 4, $category = NULL)
	{
		// get products
		if ($category != NULL)
		{
			$products = $this->get_in_category($category);
		}
		else
		{
			$products = $this->get();
		}
		
		$data = '';
		
		// loop through each product
		if ( ! empty($products))
		{
			$i = 1;
			foreach ($products as $product)
			{
				$data .= '<li';
				if ($i == $cols) 
				{  
					$data .= ' class="last"';
					$i = 0;
				}
				$data .= '><a href="' . SITE_PATH . 'product.php?id=' . $product['id'] . '">';
				$data .= '<img src="' . IMAGE_PATH . $product['image'] . '" alt="' . $product['name'] . '"><br>';
				$data .= '<strong>' . $product['name'] . '</strong></a><br/>$' . $product['price'];
				$data .= '<br><a class="button_sml" href="' . SITE_PATH . 'cart.php?id=' . $product['id'] . '">Add to cart</a></li>';
				$i++;
			}
		}
		return $data;
	}
	
}