<?php
/**
 * Created by JetBrains PhpStorm.
 * User: suresh
 * Date: 3/22/13
 * Time: 12:29 PM
 * To change this template use File | Settings | File Templates.
 */
/**
 * Class Netstarter_Cdn_Block_Html_Head
 * This is override to add query string to js and css files.
 */
class Netstarter_Cdn_Block_Html_Head extends Mage_Page_Block_Html_Head
{

    /**
     * @param string $type
     * @param string $name
     * @param null $params
     * @param null $if
     * @param null $cond
     * @return $this|Mage_Page_Block_Html_Head
     */
    public function addItem($type, $name, $params=null, $if=null, $cond=null)
    {
        $versionNumber = Mage::helper('cdn')->getVersionNumber();
        $newName = $name;
        if ($versionNumber != '0') {
            switch ($type) {
                case 'js':
                    $newName = $newName . '?v='.$versionNumber;
                    break;
                case 'js_css':
                    $newName = $newName . '?v='.$versionNumber;
                    break;
                default:
                    $newName = $name;
                    break;
            }
        }

        if ($type==='skin_css' && empty($params)) {
            $params = 'media="all"';
        }
        $this->_data['items'][$type.'/'.$newName] = array(
            'type'   => $type,
            'name'   => $newName,
            'params' => $params,
            'if'     => $if,
            'cond'   => $cond,
        );
        return $this;
    }



}