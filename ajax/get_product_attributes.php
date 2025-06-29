<?php
/**
 * Get Product Attributes AJAX Endpoint
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0
 */

// Include Dolibarr environment
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Security check
if (!$user->rights->produit->lire) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'message' => 'Access denied'));
    exit;
}

// Get parameters
$productId = GETPOST('product_id', 'int');
$token = GETPOST('token', 'alpha');

// Initialize response
$response = array(
    'success' => false,
    'has_variants' => false,
    'attributes' => array(),
    'message' => ''
);

// Validate input
if ($productId <= 0) {
    $response['message'] = 'Invalid product ID';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Verify CSRF token (basic implementation)
if (empty($token)) {
    $response['message'] = 'Security token missing';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    // Check if the product has associated variants/attributes
    $sql = "SELECT DISTINCT pa.rowid, pa.ref, pa.label, pa.description";
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
                    'values' => array()
                );
                
                // Get possible values for this attribute
                $sql2 = "SELECT pav.rowid, pav.ref, pav.value";
                $sql2.= " FROM ".MAIN_DB_PREFIX."product_attribute_value pav";
                $sql2.= " WHERE pav.fk_product_attribute = ".(int)$obj->rowid;
                $sql2.= " AND pav.entity IN (".getEntity('product_attribute').")";
                $sql2.= " ORDER BY pav.position ASC, pav.value ASC";
                
                $result2 = $db->query($sql2);
                
                if ($result2) {
                    while ($obj2 = $db->fetch_object($result2)) {
                        $attribute['values'][] = array(
                            'id' => $obj2->rowid,
                            'ref' => $obj2->ref,
                            'value' => $obj2->value
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
            $response['message'] = 'Product has no variants';
        }
    } else {
        $response['message'] = 'Database query failed: ' . $db->lasterror();
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Log for debugging (if debug mode is enabled)
if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
    dol_syslog('SmartVariants get_product_attributes.php: Product ID=' . $productId . ' Response=' . json_encode($response));
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>