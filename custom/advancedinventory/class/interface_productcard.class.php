<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * Product hooks for Advanced Inventory module
 * File: custom/advancedinventory/class/interface_productcard.class.php
 */

/**
 * Class for hooks on product card
 */
class InterfaceProductcard
{
	/**
	 * @var DoliDB Database handler
	 */
	public $db;

	/**
	 * @var string Error message
	 */
	public $error = '';

	/**
	 * @var array Errors array
	 */
	public $errors = array();

	/**
	 * @var array Hook results
	 */
	public $results = array();

	/**
	 * @var string String to return
	 */
	public $resprints;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('productcard'))) {
			// Handle smart code generation when product type or category changes
			if ($action == 'update' || $action == 'add') {
				$this->handleSmartCodeGeneration($object);
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overloading the printCommonFooter function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process
	 * @param   string          $action         Current action
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function printCommonFooter($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;

		if (in_array($parameters['currentcontext'], array('productcard'))) {
			// Include JavaScript for smart code functionality
			echo '<script src="'.dol_buildpath('/advancedinventory/js/product_smart_code.js', 1).'"></script>';
		}

		return 0;
	}

	/**
	 * Handle smart code generation for product
	 *
	 * @param Product $product Product object
	 * @return void
	 */
	private function handleSmartCodeGeneration($product)
	{
		global $db, $user;

		// Get extrafields
		if (!empty($product->array_options['options_advinv_product_type']) &&
			!empty($product->array_options['options_advinv_product_category'])) {

			$type_id = $product->array_options['options_advinv_product_type'];
			$category_id = $product->array_options['options_advinv_product_category'];

			// Generate smart code
			$smart_code = $this->generateSmartCodeForProduct($type_id, $category_id, $product->id);

			if ($smart_code) {
				// Update product with generated smart code
				$product->array_options['options_advinv_smart_code'] = $smart_code;

				// Save to database
				$sql = "SELECT COUNT(*) as count FROM ".$db->prefix()."product_extrafields WHERE fk_object = ".((int) $product->id);
				$result = $db->query($sql);
				$obj = $db->fetch_object($result);

				if ($obj->count > 0) {
					$sql = "UPDATE ".$db->prefix()."product_extrafields SET advinv_smart_code = '".$db->escape($smart_code)."' WHERE fk_object = ".((int) $product->id);
				} else {
					$sql = "INSERT INTO ".$db->prefix()."product_extrafields (fk_object, advinv_smart_code) VALUES (".((int) $product->id).", '".$db->escape($smart_code)."')";
				}

				$db->query($sql);
			}
		}
	}

	/**
	 * Generate smart code for product
	 *
	 * @param int $type_id Product type ID
	 * @param int $category_id Product category ID
	 * @param int $product_id Product ID
	 * @return string|false Generated smart code or false on error
	 */
	private function generateSmartCodeForProduct($type_id, $category_id, $product_id = 0)
	{
		require_once dol_buildpath('/advancedinventory/class/producttype.class.php');
		require_once dol_buildpath('/advancedinventory/class/productcategory.class.php');

		// Get product type
		$productType = new ProductType($this->db);
		if ($productType->fetch($type_id) <= 0) {
			return false;
		}

		// Get category and its full path
		$category = new ProductCategory($this->db);
		if ($category->fetch($category_id) <= 0) {
			return false;
		}

		// Build the smart code pattern
		$type_prefix = $productType->code_prefix;
		$category_path = str_replace('/', '-', $category->path);

		// Get next auto number
		$auto_number = $this->getNextAutoNumber($type_prefix, $category_path, $product_id);

		// Generate final smart code
		$smart_code = $type_prefix . '-' . $category_path . '-' . $auto_number;

		return $smart_code;
	}

	/**
	 * Get next auto number for the pattern
	 *
	 * @param string $type_prefix Type prefix
	 * @param string $category_path Category path
	 * @param int $exclude_product_id Product ID to exclude
	 * @return string Next auto number (001, 002, etc.)
	 */
	private function getNextAutoNumber($type_prefix, $category_path, $exclude_product_id = 0)
	{
		$pattern = $type_prefix . '-' . $category_path . '-%';

		// Search for existing smart codes with this pattern
		$sql = "SELECT pe.advinv_smart_code FROM ".$this->db->prefix()."product_extrafields pe";
		$sql .= " INNER JOIN ".$this->db->prefix()."product p ON p.rowid = pe.fk_object";
		$sql .= " WHERE pe.advinv_smart_code LIKE '".$this->db->escape($pattern)."'";

		if ($exclude_product_id > 0) {
			$sql .= " AND pe.fk_object != ".((int) $exclude_product_id);
		}

		$sql .= " ORDER BY pe.advinv_smart_code DESC LIMIT 1";

		$result = $this->db->query($sql);
		if ($result && $this->db->num_rows($result) > 0) {
			$obj = $this->db->fetch_object($result);
			$last_code = $obj->advinv_smart_code;

			// Extract the number part from the end
			$parts = explode('-', $last_code);
			if (count($parts) > 0) {
				$last_part = end($parts);
				// Check if last part is numeric
				if (is_numeric($last_part)) {
					$last_number = intval($last_part);
					$next_number = $last_number + 1;
					return sprintf('%03d', $next_number);
				}
			}
		}

		// If no existing code found, start with 001
		return '001';
	}
}
?>
