<?php
/**
 * Smart Variants Hook Class
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
     * Constructor
     */
    public function __construct()
    {
        // Constructor logic if needed
    }
    
    /**
     * Hook called when adding object line form
     * 
     * @param array  $parameters Hook parameters
     * @param object $object     Current object
     * @param string $action     Current action
     * @param object $hookmanager Hook manager
     * @return int 0 on success
     */
    public function formAddObjectLine($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $langs, $db;
        
        // Check if we're in the right context
        $contexts = array('ordercard', 'propalcard', 'invoicecard', 'supplierproposalcard');
        $currentContexts = explode(':', $parameters['context']);
        
        if (array_intersect($contexts, $currentContexts)) {
            $this->addSmartVariantSelector();
        }
        
        return 0;
    }
    
    /**
     * Add the smart variant selector interface
     */
    private function addSmartVariantSelector()
    {
        global $conf;
        
        // Include CSS
        echo '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/custom/smartvariants/css/smartvariants.css">';
        
        // Include JavaScript
        echo '<script src="'.DOL_URL_ROOT.'/custom/smartvariants/js/product_selector.js"></script>';
        
        // Add the variant selector HTML
        ?>
        <div id="smart-variant-selector" class="smart-variant-container" style="display:none;">
            <h4 class="variant-title"><?php echo 'Sélection de variante'; ?></h4>
            <div id="variant-attributes" class="variant-attributes">
                <!-- Attributes will be loaded here via AJAX -->
            </div>
            <div class="variant-actions">
                <button type="button" id="add-variant-btn" class="button" onclick="addVariantToLine()">
                    Ajouter à la commande
                </button>
                <button type="button" id="cancel-variant-btn" class="button button-cancel" onclick="cancelVariantSelection()">
                    Annuler
                </button>
            </div>
            <div id="variant-messages" class="variant-messages"></div>
        </div>
        
        <script>
        // Initialize when document is ready
        $(document).ready(function() {
            initSmartVariantSelector();
        });
        </script>
        <?php
    }
    
    /**
     * Hook called after object line creation
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
        return 0;
    }
}
?>