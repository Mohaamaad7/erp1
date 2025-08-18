<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    advancedinventory/ajax/catalog_actions.php
 * \ingroup advancedinventory
 * \brief   AJAX actions for catalog management
 */

if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1');
if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/custom/advancedinventory/class/catalogmanager.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/advancedinventory/class/supplieritem.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Load translation files
$langs->loadLangs(array("advancedinventory@advancedinventory", "products"));

// Get parameters
$action = GETPOST('action', 'aZ09');
$product_id = GETPOST('product_id', 'int');
$value = GETPOST('value', 'alpha');

// Security check
if (!$user->hasRight('advancedinventory', 'catalog', 'read')) {
	http_response_code(403);
	echo json_encode(array('error' => 'Access denied'));
	exit;
}

$permissiontoadd = $user->hasRight('advancedinventory', 'catalog', 'write');

// Set JSON header
header('Content-Type: application/json');

// Initialize objects
$catalogmanager = new AdvancedInventoryCatalogManager($db);
$response = array('success' => false, 'message' => '');

try {
	switch ($action) {
		case 'update_reorder_point':
			if (!$permissiontoadd) {
				throw new Exception($langs->trans('NotEnoughPermissions'));
			}

			$reorder_point = price2num($value);
			if ($reorder_point < 0) {
				throw new Exception($langs->trans('InvalidValue'));
			}

			$result = $catalogmanager->updateReorderPoint($product_id, $reorder_point, $user);
			if ($result > 0) {
				$response['success'] = true;
				$response['message'] = $langs->trans('ReorderPointUpdated');
				$response['new_value'] = $reorder_point;
			} else {
				throw new Exception($langs->trans('ErrorUpdatingReorderPoint'));
			}
			break;

		case 'generate_smart_code':
			if (!$permissiontoadd) {
				throw new Exception($langs->trans('NotEnoughPermissions'));
			}

			$product = new Product($db);
			if ($product->fetch($product_id) <= 0) {
				throw new Exception($langs->trans('ProductNotFound'));
			}

			$smart_code = $catalogmanager->generateSmartCode($product->type);

			// Update extrafields
			$sql = "SELECT fk_object FROM ".MAIN_DB_PREFIX."product_extrafields WHERE fk_object = ".((int) $product_id);
			$resql = $db->query($sql);

			if ($resql && $db->num_rows($resql) > 0) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."product_extrafields SET advinv_smart_code = '".$db->escape($smart_code)."' WHERE fk_object = ".((int) $product_id);
			} else {
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."product_extrafields (fk_object, advinv_smart_code) VALUES (".((int) $product_id).", '".$db->escape($smart_code)."')";
			}

			if ($db->query($sql)) {
				$response['success'] = true;
				$response['message'] = $langs->trans('SmartCodeGenerated');
				$response['smart_code'] = $smart_code;

			} else {
				throw new Exception($langs->trans('ErrorGeneratingSmartCode'));
			}
			break;

		case 'calculate_reorder_point':
			if (!$permissiontoadd) {
				throw new Exception($langs->trans('NotEnoughPermissions'));
			}

			$calculation = $catalogmanager->calculateReorderPoint($product_id);

			if ($calculation['suggested_reorder_point'] > 0) {
				$result = $catalogmanager->updateReorderPoint($product_id, $calculation['suggested_reorder_point'], $user);
				if ($result > 0) {
					$response['success'] = true;
					$response['message'] = $langs->trans('ReorderPointCalculated');
					$response['calculation'] = $calculation;
					$response['new_reorder_point'] = $calculation['suggested_reorder_point'];
				} else {
					throw new Exception($langs->trans('ErrorUpdatingReorderPoint'));
				}
			} else {
				throw new Exception($langs->trans('InsufficientDataForCalculation'));
			}
			break;

		case 'get_product_info':
			$product = new Product($db);
			if ($product->fetch($product_id) <= 0) {
				throw new Exception($langs->trans('ProductNotFound'));
			}

			// Get extrafields
			$sql = "SELECT * FROM ".MAIN_DB_PREFIX."product_extrafields WHERE fk_object = ".((int) $product_id);
			$resql = $db->query($sql);
			$extrafields = array();
			if ($resql && $db->num_rows($resql) > 0) {
				$extrafields = $db->fetch_array($resql);
			}

			// Get current stock
			$sql = "SELECT SUM(reel) as total_stock FROM ".MAIN_DB_PREFIX."product_stock WHERE fk_product = ".((int) $product_id);
			$resql = $db->query($sql);
			$total_stock = 0;
			if ($resql && $db->num_rows($resql) > 0) {
				$obj = $db->fetch_object($resql);
				$total_stock = $obj->total_stock;
			}

			// Get suppliers
			$suppliers = AdvancedInventorySupplierItem::getSuppliersByProduct($db, $product_id);

			$response['success'] = true;
			$response['product'] = array(
				'id' => $product->id,
				'ref' => $product->ref,
				'label' => $product->label,
				'type' => $product->type,
				'price' => $product->price,
				'total_stock' => $total_stock,
				'extrafields' => $extrafields,
				'suppliers' => $suppliers
			);
			break;

		case 'get_supplier_products':
			$supplier_id = GETPOST('supplier_id', 'int');
			if (empty($supplier_id)) {
				throw new Exception($langs->trans('SupplierRequired'));
			}

			$products = $catalogmanager->getProductsBySupplier($supplier_id);

			$response['success'] = true;
			$response['products'] = $products;
			break;

		case 'search_by_part_number':
			$part_number = GETPOST('part_number', 'alpha');
			if (empty($part_number)) {
				throw new Exception($langs->trans('PartNumberRequired'));
			}

			$supplier_item = AdvancedInventorySupplierItem::getByPartNumber($db, $part_number);

			if ($supplier_item) {
				$response['success'] = true;
				$response['product'] = array(
					'id' => $supplier_item->fk_product,
					'ref' => $supplier_item->product_ref,
					'label' => $supplier_item->product_label,
					'supplier_name' => $supplier_item->supplier_name,
					'part_number' => $supplier_item->supplier_part_num
				);
			} else {
				throw new Exception($langs->trans('PartNumberNotFound'));
			}
			break;

		case 'get_reorder_calculation':
			$calculation = $catalogmanager->calculateReorderPoint($product_id);

			$response['success'] = true;
			$response['calculation'] = $calculation;
			$response['message'] = $langs->trans('ReorderPointCalculationDetails');
			break;

		case 'set_default_supplier':
			if (!$permissiontoadd) {
				throw new Exception($langs->trans('NotEnoughPermissions'));
			}

			$supplier_item_id = GETPOST('supplier_item_id', 'int');
			$supplier_item = new AdvancedInventorySupplierItem($db);

			if ($supplier_item->fetch($supplier_item_id) > 0) {
				$supplier_item->is_default = 1;
				$result = $supplier_item->update($user);

				if ($result > 0) {
					$response['success'] = true;
					$response['message'] = $langs->trans('DefaultSupplierSet');
				} else {
					throw new Exception($langs->trans('ErrorSettingDefaultSupplier'));
				}
			} else {
				throw new Exception($langs->trans('SupplierItemNotFound'));
			}
			break;

		case 'get_catalog_stats':
			$stats = $catalogmanager->getCatalogStatistics();

			$response['success'] = true;
			$response['stats'] = $stats;
			break;

		case 'update_min_max_stock':
			if (!$permissiontoadd) {
				throw new Exception($langs->trans('NotEnoughPermissions'));
			}

			$min_stock = price2num(GETPOST('min_stock', 'alpha'));
			$max_stock = price2num(GETPOST('max_stock', 'alpha'));

			if ($min_stock < 0 || $max_stock < 0) {
				throw new Exception($langs->trans('InvalidValue'));
			}

			if ($max_stock > 0 && $min_stock > $max_stock) {
				throw new Exception($langs->trans('MinStockCannotBeGreaterThanMax'));
			}

			// Update extrafields
			$sql = "SELECT fk_object FROM ".MAIN_DB_PREFIX."product_extrafields WHERE fk_object = ".((int) $product_id);
			$resql = $db->query($sql);

			if ($resql && $db->num_rows($resql) > 0) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."product_extrafields";
				$sql .= " SET advinv_min_stock = ".((float) $min_stock).", advinv_max_stock = ".((float) $max_stock);
				$sql .= " WHERE fk_object = ".((int) $product_id);
			} else {
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."product_extrafields";
				$sql .= " (fk_object, advinv_min_stock, advinv_max_stock)";
				$sql .= " VALUES (".((int) $product_id).", ".((float) $min_stock).", ".((float) $max_stock).")";
			}

			if ($db->query($sql)) {
				$response['success'] = true;
				$response['message'] = $langs->trans('MinMaxStockUpdated');
				$response['min_stock'] = $min_stock;
				$response['max_stock'] = $max_stock;
			} else {
				throw new Exception($langs->trans('ErrorUpdatingMinMaxStock'));
			}
			break;

		default:
			throw new Exception($langs->trans('InvalidAction'));
	}

} catch (Exception $e) {
	$response['success'] = false;
	$response['error'] = $e->getMessage();
	http_response_code(400);
}

// Output JSON response
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
