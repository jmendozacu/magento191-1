<?php
/**
 * Changes in the product grid on Magento backend.
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Class Netstarter_Retaildirections_Block_Adminhtml_Catalog_Product_Grid
 */
class Netstarter_Retaildirections_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    /**
     * Add the category list of a product in the grid page.
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('category_list', array(
            'header'	=> Mage::helper('netstarter_retaildirections')->__('Category'),
            'index'	=> 'category_list',
            'sortable'	=> false,
            'width' => '250px',
            'type' => 'options',
            'options'	=> Mage::getSingleton('netstarter_retaildirections/system_config_source_category')->toOptionArray(),
            'renderer'	=> 'netstarter_retaildirections/adminhtml_catalog_product_grid_render_category',
            'filter_condition_callback' => array($this, 'filterCallback'),
        ),'name');

        return parent::_prepareColumns();
    }

    /**
     * When the field is used in the filter, this is added in the collection to filter it out.
     *
     * @param $collection
     * @param $column
     * @return mixed
     */
    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $_category = Mage::getModel('catalog/category')->load($value);
        $collection->addCategoryFilter($_category);
        return $collection;
    }

    /**
     * This changes product collection to add category information.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
		
        $return = parent::_prepareCollection();

        // the category already comes loaded at this point, which is a shame
        // since it removes flexibility. we can clear it out and change, but
        // it means the category was loaded twice.
        $this->getCollection()->clear();

        $eavAttribute = Mage::getModel('eav/resource_entity_attribute');
        $id = $eavAttribute->getIdByCode('catalog_category', 'name');

        // generete a new select object
        // $productCategories = $this->getCollection()->getConnection()->select();

        // create the category joins
        $this->getCollection()
                ->getSelect()
                ->joinLeft(array('cp' => 'catalog_category_product'),
                        'cp.product_id = e.entity_id',
                        null);

        $this->getCollection()
                ->getSelect()
                ->joinLeft(array('cv' => 'catalog_category_entity_varchar'),
                        "cp.category_id = cv.entity_id AND cv.attribute_id = $id",
                        array("group_concat(cv.value SEPARATOR '<br />') AS category_list"));

        $this->getCollection()
                ->getSelect()
                ->group('e.entity_id');
		
	// adds website information, as per parent collection
        $this->getCollection()->addWebsiteNamesToResult();
        
	return $return;
    }
}