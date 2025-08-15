<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \file    advancedinventory/warehouse/list.php
 * \ingroup advancedinventory
 * \brief   List page for warehouses
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
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/advancedinventory/class/warehouse.class.php';

// Load translation files
$langs->loadLangs(array("advancedinventory@advancedinventory", "other"));

// Get parameters
$action = GETPOST('action', 'aZ09');
$massaction = GETPOST('massaction', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$toselect = GETPOST('toselect', 'array');

$search_ref = GETPOST('search_ref', 'alpha');
$search_label = GETPOST('search_label', 'alpha');
$search_type = GETPOST('search_type', 'alpha');
$search_status = GETPOST('search_status', 'int');

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');

if (empty($page) || $page < 0) {
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (!$sortfield) {
	$sortfield = "t.ref";
}
if (!$sortorder) {
	$sortorder = "ASC";
}

// Initialize objects
$object = new AdvancedInventoryWarehouse($db);
$form = new Form($db);

// Security check
$result = restrictedArea($user, 'advancedinventory', 0, 'advancedinventory_warehouse', 'warehouse');

// List of fields to search into
$fieldstosearchall = array(
	't.ref' => 'Ref',
	't.label' => 'Label',
);

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
	$search_type = '';
	$search_status = '';
	$toselect = array();
	$search_array_options = array();
}

// Mass actions
if ($massaction == 'delete' && $user->hasRight('advancedinventory', 'warehouse', 'write')) {
	foreach ($toselect as $toselectid) {
		$object = new AdvancedInventoryWarehouse($db);
		$result = $object->fetch($toselectid);
		if ($result > 0) {
			$result = $object->delete($user);
			if ($result < 0) {
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}
	}
}

/*
 * View
 */

$title = $langs->trans('WarehouseList');
$help_url = '';

llxHeader('', $title, $help_url);

$sql = "SELECT t.rowid, t.ref, t.label, t.warehouse_type, t.status,";
$sql .= " t.address, t.zip, t.town, t.phone, t.email,";
$sql .= " t.fk_parent, t.date_creation, t.tms,";
$sql .= " p.label as parent_label";
$sql .= " FROM ".MAIN_DB_PREFIX."advancedinventory_warehouse as t";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."advancedinventory_warehouse as p ON t.fk_parent = p.rowid";
$sql .= " WHERE 1 = 1";

// Add search filters
if ($search_ref) {
	$sql .= natural_search('t.ref', $search_ref);
}
if ($search_label) {
	$sql .= natural_search('t.label', $search_label);
}
if ($search_type) {
	$sql .= " AND t.warehouse_type = '".$db->escape($search_type)."'";
}
if ($search_status != '' && $search_status != '-1') {
	$sql .= " AND t.status = ".((int) $search_status);
}

// Count total nb of records
$nbtotalofrecords = '';
if (!getDolGlobalInt('MAIN_DISABLE_FULL_SCANLIST')) {
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);

	if (($page * $limit) > $nbtotalofrecords) {
		$page = 0;
		$offset = 0;
	}
}

// Add sorting
$sql .= $db->order($sortfield, $sortorder);

// Add pagination
if ($limit) {
	$sql .= $db->plimit($limit + 1, $offset);
}

$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	exit;
}

$num = $db->num_rows($resql);

// Build array of data
$arrayofselected = is_array($toselect) ? $toselect : array();

// List of mass actions available
$arrayofmassactions = array();
if ($user->hasRight('advancedinventory', 'warehouse', 'write')) {
	$arrayofmassactions['delete'] = img_picto('', 'delete', 'class="pictofixedwidth"').$langs->trans("Delete");
}
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

// Build and execute select
$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
	$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$param .= '&limit='.urlencode($limit);
}
if ($search_ref) {
	$param .= '&search_ref='.urlencode($search_ref);
}
if ($search_label) {
	$param .= '&search_label='.urlencode($search_label);
}
if ($search_type) {
	$param .= '&search_type='.urlencode($search_type);
}
if ($search_status != '') {
	$param .= '&search_status='.urlencode($search_status);
}

// New button
$newcardbutton = '';
if ($user->hasRight('advancedinventory', 'warehouse', 'write')) {
	$newcardbutton = dolGetButtonTitle($langs->trans('NewWarehouse'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/custom/advancedinventory/warehouse/card.php?action=create', '', 1);
}

// Display page
print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'stock', 0, $newcardbutton, '', $limit);

print '<div class="div-table-responsive">';
print '<table class="tagtable nobottomiftotal liste'.($moreforfilter ? " listwithfilterbefore" : "").'">';

// Fields title search
print '<tr class="liste_titre_filter">';

// Ref
print '<td class="liste_titre">';
print '<input type="text" class="flat maxwidth100" name="search_ref" value="'.dol_escape_htmltag($search_ref).'">';
print '</td>';

// Label
print '<td class="liste_titre">';
print '<input type="text" class="flat maxwidth150" name="search_label" value="'.dol_escape_htmltag($search_label).'">';
print '</td>';

// Type
print '<td class="liste_titre center">';
$warehouse_types = array(
	'main' => $langs->trans('MainWarehouse'),
	'hall' => $langs->trans('Hall'),
	'shelf' => $langs->trans('Shelf'),
	'box' => $langs->trans('Box')
);
print $form->selectarray('search_type', $warehouse_types, $search_type, 1, 0, 0, '', 0, 0, 0, '', 'maxwidth100');
print '</td>';

// Parent
print '<td class="liste_titre">';
print '</td>';

// Status
print '<td class="liste_titre center">';
print $form->selectarray('search_status', array('0' => $langs->trans('Disabled'), '1' => $langs->trans('Enabled')), $search_status, 1, 0, 0, '', 0, 0, 0, '', 'maxwidth100');
print '</td>';

// Action column
print '<td class="liste_titre maxwidthsearch">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';

print '</tr>';

// Fields title label
print '<tr class="liste_titre">';
print_liste_field_titre("Ref", $_SERVER["PHP_SELF"], "t.ref", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Label", $_SERVER["PHP_SELF"], "t.label", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Type", $_SERVER["PHP_SELF"], "t.warehouse_type", "", $param, '', $sortfield, $sortorder, 'center ');
print_liste_field_titre("ParentWarehouse", $_SERVER["PHP_SELF"], "p.label", "", $param, '', $sortfield, $sortorder);
print_liste_field_titre("Status", $_SERVER["PHP_SELF"], "t.status", "", $param, '', $sortfield, $sortorder, 'center ');
print_liste_field_titre('', $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'maxwidthsearch ');
print '</tr>';

// Loop on records
$i = 0;
$totalarray = array();
$totalarray['nbfield'] = 0;

while ($i < min($num, $limit)) {
	$obj = $db->fetch_object($resql);

	$object->id = $obj->rowid;
	$object->ref = $obj->ref;
	$object->label = $obj->label;
	$object->warehouse_type = $obj->warehouse_type;
	$object->status = $obj->status;

	print '<tr class="oddeven">';

	// Ref
	print '<td class="nowraponall">';
	print '<a href="'.DOL_URL_ROOT.'/custom/advancedinventory/warehouse/card.php?id='.$obj->rowid.'">';
	print img_picto('', 'stock', 'class="pictofixedwidth"');
	print $obj->ref;
	print '</a>';
	print '</td>';

	// Label
	print '<td class="tdoverflowmax200">';
	print $obj->label;
	print '</td>';

	// Type
	print '<td class="center">';
	print $warehouse_types[$obj->warehouse_type];
	print '</td>';

	// Parent
	print '<td>';
	if ($obj->parent_label) {
		print $obj->parent_label;
	} else {
		print '-';
	}
	print '</td>';

	// Status
	print '<td class="center">';
	print $object->getLibStatut(5);
	print '</td>';

	// Action column
	print '<td class="nowrap center">';
	if ($massactionbutton || $massaction) {
		$selected = 0;
		if (in_array($obj->rowid, $arrayofselected)) {
			$selected = 1;
		}
		print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
	}
	print '</td>';

	print '</tr>';
	$i++;
}

// Show total line
if (isset($totalarray['totalizable']) && is_array($totalarray['totalizable'])) {
	print '<tr class="liste_total">';
	$i = 0;
	while ($i < $totalarray['nbfield']) {
		$i++;
		if ($i == 1) {
			print '<td class="left">'.$langs->trans("Total").'</td>';
		} else {
			print '<td></td>';
		}
	}
	print '</tr>';
}

$db->free($resql);

print '</table>';
print '</div>';

print '</form>';

// End of page
llxFooter();
$db->close();
