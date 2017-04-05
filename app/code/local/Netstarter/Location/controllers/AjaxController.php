<?php
/**
 * @author Prasad
 *
 * Class Netstarter_Location_AjaxController
 */
class Netstarter_Location_AjaxController extends Mage_Core_Controller_Front_Action
{

    private $_result = array (
        'query' => '',
        'suggestions' => array(),
        'data' => array(),
    );

    /**
     * suggestion / postcode or suburb call
     *
     * @return json
     */
    public function searchAction()
    {
        $query = trim($this->getRequest()->getParam('q'));

        $data = array();
        if($query){

            $data = Mage::getResourceModel('location/main')->getSuggestions($query);
        }

        $this->getResponse()->clearHeaders()->setBody(json_encode($data));

    }

    /*public function _searchByCountryAction()
    {
        $query = trim($this->getRequest()->getParam('q'));
        $countryCode = trim($this->getRequest()->getParam('c'));

        $data = array();
        if($query){

            $data = Mage::getResourceModel('location/main')->getSuggestions($query, $countryCode);
        }
        echo json_encode($data);
        exit;
    }*/

    public function searchbycountryAction()
    {
        $query = $this->getRequest()->getParam('query');

        $this->getResponse()->clearHeaders()->setBody($this->_makeAutocomplete($query));

    }


    protected function _makeAutocomplete($query)
    {
        $this->_result['query'] = $query;
        $countryCode = trim($this->getRequest()->getParam('c'));
        $query = trim(str_replace(' ', '', $query));
        $words = explode(' ', $query);

        if (count($words)) {

            $collection = Mage::getResourceModel('location/main');
            $resultCol = $collection->getSuggestions($query, $countryCode);

            $i = -1;
            if (count($resultCol) > 0) {
                $this->_result['data'] = array();
                foreach ($resultCol as $item) {

                    $suggestionItem = $item;
                    unset($suggestionItem['id']);

                    $i++;
                    $this->_result['suggestions'][$i] = implode(' ', $suggestionItem); // set the fulltext for the result
                    $this->_result['data'][$i][] = $item; // add items to data array
                }
            }
        }

        $core_helper = Mage::helper('core');
        if (method_exists($core_helper, "jsonEncode")) {
            $result = Mage::helper('core')->jsonEncode($this->_result);
        } else {
            $result = Zend_Json::encode($this->_result);
        }

        return $result;
    }


    /**
     * load location list
     */
    public function loadAction()
    {
        $query = trim($this->getRequest()->getParam('q'));
        $queryTxt = trim(str_replace(' ', '', $this->getRequest()->getParam('txt')));

        $data = array();

        $listBlock = $this->getLayout()->createBlock('location/stores', 'location.list', array('filter'=>$query, 'queryTxt' => $queryTxt));
        $listView = $listBlock->setTemplate('location/store_list.phtml')->toHtml();
        $locationText = $listBlock->getLocationText();
        $data['list'] = $listView;

        if($locationText) $data['text'] = $locationText;


        $this->getResponse()->clearHeaders()->setBody(json_encode($data));

    }

    /**
     * mobile use current location call
     */
    public function infoAction()
    {
        $lat = $this->getRequest()->getParam('lat');
        $lang = $this->getRequest()->getParam('lang');

        $data = array();

        if($lat && $lang){

            $listBlock = $this->getLayout()->createBlock('location/stores', 'location.list',
                            array('filter'=>array('latitude'=>$lat,'longitude'=>$lang)));

            $listView = $listBlock->setTemplate('location/store_list.phtml')->toHtml();

            $data['list'] = $listView;

        }

        $this->getResponse()->clearHeaders()->setBody(json_encode($data));

    }
}
