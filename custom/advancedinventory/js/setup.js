/**
 * Advanced Inventory Setup JavaScript
 * File: custom/advancedinventory/js/setup.js
 *
 * Organized JavaScript functions for setup page functionality
 */

var AdvancedInventorySetup = {

	/**
	 * Initialize setup page functionality
	 */
	init: function() {
		this.initCategoryManagement();
		this.initProductTypeManagement();
		this.initFormValidation();
		this.initSmartCodePreview();
	},

	/**
	 * Category Management Functions
	 */
	initCategoryManagement: function() {
		var self = this;

		// Auto-generate category code from name
		$("input[name='category_name']").on("input", function() {
			var categoryName = $(this).val();
			var categoryCode = self.generateCategoryCode(categoryName);
			$("input[name='category_code']").val(categoryCode);
		});

		// Category form validation
		$('form[action*="add_category"]').on('submit', function(e) {
			return self.validateCategoryForm(e);
		});
	},

	/**
	 * Product Type Management Functions
	 */
	initProductTypeManagement: function() {
		var self = this;

		// Auto-generate type code from name
		$("input[name='type_name']").on("input", function() {
			var typeName = $(this).val();
			var typeCode = self.generateTypeCode(typeName);
			$("input[name='type_code']").val(typeCode);

			// Auto-suggest prefix
			if (!$("input[name='code_prefix']").val()) {
				var prefix = self.suggestPrefix(typeName);
				$("input[name='code_prefix']").val(prefix);
			}
		});

		// Validate and clean prefix input
		$("input[name='code_prefix']").on("input", function() {
			var prefix = $(this).val();
			// Allow letters, numbers, and some special characters
			prefix = prefix.replace(/[^A-Za-z0-9]/g, "").substring(0, 5);
			$(this).val(prefix);
			self.updateSmartCodePreview();
		});
	},

	/**
	 * Form Validation
	 */
	initFormValidation: function() {
		// Add real-time validation classes
		$('input[required], select[required], textarea[required]').on('blur', function() {
			if ($(this).val().trim() === '') {
				$(this).addClass('field-error').removeClass('field-success');
			} else {
				$(this).addClass('field-success').removeClass('field-error');
			}
		});

		// Remove validation classes on focus
		$('input, select, textarea').on('focus', function() {
			$(this).removeClass('field-error field-success');
		});
	},

	/**
	 * Smart Code Preview
	 */
	initSmartCodePreview: function() {
		this.updateSmartCodePreview();
	},

	/**
	 * Generate category code from Arabic/English name
	 */
	generateCategoryCode: function(name) {
		if (!name) return "";

		var transliterationMap = {
			// Arabic to English
			'ا': 'A', 'ب': 'B', 'ت': 'T', 'ث': 'TH', 'ج': 'J', 'ح': 'H',
			'خ': 'KH', 'د': 'D', 'ذ': 'DH', 'ر': 'R', 'ز': 'Z', 'س': 'S',
			'ش': 'SH', 'ص': 'S', 'ض': 'D', 'ط': 'T', 'ظ': 'Z', 'ع': 'A',
			'غ': 'GH', 'ف': 'F', 'ق': 'Q', 'ك': 'K', 'ل': 'L', 'م': 'M',
			'ن': 'N', 'ه': 'H', 'و': 'W', 'ي': 'Y', 'ئ': 'Y', 'ء': 'A',
			'آ': 'A', 'أ': 'A', 'إ': 'I', 'ة': 'H', 'ى': 'A'
		};

		var code = name
			.replace(/[\u064B-\u065F\u0670\u06D6-\u06ED]/g, '') // Remove diacritics
			.split('')
			.map(char => transliterationMap[char] || char)
			.join('')
			.replace(/\s+/g, '_') // Replace spaces with underscores
			.replace(/[^A-Z0-9_]/g, '') // Remove non-alphanumeric characters
			.toUpperCase()
			.substring(0, 20); // Limit length

		return code;
	},

	/**
	 * Generate type code from Arabic/English name
	 */
	generateTypeCode: function(name) {
		if (!name) return "";

		var commonTranslations = {
			'محلي': 'LOCAL',
			'محلى': 'LOCAL',
			'مستورد': 'IMPORTED',
			'داخلي': 'INTERNAL',
			'خارجي': 'EXTERNAL',
			'Local': 'LOCAL',
			'Imported': 'IMPORTED',
			'Internal': 'INTERNAL',
			'External': 'EXTERNAL'
		};

		// Check for common translations first
		for (var arabic in commonTranslations) {
			if (name.includes(arabic)) {
				return commonTranslations[arabic];
			}
		}

		// Fall back to general transliteration
		return this.generateCategoryCode(name).substring(0, 15);
	},

	/**
	 * Suggest prefix based on type name
	 */
	suggestPrefix: function(typeName) {
		var suggestions = {
			'محلي': '0',
			'محلى': '0',
			'مستورد': '1',
			'داخلي': '0',
			'خارجي': '1',
			'local': '0',
			'imported': '1',
			'internal': '0',
			'external': '1'
		};

		var lowerName = typeName.toLowerCase();
		for (var key in suggestions) {
			if (lowerName.includes(key.toLowerCase())) {
				return suggestions[key];
			}
		}

		// Default: use first 2 characters
		return this.generateTypeCode(typeName).substring(0, 2);
	},

	/**
	 * Validate category form
	 */
	validateCategoryForm: function(e) {
		var categoryName = $("input[name='category_name']").val().trim();
		var categoryCode = $("input[name='category_code']").val().trim();

		if (categoryName === '' || categoryCode === '') {
			e.preventDefault();
			this.showMessage('error', 'اسم الفئة والكود مطلوبان');
			return false;
		}

		// Check if code already exists
		if (this.checkCodeExists(categoryCode, '.badge')) {
			e.preventDefault();
			this.showMessage('error', 'كود الفئة موجود بالفعل');
			return false;
		}

		return true;
	},

	/**
	 * Check if code already exists
	 */
	checkCodeExists: function(code, selector) {
		var exists = false;
		$(selector).each(function() {
			if ($(this).text().trim() === code) {
				exists = true;
				return false; // Break loop
			}
		});
		return exists;
	},

	/**
	 * Update smart code preview
	 */
	updateSmartCodePreview: function() {
		var typePrefix = '';
		var categoryCode = 'XXXX';
		var autoNumber = '001';

		// Get current type prefixes
		$("input[name='code_prefix']").each(function() {
			if ($(this).val()) {
				typePrefix = $(this).val();
				return false; // Break loop
			}
		});

		if (!typePrefix) typePrefix = 'X';

		var preview = '<span class="code-segment">' + typePrefix + '</span>-<span class="code-segment">' + categoryCode + '</span>-<span class="code-segment">' + autoNumber + '</span>';

		if ($('.smart-code-preview').length === 0) {
			$('.info').first().append('<div class="smart-code-preview">مثال: ' + preview + '</div>');
		} else {
			$('.smart-code-preview').html('مثال: ' + preview);
		}
	},

	/**
	 * Show message to user
	 */
	showMessage: function(type, message) {
		var messageClass = type === 'error' ? 'error' : 'success';
		var messageHtml = '<div class="' + messageClass + '">' + message + '</div>';

		// Remove existing messages
		$('.error, .success').remove();

		// Add new message at top of form
		$('form').first().before(messageHtml);

		// Auto-hide after 5 seconds
		setTimeout(function() {
			$('.' + messageClass).fadeOut();
		}, 5000);
	},

	/**
	 * Add loading spinner
	 */
	showLoadingSpinner: function(element) {
		$(element).append('<span class="loading-spinner"></span>');
	},

	/**
	 * Hide loading spinner
	 */
	hideLoadingSpinner: function() {
		$('.loading-spinner').remove();
	}
};

// Initialize when document is ready
$(document).ready(function() {
	AdvancedInventorySetup.init();
});

// Export for external use
window.AdvancedInventorySetup = AdvancedInventorySetup;
