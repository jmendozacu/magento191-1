<?php

class Netstarter_TargetRuleFix_Model_Resource_Index extends Enterprise_TargetRule_Model_Resource_Index
{

   private $_segments = array();

   public function saveProductIndex($ruleId, $productId, $storeId)
    {
        /** @var $targetRule Enterprise_TargetRule_Model_Resource_Rule */
        $targetRule = Mage::getResourceSingleton('targetrulefix/rule');
        //$targetRule->bindRuleToEntity($ruleId, $productId, 'product');
        $targetRule->bindRuleToEntity($ruleId, $productId, 'product', false); //@carco: copied from EE 1.13


        return $this;
    }

    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * Retrieve product Ids
     *
     * @param Enterprise_TargetRule_Model_Index $object
     * @return array
     */
    public function getProductIds($object)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'customer_segment_id')
            ->where('type_id = :type_id')
            ->where('entity_id = :entity_id')
            ->where('store_id = :store_id')
            ->where('customer_group_id = :customer_group_id');

        $rotationMode = Mage::helper('enterprise_targetrule')->getRotationMode($object->getType());
        if ($rotationMode == Enterprise_TargetRule_Model_Rule::ROTATION_SHUFFLE) {
            $this->orderRand($select);
        }

        $segmentsIds = array_merge(array(0), $this->_getSegmentsIdsFromCurrentCustomer());
        $bind = array(
            ':type_id'              => $object->getType(),
            ':entity_id'            => $object->getProduct()->getEntityId(),
            ':store_id'             => $object->getStoreId(),
            ':customer_group_id'    => $object->getCustomerGroupId()
        );

        $segmentsList = $adapter->fetchAll($select, $bind);

        $foundSegmentIndexes = array();
        foreach ($segmentsList as $segment) {
            $foundSegmentIndexes[] = $segment['customer_segment_id'];
        }

        $productIds = array();
        foreach ($segmentsIds as $segmentId) {
            if (in_array($segmentId, $foundSegmentIndexes)) {

                if($segmentId)
                    $this->_segments[] = $segmentId;

                $productIds = array_merge($productIds,
                    $this->getTypeIndex($object->getType())->loadProductIdsBySegmentId($object, $segmentId));
            } else {
                $matchedProductIds = $this->_matchProductIdsBySegmentId($object, $segmentId);
                $productIds = array_merge($matchedProductIds, $productIds);
                $this->getTypeIndex($object->getType())
                    ->saveResultForCustomerSegments($object, $segmentId, implode(',', $matchedProductIds));
                $this->saveFlag($object, $segmentId);
            }
        }
        $productIds = array_diff(array_unique($productIds), $object->getExcludeProductIds());

        if ($rotationMode == Enterprise_TargetRule_Model_Rule::ROTATION_SHUFFLE) {
            shuffle($productIds);
        }

        return array_slice($productIds, 0, $object->getLimit());
    }

}
