<?php
/**
 * 2016 Favizone Solutions Ltd
 *
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com  for more information.
 *
 * Favizone Admin  controller
 *
 * @category  Favizone
 * @package   Favizone_Recommender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
class Favizone_Recommender_Adminhtml_FavizoneController extends Mage_Adminhtml_Controller_Action{


    /**
     * Shows the main config page for the extension.
     */
    public function indexAction()
    {
        if (!Favizone_Recommender_Block_Common::getSelectedStore()) {
            // If we are not under a store view, then redirect to the first
            // found one because Favizone is configured per store.
            foreach (Mage::app()->getWebsites() as $website) {
                $storeId = $website->getDefaultGroup()->getDefaultStoreId();
                if (!empty($storeId)) {
                    $this->_redirect('*/*/index', array('store' => $storeId));
                    return;
                }
            }
        }

        $this->loadLayout()
             ->_title($this->__('Favizone'));
        $this->renderLayout();
    }

    /**
     * Sends the categories data to Favizone
     */
    public function sendCategoryDataAction(){

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $store = Favizone_Recommender_Block_Common::getSelectedStore();
        //categories
        Mage::helper('favizone_recommender/category')->sendCategoriesData($store->getId());
        //Preparing response
        $responseData = array(

            'success' => true,
        );
        $this->getResponse()->setBody(json_encode($responseData));
    }


    /**
     * Updates the A/B testing context
     *
     */
    public  function updateAbTestingAction(){

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $store = Favizone_Recommender_Block_Common::getSelectedStore();
        $element = Mage::helper('favizone_recommender/common')->getStoreInfo($store->getId());
        $ab_test_new_status = $this->getRequest()->getParam('ab_test');
        $ab_test_old_status = $element->getAbTest();
        if($ab_test_new_status == 'true'){
            if($ab_test_old_status != $ab_test_new_status) {
                $element->setAbTest($ab_test_new_status);
                $element->save();
                //sending status
                Mage::helper('favizone_recommender/common')->sendABTestStatus("init", $store->getId());
            }
        }
        else{
            if($ab_test_old_status != $ab_test_new_status) {
                $element->setAbTest($ab_test_new_status);
                $element->save();
                //sending status
                Mage::helper('favizone_recommender/common')->sendABTestStatus("end", $store->getId());
            }
        }


        //Preparing response
        $responseData = array(
            'success' => true,
        );
        $this->getResponse()->setBody(json_encode($responseData));
    }

    /**
     *  Updates Data
     *
     */
    public  function synchronizeDataAction(){

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $store = Favizone_Recommender_Block_Common::getSelectedStore();
        $result = Mage::helper('favizone_recommender/account')->sendAccountCreation($store->getId(), $this->getRequest()->getParam('auth_key'));
        if($result["status"] == "success"){

            $check_data = Mage::helper('favizone_recommender/common')->sendCheckInit($store->getId());
            if(($check_data['response'] == 'authorized') && ($check_data['result'] == 'Zy,]Jm9QkJ')){
                //synchronizing data
                //orders
                Mage::helper('favizone_recommender/order')->sendOrdersData($store->getId());
                //categories
                Mage::helper('favizone_recommender/category')->sendCategoriesData($store->getId());
                //products
                Mage::helper('favizone_recommender/product')->initTaggingProductData($store);
            }

            //Preparing response
            $responseData = array(

                'success' => true,
            );
        }
        else{
            //Preparing response
            $responseData = array(

                'success' => false,
            );
        }
        $this->getResponse()->setBody(json_encode($responseData));
    }

    /**
    * Reset Extension data
    */
    public  function resetDataAction(){

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $store = Favizone_Recommender_Block_Common::getSelectedStore();
        Mage::helper('favizone_recommender/common')->resetData($store->getId());
       //Preparing response
        $responseData = array(

            'success' => true,
        );
        $this->getResponse()->setBody(json_encode($responseData));
    }

    public function exportProductsToCsv(){
        $limit = 500;
        $store = Favizone_Recommender_Block_Common::getSelectedStore();
        $products_count = Mage::helper('favizone_recommender/product')->getCountAvailableProducts($store->getId()) ;
        $pagination = (int) ($products_count / $limit);
        if($products_count%$limit>0)
          $pagination += 1;
        //$products_collections = array();
        $products_collections = "";
        
       foreach (range(1, $pagination) as $offset) {
            $products = Mage::helper('favizone_recommender/product')->exportProducts($store, $limit, $offset);
           // $products_collections = array_merge($products_collections, $products);
             $products_collections .= $products;
       }
    
        Mage::helper('favizone_recommender/export')
            ->exportDataToCsv($store, $products_collections);
    }

    /**
     * Returns the currently selected store view identifier.
     *
     * @return Mage_Core_Model_Store|null the store view or null if not found.
     */
    protected function getSelectedStore()
    {
        if (Mage::app()->isSingleStoreMode()) {
            return Mage::app()->getStore(true);
        } elseif (($storeId = (int)$this->getRequest()->getParam('store')) !== 0) {
            return Mage::app()->getStore($storeId);
        } else {
            return null;
        }
    }

}