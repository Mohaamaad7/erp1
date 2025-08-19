<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    advancedinventory/catalog/list.php
 * \ingroup advancedinventory
 * \brief   Enhanced product catalog list with advanced inventory features
 */

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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/advancedinventory/class/catalogmanager.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/advancedinventory/class/supplieritem.class.php';

// أضف في بداية الملف
$modulepart = 'advancedinventory';

// Load translation files
$langs->loadLangs(array("advancedinventory@advancedinventory", "products", "stocks", "suppliers"));

// Get parameters
$action = GETPOST('action', 'aZ09');
$massaction = GETPOST('massaction', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$toselect = GETPOST('toselect', 'array');

// Search parameters
$search_ref = GETPOST('search_ref', 'alpha');
$search_label = GETPOST('search_label', 'alpha');
$search_smart_code = GETPOST('search_smart_code', 'alpha');
$search_part_number = GETPOST('search_part_number', 'alpha');
$search_type = GETPOST('search_type', 'int');
$search_tosell = GETPOST('search_tosell', 'int');
$search_tobuy = GETPOST('search_tobuy', 'int');
$search_reorder_alert = GETPOST('search_reorder_alert', 'int');
$search_supplier = GETPOST('search_supplier', 'int');

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');

if (empty($page) || $page < 0) {
	$page = 0;
}
$offset = $limit * $page;

if (!$sortfield) {
	$sortfield = "p.ref";
}
if (!$sortorder) {
	$sortorder = "ASC";
}

// Initialize objects
$catalogmanager = new AdvancedInventoryCatalogManager($db);
$form = new Form($db);
$formcompany = new FormCompany($db);
$formother = new FormOther($db);

// Security check
if (!$user->hasRight('advancedinventory', 'catalog', 'read')) {
	accessforbidden();
}

$permissiontoadd = $user->hasRight('advancedinventory', 'catalog', 'write');

/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}

// Purge search criteria
if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	$search_ref = '';
	$search_label = '';
	$search_smart_code = '';
	$search_part_number = '';
	$search_type = '';
	$search_tosell = '';
	$search_tobuy = '';
	$search_reorder_alert = '';
	$search_supplier = '';
	$toselect = array();
}

// Mass actions
if ($massaction == 'generate_smart_codes' && $permissiontoadd) {
	$count = 0;
	foreach ($toselect as $toselectid) {
		$product = new Product($db);
		if ($product->fetch($toselectid) > 0) {
			// Generate smart code if not exists
			$smart_code = $catalogmanager->generateSmartCode($product->type);

			// Update extrafields
			$sql = "SELECT fk_object FROM ".MAIN_DB_PREFIX."product_extrafields WHERE fk_object = ".((int) $toselectid);
			$resql = $db->query($sql);

			if ($resql && $db->num_rows($resql) > 0) {
				$sql = "UPDATE ".MAIN_DB_PREFIX."product_extrafields SET advinv_smart_code = '".$db->escape($smart_code)."' WHERE fk_object = ".((int) $toselectid);
			} else {
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."product_extrafields (fk_object, advinv_smart_code) VALUES (".((int) $toselectid).", '".$db->escape($smart_code)."')";
			}

			if ($db->query($sql)) {
				$count++;
			}
		}
	}

	if ($count > 0) {
		setEventMessages($langs->trans("SmartCodesGenerated", $count), null, 'mesgs');
	}
}

// Calculate reorder points
if ($massaction == 'calculate_reorder' && $permissiontoadd) {
	$count = 0;
	foreach ($toselect as $toselectid) {
		$reorder_calc = $catalogmanager->calculateReorderPoint($toselectid);
		if ($reorder_calc['suggested_reorder_point'] > 0) {
			if ($catalogmanager->updateReorderPoint($toselectid, $reorder_calc['suggested_reorder_point'], $user) > 0) {
				$count++;
			}
		}
	}

	if ($count > 0) {
		setEventMessages($langs->trans("ReorderPointsCalculated", $count), null, 'mesgs');
	}
}

/*
 * View
 */

$title = $langs->trans('AdvancedItemCatalog');
$help_url = '';

llxHeader('', $title, $help_url);

// Prepare filters
$filters = array(
	'search_ref' => $search_ref,
	'search_label' => $search_label,
	'search_smart_code' => $search_smart_code,
	'search_part_number' => $search_part_number,
	'type' => $search_type,
	'tosell' => $search_tosell,
	'tobuy' => $search_tobuy,
	'reorder_alert' => $search_reorder_alert,
	'supplier_id' => $search_supplier
);

// Get products
$products = $catalogmanager->getProductsWithInventoryInfo($filters, $sortfield, $sortorder, $limit, $offset);
$nbtotalofrecords = $catalogmanager->getProductsCount($filters);

// Build array of selected items
$arrayofselected = is_array($toselect) ? $toselect : array();

// List of mass actions available
$arrayofmassactions = array();
if ($permissiontoadd) {
	$arrayofmassactions['generate_smart_codes'] = img_picto('', 'technic', 'class="pictofixedwidth"').$langs->trans("GenerateSmartCodes");
	$arrayofmassactions['calculate_reorder'] = img_picto('', 'calc', 'class="pictofixedwidth"').$langs->trans("CalculateReorderPoints");
}
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

// Build parameters
$param = '';
if ($search_ref) $param .= '&search_ref='.urlencode($search_ref);
if ($search_label) $param .= '&search_label='.urlencode($search_label);
if ($search_smart_code) $param .= '&search_smart_code='.urlencode($search_smart_code);
if ($search_part_number) $param .= '&search_part_number='.urlencode($search_part_number);
if ($search_type != '') $param .= '&search_type='.urlencode($search_type);
if ($search_tosell != '') $param .= '&search_tosell='.urlencode($search_tosell);
if ($search_tobuy != '') $param .= '&search_tobuy='.urlencode($search_tobuy);
if ($search_reorder_alert) $param .= '&search_reorder_alert='.urlencode($search_reorder_alert);
if ($search_supplier) $param .= '&search_supplier='.urlencode($search_supplier);

// Get statistics
$stats = $catalogmanager->getCatalogStatistics();

// Display statistics boxes
print '<div class="fichecenter">';
print '<div class="fichehalfleft">';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder nohover centpercent">';
print '<tr class="liste_titre">';
print '<th colspan="2">'.$langs->trans("CatalogStatistics").'</th>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("TotalProducts").'</td>';
print '<td class="right"><strong>'.$stats['total_products'].'</strong></td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("ProductsWithSmartCode").'</td>';
print '<td class="right">';
$percentage = $stats['total_products'] > 0 ? round(($stats['products_with_smart_code'] / $stats['total_products']) * 100, 1) : 0;
print '<strong>'.$stats['products_with_smart_code'].'</strong> ('.$percentage.'%)';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("ProductsWithSuppliers").'</td>';
print '<td class="right">';
$percentage = $stats['total_products'] > 0 ? round(($stats['products_with_suppliers'] / $stats['total_products']) * 100, 1) : 0;
print '<strong>'.$stats['products_with_suppliers'].'</strong> ('.$percentage.'%)';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td class="warning">'.$langs->trans("ProductsBelowReorderPoint").'</td>';
print '<td class="right warning">';
print '<strong>'.$stats['products_below_reorder'].'</strong>';
if ($stats['products_below_reorder'] > 0) {
	print ' <a href="'.$_SERVER["PHP_SELF"].'?search_reorder_alert=1" class="button button-small">'.$langs->trans("ViewList").'</a>';
}
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("TotalSuppliers").'</td>';
print '<td class="right"><strong>'.$stats['total_suppliers'].'</strong></td>';
print '</tr>';

print '</table>';
print '</div>';

print '</div>';
print '<div class="fichehalfright">';

// Quick actions
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder nohover centpercent">';
print '<tr class="liste_titre">';
print '<th>'.$langs->trans("QuickActions").'</th>';
print '</tr>';

print '<tr class="oddeven">';
print '<td class="center">';
print '<a href="'.$_SERVER["PHP_SELF"].'?search_reorder_alert=1" class="button">';
print img_picto('', 'warning', 'class="pictofixedwidth"');
print $langs->trans("ProductsBelowReorderPoint");
print '</a>';
print '</td>';
print '</tr>';

if ($permissiontoadd) {
	print '<tr class="oddeven">';
	print '<td class="center">';
	print '<a href="'.DOL_URL_ROOT.'/product/card.php?action=create" class="button">';
	print img_picto('', 'add', 'class="pictofixedwidth"');
	print $langs->trans("NewProduct");
	print '</a>';
	print '</td>';
	print '</tr>';
}

print '</table>';
print '</div>';

print '</div>';
print '</div>';

print '<div class="clearboth"></div>';

// New button
$newcardbutton = '';
if ($permissiontoadd) {
	$newcardbutton = dolGetButtonTitle($langs->trans('NewProduct'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/product/card.php?action=create', '', 1);
}

// Display page
print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, count($products), $nbtotalofrecords, 'product', 0, $newcardbutton, '', $limit);

print '<div class="div-table-responsive">';
print '<table class="tagtable nobottomiftotal liste">';

// Fields title search
print '<tr class="liste_titre_filter">';

// Mass action
print '<td class="liste_titre center maxwidthsearch">';
// أضف name و value لزر البحث
$searchpicto = $form->showFilterAndCheckAddButtons($massactionbutton ? 1 : 0, 'checkforselect', 1, 'list', '', '', '', 'button_search');
print $searchpicto;
print '</td>';

// Ref
print '<td class="liste_titre">';
print '<input type="text" class="flat maxwidth75" name="search_ref" value="'.dol_escape_htmltag($search_ref).'">';
print '</td>';

// Label
print '<td class="liste_titre">';
print '<input type="text" class="flat maxwidth100" name="search_label" value="'.dol_escape_htmltag($search_label).'">';
print '</td>';

// Smart Code
print '<td class="liste_titre">';
print '<input type="text" class="flat maxwidth75" name="search_smart_code" value="'.dol_escape_htmltag($search_smart_code).'">';
print '</td>';

// Type
print '<td class="liste_titre center">';
print $form->selectarray('search_type', array('-1' => '', '0' => $langs->trans('Product'), '1' => $langs->trans('Service')), $search_type, 0, 0, 0, '', 0, 0, 0, '', 'maxwidth75');
print '</td>';

// Current Stock
print '<td class="liste_titre"></td>';

// Reorder Point
print '<td class="liste_titre"></td>';

// Default Supplier
print '<td class="liste_titre">';
print $formcompany->select_company($search_supplier, 'search_supplier', 's.fournisseur = 1', 'SelectSupplier', 0, 0, array(), 0, 'maxwidth100');
print '</td>';

// Part Number
print '<td class="liste_titre">';
print '<input type="text" class="flat maxwidth75" name="search_part_number" value="'.dol_escape_htmltag($search_part_number).'">';
print '</td>';

// Status filters
print '<td class="liste_titre center">';
print '<input type="checkbox" name="search_reorder_alert" value="1"'.($search_reorder_alert ? ' checked' : '').'> '.$langs->trans("BelowReorder");
print '</td>';

print '</tr>';

// Fields title label
print '<tr class="liste_titre">';
print_liste_field_titre('', $_SERVER["PHP_SELF"], "", '', $param, '', $sortfield, $sortorder, 'center maxwidthsearch ');
print_liste_field_titre("Ref", $_SERVER["PHP_SELF"], "p.ref", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Label", $_SERVER["PHP_SELF"], "p.label", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("SmartCode", $_SERVER["PHP_SELF"], "pe.advinv_smart_code", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Type", $_SERVER["PHP_SELF"], "p.fk_product_type", "", $param, '', $sortfield, $sortorder, 'center ');
print_liste_field_titre("CurrentStock", $_SERVER["PHP_SELF"], "total_stock", "", $param, '', $sortfield, $sortorder, 'right ');
print_liste_field_titre("ReorderPoint", $_SERVER["PHP_SELF"], "pe.advinv_reorder_point", "", $param, '', $sortfield, $sortorder, 'right ');
print_liste_field_titre("DefaultSupplier", $_SERVER["PHP_SELF"], "ds.supplier_name", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("PartNumber", $_SERVER["PHP_SELF"], "ds.supplier_part_num", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Status", $_SERVER["PHP_SELF"], "", "", $param, '', $sortfield, $sortorder, 'center ');
print '</tr>';

// Loop on records
$i = 0;
foreach ($products as $product_obj) {
	print '<tr class="' . $row_class . '">';

	// Mass action checkbox
	print '<td class="center">';
	if ($massactionbutton || $massaction) {
		$selected = 0;
		if (in_array($product_obj->rowid, $arrayofselected)) {
			$selected = 1;
		}
		print '<input id="cb'.$product_obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$product_obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
	}
	print '</td>';

	// Ref
	print '<td class="nowraponall">';
	$product = new Product($db);
	$product->id = $product_obj->rowid;
	$product->ref = $product_obj->ref;
	$product->type = $product_obj->fk_product_type;
	print $product->getNomUrl(1);
	print '</td>';

	// Label
	print '<td class="tdoverflowmax200">';
	print dol_escape_htmltag($product_obj->label);
	print '</td>';

	// Smart Code
	print '<td class="nowraponall" id="smartcode_'.$product_obj->rowid.'">';
	if ($product_obj->advinv_smart_code) {
		print '<span class="badge badge-info">'.$product_obj->advinv_smart_code.'</span>';
	} else {
		print '<span class="opacitymedium">'.$langs->trans("NotSet").'</span>';
	}
	print '</td>';

	// Type
	print '<td class="center">';
	print $langs->trans($product_obj->fk_product_type ? 'Service' : 'Product');
	print '</td>';

	// Current Stock
	print '<td class="right">';
// تحديد حالة المخزون
	$row_class = 'oddeven';
	if ($product_obj->advinv_reorder_point > 0) {
		if ($product_obj->total_stock <= 0) {
			$row_class .= ' reorder-critical'; // حالة الخطر الحرج
		} elseif ($product_obj->total_stock <= $product_obj->advinv_reorder_point) {
			$row_class .= ' reorder-warning'; // حالة التحذير
		}
	}
	print '<span class="'.$stock_class.'">';
	print ($product_obj->total_stock > 0 ? $product_obj->total_stock : '0');
	print '</span>';
	print '</td>';

	// Reorder Point
	print '<td class="right">';
	if ($product_obj->advinv_reorder_point > 0) {
		print $product_obj->advinv_reorder_point;
		if ($product_obj->total_stock <= $product_obj->advinv_reorder_point) {
			print ' '.img_warning($langs->trans("BelowReorderPoint"));
		}
	} else {
		print '<span class="opacitymedium">-</span>';
	}
	print '</td>';

	// Default Supplier
	print '<td class="tdoverflowmax150">';
	if ($product_obj->default_supplier_name) {
		print dol_escape_htmltag($product_obj->default_supplier_name);
		if ($product_obj->supplier_count > 1) {
			print ' <span class="badge">+'.$product_obj->supplier_count.'</span>';
		}
	} else {
		print '<span class="opacitymedium">'.$langs->trans("NoSupplierConfigured").'</span>';
	}
	print '</td>';

	// Part Number
	print '<td class="nowraponall">';
	if ($product_obj->default_part_num) {
		print '<span class="badge badge-secondary">'.$product_obj->default_part_num.'</span>';
	} else {
		print '<span class="opacitymedium">-</span>';
	}
	print '</td>';

	// Status
	print '<td class="center nowraponall">';
	// Sale status
	if ($product_obj->tosell) {
		print '<span class="badge badge-status4 badge-status" title="'.$langs->trans("OnSell").'">'.$langs->trans("Sale").'</span> ';
	}
	// Buy status
	if ($product_obj->tobuy) {
		print '<span class="badge badge-status8 badge-status" title="'.$langs->trans("OnBuy").'">'.$langs->trans("Buy").'</span>';
	}
	print '</td>';

	print '</tr>';
	$i++;
}

// Empty result
if (count($products) == 0) {
	$colspan = 10;
	print '<tr><td colspan="'.$colspan.'" class="opacitymedium">'.$langs->trans("NoRecordsFound").'</td></tr>';
}

print '</table>';
print '</div>';

print '</form>';

// End of page
llxFooter();
$db->close();
