<?php

class Netstarter_Modulerewrites_Model_Captcha_Zend extends Mage_Captcha_Model_Zend
{
    /**
     * Number of noise dots on image
     * Used twice - before and after transform
     *
     * @var int
     */
    protected $_dotNoiseLevel = 15;

    /**
     * Number of noise lines on image
     * Used twice - before and after transform
     *
     * @var int
     */
    protected $_lineNoiseLevel = 2;
}
