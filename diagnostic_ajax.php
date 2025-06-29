<?php
/**
 * AJAX endpoint diagnostic - Test direct des réponses
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0
 */

// Include Dolibarr environment
$res = 0;
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

$langs->load("admin");

echo '<!DOCTYPE html>';
echo '<html><head><title>Test AJAX Direct - SmartVariants</title>';
echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
.success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
.warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
.code-block { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; font-family: monospace; white-space: pre-wrap; overflow-x: auto; max-height: 400px; }
.test-section { border: 1px solid #dee2e6; padding: 15px; margin: 15px 0; border-radius: 5px; }
</style></head><body>';

echo '<div class="container">';
echo '<h1>🔍 Test AJAX Direct - SmartVariants</h1>';

// Find a product with variants for testing
$sql = "SELECT p.rowid, p.ref, p.label, COUNT(pac.rowid) as nb_variants";
$sql.= " FROM ".MAIN_DB_PREFIX."product p";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_attribute_combination pac";
$sql.= "   ON pac.fk_product_parent = p.rowid";
$sql.= " WHERE p.entity IN (".getEntity('product').")";
$sql.= " GROUP BY p.rowid, p.ref, p.label";
$sql.= " HAVING nb_variants > 0";
$sql.= " LIMIT 1";

$result = $db->query($sql);
$testProductId = null;

if ($result && $db->num_rows($result) > 0) {
    $obj = $db->fetch_object($result);
    $testProductId = $obj->rowid;
    echo '<div class="success">✅ Produit de test trouvé : ' . $obj->ref . ' (ID: ' . $obj->rowid . ', ' . $obj->nb_variants . ' variantes)</div>';
} else {
    echo '<div class="error">❌ Aucun produit avec variantes trouvé</div>';
}

echo '<div class="test-section">';
echo '<h3>🧪 Test 1 : Appel direct à l\'endpoint (PHP cURL)</h3>';

if ($testProductId) {
    $ajaxUrl = 'http://' . $_SERVER['HTTP_HOST'] . dol_buildpath('/smartvariants/ajax/get_product_attributes.php', 1);
    
    // Prepare POST data
    $postData = http_build_query(array(
        'product_id' => $testProductId,
        'token' => newToken()
    ));
    
    // Get cookies for session
    $cookieHeader = '';
    if (!empty($_COOKIE)) {
        $cookies = array();
        foreach ($_COOKIE as $name => $value) {
            $cookies[] = $name . '=' . $value;
        }
        $cookieHeader = implode('; ', $cookies);
    }
    
    // cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ajaxUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIE, $cookieHeader);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo '<div class="error">❌ Erreur cURL : ' . htmlspecialchars($error) . '</div>';
    } else {
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        echo '<div class="info"><strong>Code HTTP :</strong> ' . $httpCode . '</div>';
        echo '<div class="info"><strong>URL testée :</strong> ' . htmlspecialchars($ajaxUrl) . '</div>';
        echo '<div class="info"><strong>Données POST :</strong> ' . htmlspecialchars($postData) . '</div>';
        
        if ($httpCode == 200) {
            echo '<div class="success">✅ Réponse HTTP 200 OK</div>';
        } else {
            echo '<div class="error">❌ Erreur HTTP ' . $httpCode . '</div>';
        }
        
        echo '<h4>Headers de réponse :</h4>';
        echo '<div class="code-block">' . htmlspecialchars($headers) . '</div>';
        
        echo '<h4>Corps de la réponse :</h4>';
        echo '<div class="code-block">' . htmlspecialchars($body) . '</div>';
        
        // Try to decode JSON
        $jsonData = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo '<div class="success">✅ Réponse JSON valide</div>';
            echo '<h4>JSON décodé :</h4>';
            echo '<div class="code-block">' . json_encode($jsonData, JSON_PRETTY_PRINT) . '</div>';
        } else {
            echo '<div class="error">❌ Réponse JSON invalide : ' . json_last_error_msg() . '</div>';
            
            // Check if it's HTML
            if (strpos(trim($body), '<') === 0) {
                echo '<div class="warning">⚠️ La réponse semble être du HTML au lieu de JSON</div>';
            } else if (empty(trim($body))) {
                echo '<div class="warning">⚠️ La réponse est vide</div>';
            }
        }
    }
} else {
    echo '<div class="warning">⚠️ Pas de produit de test disponible</div>';
}

echo '</div>';

echo '<div class="test-section">';
echo '<h3>🧪 Test 2 : Inclusion directe du fichier AJAX</h3>';

echo '<p>Test d\'inclusion directe pour voir les erreurs PHP :</p>';

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Set POST parameters for the included file
    $_POST['product_id'] = $testProductId;
    $_POST['token'] = newToken();
    
    // Capture any output
    include dol_buildpath('/smartvariants/ajax/get_product_attributes.php', 0);
    
} catch (Exception $e) {
    echo 'EXCEPTION: ' . $e->getMessage();
} catch (Error $e) {
    echo 'ERROR: ' . $e->getMessage();
}

$includeOutput = ob_get_clean();

if (!empty($includeOutput)) {
    echo '<h4>Sortie de l\'inclusion directe :</h4>';
    echo '<div class="code-block">' . htmlspecialchars($includeOutput) . '</div>';
    
    // Try to parse as JSON
    $jsonData = json_decode($includeOutput, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo '<div class="success">✅ Inclusion directe : JSON valide</div>';
    } else {
        echo '<div class="error">❌ Inclusion directe : JSON invalide - ' . json_last_error_msg() . '</div>';
    }
} else {
    echo '<div class="warning">⚠️ Inclusion directe : Aucune sortie</div>';
}

echo '</div>';

echo '<div class="test-section">';
echo '<h3>🧪 Test 3 : Vérification des permissions utilisateur</h3>';

echo '<div class="info"><strong>Utilisateur actuel :</strong> ' . $user->login . ' (ID: ' . $user->id . ')</div>';
echo '<div class="info"><strong>Admin :</strong> ' . ($user->admin ? 'Oui' : 'Non') . '</div>';
echo '<div class="info"><strong>Droits produits :</strong> ' . (!empty($user->rights->produit) ? 'Oui' : 'Non') . '</div>';

if (!empty($user->rights->produit)) {
    echo '<div class="info"><strong>Droit lecture :</strong> ' . (!empty($user->rights->produit->lire) ? 'Oui' : 'Non') . '</div>';
    echo '<div class="info"><strong>Droit création :</strong> ' . (!empty($user->rights->produit->creer) ? 'Oui' : 'Non') . '</div>';
}

echo '</div>';

echo '<div class="test-section">';
echo '<h3>🧪 Test 4 : Vérification de l\'environnement</h3>';

echo '<div class="info"><strong>Version PHP :</strong> ' . phpversion() . '</div>';
echo '<div class="info"><strong>Version Dolibarr :</strong> ' . DOL_VERSION . '</div>';
echo '<div class="info"><strong>Chemin custom :</strong> ' . dol_buildpath('/smartvariants/', 0) . '</div>';
echo '<div class="info"><strong>URL custom :</strong> ' . dol_buildpath('/smartvariants/', 1) . '</div>';
echo '<div class="info"><strong>Entité :</strong> ' . $conf->entity . '</div>';

// Check error log
$errorLogFile = ini_get('error_log');
if ($errorLogFile && file_exists($errorLogFile)) {
    echo '<div class="info"><strong>Log d\'erreurs PHP :</strong> ' . $errorLogFile . '</div>';
    
    // Get last few lines
    $errorLines = array_slice(file($errorLogFile), -10);
    if (!empty($errorLines)) {
        echo '<h4>Dernières erreurs PHP :</h4>';
        echo '<div class="code-block">' . htmlspecialchars(implode('', $errorLines)) . '</div>';
    }
}

echo '</div>';

echo '<div class="test-section">';
echo '<h3>🔧 Actions recommandées</h3>';
echo '<ol>';
echo '<li>Vérifiez la <strong>sortie du Test 1</strong> pour voir ce que retourne réellement l\'endpoint</li>';
echo '<li>Si la réponse est <strong>du HTML</strong>, il y a une erreur PHP dans l\'endpoint</li>';
echo '<li>Si la réponse est <strong>vide</strong>, il y a un problème d\'inclusion ou de permissions</li>';
echo '<li>Consultez les <strong>logs d\'erreurs PHP</strong> et Dolibarr</li>';
echo '<li>Vérifiez que l\'utilisateur a les <strong>bonnes permissions</strong></li>';
echo '</ol>';
echo '</div>';

echo '</div></body></html>';
?>