<?php
/**
 * Category renderer for Magento product's grid.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Class Netstarter_Retaildirections_Block_Adminhtml_Catalog_Product_Grid_Render_Category
 */
class Netstarter_Retaildirections_Block_Adminhtml_Catalog_Product_Grid_Render_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param Varien_Object $row
     * @return mixed
     */
    public function render(Varien_Object $row)
    {
        // Get the data from the SQL result.
        return $row->getData('category_list');
    }
}