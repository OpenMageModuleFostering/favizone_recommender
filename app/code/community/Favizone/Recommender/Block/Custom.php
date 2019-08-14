<?php

/**
 * 2016 Favizone Solutions Ltd
 *
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com  for more information.
 *
 * Favizone Custom block
 * Adds custom  rendering data
 *
 * @category  Favizone
 * @package   Favizone_Recommender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
class Favizone_Recommender_Block_Custom extends Mage_Core_Block_Template
{

    const DEFAULT_ID = 'favizone_element';
    const ERROR_SECTION_IDENTIFIER = 'favizone_error_element';
    const OTHER_SECTION_IDENTIFIER = 'favizone_other_element';

    /**
     * Return the id of the element. If none is defined in the layout xml,
     * then set a default one.
     *
     * @return string
     */
    public function getSectionId()
    {
        $id = $this->getDivIdentifier();
        if ($id === null) {
            $id = self::DEFAULT_ID;
        }
        return $id;
    }

    /**
     * Returns the custom canal name
     *
     * @return string
     */
    public function getRenderingUrl(){

        $action = Mage::app()->getRequest()->getActionName();
        if($action =="noRoute"){

            return Mage::helper('favizone_recommender/data')->getRecommendationRendererUrl() ."/error";
        }
        else
            return Mage::helper('favizone_recommender/data')->getRecommendationRendererUrl() ."/others";
    }

    /**
     * Checks if it's a custom context or not
     *
     * @return Boolean
     */
    public function isCustomContext(){

        $context = false;
        $action = Mage::app()->getRequest()->getActionName();
        if($action =="noRoute")
            $context = true;
        else{

            $controller = Mage::app()->getFrontController()->getRequest()->getControllerName();
            $controllers = array("result","product", "index", "cart","category");

            if(!in_array($controller, $controllers))
                $context = true;
        }

        return $context;
    }

    /**
     * Returns the section identifier related to the custom context data
     *
     * @return String
     */
    public function getSectionIdentifier(){

        $action = Mage::app()->getRequest()->getActionName();
        if($action =="noRoute"){

            return  self::ERROR_SECTION_IDENTIFIER;
        }else{

            $controller = Mage::app()->getFrontController()->getRequest()->getControllerName();
            $controllers = array("result","product", "index", "cart","category");

            if(!in_array($controller, $controllers)){
                return  self::OTHER_SECTION_IDENTIFIER;
            }
        }

        return '';
    }

    /**
     * Returns the custom context rendering data
     *
     * @return array
     */
    public function getRenderingData(){

        $data_to_send = array();
        $helper = Mage::helper('favizone_recommender/common');
        $sessionIdentifier = $helper->getSessionIdentifier();
        $store_id = Mage::app()->getStore()->getId();
        if(!is_null($sessionIdentifier)){

            $data_to_send =  array(

                "key" => $helper->getStoreAccessKey($store_id) ,
                "session" => $sessionIdentifier,
                "event_params" => array( "version" => $helper ->getTestingVersion(),"session" => $sessionIdentifier),
                "cart" => $helper->getCurrentCart()
            );

            /** Searching for keywords coming from campaigns **/
            if(isset($_GET['favizone'])){
                $data_to_send['campaign'] = $_GET['favizone'];
            }
        }

        return $data_to_send;
    }
}