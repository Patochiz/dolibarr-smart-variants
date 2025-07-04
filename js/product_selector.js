/**
 * Smart Variants Product Selector - Corrected Version
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0
 */

// Global variables
let selectedProductId = null;
let productAttributes = {};
let isVariantMode = false;
let smartVariantsConfig = window.smartVariantsConfig || {};

/**
 * Initialize the smart variant selector
 */
function initSmartVariantSelector() {
    console.log('Initializing Smart Variant Selector v1.0');
    
    // Verify configuration
    if (!smartVariantsConfig.ajaxUrl) {
        console.error('SmartVariants: AJAX URL not configured');
        return;
    }
    
    // Watch for product selection changes
    watchProductSelection();
    
    // Setup event listeners
    setupEventListeners();
    
    console.log('Smart Variant Selector initialized successfully');
}

/**
 * Watch for product selection in the form
 */
function watchProductSelection() {
    // Watch the product ID field (varies by document type)
    const productSelectors = [
        'input[name="idprod"]',
        'select[name="idprod"]',
        'input[name="product_ref"]',
        '.product-selector',
        'input[name="search_idprod"]'
    ];
    
    productSelectors.forEach(function(selector) {
        $(document).on('change', selector, function() {
            const productId = $(this).val();
            console.log('Product selection changed:', selector, productId);
            
            if (productId && productId > 0) {
                checkIfProductHasVariants(productId);
            } else {
                hideVariantSelector();
            }
        });
    });
    
    // Watch for autocomplete selections (Dolibarr 20.x)
    $(document).on('awesomplete-selectcomplete', '.ui-autocomplete-input', function() {
        const productId = $(this).attr('data-product-id') || extractProductIdFromValue($(this).val());
        console.log('Autocomplete selection:', productId);
        
        if (productId) {
            setTimeout(function() {
                checkIfProductHasVariants(productId);
            }, 100);
        }
    });
    
    // Watch for jQuery UI autocomplete
    $(document).on('autocompleteselect', '.ui-autocomplete-input', function(event, ui) {
        if (ui.item && ui.item.id) {
            console.log('jQuery UI autocomplete selection:', ui.item.id);
            checkIfProductHasVariants(ui.item.id);
        }
    });
    
    // Watch for input changes with delay
    let inputTimeout = null;
    $(document).on('input', 'input[name="product_ref"]', function() {
        const self = this;
        clearTimeout(inputTimeout);
        
        inputTimeout = setTimeout(function() {
            const productId = extractProductIdFromValue($(self).val());
            if (productId) {
                checkIfProductHasVariants(productId);
            } else if ($(self).val() === '') {
                hideVariantSelector();
            }
        }, 500);
    });
}

/**
 * Extract product ID from autocomplete value
 */
function extractProductIdFromValue(value) {
    if (!value) return null;
    
    // Try to extract ID from patterns like "REF (ID)" or "REF - LABEL (ID)"
    const matches = value.match(/\((\d+)\)$/);
    if (matches && matches[1]) {
        return parseInt(matches[1]);
    }
    
    return null;
}

/**
 * Check if the selected product has variants
 * 
 * @param {int} productId Product ID to check
 */
function checkIfProductHasVariants(productId) {
    console.log('Checking variants for product:', productId);
    
    showLoadingMessage('Vérification des variantes...');
    
    const ajaxUrl = smartVariantsConfig.ajaxUrl + 'get_product_attributes.php';
    
    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: { 
            product_id: productId,
            token: smartVariantsConfig.token || getCSRFToken()
        },
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            hideLoadingMessage();
            console.log('Variants check response:', response);
            
            if (response.success && response.has_variants) {
                selectedProductId = productId;
                productAttributes = response.attributes;
                showVariantSelector(response.attributes);
            } else {
                hideVariantSelector();
                if (!response.success && response.message) {
                    console.warn('SmartVariants:', response.message);
                }
            }
        },
        error: function(xhr, status, error) {
            hideLoadingMessage();
            console.error('SmartVariants AJAX Error:', {
                url: ajaxUrl,
                status: status,
                error: error,
                response: xhr.responseText
            });
            
            if (xhr.status === 404) {
                showErrorMessage('Module SmartVariants non trouvé. Vérifiez l\'installation.');
            } else if (xhr.status === 403) {
                showErrorMessage('Accès refusé. Vérifiez vos permissions.');
            } else {
                showErrorMessage('Erreur de communication avec le serveur');
            }
            
            hideVariantSelector();
        }
    });
}

/**
 * Show the variant selector with attributes
 * 
 * @param {array} attributes Array of product attributes
 */
function showVariantSelector(attributes) {
    console.log('Showing variant selector for attributes:', attributes);
    
    if (!attributes || attributes.length === 0) {
        console.warn('No attributes to display');
        return;
    }
    
    let html = '<div class="variant-selection">';
    
    attributes.forEach(function(attr) {
        html += '<div class="form-group variant-attribute-group">';
        html += '<label class="variant-label" for="variant_attr_' + attr.id + '">' + escapeHtml(attr.label) + ' :</label>';
        html += '<select name="variant_attr_' + attr.id + '" id="variant_attr_' + attr.id + '" class="variant-attribute flat" required>';
        html += '<option value="">-- Choisir ' + escapeHtml(attr.label) + ' --</option>';
        
        if (attr.values && attr.values.length > 0) {
            attr.values.forEach(function(value) {
                html += '<option value="' + value.id + '">' + escapeHtml(value.value) + '</option>';
            });
        }
        
        html += '</select>';
        html += '</div>';
    });
    
    html += '</div>';
    
    $('#variant-attributes').html(html);
    $('#smart-variant-selector').slideDown(300);
    
    // Hide the standard add button
    hideStandardAddButtons();
    
    isVariantMode = true;
    $('body').addClass('variant-mode-active');
}

/**
 * Hide standard add buttons
 */
function hideStandardAddButtons() {
    const buttonSelectors = [
        'input[name="addline"]',
        '.button-add-line',
        'input[value="Ajouter"]',
        'button[name="addline"]',
        '.buttongen[onclick*="addline"]'
    ];
    
    buttonSelectors.forEach(function(selector) {
        $(selector).hide();
    });
}

/**
 * Show standard add buttons
 */
function showStandardAddButtons() {
    const buttonSelectors = [
        'input[name="addline"]',
        '.button-add-line',
        'input[value="Ajouter"]',
        'button[name="addline"]',
        '.buttongen[onclick*="addline"]'
    ];
    
    buttonSelectors.forEach(function(selector) {
        $(selector).show();
    });
}

/**
 * Hide the variant selector
 */
function hideVariantSelector() {
    $('#smart-variant-selector').slideUp(300);
    
    // Show the standard add button
    showStandardAddButtons();
    
    isVariantMode = false;
    selectedProductId = null;
    productAttributes = {};
    $('body').removeClass('variant-mode-active');
}

/**
 * Add the selected variant to the line
 */
function addVariantToLine() {
    console.log('Adding variant to line');
    
    // Collect selected attributes
    const selectedAttributes = {};
    let allSelected = true;
    
    $('.variant-attribute').each(function() {
        const attrId = $(this).attr('name').replace('variant_attr_', '');
        const attrValue = $(this).val();
        
        if (attrValue) {
            selectedAttributes[attrId] = attrValue;
        } else {
            allSelected = false;
        }
    });
    
    // Validate that all attributes are selected
    if (!allSelected) {
        showErrorMessage('Veuillez sélectionner tous les attributs requis');
        return;
    }
    
    // Get other form values
    const qty = $('input[name="qty"]').val() || 1;
    const price = $('input[name="price_ht"]').val() || '';
    
    showLoadingMessage('Traitement de la variante...');
    
    // Find or create the variant
    findOrCreateVariant(selectedProductId, selectedAttributes, qty, price);
}

/**
 * Find or create a variant
 * 
 * @param {int} parentId Parent product ID
 * @param {object} attributes Selected attributes
 * @param {float} qty Quantity
 * @param {float} price Price
 */
function findOrCreateVariant(parentId, attributes, qty, price) {
    const ajaxUrl = smartVariantsConfig.ajaxUrl + 'create_or_find_variant.php';
    
    $.ajax({
        url: ajaxUrl,
        method: 'POST',
        data: {
            parent_id: parentId,
            attributes: JSON.stringify(attributes),
            qty: qty,
            price: price,
            token: smartVariantsConfig.token || getCSRFToken()
        },
        dataType: 'json',
        timeout: 15000,
        success: function(response) {
            hideLoadingMessage();
            console.log('Create/find variant response:', response);
            
            if (response.success) {
                // Add the line with the variant product
                addLineToDocument(response.variant_id, response.ref, qty, price);
                showSuccessMessage(response.message || 'Variante ajoutée avec succès');
            } else {
                showErrorMessage(response.message || 'Erreur lors du traitement de la variante');
            }
        },
        error: function(xhr, status, error) {
            hideLoadingMessage();
            console.error('Create variant AJAX Error:', error);
            showErrorMessage('Erreur lors de la création/recherche de la variante');
        }
    });
}

/**
 * Add line to the document with the variant product
 * 
 * @param {int} productId Variant product ID
 * @param {string} productRef Variant product reference
 * @param {float} qty Quantity
 * @param {float} price Price
 */
function addLineToDocument(productId, productRef, qty, price) {
    console.log('Adding line to document:', productId, productRef);
    
    // Set the form values
    $('input[name="idprod"]').val(productId);
    $('input[name="product_ref"]').val(productRef);
    if (qty) $('input[name="qty"]').val(qty);
    if (price) $('input[name="price_ht"]').val(price);
    
    // Trigger the standard add line process
    setTimeout(function() {
        // Hide variant selector first
        hideVariantSelector();
        
        // Find and click the add button
        const addButtons = [
            'input[name="addline"]',
            'button[name="addline"]',
            '.button-add-line'
        ];
        
        let buttonClicked = false;
        for (let selector of addButtons) {
            const button = $(selector).first();
            if (button.length > 0) {
                button.trigger('click');
                buttonClicked = true;
                break;
            }
        }
        
        if (!buttonClicked) {
            console.warn('No add button found to trigger');
            showErrorMessage('Impossible de trouver le bouton d\'ajout standard');
        }
        
        // Reset the form
        setTimeout(resetVariantForm, 1000);
    }, 500);
}

/**
 * Cancel variant selection
 */
function cancelVariantSelection() {
    hideVariantSelector();
    resetVariantForm();
}

/**
 * Reset the variant form
 */
function resetVariantForm() {
    $('#variant-attributes').empty();
    $('#variant-messages').empty();
    selectedProductId = null;
    productAttributes = {};
    isVariantMode = false;
    $('body').removeClass('variant-mode-active');
}

/**
 * Setup additional event listeners
 */
function setupEventListeners() {
    // Close variant selector when product field is cleared
    $(document).on('input', 'input[name="product_ref"]', function() {
        if ($(this).val() === '' && isVariantMode) {
            hideVariantSelector();
        }
    });
    
    // Handle form resets
    $(document).on('reset', 'form', function() {
        if (isVariantMode) {
            hideVariantSelector();
        }
    });
}

// Utility functions

/**
 * Get CSRF token from the page
 */
function getCSRFToken() {
    return $('input[name="token"]').val() || $('meta[name="csrf-token"]').attr('content') || '';
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Show loading message
 */
function showLoadingMessage(message) {
    $('#variant-messages').html('<div class="variant-loading">' + escapeHtml(message) + '</div>');
}

/**
 * Hide loading message
 */
function hideLoadingMessage() {
    $('.variant-loading').remove();
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    $('#variant-messages').html('<div class="variant-error">' + escapeHtml(message) + '</div>');
    setTimeout(function() {
        $('.variant-error').fadeOut();
    }, 5000);
}

/**
 * Show success message
 */
function showSuccessMessage(message) {
    $('#variant-messages').html('<div class="variant-success">' + escapeHtml(message) + '</div>');
    setTimeout(function() {
        $('.variant-success').fadeOut();
    }, 3000);
}