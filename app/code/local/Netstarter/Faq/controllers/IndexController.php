<?php
/**
 * FAQ
 * @category   Netstarter
 * @package    Netstarter_Faq
 * @copyright  Copyright (c) 2012 Netstarter
 */
class Netstarter_Faq_IndexController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Displays the FAQ list.
	 */
	public function indexAction()
	{
        $this->loadLayout();
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setTitle(Mage::getStoreConfig('faq_section/settings_tab/config_faq_title'));
            $head->setKeywords(Mage::getStoreConfig('faq_section/settings_tab/config_faq_keywords'));
            $head->setDescription(Mage::getStoreConfig('faq_section/settings_tab/config_faq_meta_description'));
        }
        $this->renderLayout();
	}

	/**
	 * Displays the current FAQ's detail view
	 */
	/*public function showAction()
	{
		$this->loadLayout()->renderLayout();
	}*/
}
