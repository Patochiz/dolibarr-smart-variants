<?php
/**
 * Smart Variants Hook Class - Corrected Version
 * 
 * @package SmartVariants
 * @author  Claude AI
 * @version 1.0
 */

class ActionsSmartVariants
{
    /**
     * @var string Module name
     */
    public $name = 'smartvariants';
    
    /**
     * @var array Error messages
     */
    public $errors = array();
    
    /**
     * @var DoliDB Database handler
     */
    private $db;
    
    /**
     * Constructor
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Hook called when displaying form to add object line
     * 
     * @param array  $parameters Hook parameters
     * @param object $object     Current object
     * @param string $action     Current action
     * @param object $hookmanager Hook manager
     * @return int 0 on success
     */
    public function formAddObjectLine($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $langs, $user;
        
        // Check if user has permission to use smart variants
        if (!$user->rights->produit->lire) {
            return 0;
        }
        
        // Check if we're in the right context
        $contexts = array('ordercard', 'propalcard', 'invoicecard', 'supplierproposalcard');
        $currentContexts = explode(':', $parameters['context']);
        
        if (array_intersect($contexts, $currentContexts)) {
            $this->addSmartVariantSelector();
        }
        
        return 0;
    }
    
    /**
     * Hook called on form object options
     * 
     * @param array  $parameters Hook parameters
     * @param object $object     Current object
     * @param string $action     Current action
     * @param object $hookmanager Hook manager
     * @return int 0 on success
     */
    public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $langs, $user;
        
        $contexts = array('ordercard', 'propalcard', 'invoicecard', 'supplierproposalcard');
        $currentContexts = explode(':', $parameters['context']);
        
        if (array_intersect($contexts, $currentContexts)) {
            // Add CSS and JS early in the page
            $this->addAssets();
        }
        
        return 0;
    }
    
    /**
     * Add CSS and JavaScript assets
     */
    private function addAssets()
    {
        global $conf;
        
        static $assets_added = false;
        
        if (!$assets_added) {
            // Add CSS
            echo '<link rel="stylesheet" type="text/css" href="'.dol_buildpath('/smartvariants/css/smartvariants.css', 1).'">';
            
            // Add JavaScript
            echo '<script src="'.dol_buildpath('/smartvariants/js/product_selector.js', 1).'"></script>';
            
            $assets_added = true;
        }
    }
    
    /**
     * Add the smart variant selector interface
     */
    private function addSmartVariantSelector()
    {
        global $conf, $langs;
        
        // Ensure assets are loaded
        $this->addAssets();
        
        // Add the variant selector HTML
        ?>
        <script>
        // Set the correct AJAX URL for this installation
        window.smartVariantsConfig = {
            ajaxUrl: '<?php echo dol_buildpath('/smartvariants/ajax/', 1); ?>',
            token: '<?php echo newToken(); ?>'
        };
        </script>
        
        <div id="smart-variant-selector" class="smart-variant-container" style="display:none;">
            <h4 class="variant-title"><?php echo $langs->trans('VariantSelection'); ?></h4>
            <div id="variant-attributes" class="variant-attributes">
                <!-- Attributes will be loaded here via AJAX -->
            </div>
            <div class="variant-actions">
                <button type="button" id="add-variant-btn" class="button" onclick="addVariantToLine()">
                    <?php echo $langs->trans('AddToOrder'); ?>
                </button>
                <button type="button" id="cancel-variant-btn" class="button button-cancel" onclick="cancelVariantSelection()">
                    <?php echo $langs->trans('Cancel'); ?>
                </button>
            </div>
            <div id="variant-messages" class="variant-messages"></div>
        </div>
        
        <script>
        // Initialize when document is ready
        $(document).ready(function() {
            if (typeof initSmartVariantSelector === 'function') {
                initSmartVariantSelector();
            } else {
                console.warn('SmartVariants: initSmartVariantSelector function not found');
            }
        });
        </script>
        <?php
    }
    
    /**
     * Hook called when printing object line
     * 
     * @param array  $parameters Hook parameters
     * @param object $object     Current object
     * @param string $action     Current action
     * @param object $hookmanager Hook manager
     * @return int 0 on success
     */
    public function printObjectLine($parameters, &$object, &$action, $hookmanager)
    {
        // This can be used to modify how existing lines are displayed
        return 0;
    }
    
    /**
     * Hook called after creating object line
     * 
     * @param array  $parameters Hook parameters
     * @param object $object     Current object
     * @param string $action     Current action
     * @param object $hookmanager Hook manager
     * @return int 0 on success
     */
    public function afterObjectLine($parameters, &$object, &$action, $hookmanager)
    {
        // Post-processing logic if needed
        if (!empty($conf->global->MAIN_SMARTVARIANTS_DEBUG)) {
            dol_syslog('SmartVariants afterObjectLine: Object=' . get_class($object) . ' Action=' . $action);
        }
        
        return 0;
    }
    
    /**
     * Get product variants for debugging
     * 
     * @param int $productId Product ID
     * @return array Array of variants
     */
    public function getProductVariants($productId)
    {
        $variants = array();
        
        if ($productId > 0) {
            $sql = "SELECT p.rowid, p.ref, p.label";
            $sql.= " FROM ".MAIN_DB_PREFIX."product p";
            $sql.= " INNER JOIN ".MAIN_DB_PREFIX."product_attribute_combination pac";
            $sql.= "   ON pac.fk_product_child = p.rowid";
            $sql.= " WHERE pac.fk_product_parent = ".(int)$productId;
            $sql.= " AND p.entity IN (".getEntity('product').")";
            $sql.= " ORDER BY p.ref ASC";
            
            $result = $this->db->query($sql);
            
            if ($result) {
                while ($obj = $this->db->fetch_object($result)) {
                    $variants[] = array(
                        'id' => $obj->rowid,
                        'ref' => $obj->ref,
                        'label' => $obj->label
                    );
                }
            }
        }
        
        return $variants;
    }
}
?>