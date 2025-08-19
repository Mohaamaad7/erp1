<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * ProductType class for Advanced Inventory Module
 * File: custom/advancedinventory/class/producttype.class.php
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * ProductType class
 */
class ProductType extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'advancedinventory_product_type';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'advancedinventory_product_types';

	/**
	 * @var int Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0; // Changed to 0 to avoid extrafields table issues

	/**
	 * @var string String with name of icon for producttype. Must be the part after the 'object_' into object_producttype.png
	 */
	public $picto = 'product';

	const STATUS_DISABLED = 0;
	const STATUS_ENABLED = 1;

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'comment'=>"Id"),
		'type_name' => array('type'=>'varchar(100)', 'label'=>'TypeName', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth200', 'help'=>"TypeNameHelp", 'showoncombobox'=>'1'),
		'type_code' => array('type'=>'varchar(20)', 'label'=>'TypeCode', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth100', 'help'=>"TypeCodeHelp"),
		'code_prefix' => array('type'=>'varchar(10)', 'label'=>'CodePrefix', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1, 'css'=>'minwidth50', 'help'=>"CodePrefixHelp"),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>50, 'notnull'=>1, 'visible'=>1, 'default'=>'1', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Disabled', '1'=>'Enabled')),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid'),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2),
	);

	public $rowid;
	public $type_name;
	public $type_code;
	public $code_prefix;
	public $status;
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
		if (isset($this->type_name)) {
			$this->type_name = trim($this->type_name);
		}
		if (isset($this->type_code)) {
			$this->type_code = strtoupper(trim($this->type_code));
		}
		if (isset($this->code_prefix)) {
			$this->code_prefix = trim($this->code_prefix); // Keep original case for numbers
		}
		if (isset($this->description)) {
			$this->description = trim($this->description);
		}

		// Check parameters
		if (empty($this->type_name)) {
			$this->errors[] = $langs->trans("TypeNameRequired");
			return -1;
		}
		if (empty($this->type_code)) {
			$this->errors[] = $langs->trans("TypeCodeRequired");
			return -1;
		}
		if (empty($this->code_prefix)) {
			$this->errors[] = $langs->trans("CodePrefixRequired");
			return -1;
		}

		// Check if code already exists
		if ($this->checkCodeExists($this->type_code)) {
			$this->errors[] = $langs->trans("TypeCodeAlreadyExists");
			return -1;
		}

		$this->db->begin();

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
	 * @param string $type_code Type code
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null, $type_code = null)
	{
		if (!empty($type_code)) {
			$sql = "SELECT rowid FROM ".$this->db->prefix().$this->table_element;
			$sql .= " WHERE type_code = '".$this->db->escape($type_code)."'";
			$sql .= " AND entity IN (".getEntity($this->table_element).")";

			$resql = $this->db->query($sql);
			if ($resql) {
				if ($this->db->num_rows($resql)) {
					$obj = $this->db->fetch_object($resql);
					$id = $obj->rowid;
				} else {
					return 0;
				}
				$this->db->free($resql);
			} else {
				return -1;
			}
		}

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
		// Clean parameters
		if (isset($this->type_name)) {
			$this->type_name = trim($this->type_name);
		}
		if (isset($this->code_prefix)) {
			$this->code_prefix = trim($this->code_prefix); // Keep original case for numbers
		}

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
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".$this->db->prefix().$this->table_element." as t";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->table_element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}

		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key."=".$value;
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN (".$this->db->sanitize($this->db->escape($value)).")";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= " ".$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Check if type code already exists
	 *
	 * @param string $code Type code
	 * @param int $exclude_id ID to exclude from check
	 * @return bool
	 */
	public function checkCodeExists($code, $exclude_id = 0)
	{
		$sql = "SELECT COUNT(*) as count FROM ".$this->db->prefix()."advancedinventory_product_types";
		$sql .= " WHERE type_code = '".$this->db->escape($code)."'";
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
	 * Get all active product types
	 *
	 * @param DoliDB $db Database handler
	 * @return array Array of ProductType objects
	 */
	public static function getAllActiveTypes($db)
	{
		$types = array();

		$sql = "SELECT rowid FROM ".$db->prefix()."advancedinventory_product_types";
		$sql .= " WHERE status = 1";
		$sql .= " ORDER BY rowid";

		$result = $db->query($sql);
		if ($result) {
			while ($obj = $db->fetch_object($result)) {
				$type = new ProductType($db);
				if ($type->fetch($obj->rowid) > 0) {
					$types[] = $type;
				}
			}
		}

		return $types;
	}

	/**
	 * Get product type by code
	 *
	 * @param DoliDB $db Database handler
	 * @param string $type_code Type code
	 * @return ProductType|false ProductType object or false if not found
	 */
	public static function getByCode($db, $type_code)
	{
		$type = new ProductType($db);
		if ($type->fetch(0, null, $type_code) > 0) {
			return $type;
		}

		return false;
	}

	/**
	 * Generate next auto number for this type and category
	 *
	 * @param string $category_code Category code
	 * @return string Next auto number (001, 002, etc.)
	 */
	public function getNextAutoNumber($category_code)
	{
		$pattern = $this->code_prefix . '-' . $category_code . '-%';

		// Search in product extrafields for existing codes
		$sql = "SELECT value FROM ".$this->db->prefix()."product_extrafields";
		$sql .= " WHERE name = 'advinv_smart_code'";
		$sql .= " AND value LIKE '".$this->db->escape($pattern)."'";
		$sql .= " ORDER BY value DESC LIMIT 1";

		$result = $this->db->query($sql);
		if ($result && $this->db->num_rows($result) > 0) {
			$obj = $this->db->fetch_object($result);
			$last_code = $obj->value;

			// Extract the number part
			$parts = explode('-', $last_code);
			if (count($parts) >= 3) {
				$last_number = intval($parts[2]);
				$next_number = $last_number + 1;
				return sprintf('%03d', $next_number);
			}
		}

		// If no existing code found, start with 001
		return '001';
	}

	/**
	 * Generate smart code for product
	 *
	 * @param string $category_code Category code
	 * @return string Generated smart code
	 */
	public function generateSmartCode($category_code)
	{
		$auto_number = $this->getNextAutoNumber($category_code);
		return $this->code_prefix . '-' . $category_code . '-' . $auto_number;
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

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("ProductType").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('TypeName').':</b> '.$this->type_name;
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('TypeCode').':</b> '.$this->type_code;
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('CodePrefix').':</b> '.$this->code_prefix;

		$url = dol_buildpath('/advancedinventory/producttype_card.php', 1).'?id='.$this->id;

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
				$label = $langs->trans("ShowProductType");
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

		if ($withpicto) {
			$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		}

		if ($withpicto != 2) {
			$result .= $this->type_name;
		}

		$result .= $linkend;

		global $action, $hookmanager;
		$hookmanager->initHooks(array('producttypedao'));
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
