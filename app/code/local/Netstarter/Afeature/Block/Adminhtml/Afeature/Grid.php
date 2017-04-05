<?php
 
class Netstarter_Afeature_Block_Adminhtml_Afeature_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('afeatureGrid');
        // This is the primary key of the database
        $this->setDefaultSort('afeature_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('afeature/afeature')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $this->addColumn('afeature_id', array(
            'header'    => Mage::helper('afeature')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'afeature_id',
        ));
 
        $this->addColumn('title', array(
            'header'    => Mage::helper('afeature')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));
       
        $this->addColumn('active', array(

            'header'    => Mage::helper('afeature')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'active',
            'type'      => 'options',
            'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));
        ?>
<script>
    $j(document).ready(function()
    {
        $j('span:contains("CMS")').parent().parent().addClass('active');
    });
    </script>
<?php
 
        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
 
 
}