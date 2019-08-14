<?php

/**
 * 2016 Favizone Solutions Ltd
 *
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com  for more information.
 *
 * Favizone Admin block
 *
 * @category  Favizone
 * @package   Favizone_Recommender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */

class Favizone_Recommender_Block_Adminhtml_AdminConfig extends Mage_Adminhtml_Block_Template

{

    public function  _construct(){

    }

    /**
     * Returns the A/B testing configuration value
     *
     * @return String
     */
    public function getAbTest()
    {
        $accessKey = Mage::getModel('favizone_recommender/accessKey');
        return $accessKey->getCollection()->getFirstItem()->getAbTest();

    }

    /**
     * Returns the accessKey value if inserted
     *
     * @return String
     */
    public function getAccessKey()
    {

        $accessKey = Mage::getModel('favizone_recommender/accessKey');
        return $accessKey->getCollection();
    }

    /**
     * Checks if the access key is loaded
     *
     * @return Boolean
     */
    public function isAccessKeyLoaded()
    {
        $store = Favizone_Recommender_Block_Common::getSelectedStore();
        if(isset($store)){
            $store_data = Mage::getModel('favizone_recommender/accessKey')->load($store->getId(), 'store_id');
            return (!empty($store_data->getData()));
        }
        return false;
    }
}