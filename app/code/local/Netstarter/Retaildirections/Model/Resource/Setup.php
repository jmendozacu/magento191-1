<?php
/**
 * Class Netstarter_Retaildirections_Model_Resource_Setup
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Helps in multiwebsite/multistore handling - and the creation of flat attributes.
 *
 */
class Netstarter_Retaildirections_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * This helps create attributes for the Netstarter_Retaildirections_Model_Product model.
     *
     * These attributes are created just in case a new website/store scope is added.
     *
     * In case of a new website setup, please reffer to the mysql4-upgrade-0.0.3-0.0.4.php sql
     * update file in order for this function usage. It creates scoped attributes in case of new
     * website/store.
     *
     * @param null $website_code Magento backend website code for the new store.
     */
    public function addProductWebsiteScopeColumns ($website_code = null)
    {
        if (!is_string($website_code)) return;

        // What are the multi-scope attributes?
        $productDataModel = Mage::getModel('netstarter_retaildirections/model_product');
        $productDataModel->doNotMail(true);
        $productAttributes = $productDataModel->getProductAttributesSpecific();

        // What is the flat table?
        $tableName = Mage::getResourceModel('netstarter_retaildirections/product')->getMainTable();

        foreach($productAttributes as $attributes)
        {
            // convention is websitecode_attribute
            // please refer to the "product" database entity on config.xml ($tableName variable)
            $name = $website_code.'_'.$attributes;
            $comments = $name;

            // Create the scope columns for each attribute.
            $this->getConnection()->addColumn($tableName, $name,
                array(
                    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length'    => 255,
                    'unsigned'  => true,
                    'nullable'  => false,
                    'comment'  => $comments,
                )
            );
        }
    }
    
    /**
     * This helps remove attributes for the Netstarter_Retaildirections_Model_Product model.
     * 
     * @param null $website_code Magento backend website code for the store to have attributes removed.
     */
    public function dropProductWebsiteScopeColumns ($website_code = null)
    {
        if (!is_string($website_code)) return;

        // What are the multi-scope attributes?
        $productDataModel = Mage::getModel('netstarter_retaildirections/model_product');
        $productDataModel->doNotMail(true);
        $productAttributes = $productDataModel->getProductAttributesSpecific();

        // What is the flat table?
        $tableName = Mage::getResourceModel('netstarter_retaildirections/product')->getMainTable();

        foreach($productAttributes as $attributes)
        {
            // convention is websitecode_attribute
            // please refer to the "product" database entity on config.xml ($tableName variable)
            $name = $website_code.'_'.$attributes;
            $comments = $name;

            // Drop the scope columns for each attribute.
            $this->getConnection()->dropColumn($tableName, $name);
        }
    }
}
