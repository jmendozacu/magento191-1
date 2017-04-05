<?php
/**
 * Directory Country Resource Collection
 * Netstarter_Modulerewrites_Model_Directory_Resource_Country_Collection
 *
 * @category    code
 * @package     Netstarter_Modulerewrites
 * @copyright   www.netstarter.com.au
 * @license     www.netstarter.com.au
 */
class Netstarter_Modulerewrites_Model_Directory_Resource_Country_Collection extends Mage_Directory_Model_Resource_Country_Collection
{
    /**
     * Convert collection items to select options array
     * Overidden from Mage_Directory_Model_Resource_Country_Collection
     *
     * @param string $emptyLabel
     * @return array
     */
    public function toOptionArray($emptyLabel = ' ')
    {
        $options = $this->_toOptionArray('country_id', 'name', array('title'=>'iso2_code'));

        $sort = array();
        foreach ($options as $data) {
            $name = Mage::app()->getLocale()->getCountryTranslation($data['value']);
            if (!empty($name)) {
                $sort[$name] = $data['value'];
            }
        }

        Mage::helper('core/string')->ksortMultibyte($sort);
        $options = array();
        $optionsPriority = array();
        foreach ($sort as $label=>$value) {

            if ($value == 'AU' || $value == 'NZ') {
                $optionsPriority[] = array(
                    'value' => $value,
                    'label' => $label
                );
            } else {
                $options[] = array(
                    'value' => $value,
                    'label' => $label
                );
            }
        }

        $options = array_merge($optionsPriority, $options);
        /*
        if (count($options) > 0 && $emptyLabel !== false) {
            array_unshift($options, array('value' => '', 'label' => $emptyLabel));
        }
        */
        return $options;
    }
}
