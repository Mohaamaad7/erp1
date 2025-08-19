<?php
/* Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * Class ActionsAdvancedInventory
 * Hook actions for advanced inventory module
 */
class ActionsAdvancedInventory
{
	/**
	 * @var DoliDB Database handler
	 */
	public $db;

	/**
	 * @var string Error message
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();

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
	 * Add tabs on product card
	 *
	 * @param array $parameters Hook parameters
	 * @param Product $object Product object
	 * @param string $action Current action
	 * @param HookManager $hookmanager Hook manager
	 * @return int 0 if OK, <0 if KO
	 */
	/**
	 * Complete tabs head
	 *
	 * @param array $parameters Hook parameters
	 * @param Product $object Product object
	 * @param string $action Current action
	 * @param HookManager $hookmanager Hook manager
	 * @return int 0 if OK, <0 if KO
	 */
	public function completeTabsHead($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $user;

		if (!isset($parameters['mode']) || $parameters['mode'] != 'product') {
			return 0;
		}

		if (isset($parameters['object']) && is_object($parameters['object']) && $parameters['object']->id > 0) {
			$langs->load('advancedinventory@advancedinventory');

			// الحصول على التبويبات الحالية
			if (!isset($parameters['head'])) {
				return 0;
			}

			$head = $parameters['head'];
			$h = count($head);

			// إضافة تبويب المخزون بالمواقع
			$head[$h][0] = DOL_URL_ROOT.'/custom/advancedinventory/product/stock_by_location.php?id='.$parameters['object']->id;
			$head[$h][1] = $langs->trans("StockByLocation");
			$head[$h][2] = 'advinv_stockbylocation';
			$h++;

			// إضافة تبويب الموردين
			$head[$h][0] = DOL_URL_ROOT.'/custom/advancedinventory/product/suppliers.php?id='.$parameters['object']->id;
			$head[$h][1] = $langs->trans("MultipleSuppliers");
			$head[$h][2] = 'advinv_suppliers';
			$h++;

			// تحديث المصفوفة
			$this->results = array('head' => $head);

			return 1; // نعيد 1 للإشارة أننا عدلنا البيانات
		}

		return 0;
	}

	/**
	 * Execute action before product save
	 *
	 * @param array $parameters Hook parameters
	 * @param Product $object Product object
	 * @param string $action Current action
	 * @param HookManager $hookmanager Hook manager
	 * @return int 0 if OK, <0 if KO
	 */
	public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $user, $db;

		// فقط في صفحة المنتج
		if ($parameters['currentcontext'] == 'productcard' && $action == 'edit') {

			// JavaScript للتحقق من القيم
			?>
			<script type="text/javascript">
				$(document).ready(function() {
					// التحقق عند حفظ النموذج
					$('form[name="formproduct"]').submit(function(e) {
						var min_stock = parseFloat($('input[name="options_advinv_min_stock"]').val()) || 0;
						var max_stock = parseFloat($('input[name="options_advinv_max_stock"]').val()) || 0;
						var reorder_point = parseFloat($('input[name="options_advinv_reorder_point"]').val()) || 0;

						// التحقق من أن الحد الأدنى أقل من الأقصى
						if (min_stock > 0 && max_stock > 0 && min_stock >= max_stock) {
							alert('<?php echo dol_escape_js($langs->trans("MinStockMustBeLessThanMax")); ?>');
							e.preventDefault();
							return false;
						}

						// التحقق من أن نقطة إعادة الطلب منطقية
						if (reorder_point > 0 && min_stock > 0 && reorder_point < min_stock) {
							if (!confirm('<?php echo dol_escape_js($langs->trans("ReorderPointLessThanMinStock")); ?>')) {
								e.preventDefault();
								return false;
							}
						}

						return true;
					});

					// تلوين الحقول عند تغيير القيم
					$('input[name="options_advinv_min_stock"], input[name="options_advinv_max_stock"]').on('change', function() {
						var min = parseFloat($('input[name="options_advinv_min_stock"]').val()) || 0;
						var max = parseFloat($('input[name="options_advinv_max_stock"]').val()) || 0;

						if (min > 0 && max > 0) {
							if (min >= max) {
								$('input[name="options_advinv_min_stock"]').css('background-color', '#ffcccc');
								$('input[name="options_advinv_max_stock"]').css('background-color', '#ffcccc');
							} else {
								$('input[name="options_advinv_min_stock"]').css('background-color', '');
								$('input[name="options_advinv_max_stock"]').css('background-color', '');
							}
						}
					});
				});
			</script>
			<?php
		}

		// عرض تنبيه إذا كان المخزون تحت نقطة إعادة الطلب
		if ($parameters['currentcontext'] == 'productcard' && $object->id > 0) {
			$reorder_point = $object->array_options['options_advinv_reorder_point'] ?? 0;

			if ($reorder_point > 0 && $object->stock_reel <= $reorder_point) {
				?>
				<div class="warning">
					<strong><?php echo $langs->trans("Warning"); ?>:</strong>
					<?php echo $langs->trans("StockBelowReorderPoint", $object->stock_reel, $reorder_point); ?>
				</div>
				<?php
			}
		}

		return 0;
	}

	/**
	 * After stock movement creation
	 * Check reorder point and send alerts
	 *
	 * @param array $parameters Hook parameters
	 * @param MouvementStock $object Stock movement object
	 * @param string $action Current action
	 * @param HookManager $hookmanager Hook manager
	 * @return int 0 if OK, <0 if KO
	 */
	public function afterStockMovementSave($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $user, $db;

		// بعد حركة مخزون، تحقق من نقطة إعادة الطلب
		if ($object->fk_product > 0) {
			require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

			$product = new Product($db);
			if ($product->fetch($object->fk_product) > 0) {

				$reorder_point = $product->array_options['options_advinv_reorder_point'] ?? 0;
				$min_stock = $product->array_options['options_advinv_min_stock'] ?? 0;

				// تحديث المخزون في المواقع
				$this->updateStockLocation($object);

				// التحقق من نقطة إعادة الطلب
				if ($reorder_point > 0 && $product->stock_reel <= $reorder_point) {
					// إرسال تنبيه
					setEventMessages(
						$langs->trans("ProductReachedReorderPoint", $product->ref, $product->stock_reel, $reorder_point),
						null,
						'warnings'
					);

					// يمكن إضافة إرسال إيميل هنا
					// $this->sendReorderAlert($product, $product->stock_reel, $reorder_point);
				}

				// التحقق من الحد الأدنى
				if ($min_stock > 0 && $product->stock_reel < $min_stock) {
					setEventMessages(
						$langs->trans("ProductBelowMinimumStock", $product->ref, $product->stock_reel, $min_stock),
						null,
						'errors'
					);
				}
			}
		}

		return 0;
	}

	/**
	 * Update stock location after movement
	 *
	 * @param MouvementStock $movement Stock movement object
	 * @return int 1 if OK, <0 if KO
	 */
	private function updateStockLocation($movement)
	{
		// سنضيف هذا لاحقاً عندما ننشئ StockLocation class
		return 1;
	}



}


