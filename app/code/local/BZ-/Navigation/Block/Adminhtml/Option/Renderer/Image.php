<?php
/**
 * renderer for grid image uploader and image display
 * author Ben Zhang <bzhang@netstart.com.au>
 */
class BZ_Navigation_Block_Adminhtml_Option_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $data = parent::_getValue($row);
        $html = '';
        $option_id = $row->getData('option_id');
        $col_id = $this->getColumn()->getId();
        $upload_id = $option_id.'-up-'.$col_id;
        if($data) $has_img = true;
        else $has_img = false;
        if($data){
            $src_url = Mage::getBaseUrl('media').'bz_navigation/'.$data;
            $image = '<div class="img-holder"><img src="' . $src_url . '" alt="image" style="width:25px; height:25px;float:left;border:1px solid #666;margin-right:10px;" /></div>';
            $html .= $image;
        }else{
            $html .= '<div class="img-holder">...</div>';
        }
        $upload_block = $this->getLayout()->createBlock('adminhtml/media_uploader')
                ->setFieldId($col_id)
                ->setHasImage($has_img)
                ->setHtmlId($upload_id)
                ->setOptionId($option_id)
                ->setTemplate('bz_navigation/upload.phtml');
        $html .= $upload_block->toHtml();
        
        return $html;
    }
	
}
	