<?php
/**
 * Class that imports images.
 *
 * @category  Netstarter
 * @package   Netstarter_Importimages
 *
 * Class Netstarter_Importimages_Model_Observer
 */
class Netstarter_Importimages_Model_Observer extends Netstarter_Shelltools_Model_Shared_Abstract
{
    const IMAGE_NAME_SEPARATOR  = '_';
    const IMAGE_EXT_SEPARATOR   = '.';
    const FOLDER_UNPROCESSED    = 'unprocessed/';
    const FOLDER_PROCESSED      = 'processed/';
    
    protected $_jobId           = 'IMAGE_IMPORT';
    
    protected $_countForReport  = array(
        'success'   => 0,
        'error'     => 0,
    ); 

    protected $_mimeTable = array(
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
    );

    /**
     * Path to current PHP script to be run
     *
     * @var string
     */
    protected $_pathToFile = null;

    /**
     * Product Image Api Model
     *
     * @var Mage_Catalog_Model_Product_Attribute_Media_Api
     */
    protected $_productMediaApiModel = null;

    /**
     * List of store Ids we need to update attributes on
     *
     * @var array
     */
    protected $_storeIds = null;

    /**
     * Is subprocess mode. Used to avoid memory usage since
     * images need to be loaded into memory.
     *
     * @var bool
     */
    protected $_isSubMode = false;

    /**
     * List of store Ids within current website Id
     * We need to set attributes for each store for each product
     *
     * @return array
     */
    protected function _getStoreIds()
    {
        if ($this->_storeIds == null)
        {
            $this->_storeIds = array();

            foreach (Mage::app()->getWebsites() as $website)
            {
                $this->_storeIds += $website->getStoreIds();
            }
        }
        return $this->_storeIds;
    }

    /**
     * Set script path for further PHP subprocess initiation
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->_pathToFile = $path;
    }

    /**
     * Retrieve script path for further PHP subprocess initiation
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_pathToFile;
    }

    /**
     * Gets Product Model
     *
     * @return Mage_Catalog_Model_Product_Attribute_Media_Api
     */
    protected function _getProductMediaApiModel()
    {
        if ($this->_productMediaApiModel == null)
        {
            $this->_productMediaApiModel = Mage::getModel("catalog/product_attribute_media_api");
        }
        return $this->_productMediaApiModel;
    }

    /**
     * Set if should use PHP subprocess for each image
     *
     * @param string $isSubMode
     */
    public function setIsSubprocessMode($isSubMode)
    {
        $this->_isSubMode = $isSubMode;
    }

    /**
     * Get if should use PHP subprocess for each image
     *
     * @return string
     */
    public function getIsSubprocessMode()
    {
        return $this->_isSubMode;
    }

    /**
     * Retrieves a Image for a product
     * Updates current images (delete all old ones)
     *
     * @param  array $itemData
     */
    public function retrieveImage($itemData, $shell = false)
    {
        // return types:
        $success = array ("result" => 1, "message" => "");
        $failure = array ("result" => -1, "message" => "Failure");
        
        /*
         * If it was called by a shell context we need to unserialize item data.
         *
         * This also means we are locked in separate process of the original run. All the information we need for the update
         * should be provided within the $itemData serialized array (context data).
         */

        $itemData = $shell ? unserialize($itemData) : $itemData;
        $filename = $itemData['file']['content'];
        
        $itemData['file']['content'] = base64_encode(file_get_contents( $itemData['file']['content'] ));

        $updateImage = array(
            'types' => $itemData['types'],
            'exclude' => 0
        );

        $sku = $itemData['sku'];
        unset ($itemData['sku']);
        
        try
        {
            //remove all previous images in every store
            $items = $this->_getProductMediaApiModel()->items($sku, null, 'sku');
            foreach($items as $item)
            {
                if (strpos($item['file'], $itemData['file']['name']) !== false)
                {
                    try
                    {
                        $this->_getProductMediaApiModel()->currentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                        $this->_getProductMediaApiModel()->remove($sku, $item['file'], 'sku');
                    }
                    catch (Exception $exc)
                    {
                        // don't bubble exception, continue execution
                        $this->_log(array("No media to remove", $sku, $item['url']), Zend_Log::ERR);
                    }
                }
            }

            $this->_getProductMediaApiModel()->currentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

            // create new image
            $imageFilename = $this->_getProductMediaApiModel()->create($sku, $itemData, Mage_Core_Model_App::ADMIN_STORE_ID, 'sku');
           
            return $success;
        }
        catch (Exception $exc)
        {
            $failure["message"] = $exc->getMessage() . " on $sku for image $filename";
            return $failure;
        }
        
        return $success;
    }

    private function _getUnprocessedPath()
    {
        $path = Mage::getStoreConfig('netstarter_importimages/general/base_folder');
        return $path . self::FOLDER_UNPROCESSED;
    }

    private function _getProcessedPath()
    {
        $path = Mage::getStoreConfig('netstarter_importimages/general/base_folder');
        return $path . self::FOLDER_PROCESSED;
    }

    /**
     * Starts external PHP process for image import
     *
     * @param  array $itemData
     * @param  bool $shell
     * @return string
     */
    protected function _runSingleSku ($itemData, $shell = false)
    {
        if ($shell || $this->getIsSubprocessMode())
        {
            if ($this->getPath() !== null)
            {
                $cmd = "php -f {$this->getPath()} -- update --mode specific --context " . escapeshellarg(serialize($itemData)) . " --logfilename " . $this->_logModel->getFilename();
            }
            
            $result = unserialize(shell_exec($cmd));
            
            if (!array_key_exists("result", $result) || !array_key_exists("message", $result))
            {
                $result = array("result" => "-1", "message" => "Error on subprocess");
            }

            return $result;
        }
        else
        {
            /*
             * Regular non-shell way.
             * Can consume more memory.
             */
            return $this->retrieveImage($itemData);
        }
    }

    public function readImagesAndUpdate()
    {
        $path = $this->_getUnprocessedPath();
        $processedPath = $this->_getProcessedPath();
        
        $getProcessedAction = Mage::getStoreConfig('netstarter_importimages/general/delete');

        // using Standard PHP Library
        foreach (new DirectoryIterator($path) as $fileInfo)
        {
            try
            {
                if($fileInfo->isDot()) continue;

                $filename               = $fileInfo->getFilename();
                $filenameNoExtension    = substr($fileInfo->getBasename(), 0,
                                                strpos($fileInfo->getBasename(),self::IMAGE_EXT_SEPARATOR));
                $position               = strstr(
                                            substr(strstr($filename, self::IMAGE_NAME_SEPARATOR),1),
                                            self::IMAGE_EXT_SEPARATOR,
                                            true);
                $sku                    = strstr($filename, self::IMAGE_NAME_SEPARATOR, true);

                $itemData = array(
                    'file' => array(
                        'name' => $filenameNoExtension,
                        'content' => $path.$filename,
                        'mime' => $this->_mimeTable[pathinfo($fileInfo->getFilename(), PATHINFO_EXTENSION)]
                    ),
                    'label' => '',
                    'position' => $position,
                    'types' => $position == 1 ? array('small_image', 'image', 'thumbnail') : array(),
                    'exclude' => 0,
                    'sku' => $sku,
                );
                
                /*
                 * Todo, make logging work on -- allsub
                 */
                $return = $this->_runSingleSku ($itemData);
                
                if ($return["result"] > 0)
                {
                    // change the constant for different behaviours
                    // needs redeploy :(
                    if ($getProcessedAction == Netstarter_Importimages_Model_System_Config_Source_Fileaction::MOVE)
                    {
                        rename($path.$filename, $processedPath.$filename);
                    }
                    else if ($getProcessedAction == Netstarter_Importimages_Model_System_Config_Source_Fileaction::DELETE)
                    {
                        unlink($path.$filename);
                    }
                    
                    $this->_countForReport['success']++;
                    $this->_log("$filename imported into product $sku successfully");
                }
                else
                {
                    $this->_countForReport['error']++;
                    $this->_log(array("ERROR", $return["message"]), Zend_Log::ERR);
                }
            }
            catch (Exception $exc)
            {
                $this->_countForReport['error']++;
                $this->_log(array("ERROR", $exc->getMessage()), Zend_Log::ERR);
            }
        }
    }

    protected function _update()
    {
        $this->_log("Starting image importing");
        $this->readImagesAndUpdate();
        
        $this->_log(array(
            $this->_jobId,
            "Finish image importing with successes:",
            $this->_countForReport['success'],
            "and errors:",
            $this->_countForReport['error']
        ));
    }
    
    public function setLogFilename($filename = null)
    {
        if ($this->_logModel == null)
        {
            $this->_initLogging();
            $this->_logModel->destructWithoutEmail(true);
            if ($filename != null)
            {
                $this->_logModel->setFilename($filename);
            }
            else
            {
                throw Exception("Filename expected when starting subprocess");
            }
        }
    }
}