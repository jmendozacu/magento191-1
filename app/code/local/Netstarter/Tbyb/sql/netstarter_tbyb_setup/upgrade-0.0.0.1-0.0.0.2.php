<?php

$installer = $this;
$installer->startSetup();


$salesResourceSetupModel = Mage::getModel('sales/resource_setup', 'core_setup');

$data = array(
    'type' => 'date',
    'input' => 'date',
    'label' => 'Future Payment Date',
    'global' => 1,
    'is_required' => '0',
    'is_comparable' => '0',
    'is_searchable' => '0',
    'is_unique' => '0',
    'is_configurable' => '0',
    'user_defined' => '1',
);

//first param should relate to a key of the protected $_flatEntityTables array
$salesResourceSetupModel->addAttribute('order', 'future_payment_date', $data);


$installer->endSetup();
