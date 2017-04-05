<?php

class Plumrocket_Amp_Model_Plumrocket_FooterJs_Observer extends Plumrocket_FooterJs_Model_Observer
{
    public function esponseSendBefore($observer)
    {
        $request = Mage::app()->getRequest();

        if($request->getParam('amp') == 1) {
        	return;
        }

        if ($request->isXmlHttpRequest()) {
            return true;
        }

        $response = $observer->getResponse();
        $html = $response->getBody();

        $changed = false;
        $jsHtml = '';

        $isEsi = ($request->getActionName() == 'getBlock');

        $key = md5($request->getParam('hmac'));

        //foreach(array('#<\!--\[if[^\>]*>\s*<script.*</script>\s*<\!\[endif\]-->#isU', '#<script.*</script>#isU') as $pattern) {
        foreach(array('#<\!--\[if[^\>]*>\s*<script.*</script>\s*<\!\[endif\]-->#isU', '#<script((?!nofooterjs).)*>.*</script>#isU') as $pattern) {
            $matches = array();
            $success = preg_match_all($pattern, $html, $matches);
            if ($success) {

                $_jsHtml = implode('', $matches[0]);

                $html = preg_replace(array($pattern, "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/"), array('',"\n") , $html);
                $jsHtml .= $_jsHtml;

                $changed = true;
            }
        }

        if ($changed) {

            if ($isEsi) {
                $jsHtml = '<script type="text/javascript">if(typeof pfj_js=="undefined")pfj_js={};pfj_js["'.$key.'"]='.json_encode($jsHtml).'</script>';
                $html .= $jsHtml;
            } else {
                $jsHtml = '<script type="text/javascript">
                    function pfjRun(key){
                        if (typeof pfj_js[key]!=="undefined"){
                            pjQuery_1_10_2("body").append(pfj_js[key]);
                        } else {
                        }
                    }
                </script>' . $jsHtml;


                $bodyEnd = strrpos($html, '</body>');
                if ($bodyEnd) {
                    $html = substr($html, 0, $bodyEnd).$jsHtml.substr($html,$bodyEnd);
                } else {
                    $html .= $jsHtml;
                }
            }

            $response->setBody($html);
        }


    }

    public function getJs($observer)
    {
        if (!$observer) {
            return;
        }

        if (!($block = $observer->getBlock())) {
            return;
        }

        if (($esiUrl = $block->getEsiUrl()) && $block->getTemplate() == 'turpentine/esi.phtml' && Mage::app()->getRequest()->getParam('amp') != 1) {

            $matches = array();
            preg_match("/[\w+]\/hmac\/(\w+)\/[\w*]/", $esiUrl, $matches);
            if (!empty($matches[1])) {
                $key = md5($matches[1]);
                $transport = $observer->getTransport();
                $html = $transport->getHtml().'<script type="text/javascript">pfjRun("'.$key.'");</script>';
                $transport->setHtml($html);
            }
        }

    }

}