<?php
/**
 * Class
 *
 * @author ben zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Solr_Model_Adminhtml_System_Config_Source_Engine
{
    public function toOptionArray()
    {
        $engines = array(
            'no' => Mage::helper('bz_solr')->__('No Third Party Search Engine'),
            'bz_solr/engine'      => Mage::helper('bz_solr')->__('Solr 4+')
        );
        $options = array();
        foreach ($engines as $k => $v) {
            $options[] = array(
                'value' => $k,
                'label' => $v
            );
        }
        return $options;
    }
}
