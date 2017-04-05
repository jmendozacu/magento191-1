<?php

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->startSetup();

$entityTypeId = $setup->getEntityTypeId('customer');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

$setup->addAttribute('customer', 'rd_id', array(
                                               'input'        => 'text',
                                               'type'         => 'text',
                                               'label'        => 'Retail Direction ID',
                                               'required'     => 0,
                                               'user_defined' => 1,
                                               'is_visible'   => false,
                                          )
);

$attributeForm = Mage::getSingleton('eav/config')->getAttribute('customer', 'rd_id');
$attributeForm->setData('used_in_forms', array('adminhtml_customer'));

$attributeForm->save();
$setup->endSetup();