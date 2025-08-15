<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    advancedinventory/warehouse/card.php
 * \ingroup advancedinventory
 * \brief   Card page for warehouse (create/edit)
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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/advancedinventory/class/warehouse.class.php';

// Load translation files
$langs->loadLangs(array("advancedinventory@advancedinventory", "companies", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize objects
$object = new AdvancedInventoryWarehouse($db);
$form = new Form($db);
$formcompany = new FormCompany($db);

// Fetch object if id or ref is provided
if (($id > 0 || !empty($ref)) && $action != 'create') {
	$result = $object->fetch($id, $ref);
	if ($result < 0) {
		dol_print_error($db, $object->error);
		exit;
	}
	$id = $object->id;
}

// Security check
$permissiontoread = $user->hasRight('advancedinventory', 'warehouse', 'read');
$permissiontoadd = $user->hasRight('advancedinventory', 'warehouse', 'write');
$permissiontodelete = $user->hasRight('advancedinventory', 'warehouse', 'write');

if (!$permissiontoread) {
	accessforbidden();
}

/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {

	// Cancel
	if (GETPOST('cancel', 'alpha') && !empty($backtopage)) {
		header("Location: ".$backtopage);
		exit;
	}

	// Create warehouse
	if ($action == 'add' && $permissiontoadd) {
		$error = 0;

		// Get values from form
		$object->ref = GETPOST('ref', 'alpha');
		$object->label = GETPOST('label', 'alpha');
		$object->description = GETPOST('description', 'restricthtml');
		$object->address = GETPOST('address', 'alpha');
		$object->zip = GETPOST('zip', 'alpha');
		$object->town = GETPOST('town', 'alpha');
		$object->fk_country = GETPOST('country_id', 'int');
		$object->phone = GETPOST('phone', 'alpha');
		$object->email = GETPOST('email', 'alpha');
		$object->fk_parent = GETPOST('fk_parent', 'int');
		$object->warehouse_type = GETPOST('warehouse_type', 'alpha');
		$object->status = GETPOST('status', 'int');

		// Check required fields
		if (empty($object->label)) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("Label")), null, 'errors');
		}

		if (!$error) {
			$db->begin();

			$result = $object->create($user);

			if ($result > 0) {
				$db->commit();

				// Redirect to new object
				header("Location: ".DOL_URL_ROOT.'/custom/advancedinventory/warehouse/card.php?id='.$object->id);
				exit;
			} else {
				$db->rollback();
				setEventMessages($object->error, $object->errors, 'errors');
				$action = 'create';
			}
		} else {
			$action = 'create';
		}
	}

	// Update warehouse
	elseif ($action == 'update' && $permissiontoadd) {
		$error = 0;

		// Get values from form
		$object->ref = GETPOST('ref', 'alpha');
		$object->label = GETPOST('label', 'alpha');
		$object->description = GETPOST('description', 'restricthtml');
		$object->address = GETPOST('address', 'alpha');
		$object->zip = GETPOST('zip', 'alpha');
		$object->town = GETPOST('town', 'alpha');
		$object->fk_country = GETPOST('country_id', 'int');
		$object->phone = GETPOST('phone', 'alpha');
		$object->email = GETPOST('email', 'alpha');
		$object->fk_parent = GETPOST('fk_parent', 'int');
		$object->warehouse_type = GETPOST('warehouse_type', 'alpha');
		$object->status = GETPOST('status', 'int');

		// Check required fields
		if (empty($object->label)) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("Label")), null, 'errors');
		}

		// Prevent setting itself as parent
		if ($object->fk_parent == $object->id) {
			$error++;
			setEventMessages($langs->trans("WarehouseCannotBeItsOwnParent"), null, 'errors');
		}

		if (!$error) {
			$db->begin();

			$result = $object->update($user);

			if ($result > 0) {
				$db->commit();
				setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
			} else {
				$db->rollback();
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}
	}

	// Delete warehouse
	elseif ($action == 'confirm_delete' && $confirm == 'yes' && $permissiontodelete) {
		$db->begin();

		$result = $object->delete($user);

		if ($result > 0) {
			$db->commit();
			header("Location: ".DOL_URL_ROOT.'/custom/advancedinventory/warehouse/list.php');
			exit;
		} else {
			$db->rollback();
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
}

/*
 * View
 */

$title = $langs->trans("Warehouse");
$help_url = '';

llxHeader('', $title, $help_url);

// Create mode
if ($action == 'create') {
	print load_fiche_titre($langs->trans("NewWarehouse"), '', 'stock');

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">';

	// Ref (auto-generated)
	print '<tr><td class="titlefieldcreate">'.$langs->trans("Ref").'</td>';
	print '<td>'.$langs->trans("AutoGenerated").'</td></tr>';

	// Label
	print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td>';
	print '<td><input type="text" class="flat" name="label" size="40" maxlength="255" value="'.GETPOST('label', 'alpha').'"></td></tr>';

	// Type
	print '<tr><td>'.$langs->trans("WarehouseType").'</td>';
	print '<td>';
	$warehouse_types = array(
		'main' => $langs->trans('MainWarehouse'),
		'hall' => $langs->trans('Hall'),
		'shelf' => $langs->trans('Shelf'),
		'box' => $langs->trans('Box')
	);
	print $form->selectarray('warehouse_type', $warehouse_types, GETPOST('warehouse_type', 'alpha') ?: 'main', 0);
	print '</td></tr>';

	// Parent warehouse
	print '<tr><td>'.$langs->trans("ParentWarehouse").'</td>';
	print '<td>';
	print '<select name="fk_parent" class="flat minwidth200">';
	print '<option value="0">'.$langs->trans("None").'</option>';

	// Get all warehouses for parent selection
	$sql = "SELECT rowid, ref, label, warehouse_type";
	$sql .= " FROM ".MAIN_DB_PREFIX."advancedinventory_warehouse";
	$sql .= " WHERE status = 1";
	$sql .= " ORDER BY label ASC";

	$resql = $db->query($sql);
	if ($resql) {
		while ($obj = $db->fetch_object($resql)) {
			print '<option value="'.$obj->rowid.'"';
			if (GETPOST('fk_parent', 'int') == $obj->rowid) {
				print ' selected';
			}
			print '>'.$obj->ref.' - '.$obj->label.' ('.$warehouse_types[$obj->warehouse_type].')</option>';
		}
		$db->free($resql);
	}
	print '</select>';
	print '</td></tr>';

	// Description
	print '<tr><td class="tdtop">'.$langs->trans("Description").'</td>';
	print '<td>';
	print '<textarea name="description" class="flat" cols="60" rows="4">'.GETPOST('description', 'restricthtml').'</textarea>';
	print '</td></tr>';

	// Address
	print '<tr><td>'.$langs->trans("Address").'</td>';
	print '<td><input type="text" class="flat" name="address" size="40" maxlength="255" value="'.GETPOST('address', 'alpha').'"></td></tr>';

	// Zip
	print '<tr><td>'.$langs->trans("Zip").'</td>';
	print '<td><input type="text" class="flat" name="zip" size="10" maxlength="25" value="'.GETPOST('zip', 'alpha').'"></td></tr>';

	// Town
	print '<tr><td>'.$langs->trans("Town").'</td>';
	print '<td><input type="text" class="flat" name="town" size="30" maxlength="50" value="'.GETPOST('town', 'alpha').'"></td></tr>';

	// Country
	print '<tr><td>'.$langs->trans("Country").'</td>';
	print '<td>';
	print $form->select_country(GETPOST('country_id', 'int') ?: $conf->global->MAIN_INFO_SOCIETE_COUNTRY, 'country_id');
	print '</td></tr>';

	// Phone
	print '<tr><td>'.$langs->trans("Phone").'</td>';
	print '<td><input type="text" class="flat" name="phone" size="20" maxlength="20" value="'.GETPOST('phone', 'alpha').'"></td></tr>';

	// Email
	print '<tr><td>'.$langs->trans("Email").'</td>';
	print '<td><input type="text" class="flat" name="email" size="40" maxlength="128" value="'.GETPOST('email', 'alpha').'"></td></tr>';

	// Status
	print '<tr><td>'.$langs->trans("Status").'</td>';
	print '<td>';
	print $form->selectarray('status', array('0' => $langs->trans('Disabled'), '1' => $langs->trans('Enabled')), GETPOST('status', 'int') !== '' ? GETPOST('status', 'int') : 1, 0);
	print '</td></tr>';

	print '</table>';

	print dol_get_fiche_end();

	// Buttons
	print '<div class="center">';
	print '<input type="submit" class="button button-save" value="'.$langs->trans("Save").'">';
	print '&nbsp;&nbsp;&nbsp;';
	print '<input type="button" class="button button-cancel" value="'.$langs->trans("Cancel").'" onClick="javascript:history.go(-1)">';
	print '</div>';

	print '</form>';
}

// View/Edit mode
elseif ($object->id > 0) {
	$head = array();
	$head[0][0] = DOL_URL_ROOT.'/custom/advancedinventory/warehouse/card.php?id='.$object->id;
	$head[0][1] = $langs->trans("Card");
	$head[0][2] = 'card';

	print dol_get_fiche_head($head, 'card', $langs->trans("Warehouse"), -1, 'stock');

	// Confirm delete
	if ($action == 'delete') {
		print $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteWarehouse'), $langs->trans('ConfirmDeleteWarehouse', $object->ref), 'confirm_delete', '', 0, 1);
	}

	// Object card
	if ($action == 'edit') {
		// Edit mode
		print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';

		print '<table class="border centpercent">';

		// Ref
		print '<tr><td class="titlefield">'.$langs->trans("Ref").'</td>';
		print '<td>'.$object->ref.'</td></tr>';

		// Label
		print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td>';
		print '<td><input type="text" class="flat" name="label" size="40" value="'.$object->label.'"></td></tr>';

		// Type
		print '<tr><td>'.$langs->trans("WarehouseType").'</td>';
		print '<td>';
		$warehouse_types = array(
			'main' => $langs->trans('MainWarehouse'),
			'hall' => $langs->trans('Hall'),
			'shelf' => $langs->trans('Shelf'),
			'box' => $langs->trans('Box')
		);
		print $form->selectarray('warehouse_type', $warehouse_types, $object->warehouse_type, 0);
		print '</td></tr>';

		// Parent
		print '<tr><td>'.$langs->trans("ParentWarehouse").'</td>';
		print '<td>';
		print '<select name="fk_parent" class="flat minwidth200">';
		print '<option value="0">'.$langs->trans("None").'</option>';

		$sql = "SELECT rowid, ref, label, warehouse_type";
		$sql .= " FROM ".MAIN_DB_PREFIX."advancedinventory_warehouse";
		$sql .= " WHERE status = 1";
		$sql .= " AND rowid != ".$object->id; // Exclude self
		$sql .= " ORDER BY label ASC";

		$resql = $db->query($sql);
		if ($resql) {
			while ($obj = $db->fetch_object($resql)) {
				print '<option value="'.$obj->rowid.'"';
				if ($object->fk_parent == $obj->rowid) {
					print ' selected';
				}
				print '>'.$obj->ref.' - '.$obj->label.'</option>';
			}
			$db->free($resql);
		}
		print '</select>';
		print '</td></tr>';

		// Description
		print '<tr><td class="tdtop">'.$langs->trans("Description").'</td>';
		print '<td>';
		print '<textarea name="description" class="flat" cols="60" rows="4">'.$object->description.'</textarea>';
		print '</td></tr>';

		// Address fields...
		print '<tr><td>'.$langs->trans("Address").'</td>';
		print '<td><input type="text" class="flat" name="address" size="40" value="'.$object->address.'"></td></tr>';

		print '<tr><td>'.$langs->trans("Zip").'</td>';
		print '<td><input type="text" class="flat" name="zip" size="10" value="'.$object->zip.'"></td></tr>';

		print '<tr><td>'.$langs->trans("Town").'</td>';
		print '<td><input type="text" class="flat" name="town" size="30" value="'.$object->town.'"></td></tr>';

		print '<tr><td>'.$langs->trans("Country").'</td>';
		print '<td>';
		print $form->select_country($object->fk_country ?: $conf->global->MAIN_INFO_SOCIETE_COUNTRY, 'country_id');
		print '</td></tr>';

		print '<tr><td>'.$langs->trans("Phone").'</td>';
		print '<td><input type="text" class="flat" name="phone" size="20" value="'.$object->phone.'"></td></tr>';

		print '<tr><td>'.$langs->trans("Email").'</td>';
		print '<td><input type="text" class="flat" name="email" size="40" value="'.$object->email.'"></td></tr>';

		// Status
		print '<tr><td>'.$langs->trans("Status").'</td>';
		print '<td>';
		print $form->selectarray('status', array('0' => $langs->trans('Disabled'), '1' => $langs->trans('Enabled')), $object->status, 0);
		print '</td></tr>';

		print '</table>';

		// Buttons
		print '<div class="center">';
		print '<input type="submit" class="button button-save" value="'.$langs->trans("Save").'">';
		print '&nbsp;&nbsp;&nbsp;';
		print '<input type="button" class="button button-cancel" value="'.$langs->trans("Cancel").'" onClick="javascript:history.go(-1)">';
		print '</div>';

		print '</form>';
	} else {
		// View mode
		print '<table class="border centpercent">';

		// Ref
		print '<tr><td class="titlefield">'.$langs->trans("Ref").'</td>';
		print '<td>'.$object->ref.'</td></tr>';

		// Label
		print '<tr><td>'.$langs->trans("Label").'</td>';
		print '<td>'.$object->label.'</td></tr>';

		// Type
		print '<tr><td>'.$langs->trans("WarehouseType").'</td>';
		print '<td>'.$object->getTypeLabel().'</td></tr>';

		// Parent
		print '<tr><td>'.$langs->trans("ParentWarehouse").'</td>';
		print '<td>';
		if ($object->fk_parent > 0) {
			$parent = new AdvancedInventoryWarehouse($db);
			$parent->fetch($object->fk_parent);
			print $parent->ref.' - '.$parent->label;
		} else {
			print $langs->trans("None");
		}
		print '</td></tr>';

		// Full path
		print '<tr><td>'.$langs->trans("FullPath").'</td>';
		print '<td>'.$object->getFullPath(' > ').'</td></tr>';

		// Description
		if ($object->description) {
			print '<tr><td class="tdtop">'.$langs->trans("Description").'</td>';
			print '<td>'.nl2br($object->description).'</td></tr>';
		}

		// Address
		if ($object->address) {
			print '<tr><td>'.$langs->trans("Address").'</td>';
			print '<td>'.$object->address.'</td></tr>';
		}

		// Zip/Town
		if ($object->zip || $object->town) {
			print '<tr><td>'.$langs->trans("Zip").' / '.$langs->trans("Town").'</td>';
			print '<td>'.$object->zip.' '.$object->town.'</td></tr>';
		}

		// Country
		if ($object->fk_country > 0) {
			print '<tr><td>'.$langs->trans("Country").'</td>';
			print '<td>';
			$tmparray = getCountry($object->fk_country, 'all');
			print $tmparray['label'];
			print '</td></tr>';
		}

		// Phone
		if ($object->phone) {
			print '<tr><td>'.$langs->trans("Phone").'</td>';
			print '<td>'.dol_print_phone($object->phone).'</td></tr>';
		}

		// Email
		if ($object->email) {
			print '<tr><td>'.$langs->trans("Email").'</td>';
			print '<td>'.dol_print_email($object->email).'</td></tr>';
		}

		// Status
		print '<tr><td>'.$langs->trans("Status").'</td>';
		print '<td>'.$object->getLibStatut(5).'</td></tr>';

		// Creation info
		print '<tr><td>'.$langs->trans("DateCreation").'</td>';
		print '<td>'.dol_print_date($object->date_creation, 'dayhour').'</td></tr>';

		// Modification info
		print '<tr><td>'.$langs->trans("DateModification").'</td>';
		print '<td>'.dol_print_date($object->tms, 'dayhour').'</td></tr>';

		print '</table>';

		print dol_get_fiche_end();

		// Buttons
		print '<div class="tabsAction">';

		// Edit
		if ($permissiontoadd) {
			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken().'">'.$langs->trans("Modify").'</a>';
		}

		// Delete
		if ($permissiontodelete) {
			print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=delete&token='.newToken().'">'.$langs->trans("Delete").'</a>';
		}

		print '</div>';

		// Show children warehouses
		$children = $object->getChildren();
		if (count($children) > 0) {
			print '<br>';
			print load_fiche_titre($langs->trans("ChildWarehouses"), '', '');

			print '<table class="noborder centpercent">';
			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("Ref").'</td>';
			print '<td>'.$langs->trans("Label").'</td>';
			print '<td>'.$langs->trans("Type").'</td>';
			print '<td>'.$langs->trans("Status").'</td>';
			print '</tr>';

			foreach ($children as $child) {
				print '<tr class="oddeven">';
				print '<td><a href="'.DOL_URL_ROOT.'/custom/advancedinventory/warehouse/card.php?id='.$child->id.'">'.$child->ref.'</a></td>';
				print '<td>'.$child->label.'</td>';
				print '<td>'.$child->getTypeLabel().'</td>';
				print '<td>'.$child->getLibStatut(5).'</td>';
				print '</tr>';
			}

			print '</table>';
		}
	}
}

// End of page
llxFooter();
$db->close();
