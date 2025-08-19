<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * ProductCategory class for Advanced Inventory Module
 * File: custom/advancedinventory/class/productcategory.class.php
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * ProductCategory class
 */
class ProductCategory extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'advancedinventory_product_category';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'advancedinventory_product_categories';

	/**
	 * @var int Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0; // Changed to 0 to avoid entity issues

	/**
	 * @var int Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0; // Changed to 0 to avoid extrafields table issues

	/**
	 * @var string String with name of icon for productcategory. Must be the part after the 'object_' into object_productcategory.png
	 */
	public $picto = 'category';

	const STATUS_DISABLED = 0;
	const STATUS_ENABLED = 1;

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'comment'=>"Id"),
		'category_name' => array('type'=>'varchar(255)', 'label'=>'CategoryName', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'help'=>"CategoryNameHelp", 'showoncombobox'=>'1'),
		'category_code' => array('type'=>'varchar(50)', 'label'=>'CategoryCode', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth200', 'help'=>"CategoryCodeHelp"),
		'fk_parent' => array('type'=>'integer:ProductCategory:advancedinventory/class/productcategory.class.php', 'label'=>'ParentCategory', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1),
		'level' => array('type'=>'integer', 'label'=>'Level', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>1),
		'path' => array('type'=>'varchar(500)', 'label'=>'FullPath', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>1, 'css'=>'minwidth300'),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>70, 'notnull'=>1, 'visible'=>1, 'default'=>'1', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Disabled', '1'=>'Enabled')),
		'sort_order' => array('type'=>'integer', 'label'=>'SortOrder', 'enabled'=>'1', 'position'=>80, 'notnull'=>0, 'visible'=>-1, 'default'=>'0'),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>90, 'notnull'=>0, 'visible'=>3),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid'),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2),
	);

	public $rowid;
	public $category_name;
	public $category_code;
	public $fk_parent;
	public $level;
	public $path;
	public $status;
	public $sort_order;
	public $description;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		global $conf, $langs;

		$error = 0;

		// Clean parameters
		if (isset($this->category_name)) {
			$this->category_name = trim($this->category_name);
		}
		if (isset($this->category_code)) {
			$this->category_code = trim($this->category_code);
		}
		if (isset($this->description)) {
			$this->description = trim($this->description);
		}

		// Check parameters
		if (empty($this->category_name)) {
			$this->errors[] = $langs->trans("CategoryNameRequired");
			return -1;
		}
		if (empty($this->category_code)) {
			$this->errors[] = $langs->trans("CategoryCodeRequired");
			return -1;
		}

		// Check if code already exists
		if ($this->checkCodeExists($this->category_code)) {
			$this->errors[] = $langs->trans("CategoryCodeAlreadyExists");
			return -1;
		}

		// Calculate level and path
		$this->calculateLevelAndPath();

		$this->db->begin();

		// Set creation date and user
		$this->date_creation = dol_now();
		$this->fk_user_creat = $user->id;

		$result = $this->createCommon($user, $notrigger);

		if ($result > 0) {
			$this->db->commit();
			return $result;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		return $result;
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		// Calculate level and path
		$this->calculateLevelAndPath();

		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		global $langs;

		// Check if category has children
		if ($this->hasChildren()) {
			$this->errors[] = $langs->trans("CannotDeleteCategoryWithChildren");
			return -1;
		}

		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 * Check if category code already exists
	 *
	 * @param string $code Category code
	 * @param int $exclude_id ID to exclude from check
	 * @return bool
	 */
	public function checkCodeExists($code, $exclude_id = 0)
	{
		$sql = "SELECT COUNT(*) as count FROM ".$this->db->prefix()."advancedinventory_product_categories";
		$sql .= " WHERE category_code = '".$this->db->escape($code)."'";
		if ($exclude_id > 0) {
			$sql .= " AND rowid != ".((int) $exclude_id);
		}

		$result = $this->db->query($sql);
		if ($result) {
			$obj = $this->db->fetch_object($result);
			return ($obj->count > 0);
		}

		return false;
	}

	/**
	 * Check if category has children
	 *
	 * @return bool
	 */
	public function hasChildren()
	{
		$sql = "SELECT COUNT(*) as count FROM ".$this->db->prefix()."advancedinventory_product_categories";
		$sql .= " WHERE fk_parent = ".((int) $this->id);

		$result = $this->db->query($sql);
		if ($result) {
			$obj = $this->db->fetch_object($result);
			return ($obj->count > 0);
		}

		return false;
	}

	/**
	 * Calculate level and path based on parent
	 */
	private function calculateLevelAndPath()
	{
		if (empty($this->fk_parent)) {
			$this->level = 0;
			$this->path = $this->category_code;
		} else {
			$sql = "SELECT level, path FROM ".$this->db->prefix()."advancedinventory_product_categories";
			$sql .= " WHERE rowid = ".((int) $this->fk_parent);

			$result = $this->db->query($sql);
			if ($result && $this->db->num_rows($result) > 0) {
				$obj = $this->db->fetch_object($result);
				$this->level = $obj->level + 1;
				$this->path = $obj->path . '/' . $this->category_code;
			} else {
				$this->level = 0;
				$this->path = $this->category_code;
			}
		}
	}

	/**
	 * Get children categories
	 *
	 * @return array Array of ProductCategory objects
	 */
	public function getChildren()
	{
		$children = array();

		$sql = "SELECT rowid FROM ".$this->db->prefix()."advancedinventory_product_categories";
		$sql .= " WHERE fk_parent = ".((int) $this->id);
		$sql .= " ORDER BY sort_order, category_name";

		$result = $this->db->query($sql);
		if ($result) {
			while ($obj = $this->db->fetch_object($result)) {
				$child = new ProductCategory($this->db);
				if ($child->fetch($obj->rowid) > 0) {
					$children[] = $child;
				}
			}
		}

		return $children;
	}

	/**
	 * Get all categories in tree format
	 *
	 * @param bool $active_only Return only active categories
	 * @return array Tree array
	 */
	public static function getAllCategoriesTree($db, $active_only = true)
	{
		$categories = array();

		$sql = "SELECT rowid FROM ".$db->prefix()."advancedinventory_product_categories";
		$sql .= " WHERE (fk_parent IS NULL OR fk_parent = 0)";
		if ($active_only) {
			$sql .= " AND status = 1";
		}
		$sql .= " ORDER BY sort_order, category_name";

		$result = $db->query($sql);
		if ($result) {
			while ($obj = $db->fetch_object($result)) {
				$category = new ProductCategory($db);
				if ($category->fetch($obj->rowid) > 0) {
					$categories[] = $category->getCategoryWithChildren($active_only);
				}
			}
		}

		return $categories;
	}

	/**
	 * Get category with all its children
	 *
	 * @param bool $active_only Return only active categories
	 * @return array Category data with children
	 */
	private function getCategoryWithChildren($active_only = true)
	{
		$data = array(
			'id' => $this->id,
			'name' => $this->category_name,
			'code' => $this->category_code,
			'level' => $this->level,
			'path' => $this->path,
			'description' => $this->description,
			'children' => array()
		);

		$children = $this->getChildren();
		foreach ($children as $child) {
			if (!$active_only || $child->status == self::STATUS_ENABLED) {
				$data['children'][] = $child->getCategoryWithChildren($active_only);
			}
		}

		return $data;
	}

	/**
	 * Return the label of the status
	 *
	 * @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 * @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 * Return the status
	 *
	 * @param	int		$status        Id status
	 * @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 * @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			$this->labelStatus[self::STATUS_DISABLED] = $langs->trans('Disabled');
			$this->labelStatus[self::STATUS_ENABLED] = $langs->trans('Enabled');
			$this->labelStatusShort[self::STATUS_DISABLED] = $langs->trans('Disabled');
			$this->labelStatusShort[self::STATUS_ENABLED] = $langs->trans('Enabled');
		}

		$statusType = 'status'.$status;
		if ($status == self::STATUS_ENABLED) {
			$statusType = 'status4';
		}

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Return a link to the object card (with optionaly the picto)
	 *
	 *	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *	@param	string	$option						On what the link point to ('nolink', ...)
	 *  @param	int  	$notooltip					1=Disable tooltip
	 *  @param  string  $morecss            		Add more css on link
	 *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *	@return	string								String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("ProductCategory").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('CategoryName').':</b> '.$this->category_name;
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('CategoryCode').':</b> '.$this->category_code;

		$url = dol_buildpath('/advancedinventory/productcategory_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowProductCategory");
				$linkclose .= ' alt="'.$label.'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink') {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink') {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
		}

		if ($withpicto != 2) {
			$result .= $this->category_name;
		}

		$result .= $linkend;

		global $action, $hookmanager;
		$hookmanager->initHooks(array('productcategorydao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}
}
