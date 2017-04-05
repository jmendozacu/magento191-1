<?php

/**
 * Class Netstarter_Retaildirections_Model_System_Config_Source_Category
 *
 * @category  Netstarter
 * @package   Netstarter_Retaildirections
 *
 * Generates the category selector for the filter in the backend product list.
 * Catalog -> Product
 */
class Netstarter_Retaildirections_Model_System_Config_Source_Category
{
    /**
     * Separator for each category level deep nested.
     */
    const SEPARATOR = "--";

    /**
     * Default Magento function that returns all options.
     * It's Magento convention.
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionArray($addEmpty = true)
    {
        $options = array();
        foreach ($this->loadCategoryTree() as $category) {
            $options[$category['value']] = $category['label'];
        }

        return $options;
    }

    /**
     * Navigates recursively into the category tree.
     *
     * @param Varien_Data_Tree_Node $node
     * @param $values
     * @param int $level
     * @return mixed
     */
    public function buildCategoriesMultiselectValues(Varien_Data_Tree_Node $node, $values, $level = 0)
    {
        $level++;

        $values[$node->getId()]['value'] = $node->getId();
        $values[$node->getId()]['label'] = str_repeat(self::SEPARATOR, $level) . $node->getName();

        foreach ($node->getChildren() as $child)
        {
            // $value is sent to the next call and amended
            $values = $this->buildCategoriesMultiselectValues($child, $values, $level);
        }

        return $values;
    }

    /**
     * Load the category tree based on current Magento admin backend selection
     * of store scope.
     *
     * @return mixed
     */
    public function loadCategoryTree()
    {
        // get current store scope selected in the backend.
        $store = Mage::app()->getFrontController()->getRequest()->getParam('store', 0);

        // get root category id.
        $parentId = $store ? Mage::app()->getStore($store)->getRootCategoryId() : 1;

        $tree = Mage::getResourceSingleton('catalog/category_tree')->load();
        $root = $tree->getNodeById($parentId);

        // just in case no root category is retrieved
        if($root && $root->getId() == 1)
        {
            $root->setName(Mage::helper('catalog')->__('Root'));
        }

        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId($store)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');

        $tree->addCollectionData($collection, true);

        // recursively build array
        return $this->buildCategoriesMultiselectValues($root, array());
    }
}