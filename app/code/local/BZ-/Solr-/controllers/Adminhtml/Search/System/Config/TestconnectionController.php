<?php
/**
 * Class
 * test connection
 * @author ben zhang <ben_zhanghf@hotmail.com>
 */
class BZ_Solr_Adminhtml_Search_System_Config_TestconnectionController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check for connection to server
     */
    public function pingAction()
    {
        if (!isset($_REQUEST['host']) || !($host = $_REQUEST['host'])
            || !isset($_REQUEST['port']) || !($port = (int)$_REQUEST['port'])
            || !isset($_REQUEST['path']) || !($path = $_REQUEST['path'])
        ) {
            echo 0;
            die;
        }

        $pingUrl = 'http://' . $host . ':' . $port . '/' . $path . '/admin/ping';
        echo $pingUrl;
        if (isset($_REQUEST['timeout'])) {
            $timeout = (int)$_REQUEST['timeout'];
            if ($timeout < 0) {
                $timeout = -1;
            }
        } else {
            $timeout = 0;
        }

        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'HEAD',
                    'timeout' => $timeout
                )
            )
        );     
        // attempt a HEAD request to the solr ping page using curl as more secure
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_NOBODY => true,
            CURLOPT_URL => $pingUrl,
            CURLOPT_TIMEOUT => $timeout
        ));
        curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($statusCode == 200) echo 1;
        else echo 0;
    }
}
