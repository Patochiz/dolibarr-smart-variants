<?php
/**
 * Auto-repair script for SmartVariants issues
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

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');

echo '<!DOCTYPE html>';
echo '<html><head><title>Réparation SmartVariants</title>';
echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
.success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
.warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
.button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
.button-success { background: #28a745; }
.button-warning { background: #ffc107; color: #212529; }
.button-danger { background: #dc3545; }
.sql-block { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; font-family: monospace; white-space: pre-wrap; }
</style></head><body>';

echo '<div class="container">';
echo '<h1>🔧 Réparation automatique SmartVariants</h1>';

// Handle actions
if ($action === 'create_table' && $confirm === 'yes') {
    echo '<h2>🛠️ Création de la table manquante</h2>';
    
    $sql = "CREATE TABLE IF NOT EXISTS " . MAIN_DB_PREFIX . "product_attribute_combination_2_val (
        fk_prod_combination int(11) NOT NULL,
        fk_prod_attr int(11) NOT NULL,
        fk_prod_attr_val int(11) NOT NULL,
        KEY fk_prod_combination (fk_prod_combination),
        KEY fk_prod_attr (fk_prod_attr),
        KEY fk_prod_attr_val (fk_prod_attr_val)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    $result = $db->query($sql);
    
    if ($result) {
        echo '<div class="success">✅ Table créée avec succès : ' . MAIN_DB_PREFIX . 'product_attribute_combination_2_val</div>';
        echo '<div class="info">ℹ️ Vous pouvez maintenant retester le module.</div>';
    } else {
        echo '<div class="error">❌ Erreur lors de la création de la table : ' . $db->lasterror() . '</div>';
    }
}

if ($action === 'reset_module' && $confirm === 'yes') {
    echo '<h2>🔄 Réinitialisation du module</h2>';
    
    // Disable module
    dolibarr_del_const($db, 'MAIN_MODULE_SMARTVARIANTS', $conf->entity);
    echo '<div class="info">1. Module désactivé</div>';
    
    // Clear cache
    $sql = "DELETE FROM " . MAIN_DB_PREFIX . "const WHERE name LIKE 'SMARTVARIANTS_%'";
    $db->query($sql);
    echo '<div class="info">2. Cache nettoyé</div>';
    
    // Reactivate module
    dolibarr_set_const($db, 'MAIN_MODULE_SMARTVARIANTS', '1', 'chaine', 0, '', $conf->entity);
    echo '<div class="success">✅ Module réactivé avec succès</div>';
    echo '<div class="info">ℹ️ Testez maintenant la page de configuration.</div>';
}

// Display current status
echo '<h2>📊 État actuel</h2>';

// Check table
$sql = "SHOW TABLES LIKE '" . MAIN_DB_PREFIX . "product_attribute_combination_2_val'";
$result = $db->query($sql);
$tableExists = ($result && $db->num_rows($result) > 0);

if ($tableExists) {
    echo '<div class="success">✅ Table de liaison : EXISTE</div>';
} else {
    echo '<div class="error">❌ Table de liaison : MANQUANTE</div>';
}

// Check module activation
$moduleActive = !empty($conf->global->MAIN_MODULE_SMARTVARIANTS);
if ($moduleActive) {
    echo '<div class="success">✅ Module SmartVariants : ACTIVÉ</div>';
} else {
    echo '<div class="error">❌ Module SmartVariants : DÉSACTIVÉ</div>';
}

// Check variant module
$variantModuleActive = !empty($conf->global->MAIN_MODULE_VARIANTS);
if ($variantModuleActive) {
    echo '<div class="success">✅ Module Variants Dolibarr : ACTIVÉ</div>';
} else {
    echo '<div class="warning">⚠️ Module Variants Dolibarr : DÉSACTIVÉ</div>';
    echo '<div class="info">Le module Variants de Dolibarr doit être activé pour utiliser SmartVariants.</div>';
}

echo '<h2>🛠️ Actions de réparation disponibles</h2>';

// Action 1: Create missing table
if (!$tableExists) {
    echo '<div class="warning">';
    echo '<h3>1. Créer la table manquante</h3>';
    echo '<p>La table de liaison des variantes est manquante. Cliquez ci-dessous pour la créer automatiquement.</p>';
    echo '<div class="sql-block">CREATE TABLE ' . MAIN_DB_PREFIX . 'product_attribute_combination_2_val (...)</div>';
    echo '<a href="?action=create_table&confirm=yes" class="button button-success" onclick="return confirm(\'Êtes-vous sûr de vouloir créer cette table ?\')">Créer la table</a>';
    echo '</div>';
}

// Action 2: Reset module
echo '<div class="info">';
echo '<h3>2. Réinitialiser le module</h3>';
echo '<p>Cette action va désactiver puis réactiver le module pour corriger les problèmes de configuration.</p>';
echo '<a href="?action=reset_module&confirm=yes" class="button button-warning" onclick="return confirm(\'Êtes-vous sûr de vouloir réinitialiser le module ?\')">Réinitialiser le module</a>';
echo '</div>';

// Action 3: Manual instructions
echo '<div class="info">';
echo '<h3>3. Actions manuelles recommandées</h3>';
echo '<ol>';
echo '<li><strong>Mettre à jour les fichiers :</strong><br>';
echo '<code>cd /home/diamanti/www/doli/custom/smartvariants && git pull</code></li>';
echo '<li><strong>Vérifier les permissions :</strong><br>';
echo '<code>chmod -R 644 /home/diamanti/www/doli/custom/smartvariants/</code></li>';
echo '<li><strong>Activer le module Variants :</strong><br>';
echo 'Configuration → Modules/Applications → Chercher "Variants" → Activer</li>';
echo '<li><strong>Tester la configuration :</strong><br>';
echo 'Configuration → SmartVariants → ⚙️ (icône configuration)</li>';
echo '</ol>';
echo '</div>';

echo '<h2>🧪 Tests rapides</h2>';

echo '<div class="info">';
echo '<p><a href="test_installation.php" class="button">🧪 Lancer le test complet</a></p>';
echo '<p><a href="diagnostic_table.php" class="button">🔍 Diagnostic détaillé</a></p>';
if (file_exists('admin.php')) {
    echo '<p><a href="admin.php" class="button">⚙️ Page de configuration</a></p>';
}
echo '</div>';

echo '<h2>📋 Informations système</h2>';
echo '<p><strong>Version Dolibarr :</strong> ' . DOL_VERSION . '</p>';
echo '<p><strong>Préfixe tables :</strong> ' . MAIN_DB_PREFIX . '</p>';
echo '<p><strong>URL custom :</strong> ' . dol_buildpath('/smartvariants/', 1) . '</p>';
echo '<p><strong>Chemin custom :</strong> ' . dol_buildpath('/smartvariants/', 0) . '</p>';

echo '<div class="success">';
echo '<h3>✅ Après la réparation</h3>';
echo '<p>Une fois les problèmes corrigés :</p>';
echo '<ol>';
echo '<li>Allez dans Configuration → Modules/Applications</li>';
echo '<li>Trouvez SmartVariants et cliquez sur l\'icône ⚙️</li>';
echo '<li>Activez le mode debug pour les tests</li>';
echo '<li>Testez avec un produit ayant des variantes</li>';
echo '</ol>';
echo '</div>';

echo '</div></body></html>';
?>