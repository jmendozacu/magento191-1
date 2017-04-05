<?php

class Netstarter_Productwidget_Model_Mysql4_Look_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		//parent::__construct();
		$this->_init('productwidget/look');
	}
 }