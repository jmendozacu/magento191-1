<?php
/**
 * @author: ben zhang <ben_zhang@hotmail.com>
 */
class BZ_Solr_Block_Adminhtml_Catalog_Attribute_Edit extends Mage_Adminhtml_Block_Template
{
    public function isThirdPartSearchEngine()
    {
        return Mage::helper('bz_solr')->isThirdPartSearchEngine();
    }
}
