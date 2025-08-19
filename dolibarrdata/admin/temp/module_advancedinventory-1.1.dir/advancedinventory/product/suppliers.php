<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    advancedinventory/product/suppliers.php
 * \ingroup advancedinventory
 * \brief   Multiple suppliers management for products
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

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/advancedinventory/class/supplieritem.class.php';

// Load translation files
$langs->loadLangs(array("advancedinventory@advancedinventory", "products", "suppliers", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

$supplier_id = GETPOST('supplier_id', 'int');
$supplier_item_id = GETPOST('supplier_item_id', 'int');

// Initialize objects
$product = new Product($db);
$form = new Form($db);
$formcompany = new FormCompany($db);
$formother = new FormOther($db);

// Fetch product
if (($id > 0 || !empty($ref))) {
	$result = $product->fetch($id, $ref);
	if ($result < 0) {
		dol_print_error($db, $product->error);
		exit;
	}
	$id = $product->id;
}

if (empty($id)) {
	accessforbidden('Product ID is required');
}

// Security check
$result = restrictedArea($user, 'produit|service', $product->id, 'product&product', '', '', 'rowid', 0);

// Check module permissions
if (!$user->hasRight('advancedinventory', 'catalog', 'read')) {
	accessforbidden();
}

$permissiontoadd = $user->hasRight('advancedinventory', 'catalog', 'write');

/*
 * Actions
 */

$parameters = array('id' => $id, 'ref' => $ref);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $product, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {

	// Cancel
	if (GETPOST('cancel', 'alpha') && !empty($backtopage)) {
		header("Location: ".$backtopage);
		exit;
	}

	// Add supplier item
	if ($action == 'add_supplier' && $permissiontoadd) {
		$error = 0;

		$supplier_item = new AdvancedInventorySupplierItem($db);
		$supplier_item->fk_product = $id;
		$supplier_item->fk_soc = GETPOST('fk_soc', 'int');
		$supplier_item->supplier_part_num = GETPOST('supplier_part_num', 'alpha');
		$supplier_item->supplier_label = GETPOST('supplier_label', 'alpha');
		$supplier_item->lead_time_days = GETPOST('lead_time_days', 'int');
		$supplier_item->min_order_qty = price2num(GETPOST('min_order_qty', 'alpha'));
		$supplier_item->price = price2num(GETPOST('price', 'alpha'));
		$supplier_item->fk_multicurrency = GETPOST('fk_multicurrency', 'int');
		$supplier_item->multicurrency_price = price2num(GETPOST('multicurrency_price', 'alpha'));
		$supplier_item->is_default = GETPOST('is_default', 'int') ? 1 : 0;
		$supplier_item->quality_rating = GETPOST('quality_rating', 'int');
		$supplier_item->delivery_rating = GETPOST('delivery_rating', 'int');
		$supplier_item->status = GETPOST('status', 'int');
		$supplier_item->note_public = GETPOST('note_public', 'restricthtml');
		$supplier_item->note_private = GETPOST('note_private', 'restricthtml');

		// Validation
		if (empty($supplier_item->fk_soc)) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("Supplier")), null, 'errors');
		}

		if (!$error) {
			$db->begin();

			$result = $supplier_item->create($user);

			if ($result > 0) {
				$db->commit();
				setEventMessages($langs->trans("SupplierItemAdded"), null, 'mesgs');
				header("Location: ".$_SERVER["PHP_SELF"]."?id=".$id);
				exit;
			} else {
				$db->rollback();
				setEventMessages($supplier_item->error, $supplier_item->errors, 'errors');
			}
		}
	}

	// Update supplier item
	elseif ($action == 'update_supplier' && $permissiontoadd) {
		$error = 0;

		$supplier_item = new AdvancedInventorySupplierItem($db);
		$result = $supplier_item->fetch($supplier_item_id);

		if ($result > 0) {
			$supplier_item->supplier_part_num = GETPOST('supplier_part_num', 'alpha');
			$supplier_item->supplier_label = GETPOST('supplier_label', 'alpha');
			$supplier_item->lead_time_days = GETPOST('lead_time_days', 'int');
			$supplier_item->min_order_qty = price2num(GETPOST('min_order_qty', 'alpha'));
			$supplier_item->price = price2num(GETPOST('price', 'alpha'));
			$supplier_item->fk_multicurrency = GETPOST('fk_multicurrency', 'int');
			$supplier_item->multicurrency_price = price2num(GETPOST('multicurrency_price', 'alpha'));
			$supplier_item->is_default = GETPOST('is_default', 'int') ? 1 : 0;
			$supplier_item->quality_rating = GETPOST('quality_rating', 'int');
			$supplier_item->delivery_rating = GETPOST('delivery_rating', 'int');
			$supplier_item->status = GETPOST('status', 'int');
			$supplier_item->note_public = GETPOST('note_public', 'restricthtml');
			$supplier_item->note_private = GETPOST('note_private', 'restricthtml');

			if (!$error) {
				$db->begin();

				$result = $supplier_item->update($user);

				if ($result > 0) {
					$db->commit();
					setEventMessages($langs->trans("SupplierItemUpdated"), null, 'mesgs');
					header("Location: ".$_SERVER["PHP_SELF"]."?id=".$id);
					exit;
				} else {
					$db->rollback();
					setEventMessages($supplier_item->error, $supplier_item->errors, 'errors');
				}
			}
		}
	}

	// Delete supplier item
	elseif ($action == 'confirm_delete' && $confirm == 'yes' && $permissiontoadd) {
		$supplier_item = new AdvancedInventorySupplierItem($db);
		$result = $supplier_item->fetch($supplier_item_id);

		if ($result > 0) {
			$db->begin();

			$result = $supplier_item->delete($user);

			if ($result > 0) {
				$db->commit();
				setEventMessages($langs->trans("SupplierItemDeleted"), null, 'mesgs');
				header("Location: ".$_SERVER["PHP_SELF"]."?id=".$id);
				exit;
			} else {
				$db->rollback();
				setEventMessages($supplier_item->error, $supplier_item->errors, 'errors');
			}
		}
	}

	// Set as default
	elseif ($action == 'set_default' && $permissiontoadd) {
		$supplier_item = new AdvancedInventorySupplierItem($db);
		$result = $supplier_item->fetch($supplier_item_id);

		if ($result > 0) {
			$db->begin();

			$supplier_item->is_default = 1;
			$result = $supplier_item->update($user);

			if ($result > 0) {
				$db->commit();
				setEventMessages($langs->trans("DefaultSupplierSet"), null, 'mesgs');
			} else {
				$db->rollback();
				setEventMessages($supplier_item->error, $supplier_item->errors, 'errors');
			}
		}
	}
}

/*
 * View
 */

$title = $langs->trans('Product').' - '.$langs->trans('Suppliers');
$help_url = '';

llxHeader('', $title, $help_url);

// Product header
$head = product_prepare_head($product);
$titre = $langs->trans("CardProduct".$product->type);
$picto = ($product->type == Product::TYPE_SERVICE ? 'service' : 'product');

print dol_get_fiche_head($head, 'advancedinventory_suppliers', $titre, -1, $picto);

// Product ref
$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

$shownav = 1;
if ($user->socid && !in_array('product', explode(',', $conf->global->MAIN_MODULES_FOR_EXTERNAL))) {
	$shownav = 0;
}

dol_banner_tab($product, 'ref', $linkback, $shownav, 'ref');

print '<div class="fichecenter">';

print '<div class="underbanner clearboth"></div>';
print '<table class="border centpercent tableforfield">';

// Type
if (isModEnabled("product") && isModEnabled("service")) {
	$typeformat = 'select;0:'.$langs->trans("Product").',1:'.$langs->trans("Service");
	print '<tr><td class="titlefield">'.$langs->trans("Type").'</td><td>';
	print $langs->trans($product->type ? 'Service' : 'Product');
	print '</td></tr>';
}

print '</table>';

print '</div>';
print dol_get_fiche_end();

// Confirm delete dialog
if ($action == 'delete_supplier') {
	$supplier_item = new AdvancedInventorySupplierItem($db);
	$supplier_item->fetch($supplier_item_id);

	// Get supplier info
	$supplier = new Societe($db);
	$supplier->fetch($supplier_item->fk_soc);

	print $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$id.'&supplier_item_id='.$supplier_item_id,
		$langs->trans('DeleteSupplierItem'),
		$langs->trans('ConfirmDeleteSupplierItem', $supplier->name),
		'confirm_delete', '', 0, 1);
}

// Get suppliers list
$suppliers = AdvancedInventorySupplierItem::getSuppliersByProduct($db, $id, -1); // -1 = all statuses

// Add new supplier form
if ($action == 'create') {
	print load_fiche_titre($langs->trans("AddSupplier"), '', '');

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add_supplier">';
	print '<input type="hidden" name="id" value="'.$id.'">';

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';

	// Supplier
	print '<tr><td class="titlefield fieldrequired">'.$langs->trans("Supplier").'</td>';
	print '<td colspan="3">';
	print $form->select_company('', 'fk_soc', 's.fournisseur = 1', 'SelectSupplier', 0, 0, array(), 0, 'minwidth300');
	print '</td></tr>';

	// Supplier part number
	print '<tr><td>'.$langs->trans("SupplierPartNumber").'</td>';
	print '<td><input type="text" class="flat" name="supplier_part_num" size="20" maxlength="128" value=""></td>';

	// Lead time
	print '<td>'.$langs->trans("LeadTimeDays").'</td>';
	print '<td><input type="number" class="flat" name="lead_time_days" size="5" min="0" value="0"></td></tr>';

	// Supplier label
	print '<tr><td>'.$langs->trans("SupplierLabel").'</td>';
	print '<td><input type="text" class="flat" name="supplier_label" size="30" maxlength="255" value=""></td>';

	// Min order qty
	print '<td>'.$langs->trans("MinOrderQty").'</td>';
	print '<td><input type="text" class="flat" name="min_order_qty" size="10" value="1"></td></tr>';

	// Price
	print '<tr><td>'.$langs->trans("Price").'</td>';
	print '<td><input type="text" class="flat" name="price" size="15" value="0"></td>';

	// Currency
	print '<td>'.$langs->trans("Currency").'</td>';
	print '<td>';
	print $form->selectCurrency('', 'fk_multicurrency');
	print '</td></tr>';

	// Quality rating
	print '<tr><td>'.$langs->trans("QualityRating").' (1-5)</td>';
	print '<td>';
	print '<select name="quality_rating" class="flat">';
	for ($i = 0; $i <= 5; $i++) {
		print '<option value="'.$i.'"'.($i == 0 ? ' selected' : '').'>'.$i.($i == 0 ? ' ('.$langs->trans("NotRated").')' : '').'</option>';
	}
	print '</select>';
	print '</td>';

	// Delivery rating
	print '<td>'.$langs->trans("DeliveryRating").' (1-5)</td>';
	print '<td>';
	print '<select name="delivery_rating" class="flat">';
	for ($i = 0; $i <= 5; $i++) {
		print '<option value="'.$i.'"'.($i == 0 ? ' selected' : '').'>'.$i.($i == 0 ? ' ('.$langs->trans("NotRated").')' : '').'</option>';
	}
	print '</select>';
	print '</td></tr>';

	// Default supplier
	print '<tr><td>'.$langs->trans("DefaultSupplier").'</td>';
	print '<td><input type="checkbox" name="is_default" value="1"'.($suppliers ? '' : ' checked').'></td>';

	// Status
	print '<td>'.$langs->trans("Status").'</td>';
	print '<td>';
	print $form->selectarray('status', array('0' => $langs->trans('Disabled'), '1' => $langs->trans('Enabled')), 1, 0);
	print '</td></tr>';

	// Public note
	print '<tr><td class="tdtop">'.$langs->trans("NotePublic").'</td>';
	print '<td colspan="3">';
	print '<textarea name="note_public" class="flat" cols="80" rows="3"></textarea>';
	print '</td></tr>';

	// Private note
	print '<tr><td class="tdtop">'.$langs->trans("NotePrivate").'</td>';
	print '<td colspan="3">';
	print '<textarea name="note_private" class="flat" cols="80" rows="3"></textarea>';
	print '</td></tr>';

	print '</table>';
	print '</div>';

	// Buttons
	print '<div class="center">';
	print '<input type="submit" class="button button-save" value="'.$langs->trans("Add").'">';
	print '&nbsp;&nbsp;&nbsp;';
	print '<input type="button" class="button button-cancel" value="'.$langs->trans("Cancel").'" onClick="javascript:history.go(-1)">';
	print '</div>';

	print '</form>';

	print '<br>';
}

// Edit supplier form
elseif ($action == 'edit_supplier') {
	$supplier_item = new AdvancedInventorySupplierItem($db);
	$supplier_item->fetch($supplier_item_id);

	$supplier = new Societe($db);
	$supplier->fetch($supplier_item->fk_soc);

	print load_fiche_titre($langs->trans("EditSupplier").' - '.$supplier->name, '', '');

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update_supplier">';
	print '<input type="hidden" name="id" value="'.$id.'">';
	print '<input type="hidden" name="supplier_item_id" value="'.$supplier_item_id.'">';

	print '<div class="div-table-responsive-no-min">';
	print '<table class="noborder centpercent">';

	// Supplier (read-only)
	print '<tr><td class="titlefield">'.$langs->trans("Supplier").'</td>';
	print '<td colspan="3"><strong>'.$supplier->name.'</strong></td></tr>';

	// Supplier part number
	print '<tr><td>'.$langs->trans("SupplierPartNumber").'</td>';
	print '<td><input type="text" class="flat" name="supplier_part_num" size="20" maxlength="128" value="'.$supplier_item->supplier_part_num.'"></td>';

	// Lead time
	print '<td>'.$langs->trans("LeadTimeDays").'</td>';
	print '<td><input type="number" class="flat" name="lead_time_days" size="5" min="0" value="'.$supplier_item->lead_time_days.'"></td></tr>';

	// Supplier label
	print '<tr><td>'.$langs->trans("SupplierLabel").'</td>';
	print '<td><input type="text" class="flat" name="supplier_label" size="30" maxlength="255" value="'.$supplier_item->supplier_label.'"></td>';

	// Min order qty
	print '<td>'.$langs->trans("MinOrderQty").'</td>';
	print '<td><input type="text" class="flat" name="min_order_qty" size="10" value="'.$supplier_item->min_order_qty.'"></td></tr>';

	// Price
	print '<tr><td>'.$langs->trans("Price").'</td>';
	print '<td><input type="text" class="flat" name="price" size="15" value="'.$supplier_item->price.'"></td>';

	// Currency
	print '<td>'.$langs->trans("Currency").'</td>';
	print '<td>';
	print $form->selectCurrency($supplier_item->fk_multicurrency, 'fk_multicurrency');
	print '</td></tr>';

	// Quality rating
	print '<tr><td>'.$langs->trans("QualityRating").' (1-5)</td>';
	print '<td>';
	print '<select name="quality_rating" class="flat">';
	for ($i = 0; $i <= 5; $i++) {
		print '<option value="'.$i.'"'.($i == $supplier_item->quality_rating ? ' selected' : '').'>'.$i.($i == 0 ? ' ('.$langs->trans("NotRated").')' : '').'</option>';
	}
	print '</select>';
	print '</td>';

	// Delivery rating
	print '<td>'.$langs->trans("DeliveryRating").' (1-5)</td>';
	print '<td>';
	print '<select name="delivery_rating" class="flat">';
	for ($i = 0; $i <= 5; $i++) {
		print '<option value="'.$i.'"'.($i == $supplier_item->delivery_rating ? ' selected' : '').'>'.$i.($i == 0 ? ' ('.$langs->trans("NotRated").')' : '').'</option>';
	}
	print '</select>';
	print '</td></tr>';

	// Default supplier
	print '<tr><td>'.$langs->trans("DefaultSupplier").'</td>';
	print '<td><input type="checkbox" name="is_default" value="1"'.($supplier_item->is_default ? ' checked' : '').'></td>';

	// Status
	print '<td>'.$langs->trans("Status").'</td>';
	print '<td>';
	print $form->selectarray('status', array('0' => $langs->trans('Disabled'), '1' => $langs->trans('Enabled')), $supplier_item->status, 0);
	print '</td></tr>';

	// Public note
	print '<tr><td class="tdtop">'.$langs->trans("NotePublic").'</td>';
	print '<td colspan="3">';
	print '<textarea name="note_public" class="flat" cols="80" rows="3">'.$supplier_item->note_public.'</textarea>';
	print '</td></tr>';

	// Private note
	print '<tr><td class="tdtop">'.$langs->trans("NotePrivate").'</td>';
	print '<td colspan="3">';
	print '<textarea name="note_private" class="flat" cols="80" rows="3">'.$supplier_item->note_private.'</textarea>';
	print '</td></tr>';

	print '</table>';
	print '</div>';

	// Buttons
	print '<div class="center">';
	print '<input type="submit" class="button button-save" value="'.$langs->trans("Save").'">';
	print '&nbsp;&nbsp;&nbsp;';
	print '<input type="button" class="button button-cancel" value="'.$langs->trans("Cancel").'" onClick="javascript:history.go(-1)">';
	print '</div>';

	print '</form>';

	print '<br>';
}

// Suppliers list
$newcardbutton = '';
if ($permissiontoadd && $action != 'create' && $action != 'edit_supplier') {
	$newcardbutton = dolGetButtonTitle($langs->trans('AddSupplier'), '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?id='.$id.'&action=create', '', 1);
}

print load_fiche_titre($langs->trans("SuppliersForThisProduct"), $newcardbutton, '');

if (count($suppliers) > 0) {
	print '<div class="div-table-responsive">';
	print '<table class="noborder centpercent">';

	// Header
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Supplier").'</td>';
	print '<td>'.$langs->trans("SupplierPartNumber").'</td>';
	print '<td>'.$langs->trans("SupplierLabel").'</td>';
	print '<td class="center">'.$langs->trans("LeadTime").'</td>';
	print '<td class="center">'.$langs->trans("MinOrderQty").'</td>';
	print '<td class="right">'.$langs->trans("Price").'</td>';
	print '<td class="center">'.$langs->trans("Quality").'</td>';
	print '<td class="center">'.$langs->trans("Delivery").'</td>';
	print '<td class="center">'.$langs->trans("Default").'</td>';
	print '<td class="center">'.$langs->trans("Status").'</td>';
	print '<td class="center">'.$langs->trans("LastOrder").'</td>';
	print '<td class="center">'.$langs->trans("Action").'</td>';
	print '</tr>';

	// Loop through suppliers
	foreach ($suppliers as $supplier_obj) {
		$supplier = new Societe($db);
		$supplier->fetch($supplier_obj->fk_soc);

		$supplier_item = new AdvancedInventorySupplierItem($db);
		$supplier_item->fetch($supplier_obj->rowid);

		print '<tr class="oddeven">';

		// Supplier name
		print '<td>';
		print $supplier->getNomUrl(1);
		if ($supplier_obj->code_fournisseur) {
			print ' ('.$supplier_obj->code_fournisseur.')';
		}
		print '</td>';

		// Part number
		print '<td>';
		if ($supplier_obj->supplier_part_num) {
			print '<span class="badge badge-info">'.$supplier_obj->supplier_part_num.'</span>';
		} else {
			print '-';
		}
		print '</td>';

		// Supplier label
		print '<td>'.(empty($supplier_obj->supplier_label) ? '-' : $supplier_obj->supplier_label).'</td>';

		// Lead time
		print '<td class="center">'.$supplier_item->getLeadTimeFormatted().'</td>';

		// Min order qty
		print '<td class="center">'.($supplier_obj->min_order_qty > 0 ? $supplier_obj->min_order_qty : '-').'</td>';

		// Price
		print '<td class="right">';
		if ($supplier_obj->price > 0) {
			print price($supplier_obj->price, 0, $langs, 1, -1, -1, $conf->currency);
		} else {
			print '-';
		}
		print '</td>';

		// Quality rating
		print '<td class="center">';
		if ($supplier_obj->quality_rating > 0) {
			print $supplier_item->getRatingStars($supplier_obj->quality_rating);
		} else {
			print '-';
		}
		print '</td>';

		// Delivery rating
		print '<td class="center">';
		if ($supplier_obj->delivery_rating > 0) {
			print $supplier_item->getRatingStars($supplier_obj->delivery_rating);
		} else {
			print '-';
		}
		print '</td>';

		// Default
		print '<td class="center">';
		if ($supplier_obj->is_default) {
			print '<span class="badge badge-success">'.$langs->trans("Yes").'</span>';
		} else {
			if ($permissiontoadd) {
				print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$id.'&action=set_default&supplier_item_id='.$supplier_obj->rowid.'&token='.newToken().'" class="button button-small">'.$langs->trans("SetDefault").'</a>';
			} else {
				print $langs->trans("No");
			}
		}
		print '</td>';

		// Status
		print '<td class="center">'.$supplier_item->getLibStatut(5).'</td>';

		// Last order
		print '<td class="center">';
		if ($supplier_obj->last_order_date) {
			print dol_print_date($db->jdate($supplier_obj->last_order_date), 'day');
		} else {
			print '-';
		}
		print '</td>';

		// Actions
		print '<td class="center nowraponall">';
		if ($permissiontoadd) {
			print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$id.'&action=edit_supplier&supplier_item_id='.$supplier_obj->rowid.'" class="editfielda" title="'.$langs->trans("Edit").'">'.img_edit().'</a>';
			print ' ';
			print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$id.'&action=delete_supplier&supplier_item_id='.$supplier_obj->rowid.'" class="deletefielda" title="'.$langs->trans("Delete").'">'.img_delete().'</a>';
		}
		print '</td>';

		print '</tr>';
	}

	print '</table>';
	print '</div>';
} else {
	print '<div class="opacitymedium">'.$langs->trans("NoSuppliersConfigured").'</div>';
	if ($permissiontoadd) {
		print '<br><div class="center">';
		print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$id.'&action=create" class="button">'.$langs->trans("AddFirstSupplier").'</a>';
		print '</div>';
	}
}

// End of page
llxFooter();
$db->close();
