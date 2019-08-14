<?php
class Favizone_Recommender_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * @inheritdoc
     */
    public function isEnabledFlat()
    {
        // Never use the flat collection.
        return false;
    }
}