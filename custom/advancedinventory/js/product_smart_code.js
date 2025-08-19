/**
 * Advanced Inventory Product Smart Code JavaScript
 * File: custom/advancedinventory/js/product_smart_code.js
 *
 * Handles automatic smart code generation in product forms
 */

var ProductSmartCode = {

	/**
	 * Initialize smart code functionality
	 */
	init: function() {
		this.bindEvents();
		this.checkInitialValues();
	},

	/**
	 * Bind events to form elements
	 */
	bindEvents: function() {
		var self = this;

		// Watch for changes in product type
		$(document).on('change', 'select[name="options_advinv_product_type"]', function() {
			self.generateSmartCode();
		});

		// Watch for changes in product category
		$(document).on('change', 'select[name="options_advinv_product_category"]', function() {
			self.generateSmartCode();
		});

		// Prevent manual editing of smart code field
		$(document).on('focus', 'input[name="options_advinv_smart_code"]', function() {
			$(this).blur(); // Remove focus immediately
			self.showMessage('info', 'الكود الذكي يتم توليده تلقائياً عند اختيار النوع والفئة');
		});

		// Prevent keyboard input on smart code field
		$(document).on('keydown', 'input[name="options_advinv_smart_code"]', function(e) {
			e.preventDefault();
			return false;
		});

		// Show loading when form is submitted
		$(document).on('submit', 'form[name="formsoc"]', function() {
			if (self.isSmartCodeFieldEmpty()) {
				self.generateSmartCodeSync();
			}
		});
	},

	/**
	 * Check if both type and category are selected on page load
	 */
	checkInitialValues: function() {
		var typeSelected = this.getSelectedType();
		var categorySelected = this.getSelectedCategory();

		if (typeSelected && categorySelected && this.isSmartCodeFieldEmpty()) {
			this.generateSmartCode();
		}
	},

	/**
	 * Generate smart code via AJAX
	 */
	generateSmartCode: function() {
		var self = this;
		var typeId = this.getSelectedType();
		var categoryId = this.getSelectedCategory();
		var productId = this.getProductId();

		// Check if both values are selected
		if (!typeId || !categoryId) {
			this.clearSmartCode();
			return;
		}

		// Show loading
		this.showLoading(true);

		// AJAX request
		$.ajax({
			url: '../custom/advancedinventory/ajax/generate_smart_code.php',
			type: 'POST',
			data: {
				type_id: typeId,
				category_id: categoryId,
				product_id: productId || 0
			},
			dataType: 'json',
			success: function(response) {
				self.showLoading(false);

				if (response.success) {
					self.setSmartCode(response.smart_code);
					self.showMessage('success', response.message || 'تم توليد الكود الذكي بنجاح');
				} else {
					self.clearSmartCode();
					self.showMessage('error', response.error || 'خطأ في توليد الكود الذكي');
				}
			},
			error: function(xhr, status, error) {
				self.showLoading(false);
				self.clearSmartCode();
				self.showMessage('error', 'خطأ في الاتصال بالخادم: ' + error);
			}
		});
	},

	/**
	 * Generate smart code synchronously (for form submission)
	 */
	generateSmartCodeSync: function() {
		var typeId = this.getSelectedType();
		var categoryId = this.getSelectedCategory();
		var productId = this.getProductId();

		if (!typeId || !categoryId) {
			return;
		}

		// Synchronous AJAX (not recommended but needed for form submission)
		$.ajax({
			url: '../custom/advancedinventory/ajax/generate_smart_code.php',
			type: 'POST',
			async: false,
			data: {
				type_id: typeId,
				category_id: categoryId,
				product_id: productId || 0
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					this.setSmartCode(response.smart_code);
				}
			}.bind(this)
		});
	},

	/**
	 * Get selected product type ID
	 */
	getSelectedType: function() {
		var typeSelect = $('select[name="options_advinv_product_type"]');
		return typeSelect.length ? typeSelect.val() : null;
	},

	/**
	 * Get selected product category ID
	 */
	getSelectedCategory: function() {
		var categorySelect = $('select[name="options_advinv_product_category"]');
		return categorySelect.length ? categorySelect.val() : null;
	},

	/**
	 * Get current product ID (for edit mode)
	 */
	getProductId: function() {
		// Try to get from URL parameter
		var urlParams = new URLSearchParams(window.location.search);
		var productId = urlParams.get('id');

		if (!productId) {
			// Try to get from hidden field
			var hiddenField = $('input[name="id"]');
			productId = hiddenField.length ? hiddenField.val() : null;
		}

		return productId;
	},

	/**
	 * Check if smart code field is empty
	 */
	isSmartCodeFieldEmpty: function() {
		var smartCodeField = $('input[name="options_advinv_smart_code"]');
		return smartCodeField.length ? !smartCodeField.val().trim() : true;
	},

	/**
	 * Set smart code value
	 */
	setSmartCode: function(code) {
		var smartCodeField = $('input[name="options_advinv_smart_code"]');
		if (smartCodeField.length) {
			smartCodeField.val(code);
			// Add visual feedback
			smartCodeField.addClass('smart-code-generated');
			setTimeout(function() {
				smartCodeField.removeClass('smart-code-generated');
			}, 2000);
		}
	},

	/**
	 * Clear smart code value
	 */
	clearSmartCode: function() {
		var smartCodeField = $('input[name="options_advinv_smart_code"]');
		if (smartCodeField.length) {
			smartCodeField.val('');
		}
	},

	/**
	 * Show/hide loading indicator
	 */
	showLoading: function(show) {
		var smartCodeField = $('input[name="options_advinv_smart_code"]');
		if (!smartCodeField.length) return;

		if (show) {
			smartCodeField.val('جاري التوليد...');
			smartCodeField.addClass('loading');
		} else {
			smartCodeField.removeClass('loading');
		}
	},

	/**
	 * Show message to user
	 */
	showMessage: function(type, message) {
		// Remove existing messages
		$('.smart-code-message').remove();

		var messageClass = 'smart-code-message alert ';
		messageClass += type === 'error' ? 'alert-danger' :
			type === 'success' ? 'alert-success' : 'alert-info';

		var messageHtml = '<div class="' + messageClass + '">' + message + '</div>';

		// Find smart code field and add message after it
		var smartCodeField = $('input[name="options_advinv_smart_code"]');
		if (smartCodeField.length) {
			smartCodeField.closest('tr').after('<tr><td colspan="2">' + messageHtml + '</td></tr>');
		} else {
			// Fallback: add to top of form
			$('form[name="formsoc"]').prepend(messageHtml);
		}

		// Auto-hide after 5 seconds
		setTimeout(function() {
			$('.smart-code-message').fadeOut();
		}, 5000);
	},

	/**
	 * Add visual styling for smart code field
	 */
	addStyling: function() {
		// Add CSS for smart code field
		var css = `
        <style>
        input[name="options_advinv_smart_code"] {
            background-color: #f0f0f0 !important;
            cursor: not-allowed !important;
            color: #666 !important;
        }
        
        input[name="options_advinv_smart_code"].loading {
            background-color: #fff3cd !important;
            color: #856404 !important;
        }
        
        input[name="options_advinv_smart_code"].smart-code-generated {
            background-color: #d4edda !important;
            color: #155724 !important;
            transition: background-color 0.3s ease;
        }
        
        .smart-code-message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        </style>
        `;

		$('head').append(css);
	}
};

// Initialize when document is ready
$(document).ready(function() {
	// Check if we're on a product page
	if ($('input[name="options_advinv_smart_code"]').length > 0) {
		ProductSmartCode.init();
		ProductSmartCode.addStyling();
	}
});

// Export for external use
window.ProductSmartCode = ProductSmartCode;
