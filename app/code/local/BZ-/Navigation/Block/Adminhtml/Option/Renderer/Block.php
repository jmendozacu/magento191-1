<?php

/**
 * Class Block Description
 * @param 
 * @package BZ_Block
 * @author Ben Zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Navigation_Block_Adminhtml_Option_Renderer_Block extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        //$showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            $actionUrl = Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/block');
            $id = $row->getOptionId();
            $template_id = 'block-'.$id;
            $html = '<select id="'.$template_id.'" name="block_id" style="margin-right:10px;">';
            $html .= '<option value=""></option>';
            foreach($options as $key => $name){
                if($value && $key == $value){
                    $html .= '<option value="'.$key.'" selected="selected">'.$name.'</option>';
                }
                else $html .= '<option value="'.$key.'">'.$name.'</option>';
            }
            $html .= '</select>';
            $onclick = "updateBlock('".$actionUrl."','".$id."')";
            $html .= '<button class="save block_id" type="button" onclick="'.$onclick.'"><span><span>save</span></span></button>';
            return $html;
        }
    }
}
