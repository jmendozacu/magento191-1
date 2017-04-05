<?php

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->startSetup();

$setup->removeAttribute('customer', 'rd_id');


$entityTypeCusId = $setup->getEntityTypeId('customer');
$attributeCusSetId = $setup->getDefaultAttributeSetId($entityTypeCusId);

$setup->addAttribute('customer', 'rd_id', array(
        'input'        => 'text',
        'type'         => 'varchar',
        'global'       =>    1,
        'label'        => 'Retail Direction ID',
        'required'     => 0,
        'default'      =>  '',
        'user_defined' => 1,
        'source'      =>   NULL,
        'is_visible'   => false,
    )
);

$attributeForm = Mage::getSingleton('eav/config')->getAttribute('customer', 'rd_id');
$attributeForm->setData('used_in_forms', array('adminhtml_customer'));
$attributeForm->save();

$setup->removeAttribute('customer_address', 'location_ref');
$entityTypeId = $setup->getEntityTypeId('customer_address');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

$setup->addAttribute('customer_address', 'location_ref', array(
        'input'        => 'text',
        'type'         => 'varchar',
        'global'       =>    1,
        'label'        => 'Retail Direction Location Ref',
        'required'     => 0,
        'default'      =>  '',
        'source'      =>   NULL,
        'user_defined' => 1,
        'is_visible'   => false,
    )
);

$attributeForm = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'location_ref');
$attributeForm->setData('used_in_forms', array('adminhtml_customer_address'));

$attributeForm->save();
$setup->endSetup();