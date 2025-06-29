<?php
/**
 * Create or Find Variant AJAX Endpoint - Fixed Version
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0.2
 */

// Define constants for AJAX calls
if (!defined('NOREQUIREUSER')) define('NOREQUIREUSER', '0');
if (!defined('NOREQUIREDB')) define('NOREQUIREDB', '0');
if (!defined('NOREQUIRESOC')) define('NOREQUIRESOC', '1');
if (!defined('NOREQUIRETRAN')) define('NOREQUIRETRAN', '1');
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', '1');
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1');
if (!defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');
if (!defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '0');

// Include Dolibarr environment
$res = 0;
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";
if (!$res) {
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode(array('success' => false, 'message' => 'Cannot load Dolibarr environment')));
}

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductAttribute.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductAttributeValue.class.php';
require_once DOL_DOCUMENT_ROOT.'/variants/class/ProductCombination.class.php';

// Initialize response
$response = array(
    'success' => false,
    'variant_id' => null,
    'ref' => '',
    'message' => ''
);

// Check if user is logged in
if (empty($user) || empty($user->id)) {
    $response['message'] = 'User not authenticated';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Security check - need create rights for variants
if (empty($user->rights->produit->creer) && empty($user->admin)) {
    $response['message'] = 'Insufficient permissions - need product create rights';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Get parameters
$parentId = GETPOST('parent_id', 'int');
$attributesJson = GETPOST('attributes', 'alpha');
$qty = GETPOST('qty', 'int');
$price = GETPOST('price', 'alpha');
$token = GETPOST('token', 'alpha');

// Validate input
if ($parentId <= 0) {
    $response['message'] = 'Invalid parent product ID';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (empty($attributesJson)) {
    $response['message'] = 'No attributes provided';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Parse attributes
$attributes = json_decode($attributesJson, true);
if (!is_array($attributes) || empty($attributes)) {
    $response['message'] = 'Invalid attributes format';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Debug logging
if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
    dol_syslog('SmartVariants create_or_find_variant.php: Parent ID=' . $parentId . ' Attributes=' . $attributesJson . ' User=' . $user->id);
}

try {
    // Load parent product
    $parentProduct = new Product($db);
    $result = $parentProduct->fetch($parentId);
    
    if ($result <= 0) {
        $response['message'] = 'Parent product not found';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Start transaction
    $db->begin();
    
    // Check if a variant with these exact attributes already exists
    $existingVariantId = findExistingVariant($parentId, $attributes);
    
    if ($existingVariantId) {
        // Use existing variant
        $variant = new Product($db);
        $variant->fetch($existingVariantId);
        
        $response['success'] = true;
        $response['variant_id'] = $existingVariantId;
        $response['ref'] = $variant->ref;
        $response['message'] = 'Variante existante utilisée';
        
        if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
            dol_syslog('SmartVariants: Using existing variant ID=' . $existingVariantId);
        }
    } else {
        // Create new variant if auto-creation is enabled
        if (!empty($conf->global->SMARTVARIANTS_AUTO_CREATE)) {
            $variantId = createNewVariant($parentProduct, $attributes);
            
            if ($variantId > 0) {
                $variant = new Product($db);
                $variant->fetch($variantId);
                
                $response['success'] = true;
                $response['variant_id'] = $variantId;
                $response['ref'] = $variant->ref;
                $response['message'] = 'Nouvelle variante créée';
                
                if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
                    dol_syslog('SmartVariants: Created new variant ID=' . $variantId);
                }
            } else {
                throw new Exception('Failed to create new variant');
            }
        } else {
            $response['message'] = 'Variante inexistante. Création automatique désactivée.';
        }
    }
    
    if ($response['success']) {
        $db->commit();
    } else {
        $db->rollback();
    }
    
} catch (Exception $e) {
    $db->rollback();
    $response['message'] = 'Error: ' . $e->getMessage();
    
    if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
        dol_syslog('SmartVariants create_or_find_variant.php ERROR: ' . $e->getMessage(), LOG_ERR);
    }
}

// Log response for debugging
if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
    dol_syslog('SmartVariants create_or_find_variant.php Response: ' . json_encode($response));
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

/**
 * Find existing variant with exact attribute combination
 * 
 * @param int $parentId Parent product ID
 * @param array $attributes Selected attributes
 * @return int|false Variant ID or false if not found
 */
function findExistingVariant($parentId, $attributes)
{
    global $db;
    
    // Build query to find combination with exact attributes
    $sql = "SELECT pac.fk_product_child";
    $sql.= " FROM ".MAIN_DB_PREFIX."product_attribute_combination pac";
    $sql.= " WHERE pac.fk_product_parent = ".(int)$parentId;
    $sql.= " AND pac.entity IN (".getEntity('product').")";
    
    $result = $db->query($sql);
    
    if (!$result) {
        return false;
    }
    
    // Check each combination to find exact match
    while ($obj = $db->fetch_object($result)) {
        $combinationId = getCombinationId($parentId, $obj->fk_product_child);
        
        if ($combinationId && hasExactAttributes($combinationId, $attributes)) {
            return $obj->fk_product_child;
        }
    }
    
    return false;
}

/**
 * Get combination ID for a product child
 * 
 * @param int $parentId Parent product ID
 * @param int $childId Child product ID
 * @return int|false Combination ID or false
 */
function getCombinationId($parentId, $childId)
{
    global $db;
    
    $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."product_attribute_combination";
    $sql.= " WHERE fk_product_parent = ".(int)$parentId;
    $sql.= " AND fk_product_child = ".(int)$childId;
    $sql.= " AND entity IN (".getEntity('product').")";
    
    $result = $db->query($sql);
    
    if ($result && $db->num_rows($result) > 0) {
        $obj = $db->fetch_object($result);
        return $obj->rowid;
    }
    
    return false;
}

/**
 * Check if combination has exact attributes
 * 
 * @param int $combinationId Combination ID
 * @param array $attributes Required attributes
 * @return bool True if exact match
 */
function hasExactAttributes($combinationId, $attributes)
{
    global $db;
    
    // Get all attributes for this combination
    $sql = "SELECT fk_prod_attr, fk_prod_attr_val";
    $sql.= " FROM ".MAIN_DB_PREFIX."product_attribute_combination_2_val";
    $sql.= " WHERE fk_prod_combination = ".(int)$combinationId;
    
    $result = $db->query($sql);
    
    if (!$result) {
        return false;
    }
    
    $combinationAttrs = array();
    while ($obj = $db->fetch_object($result)) {
        $combinationAttrs[$obj->fk_prod_attr] = $obj->fk_prod_attr_val;
    }
    
    // Check if attributes match exactly
    if (count($combinationAttrs) !== count($attributes)) {
        return false;
    }
    
    foreach ($attributes as $attrId => $attrValueId) {
        if (!isset($combinationAttrs[$attrId]) || $combinationAttrs[$attrId] != $attrValueId) {
            return false;
        }
    }
    
    return true;
}

/**
 * Create a new variant product
 * 
 * @param Product $parentProduct Parent product object
 * @param array $attributes Selected attributes
 * @return int|false New variant ID or false on error
 */
function createNewVariant($parentProduct, $attributes)
{
    global $db, $user, $conf;
    
    try {
        // Create new product
        $variant = new Product($db);
        
        // Copy basic properties from parent
        $variant->ref = generateVariantRef($parentProduct, $attributes);
        $variant->label = generateVariantLabel($parentProduct, $attributes);
        $variant->description = $parentProduct->description;
        $variant->type = $parentProduct->type;
        $variant->status = $parentProduct->status;
        $variant->status_buy = $parentProduct->status_buy;
        $variant->price = $parentProduct->price;
        $variant->price_base_type = $parentProduct->price_base_type;
        $variant->tva_tx = $parentProduct->tva_tx;
        $variant->weight = $parentProduct->weight;
        $variant->weight_units = $parentProduct->weight_units;
        $variant->length = $parentProduct->length;
        $variant->length_units = $parentProduct->length_units;
        $variant->width = $parentProduct->width;
        $variant->width_units = $parentProduct->width_units;
        $variant->height = $parentProduct->height;
        $variant->height_units = $parentProduct->height_units;
        
        // Create the product
        $variantId = $variant->create($user);
        
        if ($variantId <= 0) {
            throw new Exception('Failed to create variant product: ' . implode(', ', $variant->errors));
        }
        
        // Create the combination
        $combination = new ProductCombination($db);
        $combination->fk_product_parent = $parentProduct->id;
        $combination->fk_product_child = $variantId;
        $combination->variation_price = 0;
        $combination->variation_price_percentage = 0;
        $combination->variation_weight = 0;
        
        $combinationId = $combination->create($user);
        
        if ($combinationId <= 0) {
            // Delete the variant product if combination creation fails
            $variant->delete($user);
            throw new Exception('Failed to create product combination');
        }
        
        // Link attributes to combination
        foreach ($attributes as $attrId => $attrValueId) {
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."product_attribute_combination_2_val";
            $sql.= " (fk_prod_combination, fk_prod_attr, fk_prod_attr_val)";
            $sql.= " VALUES (".(int)$combinationId.", ".(int)$attrId.", ".(int)$attrValueId.")";
            
            $result = $db->query($sql);
            
            if (!$result) {
                throw new Exception('Failed to link attributes to combination');
            }
        }
        
        return $variantId;
        
    } catch (Exception $e) {
        if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
            dol_syslog('SmartVariants createNewVariant ERROR: ' . $e->getMessage(), LOG_ERR);
        }
        return false;
    }
}

/**
 * Generate reference for new variant
 * 
 * @param Product $parentProduct Parent product
 * @param array $attributes Selected attributes
 * @return string Generated reference
 */
function generateVariantRef($parentProduct, $attributes)
{
    global $db;
    
    $ref = $parentProduct->ref;
    
    // Add attribute values to reference
    foreach ($attributes as $attrId => $attrValueId) {
        $sql = "SELECT pav.ref, pav.value FROM ".MAIN_DB_PREFIX."product_attribute_value pav";
        $sql.= " WHERE pav.rowid = ".(int)$attrValueId;
        
        $result = $db->query($sql);
        
        if ($result && $db->num_rows($result) > 0) {
            $obj = $db->fetch_object($result);
            $valueRef = !empty($obj->ref) ? $obj->ref : substr($obj->value, 0, 3);
            $ref .= '-' . strtoupper($valueRef);
        }
    }
    
    return $ref;
}

/**
 * Generate label for new variant
 * 
 * @param Product $parentProduct Parent product
 * @param array $attributes Selected attributes
 * @return string Generated label
 */
function generateVariantLabel($parentProduct, $attributes)
{
    global $db;
    
    $label = $parentProduct->label;
    $attributeLabels = array();
    
    // Collect attribute values
    foreach ($attributes as $attrId => $attrValueId) {
        $sql = "SELECT pav.value FROM ".MAIN_DB_PREFIX."product_attribute_value pav";
        $sql.= " WHERE pav.rowid = ".(int)$attrValueId;
        
        $result = $db->query($sql);
        
        if ($result && $db->num_rows($result) > 0) {
            $obj = $db->fetch_object($result);
            $attributeLabels[] = $obj->value;
        }
    }
    
    if (!empty($attributeLabels)) {
        $label .= ' (' . implode(', ', $attributeLabels) . ')';
    }
    
    return $label;
}
?>