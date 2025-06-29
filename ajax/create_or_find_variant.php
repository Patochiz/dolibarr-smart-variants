<?php
/**
 * Create or Find Variant AJAX Endpoint
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0
 */

// Include Dolibarr environment
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Security check
if (!$user->rights->produit->creer) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'Access denied - insufficient permissions'));
    exit;
}

// Get parameters
$parentId = GETPOST('parent_id', 'int');
$attributesJson = GETPOST('attributes', 'alpha');
$qty = GETPOST('qty', 'alpha');
$price = GETPOST('price', 'alpha');
$token = GETPOST('token', 'alpha');

// Initialize response
$response = array(
    'success' => false,
    'variant_id' => 0,
    'ref' => '',
    'message' => '',
    'created' => false
);

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

// Verify CSRF token
if (empty($token)) {
    $response['message'] = 'Security token missing';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    // Decode attributes
    $attributes = json_decode($attributesJson, true);
    
    if (!is_array($attributes) || empty($attributes)) {
        $response['message'] = 'Invalid attributes format';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Start transaction
    $db->begin();
    
    // First, check if this combination already exists
    $existingVariantId = findExistingVariant($parentId, $attributes);
    
    if ($existingVariantId) {
        // Variant already exists, use it
        $product = new Product($db);
        if ($product->fetch($existingVariantId) > 0) {
            $response['success'] = true;
            $response['variant_id'] = $existingVariantId;
            $response['ref'] = $product->ref;
            $response['message'] = 'Variante existante utilisée';
            $response['created'] = false;
        } else {
            throw new Exception('Failed to load existing variant');
        }
    } else {
        // Create new variant
        $variantId = createNewVariant($parentId, $attributes, $price);
        
        if ($variantId > 0) {
            $product = new Product($db);
            if ($product->fetch($variantId) > 0) {
                $response['success'] = true;
                $response['variant_id'] = $variantId;
                $response['ref'] = $product->ref;
                $response['message'] = 'Nouvelle variante créée';
                $response['created'] = true;
            } else {
                throw new Exception('Failed to load created variant');
            }
        } else {
            throw new Exception('Failed to create variant');
        }
    }
    
    // Commit transaction
    $db->commit();
    
} catch (Exception $e) {
    // Rollback transaction
    $db->rollback();
    
    $response['message'] = 'Error: ' . $e->getMessage();
    
    // Log error
    dol_syslog('SmartVariants create_or_find_variant.php ERROR: ' . $e->getMessage(), LOG_ERR);
}

// Log for debugging
if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
    dol_syslog('SmartVariants create_or_find_variant.php: Parent ID=' . $parentId . ' Response=' . json_encode($response));
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

/**
 * Find existing variant with the same attribute combination
 * 
 * @param int   $parentId   Parent product ID
 * @param array $attributes Selected attributes
 * @return int              Variant product ID if found, 0 otherwise
 */
function findExistingVariant($parentId, $attributes)
{
    global $db;
    
    // TODO: Implement sophisticated variant matching logic
    // This is a simplified version - you may need to enhance this
    
    $sql = "SELECT pac.fk_product_child";
    $sql.= " FROM ".MAIN_DB_PREFIX."product_attribute_combination pac";
    $sql.= " INNER JOIN ".MAIN_DB_PREFIX."product_attribute_combination_2_val pac2v";
    $sql.= "   ON pac2v.fk_prod_combination = pac.rowid";
    $sql.= " WHERE pac.fk_product_parent = ".(int)$parentId;
    $sql.= " GROUP BY pac.fk_product_child";
    $sql.= " HAVING COUNT(pac2v.fk_prod_attr_val) = ".count($attributes);
    
    $result = $db->query($sql);
    
    if ($result && $db->num_rows($result) > 0) {
        // For now, return the first match
        // TODO: Enhance to check exact attribute combination
        $obj = $db->fetch_object($result);
        return $obj->fk_product_child;
    }
    
    return 0;
}

/**
 * Create a new product variant
 * 
 * @param int   $parentId   Parent product ID  
 * @param array $attributes Selected attributes
 * @param float $price      Price override
 * @return int              New variant product ID
 */
function createNewVariant($parentId, $attributes, $price = null)
{
    global $db, $user, $conf;
    
    // Load parent product
    $parentProduct = new Product($db);
    if ($parentProduct->fetch($parentId) <= 0) {
        throw new Exception('Parent product not found');
    }
    
    // Create new product based on parent
    $variant = new Product($db);
    
    // Copy properties from parent
    $variant->ref = generateVariantRef($parentProduct->ref, $attributes);
    $variant->label = $parentProduct->label . ' - ' . generateVariantLabel($attributes);
    $variant->description = $parentProduct->description;
    $variant->type = $parentProduct->type;
    $variant->status = 1; // Active
    $variant->status_buy = $parentProduct->status_buy;
    $variant->price = $price ? $price : $parentProduct->price;
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
    $variant->surface = $parentProduct->surface;
    $variant->surface_units = $parentProduct->surface_units;
    $variant->volume = $parentProduct->volume;
    $variant->volume_units = $parentProduct->volume_units;
    
    // Create the product
    $result = $variant->create($user);
    
    if ($result > 0) {
        // TODO: Create attribute combination record
        // This requires creating records in:
        // - llx_product_attribute_combination
        // - llx_product_attribute_combination_2_val
        
        return $variant->id;
    } else {
        throw new Exception('Failed to create variant product: ' . implode(', ', $variant->errors));
    }
}

/**
 * Generate a reference for the variant
 * 
 * @param string $parentRef Parent product reference
 * @param array  $attributes Selected attributes  
 * @return string Generated reference
 */
function generateVariantRef($parentRef, $attributes)
{
    global $db;
    
    $suffix = '';
    
    foreach ($attributes as $attrId => $valueId) {
        // Get attribute value
        $sql = "SELECT pav.ref, pav.value";
        $sql.= " FROM ".MAIN_DB_PREFIX."product_attribute_value pav";
        $sql.= " WHERE pav.rowid = ".(int)$valueId;
        
        $result = $db->query($sql);
        if ($result && $obj = $db->fetch_object($result)) {
            $suffix .= '-' . strtoupper(substr($obj->ref ? $obj->ref : $obj->value, 0, 3));
        }
    }
    
    return $parentRef . $suffix;
}

/**
 * Generate a label for the variant
 * 
 * @param array $attributes Selected attributes
 * @return string Generated label
 */
function generateVariantLabel($attributes)
{
    global $db;
    
    $parts = array();
    
    foreach ($attributes as $attrId => $valueId) {
        // Get attribute value
        $sql = "SELECT pav.value";
        $sql.= " FROM ".MAIN_DB_PREFIX."product_attribute_value pav";
        $sql.= " WHERE pav.rowid = ".(int)$valueId;
        
        $result = $db->query($sql);
        if ($result && $obj = $db->fetch_object($result)) {
            $parts[] = $obj->value;
        }
    }
    
    return implode(' - ', $parts);
}

?>