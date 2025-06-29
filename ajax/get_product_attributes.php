<?php
/**
 * Get Product Attributes AJAX Endpoint - Path Fixed Version
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0.3
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

// Include Dolibarr environment - Multiple path attempts
$res = 0;
$attempted_paths = array();

// Try different possible paths
$possible_paths = array(
    "../../../main.inc.php",           // From /custom/smartvariants/ajax/ to /
    "../../../../main.inc.php",        // Alternative
    "../../../doli/main.inc.php",     // If in subdirectory
    "../../../../doli/main.inc.php",   // Alternative subdirectory
    "/home/diamanti/www/doli/main.inc.php", // Absolute path based on your config
);

foreach ($possible_paths as $path) {
    $attempted_paths[] = $path;
    if (file_exists($path)) {
        $res = @include $path;
        if ($res) {
            break;
        }
    }
}

// If still not loaded, try to find it dynamically
if (!$res) {
    // Try to find main.inc.php by going up the directory tree
    $current_dir = __DIR__;
    for ($i = 0; $i < 6; $i++) {
        $current_dir = dirname($current_dir);
        $main_file = $current_dir . '/main.inc.php';
        $attempted_paths[] = $main_file;
        
        if (file_exists($main_file)) {
            $res = @include $main_file;
            if ($res) {
                break;
            }
        }
    }
}

if (!$res) {
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode(array(
        'success' => false, 
        'message' => 'Cannot load Dolibarr environment',
        'debug' => array(
            'attempted_paths' => $attempted_paths,
            'current_dir' => __DIR__,
            'script_name' => $_SERVER['SCRIPT_NAME']
        )
    )));
}

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Initialize response
$response = array(
    'success' => false,
    'has_variants' => false,
    'attributes' => array(),
    'message' => ''
);

// Check if user is logged in
if (empty($user) || empty($user->id)) {
    $response['message'] = 'User not authenticated';
    $response['debug'] = array(
        'user_exists' => !empty($user),
        'user_id' => !empty($user) ? $user->id : 'N/A'
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Security check - more flexible permission check
if (empty($user->rights->produit->lire) && empty($user->admin)) {
    $response['message'] = 'Insufficient permissions - need product read rights';
    $response['debug'] = array(
        'admin' => !empty($user->admin),
        'produit_rights' => !empty($user->rights->produit),
        'read_rights' => !empty($user->rights->produit->lire)
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Get parameters
$productId = GETPOST('product_id', 'int');
$token = GETPOST('token', 'alpha');

// Validate input
if ($productId <= 0) {
    $response['message'] = 'Invalid product ID: ' . $productId;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Debug logging
if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
    dol_syslog('SmartVariants get_product_attributes.php: Product ID=' . $productId . ' User=' . $user->id);
}

try {
    // First check if the product exists and is a parent product
    $product = new Product($db);
    $result = $product->fetch($productId);
    
    if ($result <= 0) {
        $response['message'] = 'Product not found (ID: ' . $productId . ')';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Check if this product has variants (children)
    $sql = "SELECT COUNT(*) as nb_variants";
    $sql.= " FROM ".MAIN_DB_PREFIX."product_attribute_combination pac";
    $sql.= " WHERE pac.fk_product_parent = ".(int)$productId;
    $sql.= " AND pac.entity IN (".getEntity('product').")";
    
    $result = $db->query($sql);
    $hasVariants = false;
    
    if ($result) {
        $obj = $db->fetch_object($result);
        $hasVariants = ($obj->nb_variants > 0);
    }
    
    if (!$hasVariants) {
        $response['success'] = true;
        $response['message'] = 'Product has no variants';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Get the attributes used by this product's variants
    $sql = "SELECT DISTINCT pa.rowid, pa.ref, pa.label, pa.description, pa.position";
    $sql.= " FROM ".MAIN_DB_PREFIX."product_attribute pa";
    $sql.= " INNER JOIN ".MAIN_DB_PREFIX."product_attribute_combination_2_val pac2v";
    $sql.= "   ON pac2v.fk_prod_attr = pa.rowid";
    $sql.= " INNER JOIN ".MAIN_DB_PREFIX."product_attribute_combination pac";
    $sql.= "   ON pac.rowid = pac2v.fk_prod_combination";
    $sql.= " WHERE pac.fk_product_parent = ".(int)$productId;
    $sql.= " AND pa.entity IN (".getEntity('product_attribute').")";
    $sql.= " ORDER BY pa.position ASC, pa.label ASC";
    
    $result = $db->query($sql);
    
    if ($result) {
        $numRows = $db->num_rows($result);
        
        if ($numRows > 0) {
            $response['has_variants'] = true;
            
            while ($obj = $db->fetch_object($result)) {
                $attribute = array(
                    'id' => $obj->rowid,
                    'ref' => $obj->ref,
                    'label' => $obj->label,
                    'description' => $obj->description,
                    'position' => $obj->position,
                    'values' => array()
                );
                
                // Get possible values for this attribute that are actually used by this product
                $sql2 = "SELECT DISTINCT pav.rowid, pav.ref, pav.value, pav.position";
                $sql2.= " FROM ".MAIN_DB_PREFIX."product_attribute_value pav";
                $sql2.= " INNER JOIN ".MAIN_DB_PREFIX."product_attribute_combination_2_val pac2v";
                $sql2.= "   ON pac2v.fk_prod_attr_val = pav.rowid";
                $sql2.= " INNER JOIN ".MAIN_DB_PREFIX."product_attribute_combination pac";
                $sql2.= "   ON pac.rowid = pac2v.fk_prod_combination";
                $sql2.= " WHERE pac.fk_product_parent = ".(int)$productId;
                $sql2.= "   AND pav.fk_product_attribute = ".(int)$obj->rowid;
                $sql2.= "   AND pav.entity IN (".getEntity('product_attribute').")";
                $sql2.= " ORDER BY pav.position ASC, pav.value ASC";
                
                $result2 = $db->query($sql2);
                
                if ($result2) {
                    while ($obj2 = $db->fetch_object($result2)) {
                        $attribute['values'][] = array(
                            'id' => $obj2->rowid,
                            'ref' => $obj2->ref,
                            'value' => $obj2->value,
                            'position' => $obj2->position
                        );
                    }
                }
                
                // Only add attribute if it has values
                if (!empty($attribute['values'])) {
                    $response['attributes'][] = $attribute;
                }
            }
            
            // Update response status
            if (!empty($response['attributes'])) {
                $response['success'] = true;
                $response['message'] = 'Attributes retrieved successfully';
            } else {
                $response['has_variants'] = false;
                $response['message'] = 'No valid attributes found';
            }
        } else {
            $response['success'] = true;
            $response['message'] = 'No attributes found for this product';
        }
    } else {
        $response['message'] = 'Database query failed: ' . $db->lasterror();
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    
    if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
        dol_syslog('SmartVariants get_product_attributes.php ERROR: ' . $e->getMessage(), LOG_ERR);
    }
}

// Log response for debugging
if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
    dol_syslog('SmartVariants get_product_attributes.php Response: ' . json_encode($response));
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>