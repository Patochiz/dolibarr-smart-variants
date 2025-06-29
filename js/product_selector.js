/**
 * Smart Variants Product Selector
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0
 */

// Global variables
let selectedProductId = null;
let productAttributes = {};
let isVariantMode = false;

/**
 * Initialize the smart variant selector
 */
function initSmartVariantSelector() {
    console.log('Initializing Smart Variant Selector');
    
    // Watch for product selection changes
    watchProductSelection();
    
    // Setup event listeners
    setupEventListeners();
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
        '.product-selector'
    ];
    
    productSelectors.forEach(function(selector) {
        $(document).on('change', selector, function() {
            const productId = $(this).val();
            if (productId && productId > 0) {
                checkIfProductHasVariants(productId);
            } else {
                hideVariantSelector();
            }
        });
    });
    
    // Also watch for autocomplete selections
    $(document).on('awesomplete-selectcomplete', '.product-autocomplete', function() {
        const productId = $(this).data('product-id');
        if (productId) {
            checkIfProductHasVariants(productId);
        }
    });
}

/**
 * Check if the selected product has variants
 * 
 * @param {int} productId Product ID to check
 */
function checkIfProductHasVariants(productId) {
    console.log('Checking variants for product:', productId);
    
    showLoadingMessage('Vérification des variantes...');
    
    $.ajax({
        url: '/custom/smartvariants/ajax/get_product_attributes.php',
        method: 'POST',
        data: { 
            product_id: productId,
            token: getCSRFToken()
        },
        dataType: 'json',
        success: function(response) {
            hideLoadingMessage();
            
            if (response.success && response.has_variants) {
                selectedProductId = productId;
                productAttributes = response.attributes;
                showVariantSelector(response.attributes);
            } else {
                hideVariantSelector();
                if (!response.success) {
                    showErrorMessage(response.message || 'Erreur lors de la vérification des variantes');
                }
            }
        },
        error: function(xhr, status, error) {
            hideLoadingMessage();
            console.error('AJAX Error:', error);
            showErrorMessage('Erreur de communication avec le serveur');
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
    
    let html = '<div class="variant-selection">';
    
    attributes.forEach(function(attr) {
        html += '<div class="form-group variant-attribute-group">';
        html += '<label class="variant-label" for="variant_attr_' + attr.id + '">' + attr.label + ' :</label>';
        html += '<select name="variant_attr_' + attr.id + '" id="variant_attr_' + attr.id + '" class="variant-attribute flat" required>';
        html += '<option value="">-- Choisir ' + attr.label + ' --</option>';
        
        attr.values.forEach(function(value) {
            html += '<option value="' + value.id + '">' + escapeHtml(value.value) + '</option>';
        });
        
        html += '</select>';
        html += '</div>';
    });
    
    html += '</div>';
    
    $('#variant-attributes').html(html);
    $('#smart-variant-selector').slideDown(300);
    
    // Hide the standard add button
    $('input[name="addline"], .button-add-line').hide();
    
    isVariantMode = true;
}

/**
 * Hide the variant selector
 */
function hideVariantSelector() {
    $('#smart-variant-selector').slideUp(300);
    
    // Show the standard add button
    $('input[name="addline"], .button-add-line').show();
    
    isVariantMode = false;
    selectedProductId = null;
    productAttributes = {};
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
    $.ajax({
        url: '/custom/smartvariants/ajax/create_or_find_variant.php',
        method: 'POST',
        data: {
            parent_id: parentId,
            attributes: JSON.stringify(attributes),
            qty: qty,
            price: price,
            token: getCSRFToken()
        },
        dataType: 'json',
        success: function(response) {
            hideLoadingMessage();
            
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
            console.error('AJAX Error:', error);
            showErrorMessage('Erreur de communication avec le serveur');
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
        // Hide variant selector
        hideVariantSelector();
        
        // Trigger the standard form submission
        $('input[name="addline"]').trigger('click');
        
        // Reset the form
        resetVariantForm();
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
}

// Utility functions

/**
 * Get CSRF token from the page
 */
function getCSRFToken() {
    return $('input[name="token"]').val() || '';
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
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