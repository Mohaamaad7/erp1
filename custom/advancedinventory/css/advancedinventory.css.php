<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 */

//if (!defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');
//if (!defined('NOREQUIREDB'))   define('NOREQUIREDB','1');
if (!defined('NOREQUIRESOC'))    define('NOREQUIRESOC', '1');
//if (!defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');
if (!defined('NOCSRFCHECK'))     define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL', 1);
if (!defined('NOLOGIN'))         define('NOLOGIN', 1);
if (!defined('NOREQUIREMENU'))   define('NOREQUIREMENU', 1);
if (!defined('NOREQUIREHTML'))   define('NOREQUIREHTML', 1);
if (!defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX', '1');

session_cache_limiter('public');

require_once '../../main.inc.php';

// Define css type
header('Content-type: text/css');
?>

/* ========================================
   Advanced Inventory Module Custom CSS
   ======================================== */

/* Product Extrafields - تمييز حقول المنتجات */
tr.trextrafields_advinv_smart_code,
tr.trextrafields_advinv_reorder_point,
tr.trextrafields_advinv_min_stock,
tr.trextrafields_advinv_max_stock,
tr.trextrafields_advinv_batch_tracking,
tr.trextrafields_advinv_shelf_life,
tr.trextrafields_advinv_lead_time {
	background: linear-gradient(to right, #ffffff 0%, #e3f2fd 100%) !important;
}

tr.trextrafields_advinv_smart_code td.titlefield,
tr.trextrafields_advinv_reorder_point td.titlefield,
tr.trextrafields_advinv_min_stock td.titlefield,
tr.trextrafields_advinv_max_stock td.titlefield,
tr.trextrafields_advinv_batch_tracking td.titlefield,
tr.trextrafields_advinv_shelf_life td.titlefield,
tr.trextrafields_advinv_lead_time td.titlefield {
	border-left: 4px solid #2196F3 !important;
	padding-left: 10px !important;
	font-weight: 500;
}

/* Stock Movement Extrafields - تمييز حقول الحركات */
tr.trextrafields_advinv_transfer_ref,
tr.trextrafields_advinv_warehouse_to,
tr.trextrafields_advinv_approval_status,
tr.trextrafields_advinv_approved_by,
tr.trextrafields_advinv_approval_date {
	background: linear-gradient(to right, #ffffff 0%, #e8f5e9 100%) !important;
}

tr.trextrafields_advinv_transfer_ref td.titlefield,
tr.trextrafields_advinv_warehouse_to td.titlefield,
tr.trextrafields_advinv_approval_status td.titlefield,
tr.trextrafields_advinv_approved_by td.titlefield,
tr.trextrafields_advinv_approval_date td.titlefield {
	border-left: 4px solid #4CAF50 !important;
	padding-left: 10px !important;
	font-weight: 500;
}

/* Separators - الفواصل */
tr.trextrafields_advinv_separator_main td,
tr.trextrafields_advinv_mov_separator_main td {
	background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%) !important;
	color: white !important;
	font-weight: bold !important;
	padding: 10px !important;
	text-align: center !important;
	font-size: 14px !important;
	text-transform: uppercase;
	letter-spacing: 1px;
}

tr.trextrafields_advinv_separator_end td,
tr.trextrafields_advinv_mov_separator_end td {
	height: 2px !important;
	background: linear-gradient(90deg, #2a5298 0%, #1e3c72 100%) !important;
	padding: 0 !important;
}

/* Icons for sections */
tr.trextrafields_advinv_separator_main td:before {
	content: "📦 ";
	font-size: 16px;
}

tr.trextrafields_advinv_mov_separator_main td:before {
	content: "🔄 ";
	font-size: 16px;
}

/* Hover effects */
tr[class*="trextrafields_advinv"]:hover {
	transform: translateX(2px);
	transition: all 0.2s ease;
}

/* في القوائم */
.advinv_smart_code,
.advinv_reorder_point,
.advinv_min_stock,
.advinv_max_stock,
.advinv_batch_tracking,
.advinv_shelf_life,
.advinv_lead_time {
	position: relative;
}

.advinv_smart_code:before,
.advinv_reorder_point:before,
.advinv_min_stock:before,
.advinv_max_stock:before,
.advinv_batch_tracking:before,
.advinv_shelf_life:before,
.advinv_lead_time:before {
	content: "ADV";
	position: absolute;
	top: -5px;
	right: -5px;
	background: #2196F3;
	color: white;
	font-size: 9px;
	padding: 1px 3px;
	border-radius: 3px;
	font-weight: bold;
}

/* Badges in lists */
span.badge-advinv {
	background-color: #2196F3;
	color: white;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 11px;
	margin-left: 5px;
}

/* تحسين مظهر الإدخال */
input[name*="options_advinv"],
select[name*="options_advinv"],
textarea[name*="options_advinv"] {
	border: 1px solid #2196F3 !important;
	background-color: #fafafa !important;
}

input[name*="options_advinv"]:focus,
select[name*="options_advinv"]:focus,
textarea[name*="options_advinv"]:focus {
	border-color: #1976D2 !important;
	box-shadow: 0 0 5px rgba(33, 150, 243, 0.3) !important;
}

/* تمييز في صفحة المنتج */
.tabBar .advinv-section {
	border: 1px solid #2196F3;
	border-radius: 5px;
	padding: 10px;
	margin: 10px 0;
	background: linear-gradient(to bottom, #ffffff 0%, #e3f2fd 100%);
}

/* Responsive adjustments */
@media (max-width: 768px) {
	tr[class*="trextrafields_advinv"] td.titlefield {
		border-left-width: 3px !important;
	}
}

.product_extras_advinv_smart_code{
	border: 1px solid #2196F3;
	border-radius: 5px;
	padding: 10px;
	margin: 10px 0;
	background: linear-gradient(to bottom, #ffffff 0%, #e3f2fd 100%);
}

/* Enhanced highlighting for all Advanced Inventory fields */
.trextrafields_advinv_smart_code,
.trextrafields_advinv_reorder_point,
.trextrafields_advinv_min_stock,
.trextrafields_advinv_max_stock,
.trextrafields_advinv_batch_tracking,
.trextrafields_advinv_shelf_life,
.trextrafields_advinv_lead_time {
	position: relative;
}

.trextrafields_advinv_smart_code:after,
.trextrafields_advinv_reorder_point:after,
.trextrafields_advinv_min_stock:after,
.trextrafields_advinv_max_stock:after,
.trextrafields_advinv_batch_tracking:after,
.trextrafields_advinv_shelf_life:after,
.trextrafields_advinv_lead_time:after {
	content: "";
	position: absolute;
	top: 0;
	right: 0;
	bottom: 0;
	width: 3px;
	background: linear-gradient(to bottom, #2196F3, #1976D2);
}

/* Enhanced input styling for Advanced Inventory fields */
input[name*="options_advinv_smart_code"],
input[name*="options_advinv_reorder_point"],
input[name*="options_advinv_min_stock"],
input[name*="options_advinv_max_stock"],
input[name*="options_advinv_batch_tracking"],
input[name*="options_advinv_shelf_life"],
input[name*="options_advinv_lead_time"],
select[name*="options_advinv_smart_code"],
select[name*="options_advinv_reorder_point"],
select[name*="options_advinv_min_stock"],
select[name*="options_advinv_max_stock"],
select[name*="options_advinv_batch_tracking"],
select[name*="options_advinv_shelf_life"],
select[name*="options_advinv_lead_time"] {
	border: 2px solid #2196F3 !important;
	background-color: #f8fbff !important;
	border-radius: 4px !important;
	transition: all 0.3s ease !important;
}

input[name*="options_advinv_smart_code"]:focus,
input[name*="options_advinv_reorder_point"]:focus,
input[name*="options_advinv_min_stock"]:focus,
input[name*="options_advinv_max_stock"]:focus,
input[name*="options_advinv_batch_tracking"]:focus,
input[name*="options_advinv_shelf_life"]:focus,
input[name*="options_advinv_lead_time"]:focus,
select[name*="options_advinv_smart_code"]:focus,
select[name*="options_advinv_reorder_point"]:focus,
select[name*="options_advinv_min_stock"]:focus,
select[name*="options_advinv_max_stock"]:focus,
select[name*="options_advinv_batch_tracking"]:focus,
select[name*="options_advinv_shelf_life"]:focus,
select[name*="options_advinv_lead_time"]:focus {
	border-color: #1976D2 !important;
	box-shadow: 0 0 8px rgba(33, 150, 243, 0.4) !important;
	background-color: #ffffff !important;
}

/* Special styling for boolean fields */
input[type="checkbox"][name*="options_advinv_batch_tracking"] {
	accent-color: #2196F3 !important;
	transform: scale(1.2) !important;
}

/* Enhanced separator styling */
.trextrafields_advinv_separator_main td,
.trextrafields_advinv_mov_separator_main td {
	position: relative;
	overflow: hidden;
}

.trextrafields_advinv_separator_main td:before,
.trextrafields_advinv_mov_separator_main td:before {
	content: "";
	position: absolute;
	top: 0;
	left: -100%;
	width: 100%;
	height: 100%;
	background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
	animation: shimmer 2s infinite;
}

@keyframes shimmer {
	0% { left: -100%; }
	100% { left: 100%; }
}

/* Additional styling for different contexts */
/* Product card page specific styling */
.product_extras_advinv_smart_code,
.product_extras_advinv_reorder_point,
.product_extras_advinv_min_stock,
.product_extras_advinv_max_stock,
.product_extras_advinv_batch_tracking,
.product_extras_advinv_shelf_life,
.product_extras_advinv_lead_time {
	border: 2px solid #2196F3 !important;
	border-radius: 8px !important;
	padding: 15px !important;
	margin: 15px 0 !important;
	background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%) !important;
	box-shadow: 0 2px 8px rgba(33, 150, 243, 0.1) !important;
	position: relative;
}

.product_extras_advinv_smart_code:before,
.product_extras_advinv_reorder_point:before,
.product_extras_advinv_min_stock:before,
.product_extras_advinv_max_stock:before,
.product_extras_advinv_batch_tracking:before,
.product_extras_advinv_shelf_life:before,
.product_extras_advinv_lead_time:before {
	content: "📦 ADV";
	position: absolute;
	top: -10px;
	left: 15px;
	background: #2196F3;
	color: white;
	font-size: 11px;
	padding: 3px 8px;
	border-radius: 12px;
	font-weight: bold;
	box-shadow: 0 2px 4px rgba(33, 150, 243, 0.3);
}

/* Stock movement fields styling */
.stock_mouvement_extras_advinv_transfer_ref,
.stock_mouvement_extras_advinv_warehouse_to,
.stock_mouvement_extras_advinv_approval_status,
.stock_mouvement_extras_advinv_approved_by,
.stock_mouvement_extras_advinv_approval_date {
	border: 2px solid #4CAF50 !important;
	border-radius: 8px !important;
	padding: 15px !important;
	margin: 15px 0 !important;
	background: linear-gradient(135deg, #ffffff 0%, #e8f5e9 100%) !important;
	box-shadow: 0 2px 8px rgba(76, 175, 80, 0.1) !important;
	position: relative;
}

.stock_mouvement_extras_advinv_transfer_ref:before,
.stock_mouvement_extras_advinv_warehouse_to:before,
.stock_mouvement_extras_advinv_approval_status:before,
.stock_mouvement_extras_advinv_approved_by:before,
.stock_mouvement_extras_advinv_approval_date:before {
	content: "🔄 MOV";
	position: absolute;
	top: -10px;
	left: 15px;
	background: #4CAF50;
	color: white;
	font-size: 11px;
	padding: 3px 8px;
	border-radius: 12px;
	font-weight: bold;
	box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
}

/* Enhanced table row styling */
table.trextrafields_advinv_smart_code,
table.trextrafields_advinv_reorder_point,
table.trextrafields_advinv_min_stock,
table.trextrafields_advinv_max_stock,
table.trextrafields_advinv_batch_tracking,
table.trextrafields_advinv_shelf_life,
table.trextrafields_advinv_lead_time {
	border: 1px solid #2196F3 !important;
	border-radius: 6px !important;
	overflow: hidden;
}

/* Print styles */
@media print {
	.trextrafields_advinv_smart_code,
	.trextrafields_advinv_reorder_point,
	.trextrafields_advinv_min_stock,
	.trextrafields_advinv_max_stock,
	.trextrafields_advinv_batch_tracking,
	.trextrafields_advinv_shelf_life,
	.trextrafields_advinv_lead_time {
		background: #f0f8ff !important;
		border: 1px solid #2196F3 !important;
	}

	.trextrafields_advinv_separator_main td,
	.trextrafields_advinv_mov_separator_main td {
		background: #1e3c72 !important;
		color: white !important;
	}
}
.product_extras_advinv_reorder_point{
	background: #1e3c72 !important;
	font-size: larger;
	color: red !important;
}
