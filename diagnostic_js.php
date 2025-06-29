<?php
/**
 * JavaScript diagnostic for SmartVariants interface
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
echo '<html><head><title>Diagnostic Interface SmartVariants</title>';
echo '<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
.success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
.warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
.code-block { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; font-family: monospace; white-space: pre-wrap; }
.test-section { border: 1px solid #dee2e6; padding: 15px; margin: 15px 0; border-radius: 5px; }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head><body>';

echo '<div class="container">';
echo '<h1>🔍 Diagnostic Interface JavaScript - SmartVariants</h1>';

// Check if SmartVariants is active
if (empty($conf->global->MAIN_MODULE_SMARTVARIANTS)) {
    echo '<div class="error">❌ Module SmartVariants non activé !</div>';
    echo '</div></body></html>';
    exit;
}

echo '<div class="success">✅ Module SmartVariants activé</div>';

// Test CSS file loading
$cssPath = dol_buildpath('/smartvariants/css/smartvariants.css', 0);
$cssExists = file_exists($cssPath);
$cssUrl = dol_buildpath('/smartvariants/css/smartvariants.css', 1);

echo '<div class="test-section">';
echo '<h3>📱 Test des fichiers d\'interface</h3>';

if ($cssExists) {
    echo '<div class="success">✅ Fichier CSS trouvé : ' . $cssPath . '</div>';
    echo '<p><strong>URL CSS :</strong> <a href="' . $cssUrl . '" target="_blank">' . $cssUrl . '</a></p>';
} else {
    echo '<div class="error">❌ Fichier CSS manquant : ' . $cssPath . '</div>';
}

// Test JS file loading
$jsPath = dol_buildpath('/smartvariants/js/product_selector.js', 0);
$jsExists = file_exists($jsPath);
$jsUrl = dol_buildpath('/smartvariants/js/product_selector.js', 1);

if ($jsExists) {
    echo '<div class="success">✅ Fichier JavaScript trouvé : ' . $jsPath . '</div>';
    echo '<p><strong>URL JS :</strong> <a href="' . $jsUrl . '" target="_blank">' . $jsUrl . '</a></p>';
} else {
    echo '<div class="error">❌ Fichier JavaScript manquant : ' . $jsPath . '</div>';
}
echo '</div>';

// Test AJAX endpoints
echo '<div class="test-section">';
echo '<h3>🔗 Test des endpoints AJAX</h3>';

$ajaxPath1 = dol_buildpath('/smartvariants/ajax/get_product_attributes.php', 0);
$ajaxExists1 = file_exists($ajaxPath1);
$ajaxUrl1 = dol_buildpath('/smartvariants/ajax/get_product_attributes.php', 1);

if ($ajaxExists1) {
    echo '<div class="success">✅ Endpoint attributs trouvé : ' . $ajaxPath1 . '</div>';
    echo '<p><strong>URL AJAX :</strong> <a href="' . $ajaxUrl1 . '" target="_blank">' . $ajaxUrl1 . '</a></p>';
} else {
    echo '<div class="error">❌ Endpoint attributs manquant : ' . $ajaxPath1 . '</div>';
}

$ajaxPath2 = dol_buildpath('/smartvariants/ajax/create_or_find_variant.php', 0);
$ajaxExists2 = file_exists($ajaxPath2);

if ($ajaxExists2) {
    echo '<div class="success">✅ Endpoint création variantes trouvé</div>';
} else {
    echo '<div class="error">❌ Endpoint création variantes manquant</div>';
}
echo '</div>';

// JavaScript diagnostic
echo '<div class="test-section">';
echo '<h3>🧪 Tests JavaScript en temps réel</h3>';

echo '<div id="js-test-results"></div>';

echo '<script>';
// Load our CSS and JS files
echo 'document.addEventListener("DOMContentLoaded", function() {';
echo '  var testResults = document.getElementById("js-test-results");';
echo '  var results = [];';

// Test 1: jQuery
echo '  if (typeof jQuery !== "undefined") {';
echo '    results.push("<div class=\"success\">✅ jQuery disponible (version " + jQuery.fn.jquery + ")</div>");';
echo '  } else {';
echo '    results.push("<div class=\"error\">❌ jQuery non disponible</div>");';
echo '  }';

// Test 2: Load our CSS
echo '  var cssLink = document.createElement("link");';
echo '  cssLink.rel = "stylesheet";';
echo '  cssLink.href = "' . $cssUrl . '";';
echo '  cssLink.onload = function() {';
echo '    results.push("<div class=\"success\">✅ CSS SmartVariants chargé avec succès</div>");';
echo '    testResults.innerHTML = results.join("");';
echo '  };';
echo '  cssLink.onerror = function() {';
echo '    results.push("<div class=\"error\">❌ Erreur de chargement CSS SmartVariants</div>");';
echo '    testResults.innerHTML = results.join("");';
echo '  };';
echo '  document.head.appendChild(cssLink);';

// Test 3: Load our JS
echo '  var jsScript = document.createElement("script");';
echo '  jsScript.src = "' . $jsUrl . '";';
echo '  jsScript.onload = function() {';
echo '    results.push("<div class=\"success\">✅ JavaScript SmartVariants chargé</div>");';
echo '    setTimeout(function() {';
echo '      if (typeof initSmartVariantSelector === "function") {';
echo '        results.push("<div class=\"success\">✅ Fonction initSmartVariantSelector disponible</div>");';
echo '      } else {';
echo '        results.push("<div class=\"error\">❌ Fonction initSmartVariantSelector introuvable</div>");';
echo '      }';
echo '      testResults.innerHTML = results.join("");';
echo '    }, 100);';
echo '  };';
echo '  jsScript.onerror = function() {';
echo '    results.push("<div class=\"error\">❌ Erreur de chargement JavaScript SmartVariants</div>");';
echo '    testResults.innerHTML = results.join("");';
echo '  };';
echo '  document.head.appendChild(jsScript);';

echo '});';
echo '</script>';
echo '</div>';

// Configuration test
echo '<div class="test-section">';
echo '<h3>⚙️ Configuration actuelle</h3>';

$debugMode = !empty($conf->global->MAIN_SMARTVARIANTS_DEBUG);
$autoCreate = !empty($conf->global->SMARTVARIANTS_AUTO_CREATE);

if ($debugMode) {
    echo '<div class="success">✅ Mode debug ACTIVÉ</div>';
} else {
    echo '<div class="warning">⚠️ Mode debug DÉSACTIVÉ (recommandé pour les tests)</div>';
}

if ($autoCreate) {
    echo '<div class="success">✅ Création automatique ACTIVÉE</div>';
} else {
    echo '<div class="warning">⚠️ Création automatique DÉSACTIVÉE</div>';
}
echo '</div>';

// Test with a product that has variants
echo '<div class="test-section">';
echo '<h3>🧪 Test AJAX avec un produit à variantes</h3>';

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
    echo '<div class="info">📦 Produit de test : ' . $obj->ref . ' (' . $obj->nb_variants . ' variantes)</div>';
    
    echo '<button onclick="testAjaxCall(' . $obj->rowid . ')" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Tester l\'appel AJAX</button>';
    echo '<div id="ajax-test-result" style="margin-top: 10px;"></div>';
    
    echo '<script>';
    echo 'function testAjaxCall(productId) {';
    echo '  var resultDiv = document.getElementById("ajax-test-result");';
    echo '  resultDiv.innerHTML = "<div class=\"info\">🔄 Test en cours...</div>";';
    echo '  ';
    echo '  fetch("' . $ajaxUrl1 . '", {';
    echo '    method: "POST",';
    echo '    headers: { "Content-Type": "application/x-www-form-urlencoded" },';
    echo '    body: "product_id=" + productId + "&token=' . newToken() . '"';
    echo '  })';
    echo '  .then(response => response.json())';
    echo '  .then(data => {';
    echo '    if (data.success && data.has_variants) {';
    echo '      resultDiv.innerHTML = "<div class=\"success\">✅ AJAX fonctionne ! " + data.attributes.length + " attributs trouvés</div>";';
    echo '    } else {';
    echo '      resultDiv.innerHTML = "<div class=\"warning\">⚠️ AJAX répond mais pas de variantes : " + JSON.stringify(data) + "</div>";';
    echo '    }';
    echo '  })';
    echo '  .catch(error => {';
    echo '    resultDiv.innerHTML = "<div class=\"error\">❌ Erreur AJAX : " + error + "</div>";';
    echo '  });';
    echo '}';
    echo '</script>';
} else {
    echo '<div class="warning">⚠️ Aucun produit avec variantes trouvé dans la base</div>';
}
echo '</div>';

// Instructions for testing
echo '<div class="test-section">';
echo '<h3>📋 Instructions pour tester l\'interface</h3>';
echo '<ol>';
echo '<li><strong>Activez le mode debug</strong> dans la <a href="' . dol_buildpath('/smartvariants/admin/admin.php', 1) . '">configuration SmartVariants</a></li>';
echo '<li><strong>Créez une nouvelle commande client</strong> : Ventes → Commandes → Nouvelle</li>';
echo '<li><strong>Commencez à saisir un produit</strong> avec des variantes (ex: MOLENE)</li>';
echo '<li><strong>Ouvrez la console développeur</strong> (F12) pour voir les erreurs JavaScript</li>';
echo '<li><strong>Vérifiez que l\'interface SmartVariants apparaît</strong> sous le champ produit</li>';
echo '</ol>';
echo '</div>';

echo '<div class="test-section">';
echo '<h3>🐛 Débogage console JavaScript</h3>';
echo '<div class="code-block">// Ouvrez la console (F12) et tapez ces commandes pour déboguer :

// 1. Vérifier que jQuery est chargé
console.log("jQuery:", typeof jQuery, jQuery ? jQuery.fn.jquery : "non trouvé");

// 2. Vérifier que nos scripts sont chargés
console.log("initSmartVariantSelector:", typeof initSmartVariantSelector);

// 3. Vérifier la configuration
console.log("smartVariantsConfig:", window.smartVariantsConfig);

// 4. Forcer l\'initialisation
if (typeof initSmartVariantSelector === "function") {
    initSmartVariantSelector();
    console.log("SmartVariants initialisé manuellement");
}

// 5. Vérifier les sélecteurs de produits
console.log("Champs produits trouvés:", $("input[name=\'idprod\'], input[name=\'product_ref\']").length);</div>';
echo '</div>';

echo '<div class="info">';
echo '<h3>🔧 Actions recommandées si l\'interface ne s\'affiche pas :</h3>';
echo '<ol>';
echo '<li>Vérifiez la console JavaScript (F12) pour les erreurs</li>';
echo '<li>Testez l\'appel AJAX ci-dessus</li>';
echo '<li>Activez le mode debug dans la configuration</li>';
echo '<li>Consultez les logs Dolibarr : Configuration → Journal système</li>';
echo '<li>Vérifiez que vous testez bien avec un produit qui a des variantes</li>';
echo '</ol>';
echo '</div>';

echo '</div></body></html>';
?>