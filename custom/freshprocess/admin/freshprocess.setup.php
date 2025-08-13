<?php
/* Copyright (C) 2007-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       dev/skeletons/skeleton_page.php
 *		\ingroup    mymodule othermodule1 othermodule2
 *		\brief      This file is an example of a php page
 *					Put here some comments
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include '../main.inc.php';					// to work if your module directory is into dolibarr root htdocs directory
if (! $res && file_exists("../../main.inc.php")) $res=@include '../../main.inc.php';			// to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../dolibarr/htdocs/main.inc.php';     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include '../../../../dolibarr/htdocs/main.inc.php';   // Used on dev env only
if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs
// dol_include_once('/module/class/skeleton_class.class.php');

// Load traductions files requiredby by page
// $langs->load("companies");
// $langs->load("other");
$langs->load("freshprocess@freshprocess");

// Get parameters
$id			= GETPOST('id','int');
$action		= GETPOST('action','alpha');
$myparam	= GETPOST('myparam','alpha');

// Protection if external user
if ($user->societe_id > 0)
{
    //accessforbidden();
}



/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

// if ($action == 'add')
// {
// 	$object=new Skeleton_Class($db);
// 	$object->prop1=$_POST["field1"];
// 	$object->prop2=$_POST["field2"];
// 	$result=$object->create($user);
// 	if ($result > 0)
// 	{
// 		// Creation OK
// 	}
// 	{
// 		// Creation KO
// 		$mesg=$object->error;
// 	}
// }





/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/

llxHeader('','Freshprocess','');

echo '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';

/* Configuration du module */
$config = json_decode(@file_get_contents(dirname(__FILE__).'/../config/api.config.json', false));

/* URL de la cron */
$cron_freshprocess = implode('', array(
    $_SERVER['REQUEST_SCHEME'].'://',
    $_SERVER['SERVER_NAME'],
    implode('/', array_slice(explode('/', $_SERVER['REQUEST_URI']), 0, -2)),
    '/?action=crontab'
));
?>
<h1>[API] Freshprocess</h1>
<style type="text/css">
.freshprocessConfig input {
    width: 100%;
}
</style>
<table class="freshprocessConfig noborder">
    <tbody>
        <tr class="liste_titre">
            <td><?php echo $langs->trans('FreshProcessProps'); ?></td>
            <td><?php echo $langs->trans('FreshProcessValues'); ?></td>
        </tr>
        <tr class="pair">
            <td><a href="<?php echo $cron_freshprocess; ?>" target="_blank"><?php echo $langs->trans('FreshProcessCronUrl'); ?></a></td>
            <td><input type="text" value="<?php echo $cron_freshprocess;
            ?>" readonly /></td>
        </tr>
        <tr class="impaire">
            <td><?php echo $langs->trans('FreshProcessApiUrl'); ?></td>
            <td><input type="text" name="api" value="<?php echo (isset($config->api) ? $config->api : ''); ?>" /></td>
        </tr>
        <?php foreach((isset($config->keys) ? $config->keys : array()) as $index => $key) { ?>
            <tr class="liste_titre">
                <td><?php echo $langs->trans('FreshProcessKey'); ?> <?php echo $index+1; ?></td>
                <td></td>
            </tr>
            <tr class="pair">
                <td><?php echo $langs->trans('FreshProcessKeyName'); ?></td>
                <td><input type="text" name="keyName[]" value="<?php echo (isset($key->name) ? $key->name : ''); ?>" /></td>
            </tr>
            <tr class="impaire">
                <td><?php echo $langs->trans('FreshProcessPublicKey'); ?></td>
                <td><input type="text" name="publicKey[]" value="<?php echo (isset($key->public) ? $key->public : ''); ?>" /></td>
            </tr>
            <tr class="pair">
                <td><?php echo $langs->trans('FreshProcessPrivateKey'); ?></td>
                <td><input type="password" name="privateKey[]" placeholder="[<?php
                    echo $langs->trans((isset($key->private) && !empty($key->private) ? 'FreshProcessFilled' : 'FreshProcessEmpty'));
                ?>]" /></td>
            </tr>
            <tr class="impaire">
                <td><?php echo $langs->trans('FreshProcessRefClient'); ?></td>
                <td><input type="text" name="refClient[]" value="<?php echo (isset($key->ref_client) && is_array($key->ref_client) ? implode(', ', $key->ref_client) : ''); ?>" /></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php
llxFooter();
$db->close();
