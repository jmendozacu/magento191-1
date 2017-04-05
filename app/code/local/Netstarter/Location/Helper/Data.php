<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_Helper_Data
 */
class Netstarter_Location_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getLocation()
    {
        return Mage::registry('current_store');
    }

    public function getLocationUrl()
    {
        return rtrim($this->_getUrl('store'), '/');
    }

    /**
     * prepare breadcrumb for pages
     *
     * @return array
     */
    public function getBreadcrumbPath()
    {
        $path = array();

        $path['store_main'] = array(
            'label' => 'Store',
            'link' => ''
        );

        if($this->getLocation()){

            $path['store'] = array(
                'label' => $this->getLocation()->getName(),
                'link' => ''
            );
            $path['store_main']['link'] = Mage::getUrl().'store';
        }

        return $path;
    }

    /**
     * default location
     *
     * @return string
     */
    public function getBasePoint()
    {
        $baseLat   = Mage::getStoreConfig('storelocator_config/point/latitude');
        $baseLang   = Mage::getStoreConfig('storelocator_config/point/longitude');

        return json_encode(array('lat'=>$baseLat,'lang'=>$baseLang));
    }

    /**
     * get geo location from ip
     *
     * free service http://freegeoip.net/json
     *
     * site url : http://freegeoip.net/
     *
     * Github location : https://github.com/fiorix/freegeoip
     *
     * @return null
     */
    public function getIpGio()
    {
        $geo = null;

        try{

            $ip  = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
            $url = "http://freegeoip.net/json/$ip";
            $ch  = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $data = curl_exec($ch);
            curl_close($ch);

            if ($data) {

                $location = json_decode($data);
                if($location->latitude && $location->longitude){
                    $geo['latitude'] = $location->latitude;
                    $geo['longitude'] = $location->longitude;
                }
            }

        }catch (Exception $e){

            return $geo;
        }

        return $geo;
    }

    public function getFormattedSuburbPostCode($recId='') {
        if ($recId){
            $suburbLine = array();
            $suburb = Mage::getModel('location/postcode')->load($recId);
            if ($suburb instanceof Netstarter_Location_Model_Postcode) {
                $suburbLine[] = $suburb->getPostcode();
                $suburbLine[] = $suburb->getSuburb();
                $suburbLine[] = $suburb->getStatecode();
            }
            if ($suburbLine) {
                return implode(' ', $suburbLine);
            }
            return '';
        }
    }
}