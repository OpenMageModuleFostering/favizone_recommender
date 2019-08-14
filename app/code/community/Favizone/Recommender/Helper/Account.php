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
 *
 */
class  Favizone_Recommender_Helper_Account extends Mage_Core_Helper_Abstract
{

    /**
     * @param $store_id
     * @param $email
     * @return array
     * @throws Exception
     */
    public function sendAccountCreation($store_id, $email){
        try{

            //Preparing Account data
            $store = Mage::app()->getStore($store_id);
            $account_data = array();
            $account_data["email"] = $email;
            $account_data["cms_name"] = "magento";
            $cms_version = $this->getAccountVersion();
            $account_data["cms_version"] = $cms_version;
            $account_data["shop_identifier"] = $store->getId();
            $account_data["shop_url"] = $store->getUrl();
            $account_data["shop_name"] = $store->getFrontendName();
            $account_data["language"] =  substr(Mage::getStoreConfig('general/locale/code', $store->getId()),0,2);
            //default identifier, locale object is defined by code
            $account_data["language_identifier"] = 0;
            $account_data["timezone"] = Mage::getStoreConfig('general/locale/timezone',$store->getId());
            $country_code = Mage::getStoreConfig('general/country/default',$store->getId()); // Get the country code
            $country_name = Mage::getModel('directory/country')->loadByCode($country_code)->getName(); // Get the country object
            $account_data["country"] = $country_name;
            $currency_code = $store->getCurrentCurrencyCode();
            $currency_code = Mage::app()->getLocale()->currency( $currency_code )->getSymbol();
            $account_data["currency"] = "";
            if(isset($currency_code))
                $account_data["currency"] = $currency_code;

            $result =  $this->sendAccountData($account_data);
            if (array_key_exists("application_key",$result)){
                $application_key = $result['application_key'];
                //Preparing store  accessing parameters for save
                $access_key_model = Mage::getModel('favizone_recommender/accessKey');
                $access_key_model->setStoreId($store_id);
                $access_key_model->setAccessKey($application_key);
                $access_key_model->setAbDiff(0);
                $access_key_model->setAbTest('false');
                $access_key_model->save();
            }

            return  array("status"=>$result['status'] );
        }
        catch (Exception $exception){
            return  array("status"=>"error");
        }

    }


    protected function sendAccountData($account_data){

        $sender = Mage::helper('favizone_recommender/sender');
        $data = Mage::helper('favizone_recommender/data');
        $result = $sender->postRequest($data->getAccountUrl(), $account_data);
        return json_decode($result,true) ;
    }

    protected function getAccountVersion(){

        $cms_version = Mage::getVersion();

        if($cms_version>=1.6 && $cms_version<1.7)
            return "1.6";

        if($cms_version>=1.7 && $cms_version<1.8)
            return "1.7";

        if($cms_version>=1.8 && $cms_version<1.9)
            return "1.8";

        return "1.9";

    }
}