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
class  Favizone_Recommender_Helper_Sender extends Mage_Core_Helper_Abstract
{
    /**
     * POST data .
     *
     * @param String $host.
     * @param String $path.
     * @param array()
     * @return array() the response data
     */
    public function postRequest($url, $body=array())
    {
        $iClient = new Varien_Http_Client();
        $iClient->setUri($url)
                ->setMethod('POST')
                ->setConfig(array(
                    'timeout'=>20,
                ));
       
        //Request header
        $iClient->setHeaders(array(
            'Content-Type' =>'application/json',
            'Origin'=>$_SERVER['SERVER_NAME'],
            'Content-Length' => strlen($body),
        ));

        $iClient->setRawData(json_encode($body));
        try{
            $response = $iClient->request();

            if(!empty($response)&&!is_null($response)){

                return Zend_Http_Response::extractBody($response) ;
            }
                
        }catch (Zend_Http_Client_Exception $e) {
            return "{}";
        }
    }
}