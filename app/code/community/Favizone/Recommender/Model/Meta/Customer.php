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
class Favizone_Recommender_Model_Meta_Customer extends Mage_Core_Model_Abstract{

    /**
     * return the given customer data
     *
     * @param $customer_identifier integer
     * @return object
    */
    public function loadCustomerData($customer_identifier){
        try{

            $store = Mage::app()->getStore();
            $customer_data = array();
            $customer = Mage::getModel('customer/customer')->load($customer_identifier);

            $customer_data['id'] = $customer_identifier;
            $customer_data['email'] = $customer->getEmail();
            $customer_data['session_id'] = Mage::helper('favizone_recommender/common')->getSessionIdentifier();
            $customer_data['firstname'] = $customer->getFirstname();
            $customer_data['lastname'] = $customer->getLastname();
            $customer_data['gender'] = 'h';
            if( $customer->getGender() == 2)
                $customer_data['gender'] = 'f';
            $customer_data['country'] = "";
            if( $customer->getDefaultBillingAddress())
                $customer_data['country'] =  $customer->getDefaultBillingAddress()->getCountry();
            $customer_data['currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();

            //Languages
            $customer_data['languages'] = array(substr(Mage::getStoreConfig('general/locale/code', $store->getId()),0,2));

            return $customer_data;
        }
        catch (Exception $e) {
            return  array();
        }
    }
}