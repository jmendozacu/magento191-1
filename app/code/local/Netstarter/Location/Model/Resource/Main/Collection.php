<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Model_Resource_Main_Collection
 */
class Netstarter_Location_Model_Resource_Main_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    const LOCATION_DEFAULT_RANGE = 40;

    const LOCATION_DEFAULT_LIMIT = 25;

    const LOCATION_MINIMUM_LIMIT = 5;

    protected $_distances;



    public function _construct() {

        parent::_construct();
        $this->_init('location/main');
    }

    public function getDistances()
    {
        return $this->_distances;
    }

    /**
     * Range calculation
     *
     * 1) check with 40 km radius limit 25
     * 2) id count >5, return
     * 3) else get locations without radius, minimum 5
     *
     * @param null $filter
     * @return $this
     */
    public function getStoresInRange($filter = null)
    {
        if($filter){

            if(is_array($filter)){

                $baseLat = $filter['latitude'];
                $baseLang = $filter['longitude'];
            }else{
                $data = $this->getResource()->getPostCodeData($filter);
                if ($data) {
                    $baseLat = $data['latitude'];
                    $baseLang = $data['longitude'];
                }
            }
        }else{

            $baseLat   = Mage::getStoreConfig('storelocator_config/point/latitude');
            $baseLang   = Mage::getStoreConfig('storelocator_config/point/longitude');
        }

        $configLimit   = Mage::getStoreConfig('storelocator_config/limit/store_limit');
        $configRange   = Mage::getStoreConfig('storelocator_config/limit/range');

        $limit = (!empty($configLimit))? $configLimit:self::LOCATION_DEFAULT_LIMIT;

        $range = (!empty($configRange))? $configRange:self::LOCATION_DEFAULT_RANGE;

//       $count = $this->getResource()->getDistance($baseLat, $baseLang, $limit, $range);
        $objs = $this->getResource()->getDistance($baseLat, $baseLang, $limit, $range);

        if(count($objs) <= self::LOCATION_MINIMUM_LIMIT) {

            $limit = self::LOCATION_MINIMUM_LIMIT;
            $range = null;

            $objs = $this->getResource()->getDistance($baseLat, $baseLang, $limit, $range);
        }

        $objIds = array();

        foreach($objs as $obj){

            $objIds[] = (int) $obj['location_Id'];
            $this->_distances[$obj['location_Id']] = $obj['distance'];
        }

        $this->_setIsLoaded = true;

        if($objIds){

            $select = $this->getResource()->getStoresByIds($objIds);

            if($select instanceof Varien_Db_Select){

                $this->_setIsLoaded = false;
                $this->_select = $select;
            }
        }

        return $this;

    }

}
