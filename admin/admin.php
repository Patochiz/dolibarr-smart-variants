<?php
/**
 * Smart Variants Admin Configuration Page
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0.1
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res && file_exists("../../../../main.inc.php")) $res = @include "../../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

// Translations
$langs->loadLangs(array("admin", "products", "smartvariants@smartvariants"));

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$type = 'smartvariants';

$error = 0;
$setupnotempty = 0;

// Actions
if ($action == 'updateMask') {
    $maskconstorder = GETPOST('maskconstorder', 'alpha');
    $maskorder = GETPOST('maskorder', 'alpha');
    
    if ($maskconstorder) {
        $res = dolibarr_set_const($db, $maskconstorder, $maskorder, 'chaine', 0, '', $conf->entity);
    }
    
    if (!($res > 0)) $error++;
    
    if (!$error) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

if ($action == 'set') {
    $constname = GETPOST('constname', 'alpha');
    $constvalue = GETPOST('constvalue', 'alpha');
    
    $res = dolibarr_set_const($db, $constname, $constvalue, 'chaine', 0, '', $conf->entity);
    if (!($res > 0)) $error++;
    
    if (!$error) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

if ($action == 'del') {
    $constname = GETPOST('constname', 'alpha');
    
    $res = dolibarr_del_const($db, $constname, $conf->entity);
    if (!($res > 0)) $error++;
    
    if (!$error) {
        setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    } else {
        setEventMessages($langs->trans("Error"), null, 'errors');
    }
}

/*
 * View
 */

$form = new Form($db);

llxHeader('', $langs->trans("SmartVariantsSetup"));

$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("SmartVariantsSetup"), $linkback, 'title_setup');

$head = array();
$h = 0;

$head[$h][0] = DOL_URL_ROOT."/custom/smartvariants/admin/admin.php";
$head[$h][1] = $langs->trans("Settings");
$head[$h][2] = 'settings';
$h++;

dol_fiche_head($head, 'settings', '', -1);

print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="set">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print "</tr>\n";

// Debug mode
print '<tr class="oddeven">';
print '<td>'.$langs->trans("DebugMode").'</td>';
print '<td>';
print ajax_constantonoff('MAIN_SMARTVARIANTS_DEBUG');
print '</td>';
print '<td>'.$langs->trans("EnableDebugMode").'</td>';
print '</tr>';

// Auto-create variants
print '<tr class="oddeven">';
print '<td>'.$langs->trans("AutoCreateVariants").'</td>';
print '<td>';
print ajax_constantonoff('SMARTVARIANTS_AUTO_CREATE');
print '</td>';
print '<td>'.$langs->trans("AutomaticallyCreateNonExistentVariants").'</td>';
print '</tr>';

// Show variant reference
print '<tr class="oddeven">';
print '<td>'.$langs->trans("ShowVariantReference").'</td>';
print '<td>';
print ajax_constantonoff('SMARTVARIANTS_SHOW_REFERENCE');
print '</td>';
print '<td>'.$langs->trans("ShowVariantReferenceInSelectors").'</td>';
print '</tr>';

// Cache duration
print '<tr class="oddeven">';
print '<td>'.$langs->trans("CacheDuration").'</td>';
print '<td>';
print '<input type="hidden" name="constname" value="SMARTVARIANTS_CACHE_DURATION">';
print '<input type="text" name="constvalue" value="'.getDolGlobalString('SMARTVARIANTS_CACHE_DURATION', '3600').'" size="10">';
print '</td>';
print '<td>'.$langs->trans("AttributesCacheDurationInSeconds").'</td>';
print '</tr>';

print '</table>';

dol_fiche_end();

print '<div class="center">';
print '<input type="submit" class="button button-edit" value="'.$langs->trans("Modify").'">';
print '</div>';

print '</form>';

print '<br>';

// Information section
print '<div class="info">';
print '<h3>'.$langs->trans("Information").'</h3>';
print '<p>'.$langs->trans("SmartVariantsDescription").'</p>';
print '<ul>';
print '<li>'.$langs->trans("OnlyDisplaysAttributesOfSelectedProduct").'</li>';
print '<li>'.$langs->trans("SimplifiesVariantSelection").'</li>';
print '<li>'.$langs->trans("AutomaticallyCreatesVariantsIfNeeded").'</li>';
print '<li>'.$langs->trans("IntegratesSeamlesslyWithDolibarr").'</li>';
print '</ul>';
print '</div>';

// Usage statistics (if debug mode is enabled)
if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
    print '<br>';
    print '<div class="info">';
    print '<h3>'.$langs->trans("DebugInformation").'</h3>';
    print '<p><strong>'.$langs->trans("ModulePath").':</strong> '.dol_buildpath('/smartvariants/', 0).'</p>';
    print '<p><strong>'.$langs->trans("ModuleURL").':</strong> '.dol_buildpath('/smartvariants/', 1).'</p>';
    print '<p><strong>'.$langs->trans("DolibarrVersion").':</strong> '.DOL_VERSION.'</p>';
    print '<p><strong>'.$langs->trans("PHPVersion").':</strong> '.phpversion().'</p>';
    print '</div>';
}

// Quick access buttons
print '<br>';
print '<div class="center">';
print '<a href="'.dol_buildpath('/smartvariants/test_installation.php', 1).'" class="button">🧪 Test d\'installation</a> ';
print '<a href="'.dol_buildpath('/smartvariants/diagnostic_table.php', 1).'" class="button">🔍 Diagnostic</a> ';
print '<a href="'.dol_buildpath('/smartvariants/repair.php', 1).'" class="button">🔧 Réparation</a>';
print '</div>';

llxFooter();
$db->close();
?>