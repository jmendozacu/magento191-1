<?php
/**
 * Class Weight
 *
 * @author bzhang@netstarter.com.au
 */
class BZ_Solr_Model_Weight
{
    /**
     * Quick search weights
     *
     * @var array
     */
    static $weights = array(
        1,
        2,
        3,
        4,
        5
    );

    static public function getOptions()
    {
        $res = array();
        foreach (self::getValues() as $value) {
            $res[] = array(
               'value' => $value,
               'label' => $value
            );
        }
        return $res;
    }

    static public function getValues()
    {
        return self::$weights;
    }
}