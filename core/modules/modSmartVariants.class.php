<?php
/**
 * Module descriptor class for SmartVariants
 * 
 * @package SmartVariants
 * @author  Claude AI  
 * @version 1.0.2
 */

dol_include_once('/core/modules/DolibarrModules.class.php');

class modSmartVariants extends DolibarrModules
{
    /**
     * Constructor
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;

        // Module identification
        $this->numero = 500100; // Unique module number
        $this->rights_class = 'smartvariants';
        
        // Module properties
        $this->family = "products";
        $this->module_position = 90;
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Smart Variants - Gestion intelligente des variantes de produits";
        $this->descriptionlong = "Module pour simplifier la sélection et création de variantes de produits dans Dolibarr. Affiche uniquement les attributs du produit sélectionné au lieu de tous les attributs globaux.";
        
        // Version info
        $this->version = '1.0.2';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'product';
        
        // Module author
        $this->editor_name = 'Claude AI';
        $this->editor_url = '';
        
        // Dependencies
        $this->depends = array('modProduct');
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->langfiles = array("smartvariants@smartvariants");
        
        // Constants
        $this->const = array();
        
        // Module boxes
        $this->boxes = array();
        
        // Module permissions
        $this->rights = array();
        $r = 0;
        
        $this->rights[$r][0] = $this->numero + $r;
        $this->rights[$r][1] = 'Utiliser Smart Variants';
        $this->rights[$r][4] = 'smartvariants';
        $this->rights[$r][5] = 'use';
        $r++;
        
        // Main menu entries
        $this->menu = array();
        
        // Module configuration page - Structure attendue par Dolibarr
        $this->config_page_url = array("admin/admin.php@smartvariants");
        
        // Module parts
        $this->module_parts = array(
            'hooks' => array(
                'ordercard',
                'propalcard', 
                'invoicecard',
                'supplierproposalcard'
            ),
            'css' => array(
                '/smartvariants/css/smartvariants.css'
            ),
            'js' => array(
                '/smartvariants/js/product_selector.js'
            )
        );
        
        // Dictionaries
        $this->dictionaries = array();
        
        // Sql file list to execute on module activation
        $this->dirs = array("/smartvariants/temp");
    }

    /**
     * Function called when module is enabled
     */
    public function init($options = '')
    {
        global $conf, $langs, $user;
        
        $result = $this->_load_tables('/smartvariants/sql/');
        if ($result < 0) return -1;
        
        return $this->_init(array(), $options);
    }

    /**
     * Function called when module is disabled
     */
    public function remove($options = '')
    {
        $result = $this->_remove(array(), $options);
        return $result;
    }
}
?>