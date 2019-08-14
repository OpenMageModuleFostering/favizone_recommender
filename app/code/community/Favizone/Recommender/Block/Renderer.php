<?php

/**
 * 2016 Favizone Solutions Ltd
 *
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com  for more information.
 *
 * Favizone Rendering block
 * Adds Common rendering data
 *
 * @category  Favizone
 * @package   Favizone_Recommender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
class Favizone_Recommender_Block_Renderer extends Mage_Core_Block_Template
{
    /**
     * Returns the canal name
     *
     * @return String
     * */
    public function getCanalData(){

        $canal = $this->getCanal();
        return $canal;

    }

    /**
     * Returns the canal rendering url
     *
     * @return String
     * */
    public function getRenderingUrl(){

        return Mage::helper('favizone_recommender/data')->getRecommendationRendererUrl() ."/".$this->getCanal();
    }

    /**
     * Returns the canal rendering data
     *
     * @return Object
     * */
    public function getRenderingData(){
        return json_encode($this->getRenderingCanal($this->getCanal())) ;
    }

    /**
     * Returns  rendering data to the given canal
     *
     * @var $canal String
     * @return Object
     * */
    public function getRenderingCanal($canal){

        $data_to_send = array();
        $helper = Mage::helper('favizone_recommender/common');
        $sessionIdentifier = $helper->getSessionIdentifier();
        $test_version =  $helper->getTestingVersion();
        $accessKey = $helper->getApplicationKey();
        if(!is_null($sessionIdentifier)){
            switch($canal){
                case "product":
                    $data_to_send =  array(

                        "product"=> Mage::registry('current_product')->getId(),
                        "key" => $accessKey,
                        "session" => $sessionIdentifier,
                        "event_params" => array( "version" => $test_version
                        ,"session" => $sessionIdentifier
                        ),
                        "cart" => $helper->getCurrentCart()
                    );
                    break;
                case "category":

                    $category = Mage::registry('current_category');
                    $data_to_send =array( "key" => $accessKey,
                            "session" => $sessionIdentifier,
                            "event_params" => array( "version" => $test_version
                                                     ,"session" => $sessionIdentifier
                                                    ),
                            "category" => $category->getId(),
                            "cart" => $helper->getCurrentCart()
                        );
                    break;
                case "search":
                    $data_to_send =  array(
                            "key" => $accessKey,
                            "session" => $sessionIdentifier,
                            "event_params" => array( "version" => $test_version,"session" => $sessionIdentifier),
                            "search" => Mage::helper('catalogsearch')->getQueryText(),
                            "cart" => $helper->getCurrentCart()
                    );
                    break;
                default:
                    $data_to_send =  array(
                        "key" => $accessKey,
                        "session" => $sessionIdentifier,
                        "event_params" => array( "version" => $test_version,"session" => $sessionIdentifier),
                        "cart" => $helper->getCurrentCart()
                    );
                    break;

            }
        }
        /** Searching for keywords coming from campaigns **/
        if(isset($_GET['favizone'])){
            $data_to_send['campaign'] = $_GET['favizone'];
        }

        return $data_to_send;
    }
}

