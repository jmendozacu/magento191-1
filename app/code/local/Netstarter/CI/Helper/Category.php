<?php
/**
 * Created by JetBrains PhpStorm.
 * User: prasad
 * Date: 9/9/13
 * Time: 12:01 PM
 * To change this template use File | Settings | File Templates.
 */
class Netstarter_CI_Helper_Category
{

    public $_categoryTree;
    public $_connection;
    public $_updateFile;
    public $_parentCats;


    public function __construct()
    {
        $this->_connection      = Mage::getSingleton('core/resource')->getConnection('write');
        $this->_updateFile = 'cat_upd' . $importFile = Mage::getModel('core/date')->date('Ymd') . '.sql';
    }

    public function getTree($parentId)
    {
        $allCats = Mage::getModel('catalog/category')->getCollection()
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToFilter('parent_id',array('eq' => $parentId));

        return $allCats;
    }

    public function getCategoryTree()
    {

        $result = $this->_connection->query("SELECT entity_id  FROM `catalog_category_entity` e WHERE parent_id = 1");
        $this->_parentCats[79] = 79;
        $this->_parentCats[80] = 80;

        foreach($result->fetchAll() as $row){

            $allCats = $this->getTree($row['entity_id']);

            foreach($allCats as $cat){

                $childL1 = $this->getTree($cat->getEntityId());

                foreach($childL1 as $catL1){

                    $catId = (int)$cat->getEntityId();
                    $this->_categoryTree[(int)$catL1->getEntityId()] = $catId;
                    $this->_parentCats[$catId] = $catId;
                }
            }
        }
    }


    public function sync()
    {

        $this->getCategoryTree();
        $_parentCatsStr = implode(',', $this->_parentCats);


        $result = $this->_connection->query("SELECT e.entity_id, category_id, p.position  FROM `catalog_product_entity` e
        JOIN`catalog_category_product` p ON e.entity_id = p.product_id
        JOIN `catalog_product_entity_int` i ON e.entity_id = i.entity_id
        JOIN `catalog_category_entity` c ON p.category_id = c.entity_id
        WHERE p.category_id NOT IN ($_parentCatsStr) AND i. attribute_id = 102 AND  i.value IN (4,3,2)
        GROUP BY e.entity_id, c.parent_id");

        if ($result !== false){

            foreach($result->fetchAll() as $row){

                $categoryId = (int)$row['category_id'];
                $entityId = (int)$row['entity_id'];
                $position = 1;

                if(isset($this->_categoryTree[$categoryId])){

                    $parentId = $this->_categoryTree[$categoryId];

                    if($parentId == 10 || $parentId == 81) continue;

                    $resultCheck = $this->_connection->query("SELECT category_id, position FROM `catalog_category_product` p
                                    WHERE product_id = $entityId AND category_id = $parentId");

                    echo "$entityId\n";
                    if ($resultCheck !== false && $categoryProducts = $resultCheck->fetch(Zend_Db::FETCH_ASSOC)){

//                        $position = (!empty($categoryProducts['position'])?(int)$categoryProducts['position']:$position);
//                        exec("echo \"UPDATE catalog_category_product SET position = $position WHERE category_id = $parentId AND product_id = $entityId;\" >> " . Mage::getBaseDir().'/var/ci/unprocessed/'. $this->_updateFile);
                    }else{
                        exec("echo \"INSERT INTO catalog_category_product VALUES ($parentId, $entityId,$position);\" >> " . Mage::getBaseDir().'/var/ci/unprocessed/'. $this->_updateFile);
                    }
                }
            }

            $fileToInsert = Mage::getBaseDir().'/var/ci/unprocessed/'. $this->_updateFile;

            if(file_exists($fileToInsert)){

                $host = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/host');
                $username = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/username');
                $password = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/password');
                $dbname = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');


                echo("---------- UPDATE COMPLETE DOING QUERY ----- \n");

                $return = array();

                exec("mysql -h $host -u $username --password=\"$password\" $dbname < " . $fileToInsert, $return);
                exec('mv ' . $fileToInsert . ' ' . Mage::getBaseDir().'/var/ci/processed/');

//                echo("---------- UPDATE COMPLETE DOING INDEX----- \n");
//
//                exec('php ' . Mage::getBaseDir() . DS . 'shell' . DS . 'indexer.php --reindex catalog_category_product', $return);
//
//                Mage::app()->getCacheInstance()->cleanType('block_html');
//                Mage::app()->getCacheInstance()->cleanType('full_page');
            }
        }
    }
}