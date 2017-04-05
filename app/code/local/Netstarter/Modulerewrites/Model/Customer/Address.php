<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 6/11/13
 * Time: 9:21 AM
 * To change this template use File | Settings | File Templates.
 */ 
class Netstarter_Modulerewrites_Model_Customer_Address extends Mage_Customer_Model_Address
{

    /**
     * check original values are changed
     *
     * specific usage in RD customer address save observer
     *
     * @return bool
     */
    public function hasOriginalDataChanges()
    {
        if (!$this->getOrigData()) {
            return true;
        }

        $fields = $this->getOrigData();

        foreach (array_keys($fields) as $field) {

            if ($field != 'updated_at' && $this->getOrigData($field) != $this->getData($field)) {
                return true;
            }
        }

        return false;
    }
}