/**
 * Advanced Inventory Catalog JavaScript Functions
 * Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
 */

class AdvancedInventoryCatalog {
	constructor() {
		this.baseUrl = '/custom/advancedinventory/ajax/catalog_actions.php';
		this.init();
	}

	init() {
		// Bind events
		this.bindEvents();

		// Initialize tooltips if available
		if (typeof jQuery !== 'undefined' && jQuery.fn.tooltip) {
			jQuery('[data-toggle="tooltip"]').tooltip();
		}
	}

	bindEvents() {
		// Inline edit for reorder point
		document.addEventListener('click', (e) => {
			if (e.target.classList.contains('edit-reorder-point')) {
				this.editReorderPoint(e.target);
			}
		});

		// Generate smart code
		document.addEventListener('click', (e) => {
			if (e.target.classList.contains('generate-smart-code')) {
				this.generateSmartCode(e.target);
			}
		});

		// Calculate reorder point
		document.addEventListener('click', (e) => {
			if (e.target.classList.contains('calculate-reorder-point')) {
				this.calculateReorderPoint(e.target);
			}
		});

		// Search by part number
		document.addEventListener('keyup', (e) => {
			if (e.target.classList.contains('search-part-number')) {
				this.debounce(() => {
					this.searchByPartNumber(e.target.value);
				}, 500)();
			}
		});

		// Set default supplier
		document.addEventListener('click', (e) => {
			if (e.target.classList.contains('set-default-supplier')) {
				this.setDefaultSupplier(e.target);
			}
		});
	}

	/**
	 * Edit reorder point inline
	 */
	editReorderPoint(element) {
		const productId = element.dataset.productId;
		const currentValue = element.dataset.currentValue || '0';

		const input = document.createElement('input');
		input.type = 'number';
		input.min = '0';
		input.step = '0.01';
		input.value = currentValue;
		input.className = 'flat';
		input.style.width = '80px';

		const saveBtn = document.createElement('button');
		saveBtn.type = 'button';
		saveBtn.className = 'button button-small';
		saveBtn.textContent = '✓';
		saveBtn.style.marginLeft = '5px';

		const cancelBtn = document.createElement('button');
		cancelBtn.type = 'button';
		cancelBtn.className = 'button button-small';
		cancelBtn.textContent = '✗';
		cancelBtn.style.marginLeft = '2px';

		const container = document.createElement('div');
		container.appendChild(input);
		container.appendChild(saveBtn);
		container.appendChild(cancelBtn);

		const originalContent = element.innerHTML;
		element.innerHTML = '';
		element.appendChild(container);

		input.focus();
		input.select();

		// Save function
		const save = () => {
			const newValue = parseFloat(input.value) || 0;
			this.updateReorderPoint(productId, newValue)
				.then(response => {
					if (response.success) {
						element.innerHTML = newValue > 0 ? newValue : '-';
						element.dataset.currentValue = newValue;
						this.showMessage(response.message, 'success');
					} else {
						throw new Error(response.error || 'Unknown error');
					}
				})
				.catch(error => {
					element.innerHTML = originalContent;
					this.showMessage(error.message, 'error');
				});
		};

		// Cancel function
		const cancel = () => {
			element.innerHTML = originalContent;
		};

		saveBtn.addEventListener('click', save);
		cancelBtn.addEventListener('click', cancel);
		input.addEventListener('keyup', (e) => {
			if (e.key === 'Enter') save();
			if (e.key === 'Escape') cancel();
		});
	}

	/**
	 * Generate smart code for product
	 */
	generateSmartCode(element) {
		const productId = element.dataset.productId;

		// تعطيل الزر أثناء التحميل
		const originalText = element.textContent;
		element.disabled = true;
		element.textContent = 'جاري التوليد...';

		this.makeRequest('generate_smart_code', { product_id: productId })
			.then(response => {
				if (response.success) {
					// 1. تحديث خلية الكود الذكي
					const codeCell = document.getElementById(`smartcode_${productId}`);
					if (codeCell) {
						codeCell.innerHTML = `<span class="badge badge-info">${response.smart_code}</span>`;
					}

					// 2. تحديث نص الزر
					element.textContent = 'تم التوليد ✓';

					// 3. إظهار رسالة نجاح
					this.showMessage(response.message, 'success');
				} else {
					throw new Error(response.error || 'خطأ غير معروف');
				}
			})
			.catch(error => {
				// إرجاع حالة الزر الأصلية
				element.disabled = false;
				element.textContent = originalText;

				// إظهار رسالة الخطأ
				this.showMessage(error.message, 'error');
			});
	}

	/**
	 * Calculate reorder point based on consumption
	 */
	calculateReorderPoint(element) {
		const productId = element.dataset.productId;

		element.disabled = true;
		element.textContent = '...';

		this.makeRequest('calculate_reorder_point', { product_id: productId })
			.then(response => {
				if (response.success) {
					// Update reorder point display
					const reorderElement = document.querySelector(`[data-product-id="${productId}"][data-field="reorder_point"]`);
					if (reorderElement) {
						reorderElement.textContent = response.new_reorder_point;
						reorderElement.dataset.currentValue = response.new_reorder_point;
					}

					// Show calculation details
					this.showCalculationDetails(response.calculation);
					this.showMessage(response.message, 'success');
				} else {
					throw new Error(response.error || 'Unknown error');
				}
			})
			.catch(error => {
				this.showMessage(error.message, 'error');
			})
			.finally(() => {
				element.disabled = false;
				element.textContent = 'Calculate';
			});
	}

	/**
	 * Search products by part number
	 */
	searchByPartNumber(partNumber) {
		if (partNumber.length < 3) {
			this.clearSearchResults();
			return;
		}

		this.makeRequest('search_by_part_number', { part_number: partNumber })
			.then(response => {
				if (response.success) {
					this.displaySearchResults([response.product]);
				} else {
					this.clearSearchResults();
				}
			})
			.catch(error => {
				this.clearSearchResults();
			});
	}

	/**
	 * Set supplier as default
	 */
	setDefaultSupplier(element) {
		const supplierItemId = element.dataset.supplierItemId;
		const productId = element.dataset.productId;

		element.disabled = true;

		this.makeRequest('set_default_supplier', { supplier_item_id: supplierItemId })
			.then(response => {
				if (response.success) {
					// Update UI - remove default from others and set for this one
					document.querySelectorAll(`[data-product-id="${productId}"].set-default-supplier`).forEach(btn => {
						btn.textContent = 'Set Default';
						btn.disabled = false;
					});

					element.textContent = 'Default';
					element.classList.add('button-default');

					this.showMessage(response.message, 'success');
				} else {
					throw new Error(response.error || 'Unknown error');
				}
			})
			.catch(error => {
				this.showMessage(error.message, 'error');
				element.disabled = false;
			});
	}

	/**
	 * Update reorder point via AJAX
	 */
	updateReorderPoint(productId, value) {
		return this.makeRequest('update_reorder_point', {
			product_id: productId,
			value: value
		});
	}

	/**
	 * Show calculation details in modal or popup
	 */
	showCalculationDetails(calculation) {
		const details = `
            <div class="calculation-details">
                <h4>تفاصيل حساب نقطة إعادة الطلب</h4>
                <table class="noborder">
                    <tr><td>متوسط الاستهلاك اليومي:</td><td><strong>${calculation.avg_daily_consumption.toFixed(2)}</strong></td></tr>
                    <tr><td>أقصى استهلاك يومي:</td><td><strong>${calculation.max_daily_consumption.toFixed(2)}</strong></td></tr>
                    <tr><td>مدة التوريد (أيام):</td><td><strong>${calculation.lead_time_days}</strong></td></tr>
                    <tr><td>المخزون الآمن:</td><td><strong>${calculation.safety_stock.toFixed(2)}</strong></td></tr>
                    <tr><td>نقطة إعادة الطلب المقترحة:</td><td><strong>${calculation.suggested_reorder_point}</strong></td></tr>
                </table>
                <p><small>الحساب: (متوسط الاستهلاك × مدة التوريد) + المخزون الآمن</small></p>
            </div>
        `;

		// Show in modal if available, otherwise alert
		if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
			const modal = jQuery('#calculationModal');
			if (modal.length) {
				modal.find('.modal-body').html(details);
				modal.modal('show');
				return;
			}
		}

		// Fallback to alert (you can improve this with a custom modal)
		alert(`نقطة إعادة الطلب المقترحة: ${calculation.suggested_reorder_point}\nبناءً على متوسط استهلاك ${calculation.avg_daily_consumption.toFixed(2)} يومياً`);
	}

	/**
	 * Display search results
	 */
	displaySearchResults(products) {
		const resultsContainer = document.getElementById('part-number-results');
		if (!resultsContainer) return;

		let html = '<div class="search-results">';
		products.forEach(product => {
			html += `
                <div class="search-result-item">
                    <strong>${product.ref}</strong> - ${product.label}<br>
                    <small>مورد: ${product.supplier_name} | رقم القطعة: ${product.part_number}</small>
                </div>
            `;
		});
		html += '</div>';

		resultsContainer.innerHTML = html;
	}

	/**
	 * Clear search results
	 */
	clearSearchResults() {
		const resultsContainer = document.getElementById('part-number-results');
		if (resultsContainer) {
			resultsContainer.innerHTML = '';
		}
	}

	/**
	 * Make AJAX request
	 */
	makeRequest(action, data = {}) {
		const formData = new FormData();
		formData.append('action', action);

		Object.keys(data).forEach(key => {
			formData.append(key, data[key]);
		});

		return fetch(this.baseUrl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
			.then(response => {
				if (!response.ok) {
					throw new Error(`HTTP ${response.status}`);
				}
				return response.json();
			})
			.catch(error => {
				console.error('AJAX Error:', error);
				throw error;
			});
	}

	/**
	 * Show message to user
	 */
	showMessage(message, type = 'info') {
		// Try to use Dolibarr's message system if available
		if (typeof setEventMessages === 'function') {
			if (type === 'success') {
				setEventMessages(message, null, 'mesgs');
			} else {
				setEventMessages(message, null, 'errors');
			}
			return;
		}

		// Fallback to creating our own message
		const messageDiv = document.createElement('div');
		messageDiv.className = `message-${type}`;
		messageDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            border-radius: 4px;
            z-index: 9999;
            max-width: 300px;
            ${type === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'}
        `;
		messageDiv.textContent = message;

		document.body.appendChild(messageDiv);

		setTimeout(() => {
			if (messageDiv.parentNode) {
				messageDiv.parentNode.removeChild(messageDiv);
			}
		}, 5000);
	}

	/**
	 * Debounce function for search
	 */
	debounce(func, wait) {
		let timeout;
		return function executedFunction(...args) {
			const later = () => {
				clearTimeout(timeout);
				func(...args);
			};
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	}

	/**
	 * Refresh catalog statistics
	 */
	refreshStats() {
		this.makeRequest('get_catalog_stats')
			.then(response => {
				if (response.success) {
					this.updateStatsDisplay(response.stats);
				}
			})
			.catch(error => {
				console.error('Error refreshing stats:', error);
			});
	}

	/**
	 * Update statistics display
	 */
	updateStatsDisplay(stats) {
		Object.keys(stats).forEach(key => {
			const element = document.querySelector(`[data-stat="${key}"]`);
			if (element) {
				element.textContent = stats[key];
			}
		});
	}
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
	window.advancedInventoryCatalog = new AdvancedInventoryCatalog();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
	module.exports = AdvancedInventoryCatalog;
}
