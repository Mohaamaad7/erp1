<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/advancedinventory/class/supplieritem.class.php';

/**
 * Class AdvancedInventoryCatalogManager
 * Manage advanced catalog operations
 */
class AdvancedInventoryCatalogManager
{
    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get products with advanced inventory information
     *
     * @param  array $filters Search filters
     * @param  string $sortfield Sort field
     * @param  string $sortorder Sort order
     * @param  int $limit Limit
     * @param  int $offset Offset
     * @return array Products array
     */
    public function getProductsWithInventoryInfo($filters = array(), $sortfield = 'p.ref', $sortorder = 'ASC', $limit = 0, $offset = 0)
    {
        $products = array();

        $sql = "SELECT p.rowid, p.ref, p.label, p.price, p.fk_product_type,";
        $sql .= " p.tosell, p.tobuy, p.seuil_stock_alerte, p.desiredstock";

        // Add extrafields with LEFT JOIN to avoid missing products
        if ($this->checkExtrafieldsTable()) {
            $sql .= ", pe.advinv_smart_code, pe.advinv_reorder_point, pe.advinv_min_stock,";
            $sql .= " pe.advinv_max_stock, pe.advinv_batch_tracking, pe.advinv_shelf_life, pe.advinv_lead_time";
        } else {
            $sql .= ", NULL as advinv_smart_code, NULL as advinv_reorder_point, NULL as advinv_min_stock,";
            $sql .= " NULL as advinv_max_stock, NULL as advinv_batch_tracking, NULL as advinv_shelf_life, NULL as advinv_lead_time";
        }

        // Add stock info if stock table exists
        if ($this->checkStockTable()) {
            $sql .= ", COALESCE(stock_total.total_stock, 0) as total_stock";
        } else {
            $sql .= ", 0 as total_stock";
        }

        // Add supplier info if supplier table exists
        if ($this->checkSupplierTable()) {
            $sql .= ", COALESCE(supplier_count.supplier_count, 0) as supplier_count, ds.supplier_name as default_supplier_name, ds.supplier_part_num as default_part_num";
        } else {
            $sql .= ", 0 as supplier_count, NULL as default_supplier_name, NULL as default_part_num";
        }

        $sql .= " FROM ".MAIN_DB_PREFIX."product as p";

        // Add extrafields table if exists
        if ($this->checkExtrafieldsTable()) {
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_extrafields as pe ON p.rowid = pe.fk_object";
        }

        // Add stock subquery if table exists
        if ($this->checkStockTable()) {
            $sql .= " LEFT JOIN (";
            $sql .= "   SELECT fk_product, SUM(reel) as total_stock";
            $sql .= "   FROM ".MAIN_DB_PREFIX."product_stock";
            $sql .= "   GROUP BY fk_product";
            $sql .= " ) as stock_total ON p.rowid = stock_total.fk_product";
        }

        // Add supplier info if table exists
        if ($this->checkSupplierTable()) {
            // Supplier count subquery
            $sql .= " LEFT JOIN (";
            $sql .= "   SELECT fk_product, COUNT(*) as supplier_count";
            $sql .= "   FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item";
            $sql .= "   WHERE status = 1";
            $sql .= "   GROUP BY fk_product";
            $sql .= " ) as supplier_count ON p.rowid = supplier_count.fk_product";

            // Default supplier subquery
            $sql .= " LEFT JOIN (";
            $sql .= "   SELECT si.fk_product, s.nom as supplier_name, si.supplier_part_num";
            $sql .= "   FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item as si";
            $sql .= "   LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON si.fk_soc = s.rowid";
            $sql .= "   WHERE si.is_default = 1 AND si.status = 1";
            $sql .= " ) as ds ON p.rowid = ds.fk_product";
        }

        $sql .= " WHERE p.entity IN (".getEntity('product').")";

        // Apply filters safely - FIX: Check if filters exist and not empty
        if (isset($filters['search_ref']) && !empty(trim($filters['search_ref']))) {
            $sql .= " AND p.ref LIKE '%".$this->db->escape(trim($filters['search_ref']))."%'";
        }
        if (isset($filters['search_label']) && !empty(trim($filters['search_label']))) {
            $sql .= " AND p.label LIKE '%".$this->db->escape(trim($filters['search_label']))."%'";
        }
        if (isset($filters['search_smart_code']) && !empty(trim($filters['search_smart_code'])) && $this->checkExtrafieldsTable()) {
            $sql .= " AND pe.advinv_smart_code LIKE '%".$this->db->escape(trim($filters['search_smart_code']))."%'";
        }
        if (isset($filters['search_part_number']) && !empty(trim($filters['search_part_number'])) && $this->checkSupplierTable()) {
            $sql .= " AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item si3 WHERE si3.fk_product = p.rowid AND si3.supplier_part_num LIKE '%".$this->db->escape(trim($filters['search_part_number']))."%')";
        }

        // FIX: Type filter should check for numeric values properly
        if (isset($filters['type']) && $filters['type'] !== '' && $filters['type'] != '-1' && $filters['type'] >= 0) {
            $sql .= " AND p.fk_product_type = ".((int) $filters['type']);
        }
        if (isset($filters['tosell']) && $filters['tosell'] !== '' && $filters['tosell'] != '-1') {
            $sql .= " AND p.tosell = ".((int) $filters['tosell']);
        }
        if (isset($filters['tobuy']) && $filters['tobuy'] !== '' && $filters['tobuy'] != '-1') {
            $sql .= " AND p.tobuy = ".((int) $filters['tobuy']);
        }

        // FIX: Reorder alert filter
        if (isset($filters['reorder_alert']) && !empty($filters['reorder_alert']) && $this->checkExtrafieldsTable()) {
            $sql .= " AND pe.advinv_reorder_point > 0";
            if ($this->checkStockTable()) {
                $sql .= " AND COALESCE(stock_total.total_stock, 0) <= pe.advinv_reorder_point";
            }
        }

        // FIX: Supplier filter
        if (isset($filters['supplier_id']) && $filters['supplier_id'] > 0 && $this->checkSupplierTable()) {

            $sql .= " AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item si4 WHERE si4.fk_product = p.rowid AND si4.fk_soc = ".((int) $filters['supplier_id']).")";
        }

        // Add sorting - validate sortfield to prevent SQL injection
        $allowed_sort_fields = array(
            'p.ref', 'p.label', 'p.fk_product_type', 'p.tosell', 'p.tobuy',
            'pe.advinv_smart_code', 'pe.advinv_reorder_point', 'total_stock',
            'ds.supplier_name', 'ds.supplier_part_num'
        );

        if ($sortfield && in_array($sortfield, $allowed_sort_fields) && in_array(strtoupper($sortorder), array('ASC', 'DESC'))) {
            $sql .= " ORDER BY ".$sortfield." ".strtoupper($sortorder);
        } else {
            $sql .= " ORDER BY p.ref ASC";
        }

        // Add pagination
        if ($limit > 0) {
            $sql .= $this->db->plimit($limit, $offset);
        }

        // Debug the query
        dol_syslog(get_class($this)."::getProductsWithInventoryInfo SQL: ".$sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $products[] = $obj;
            }
            $this->db->free($resql);
        } else {
            dol_syslog(get_class($this)."::getProductsWithInventoryInfo SQL Error: ".$this->db->lasterror(), LOG_ERR);
        }

        return $products;
    }

    /**
     * Check if extrafields table exists
     *
     * @return bool
     */
    private function checkExtrafieldsTable()
    {
        $sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."product_extrafields'";
        $resql = $this->db->query($sql);
        return ($resql && $this->db->num_rows($resql) > 0);
    }

    /**
     * Check if stock table exists
     *
     * @return bool
     */
    private function checkStockTable()
    {
        $sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."product_stock'";
        $resql = $this->db->query($sql);
        return ($resql && $this->db->num_rows($resql) > 0);
    }

    /**
     * Check if supplier table exists
     *
     * @return bool
     */
    private function checkSupplierTable()
    {
        $sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX."advancedinventory_supplier_item'";
        $resql = $this->db->query($sql);
        return ($resql && $this->db->num_rows($resql) > 0);
    }

    /**
     * Get total count of products (for pagination)
     *
     * @param  array $filters Search filters
     * @return int   Total count
     */
    /**
     * Get total count of products (for pagination)
     *
     * @param  array $filters Search filters
     * @return int   Total count
     */
    public function getProductsCount($filters = array())
    {
        $sql = "SELECT COUNT(DISTINCT p.rowid) as total";
        $sql .= " FROM ".MAIN_DB_PREFIX."product as p";

        if ($this->checkExtrafieldsTable()) {
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_extrafields as pe ON p.rowid = pe.fk_object";
        }

        if (isset($filters['reorder_alert']) && !empty($filters['reorder_alert']) && $this->checkStockTable()) {
            $sql .= " LEFT JOIN (";
            $sql .= "     SELECT fk_product, SUM(reel) as total_stock";
            $sql .= "     FROM ".MAIN_DB_PREFIX."product_stock";
            $sql .= "     GROUP BY fk_product";
            $sql .= " ) as stock_total ON p.rowid = stock_total.fk_product";
        }

        $sql .= " WHERE p.entity IN (".getEntity('product').")";

        // --- نفس منطق الفلترة من الدالة الرئيسية ---
        if (isset($filters['search_ref']) && !empty(trim($filters['search_ref']))) {
            $sql .= " AND p.ref LIKE '%".$this->db->escape(trim($filters['search_ref']))."%'";
        }
        if (isset($filters['search_label']) && !empty(trim($filters['search_label']))) {
            $sql .= " AND p.label LIKE '%".$this->db->escape(trim($filters['search_label']))."%'";
        }
        if (!empty(trim($filters['search_smart_code'])) && $this->checkExtrafieldsTable()) {
            $sql .= " AND pe.advinv_smart_code LIKE '%".$this->db->escape(trim($filters['search_smart_code']))."%'";
        }
        if (isset($filters['search_part_number']) && !empty(trim($filters['search_part_number'])) && $this->checkSupplierTable()) {
            $sql .= " AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item si3 WHERE si3.fk_product = p.rowid AND si3.supplier_part_num LIKE '%".$this->db->escape(trim($filters['search_part_number']))."%')";
        }
        if (isset($filters['type']) && $filters['type'] !== '' && $filters['type'] != '-1') {
            $sql .= " AND p.fk_product_type = ".((int) $filters['type']);
        }
        if (isset($filters['tosell']) && $filters['tosell'] !== '' && $filters['tosell'] != '-1') {
            $sql .= " AND p.tosell = ".((int) $filters['tosell']);
        }
        if (isset($filters['tobuy']) && $filters['tobuy'] !== '' && $filters['tobuy'] != '-1') {
            $sql .= " AND p.tobuy = ".((int) $filters['tobuy']);
        }
        if (isset($filters['reorder_alert']) && !empty($filters['reorder_alert']) && $this->checkExtrafieldsTable()) {
            $sql .= " AND pe.advinv_reorder_point > 0";
            if ($this->checkStockTable()) {
                $sql .= " AND COALESCE(stock_total.total_stock, 0) <= pe.advinv_reorder_point";
            }
        }
        if (isset($filters['supplier_id']) && !empty($filters['supplier_id']) && $this->checkSupplierTable()) {
            $sql .= " AND EXISTS (SELECT 1 FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item si4 WHERE si4.fk_product = p.rowid AND si4.fk_soc = ".((int) $filters['supplier_id']).")";
        }
        // --- نهاية منطقة الفلترة المنسوخة ---

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            return $obj->total;
        }

        return 0;
    }    /**
     * Get catalog statistics
     *
     * @return array Statistics
     */
    public function getCatalogStatistics()
    {
        $stats = array(
            'total_products' => 0,
            'products_with_smart_code' => 0,
            'products_with_suppliers' => 0,
            'products_below_reorder' => 0,
            'total_suppliers' => 0
        );

        // Total products
        $sql = "SELECT COUNT(*) as total FROM ".MAIN_DB_PREFIX."product WHERE entity IN (".getEntity('product').")";
        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $stats['total_products'] = $obj->total;
        }

        // Products with smart code (only if extrafields table exists)
        if ($this->checkExtrafieldsTable()) {
            $sql = "SELECT COUNT(*) as total FROM ".MAIN_DB_PREFIX."product p";
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_extrafields pe ON p.rowid = pe.fk_object";
            $sql .= " WHERE pe.advinv_smart_code IS NOT NULL AND pe.advinv_smart_code != ''";
            $sql .= " AND p.entity IN (".getEntity('product').")";
            $resql = $this->db->query($sql);
            if ($resql) {
                $obj = $this->db->fetch_object($resql);
                $stats['products_with_smart_code'] = $obj->total;
            }

            // Products below reorder point - FIX: Better calculation
            $sql = "SELECT COUNT(DISTINCT p.rowid) as total";
            $sql .= " FROM ".MAIN_DB_PREFIX."product p";
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_extrafields pe ON p.rowid = pe.fk_object";
            if ($this->checkStockTable()) {
                $sql .= " LEFT JOIN (SELECT fk_product, SUM(reel) as total_stock FROM ".MAIN_DB_PREFIX."product_stock GROUP BY fk_product) ps ON p.rowid = ps.fk_product";
                $sql .= " WHERE pe.advinv_reorder_point > 0 AND COALESCE(ps.total_stock, 0) <= pe.advinv_reorder_point";
            } else {
                $sql .= " WHERE pe.advinv_reorder_point > 0";
            }
            $sql .= " AND p.entity IN (".getEntity('product').")";

            $resql = $this->db->query($sql);
            if ($resql) {
                $obj = $this->db->fetch_object($resql);
                $stats['products_below_reorder'] = $obj->total;
            }
        }

        // Products with suppliers (only if supplier table exists)
        if ($this->checkSupplierTable()) {
            $sql = "SELECT COUNT(DISTINCT si.fk_product) as total";
            $sql .= " FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item si";
            $sql .= " INNER JOIN ".MAIN_DB_PREFIX."product p ON si.fk_product = p.rowid";
            $sql .= " WHERE si.status = 1 AND p.entity IN (".getEntity('product').")";
            $resql = $this->db->query($sql);
            if ($resql) {
                $obj = $this->db->fetch_object($resql);
                $stats['products_with_suppliers'] = $obj->total;
            }

            // Total suppliers
            $sql = "SELECT COUNT(DISTINCT si.fk_soc) as total";
            $sql .= " FROM ".MAIN_DB_PREFIX."advancedinventory_supplier_item si";
            $sql .= " WHERE si.status = 1";
            $resql = $this->db->query($sql);
            if ($resql) {
                $obj = $this->db->fetch_object($resql);
                $stats['total_suppliers'] = $obj->total;
            }
        }

        return $stats;
    }

    /**
     * Generate smart code for product
     *
     * @param  int    $product_type Product type (0=product, 1=service)
     * @param  string $category_code Category code
     * @param  string $origin_code Origin code (00=local, 01=imported)
     * @return string Smart code
     */
    public function generateSmartCode($product_type = 0, $category_code = '4526', $origin_code = '00')
    {
        // Simple sequential number for now
        $next_seq = 1;

        if ($this->checkExtrafieldsTable()) {
            $sql = "SELECT pe.advinv_smart_code";
            $sql .= " FROM ".MAIN_DB_PREFIX."product_extrafields as pe";
            $sql .= " WHERE pe.advinv_smart_code LIKE '".$this->db->escape($category_code)."-".$this->db->escape($origin_code)."-%'";
            $sql .= " ORDER BY pe.advinv_smart_code DESC LIMIT 1";

            $resql = $this->db->query($sql);
            if ($resql && $this->db->num_rows($resql) > 0) {
                $obj = $this->db->fetch_object($resql);
                $parts = explode('-', $obj->advinv_smart_code);
                if (count($parts) >= 4) {
                    $last_seq = intval($parts[2].$parts[3]);
                    $next_seq = $last_seq + 1;
                }
            }
        }

        // Format: XXXX-XX-XXX-XXXX
        $seq_str = str_pad($next_seq, 7, '0', STR_PAD_LEFT);
        $part1 = substr($seq_str, 0, 3);
        $part2 = substr($seq_str, 3, 4);

        return $category_code.'-'.$origin_code.'-'.$part1.'-'.$part2;
    }

    /**
     * Update reorder point for product
     *
     * @param  int   $product_id Product ID
     * @param  float $reorder_point New reorder point
     * @param  User  $user User making the change
     * @return int   <0 if KO, >0 if OK
     */
    public function updateReorderPoint($product_id, $reorder_point, $user)
    {
        if (!$this->checkExtrafieldsTable()) {
            return -1; // Table doesn't exist
        }

        // Check if extrafields record exists
        $sql = "SELECT fk_object FROM ".MAIN_DB_PREFIX."product_extrafields WHERE fk_object = ".((int) $product_id);
        $resql = $this->db->query($sql);

        if ($resql && $this->db->num_rows($resql) > 0) {
            // Update existing record
            $sql = "UPDATE ".MAIN_DB_PREFIX."product_extrafields";
            $sql .= " SET advinv_reorder_point = ".((float) $reorder_point);
            $sql .= " WHERE fk_object = ".((int) $product_id);
        } else {
            // Insert new record
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."product_extrafields";
            $sql .= " (fk_object, advinv_reorder_point)";
            $sql .= " VALUES (".((int) $product_id).", ".((float) $reorder_point).")";
        }

        return $this->db->query($sql) ? 1 : -1;
    }

    /**
     * Calculate suggested reorder point based on consumption
     *
     * @param  int   $product_id Product ID
     * @param  int   $days_back Days to analyze (default 90)
     * @return array Suggested reorder point and statistics
     */
    public function calculateReorderPoint($product_id, $days_back = 90)
    {
        $result = array(
            'suggested_reorder_point' => 10, // Default value
            'avg_daily_consumption' => 0,
            'max_daily_consumption' => 0,
            'lead_time_days' => 30,
            'safety_stock' => 5
        );

        // For now, return a simple calculation
        // This can be enhanced later with actual consumption data
        $result['suggested_reorder_point'] = 10;

        return $result;
    }

    /**
     * Get products that need reorder
     *
     * @return array Products below reorder point
     */
    public function getProductsBelowReorderPoint()
    {
        $products = array();

        if (!$this->checkExtrafieldsTable() || !$this->checkStockTable()) {
            return $products;
        }

        $sql = "SELECT p.rowid, p.ref, p.label, pe.advinv_reorder_point,";
        $sql .= " COALESCE(SUM(ps.reel), 0) as current_stock,";
        $sql .= " (pe.advinv_reorder_point - COALESCE(SUM(ps.reel), 0)) as shortage";
        $sql .= " FROM ".MAIN_DB_PREFIX."product as p";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_extrafields as pe ON p.rowid = pe.fk_object";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_stock as ps ON p.rowid = ps.fk_product";
        $sql .= " WHERE pe.advinv_reorder_point > 0";
        $sql .= " AND p.entity IN (".getEntity('product').")";
        $sql .= " GROUP BY p.rowid, p.ref, p.label, pe.advinv_reorder_point";
        $sql .= " HAVING COALESCE(SUM(ps.reel), 0) <= pe.advinv_reorder_point";
        $sql .= " ORDER BY shortage DESC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $products[] = $obj;
            }
        }

        return $products;
    }

    /**
     * Get products by supplier
     *
     * @param  int   $supplier_id Supplier ID
     * @return array Products for this supplier
     */
    public function getProductsBySupplier($supplier_id)
    {
        $products = array();

        if (!$this->checkSupplierTable()) {
            return $products;
        }

        $sql = "SELECT p.rowid, p.ref, p.label, si.supplier_part_num, si.price,";
        $sql .= " si.lead_time_days, si.is_default";
        if ($this->checkStockTable()) {
            $sql .= ", COALESCE(SUM(ps.reel), 0) as current_stock";
        } else {
            $sql .= ", 0 as current_stock";
        }
        $sql .= " FROM ".MAIN_DB_PREFIX."product as p";
        $sql .= " INNER JOIN ".MAIN_DB_PREFIX."advancedinventory_supplier_item as si ON p.rowid = si.fk_product";
        if ($this->checkStockTable()) {
            $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."product_stock as ps ON p.rowid = ps.fk_product";
        }
        $sql .= " WHERE si.fk_soc = ".((int) $supplier_id);
        $sql .= " AND si.status = 1";
        $sql .= " AND p.entity IN (".getEntity('product').")";
        if ($this->checkStockTable()) {
            $sql .= " GROUP BY p.rowid, p.ref, p.label, si.supplier_part_num, si.price, si.lead_time_days, si.is_default";
        }
        $sql .= " ORDER BY si.is_default DESC, p.ref ASC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $products[] = $obj;
            }
        }

        return $products;
    }

    /**
     * Get single product with all inventory info - for AJAX refresh
     *
     * @param  int   $product_id Product ID
     * @return array Product info
     */
    public function getSingleProductInfo($product_id)
    {
        $filters = array(); // No filters for single product
        $products = $this->getProductsWithInventoryInfo($filters, 'p.ref', 'ASC', 1, 0);

        foreach ($products as $product) {
            if ($product->rowid == $product_id) {
                return $product;
            }
        }

        return null;
    }
}
