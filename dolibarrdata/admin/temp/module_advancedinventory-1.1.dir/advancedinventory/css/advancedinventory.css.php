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

require_once '../../../main.inc.php';

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



/*
 * إضافة هذا CSS لملف: /css/advancedinventory.css.php
 * في نهاية الملف
 */

/* ========================================
   Suppliers Management Enhancements
   ======================================== */

/* Supplier items table styling */
.supplier-items-table {
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	overflow: hidden;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.supplier-items-table .liste_titre {
	background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
	color: white;
	font-weight: bold;
}

.supplier-items-table .liste_titre td {
	padding: 12px 8px;
	text-align: center;
	border-bottom: 2px solid #1565C0;
}

/* Supplier part number badge */
.badge-info {
	background-color: #17a2b8;
	color: white;
	padding: 4px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: bold;
	text-transform: uppercase;
}

/* Default supplier badge */
.badge-success {
	background-color: #28a745;
	color: white;
	padding: 4px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: bold;
}

/* Rating stars */
.rating-stars {
	color: #ffc107;
	font-size: 14px;
}

.rating-stars .fa-star-o {
	color: #ddd;
}

/* Lead time formatting */
.lead-time-badge {
	background-color: #6c757d;
	color: white;
	padding: 2px 6px;
	border-radius: 8px;
	font-size: 11px;
}

/* Add supplier form enhancements */
.add-supplier-form {
	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	border: 1px solid #dee2e6;
	border-radius: 10px;
	padding: 20px;
	margin: 20px 0;
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.add-supplier-form .titlefield {
	background-color: #e3f2fd;
	font-weight: bold;
	padding: 10px;
	border-left: 4px solid #2196F3;
}

/* Form input enhancements */
.supplier-form input[type="text"],
.supplier-form input[type="number"],
.supplier-form select,
.supplier-form textarea {
	border: 2px solid #e0e0e0;
	border-radius: 6px;
	padding: 8px 12px;
	transition: all 0.3s ease;
}

.supplier-form input[type="text"]:focus,
.supplier-form input[type="number"]:focus,
.supplier-form select:focus,
.supplier-form textarea:focus {
	border-color: #2196F3;
	box-shadow: 0 0 8px rgba(33, 150, 243, 0.3);
	outline: none;
}

/* Action buttons styling */
.supplier-actions {
	display: flex;
	gap: 5px;
	justify-content: center;
	align-items: center;
}

.supplier-actions .editfielda,
.supplier-actions .deletefielda {
	padding: 5px 8px;
	border-radius: 4px;
	transition: all 0.2s ease;
}

.supplier-actions .editfielda:hover {
	background-color: #e3f2fd;
}

.supplier-actions .deletefielda:hover {
	background-color: #ffebee;
}

/* Set default button */
.button-small {
	padding: 4px 8px !important;
	font-size: 11px !important;
	border-radius: 4px !important;
	background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%) !important;
	color: white !important;
	border: none !important;
	cursor: pointer !important;
	transition: all 0.2s ease !important;
}

.button-small:hover {
	transform: translateY(-1px) !important;
	box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
}

/* Quality/Delivery rating display */
.rating-display {
	display: inline-flex;
	align-items: center;
	gap: 2px;
}

.rating-display i {
	font-size: 12px;
}

/* Empty state styling */
.no-suppliers-message {
	text-align: center;
	padding: 60px 20px;
	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	border-radius: 10px;
	border: 2px dashed #dee2e6;
	margin: 20px 0;
}

.no-suppliers-message .fa {
	font-size: 48px;
	color: #6c757d;
	margin-bottom: 15px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
	.supplier-items-table {
		font-size: 13px;
	}

	.supplier-actions {
		flex-direction: column;
		gap: 2px;
	}

	.badge-info,
	.badge-success {
		font-size: 10px;
		padding: 2px 4px;
	}

	.rating-display i {
		font-size: 10px;
	}
}

/* Hover effects for table rows */
.supplier-items-table tr.oddeven:hover {
	background-color: #f0f8ff !important;
	transform: translateX(2px);
	transition: all 0.2s ease;
}

/* Form validation errors */
.supplier-form .error-field {
	border-color: #dc3545 !important;
	background-color: #fff5f5 !important;
}

.error-message {
	color: #dc3545;
	font-size: 12px;
	margin-top: 4px;
	font-weight: bold;
}

/* Success message styling */
.success-message {
	background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
	border: 1px solid #28a745;
	color: #155724;
	padding: 12px 16px;
	border-radius: 6px;
	margin: 15px 0;
	font-weight: bold;
}

/* Form section headers */
.form-section-header {
	background: linear-gradient(90deg, #2196F3 0%, #1976D2 100%);
	color: white;
	padding: 10px 15px;
	margin: 20px 0 10px 0;
	border-radius: 6px;
	font-weight: bold;
	text-transform: uppercase;
	letter-spacing: 1px;
}

/* Price display formatting */
.price-display {
	font-weight: bold;
	color: #28a745;
}

.price-display.zero {
	color: #6c757d;
	font-style: italic;
}

/* Status indicators */
.status-active {
	color: #28a745;
	font-weight: bold;
}

.status-inactive {
	color: #dc3545;
	font-weight: bold;
}

/* Loading states */
.loading-overlay {
	position: relative;
}

.loading-overlay::after {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(255, 255, 255, 0.8);
	display: none;
}

.loading-overlay.loading::after {
	display: block;
}

/*
 * إضافة هذا CSS لملف: /css/advancedinventory.css.php
 * في نهاية الملف (بعد CSS الموردين)
 */

/* ========================================
   Advanced Catalog Enhancements
   ======================================== */

/* Statistics boxes */
.catalog-stats-container {
	display: flex;
	gap: 20px;
	margin-bottom: 20px;
}

.stats-box {
	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	border: 1px solid #dee2e6;
	border-radius: 10px;
	padding: 20px;
	text-align: center;
	flex: 1;
	position: relative;
	overflow: hidden;
}

.stats-box::before {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	height: 4px;
	background: linear-gradient(90deg, #2196F3 0%, #1976D2 100%);
}

.stats-box.warning::before {
	background: linear-gradient(90deg, #ff9800 0%, #f57c00 100%);
}

.stats-box.danger::before {
	background: linear-gradient(90deg, #f44336 0%, #d32f2f 100%);
}

.stats-number {
	font-size: 2.5em;
	font-weight: bold;
	color: #2196F3;
	display: block;
	margin-bottom: 10px;
}

.stats-box.warning .stats-number {
	color: #ff9800;
}

.stats-box.danger .stats-number {
	color: #f44336;
}

.stats-label {
	font-size: 14px;
	color: #6c757d;
	text-transform: uppercase;
	letter-spacing: 1px;
}

.stats-percentage {
	font-size: 12px;
	color: #28a745;
	margin-left: 8px;
	font-weight: bold;
}

/* Smart code badges */
.badge-smart-code {
	background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
	color: white;
	padding: 4px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: bold;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	box-shadow: 0 2px 4px rgba(33, 150, 243, 0.3);
}

/* Status badges */
.status-badge-container {
	display: flex;
	gap: 4px;
	justify-content: center;
	flex-wrap: wrap;
}

.badge-status {
	padding: 3px 6px;
	border-radius: 8px;
	font-size: 10px;
	font-weight: bold;
	text-transform: uppercase;
}

.badge-status4 {
	background-color: #28a745;
	color: white;
}

.badge-status8 {
	background-color: #17a2b8;
	color: white;
}

/* Reorder point warnings */
.reorder-warning {
	background-color: #fff3cd !important;
	border-left: 4px solid #ffc107 !important;
	color: #856404 !important;
	font-weight: bold;
}

.reorder-critical {
	background-color: #f8d7da !important;
	border-left: 4px solid #dc3545 !important;
	color: #721c24 !important;
	font-weight: bold;
	animation: pulse-warning 2s infinite;
}

@keyframes pulse-warning {
	0% { opacity: 1; }
	50% { opacity: 0.7; }
	100% { opacity: 1; }
}

/* Inline edit controls */
.inline-edit-container {
	display: inline-flex;
	align-items: center;
	gap: 4px;
}

.inline-edit-input {
	width: 80px;
	padding: 2px 6px;
	border: 1px solid #2196F3;
	border-radius: 4px;
	font-size: 12px;
}

.inline-edit-input:focus {
	outline: none;
	border-color: #1976D2;
	box-shadow: 0 0 4px rgba(33, 150, 243, 0.3);
}

.inline-edit-save,
.inline-edit-cancel {
	padding: 2px 6px;
	border: none;
	border-radius: 3px;
	font-size: 11px;
	cursor: pointer;
	transition: all 0.2s ease;
}

.inline-edit-save {
	background-color: #28a745;
	color: white;
}

.inline-edit-save:hover {
	background-color: #218838;
}

.inline-edit-cancel {
	background-color: #6c757d;
	color: white;
}

.inline-edit-cancel:hover {
	background-color: #5a6268;
}

/* 1. تحديث تنسيقات التحذير والخطر الحرج - أضف هذه التعديلات */
tr.reorder-warning {
	background-color: #fff3cd !important;
	border-left: 4px solid #ffc107 !important;
	animation: pulse-warning 2s infinite;
}

tr.reorder-critical {
	background-color: #f8d7da !important;
	border-left: 4px solid #dc3545 !important;
	animation: blink-warning 1.5s infinite;
}

/* 2. إضافة حركات التنبيه الجديدة */
@keyframes blink-warning {
	0%, 100% { opacity: 1; }
	50% { opacity: 0.5; }
}

/* 3. تحديث تنسيقات النص */
tr.reorder-warning td {
	font-weight: bold !important;
	color: #856404 !important;
}

tr.reorder-critical td {
	font-weight: bold !important;
	color: #721c24 !important;
}

/* 4. تحسين حركة النبض - استبدل التعريف الحالي بهذا */
@keyframes pulse-warning {
	0% {
		background-color: #fff3cd;
		box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
	}
	50% {
		background-color: #ffeeba;
		box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
	}
	100% {
		background-color: #fff3cd;
		box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
	}
}

/* 5. إضافة تأثيرات ظل للخلايا التحذيرية */
tr.reorder-warning td,
tr.reorder-critical td {
	position: relative;
	z-index: 1;
}

tr.reorder-warning td::before,
tr.reorder-critical td::before {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: -1;
	opacity: 0.3;
}

tr.reorder-warning td::before {
	background-color: #ffc107;
}

tr.reorder-critical td::before {
	background-color: #dc3545;
}

/* Action buttons */
.catalog-action-buttons {
	display: flex;
	gap: 4px;
	justify-content: center;
}

.action-btn {
	padding: 4px 8px;
	border: none;
	border-radius: 4px;
	font-size: 11px;
	cursor: pointer;
	transition: all 0.2s ease;
	text-decoration: none;
	display: inline-flex;
	align-items: center;
	gap: 2px;
}

.action-btn-generate {
	background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
	color: white;
}

.action-btn-calculate {
	background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
	color: #212529;
}

.action-btn-edit {
	background: linear-gradient(135deg, #28a745 0%, #218838 100%);
	color: white;
}

.action-btn:hover {
	transform: translateY(-1px);
	box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.action-btn:disabled {
	opacity: 0.6;
	cursor: not-allowed;
	transform: none;
}

/* Search enhancements */
.advanced-search-container {
	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	border: 1px solid #dee2e6;
	border-radius: 8px;
	padding: 15px;
	margin-bottom: 20px;
}

.search-field-enhanced {
	border: 2px solid #e0e0e0;
	border-radius: 6px;
	padding: 6px 10px;
	transition: all 0.3s ease;
	width: 100%;
	max-width: 150px;
}

.search-field-enhanced:focus {
	border-color: #2196F3;
	box-shadow: 0 0 6px rgba(33, 150, 243, 0.3);
	outline: none;
}

.search-results-dropdown {
	position: absolute;
	top: 100%;
	left: 0;
	right: 0;
	background: white;
	border: 1px solid #ddd;
	border-top: none;
	border-radius: 0 0 6px 6px;
	box-shadow: 0 4px 8px rgba(0,0,0,0.1);
	z-index: 1000;
	max-height: 200px;
	overflow-y: auto;
}

.search-result-item {
	padding: 10px 15px;
	border-bottom: 1px solid #f0f0f0;
	cursor: pointer;
	transition: background-color 0.2s ease;
}

.search-result-item:hover {
	background-color: #f8f9fa;
}

.search-result-item:last-child {
	border-bottom: none;
}

/* Supplier count indicator */
.supplier-count-badge {
	background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
	color: white;
	padding: 2px 6px;
	border-radius: 10px;
	font-size: 10px;
	font-weight: bold;
	margin-left: 5px;
}

/* Part number badges */
.part-number-badge {
	background-color: #6c757d;
	color: white;
	padding: 3px 8px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: bold;
	font-family: monospace;
}

/* Stock level indicators */
.stock-level-good {
	color: #28a745;
	font-weight: bold;
}

.stock-level-warning {
	color: #ffc107;
	font-weight: bold;
}

.stock-level-danger {
	color: #dc3545;
	font-weight: bold;
	animation: blink-warning 1.5s infinite;
}

@keyframes blink-warning {
	0%, 100% { opacity: 1; }
	50% { opacity: 0.5; }
}

/* Quick filters */
.quick-filters {
	display: flex;
	gap: 10px;
	margin-bottom: 15px;
	flex-wrap: wrap;
}

.quick-filter-btn {
	padding: 6px 12px;
	border: 2px solid #e0e0e0;
	border-radius: 20px;
	background: white;
	color: #666;
	text-decoration: none;
	font-size: 12px;
	transition: all 0.3s ease;
	cursor: pointer;
}

.quick-filter-btn:hover,
.quick-filter-btn.active {
	border-color: #2196F3;
	background: #2196F3;
	color: white;
	transform: translateY(-1px);
}

.quick-filter-btn .count {
	background: rgba(255,255,255,0.3);
	padding: 2px 6px;
	border-radius: 10px;
	margin-left: 5px;
	font-weight: bold;
}

/* Loading states */
.loading-spinner {
	display: inline-block;
	width: 16px;
	height: 16px;
	border: 2px solid #f3f3f3;
	border-top: 2px solid #2196F3;
	border-radius: 50%;
	animation: spin 1s linear infinite;
	margin-right: 5px;
}

@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}

.loading-overlay {
	position: relative;
}

.loading-overlay::after {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(255, 255, 255, 0.8);
	display: flex;
	align-items: center;
	justify-content: center;
	z-index: 10;
}

.loading-overlay.loading::after {
	content: "⏳";
	font-size: 24px;
}

/* Message notifications */
.message-success,
.message-error,
.message-info {
	position: fixed;
	top: 20px;
	right: 20px;
	padding: 12px 16px;
	border-radius: 6px;
	z-index: 9999;
	max-width: 350px;
	box-shadow: 0 4px 12px rgba(0,0,0,0.15);
	animation: slideInRight 0.3s ease;
}

.message-success {
	background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
	color: #155724;
	border-left: 4px solid #28a745;
}

.message-error {
	background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
	color: #721c24;
	border-left: 4px solid #dc3545;
}

.message-info {
	background: linear-gradient(135deg, #cce7ff 0%, #b3d9ff 100%);
	color: #004085;
	border-left: 4px solid #2196F3;
}

@keyframes slideInRight {
	from {
		transform: translateX(100%);
		opacity: 0;
	}
	to {
		transform: translateX(0);
		opacity: 1;
	}
}

/* Calculation details modal */
.calculation-details {
	background: white;
	padding: 20px;
	border-radius: 8px;
	box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.calculation-details h4 {
	color: #2196F3;
	margin-bottom: 15px;
	border-bottom: 2px solid #e0e0e0;
	padding-bottom: 8px;
}

.calculation-details table {
	width: 100%;
	margin-bottom: 15px;
}

.calculation-details table td {
	padding: 8px;
	border-bottom: 1px solid #f0f0f0;
}

.calculation-details table td:first-child {
	font-weight: normal;
	color: #666;
}

.calculation-details table td:last-child {
	text-align: right;
}

/* Enhanced table styling */
.catalog-table {
	border-collapse: collapse;
	width: 100%;
}

.catalog-table tr.oddeven:nth-child(even) {
	background-color: #f8f9fa;
}

.catalog-table tr.oddeven:hover {
	background-color: #e3f2fd !important;
	transition: background-color 0.2s ease;
}

.catalog-table .liste_titre {
	background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
	color: white;
}

.catalog-table .liste_titre td {
	padding: 12px 8px;
	font-weight: bold;
	text-transform: uppercase;
	font-size: 11px;
	letter-spacing: 0.5px;
}

/* Mobile responsive */
@media (max-width: 768px) {
	.catalog-stats-container {
		flex-direction: column;
		gap: 10px;
	}

	.stats-box {
		padding: 15px;
	}

	.stats-number {
		font-size: 2em;
	}

	.quick-filters {
		flex-direction: column;
		gap: 5px;
	}

	.action-btn {
		padding: 6px 10px;
		font-size: 12px;
	}

	.search-field-enhanced {
		max-width: none;
	}

	.catalog-action-buttons {
		flex-direction: column;
		gap: 2px;
	}
}

/* Print styles */
@media print {
	.catalog-stats-container,
	.quick-filters,
	.catalog-action-buttons,
	.action-btn {
		display: none !important;
	}

	.catalog-table {
		border: 1px solid #000;
	}

	.catalog-table .liste_titre {
		background: #f0f0f0 !important;
		color: #000 !important;
	}

	.badge-smart-code,
	.part-number-badge,
	.supplier-count-badge {
		border: 1px solid #000;
		color: #000 !important;
		background: none !important;
	}
}


