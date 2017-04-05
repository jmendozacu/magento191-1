<?php
/**
 * Top menu block
 * extend Mage_Page_Block_Html_Topmenu *
 */

class Netstarter_Menu_Block_Html_Topmenu extends Mage_Page_Block_Html_Topmenu
{
    protected $_menu;

    private $_persistedCats  = array();
    /**
     * Current entity key
     *
     * @var string|int
     */
    protected $_currentEntityKey;

    /**
     * Init top menu tree structure
     */
    public function _construct()
    {
        $this->_menu = new Varien_Data_Tree_Node(array(), 'root', new Varien_Data_Tree());

        $this->addData(array(
            'cache_lifetime' => false,
            'cache_tags'    => array(Mage_Core_Model_Store::CACHE_TAG, Mage_Cms_Model_Block::CACHE_TAG)
        ));
    }


    private function _persistCats(){

        $tmp = Mage::registry('persist_cats');
        if(empty($tmp)){
            Mage::register('persist_cats', $this->_persistedCats);
        }
    }


    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '')
    {
        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu,
            'block' => $this
        ));

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtml($this->_menu, $childrenWrapClass);

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
            'menu' => $this->_menu,
            'html' => $html
        ));

        return $html;
    }



    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param Varien_Data_Tree_Node $menuTree
     * @param string $childrenWrapClass
     * @return string
     */
    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass)
    {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {

            $categoryNodeId = $child->getData('id');
            $catIdArr = explode('-', $categoryNodeId); // exploding category ids
            $catId = end($catIdArr);

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            //get subcat limit from admin configuration
            $subcatLimit = Mage::getStoreConfig('catalog/frontend/subcategory_limit', Mage::app()->getStore());

            if (!empty($catId)) {
                $category = Mage::getModel('catalog/category')->load($catId);
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $catids = explode(",",Mage::getStoreConfig('design/catalogpage/catlabelpos',Mage::app()->getStore()));
            $catlabel= Mage::getStoreConfig('design/catalogpage/catlabelname',Mage::app()->getStore());
            if(!in_array(str_replace( "category-node-", "", $child->getId() ), $catids))
            {
            $html .= '<a href="' . $category->getUrl() . '" ' . $outermostClassCode . '><span>'
                . $this->escapeHtml($child->getName()) .'</span></a>';
            }
            else
            {
                $html .= '<a href="' . $category->getUrl() . '" ' . $outermostClassCode . '><span style="
    margin-left: -22%;">'
                    . $this->escapeHtml($child->getName()) . '<span class="cat-label cat-label-label2 pin-bottom">'.$catlabel.'</span></span></a>';

            }
            if ($child->hasChildren()) {
                if (!empty($childrenWrapClass)) {
                    $html .= '<div class="' . $childrenWrapClass . '">';
                }

                // get category collection with the limit of cat items per block.
                $collection = Mage::getModel('catalog/category')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('include_in_menu', 1)
                    ->addAttributeToFilter('is_active', 1)
                    ->addAttributeToSelect('custom_link_url')
                    ->addAttributeToFilter('parent_id', $catId)
                    ->addAttributeToSort('position', 'ASC')
                    ->setPageSize($subcatLimit);
                $count = 0;

                $html .= '<div class="sub-nav-main-wraper level' . $childLevel . '">';

                $html .= "<div class='closemenu'></div>";
                $html .= "<div class='subnavMainWraperSub'>";
                $html .= "<div class='cat-title'><h2>" . $this->escapeHtml($child->getName()) . "</h2></div><div class='linkWraper'>";
                $html .= "<div class='links'>";
                foreach ($collection as $cat){
                    if($count == 0) {
                        $html .= '<ul>'; // if count = 0, start <ul>
                    }

                    // generate sub category menu

                    $html .= "<li class='nav-item'><a href='" . $cat->getUrl() . "'>" . $cat->getName() . "</a></li>";
                    $count ++;
                    if ($count > 7) {
                        $html .= "</ul>"; // if count = itemsPerRow, close </ul>
                        $count = 0; // set count to 0
                    }
                }

                $html .= "</div>";

                $html .= $this->_setFilters($catId);
                $html .= "</div>";

                //$category = Mage::getModel('catalog/category')->load($catId);
                $path = Mage::getBaseUrl('media', true) . 'catalog' . DS . 'category' . DS;

                if($category->getNavigationImage() == "") :
                    $navImage = $path . "50-off.jpg";
                else :
                    $navImage = $path . $category->getNavigationImage();
                endif;


                $html .= '<div class="sub-nav-main-wraper-right">';

                $html .= "<a href='" . $category->getUrl() . "' ><img src='" . $navImage . "' /></a>";

                $html .= '</div></div></div>';

            }
            $html .= '</li>';

            $counter++;

            $obj = new Varien_Object();
            $obj->setName($child->getName());
            $obj->setUrl($child->getUrl());

            //$this->_persistedCats[$child->getId()] = $obj;
        }

        //$this->_persistCats();

        return $html;
    }

    /**
     * Generates top menu filters from attributes in admin
     *
     * @param int $catId
     * @return string
     */

    protected function _setFilters($catId)
    {
        // to get attribute codes "promotion type and homepage_feature"
        $productModel = Mage::getModel('catalog/product');

        $promo_attr = $productModel->getResource()->getAttribute("features");

        $html = "";

        if($promo_attr){

            $category = Mage::getModel('catalog/category')->load($catId);
            $currentCategory = strtolower($category->getUrlKey());

            /* $html = "<div class='sub-nav-main-special-links' style='height: 60px;border:none;'>";
            * $html .= "<ul>";
               $html .= "<li class='whatsNew'>";
               $html .= "<a href='/" . $currentCategory . "/features/new/1' class='new-relese'>New</a>";
               $html .= "</li>";

               $html .= "<li class='onSale'>";
               $html .= "<a href='/" . $currentCategory . "/features/our-picks/1' class='sale'>Our Picks</a>";
               $html .= "</li>";

               $html .= "<li class='bestSeller'>";
               $html .= "<a href='/" . $currentCategory . "/features/best-sellers/1' class='best-sellers'>Best Sellers</a>";
               $html .= "</li>";

               $html .= "</ul>";
            $html .= "</div>";*/
        }

        return $html;
    }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @param Varien_Data_Tree_Node $item
     * @return string
     */
    protected function _getRenderedMenuItemAttributes(Varien_Data_Tree_Node $item)
    {
        $html = '';
        $attributes = $this->_getMenuItemAttributes($item);

        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
        }

        return $html;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(Varien_Data_Tree_Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        $attributes = array(
            'class' => implode(' ', $menuItemClasses)
        );

        return $attributes;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Varien_Data_Tree_Node $item)
    {
        $classes = array();

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }
}
