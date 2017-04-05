<?php
/**
 * Class Filter
 *
 *
 */
class Netstarter_Colors_Model_Resource_Filter_Option extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('colors/filter_option', 'value_id');
    }

    /**
     * get associate list of relative colors
     *
     * @param $options
     * @param $mode
     * @return array
     */
    public function getFilteredOptions($options,$mode)
    {

        $data = array();

        if(!empty($options)){

            $read = $this->_getReadAdapter();

            $selectAttr = ($mode == 1)?array('option_id','option'=>'color_code'):
                array('option_id','option'=>'filename');


            $select = $read->select()->from(array('idx' => $this->getMainTable()),$selectAttr);
            $select->where('option_id IN (?)', $options);
            $select->limit(50);

            $data = $read->fetchAssoc($select);
        }

        return $data;
    }
}