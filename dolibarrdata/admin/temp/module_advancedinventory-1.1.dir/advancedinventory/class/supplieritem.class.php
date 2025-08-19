<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class AdvancedInventorySupplierItem
 * Manage multiple suppliers per item with detailed information
 */
class AdvancedInventorySupplierItem extends CommonObject
{
	/**
	 * @var string ID of module
	 */
	public $module = 'advancedinventory';

	/**
	 * @var string Element type
	 */
	public $element = 'advancedinventory_supplier_item';

	/**
	 * @var string Table name
	 */
	public $table_element = 'advancedinventory_supplier_item';

	/**
	 * @var string Picto
	 */
	public $picto = 'company';

	/**
	 * @var array Fields definition
	 */
	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'ID', 'enabled' => 1, 'visible' => -1, 'position' => 1, 'notnull' => 1, 'index' => 1),
		'fk_product' => array('type' => 'integer', 'label' => 'Product', 'enabled' => 1, 'visible' => 1, 'position' => 10, 'notnull' => 1, 'index' => 1),
		'fk_soc' => array('type' => 'integer', 'label' => 'Supplier', 'enabled' => 1, 'visible' => 1, 'position' => 20, 'notnull' => 1, 'index' => 1),
		'supplier_part_num' => array('type' => 'varchar(128)', 'label' => 'SupplierPartNumber', 'enabled' => 1, 'visible' => 1, 'position' => 30),
		'supplier_label' => array('type' => 'varchar(255)', 'label' => 'SupplierLabel', 'enabled' => 1, 'visible' => 1, 'position' => 40),
		'lead_time_days' => array('type' => 'integer', 'label' => 'LeadTimeDays', 'enabled' => 1, 'visible' => 1, 'position' => 50, 'default' => 0),
		'min_order_qty' => array('type' => 'double(24,8)', 'label' => 'MinOrderQty', 'enabled' => 1, 'visible' => 1, 'position' => 60, 'default' => 1),
		'price' => array('type' => 'double(24,8)', 'label' => 'Price', 'enabled' => 1, 'visible' => 1, 'position' => 70, 'default' => 0),
		'fk_multicurrency' => array('type' => 'integer', 'label' => 'Currency', 'enabled' => 1, 'visible' => 1, 'position' => 80),
		'multicurrency_price' => array('type' => 'double(24,8)', 'label' => 'MulticurrencyPrice', 'enabled' => 1, 'visible' => 1, 'position' => 90, 'default' => 0),
		'is_default' => array('type' => 'integer', 'label' => 'DefaultSupplier', 'enabled' => 1, 'visible' => 1, 'position' => 100, 'default' => 0),
		'last_order_date' => array('type' => 'date', 'label' => 'LastOrderDate', 'enabled' => 1, 'visible' => 1, 'position' => 110),
		'quality_rating' => array('type' => 'integer', 'label' => 'QualityRating', 'enabled' => 1, 'visible' => 1, 'position' => 120, 'default' => 0),
		'delivery_rating' => array('type' => 'integer', 'label' => 'DeliveryRating', 'enabled' => 1, 'visible' => 1, 'position' => 130, 'default' => 0),
		'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'visible' => 1, 'position' => 140, 'notnull' => 1, 'default' => 1),
		'note_public' => array('type' => 'text', 'label' => 'NotePublic', 'enabled' => 1, 'visible' => 3, 'position' => 150),
		'note_private' => array('type' => 'text', 'label' => 'NotePrivate', 'enabled' => 1, 'visible' => 3, 'position' => 160),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'visible' => -2, 'position' => 500, 'notnull' => 1),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'visible' => -2, 'position' => 501, 'notnull' => 1),
		'fk_user_creat' => array('type' => 'integer', 'label' => 'UserAuthor', 'enabled' => 1, 'visible' => -2, 'position' => 510, 'notnull' => 1),
		'fk_user_modif' => array('type' => 'integer', 'label' => 'UserModif', 'enabled' => 1, 'visible' => -2, 'position' => 511)
	);

	// Standard fields
	public $rowid;
	public $fk_product;
	public $fk_soc;
	public $supplier_part_num;
	public $supplier_label;
	public $lead_time_days;
	public $min_order_qty;
	public $price;
	public $fk_multicurrency;
	public $multicurrency_price;
	public $is_default;
	public $last_order_date;
	public $quality_rating;
	public $delivery_rating;
	public $status;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs;

		$this->db = $db;
		$this->ismultientitymanaged = 1;
		$this->isextrafieldmanaged = 0;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}
	}

	/**
	 * Create object in database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		global $conf;

		$error = 0;

		// Check if combination already exists
		if ($this->supplierItemExists($this->fk_product, $this->fk_soc)) {
			$this->error = 'SupplierItemAlreadyExists';
			return -1;
		}

		$this->db->begin();

		// If this is set as default, unset others
		if ($this->is_default) {
			$this->unsetOtherDefaults($this->fk_product);
		}

		$result = $this->createCommon($user, $notrigger);

		if ($result < 0) {
			$error++;
			$this->db->rollback();
			return -1;
		}

		$this->db->commit();
		return $this->id;
	}

	/**
	 * Load object in memory from database
	 *
	 * @param  int    $id   Id object
	 * @param  string $ref  Ref
	 * @return int          <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		return $this->fetchCommon($id, $ref);
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
		$this->db->begin();

		// If this is set as default, unset others
		if ($this->is_default) {
			$this->unsetOtherDefaults($this->fk_product, $this->id);
		}

		$result = $this->updateCommon($user, $notrigger);

		if ($result < 0) {
			$this->db->rollback();
			return -1;
		}

		$this->db->commit();
		return 1;
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
	 * Check if supplier item combination already exists
	 *
	 * @param  int $fk_product Product ID
	 * @param  int $fk_soc     Supplier ID
	 * @param  int $exclude_id ID to exclude from check (for updates)
	 * @return bool            True if exists
	 */
	public function supplierItemExists($fk_product, $fk_soc, $exclude_id = 0)
	{
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE fk_product = ".((int) $fk_product);
		$sql .= " AND fk_soc = ".((int) $fk_soc);
		if ($exclude_id > 0) {
			$sql .= " AND rowid != ".((int) $exclude_id);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			return $this->db->num_rows($resql) > 0;
		}
		return false;
	}

	/**
	 * Unset default flag for other suppliers of the same product
	 *
	 * @param  int $fk_product Product ID
	 * @param  int $exclude_id ID to exclude from update
	 * @return int             <0 if KO, >0 if OK
	 */
	private function unsetOtherDefaults($fk_product, $exclude_id = 0)
	{
		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " SET is_default = 0";
		$sql .= " WHERE fk_product = ".((int) $fk_product);
		if ($exclude_id > 0) {
			$sql .= " AND rowid != ".((int) $exclude_id);
		}

		return $this->db->query($sql);
	}

	/**
	 * Get all suppliers for a product
	 *
	 * @param  int   $fk_product Product ID
	 * @param  int   $status     Filter by status (-1 = all, 0 = inactive, 1 = active)
	 * @return array             Array of supplier items
	 */
	public static function getSuppliersByProduct($db, $fk_product, $status = 1)
	{
		$suppliers = array();

		$sql = "SELECT si.*, s.nom as supplier_name, s.code_fournisseur";
		$sql .= " FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item as si";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON si.fk_soc = s.rowid";
		$sql .= " WHERE si.fk_product = ".((int) $fk_product);
		if ($status >= 0) {
			$sql .= " AND si.status = ".((int) $status);
		}
		$sql .= " ORDER BY si.is_default DESC, s.nom ASC";

		$resql = $db->query($sql);
		if ($resql) {
			while ($obj = $db->fetch_object($resql)) {
				$suppliers[] = $obj;
			}
		}

		return $suppliers;
	}

	/**
	 * Get default supplier for a product
	 *
	 * @param  DoliDB $db         Database handler
	 * @param  int    $fk_product Product ID
	 * @return object|null        Supplier object or null
	 */
	public static function getDefaultSupplier($db, $fk_product)
	{
		$sql = "SELECT si.*, s.nom as supplier_name, s.code_fournisseur";
		$sql .= " FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item as si";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON si.fk_soc = s.rowid";
		$sql .= " WHERE si.fk_product = ".((int) $fk_product);
		$sql .= " AND si.is_default = 1";
		$sql .= " AND si.status = 1";

		$resql = $db->query($sql);
		if ($resql && $db->num_rows($resql) > 0) {
			return $db->fetch_object($resql);
		}

		return null;
	}

	/**
	 * Get supplier by part number
	 *
	 * @param  DoliDB $db              Database handler
	 * @param  string $supplier_part_num Supplier part number
	 * @param  int    $fk_soc          Supplier ID (optional)
	 * @return object|null             Supplier item object or null
	 */
	public static function getByPartNumber($db, $supplier_part_num, $fk_soc = 0)
	{
		$sql = "SELECT si.*, s.nom as supplier_name, p.ref as product_ref, p.label as product_label";
		$sql .= " FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item as si";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON si.fk_soc = s.rowid";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product as p ON si.fk_product = p.rowid";
		$sql .= " WHERE si.supplier_part_num = '".$db->escape($supplier_part_num)."'";
		$sql .= " AND si.status = 1";
		if ($fk_soc > 0) {
			$sql .= " AND si.fk_soc = ".((int) $fk_soc);
		}

		$resql = $db->query($sql);
		if ($resql && $db->num_rows($resql) > 0) {
			return $db->fetch_object($resql);
		}

		return null;
	}

	/**
	 * Return label of status
	 *
	 * @param  int    $mode 0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=short label + picto, 6=Long label + picto
	 * @return string       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 * Return label of a status
	 *
	 * @param  int    $status Status
	 * @param  int    $mode   0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=short label + picto, 6=Long label + picto
	 * @return string         Label of status
	 */
	public static function LibStatut($status, $mode = 0)
	{
		global $langs;

		if (empty($status)) {
			$status = 0;
		}

		$labelStatus = $langs->transnoentitiesnoconv('Disabled');
		$labelStatusShort = $langs->transnoentitiesnoconv('Disabled');
		$statusType = 'status5';

		if ($status == 1) {
			$labelStatus = $langs->transnoentitiesnoconv('Enabled');
			$labelStatusShort = $langs->transnoentitiesnoconv('Enabled');
			$statusType = 'status4';
		}

		return dolGetStatus($labelStatus, $labelStatusShort, '', $statusType, $mode);
	}

	/**
	 * Get rating stars HTML
	 *
	 * @param  int    $rating Rating value (1-5)
	 * @return string         HTML stars
	 */
	public function getRatingStars($rating)
	{
		$stars = '';
		for ($i = 1; $i <= 5; $i++) {
			if ($i <= $rating) {
				$stars .= '<i class="fa fa-star" style="color: #ffc107;"></i>';
			} else {
				$stars .= '<i class="fa fa-star-o" style="color: #ddd;"></i>';
			}
		}
		return $stars;
	}

	/**
	 * Get lead time formatted string
	 *
	 * @return string Formatted lead time
	 */
	public function getLeadTimeFormatted()
	{
		global $langs;

		if ($this->lead_time_days <= 0) {
			return '-';
		}

		if ($this->lead_time_days == 1) {
			return '1 '.$langs->trans('Day');
		}

		if ($this->lead_time_days < 7) {
			return $this->lead_time_days.' '.$langs->trans('Days');
		}

		if ($this->lead_time_days < 30) {
			$weeks = round($this->lead_time_days / 7, 1);
			return $weeks.' '.$langs->trans('Weeks');
		}

		$months = round($this->lead_time_days / 30, 1);
		return $months.' '.$langs->trans('Months');
	}

	/**
	 * Update last order date
	 *
	 * @param  int $fk_product Product ID
	 * @param  int $fk_soc     Supplier ID
	 * @return int             <0 if KO, >0 if OK
	 */
	public static function updateLastOrderDate($db, $fk_product, $fk_soc)
	{
		$sql = "UPDATE ".MAIN_DB_PREFIX."advancedinventory_supplier_item";
		$sql .= " SET last_order_date = '".$db->idate(dol_now())."'";
		$sql .= " WHERE fk_product = ".((int) $fk_product);
		$sql .= " AND fk_soc = ".((int) $fk_soc);

		return $db->query($sql);
	}
}
