<?php

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->startSetup();

$entityTypeId = $setup->getEntityTypeId('customer_address');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

$setup->addAttribute('customer_address', 'location_ref', array(
                                               'input'        => 'text',
                                               'type'         => 'text',
                                               'label'        => 'Retail Direction Location Ref',
                                               'required'     => 0,
                                               'user_defined' => 1,
                                               'is_visible'   => false,
                                          )
);

$attributeForm = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'location_ref');
$attributeForm->setData('used_in_forms', array('adminhtml_customer_address'));

$attributeForm->save();
$setup->endSetup();