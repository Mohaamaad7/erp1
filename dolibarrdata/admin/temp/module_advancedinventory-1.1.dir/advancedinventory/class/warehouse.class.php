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
 * Class AdvancedInventoryWarehouse
 * Manage warehouses with hierarchical structure
 */
class AdvancedInventoryWarehouse extends CommonObject
{
	/**
	 * @var string ID of module
	 */
	public $module = 'advancedinventory';

	/**
	 * @var string Element type
	 */
	public $element = 'advancedinventory_warehouse';

	/**
	 * @var string Table name
	 */
	public $table_element = 'advancedinventory_warehouse';

	/**
	 * @var string Picto
	 */
	public $picto = 'stock';

	/**
	 * @var array Fields definition
	 */
	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'ID', 'enabled' => 1, 'visible' => -1, 'position' => 1, 'notnull' => 1, 'index' => 1, 'comment' => 'Id'),
		'ref' => array('type' => 'varchar(128)', 'label' => 'Ref', 'enabled' => 1, 'visible' => 1, 'position' => 10, 'notnull' => 1, 'showoncombobox' => 1, 'searchall' => 1),
		'label' => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'visible' => 1, 'position' => 20, 'notnull' => 1, 'searchall' => 1, 'showoncombobox' => 2),
		'description' => array('type' => 'text', 'label' => 'Description', 'enabled' => 1, 'visible' => 3, 'position' => 30),
		'address' => array('type' => 'varchar(255)', 'label' => 'Address', 'enabled' => 1, 'visible' => 1, 'position' => 40),
		'zip' => array('type' => 'varchar(25)', 'label' => 'Zip', 'enabled' => 1, 'visible' => 1, 'position' => 50),
		'town' => array('type' => 'varchar(50)', 'label' => 'Town', 'enabled' => 1, 'visible' => 1, 'position' => 60),
		'fk_country' => array('type' => 'integer', 'label' => 'Country', 'enabled' => 1, 'visible' => 1, 'position' => 70),
		'phone' => array('type' => 'varchar(20)', 'label' => 'Phone', 'enabled' => 1, 'visible' => 1, 'position' => 80),
		'email' => array('type' => 'varchar(128)', 'label' => 'Email', 'enabled' => 1, 'visible' => 1, 'position' => 90),
		'fk_parent' => array('type' => 'integer', 'label' => 'ParentWarehouse', 'enabled' => 1, 'visible' => 1, 'position' => 100),
		'warehouse_type' => array('type' => 'varchar(50)', 'label' => 'Type', 'enabled' => 1, 'visible' => 1, 'position' => 110, 'arrayofkeyval' => array('main' => 'Main', 'hall' => 'Hall', 'shelf' => 'Shelf', 'box' => 'Box')),
		'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'visible' => 1, 'position' => 120, 'notnull' => 1, 'default' => 1, 'arrayofkeyval' => array('0' => 'Disabled', '1' => 'Enabled')),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'visible' => -2, 'position' => 500, 'notnull' => 1),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'visible' => -2, 'position' => 501, 'notnull' => 1),
		'fk_user_creat' => array('type' => 'integer', 'label' => 'UserAuthor', 'enabled' => 1, 'visible' => -2, 'position' => 510, 'notnull' => 1),
		'fk_user_modif' => array('type' => 'integer', 'label' => 'UserModif', 'enabled' => 1, 'visible' => -2, 'position' => 511)
	);

	// Standard fields
	public $rowid;
	public $ref;
	public $label;
	public $description;
	public $address;
	public $zip;
	public $town;
	public $fk_country;
	public $phone;
	public $email;
	public $fk_parent;
	public $warehouse_type;
	public $status;
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
		$this->isextrafieldmanaged = 1;

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
		if (empty($this->ref)) {
			$this->ref = $this->getNextNumRef();
		}

		return $this->createCommon($user, $notrigger);
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
	 * Get next ref
	 *
	 * @return string
	 */
	public function getNextNumRef()
	{
		global $conf, $db;

		$sql = "SELECT ref FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE ref LIKE 'WH%'";
		$sql .= " ORDER BY ref DESC LIMIT 1";

		$resql = $db->query($sql);
		if ($resql) {
			$num_rows = $db->num_rows($resql);

			if ($num_rows > 0) {
				$obj = $db->fetch_object($resql);
				$number = intval(substr($obj->ref, 2)) + 1;
				return "WH" . str_pad($number, 5, "0", STR_PAD_LEFT);
			}
		}

		return "WH00001";
	}
	/**
	 * Get parent warehouse
	 *
	 * @return AdvancedInventoryWarehouse|null
	 */
	public function getParent()
	{
		if (!empty($this->fk_parent)) {
			$parent = new AdvancedInventoryWarehouse($this->db);
			if ($parent->fetch($this->fk_parent) > 0) {
				return $parent;
			}
		}
		return null;
	}

	/**
	 * Get children warehouses
	 *
	 * @return array Array of AdvancedInventoryWarehouse objects
	 */
	public function getChildren()
	{
		$children = array();

		$sql = "SELECT rowid";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE fk_parent = ".((int) $this->id);
		$sql .= " AND entity = ".$this->entity;
		$sql .= " ORDER BY label ASC";

		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$child = new AdvancedInventoryWarehouse($this->db);
				if ($child->fetch($obj->rowid) > 0) {
					$children[] = $child;
				}
			}
		}

		return $children;
	}

	/**
	 * Get full path (hierarchy)
	 *
	 * @param  string $separator Separator between levels
	 * @return string            Full path
	 */
	public function getFullPath($separator = ' > ')
	{
		$path = array();
		$current = clone $this;

		while ($current) {
			array_unshift($path, $current->label);
			$current = $current->getParent();
		}

		return implode($separator, $path);
	}

	/**
	 * Get warehouse type label
	 *
	 * @return string
	 */
	public function getTypeLabel()
	{
		global $langs;

		$types = array(
			'main' => $langs->trans('MainWarehouse'),
			'hall' => $langs->trans('Hall'),
			'shelf' => $langs->trans('Shelf'),
			'box' => $langs->trans('Box')
		);

		return isset($types[$this->warehouse_type]) ? $types[$this->warehouse_type] : $this->warehouse_type;
	}
}
