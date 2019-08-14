<?php

/**
 * 2016 Favizone Solutions Ltd
 *
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com  for more information.
 *
 * Favizone Tracking block
 * Adds the tracking data
 *
 * @category  Favizone
 * @package   Favizone_Recommender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
class Favizone_Recommender_Block_Tracker extends Mage_Core_Block_Template
{

    /**
     * Returns the data of the given event name
     *
     * @param $key String
     * @return String
     * */
    public function getTrackerEventByKey($key){

        $helper = Mage::helper('favizone_recommender/common');
        $event = '';
        $event.= $helper->getTestingVersion().' ';
        $event.= $helper->getSessionIdentifier().' ';
        $event.= $key.' ';

        return $event;
    }
    /**
     * Returns events data
     *
     * @return Array
     * */
    public function TrackerEvents(){

        $events = array();
        $helper = Mage::helper('favizone_recommender/common');
        $test_version =  $helper->getTestingVersion();
        $event = '';
        $event.= $test_version.' ';
        $event.= $helper->getSessionIdentifier().' ';
        $store = Mage::app()->getStore();

        switch ($this->getEvent()){
            case 'viewProduct':
                $product = Mage::registry('current_product');
                if($product){

                    $event.= "viewProduct ".$product->getId()." 1 1";
                    array_push($events, $event);
                }

                break;
            case 'viewCategory':
                $category = Mage::registry('current_category');
                if($category){
                    $path = str_replace(' ', "fz#", Favizone_Recommender_Helper_Category::getCategoryPath($category->getPath(), $store->getId())) ;
                    $event.= "viewCategory ".$path." 1 1";
                    array_push($events, $event);
                }

                break;
            case "doSearch":

                $event .= "doSearch ";
                $event .= urlencode( Mage::helper('catalogsearch')->getQueryText())." 1 1";
                array_push($events, $event);
                break;
        }
        //searching for keywords coming from search engines or campaigns
        if(!is_null($_GET['favizone'])){

            $event = '';
            $event.= $test_version.' ';
            $event.= $helper->getSessionIdentifier().' ';
            array_push($events, $event."searchCampaign ".urlencode($_GET['favizone'])." 1 1");
        }

        /** Searching for widget  tag **/
        if (isset($_GET['favizone_widget_email'])) {

            $event= $test_version.' '.$helper->getSessionIdentifier().' ';
            array_push($events, $event."clickWidget ".$_GET['favizone_widget_email']." 1 1");

        }
        
        return $events;
    }

    /**
     * Returns visit event structure
     *
     * @return String
     * */
    public function getVisitEvent(){
        
        $helper = Mage::helper('favizone_recommender/common');
        $test_version =  $helper->getTestingVersion();
        $event = '';
        $event.= $test_version.' ';
        $event.= $helper->getSessionIdentifier().' ';
        $event.="visit 1 1 1";
        return $event;
    }

    /**
     * Returns the custom data
     *
     * @return array
     * */
    public function getCustomData(){

        $result = array();
        $store = Mage::app()->getStore();
        switch ($this->getEvent()){
            case 'viewCategory':
                $categoryData = Favizone_Recommender_Helper_Category::getCategoryData(Mage::registry('current_category')->getId(), $store->getId()) ;
                $result['custom_event_key'] = $this->getEvent();
                $result['custom_event_value'] = Mage::registry('current_category')->getId();
                $result['category_data'] = $categoryData ;
                break;
            case 'viewProduct':
                $meta = Mage::getModel('favizone_recommender/meta_product');
                $product = Mage::registry('current_product');
                // Load the product model for this particular store view.
                $product = Mage::getModel('catalog/product')
                                    ->setStoreId($store->getId())
                                    ->load($product->getId());
                $favizone_product = $meta->loadProductData($product, $store, false);
                $result['product_data']= $favizone_product;
                break;
        }
        /** searching for keywords coming from FB messenger */
        if(!is_null($_GET['fz_p'])){

            $result['favizone_facebook_profile'] = $_GET['fz_p'];
        }

        /** searching for keywords coming from campaigns */
        if(!is_null($_GET['favizone'])){

            $result['search_campaign'] = 'campaign';
            $result['search_campaign_value'] = $_GET['favizone'];
        }

        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $customer_meta = Mage::getModel('favizone_recommender/meta_customer');
            $customer_data = $customer_meta->loadCustomerData(Mage::getSingleton('customer/session')->getCustomer()->getId());
            $result['user_data'] = $customer_data;
        }

        return $result;
    }

    public function getCookiesExpirationTime(){
       
        $lifeTime = Mage::getModel('core/cookie')->getLifetime('favizone_visit_'.Mage::app()->getStore()->getId()) ;
        return $lifeTime ;
    }
}