<?php
/**
 * Diagnostic script for missing table
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

echo '<!DOCTYPE html>';
echo '<html><head><title>Diagnostic Table Manquante</title>';
echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.warning { color: #ffc107; font-weight: bold; }
.info { color: #17a2b8; font-weight: bold; }
.sql-block { background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0; font-family: monospace; }
</style></head><body>';

echo '<div class="container">';
echo '<h1>🔍 Diagnostic Table Manquante - SmartVariants</h1>';

// Check all variant-related tables
$variantTables = array(
    'product_attribute' => 'Table des attributs de produits',
    'product_attribute_value' => 'Table des valeurs d\'attributs',
    'product_attribute_combination' => 'Table des combinaisons de variantes',
    'product_attribute_combination_2_val' => 'Table de liaison (problématique)',
    'product_attribute_combination2val' => 'Table de liaison (nom alternatif)',
    'product_variant' => 'Table variantes (ancien nom)',
    'product_variant_attribute' => 'Table attributs variantes (ancien nom)'
);

echo '<h2>📋 Vérification des tables de variantes</h2>';

foreach ($variantTables as $table => $description) {
    $fullTableName = MAIN_DB_PREFIX . $table;
    $sql = "SHOW TABLES LIKE '" . $fullTableName . "'";
    $result = $db->query($sql);
    $exists = ($result && $db->num_rows($result) > 0);
    
    $class = $exists ? 'success' : 'error';
    $status = $exists ? '✅ EXISTE' : '❌ MANQUANTE';
    
    echo '<p class="' . $class . '">' . $status . ' - ' . $description . ' (' . $fullTableName . ')</p>';
    
    if ($exists && $table === 'product_attribute_combination') {
        // Check if there are records
        $sql2 = "SELECT COUNT(*) as nb FROM " . $fullTableName;
        $result2 = $db->query($sql2);
        if ($result2) {
            $obj = $db->fetch_object($result2);
            echo '<p class="info">   📊 Contient ' . $obj->nb . ' enregistrements</p>';
        }
    }
}

// Check module variants activation
echo '<h2>🔧 Vérification modules Dolibarr</h2>';

$moduleChecks = array(
    'MAIN_MODULE_VARIANTS' => 'Module Variants (nouveau)',
    'MAIN_MODULE_PRODUCTATTRIBUTE' => 'Module Product Attributes',
    'MAIN_MODULE_PRODUCT' => 'Module Products'
);

foreach ($moduleChecks as $constant => $description) {
    $activated = !empty($conf->global->$constant);
    $class = $activated ? 'success' : 'warning';
    $status = $activated ? '✅ ACTIVÉ' : '⚠️ DÉSACTIVÉ';
    
    echo '<p class="' . $class . '">' . $status . ' - ' . $description . ' (' . $constant . ')</p>';
}

// Try to find the correct table name
echo '<h2>🔍 Recherche de la table de liaison</h2>';

$possibleNames = array(
    MAIN_DB_PREFIX . 'product_attribute_combination_2_val',
    MAIN_DB_PREFIX . 'product_attribute_combination2val',
    MAIN_DB_PREFIX . 'product_attribute_combinaison_2_val',
    MAIN_DB_PREFIX . 'productattribute_combination_2_val',
    MAIN_DB_PREFIX . 'product_variant_value',
    MAIN_DB_PREFIX . 'product_variant_attribute'
);

$foundTable = null;
foreach ($possibleNames as $tableName) {
    $sql = "SHOW TABLES LIKE '" . $tableName . "'";
    $result = $db->query($sql);
    if ($result && $db->num_rows($result) > 0) {
        $foundTable = $tableName;
        echo '<p class="success">✅ Table trouvée: ' . $tableName . '</p>';
        
        // Check structure
        $sql2 = "DESCRIBE " . $tableName;
        $result2 = $db->query($sql2);
        if ($result2) {
            echo '<p class="info">Structure de la table:</p>';
            echo '<div class="sql-block">';
            while ($obj = $db->fetch_object($result2)) {
                echo $obj->Field . ' (' . $obj->Type . ')<br>';
            }
            echo '</div>';
        }
        break;
    }
}

if (!$foundTable) {
    echo '<p class="error">❌ Aucune table de liaison trouvée</p>';
    
    echo '<h2>🛠️ Solution recommandée</h2>';
    echo '<p>La table de liaison des variantes est manquante. Voici comment la créer :</p>';
    
    echo '<h3>Option 1: Via interface Dolibarr</h3>';
    echo '<ol>';
    echo '<li>Allez dans <strong>Configuration → Modules/Applications</strong></li>';
    echo '<li>Cherchez "Variants" ou "Variantes"</li>';
    echo '<li>Activez le module si ce n\'est pas fait</li>';
    echo '<li>Si le module est déjà activé, désactivez-le puis réactivez-le</li>';
    echo '</ol>';
    
    echo '<h3>Option 2: Création manuelle de la table</h3>';
    echo '<p>Si l\'option 1 ne fonctionne pas, exécutez cette requête SQL :</p>';
    echo '<div class="sql-block">';
    echo 'CREATE TABLE ' . MAIN_DB_PREFIX . 'product_attribute_combination_2_val (<br>';
    echo '&nbsp;&nbsp;fk_prod_combination int(11) NOT NULL,<br>';
    echo '&nbsp;&nbsp;fk_prod_attr int(11) NOT NULL,<br>';
    echo '&nbsp;&nbsp;fk_prod_attr_val int(11) NOT NULL,<br>';
    echo '&nbsp;&nbsp;KEY fk_prod_combination (fk_prod_combination),<br>';
    echo '&nbsp;&nbsp;KEY fk_prod_attr (fk_prod_attr),<br>';
    echo '&nbsp;&nbsp;KEY fk_prod_attr_val (fk_prod_attr_val)<br>';
    echo ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    echo '</div>';
}

// Check Dolibarr version
echo '<h2>ℹ️ Informations système</h2>';
echo '<p><strong>Version Dolibarr:</strong> ' . DOL_VERSION . '</p>';
echo '<p><strong>Base de données:</strong> ' . $db->type . '</p>';
echo '<p><strong>Préfixe tables:</strong> ' . MAIN_DB_PREFIX . '</p>';

// Check if we can create test table
echo '<h2>🧪 Test de permissions</h2>';
$testTableName = MAIN_DB_PREFIX . 'smartvariants_test';

try {
    $sql = "CREATE TABLE IF NOT EXISTS " . $testTableName . " (id int(11) AUTO_INCREMENT PRIMARY KEY, test varchar(50))";
    $result = $db->query($sql);
    
    if ($result) {
        echo '<p class="success">✅ Permissions de création de tables: OK</p>';
        
        // Clean up
        $sql = "DROP TABLE " . $testTableName;
        $db->query($sql);
    } else {
        echo '<p class="error">❌ Permissions insuffisantes pour créer des tables</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">❌ Erreur lors du test: ' . $e->getMessage() . '</p>';
}

echo '<h2>🔧 Actions recommandées</h2>';
echo '<ol>';
if (!$foundTable) {
    echo '<li class="error">Créez la table manquante en suivant les instructions ci-dessus</li>';
}
echo '<li>Mettez à jour le module depuis GitHub (git pull)</li>';
echo '<li>Désactivez le module SmartVariants</li>';
echo '<li>Réactivez le module SmartVariants</li>';
echo '<li>Testez la page de configuration</li>';
echo '</ol>';

echo '</div></body></html>';
?>