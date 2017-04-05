<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 7/18/13
 * Time: 3:33 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Extcatalog_Block_Layer_Sublist extends Mage_Core_Block_Template
{
    /**
     * @todo need to find a better way to get subcats
     *
     * @return null
     */

    public function getSiblingList()
    {
        $category = Mage::registry('current_category');

        if($category){

            $parentCatId = $category->getParentId();

            if($parentCatId){

                $children = Mage::getModel('catalog/category')
                            ->load($parentCatId)
                            ->getChildrenCategories();

                return $children;
            }
        }

        return null;
    }
}