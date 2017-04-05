<?php
/**
 * Static Blocks needed for Bra Finder
 */

$content = <<<EOF
    <h2>Your Measurements are only a guide <br/> but the essential starting point</h2>
    <span class="wizard-step-content-line"></span>
    <p class="wizard-step-content">
        This is a test contet for wizard page
    </p>
    <span class="wizard-step-content-line"></span>
EOF;
$cmsPageData = array(
    'title' => 'Bra Finder Wizard - Size',
    'identifier' => 'bra-finder-wizard-size',
    'content_heading' => 'Bra Fitter - Size',
    'stores' => array(0),//available for all store views
    'content' => "$content"
);

Mage::getModel('cms/block')->setData($cmsPageData)->save();



$content = <<<EOF
    <h2>Thank You</h2>
    <span class="wizard-step-content-line"></span>
    <p class="wizard-step-content">
        Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum Lorem ipsum
    </p>
    <span class="wizard-step-content-line"></span>
    <h2>10% Off Coupon!<span>*</span></h2>
EOF;
$cmsPageData = array(
    'title' => 'Thank You',
    'identifier' => 'bra-finder-wizard-getemail',
    'content_heading' => 'Bra Fitter - Get Email',
    'stores' => array(0),//available for all store views
    'content' => "$content"
);


Mage::getModel('cms/block')->setData($cmsPageData)->save();


$content = <<<EOF
    <h2>Success</h2>
    <p class="wizard-step-success">
        We think your feel will like these, But remember, for the ultimate fit from
        one of our qualified fit technicians, please visit one of our 140 stores nationwide
    </p>
EOF;
$cmsPageData = array(
    'title' => 'Success',
    'identifier' => 'bra-finder-wizard-success',
    'content_heading' => 'Bra Fitter - Success',
    'stores' => array(0),//available for all store views
    'content' => "$content"
);


Mage::getModel('cms/block')->setData($cmsPageData)->save();