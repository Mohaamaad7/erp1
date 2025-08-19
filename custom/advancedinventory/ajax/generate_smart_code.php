<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * AJAX handler for Smart Code generation
 * File: custom/advancedinventory/ajax/generate_smart_code.php
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res && file_exists("../../../../main.inc.php")) {
	$res = @include "../../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once dol_buildpath('/advancedinventory/class/producttype.class.php');
require_once dol_buildpath('/advancedinventory/class/productcategory.class.php');

// Load translation files
$langs->loadLangs(array("advancedinventory@advancedinventory"));



// Initialize response
$response = array();
header('Content-Type: application/json');

try {
	// Check permissions
	if (empty($user->id)) {
		throw new Exception($langs->trans('AccessForbidden'));
	}

	// Get parameters
	$type_id = GETPOST('type_id', 'int');
	$category_id = GETPOST('category_id', 'int');
	$product_id = GETPOST('product_id', 'int'); // For checking existing codes

	// Validate input
	if (empty($type_id) || empty($category_id)) {
		throw new Exception($langs->trans('TypeAndCategoryRequired'));
	}

// Security check
	if (!$user->hasRight('advancedinventory', 'catalog', 'read')) {
		http_response_code(403);
		echo json_encode(array('error' => 'Access denied'));
		exit;
	}

	$permissiontoadd = $user->hasRight('advancedinventory', 'catalog', 'write');

	// Generate smart code
	$smart_code = generateSmartCode($db, $type_id, $category_id, $product_id);

	if ($smart_code) {
		$response['success'] = true;
		$response['smart_code'] = $smart_code;
		$response['message'] = $langs->trans('SmartCodeGenerated');
	} else {
		throw new Exception($langs->trans('ErrorGeneratingSmartCode'));
	}

} catch (Exception $e) {
	$response['success'] = false;
	$response['error'] = $e->getMessage();
}

echo json_encode($response);

/**
 * Generate smart code based on type and category
 *
 * @param DoliDB $db Database handler
 * @param int $type_id Product type ID
 * @param int $category_id Product category ID
 * @param int $product_id Product ID (for excluding from duplicate check)
 * @return string|false Generated smart code or false on error
 */
function generateSmartCode($db, $type_id, $category_id, $product_id = 0)
{
	// Get product type
	$productType = new ProductType($db);
	if ($productType->fetch($type_id) <= 0) {
		return false;
	}

	// Get category and its full path
	$category = new ProductCategory($db);
	if ($category->fetch($category_id) <= 0) {
		return false;
	}

	// Build the smart code pattern
	$type_prefix = $productType->code_prefix;
	$category_path = str_replace('/', '-', $category->path);

	// Get next auto number
	$auto_number = getNextAutoNumber($db, $type_prefix, $category_path, $product_id);

	// Generate final smart code
	$smart_code = $type_prefix . '-' . $category_path . '-' . $auto_number;

	return $smart_code;
}

/**
 * Get next auto number for the pattern
 *
 * @param DoliDB $db Database handler
 * @param string $type_prefix Type prefix
 * @param string $category_path Category path
 * @param int $exclude_product_id Product ID to exclude
 * @return string Next auto number (001, 002, etc.)
 */
function getNextAutoNumber($db, $type_prefix, $category_path, $exclude_product_id = 0)
{
	$pattern = $type_prefix . '-' . $category_path . '-%';

	// Search for existing smart codes with this pattern
	$sql = "SELECT pe.advinv_smart_code FROM ".$db->prefix()."product_extrafields pe";
	$sql .= " INNER JOIN ".$db->prefix()."product p ON p.rowid = pe.fk_object";
	$sql .= " WHERE pe.advinv_smart_code LIKE '".$db->escape($pattern)."'";

	if ($exclude_product_id > 0) {
		$sql .= " AND pe.fk_object != ".((int) $exclude_product_id);
	}

	$sql .= " ORDER BY pe.advinv_smart_code DESC LIMIT 1";

	$result = $db->query($sql);
	if ($result && $db->num_rows($result) > 0) {
		$obj = $db->fetch_object($result);
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

/**
 * Check if smart code already exists
 *
 * @param DoliDB $db Database handler
 * @param string $smart_code Smart code to check
 * @param int $exclude_product_id Product ID to exclude
 * @return bool True if exists, false otherwise
 */
function smartCodeExists($db, $smart_code, $exclude_product_id = 0)
{
	$sql = "SELECT COUNT(*) as count FROM ".$db->prefix()."product_extrafields";
	$sql .= " WHERE advinv_smart_code = '".$db->escape($smart_code)."'";

	if ($exclude_product_id > 0) {
		$sql .= " AND fk_object != ".((int) $exclude_product_id);
	}

	$result = $db->query($sql);
	if ($result) {
		$obj = $db->fetch_object($result);
		return ($obj->count > 0);
	}

	return false;
}
?>
