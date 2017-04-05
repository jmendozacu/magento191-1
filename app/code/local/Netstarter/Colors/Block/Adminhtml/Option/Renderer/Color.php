<?php
/**
 * Class Color
 *
 * @author bzhang@netstarter.com.au
 */
class Netstarter_Colors_Block_Adminhtml_Option_Renderer_Color extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $data = $this->_getValue($row);
        $id = $row->getOptionId();
        $colorUrl = Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/color');
        $color_id = 'color-'.$id;
        $html = '<div id="'.$color_id.'" style="width:25px; height:25px; border:1px solid #666; margin-right:20px;float:left;"></div>';
        $html .= '<input class="color {styleElement:\''.$color_id.'\'}" style="width:60px; margin-right:10px;" value="'.$data.'" id="input-'.$id.'"/>';
        $onclick = "updateColor('".$colorUrl."','".$id."')";
        $html .= '<button class="save color-code" type="button" onclick="'.$onclick.'"><span><span>Update</span></span></button>';
        return $html;
    }
}
