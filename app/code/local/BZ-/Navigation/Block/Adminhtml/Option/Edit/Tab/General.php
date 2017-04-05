<?php
/**
 * Class General
 *
 * @author ben_zhanghf@hotmail.com
 */
class BZ_Navigation_Block_Adminhtml_Option_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $model = Mage::registry('attribute_model');
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset',array(
            'legend'=>Mage::helper('bz_navigation')->__('Filter Settings Information'),
            'class'     => 'fieldset-wide'
            ));
        $filter = Mage::getModel('bz_navigation/filter');
        if ($model->getId()) {
            $filter->loadByAttributeId($model->getId());
            
            $fieldset->addField('filter_id', 'hidden', array(
                'name' => 'filter_id',
            ));
            
            $fieldset->addField('attribute_id', 'hidden', array(
                'name' => 'attribute_id',
            ));
            
            $options = array(0=>"Default Expand", 1=>"Default Hide");
            $fieldset->addField('display_mode', 'select', array(
                'label' => Mage::helper('bz_navigation')->__('Filter Mode'),
                'title' => Mage::helper('bz_navigation')->__('Filter Mode'),
                'name' => 'display_mode',
                'required' => false,
                'options' => $options,
                'note' => 'Actual display depends on the design template which can be changed by frontend developers.'
            ));
            
            $options = array(
                0=>"No Replacement",
                1=>"Use Image",
                2=>"Use Both Image and Label",
                3=>"Use Color Code",
                4=>"Use Both Color Code and Label");
            
            $fieldset->addField('image_mode', 'select', array(
                'label' => Mage::helper('bz_navigation')->__('Label Replacement'),
                'title' => Mage::helper('bz_navigation')->__('Label Replacement'),
                'name' => 'image_mode',
                'required' => false,
                'options' => $options,
                'note' => 'Actual implementation depends on frontend template'
            ));
            
            $fieldset->addField('image_width', 'text', array(
                'name' => 'image_width',
                'label' => Mage::helper('bz_navigation')->__('Image Width'),
                'title' => Mage::helper('bz_navigation')->__('Image Width'),
                'required' => false,
                'note' => 'Setting filter image width'
            ));
            
            $fieldset->addField('image_height', 'text', array(
                'name' => 'image_height',
                'label' => Mage::helper('bz_navigation')->__('Image Height'),
                'title' => Mage::helper('bz_navigation')->__('Image Height'),
                'required' => false,
                'note' => 'Setting filter image height'
            ));
            
            $fieldset->addField('option_limit', 'text', array(
                'name' => 'option_limit',
                'label' => Mage::helper('bz_navigation')->__('Option Display Limit'),
                'title' => Mage::helper('bz_navigation')->__('Option Display Limit'),
                'required' => false,
                'note' => 'Display only the number of options and hide others. (total number of options still loaded just hide other by css)'
            ));
              
            $fieldset->addField('is_follow', 'select', array(
                'name' => 'is_follow',
                'label' => Mage::helper('bz_navigation')->__('Is Follow By Search Engine'),
                'title' => Mage::helper('bz_navigation')->__('Is Follow By Search Engine'),
                'options' => array('1' => Mage::helper('bz_navigation')->__('Yes'), '0' => Mage::helper('bz_navigation')->__('No')),
                'selected' => 1,
                'note' => 'If no, all links from this filter will have no follow attribute. If Yes only has follow for one selection no multiple selection.'
            ));
            
            $fieldset->addField('tooltip', 'textarea', array(
                'name' => 'tooltip',
                'label' => Mage::helper('bz_navigation')->__('Tooltips'),
                'title' => Mage::helper('bz_navigation')->__('Tooltips'),
                'required' => false,
                'note' => 'Display Tooltips for this filter in HTML.'
            ));
            
            $fieldset->addField('meta_description', 'textarea', array(
                'name' => 'meta_description',
                'label' => Mage::helper('bz_navigation')->__('Meta Description Template'),
                'title' => Mage::helper('bz_navigation')->__('Meta Description Template'),
                'required' => false,
                'note' => 'Meta Description Template update, once the filter has option being selected.'
            ));
            
        }
        $data_array = $filter->getData();
        $data = array_merge($data_array,array('attribute_id'=>$model->getId()));
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    public function getTabLabel(){
        return Mage::helper('bz_navigation')->__('Filter Settings');
    }
   
    public function getTabTitle(){
        return Mage::helper('bz_navigation')->__('Filter Settings');
    }

    public function canShowTab(){
        return true;
    }

    public function isHidden(){
        return false;
    }
}
