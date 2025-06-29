<?php
/**
 * Test script for SmartVariants installation
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0
 * 
 * Usage: Place this file in /custom/smartvariants/ and access via browser
 */

// Include Dolibarr environment
$res = 0;
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

// Security check
if (!$user->admin) {
    accessforbidden();
}

$langs->load("admin");

// Test results
$tests = array();

/**
 * Add test result
 */
function addTest($name, $success, $message = '', $details = '') {
    global $tests;
    $tests[] = array(
        'name' => $name,
        'success' => $success,
        'message' => $message,
        'details' => $details
    );
}

/**
 * Check file exists
 */
function checkFile($file, $name) {
    $fullPath = dol_buildpath('/smartvariants/' . $file, 0);
    $exists = file_exists($fullPath);
    addTest(
        $name,
        $exists,
        $exists ? "✅ Fichier trouvé" : "❌ Fichier manquant",
        "Chemin: " . $fullPath
    );
    return $exists;
}

/**
 * Check database tables
 */
function checkDatabase() {
    global $db;
    
    $tables = array(
        'product' => 'Table des produits',
        'product_attribute' => 'Table des attributs',
        'product_attribute_value' => 'Table des valeurs d\'attributs',
        'product_attribute_combination' => 'Table des combinaisons',
        'product_attribute_combination_2_val' => 'Table de liaison combinaisons-valeurs'
    );
    
    foreach ($tables as $table => $description) {
        $sql = "SHOW TABLES LIKE '".MAIN_DB_PREFIX.$table."'";
        $result = $db->query($sql);
        $exists = ($result && $db->num_rows($result) > 0);
        
        addTest(
            $description,
            $exists,
            $exists ? "✅ Table existe" : "❌ Table manquante",
            "Table: ".MAIN_DB_PREFIX.$table
        );
    }
}

/**
 * Check module activation
 */
function checkModuleActivation() {
    global $conf;
    
    $activated = !empty($conf->global->MAIN_MODULE_SMARTVARIANTS);
    addTest(
        "Activation du module",
        $activated,
        $activated ? "✅ Module activé" : "❌ Module non activé",
        "Constante: MAIN_MODULE_SMARTVARIANTS"
    );
    return $activated;
}

/**
 * Check permissions
 */
function checkPermissions() {
    global $user;
    
    $hasProductRights = !empty($user->rights->produit->lire);
    addTest(
        "Permissions produits",
        $hasProductRights,
        $hasProductRights ? "✅ Permissions OK" : "❌ Permissions insuffisantes",
        "Droit requis: produit->lire"
    );
    
    return $hasProductRights;
}

/**
 * Test sample product with variants
 */
function testSampleProduct() {
    global $db;
    
    // Find a product with variants
    $sql = "SELECT p.rowid, p.ref, p.label, COUNT(pac.rowid) as nb_variants";
    $sql.= " FROM ".MAIN_DB_PREFIX."product p";
    $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."product_attribute_combination pac";
    $sql.= "   ON pac.fk_product_parent = p.rowid";
    $sql.= " WHERE p.entity IN (".getEntity('product').")";
    $sql.= " GROUP BY p.rowid, p.ref, p.label";
    $sql.= " HAVING nb_variants > 0";
    $sql.= " LIMIT 1";
    
    $result = $db->query($sql);
    
    if ($result && $db->num_rows($result) > 0) {
        $obj = $db->fetch_object($result);
        addTest(
            "Produit avec variantes trouvé",
            true,
            "✅ Produit: " . $obj->ref . " (" . $obj->nb_variants . " variantes)",
            "ID: " . $obj->rowid . " - " . $obj->label
        );
        return $obj->rowid;
    } else {
        addTest(
            "Produit avec variantes",
            false,
            "❌ Aucun produit avec variantes trouvé",
            "Créez un produit avec des variantes pour tester le module"
        );
        return false;
    }
}

/**
 * Test AJAX endpoint
 */
function testAjaxEndpoint($productId) {
    if (!$productId) return false;
    
    $ajaxFile = dol_buildpath('/smartvariants/ajax/get_product_attributes.php', 0);
    $exists = file_exists($ajaxFile);
    
    addTest(
        "Endpoint AJAX",
        $exists,
        $exists ? "✅ Fichier AJAX accessible" : "❌ Fichier AJAX manquant",
        "Fichier: " . $ajaxFile
    );
    
    return $exists;
}

// Run all tests
echo '<!DOCTYPE html>';
echo '<html><head><title>Test SmartVariants</title>';
echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
.test { margin: 10px 0; padding: 10px; border-left: 4px solid #ddd; background: #f9f9f9; }
.test.success { border-left-color: #28a745; }
.test.error { border-left-color: #dc3545; }
.test-name { font-weight: bold; margin-bottom: 5px; }
.test-message { margin-bottom: 5px; }
.test-details { font-size: 0.9em; color: #666; }
.summary { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; }
.header { text-align: center; margin-bottom: 30px; }
</style></head><body>';

echo '<div class="container">';
echo '<div class="header">';
echo '<h1>🧪 Test d\'installation SmartVariants</h1>';
echo '<p>Dolibarr ' . DOL_VERSION . ' - ' . date('Y-m-d H:i:s') . '</p>';
echo '</div>';

// Run tests
echo '<h2>📁 Vérification des fichiers</h2>';
checkFile('core/modules/modSmartVariants.class.php', 'Descripteur du module');
checkFile('core/hooks/smartvariants.class.php', 'Classe de hooks');
checkFile('css/smartvariants.css', 'Fichier CSS');
checkFile('js/product_selector.js', 'Fichier JavaScript');
checkFile('ajax/get_product_attributes.php', 'Endpoint AJAX attributs');
checkFile('ajax/create_or_find_variant.php', 'Endpoint AJAX variantes');
checkFile('admin.php', 'Page d\'administration');

echo '<h2>🗄️ Vérification de la base de données</h2>';
checkDatabase();

echo '<h2>⚙️ Vérification de la configuration</h2>';
$moduleActivated = checkModuleActivation();
checkPermissions();

echo '<h2>🧪 Tests fonctionnels</h2>';
$sampleProductId = testSampleProduct();
testAjaxEndpoint($sampleProductId);

// Summary
$totalTests = count($tests);
$successfulTests = array_filter($tests, function($test) { return $test['success']; });
$successCount = count($successfulTests);
$errorCount = $totalTests - $successCount;

echo '<div class="summary">';
echo '<h2>📊 Résumé</h2>';
echo '<p><strong>Total:</strong> ' . $totalTests . ' tests</p>';
echo '<p><strong>✅ Succès:</strong> ' . $successCount . '</p>';
echo '<p><strong>❌ Erreurs:</strong> ' . $errorCount . '</p>';

if ($errorCount == 0) {
    echo '<p style="color: #28a745; font-weight: bold;">🎉 Tous les tests sont passés ! Le module est correctement installé.</p>';
} else {
    echo '<p style="color: #dc3545; font-weight: bold;">⚠️ Certains tests ont échoué. Consultez les détails ci-dessous.</p>';
}
echo '</div>';

echo '<h2>📋 Détails des tests</h2>';

foreach ($tests as $test) {
    $class = $test['success'] ? 'success' : 'error';
    echo '<div class="test ' . $class . '">';
    echo '<div class="test-name">' . htmlspecialchars($test['name']) . '</div>';
    echo '<div class="test-message">' . htmlspecialchars($test['message']) . '</div>';
    if (!empty($test['details'])) {
        echo '<div class="test-details">' . htmlspecialchars($test['details']) . '</div>';
    }
    echo '</div>';
}

echo '<h2>🔧 Actions recommandées</h2>';

if ($errorCount > 0) {
    echo '<ul>';
    
    if (!$moduleActivated) {
        echo '<li>Activez le module dans Configuration → Modules/Applications</li>';
    }
    
    foreach ($tests as $test) {
        if (!$test['success'] && strpos($test['name'], 'Fichier') !== false) {
            echo '<li>Créez le fichier manquant: ' . htmlspecialchars($test['details']) . '</li>';
        }
    }
    
    if (!$sampleProductId) {
        echo '<li>Créez un produit avec des variantes pour tester la fonctionnalité</li>';
    }
    
    echo '</ul>';
} else {
    echo '<p>✅ Tout semble en ordre ! Vous pouvez maintenant tester le module en créant une commande.</p>';
    echo '<p><a href="' . DOL_URL_ROOT . '/custom/smartvariants/admin.php">→ Aller à la configuration du module</a></p>';
}

echo '<h2>🐛 En cas de problème</h2>';
echo '<ol>';
echo '<li>Activez le mode debug dans la configuration du module</li>';
echo '<li>Consultez les logs Dolibarr (Configuration → Journal système)</li>';
echo '<li>Ouvrez la console développeur (F12) pour voir les erreurs JavaScript</li>';
echo '<li>Vérifiez les permissions de fichiers sur le serveur</li>';
echo '</ol>';

echo '</div></body></html>';
?>