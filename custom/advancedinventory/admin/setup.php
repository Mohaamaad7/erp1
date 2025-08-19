<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once dol_buildpath('/advancedinventory/class/productcategory.class.php');
require_once dol_buildpath('/advancedinventory/class/producttype.class.php');

// Load translation files
$langs->loadLangs(array("admin", "advancedinventory@advancedinventory"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$tab = GETPOST('tab', 'alpha');
if (empty($tab)) $tab = 'general'; // Default to general settings

/*
 * Actions
 */

// Product Categories Actions
if ($action == 'add_category') {
	$category_name = GETPOST('category_name', 'alpha');
	$category_code = GETPOST('category_code', 'alpha');
	$parent_id = GETPOST('parent_id', 'int');
	$description = GETPOST('description', 'restricthtml');

	if (!empty($category_name) && !empty($category_code)) {
		$category = new ProductCategory($db);
		$category->category_name = $category_name;
		$category->category_code = strtoupper($category_code);
		$category->fk_parent = ($parent_id > 0) ? $parent_id : null;
		$category->description = $description;
		$category->status = ProductCategory::STATUS_ENABLED;

		$result = $category->create($user);
		if ($result > 0) {
			setEventMessages($langs->trans("CategoryAddedSuccessfully"), null, 'mesgs');
		} else {
			setEventMessages($category->error, $category->errors, 'errors');
		}
	} else {
		setEventMessages($langs->trans("CategoryNameAndCodeRequired"), null, 'errors');
	}

	// Stay on categories tab
	$tab = 'categories';
}

if ($action == 'delete_category') {
	$id = GETPOST('id', 'int');
	if ($id > 0) {
		$category = new ProductCategory($db);
		if ($category->fetch($id) > 0) {
			$result = $category->delete($user);
			if ($result > 0) {
				setEventMessages($langs->trans("CategoryDeletedSuccessfully"), null, 'mesgs');
			} else {
				setEventMessages($category->error, $category->errors, 'errors');
			}
		}
	}

	// Stay on categories tab
	$tab = 'categories';
}

// Product Types Actions
if ($action == 'add_product_type') {
	$type_name = GETPOST('type_name', 'alpha');
	$type_code = GETPOST('type_code', 'alpha');
	$code_prefix = GETPOST('code_prefix', 'alpha');
	$description = GETPOST('description', 'restricthtml');

	if (!empty($type_name) && !empty($type_code) && !empty($code_prefix)) {
		$productType = new ProductType($db);
		$productType->type_name = $type_name;
		$productType->type_code = strtoupper($type_code);
		$productType->code_prefix = trim($code_prefix); // Keep original case for numbers
		$productType->description = $description;
		$productType->status = ProductType::STATUS_ENABLED;

		$result = $productType->create($user);
		if ($result > 0) {
			setEventMessages($langs->trans("ProductTypeAddedSuccessfully"), null, 'mesgs');
		} else {
			setEventMessages($productType->error, $productType->errors, 'errors');
		}
	} else {
		setEventMessages($langs->trans("TypeNameCodeAndPrefixRequired"), null, 'errors');
	}

	// Stay on types tab
	$tab = 'types';
}

if ($action == 'update_product_type') {
	$type_id = GETPOST('type_id', 'int');
	$type_name = GETPOST('type_name', 'alpha');
	$code_prefix = GETPOST('code_prefix', 'alpha');

	if ($type_id > 0 && !empty($type_name) && !empty($code_prefix)) {
		$productType = new ProductType($db);
		if ($productType->fetch($type_id) > 0) {
			$productType->type_name = $type_name;
			$productType->code_prefix = trim($code_prefix); // Keep original case for numbers

			$result = $productType->update($user);
			if ($result > 0) {
				setEventMessages($langs->trans("ProductTypeUpdatedSuccessfully"), null, 'mesgs');
			} else {
				setEventMessages($productType->error, $productType->errors, 'errors');
			}
		}
	}

	// Stay on types tab
	$tab = 'types';
}

if ($action == 'delete_product_type') {
	$id = GETPOST('id', 'int');
	if ($id > 0) {
		$productType = new ProductType($db);
		if ($productType->fetch($id) > 0) {
			$result = $productType->delete($user);
			if ($result > 0) {
				setEventMessages($langs->trans("ProductTypeDeletedSuccessfully"), null, 'mesgs');
			} else {
				setEventMessages($productType->error, $productType->errors, 'errors');
			}
		}
	}

	// Stay on types tab
	$tab = 'types';
}

if ($action == 'cleanuninstall') {
	// Double check admin rights
	if ($user->admin) {
		// Disable module with clean uninstall option
		include_once DOL_DOCUMENT_ROOT.'/core/class/dolibarr_modules.class.php';
		$moduleName = 'modAdvancedinventory';
		$moduleFile = dol_buildpath('/advancedinventory/core/modules/modAdvancedinventory.class.php', 0);

		if (file_exists($moduleFile)) {
			require_once $moduleFile;
			$objMod = new $moduleName($db);
			$result = $objMod->remove('cleanuninstall');

			if ($result > 0) {
				setEventMessages($langs->trans("CleanUninstallSuccess"), null, 'mesgs');
				// Redirect to modules page
				header("Location: ".DOL_URL_ROOT.'/admin/modules.php?mode=common');
				exit;
			} else {
				setEventMessages($langs->trans("CleanUninstallError"), null, 'errors');
			}
		}
	}
}

/*
 * Helper functions to get data properly
 */

/**
 * Get all categories with proper SQL query
 */
function getAllCategories($db) {
	$categories = array();

	$sql = "SELECT rowid, category_name, category_code, fk_parent, level, path, status, description";
	$sql .= " FROM ".$db->prefix()."advancedinventory_product_categories";
	$sql .= " ORDER BY path, category_name";

	$result = $db->query($sql);
	if ($result) {
		while ($obj = $db->fetch_object($result)) {
			$categories[] = array(
				'id' => $obj->rowid,
				'name' => $obj->category_name,
				'code' => $obj->category_code,
				'parent_id' => $obj->fk_parent,
				'level' => $obj->level,
				'path' => $obj->path,
				'status' => $obj->status,
				'description' => $obj->description
			);
		}
	}

	return $categories;
}

/**
 * Get all product types with proper SQL query
 */
function getAllProductTypes($db) {
	$types = array();

	$sql = "SELECT rowid, type_name, type_code, code_prefix, status, description";
	$sql .= " FROM ".$db->prefix()."advancedinventory_product_types";
	$sql .= " ORDER BY rowid";

	$result = $db->query($sql);
	if ($result) {
		while ($obj = $db->fetch_object($result)) {
			$types[] = array(
				'id' => $obj->rowid,
				'name' => $obj->type_name,
				'code' => $obj->type_code,
				'prefix' => $obj->code_prefix,
				'status' => $obj->status,
				'description' => $obj->description
			);
		}
	}

	return $types;
}

/*
 * View
 */

$form = new Form($db);

llxHeader('', $langs->trans("AdvancedinventorySetup"));

print load_fiche_titre($langs->trans("AdvancedinventorySetup"), '', 'title_setup');

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("AdvancedinventorySetup"), $linkback, 'object_advancedinventory@advancedinventory');

// Configuration header with tabs (reordered - General first)
$head = array();
$head[0][0] = DOL_URL_ROOT.'/custom/advancedinventory/admin/setup.php?tab=general';
$head[0][1] = $langs->trans("GeneralSettings");
$head[0][2] = 'general';

$head[1][0] = DOL_URL_ROOT.'/custom/advancedinventory/admin/setup.php?tab=categories';
$head[1][1] = $langs->trans("ProductCategories");
$head[1][2] = 'categories';

$head[2][0] = DOL_URL_ROOT.'/custom/advancedinventory/admin/setup.php?tab=types';
$head[2][1] = $langs->trans("ProductTypes");
$head[2][2] = 'types';

print dol_get_fiche_head($head, $tab, '', -1, 'advancedinventory@advancedinventory');

// Setup page content
print '<span class="opacitymedium">'.$langs->trans("AdvancedinventorySetupDesc").'</span><br><br>';

/*
 * TAB: General Settings (First tab)
 */
if ($tab == 'general') {
	print load_fiche_titre($langs->trans("GeneralSettings"), '', 'fa-cogs');

	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Parameter").'</td>';
	print '<td>'.$langs->trans("Value").'</td>';
	print '<td>'.$langs->trans("Action").'</td>';
	print '</tr>';

	// Add more general settings here as needed
	print '<tr><td colspan="3" class="center opacitymedium">'.$langs->trans("NoSettingsYet").'</td></tr>';

	print '</table>';

	// Smart Code Preview Section (moved here for better organization)
	print '<br><br>';
	print load_fiche_titre($langs->trans("SmartCodeGeneration"), '', 'fa-code');

	print '<div class="info">';
	print '<h4>'.$langs->trans("SmartCodePattern").'</h4>';
	print '<div class="smart-code-preview">';
	print '<span class="code-segment">[TYPE_PREFIX]</span>-<span class="code-segment">[CATEGORY_CODE]</span>-<span class="code-segment">[AUTO_NUMBER]</span>';
	print '</div>';

	// Show examples based on current types and categories
	print '<p>'.$langs->trans("SmartCodeExamples").':</p>';
	print '<ul>';
	$types = getAllProductTypes($db);
	$categories = getAllCategories($db);
	if (!empty($types) && !empty($categories)) {
		$exampleType = $types[0];
		$exampleCategory = $categories[0];
		print '<li><strong>'.$exampleType['prefix'].'-'.$exampleCategory['code'].'-001</strong> ('.$exampleType['name'].' - '.$exampleCategory['name'].')</li>';

		if (count($types) > 1 && count($categories) > 1) {
			$exampleType2 = $types[1];
			$exampleCategory2 = $categories[1];
			print '<li><strong>'.$exampleType2['prefix'].'-'.$exampleCategory2['code'].'-001</strong> ('.$exampleType2['name'].' - '.$exampleCategory2['name'].')</li>';
		}
	} else {
		print '<li><strong>0-MOTOR-001</strong> (محلي - موتور)</li>';
		print '<li><strong>1-FABRIC-002</strong> (مستورد - قماش)</li>';
	}
	print '</ul>';
	print '</div>';

	// Module Statistics
	print '<br><br>';
	print load_fiche_titre($langs->trans("ModuleStatistics"), '', 'fa-chart-bar');

	$totalCategories = count(getAllCategories($db));
	$totalTypes = count(getAllProductTypes($db));

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Statistic").'</td>';
	print '<td>'.$langs->trans("Count").'</td>';
	print '</tr>';

	print '<tr class="oddeven">';
	print '<td>'.$langs->trans("TotalCategories").'</td>';
	print '<td><span class="badge badge-status4">'.$totalCategories.'</span></td>';
	print '</tr>';

	print '<tr class="oddeven">';
	print '<td>'.$langs->trans("TotalProductTypes").'</td>';
	print '<td><span class="badge badge-status4">'.$totalTypes.'</span></td>';
	print '</tr>';

	print '</table>';
	print '</div>';

	// Danger Zone
	print '<br><br>';
	print '<div class="error">';
	print load_fiche_titre($langs->trans("DangerZone"), '', 'fa-exclamation-triangle');
	print '<div class="warning">';
	print img_warning().' '.$langs->trans("CleanUninstallWarning").'<br>';
	print $langs->trans("CleanUninstallDesc").'<br><br>';
	print '<a href="'.$_SERVER["PHP_SELF"].'?action=cleanuninstall&token='.newToken().'" class="button" ';
	print 'onclick="return confirm(\''.$langs->trans("ConfirmCleanUninstall").'\');">';
	print $langs->trans("CleanUninstall");
	print '</a>';
	print '</div>';
	print '</div>';
}

/*
 * TAB: Product Categories
 */
elseif ($tab == 'categories') {
	print load_fiche_titre($langs->trans("ProductCategoriesManagement"), '', 'fa-sitemap');

	// Add new category form
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?tab=categories">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add_category">';

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("CategoryName").'</td>';
	print '<td>'.$langs->trans("CategoryCode").'</td>';
	print '<td>'.$langs->trans("ParentCategory").'</td>';
	print '<td>'.$langs->trans("Description").'</td>';
	print '<td>'.$langs->trans("Action").'</td>';
	print '</tr>';

	print '<tr class="oddeven">';
	print '<td><input type="text" name="category_name" class="flat minwidth200" required></td>';
	print '<td><input type="text" name="category_code" class="flat minwidth100" required placeholder="CODE"></td>';
	print '<td>';

	// Parent category dropdown
	print '<select name="parent_id" class="flat minwidth150">';
	print '<option value="0">'.$langs->trans("NoParent").'</option>';

	$categories = getAllCategories($db);
	foreach ($categories as $cat) {
		$indent = str_repeat('&nbsp;&nbsp;&nbsp;', $cat['level']);
		print '<option value="'.$cat['id'].'">'.$indent.$cat['name'].'</option>';
	}
	print '</select>';
	print '</td>';
	print '<td><textarea name="description" class="flat minwidth200" rows="2"></textarea></td>';
	print '<td><input type="submit" class="button small" value="'.$langs->trans("Add").'"></td>';
	print '</tr>';
	print '</table>';
	print '</div>';
	print '</form>';

	// Display existing categories
	print '<br>';
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("CategoryName").'</td>';
	print '<td>'.$langs->trans("CategoryCode").'</td>';
	print '<td>'.$langs->trans("Level").'</td>';
	print '<td>'.$langs->trans("FullPath").'</td>';
	print '<td>'.$langs->trans("Action").'</td>';
	print '</tr>';

	$categories = getAllCategories($db);
	if (!empty($categories)) {
		$var = false;
		foreach ($categories as $cat) {
			$var = !$var;
			print '<tr class="'.($var ? 'pair' : 'impair').'">';

			$indent = str_repeat('&nbsp;&nbsp;&nbsp;', $cat['level']);
			print '<td>'.$indent.$cat['name'].'</td>';
			print '<td><span class="badge">'.$cat['code'].'</span></td>';
			print '<td>'.$cat['level'].'</td>';
			print '<td><small>'.$cat['path'].'</small></td>';
			print '<td>';
			print '<a href="'.$_SERVER["PHP_SELF"].'?action=delete_category&token='.newToken().'&id='.$cat['id'].'&tab=categories" ';
			print 'onclick="return confirm(\''.$langs->trans("ConfirmDeleteCategory").'\');">';
			print img_delete();
			print '</a>';
			print '</td>';
			print '</tr>';
		}
	} else {
		print '<tr><td colspan="5" class="center opacitymedium">'.$langs->trans("NoRecordsFound").'</td></tr>';
	}
	print '</table>';
	print '</div>';
}

/*
 * TAB: Product Types
 */
elseif ($tab == 'types') {
	print load_fiche_titre($langs->trans("ProductTypesManagement"), '', 'fa-tags');

	// Add new product type form
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?tab=types">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add_product_type">';

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("TypeName").'</td>';
	print '<td>'.$langs->trans("TypeCode").'</td>';
	print '<td>'.$langs->trans("CodePrefix").'</td>';
	print '<td>'.$langs->trans("Description").'</td>';
	print '<td>'.$langs->trans("Action").'</td>';
	print '</tr>';

	print '<tr class="oddeven">';
	print '<td><input type="text" name="type_name" class="flat minwidth150" required placeholder="'.$langs->trans("Example").': محلي"></td>';
	print '<td><input type="text" name="type_code" class="flat minwidth100" required placeholder="LOCAL"></td>';
	print '<td><input type="text" name="code_prefix" class="flat minwidth50" required placeholder="L, I, 0, 1, 00, 01..." maxlength="5"></td>';
	print '<td><textarea name="description" class="flat minwidth200" rows="2" placeholder="'.$langs->trans("Optional").'"></textarea></td>';
	print '<td><input type="submit" class="button small" value="'.$langs->trans("Add").'"></td>';
	print '</tr>';
	print '</table>';
	print '</div>';
	print '</form>';

	// Display existing product types
	print '<br>';
	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("TypeName").'</td>';
	print '<td>'.$langs->trans("TypeCode").'</td>';
	print '<td>'.$langs->trans("CodePrefix").'</td>';
	print '<td>'.$langs->trans("Description").'</td>';
	print '<td>'.$langs->trans("Action").'</td>';
	print '</tr>';

	// Load product types
	$types = getAllProductTypes($db);
	if (!empty($types)) {
		$var = false;
		foreach ($types as $type) {
			$var = !$var;
			print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?tab=types" style="margin:0;">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="update_product_type">';
			print '<input type="hidden" name="type_id" value="'.$type['id'].'">';

			print '<tr class="'.($var ? 'pair' : 'impair').'">';
			print '<td>';
			print '<input type="text" name="type_name" value="'.$type['name'].'" class="flat minwidth150">';
			print '</td>';
			print '<td>';
			print '<span class="badge">'.$type['code'].'</span>';
			print '</td>';
			print '<td>';
			print '<input type="text" name="code_prefix" value="'.$type['prefix'].'" class="flat minwidth50" maxlength="5">';
			print '</td>';
			print '<td>';
			print '<small>'.dol_escape_htmltag($type['description']).'</small>';
			print '</td>';
			print '<td>';
			print '<input type="submit" class="button small" value="'.$langs->trans("Update").'">';
			print ' <a href="'.$_SERVER["PHP_SELF"].'?action=delete_product_type&token='.newToken().'&id='.$type['id'].'&tab=types" ';
			print 'onclick="return confirm(\''.$langs->trans("ConfirmDeleteProductType").'\');">';
			print img_delete();
			print '</a>';
			print '</td>';
			print '</tr>';
			print '</form>';
		}
	} else {
		print '<tr><td colspan="5" class="center opacitymedium">'.$langs->trans("NoRecordsFound").'</td></tr>';
	}
	print '</table>';
	print '</div>';
}

print dol_get_fiche_end();

// Include external JavaScript file instead of inline
print '<script src="'.dol_buildpath('/advancedinventory/js/setup.js', 1).'"></script>';

llxFooter();
$db->close();
?>
