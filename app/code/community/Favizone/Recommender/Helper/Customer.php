<?php

/**
 * 2016 Favizone Solutions Ltd
 *
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com  for more information.
 *
 *
 * @category  Favizone
 * @package   Favizone_Recommender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
class  Favizone_Recommender_Helper_Customer extends Mage_Core_Helper_Abstract{

    /**
     * Sends the customer's data to Favizone
     *
     * @param $customer_identifier integer
     */
    public function sendCustomerData($customer_identifier, $store_id){

        $customer_meta = Mage::getModel('favizone_recommender/meta_customer');
        $customer_data = $customer_meta->loadCustomerData($customer_identifier);
        $common_helper = Mage::helper('favizone_recommender/common');
        $accessKey = $common_helper->getStoreAccessKey($store_id);
        $event = $common_helper->getTestingVersion($store_id).' ';
        $event.= $common_helper->getSessionIdentifier($store_id).' loginUser 1 1 1';

        ;
        $data_to_send =  array( "key"=> $accessKey,
                                "events" =>array($event),
                                "session"=>$common_helper->getSessionIdentifier(),
                                "custom_event_value"=> $customer_data);

        $sender = Mage::helper('favizone_recommender/sender');
        $data = Mage::helper('favizone_recommender/data');

        $sender->postRequest($data->getSendEventUrl(), $data_to_send);
    }
}