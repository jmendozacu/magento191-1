<?php
 
class Netstarter_Afeature_Block_Adminhtml_Afeature_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('afeature_form', array('legend'=>Mage::helper('afeature')->__('Item information')));
       
        $fieldset->addField('title', 'text', array(
            'label'     => $this->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
            'note'      => $this->__('This text is not displayed on the website')
            
        ));

        $fieldset->addField('active', 'select', array(
            'label'     => $this->__('Status'),
            'name'      => 'active',
            'values'    => array(
                array(
                    'value'     => 0,
                    'label'     => 'Inactive',
                ),
                array(
                    'value'     => 1,
                    'label'     => 'Active',
                ),
            ),
        ));

        $fieldset->addField('is_hidden', 'select', array(
            'label'     => $this->__('Is hidden in desktop'),
            'name'      => 'is_hidden',
            'note'      => 'If \'Hide\' the image will only be hidden in mobile view. If \'Show\' the image will be shown in all views',
            'values'    => array(
                array(
                    'value'     => 0,
                    'label'     => 'Show',
                ),
                array(
                    'value'     => 1,
                    'label'     => 'Hide',
                ),
            ),
        ));


        $mainImageUrl = Mage::registry('afeature_data')->getImageUrl();

        $fieldset->addField('fileinputname', 'image', array(
            'label'     => $this->__('Image'),
            'required'  => true,
            'name'      => 'fileinputname',
            'note'      => $this->__('Max. file size = 500 kb. Only PNG, JPG, JPEG types are allowed')
        ))->setAfterElementHtml(($mainImageUrl?"<img src='/media/afeature/main/".$mainImageUrl."' height='auto' width='100px'/>":''));

        $mobileImageUrl = Mage::registry('afeature_data')->getMobileImageUrl();

        $fieldset->addField('mobile_fileinputname', 'image', array(
            'label'     => $this->__('Mobile Image'),
//            'required'  => true,
            'name'      => 'mobile_fileinputname',
            'note'      => $this->__('Max. file size = 500 kb. Only PNG, JPG, JPEG types are allowed')
        ))->setAfterElementHtml(($mobileImageUrl?"<img src='/media/afeature/mobile/".$mobileImageUrl."' height='auto' width='100px'/>":''));

        $fieldset->addField('url', 'text', array(
            'label'     => $this->__('URL'),
            'required'  => false,
            'name'      => 'url'
        ));
        $fieldset->addField('alt', 'text', array(
            'label'     => $this->__('Image Alt Tag'),
            'required'  => false,
            'name'      => 'tagline',
            'note'      => $this->__('Alt tag will be improve search engine optimisation')
        ));

        $status =  $fieldset->addField('has_text', 'select', array(
            'label'     => $this->__('Has Text Layer'),
            'name'      => 'has_text',
            'values'    => array(
                1 => 'Yes',
                0   => 'No'
            )
        ));

        $position = $fieldset->addField('text_position', 'select', array(
            'label'     => $this->__('Text Position'),
            'name'      => 'text_position',
            'values'    => array(
                array(
                    'value'     => 0,
                    'label'     => $this->__('Left'),
                ),
                array(
                    'value'     => 1,
                    'label'     => $this->__('Right'),
                ),
            ),
        ));

        $shortDesc = $fieldset->addField('short_desc', 'text', array(
            'label'     => $this->__('Short Description'),
            'required'  => false,
            'name'      => 'short_desc',
            'note'      => $this->__('20 maximum characters')
        ));


        $longDesc = $fieldset->addField('long_desc', 'text', array(
            'label'     => $this->__('Long Description'),
            'required'  => false,
            'name'      => 'long_desc',
            'note'      => $this->__('40 maximum characters')
        ));

        $linkText = $fieldset->addField('link_text', 'text', array(
            'label'     => $this->__('Link Text'),
            'required'  => false,
            'name'      => 'link_text',
            'note'      => $this->__('20 maximum characters')
        ));

        $bgColor = $fieldset->addField('bg_color', 'text', array(
            'label'     => $this->__('Background Color'),
            'required'  => false,
            'name'      => 'bg_color',
            'class'     => 'color {required:false, adjust:false, hash:true}',
            'note'      => $this->__('Select the Color')
        ));



        if ( Mage::getSingleton('adminhtml/session')->getAfeatureData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getAfeatureData());
            Mage::getSingleton('adminhtml/session')->setAfeatureData(null);
        } elseif ( Mage::registry('afeature_data') ) {
            $form->setValues(Mage::registry('afeature_data')->getData());
        }


        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($status->getHtmlId(), $status->getName())
                ->addFieldMap($shortDesc->getHtmlId(), $shortDesc->getName())
                ->addFieldMap($longDesc->getHtmlId(), $longDesc->getName())
                ->addFieldMap($linkText->getHtmlId(), $linkText->getName())
                ->addFieldMap($bgColor->getHtmlId(), $bgColor->getName())
                ->addFieldMap($position->getHtmlId(), $position->getName())
                ->addFieldDependence(
                    $shortDesc->getName(),
                    $status->getName(),
                    1
                )
                ->addFieldDependence(
                    $longDesc->getName(),
                    $status->getName(),
                    1
                )->addFieldDependence(
                    $linkText->getName(),
                    $status->getName(),
                    1
                )->addFieldDependence(
                    $bgColor->getName(),
                    $status->getName(),
                    1
                )->addFieldDependence(
                    $position->getName(),
                    $status->getName(),
                    1
                )
        );

        return parent::_prepareForm();
    }
}