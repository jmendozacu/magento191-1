<?php
 
class Netstarter_Afeature_Model_Mysql4_Afeature_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        //parent::__construct();
        $this->_init('afeature/afeature');
    }

    public function setRandomOrder()
    {
        $this->getSelect()->order(new Zend_Db_Expr('RAND()'));
        return $this;
    }
}