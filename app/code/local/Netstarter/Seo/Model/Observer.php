<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gayan Thrimanne
 * Date: 2/14/13
 * Time: 2:13 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_Seo_Model_Observer
{


    /* run @ cms_page_prepare_save action
     * save seo info to nets_seocms table
    */
    public function setCmsSeo($observer)
    {
        $cmsModel = $observer->getPage();
        $cmsRequest = $observer->getRequest();

        try {
            $seoCms = Mage::getModel('netstarter_seo/seocms')->load($cmsModel->getPageId(), 'page_id');
            if ($seoCms) {
                $seoCms->setPageId($cmsModel->getPageId());
            }

            $seoCms->setPagetitle($cmsRequest->getParam('pagetitle'));
            $seoCms->setShowInXmlsitemap($cmsRequest->getParam('show_in_xmlsitemap'));
            $seoCms->setFrequency($cmsRequest->getParam('frequency'));
            $seoCms->setPriority($cmsRequest->getParam('priority'));
            $seoCms->setRobotTags($cmsRequest->getParam('robot_tags'));
            $seoCms->setShowInSitemap($cmsRequest->getParam('show_in_sitemap'));
            $seoCms->setCanonicalUrl($cmsRequest->getParam('canonical_url'));
            $seoCms->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }


    /**
    run @ controller_action_layout_render_before_cms_page_view
    this will set meta title (browser title) with separate field added on cms pages
    if this field is empty it will show the default page name as meta title
     */
    public function setMetaTitle()
    {

        $pageId = Mage::getSingleton('cms/page')->getPageId();
        $seoCmsPage = Mage::getSingleton('netstarter_seo/seocms')->load($pageId, 'page_id');

        if ($seoCmsPage) {
            $pageTitle = $seoCmsPage->getPagetitle() != "" ? $seoCmsPage->getPagetitle() : Mage::getSingleton('cms/page')->getTitle();
        } else {
            $pageTitle = Mage::getSingleton('cms/page')->getTitle();
        }

        $head = Mage::app()->getLayout()->getBlock('head');

        if ($head) {
            $head->setTitle($pageTitle);
        }

        return $this;
    }

    /*  run @ controller_action_layout_render_before_cms_page_view
    will set the canonical url for cms page
    */
    public function setCanonicalCmsUrl(){

        $pageId = Mage::getSingleton('cms/page')->getPageId();
        $seoCmsPage = Mage::getSingleton('netstarter_seo/seocms')->load($pageId, 'page_id');

        Mage::register('canonical_cms_url', $seoCmsPage->getCanonicalUrl());

        return $this;
    }

    /*
     * run @ adminhtml_cms_page_edit_tab_main_prepare_form
     * Add Cms Fields ( pagetitle / show_in_xmlsitemap / frequency / priority)
     * */
    public function cmsField($observer)
    {
        //get form instance
        $form = $observer->getForm();

        //get CMS model with data
        $cmsPage = Mage::registry('cms_page');

        $seoCmsPage = Mage::getModel('netstarter_seo/seocms')->load($cmsPage->getPageId(), 'page_id');

        $cmsPage->setPagetitle($seoCmsPage->getPagetitle());
        $cmsPage->setShowInXmlsitemap($seoCmsPage->getShowInXmlsitemap());
        $cmsPage->setFrequency($seoCmsPage->getFrequency());
        $cmsPage->setPriority($seoCmsPage->getPriority());
        $cmsPage->setRobotTags($seoCmsPage->getRobotTags());
        $cmsPage->setShowInSitemap($seoCmsPage->getShowInSitemap());
        $cmsPage->setCanonicalUrl($seoCmsPage->getCanonicalUrl());


        //create new custom fieldset 'netstarter_content_fieldset'
        $fieldset = $form->addFieldset(
            'page_meta_fieldset',
            array('legend' => Mage::helper('cms')->__('Seo'),
                'class' => 'fieldset')
        );

        //add new fields
        $fieldset->addField('pagetitle', 'text', array(
            'name' => 'pagetitle',
            'label' => Mage::helper('cms')->__('Navigation Title'),
            'title' => Mage::helper('cms')->__('Navigation Title'),
            'disabled' => false
        ));


        $fieldset->addField('show_in_sitemap', 'select', array(
            'name' => 'show_in_sitemap',
            'label' => Mage::helper('cms')->__('Show in HTML Sitemap'),
            'title' => Mage::helper('cms')->__('Show in HTML Sitemap'),
            'required' => false,
            'options' => array(
                '1' => Mage::helper('cms')->__('Yes'),
                '0' => Mage::helper('cms')->__('No'),
                '2' => Mage::helper('cms')->__('Please select')
            )
        ));

        $fieldset->addField('show_in_xmlsitemap', 'select', array(
            'name' => 'show_in_xmlsitemap',
            'label' => Mage::helper('cms')->__('Show in XML Sitemap'),
            'title' => Mage::helper('cms')->__('Show in XML Sitemap'),
            'required' => false,
            'options' => array(
                '1' => Mage::helper('cms')->__('Yes'),
                '0' => Mage::helper('cms')->__('No'),
                '2' => Mage::helper('cms')->__('Please select')
            )
        ));

        $fieldset->addField('frequency', 'select', array(
            'name' => 'frequency',
            'label' => Mage::helper('cms')->__('Frequency'),
            'title' => Mage::helper('cms')->__('Frequency'),
            'required' => false,
            'options' => array(
                'always' => Mage::helper('cms')->__('always'),
                'hourly' => Mage::helper('cms')->__('hourly'),
                'daily' => Mage::helper('cms')->__('daily'),
                'weekly' => Mage::helper('cms')->__('weekly'),
                'monthly' => Mage::helper('cms')->__('monthly'),
                'yearly' => Mage::helper('cms')->__('yearly'),
                'never' => Mage::helper('cms')->__('never'),
                '' => Mage::helper('cms')->__('Please select')
            )
        ));

        $fieldset->addField('priority', 'select', array(
            'name' => 'priority',
            'label' => Mage::helper('cms')->__('Priority'),
            'title' => Mage::helper('cms')->__('Priority'),
            'required' => false,
            'options' => array(
                '0' => Mage::helper('cms')->__('0'),
                '0.1' => Mage::helper('cms')->__('0.1'),
                '0.2' => Mage::helper('cms')->__('0.2'),
                '0.3' => Mage::helper('cms')->__('0.3'),
                '0.4' => Mage::helper('cms')->__('0.4'),
                '0.5' => Mage::helper('cms')->__('0.5'),
                '0.6' => Mage::helper('cms')->__('0.6'),
                '0.7' => Mage::helper('cms')->__('0.7'),
                '0.8' => Mage::helper('cms')->__('0.8'),
                '0.9' => Mage::helper('cms')->__('0.9'),
                '1' => Mage::helper('cms')->__('1'),
                '' => Mage::helper('cms')->__('Please select')
            )
        ));

        $fieldset->addField('robot_tags', 'select', array(
            'name' => 'robot_tags',
            'label' => Mage::helper('cms')->__('Robot Tag'),
            'title' => Mage::helper('cms')->__('Robot Tag'),
            'required' => false,
            'options' => array(
                'NOINDEX, FOLLOW' => Mage::helper('cms')->__('NOINDEX, FOLLOW'),
                'INDEX, NOFOLLOW' => Mage::helper('cms')->__('INDEX, NOFOLLOW'),
                'NOINDEX, NOFOLLOW' => Mage::helper('cms')->__('NOINDEX, NOFOLLOW'),
                'INDEX, FOLLOW' => Mage::helper('cms')->__('INDEX, FOLLOW'),
                'INDEX, FOLLOW' => Mage::helper('cms')->__('Please Select')
            )
        ));

        $fieldset->addField('canonical_url', 'text', array(
            'name' => 'canonical_url',
            'label' => Mage::helper('cms')->__('Canonical Url'),
            'title' => Mage::helper('cms')->__('Canonical Url'),
            'disabled' => false
        ));

    }
}