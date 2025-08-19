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

// Load translation files
$langs->loadLangs(array("admin", "advancedinventory@advancedinventory"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

$action = GETPOST('action', 'aZ09');

/*
 * Actions
 */

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
 * View
 */

$form = new Form($db);

llxHeader('', $langs->trans("AdvancedinventorySetup"));

print load_fiche_titre($langs->trans("AdvancedinventorySetup"), '', 'title_setup');

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("AdvancedinventorySetup"), $linkback, 'object_advancedinventory@advancedinventory');

// Configuration header
$head = array();
$head[0][0] = DOL_URL_ROOT.'/advancedinventory/admin/setup.php';
$head[0][1] = $langs->trans("Settings");
$head[0][2] = 'settings';

print dol_get_fiche_head($head, 'settings', '', -1, 'advancedinventory@advancedinventory');

// Setup page content
print '<span class="opacitymedium">'.$langs->trans("AdvancedinventorySetupDesc").'</span><br><br>';

// General settings section
print load_fiche_titre($langs->trans("GeneralSettings"), '', '');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '<td>'.$langs->trans("Action").'</td>';
print "</tr>\n";

// Add configuration parameters here in the future

print '</table>';

print '<br><br>';

// Danger Zone - Only for admin
if ($user->admin) {
	print load_fiche_titre($langs->trans("DangerZone"), '', '');

	print '<div class="warning">';
	print '<strong>'.$langs->trans("Warning").'!</strong><br>';
	print $langs->trans("CleanUninstallWarning").'<br>';
	print $langs->trans("CleanUninstallDesc").'<br><br>';

	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="cleanuninstall">';
	print '<input type="submit" class="button button-cancel" value="'.$langs->trans("CleanUninstall").'" ';
	print 'onclick="return confirm(\''.$langs->trans("ConfirmCleanUninstall").'\')">';
	print '</form>';
	print '</div>';
}

print dol_get_fiche_end();

llxFooter();
$db->close();
