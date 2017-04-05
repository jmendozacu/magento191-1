<?php
/**
 * Mobile Detect
*
* @license    http://www.opensource.org/licenses/mit-license.php The MIT License
* @version    SVN: $Id: Mobile_Detect.php 3 2009-05-21 13:06:28Z vic.stanciu $
* @version    SVN: $Id: Mobile_Detect.php 3 2011-04-19 18:44:28Z sjevsejev $
*/
/**
 * @category   Netstarter
 * @package    Netstarter_Mobile
 * @author     M. G. N. Dhanushka <gdhanushka@netstarter.com>
 */
class Netstarter_Mobile_Helper_Detect extends Mage_Core_Helper_Abstract
{
	protected static $_isMobile     = null;
    protected static $_isTouch      = null;


    protected $_devices = array(
			"android"       => "android",
			"blackberry"    => "blackberry",
			"iphone"        => "(iphone|ipod)",
			"opera"         => "opera mini",
			"palm"          => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
			"windows"       => "windows ce; (iemobile|ppc|smartphone)",
			"generic"       => "(kindle|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap)"
	);

    /* setting up a cookie to SLI search to get the current store */
    public function __construct()
    {

        try{

            if(isset($_SERVER['HTTP_HOST'])){

                $storeCode = Mage::app()->getStore()->getCode();
                $currency = ($storeCode == "nz")?"NZD":"AUD";
                $expire = time()+946080000;
                $domain = $_SERVER['HTTP_HOST'];
                $domain = str_replace("www.",'',$domain);

                setcookie("sli_cookie[Name]", "sli_data", $expire);
                setcookie("sli_cookie[Currency]", $currency, $expire);
                setcookie("sli_cookie[Domain]", "." . $domain, $expire);
                setcookie("sli_cookie[Path]", "/", $expire);

                $cartQuote = Mage::getSingleton('checkout/session')->getQuote();

                if($cartQuote){
                    $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals(); //Total object
                    $grandTotal = $totals["grand_total"]->getValue();
                    $totalItems = $cartQuote->getItemsQty();
                    $grandTotal = number_format($grandTotal, 2);
                }

                if($totalItems > 0){

                    setcookie("SLICARTCOUNT[Domain]", "." . $domain, 0);
                    setcookie("SLICARTCOUNT[Qty]", $totalItems, 0);
                    setcookie("SLICARTCOUNT[Subtotal]", $grandTotal, 0);
                }
            }

        }catch (Exception $e){

            Mage::logException($e);
        }
    }


	/**
	 * Returns true if any type of mobile device detected, including special ones
	 * @return bool
	 */
	public function isMobile()
    {

        if(isset(self::$_isMobile)){

            return self::$_isMobile;
        }else{

            self::$_isMobile = false;
            self::$_isMobile = $this->isDevice($this->_devices);

            return self::$_isMobile;
        }
	}


    /**
     * Returns true if any type of touch device detected, including special ones
     * @return bool
     */
    public function isTouch()
    {

        if(isset(self::$_isTouch)){

            return self::$_isTouch;
        }else{

            self::$_isTouch = false;

            if(self::$_isMobile){

                self::$_isTouch = true;
            }else{
                $devicesClone = $this->_devices;
                $devicesClone['ipad'] = 'ipad';

                self::$_isTouch = $this->isDevice($devicesClone);
            }

            return self::$_isTouch;
        }
    }

	protected function isDevice($devices)
    {
        $userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

        if($userAgent){

            foreach ($devices as $regexp) {

                $return = ((bool) preg_match("/{$regexp}/i", $userAgent));

                if ($return) {

                    return true;
                    break;
                }
            }
        }

        return false;
	}
}
