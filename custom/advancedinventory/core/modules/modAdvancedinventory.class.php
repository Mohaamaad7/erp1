<?php
/* Copyright (C) 2004-2018	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019	Nicolas ZABOURI				<info@inovea-conseil.com>
 * Copyright (C) 2019-2024	Frédéric France				<frederic.france@free.fr>
 * Copyright (C) 2025		Muhammad Abd ElRazik		<mohaamaad7@gmail.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   advancedinventory     Module Advancedinventory
 *  \brief      Advancedinventory module descriptor.
 *
 *  \file       htdocs/advancedinventory/core/modules/modAdvancedinventory.class.php
 *  \ingroup    advancedinventory
 *  \brief      Description and activation file for module Advancedinventory
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';
include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

/**
 *  Description and activation class for module Advancedinventory
 */
class modAdvancedinventory extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 500000; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'advancedinventory';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "Reyada";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';

		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleAdvancedinventoryName' not found (Advancedinventory is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// DESCRIPTION_FLAG
		// Module description, used if translation string 'ModuleAdvancedinventoryDesc' not found (Advancedinventory is name of module).
		$this->description = "AdvancedinventoryDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "AdvancedinventoryDescription";

		// Author
		$this->editor_name = 'Dr Mohammad Abd ElRazik';
		$this->editor_url = 'https://www.areyada.com';		// Must be an external online web site
		$this->editor_squarred_logo = '';					// Must be image filename into the module/img directory followed with @modulename. Example: 'myimage.png@advancedinventory'

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated', 'experimental_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where ADVANCEDINVENTORY is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		// To use a supported fa-xxx css style of font awesome, use this->picto='xxx'
		$this->picto = 'fa-file';

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 0,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/advancedinventory/css/advancedinventory.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/advancedinventory/js/advancedinventory.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			/* BEGIN MODULEBUILDER HOOKSCONTEXTS */
			'hooks' => array(
				//   'data' => array(
				//       'hookcontext1',
				//       'hookcontext2',
				//   ),
				//   'entity' => '0',
			),
			/* END MODULEBUILDER HOOKSCONTEXTS */
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
			// Set this to 1 if the module provides a website template into doctemplates/websites/website_template-mytemplate
			'websitetemplates' => 0,
			// Set this to 1 if the module provides a captcha driver
			'captcha' => 0
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/advancedinventory/temp","/advancedinventory/subdir");
		$this->dirs = array("/advancedinventory/temp");

		// Config pages. Put here list of php page, stored into advancedinventory/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@advancedinventory");

		// Dependencies
		// A condition to hide module
		$this->hidden = getDolGlobalInt('MODULE_ADVANCEDINVENTORY_DISABLED'); // A condition to disable module;
		// List of module class names that must be enabled if this module is enabled. Example: array('always'=>array('modModuleToEnable1','modModuleToEnable2'), 'FR'=>array('modModuleToEnableFR')...)
		$this->depends = array();
		// List of module class names to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->requiredby = array();
		// List of module class names this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array();

		// The language file dedicated to your module
		$this->langfiles = array("advancedinventory@advancedinventory");

		// Prerequisites
		$this->phpmin = array(7, 1); // Minimum version of PHP required by module
		// $this->phpmax = array(8, 0); // Maximum version of PHP required by module
		$this->need_dolibarr_version = array(19, -3); // Minimum version of Dolibarr required by module
		// $this->max_dolibarr_version = array(19, -3); // Maximum version of Dolibarr required by module
		$this->need_javascript_ajax = 0;

		// Messages at activation
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','MX'='textmx'...)
		//$this->automatic_activation = array('FR'=>'AdvancedinventoryWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('ADVANCEDINVENTORY_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('ADVANCEDINVENTORY_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isModEnabled("advancedinventory")) {
			$conf->advancedinventory = new stdClass();
			$conf->advancedinventory->enabled = 0;
		}

		// Array to add new pages in new tabs
		/* BEGIN MODULEBUILDER TABS */
		$this->tabs = array();
		/* END MODULEBUILDER TABS */
		// Example:
		// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data' => 'objecttype:+tabname1:Title1:mylangfile@advancedinventory:$user->hasRight(\'advancedinventory\', \'read\'):/advancedinventory/mynewtab1.php?id=__ID__');
		// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data' => 'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@advancedinventory:$user->hasRight(\'othermodule\', \'read\'):/advancedinventory/mynewtab2.php?id=__ID__',
		// To remove an existing tab identified by code tabname
		// $this->tabs[] = array('data' => 'objecttype:-tabname:NU:conditiontoremove');
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'delivery'         to add a tab in delivery view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in foundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in sale order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view


		// Dictionaries
		/* Example:
		 $this->dictionaries=array(
		 'langs' => 'advancedinventory@advancedinventory',
		 // List of tables we want to see into dictionary editor
		 'tabname' => array("table1", "table2", "table3"),
		 // Label of tables
		 'tablib' => array("Table1", "Table2", "Table3"),
		 // Request to select fields
		 'tabsql' => array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.$this->db->prefix().'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.$this->db->prefix().'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.$this->db->prefix().'table3 as f'),
		 // Sort order
		 'tabsqlsort' => array("label ASC", "label ASC", "label ASC"),
		 // List of fields (result of select to show dictionary)
		 'tabfield' => array("code,label", "code,label", "code,label"),
		 // List of fields (list of fields to edit a record)
		 'tabfieldvalue' => array("code,label", "code,label", "code,label"),
		 // List of fields (list of fields for insert)
		 'tabfieldinsert' => array("code,label", "code,label", "code,label"),
		 // Name of columns with primary key (try to always name it 'rowid')
		 'tabrowid' => array("rowid", "rowid", "rowid"),
		 // Condition to show each dictionary
		 'tabcond' => array(isModEnabled('advancedinventory'), isModEnabled('advancedinventory'), isModEnabled('advancedinventory')),
		 // Tooltip for every fields of dictionaries: DO NOT PUT AN EMPTY ARRAY
		 'tabhelp' => array(array('code' => $langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'), array('code' => $langs->trans('CodeTooltipHelp'), 'field2' => 'field2tooltip'), ...),
		 );
		 */
		/* BEGIN MODULEBUILDER DICTIONARIES */
		$this->dictionaries = array();
		/* END MODULEBUILDER DICTIONARIES */

		// Boxes/Widgets
		// Add here list of php file(s) stored in advancedinventory/core/boxes that contains a class to show a widget.
		/* BEGIN MODULEBUILDER WIDGETS */
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'advancedinventorywidget1.php@advancedinventory',
			//      'note' => 'Widget provided by Advancedinventory',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);
		/* END MODULEBUILDER WIDGETS */

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		/* BEGIN MODULEBUILDER CRON */
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/advancedinventory/class/myobject.class.php',
			//      'objectname' => 'MyObject',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => 'isModEnabled("advancedinventory")',
			//      'priority' => 50,
			//  ),
		);
		/* END MODULEBUILDER CRON */
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'isModEnabled("advancedinventory")', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'isModEnabled("advancedinventory")', 'priority'=>50)
		// );


		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		// Permissions provided by this module
		$this->rights = array();
		$r = 0;

// الصلاحيات الرئيسية للكتالوج
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'View Item Catalog';
		$this->rights[$r][4] = 'catalog';
		$this->rights[$r][5] = 'read';

		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'Manage Item Catalog';
		$this->rights[$r][4] = 'catalog';
		$this->rights[$r][5] = 'write';

// صلاحيات المخازن
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'View Warehouses';
		$this->rights[$r][4] = 'warehouse';
		$this->rights[$r][5] = 'read';

		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'Manage Warehouses';
		$this->rights[$r][4] = 'warehouse';
		$this->rights[$r][5] = 'write';

// صلاحيات المعاملات
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'View Inventory Transactions';
		$this->rights[$r][4] = 'transaction';
		$this->rights[$r][5] = 'read';

		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'Create Inventory Transactions';
		$this->rights[$r][4] = 'transaction';
		$this->rights[$r][5] = 'write';

// صلاحيات الجرد
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'Perform Inventory Count';
		$this->rights[$r][4] = 'inventory';
		$this->rights[$r][5] = 'count';

// صلاحيات التقارير
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'View Reports';
		$this->rights[$r][4] = 'reports';
		$this->rights[$r][5] = 'read';

// صلاحيات الإعدادات
		$r++;
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r);
		$this->rights[$r][1] = 'Configure Module Settings';
		$this->rights[$r][4] = 'settings';
		$this->rights[$r][5] = 'admin';
		/* END MODULEBUILDER PERMISSIONS */


		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu' => '', // Will be stored into mainmenu + leftmenu. Use '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'top', // This is a Top menu entry
			'titre' => 'ModuleAdvancedinventoryName',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle"'),
			'mainmenu' => 'advancedinventory',
			'leftmenu' => '',
			'url' => '/advancedinventory/advancedinventoryindex.php',
			'langs' => 'advancedinventory@advancedinventory', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("advancedinventory")', // Define condition to show or hide menu entry. Use 'isModEnabled("advancedinventory")' if entry must be visible if module is enabled.
			'perms' => '1', // Use 'perms'=>'$user->hasRight("advancedinventory", "myobject", "read")' if you want your menu with a permission rules
			'target' => '',
			'user' => 2, // 0=Menu for internal users, 1=external users, 2=both
		);
		/* END MODULEBUILDER TOPMENU */

// القوائم الفرعية للموديول

// قائمة الكتالوج
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=advancedinventory',
			'type' => 'left',
			'titre' => 'ItemCatalog',
			'prefix' => img_picto('', 'product', 'class="pictofixedwidth"'),
			'mainmenu' => 'advancedinventory',
			'leftmenu' => 'catalog',
			'url' => '/advancedinventory/catalog/list.php',
			'langs' => 'advancedinventory@advancedinventory',
			'position' => 1100,
			'enabled' => 'isModEnabled("advancedinventory")',
			'perms' => '$user->hasRight("advancedinventory", "catalog", "read")',
			'target' => '',
			'user' => 2,
		);

// قائمة المخازن
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=advancedinventory',
			'type' => 'left',
			'titre' => 'Warehouses',
			'prefix' => img_picto('', 'stock', 'class="pictofixedwidth"'),
			'mainmenu' => 'advancedinventory',
			'leftmenu' => 'warehouses',
			'url' => '/advancedinventory/warehouse/list.php',
			'langs' => 'advancedinventory@advancedinventory',
			'position' => 1200,
			'enabled' => 'isModEnabled("advancedinventory")',
			'perms' => '$user->hasRight("advancedinventory", "warehouse", "read")',
			'target' => '',
			'user' => 2,
		);

// قائمة المعاملات
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=advancedinventory',
			'type' => 'left',
			'titre' => 'Transactions',
			'prefix' => img_picto('', 'movement', 'class="pictofixedwidth"'),
			'mainmenu' => 'advancedinventory',
			'leftmenu' => 'transactions',
			'url' => '/advancedinventory/transaction/list.php',
			'langs' => 'advancedinventory@advancedinventory',
			'position' => 1300,
			'enabled' => 'isModEnabled("advancedinventory")',
			'perms' => '$user->hasRight("advancedinventory", "transaction", "read")',
			'target' => '',
			'user' => 2,
		);

// قائمة التقارير
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=advancedinventory',
			'type' => 'left',
			'titre' => 'Reports',
			'prefix' => img_picto('', 'stats', 'class="pictofixedwidth"'),
			'mainmenu' => 'advancedinventory',
			'leftmenu' => 'reports',
			'url' => '/advancedinventory/reports/index.php',
			'langs' => 'advancedinventory@advancedinventory',
			'position' => 1400,
			'enabled' => 'isModEnabled("advancedinventory")',
			'perms' => '$user->hasRight("advancedinventory", "reports", "read")',
			'target' => '',
			'user' => 2,
		);

// قائمة الإعدادات
		$this->menu[$r++] = array(
			'fk_menu' => 'fk_mainmenu=advancedinventory',
			'type' => 'left',
			'titre' => 'Settings',
			'prefix' => img_picto('', 'setup', 'class="pictofixedwidth"'),
			'mainmenu' => 'advancedinventory',
			'leftmenu' => 'settings',
			'url' => '/advancedinventory/admin/setup.php',
			'langs' => 'advancedinventory@advancedinventory',
			'position' => 1500,
			'enabled' => 'isModEnabled("advancedinventory")',
			'perms' => '$user->hasRight("advancedinventory", "settings", "admin")',
			'target' => '',
			'user' => 2,
		);

		/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT *//* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU MYOBJECT */
		/*
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=advancedinventory',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',                          // This is a Left menu entry
			'titre' => 'MyObject',
			'prefix' => img_picto('', $this->picto, 'class="pictofixedwidth valignmiddle paddingright"'),
			'mainmenu' => 'advancedinventory',
			'leftmenu' => 'myobject',
			'url' => '/advancedinventory/advancedinventoryindex.php',
			'langs' => 'advancedinventory@advancedinventory',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("advancedinventory")', // Define condition to show or hide menu entry. Use 'isModEnabled("advancedinventory")' if entry must be visible if module is enabled.
			'perms' => '$user->hasRight("advancedinventory", "myobject", "read")',
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'MyObject'
		);
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=advancedinventory,fk_leftmenu=myobject',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',			                // This is a Left menu entry
			'titre' => 'New_MyObject',
			'mainmenu' => 'advancedinventory',
			'leftmenu' => 'advancedinventory_myobject_new',
			'url' => '/advancedinventory/myobject_card.php?action=create',
			'langs' => 'advancedinventory@advancedinventory',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("advancedinventory")', // Define condition to show or hide menu entry. Use 'isModEnabled("advancedinventory")' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms' => '$user->hasRight("advancedinventory", "myobject", "write")'
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'MyObject'
		);
		$this->menu[$r++]=array(
			'fk_menu' => 'fk_mainmenu=advancedinventory,fk_leftmenu=myobject',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type' => 'left',			                // This is a Left menu entry
			'titre' => 'List_MyObject',
			'mainmenu' => 'advancedinventory',
			'leftmenu' => 'advancedinventory_myobject_list',
			'url' => '/advancedinventory/myobject_list.php',
			'langs' => 'advancedinventory@advancedinventory',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position' => 1000 + $r,
			'enabled' => 'isModEnabled("advancedinventory")', // Define condition to show or hide menu entry. Use 'isModEnabled("advancedinventory")' if entry must be visible if module is enabled.
			'perms' => '$user->hasRight("advancedinventory", "myobject", "read")'
			'target' => '',
			'user' => 2,				                // 0=Menu for internal users, 1=external users, 2=both
			'object' => 'MyObject'
		);
		*/
		/* END MODULEBUILDER LEFTMENU MYOBJECT */


		// Exports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER EXPORT MYOBJECT */
		/*
		$langs->load("advancedinventory@advancedinventory");
		$this->export_code[$r] = $this->rights_class.'_'.$r;
		$this->export_label[$r] = 'MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r] = $this->picto;
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'MyObject'; $keyforclassfile='/advancedinventory/class/myobject.class.php'; $keyforelement='myobject@advancedinventory';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'MyObjectLine'; $keyforclassfile='/advancedinventory/class/myobject.class.php'; $keyforelement='myobjectline@advancedinventory'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@advancedinventory';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='myobjectline'; $keyforaliasextra='extraline'; $keyforelement='myobjectline@advancedinventory';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('myobjectline' => array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field' => '...');
		//$this->export_examplevalues_array[$r] = array('t.field' => 'Example');
		//$this->export_help_array[$r] = array('t.field' => 'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.$this->db->prefix().'advancedinventory_myobject as t';
		//$this->export_sql_end[$r]  .=' LEFT JOIN '.$this->db->prefix().'advancedinventory_myobject_line as tl ON tl.fk_myobject = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('myobject').')';
		$r++; */
		/* END MODULEBUILDER EXPORT MYOBJECT */

		// Imports profiles provided by this module
		$r = 0;
		/* BEGIN MODULEBUILDER IMPORT MYOBJECT */
		/*
		$langs->load("advancedinventory@advancedinventory");
		$this->import_code[$r] = $this->rights_class.'_'.$r;
		$this->import_label[$r] = 'MyObjectLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->import_icon[$r] = $this->picto;
		$this->import_tables_array[$r] = array('t' => $this->db->prefix().'advancedinventory_myobject', 'extra' => $this->db->prefix().'advancedinventory_myobject_extrafields');
		$this->import_tables_creator_array[$r] = array('t' => 'fk_user_author'); // Fields to store import user id
		$import_sample = array();
		$keyforclass = 'MyObject'; $keyforclassfile='/advancedinventory/class/myobject.class.php'; $keyforelement='myobject@advancedinventory';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinimport.inc.php';
		$import_extrafield_sample = array();
		$keyforselect='myobject'; $keyforaliasextra='extra'; $keyforelement='myobject@advancedinventory';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinimport.inc.php';
		$this->import_fieldshidden_array[$r] = array('extra.fk_object' => 'lastrowid-'.$this->db->prefix().'advancedinventory_myobject');
		$this->import_regex_array[$r] = array();
		$this->import_examplevalues_array[$r] = array_merge($import_sample, $import_extrafield_sample);
		$this->import_updatekeys_array[$r] = array('t.ref' => 'Ref');
		$this->import_convertvalue_array[$r] = array(
			't.ref' => array(
				'rule'=>'getrefifauto',
				'class'=>(!getDolGlobalString('ADVANCEDINVENTORY_MYOBJECT_ADDON') ? 'mod_myobject_standard' : getDolGlobalString('ADVANCEDINVENTORY_MYOBJECT_ADDON')),
				'path'=>"/core/modules/advancedinventory/".(!getDolGlobalString('ADVANCEDINVENTORY_MYOBJECT_ADDON') ? 'mod_myobject_standard' : getDolGlobalString('ADVANCEDINVENTORY_MYOBJECT_ADDON')).'.php',
				'classobject'=>'MyObject',
				'pathobject'=>'/advancedinventory/class/myobject.class.php',
			),
			't.fk_soc' => array('rule' => 'fetchidfromref', 'file' => '/societe/class/societe.class.php', 'class' => 'Societe', 'method' => 'fetch', 'element' => 'ThirdParty'),
			't.fk_user_valid' => array('rule' => 'fetchidfromref', 'file' => '/user/class/user.class.php', 'class' => 'User', 'method' => 'fetch', 'element' => 'user'),
			't.fk_mode_reglement' => array('rule' => 'fetchidfromcodeorlabel', 'file' => '/compta/paiement/class/cpaiement.class.php', 'class' => 'Cpaiement', 'method' => 'fetch', 'element' => 'cpayment'),
		);
		$this->import_run_sql_after_array[$r] = array();
		$r++; */
		/* END MODULEBUILDER IMPORT MYOBJECT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int<-1,1>          	1 if OK, <=0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		// Create tables of module at module activation
		$result = $this->_load_tables('/advancedinventory/sql/');
		if ($result < 0) {
			return -1;
		}

		// Create extrafields during init
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);

		// Add separator for Advanced Inventory fields
		$extrafields->addExtraField(
			'advinv_separator_main',
			"AdvancedInventoryFields",  // سيترجم من ملف اللغة
			'separator',
			90,  // Position before our fields
			'',
			'product',
			0, 0, '',
			array('csslist' => 'background: #2c3e50; color: white; font-weight: bold;'),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// الكود الذكي
		$result1 = $extrafields->addExtraField(
			'advinv_smart_code',
			"SmartCode",
			'varchar',
			100,
			20,
			'product',
			0, 0, '',
			array(
				'help' => 'SmartCodeHelp',
				'csslist' => 'background-color: #e8f4f8;',  // لون خلفية مميز
				'css' => 'background-color: #e8f4f8;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// نقطة إعادة الطلب
		$result2 = $extrafields->addExtraField(
			'advinv_reorder_point',
			"ReorderPoint",
			'double',
			110,
			'24,8',
			'product',
			0, 0, '0',
			array(
				'help' => 'ReorderPointHelp',
				'csslist' => 'background-color: #e8f4f8;',
				'css' => 'background-color: #e8f4f8;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// الحد الأدنى للمخزون
		$result3 = $extrafields->addExtraField(
			'advinv_min_stock',
			"MinimumStock",
			'double',
			120,
			'24,8',
			'product',
			0, 0, '0',
			array(
				'help' => 'MinimumStockHelp',
				'csslist' => 'background-color: #e8f4f8;',
				'css' => 'background-color: #e8f4f8;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// الحد الأقصى للمخزون
		$result4 = $extrafields->addExtraField(
			'advinv_max_stock',
			"MaximumStock",
			'double',
			130,
			'24,8',
			'product',
			0, 0, '0',
			array(
				'help' => 'MaximumStockHelp',
				'csslist' => 'background-color: #e8f4f8;',
				'css' => 'background-color: #e8f4f8;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// تتبع الدفعات
		$result5 = $extrafields->addExtraField(
			'advinv_batch_tracking',
			"BatchTracking",
			'boolean',
			140,
			'',
			'product',
			0, 0, '0',
			array(
				'help' => 'BatchTrackingHelp',
				'csslist' => 'background-color: #e8f4f8;',
				'css' => 'background-color: #e8f4f8;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// مدة الصلاحية بالأيام
		$result6 = $extrafields->addExtraField(
			'advinv_shelf_life',
			"ShelfLifeDays",
			'int',
			150,
			10,
			'product',
			0, 0, '',
			array(
				'help' => 'ShelfLifeHelp',
				'csslist' => 'background-color: #e8f4f8;',
				'css' => 'background-color: #e8f4f8;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

		$result6 = $extrafields->addExtraField(
			'advinv_lead_time',
			"LeadTime",
			'int',
			150,
			10,
			'product',
			0, 0, '',
			array(
				'help' => 'LeadTimeHelp',
				'csslist' => 'background-color: #e8f4f8;',
				'css' => 'background-color: #e8f4f8;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// Add closing separator
		$extrafields->addExtraField(
			'advinv_separator_end',
			"",  // فاصل فارغ للإغلاق
			'separator',
			160,
			'',
			'product',
			0, 0, '',
			array('csslist' => 'height: 2px; background: #2c3e50;'),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// ========== STOCK MOVEMENT EXTRAFIELDS ==========

// Add separator for Advanced Inventory movement fields
		$extrafields->addExtraField(
			'advinv_mov_separator_main',
			"AdvancedInventoryMovementFields",
			'separator',
			190,
			'',
			'stock_mouvement',
			0, 0, '',
			array('csslist' => 'background: #27ae60; color: white; font-weight: bold;'),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// رقم التحويل
		$result7 = $extrafields->addExtraField(
			'advinv_transfer_ref',
			"TransferReference",
			'varchar',
			200,
			128,
			'stock_mouvement',
			0, 0, '',
			array(
				'help' => 'TransferReferenceHelp',
				'csslist' => 'background-color: #e8f8f5;',
				'css' => 'background-color: #e8f8f5;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// المخزن الوجهة (للتحويلات)
		$result8 = $extrafields->addExtraField(
			'advinv_warehouse_to',
			"DestinationWarehouse",
			'sellist',
			210,
			'',
			'stock_mouvement',
			0, 0, '',
			array(
				'options' => array('advancedinventory_warehouse:label:rowid::status=1' => null),
				'help' => 'DestinationWarehouseHelp',
				'csslist' => 'background-color: #e8f8f5;',
				'css' => 'background-color: #e8f8f5;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// حالة الموافقة
		$result9 = $extrafields->addExtraField(
			'advinv_approval_status',
			"ApprovalStatus",
			'select',
			220,
			'',
			'stock_mouvement',
			0, 0, '0',
			array(
				'options' => array(
					'0' => 'Pending',
					'1' => 'Approved',
					'2' => 'Rejected'
				),
				'help' => 'ApprovalStatusHelp',
				'csslist' => 'background-color: #e8f8f5;',
				'css' => 'background-color: #e8f8f5;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// المستخدم الموافق
		$result10 = $extrafields->addExtraField(
			'advinv_approved_by',
			"ApprovedBy",
			'link',
			230,
			'',
			'stock_mouvement',
			0, 0, '',
			array(
				'options' => array('User:user/class/user.class.php' => null),
				'help' => 'ApprovedByHelp',
				'csslist' => 'background-color: #e8f8f5;',
				'css' => 'background-color: #e8f8f5;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// تاريخ الموافقة
		$result11 = $extrafields->addExtraField(
			'advinv_approval_date',
			"ApprovalDate",
			'datetime',
			240,
			'',
			'stock_mouvement',
			0, 0, '',
			array(
				'help' => 'ApprovalDateHelp',
				'csslist' => 'background-color: #e8f8f5;',
				'css' => 'background-color: #e8f8f5;'
			),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);

// Add closing separator
		$extrafields->addExtraField(
			'advinv_mov_separator_end',
			"",
			'separator',
			250,
			'',
			'stock_mouvement',
			0, 0, '',
			array('csslist' => 'height: 2px; background: #27ae60;'),
			1, '', 1, 0, '', '',
			'advancedinventory@advancedinventory',
			'isModEnabled("advancedinventory")'
		);


		$sql = array();

		// Document templates
		$moduledir = dol_sanitizeFileName('advancedinventory');
		$myTmpObjects = array();
		$myTmpObjects['MyObject'] = array('includerefgeneration' => 0, 'includedocgeneration' => 0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectArray['includerefgeneration']) {
				$src = DOL_DOCUMENT_ROOT.'/install/doctemplates/'.$moduledir.'/template_myobjects.odt';
				$dirodt = DOL_DATA_ROOT.($conf->entity > 1 ? '/'.$conf->entity : '').'/doctemplates/'.$moduledir;
				$dest = $dirodt.'/template_myobjects.odt';

				if (file_exists($src) && !file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result = dol_copy($src, $dest, '0', 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error = $langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".$this->db->prefix()."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".$this->db->prefix()."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")",
					"DELETE FROM ".$this->db->prefix()."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".$this->db->escape(strtolower($myTmpObjectKey))."' AND entity = ".((int) $conf->entity),
					"INSERT INTO ".$this->db->prefix()."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".$this->db->escape(strtolower($myTmpObjectKey))."', ".((int) $conf->entity).")"
				));
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *	Function called when module is disabled.
	 *	Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted
	 *
	 *	@param	string		$options	Options when enabling module ('', 'noboxes')
	 *	@return	int<-1,1>				1 if OK, <=0 if KO
	 */
	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param	string		$options	Options when disabling module ('', 'noboxes', 'cleanuninstall')
	 * @return	int						1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		global $conf, $langs, $user;

		$sql = array();

		// Check if clean uninstall is requested (admin only)
		if ($options === 'cleanuninstall' && !empty($user->admin)) {

			// WARNING: This will delete ALL module data permanently
			include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
			$extrafields = new ExtraFields($this->db);

			// Delete extrafields definitions (structure)
			// Product extrafields
			$extrafields->delete('advinv_smart_code', 'product');
			$extrafields->delete('advinv_reorder_point', 'product');
			$extrafields->delete('advinv_min_stock', 'product');
			$extrafields->delete('advinv_max_stock', 'product');
			$extrafields->delete('advinv_batch_tracking', 'product');
			$extrafields->delete('advinv_shelf_life', 'product');

			// Stock Movement extrafields
			$extrafields->delete('advinv_transfer_ref', 'stock_mouvement');
			$extrafields->delete('advinv_warehouse_to', 'stock_mouvement');
			$extrafields->delete('advinv_approval_status', 'stock_mouvement');
			$extrafields->delete('advinv_approved_by', 'stock_mouvement');
			$extrafields->delete('advinv_approval_date', 'stock_mouvement');

			// Drop our custom tables (with their data)
			$sql[] = "DROP TABLE IF EXISTS ".$this->db->prefix()."advancedinventory_warehouse";
			$sql[] = "DROP TABLE IF EXISTS ".$this->db->prefix()."advancedinventory_supplier_item";
			$sql[] = "DROP TABLE IF EXISTS ".$this->db->prefix()."advancedinventory_stock_location";

			// Remove actual data columns from extrafields tables
			// Note: These columns might not exist if extrafields were never used
			$sql[] = "ALTER TABLE ".$this->db->prefix()."product_extrafields
                  DROP COLUMN IF EXISTS advinv_smart_code,
                  DROP COLUMN IF EXISTS advinv_reorder_point,
                  DROP COLUMN IF EXISTS advinv_min_stock,
                  DROP COLUMN IF EXISTS advinv_max_stock,
                  DROP COLUMN IF EXISTS advinv_batch_tracking,
                  DROP COLUMN IF EXISTS advinv_shelf_life";

			$sql[] = "ALTER TABLE ".$this->db->prefix()."stock_mouvement_extrafields
                  DROP COLUMN IF EXISTS advinv_transfer_ref,
                  DROP COLUMN IF EXISTS advinv_warehouse_to,
                  DROP COLUMN IF EXISTS advinv_approval_status,
                  DROP COLUMN IF EXISTS advinv_approved_by,
                  DROP COLUMN IF EXISTS advinv_approval_date";

			// Log this action
			dol_syslog(get_class($this)."::remove Clean uninstall performed by user ".$user->login, LOG_WARNING);
		}
		// Normal disable - keep everything safe
		else {
			// Don't delete anything, just disable the module
			// Data remains safe for future use
			dol_syslog(get_class($this)."::remove Module disabled, data preserved", LOG_INFO);
		}

		return $this->_remove($sql, $options);
	}
}
