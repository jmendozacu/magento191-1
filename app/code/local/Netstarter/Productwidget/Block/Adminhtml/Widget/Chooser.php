<?php
class Netstarter_Productwidget_Block_Adminhtml_Widget_Chooser extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Store selected banner Ids
     * Used in initial setting selected products
     *
     * @var array
     */
    protected $_selectedProducts = array();

    /**
     * Store hidden banner ids field id
     *
     * @var string
     */
    protected $_elementValueId = '';

    /**
     * Block construction, prepare grid params
     *
     * @param array $arguments Object data
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
        $this->setDefaultFilter(array('in_products'=>1));
    }

    /**
     * Prepare chooser element HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element Form Element
     * @return Varien_Data_Form_Element_Abstract
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_elementValueId = "{$element->getId()}";
        $this->_selectedProducts = explode(',', $element->getValue());

        //Create hidden field that store selected banner ids
        $hidden = new Varien_Data_Form_Element_Hidden($element->getData());
        $hidden->setId($this->_elementValueId)->setForm($element->getForm());
        $hiddenHtml = $hidden->getElementHtml();

        $element->setValue('')->setValueClass('value2');
        $element->setData('after_element_html', $this->toHtml());
        //$element->setData('after_element_html', $this->toHtml().$hiddenHtml);
        $element->setData('after_element_html', $hiddenHtml.$this->toHtml());
        return $element;
    }




    /**
     * Grid row init js callback
     *
     * @return string
     */
    public function getRowInitCallback()
    {

        return 'function(grid, row){
            if(!grid.selProductsIds){
                grid.selProductsIds = {};
                if($(\'' . $this->_elementValueId . '\').value != \'\'){
                    var elementValues = $(\'' . $this->_elementValueId . '\').value.split(\',\');
                    for(var i = 0; i < elementValues.length; i++){
                        grid.selProductsIds[elementValues[i]] = i+1;
                    }
                }
                grid.reloadParams = {};
                grid.reloadParams[\'selected_products[]\'] = Object.keys(grid.selProductsIds);
            }
            var inputs      = Element.select($(row), \'input\');
            var checkbox    = inputs[0];
            var position    = inputs[1];
            var productsNum  = grid.selProductsIds.length;
            var productId    = checkbox.value;

            inputs[1].checkboxElement = checkbox;

            var indexOf = Object.keys(grid.selProductsIds).indexOf(productId);
            if(indexOf >= 0){
                checkbox.checked = true;
                if (!position.value) {
                    position.value = indexOf + 1;
                }
            } else {
                checkbox.checked = false;
            }
            checkbox.disabled = false;

            Event.observe(position,\'change\', function(){
                var checkb = Element.select($(row), \'input\')[0];
                if(checkb.checked){
                    grid.selProductsIds[checkb.value] = this.value;
                    var idsclone = Object.clone(grid.selProductsIds);
                    var bans = Object.keys(grid.selProductsIds);
                    var pos = Object.values(grid.selProductsIds).sort(sortNumeric);
                    var products = [];
                    var k = 0;

                    for(var j = 0; j < pos.length; j++){
                        for(var i = 0; i < bans.length; i++){
                            if(idsclone[bans[i]] == pos[j]){
                                products[k] = bans[i];
                                k++;
                                delete(idsclone[bans[i]]);
                                break;
                            }
                        }
                    }
                    $(\'' . $this->_elementValueId . '\').value = products.join(\',\');
                }
            });
        }
        ';

    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {

        return 'function (grid, event) {
                if(!grid.selProductsIds){
                    grid.selProductsIds = {};
                }

                var trElement   = Event.findElement(event, "tr");
                var isInput     = Event.element(event).tagName == \'INPUT\';
                var inputs      = Element.select(trElement, \'input\');
                var checkbox    = inputs[0];
                var position    = inputs[1].value || 1;
                var checked     = isInput ? checkbox.checked : !checkbox.checked;
                checkbox.checked = checked;
                var productId    = checkbox.value;

                if(checked){
                    if(Object.keys(grid.selProductsIds).indexOf(productId) < 0){
                        grid.selProductsIds[productId] = position;
                    }
                }
                else{
                    delete(grid.selProductsIds[productId]);
                }

                var idsclone = Object.clone(grid.selProductsIds);
                var bans = Object.keys(grid.selProductsIds);
                var pos = Object.values(grid.selProductsIds).sort(sortNumeric);
                var products = [];
                var k = 0;
                for(var j = 0; j < pos.length; j++){
                    for(var i = 0; i < bans.length; i++){
                        if(idsclone[bans[i]] == pos[j]){
                            products[k] = bans[i];
                            k++;
                            delete(idsclone[bans[i]]);
                            break;
                        }
                    }
                }
                $(\'' . $this->_elementValueId . '\').value = products.join(\',\');
                grid.reloadParams = {};
                grid.reloadParams[\'selected_products[]\'] = products;
            }
        ';

    }

    /**
     * Checkbox Check JS Callback
     *
     * @return string
     */
    public function getCheckboxCheckCallback()
    {

        return 'function (grid, element, checked) {
                    if(!grid.selProductsIds){
                        grid.selProductsIds = {};
                    }
                    var checkbox    = element;

                    checkbox.checked = checked;
                    var productId    = checkbox.value;
                    if(productId == \'on\'){
                        return;
                    }
                    var trElement   = element.up(\'tr\');
                    var inputs      = Element.select(trElement, \'input\');
                    var position    = inputs[1].value || 1;

                    if(checked){
                        if(Object.keys(grid.selProductsIds).indexOf(productId) < 0){
                            grid.selProductsIds[productId] = position;
                        }
                    }
                    else{
                        delete(grid.selProductsIds[productId]);
                    }

                    var idsclone = Object.clone(grid.selProductsIds);
                    var bans = Object.keys(grid.selProductsIds);
                    var pos = Object.values(grid.selProductsIds).sort(sortNumeric);
                    var products = [];
                    var k = 0;
                    for(var j = 0; j < pos.length; j++){
                        for(var i = 0; i < bans.length; i++){
                            if(idsclone[bans[i]] == pos[j]){
                                products[k] = bans[i];
                                k++;
                                delete(idsclone[bans[i]]);
                                break;
                            }
                        }
                    }
                    $(\'' . $this->_elementValueId . '\').value = products.join(\',\');
                    grid.reloadParams = {};
                    grid.reloadParams[\'selected_products[]\'] = products;
                }';

    }




    protected function _prepareColumns()
    {
        $productsValues = $this->getSelectedProducts() ? $this->getSelectedProducts() : '';

        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_products',
            'values'    => $productsValues,
            'align'     => 'center',
            'index'     => 'product_id',
        ));

        $this->addColumn('position', array(
            'header'         => $this->__('Position'),
            'name'           => 'position',
            'type'           => 'number',
            'width' => '50px',
            'validate_class' => 'validate-number',
            'index'          => 'position',
            'editable'       => true,
            'filter'         => false,
            'edit_only'      => true,
            'sortable'       => false
        ));

        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
            ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name'
            ));

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            ));


        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
            ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('websites',
                array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                ));
        }



        //if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
        //    $this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
        //}

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }



    /* Set custom filter for in banner flag
     *
     * @param string $column
     * @return Enterprise_Banner_Block_Widget_Chooser
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {

                $this->getCollection()->getSelect()->where('e.entity_id IN (?)',$productIds);

            } else {
                if ($productIds) {
                    $this->getCollection()->getSelect()->where('e.entity_id NOT IN (?)',$productIds);
                }
            }
        }elseif ($column->getId() == 'websites') {
            $this->getCollection()->joinField('websites',
                'catalog/product_website',
                'website_id',
                'product_id=entity_id',
                null,
                'left');
            $this->getCollection()->getSelect()->where('website_id = '.$column->getFilter()->getValue());

        }
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    /**
     * Disable massaction functioanality
     *
     * @return Enterprise_Banner_Block_Widget_Chooser
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Adds additional parameter to URL for loading only products grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('productwidget/widget/grid', array('_current'=>true));
    }

    /**
     * Setter
     *
     * @param array $selectedproducts
     * @return Enterprise_Banner_Block_Widget_Chooser
     */
    public function setSelectedProducts($selectedProducts)
    {
        if (is_string($selectedProducts)) {
            $selectedProducts = explode(',', $selectedProducts);
        }
        $this->_selectedProducts = $selectedProducts;
        return $this;
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Set products' positions of saved products
     *
     * @return Enterprise_Banner_Block_Adminhtml_Widget_Chooser
     */
    protected function _prepareCollection()
    {

        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
        $collection->addStoreFilter($store);
        $collection->joinAttribute(
            'name',
            'catalog_product/name',
            'entity_id',
            null,
            'inner',
            $adminStore
        );
        $collection->joinAttribute(
            'custom_name',
            'catalog_product/name',
            'entity_id',
            null,
            'inner',
            $store->getId()
        );
        $collection->joinAttribute(
            'status',
            'catalog_product/status',
            'entity_id',
            null,
            'inner',
            $store->getId()
        );
        $collection->joinAttribute(
            'visibility',
            'catalog_product/visibility',
            'entity_id',
            null,
            'inner',
            $store->getId()
        );
        $collection->joinAttribute(
            'price',
            'catalog_product/price',
            'entity_id',
            null,
            'left',
            $store->getId()
        );

        $this->setCollection($collection);

        parent::_prepareCollection();

        $this->getCollection()->addWebsiteNamesToResult();



        foreach ($this->getCollection() as $item) {
            foreach ($this->getSelectedProducts() as $pos => $product) {
                if ($product == $item->getEntityId()) {
                    $item->setPosition($pos + 1);
                }
            }
        }
        return $this;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        if ($selectedProducts = $this->getRequest()->getParam('selected_products', $this->_selectedProducts)) {

            $this->setSelectedProducts($selectedProducts);
        } else {
            $this->setSelectedProducts(array());
        }
        return $this->_selectedProducts;
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $this->setId('productGrid');
    }
}
