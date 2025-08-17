<?php
// Enhanced Item Catalog Page for Advanced Inventory Module
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';

$langs->load('products');
$langs->load('advancedinventory@advancedinventory');

// Security check
if (!$user->rights->advancedinventory->read) accessforbidden();

$form = new Form($db);

$page_name = $langs->trans('EnhancedCatalogList');
llxHeader('', $page_name);

// --- Filter Bar ---
print '<form method="GET" action="list.php">';
print '<div class="filter-bar">';

// General Search Box
print '<input type="text" name="search" placeholder="Search Ref, Label, Smart Code" value="'.dol_escape_htmltag(GETPOST('search','alpha')).'">';

// Category Dropdown
print $form->select_all_categories('', GETPOST('catid','int'), 'catid', 0, array(), 1);

// Warehouse Dropdown (custom table)
$sql_warehouses = "SELECT rowid, label FROM llx_advancedinventory_warehouse ORDER BY label";
$res_warehouses = $db->query($sql_warehouses);
print '<select name="warehouseid"><option value="">-- Warehouse --</option>';
while ($objw = $db->fetch_object($res_warehouses)) {
    print '<option value="'.$objw->rowid.'"'.(GETPOST('warehouseid','int')==$objw->rowid?' selected':'').'>'.dol_escape_htmltag($objw->label).'</option>';
}
print '</select>';

// Reorder Point Checkbox
print '<label><input type="checkbox" name="reorderonly" value="1"'.(GETPOST('reorderonly','int')?' checked':'').'> Only show items at reorder point</label>';

print '<input type="submit" value="Filter">';
print '</div>';
print '</form>';

// --- Data Query ---
$search = $db->escape(GETPOST('search','alpha'));
$catid = (int)GETPOST('catid','int');
$warehouseid = (int)GETPOST('warehouseid','int');
$reorderonly = (int)GETPOST('reorderonly','int');

$sql = "SELECT p.rowid, p.ref, p.label, p.photo, ef.smart_code, ef.reorder_point, 
    COALESCE(SUM(sw.reel),0) as total_stock, 
    s.nom as supplier_name, 
    ef.reorder_point,
    p.tosell, p.tobuy
FROM llx_product p
LEFT JOIN llx_product_extrafields ef ON p.rowid = ef.fk_object
LEFT JOIN llx_stock_warehouse sw ON p.rowid = sw.fk_product
LEFT JOIN llx_advancedinventory_supplier_item asi ON asi.fk_product = p.rowid AND asi.is_default = 1
LEFT JOIN llx_societe s ON asi.fk_supplier = s.rowid
WHERE p.entity = " . $conf->entity;

if ($search) {
    $sql .= " AND (p.ref LIKE '%$search%' OR p.label LIKE '%$search%' OR ef.smart_code LIKE '%$search%')";
}
if ($catid) {
    $sql .= " AND p.rowid IN (SELECT fk_product FROM llx_product_categorie WHERE fk_categorie = $catid)";
}
if ($warehouseid) {
    $sql .= " AND sw.fk_warehouse = $warehouseid";
}
$sql .= " GROUP BY p.rowid";
if ($reorderonly) {
    $sql .= " HAVING total_stock <= ef.reorder_point AND ef.reorder_point > 0";
}
$sql .= " ORDER BY p.ref";

$resql = $db->query($sql);
if (!$resql) {
    dol_print_error($db);
} else {
    // --- Results Table ---
    print '<style>.reorder-warning { background: #fff8dc; }</style>';
    print '<table class="liste tableforlist">';
    print '<tr>';
    print '<th>Photo</th><th>Smart Code</th><th>Ref</th><th>Label</th><th>Total Stock</th><th>Default Supplier</th><th>Reorder Point</th><th>Status</th>';
    print '</tr>';
    while ($obj = $db->fetch_object($resql)) {
        $rowclass = ($obj->total_stock <= $obj->reorder_point && $obj->reorder_point > 0) ? 'reorder-warning' : '';
        print '<tr class="'.$rowclass.'">';
        // Product Photo
        print '<td>';
        if ($obj->photo) {
            print '<img src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=product&file='.urlencode($obj->photo).'" height="40">';
        }
        print '</td>';
        // Smart Code
        print '<td>'.dol_escape_htmltag($obj->smart_code).'</td>';
        // Ref
        print '<td>'.dol_escape_htmltag($obj->ref).'</td>';
        // Label
        print '<td>'.dol_escape_htmltag($obj->label).'</td>';
        // Total Stock
        print '<td>'.$obj->total_stock.'</td>';
        // Default Supplier
        print '<td>'.dol_escape_htmltag($obj->supplier_name).'</td>';
        // Reorder Point
        print '<td>'.$obj->reorder_point.'</td>';
        // Status
        $status = ($obj->tosell ? $langs->trans('Sellable') : $langs->trans('NotSellable'));
        print '<td>'.$status.'</td>';
        print '</tr>';
    }
    print '</table>';
}

llxFooter();
$db->close();
