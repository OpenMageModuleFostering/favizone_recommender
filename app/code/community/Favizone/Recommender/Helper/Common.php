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
class  Favizone_Recommender_Helper_Common extends Mage_Core_Helper_Abstract
{
    private $default_v = "N";
    private $current_v;

    /**
     * Returns the connection identifier
     *
     * @return String
     */
    public function getSessionIdentifier($store_id = null){

        $cookie = Mage::getModel('core/cookie');
        if(is_null($store_id)){
            $store = Mage::app()->getStore();
            $store_id = $store->getId();
        }

        if($cookie->get('favizone_connection_identifier_'.$store_id) && !empty($cookie->get('favizone_connection_identifier_'.$store_id)))
            return $cookie->get('favizone_connection_identifier_'.$store_id);
        return "anonymous";
    }

    /**
     * Generates the connection identifier
     *
     * @return String
     */
    public function generateSessionIdentifier($store_id = null){

        if(is_null($store_id)){

            $store = Mage::app()->getStore();
            $store_id = $store->getId();
        }
        $sessionIdentifier = null;
        $cookie = Mage::getModel('core/cookie');
        //session identifier format :favizone_connection_identifier_idStore
        //if(!empty($cookie->get('favizone_connection_identifier_'.$store_id)) != "" && !is_null($cookie->get('favizone_connection_identifier_'.$store_id))){
        if($cookie->get('favizone_connection_identifier_'.$store_id)){

            $sessionIdentifier = Mage::getModel('core/cookie')->get('favizone_connection_identifier_'.$store_id);
        }
        else{

            $sender = Mage::helper('favizone_recommender/sender');
            $data = Mage::helper('favizone_recommender/data');
            $data_to_send =  array( "key" => $this->getApplicationKey());

            $result = $sender->postRequest($data->getRegisterProfiletUrl(), $data_to_send);
            $result = json_decode($result,true);
            if($result['response'] == 'authorized' || $result['response'] == 'success' ){

                $cookie->set('favizone_connection_identifier_'.$store_id, $result['identifier'], null, '/', null, false, false);

                $abTest = $this->getStoreInfo($store_id)->getAbTest();
                $abTest = ($abTest == 'true');
                if(!$abTest){

                    $cookie->set('favizone_visit_'.$store_id, true, null, '/', null, false, false);
                }
                    
                $sessionIdentifier = $result['identifier'];
            }
        }

        return $sessionIdentifier;
    }

    /**
     * Return the application's access key
     *
     * @return String
     */
    public function getApplicationKey(){

        $store = Mage::app()->getStore();

        $accessKey = $this->getStoreAccessKey($store->getId());
        return $accessKey;
    }

    /**
     * Loading  A/B testing version from session
     *
     * @return string {'A', 'B'}
     */
    public function getTestingVersion()
    {

        $store = Mage::app()->getStore();
        $favizone_store_data = $this->getStoreInfo($store->getId());
        $abTest = $favizone_store_data->getAbTest();
        $abTest = ($abTest == 'true');
        $sessionIdentifier = Mage::getModel('core/cookie')->get('favizone_connection_identifier_'.$store->getId());
        //Old user
        if($sessionIdentifier){
            $current_version = Mage::getModel('core/cookie')->get('favizone_AB_'.$store->getId());
            //A/B test is active
            if($abTest){

                if($current_version){
                    if($current_version == 'N'){

                        $current_version = $this->get_random_version();
                        Mage::getModel('core/cookie')->set('favizone_AB_'.$store->getId(), $current_version, null, '/', null, false, false);
                        Mage::getModel('core/cookie')->set('favizone_visit_'.$store->getId(), true, null, '/', null, false, false);

                    }
                }
                else{

                    $current_version = $this->get_random_version();
                    Mage::getModel('core/cookie')->set('favizone_AB_'.$store->getId(), $current_version, null, '/', null, false, false);
                }
            }
            //A/B test is inactive
            else{

                if($current_version != 'N'){

                    $current_version = 'N';
                    Mage::getModel('core/cookie')->set('favizone_AB_'.$store->getId(), $current_version, null, '/', null, false, false);
                }
            }

            return $current_version;
        }
        //New user
        else{

            //A/B test is active
            if($abTest){

                $current_version = $this->get_random_version();
                Mage::getModel('core/cookie')->set('favizone_AB_'.$store->getId(), $current_version, null, '/', null, false, false);
                Mage::getModel('core/cookie')->set('favizone_visit_'.$store->getId(), true, null, '/', null, false, false);

            }
            //A/B test is inactive
            else{

                $current_version = 'N';
                Mage::getModel('core/cookie')->set('favizone_AB_'.$store->getId(), $current_version, null, '/', null, false, false);
            }

            return $current_version;
        }

    }


    private function get_random_version(){

        if(rand(1, 2) == 2){
            $version  = 'A';

        }
        else{
            $version  = 'B';

        }

        return $version;
    }


    /**
     * Gets the distinct current cart
     *
     * @return array
     */
    public function getCurrentCart(){

        $product_ids = array();
        $cart = Mage::getModel('checkout/cart')->getQuote();
        foreach ($cart->getAllVisibleItems() as $item) {

             array_push($product_ids, $item->getProduct()->getId());
        }
        return $product_ids;
    }

    /**
     * Return full current cart
     *
     * @return array
     */
    public function getCurrentFullCart(){

        $product_ids = array();
        $cart = Mage::getModel('checkout/cart')->getQuote();
        foreach ($cart->getAllItems() as $item) {

            array_push($product_ids, $item->getProduct()->getId());
        }
        return $product_ids;
    }

    /**
     * Validate current context
     *
     * @return boolean
     */
    public  function validateContext(){

        $store = $this->getStoreInfo( Mage::app()->getStore()->getId());
        if(!empty($store->getData())){
            return true;
        }
        return false;
    }

    /**
     * Verifies if preview mode is enabled
     *
     * @return array
     */
    public function isPreviewMode(){

        $cookie = Mage::getModel('core/cookie');

        if (isset($_GET['favizone_preview']) && $_GET['favizone_preview'] == "true") {

            $cookie->set('favizone_preview', true, null, '/', null, false, false);
            return true;
        }
        if (isset($_GET['favizone_preview']) && $_GET['favizone_preview'] == "false") {

            $cookie->delete('favizone_preview');
            return false;
        }

        if(!empty($cookie->get('favizone_preview')))
            return true;

        return false;

    }

    /**
     * @param $store_id
     * @return mixed
     */
    public function getStoreAccessKey($store_id){

        $store_data = Mage::getModel('favizone_recommender/accessKey')->load($store_id, 'store_id');
        return $store_data->getAccessKey() ;

    }

    /**
     * @param $store_id
     * @return mixed
     */
    public function sendCheckInit($store_id){

        $sender = Mage::helper('favizone_recommender/sender');
        $data = Mage::helper('favizone_recommender/data');
        $access_key = $this->getStoreAccessKey($store_id);

        $data_to_send =  array( "key"=>$access_key
        ,"cms_version"=>("1.9")
        ,"cms_name"=>"magento");

        $result = $sender->postRequest($data->getCheckInitUrl(), $data_to_send);
        return json_decode($result,true) ;
    }

    /**
     * @param $store_id
     * @return Mage_Core_Model_Abstract
     */
    public function getStoreInfo($store_id){

        return Mage::getModel('favizone_recommender/accessKey')->load($store_id, 'store_id');
    }

    /**
     * Update AB/Test status
     *
     * @param $action
     * @return Array() the categories list
     */
    public function sendABTestStatus($action, $store_id){

        $sender = Mage::helper('favizone_recommender/sender');
        $data = Mage::helper('favizone_recommender/data');
        $access_key = $this->getStoreAccessKey($store_id);

        $data_to_send =  array( "key"=>$access_key);
        if($action == "init")
            $sender->postRequest($data->getInitABTestUrl(), $data_to_send);
        else
            $sender->postRequest($data->getEndABTestUrl(), $data_to_send);

    }


    /**
     * reset extension's data for the given shop 
     *
     * @param $store_id
     * @return Array() the categories list
     */
    public function resetData(){
        $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $transaction->beginTransaction();

            $transaction->query('DELETE FROM favizone_recommender_access_key');

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack(); // if anything goes wrong, this will undo all changes you made to your database
        }
    }
}