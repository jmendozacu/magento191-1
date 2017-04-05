<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Model_Resource_Main
 */
class Netstarter_Location_Model_Resource_Main extends Mage_Core_Model_Resource_Db_Abstract
{

    protected $_results;

    public function _construct()
    {
        $this->_init('location/main', 'location_id');
    }

    public function getResults()
    {
        return $this->_results;
    }

    public function getConfigValues($path, $store = null, $isObject = false)
    {
        $config = Mage::getStoreConfig('storelocator_config/'.$path, $store);

        if($isObject){

            $obj = new Varien_Object();
            if($config){
                foreach ($config as $code => $value) {
                    $obj->setData($code, $value);
                }
            }
            return $obj;
        }

        return $config;
    }


    public function lookupStoreIds($id)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('location/web_store'), 'store_id')
            ->where('location_id = :location_id');

        $binds = array(
            ':location_id' => (int) $id
        );

        return $adapter->fetchCol($select, $binds);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if($object->getLocationId()){

            $condition = array('location_id = ?' => $object->getLocationId());

            $resourceTbl = $this->getTable('location/spatial_index');
            $this->_getWriteAdapter()->delete($resourceTbl, $condition);
            $data['location_id'] = $object->getLocationId();
            $geo = 'GEOMFROMTEXT(\'POINT('.($object->getLatitude()).'  '.($object->getLongitude()). ')\')';

            $stmt = "INSERT INTO {$resourceTbl} VALUES ({$object->getLocationId()}, $geo, {$object->getActive()});";

            $this->_getWriteAdapter()->query($stmt);


            $oldStores = $this->lookupStoreIds($object->getLocationId());
            $newStores = (array)$object->getStores();

            $table  = $this->getTable('location/web_store');
            $insert = array_diff($newStores, $oldStores);
            $delete = array_diff($oldStores, $newStores);

            if ($delete) {
                $where = array(
                    'location_id = ?'     => (int) $object->getLocationId(),
                    'store_id IN (?)' => $delete
                );

                $this->_getWriteAdapter()->delete($table, $where);
            }

            if ($insert) {
                $data = array();

                foreach ($insert as $storeId) {
                    $data[] = array(
                        'location_id'  => (int) $object->getLocationId(),
                        'store_id' => (int) $storeId
                    );
                }

                $this->_getWriteAdapter()->insertMultiple($table, $data);
            }

        }

        return parent::_afterSave($object);
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        if($object->getLocationId()){

            $condition = array('location_id = ?' => $object->getLocationId());
            $resourceTbl = $this->getTable('location/spatial_index');
            $this->_getWriteAdapter()->delete($resourceTbl, $condition);
        }

        return $this;
    }


    public function saveInfo($obj)
    {
        $resourceTbl = $this->getTable('location/info');

        $select = $this->_getReadAdapter()->select()
            ->from($resourceTbl)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('info_id')
            ->where('store_id = ?', $obj->getStoreId())
            ->limit(1);

        $hasValue = $this->_getReadAdapter()->fetchOne($select);

        $data = $this->_prepareDataForTable($obj, $resourceTbl);

        if($hasValue){
            $this->_getWriteAdapter()->update($resourceTbl, $data, "info_id = {$hasValue}" );
        }else{
            $this->_getWriteAdapter()->insert($resourceTbl, $data);
        }
    }

    public function getPostCodeData($id)
    {
        $read = $this->_getReadAdapter();

        $select = $read->select()
            ->from($this->getTable('location/postcode'))
            ->where('id =?', (int) $id);
        $data = $read->fetchRow($select);

        return $data;
    }

    public function countDistance($baseLat, $baseLang, $limit, $range = null)
    {
        $read = $this->_getReadAdapter();

        $select = $read->select()->from(array('idx' => $this->getTable('location/spatial_index')),
            array('distance' => "ROUND(GLENGTH(
                                    LINESTRINGFROMWKB(
                                      LINESTRING(
                                       GEOMFROMTEXT('POINT($baseLat $baseLang)'),
                                       `store_location`
                                      )
                                     )
                                    )*100, 2)"));
        $select->where('active = 1');
        $select->having('distance <= ?', (int) $range);
        $select->limit($limit);

        $data = $read->fetchAll($select);
        $count = (!empty($data))?count($data):0;

        return $count;
    }



    public function getDistance($baseLat, $baseLang, $limit, $range = null)
    {
        $read = $this->_getReadAdapter();

        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID, Mage::app()->getStore()->getId());

        $select = $read->select()->from(array('idx' => $this->getTable('location/spatial_index')),
            array('location_Id','distance' => "ROUND(GLENGTH(
                                    LINESTRINGFROMWKB(
                                      LINESTRING(
                                       GEOMFROMTEXT('POINT($baseLat $baseLang)'),
                                       `store_location`
                                      )
                                     )
                                    )*100, 2)"))->join(
            array('st' => $this->getTable('location/web_store')),
            'idx.location_id = st.location_id',
            array())->where('st.store_id IN (?)', $stores);
 	    $select->where('active = 1');
        if($range) $select->having('distance <= ?', (int) $range);
        $select->order('distance');
        $select->limit($limit);

        $data = $read->fetchAll($select);

        return $data;
    }

    public function getStoresByIds($stores)
    {
        $read = $this->_getReadAdapter();
        $storesStr = implode(',' ,$stores);

        $select = null;

        if($stores){

            $select = $read->select()->from(array('main' => $this->getMainTable()));
            $select->join(array('info' => $this->getTable('location/info')),'main.location_id = info.store_id', array('hours','address'));
            $select->where('active = 1 AND main.location_id IN (?)', $stores);
            $select->order(new Zend_Db_Expr("FIELD(main.location_id, $storesStr)"));
        }

        return $select;
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());

            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }


    public function loadInfo(Mage_Core_Model_Abstract $obj)
    {
        $read = $this->_getReadAdapter();
        $locId = $obj->getLocationId();

        $object = Mage::getModel('location/info');
        if(!is_null($locId)){

            $select = $read->select()
                ->from($this->getTable('location/info'))
                ->where('store_id = ?', $obj->getLocationId());

            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        return $object;
    }

    public function deleteInfo($obj)
    {
        if($obj->getLocationId()){

            $condition = array('store_id = ?' => $obj->getLocationId());
            $resourceTbl = $this->getTable('location/info');
            $this->_getWriteAdapter()->delete($resourceTbl, $condition);

            return true;
        }

        return false;
    }

    public function getSuggestions($key, $cntCode=null)
    {

       /* if (!$cntCode){
            $cntCode = Mage::app()->getStore()->getCode();
        }*/

        $postcodeTbl = $this->getTable('location/postcode');

		$conn = $this->getReadConnection();
		$select = $conn->select();

		$selectU = clone $select;
		$psSelect = $conn->select()->from(array('tb1'=>$postcodeTbl),array('id','postcode','suburb','statecode'))->limit(10);

        if($cntCode)
            $psSelect->where('countrycode = ?', $cntCode);

		$suSelect = clone $psSelect;

		$psSelect->where("postcode LIKE '{$key}%'");
		$suSelect->where("suburb LIKE '{$key}%'");

        $selectU->union(array($psSelect, $suSelect));

        $select->from($selectU)->limit(10);
        $this->_results = $this->_getReadAdapter()->fetchAll($select);

        if(!count($this->_results)){

            $fullTextSelect = $conn->select()->from(array('tb1'=>$postcodeTbl),array('id','postcode','suburb','statecode'))
                                ->where("fulltextcode LIKE '%{$key}%'")->limit(10);

            if($cntCode)
                $fullTextSelect->where('countrycode = ?', $cntCode);

            $this->_results = $this->_getReadAdapter()->fetchAll($fullTextSelect);
        }

        return  $this->_results;
    }


    public function checkIdentifier($identifier, $store, $isActive = null)
    {
        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID, $store);
        $select = $this->_getReadAdapter()->select()
            ->from(array('main' => $this->getMainTable()))
            ->join(
            array('st' => $this->getTable('location/web_store')),
            'main.location_id = st.location_id',
            array())
            ->where('main.identifier = ?', $identifier)
            ->where('st.store_id IN (?)', $stores);

        if (!is_null($isActive)) {
            $select->where('main.active = ?', $isActive);
        }

        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns('main.location_id')
            ->order('main.location_id DESC')
            ->limit(1);

        return $this->_getReadAdapter()->fetchOne($select);

    }

    public function getStateShortCode($countryCode, $state) {
        if (!$state) {
            return false;
        }

        $postcodeTbl = $this->getTable('location/postcode');

        $conn = $this->getReadConnection();

        $select = $conn->select()
            ->from(array('tb1'=>$postcodeTbl), array('statecode'))
            ->where('countrycode = ?', trim($countryCode))
            ->where('state = ?', trim($state))
            ->limit(1);

        $this->_results = $this->_getReadAdapter()->fetchOne($select);
        return $this->_results;
    }
}
