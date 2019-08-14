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
class Favizone_Recommender_Model_Observer
{
    /**
     * Xml layout handle for the default page footer section.
     */
    const LAYOUT_PAGE_DEFAULT_FOOTER = 'favizone_recommender_page_default_footer';

    /**
     * Event handler for the "catalog_product_save_after" event.
     * Sends a new product's to Favizone.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     */
    public function sendUpdateProduct(Varien_Event_Observer $observer)
    {
        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {

            /** @var Mage_Catalog_Model_Product $product */
            $product = $observer->getEvent()->getProduct();
            $meta = Mage::getModel('favizone_recommender/meta_product');
            foreach ($product->getStoreIds() as $storeId) {
                $store = Mage::app()->getStore($storeId);

                if(!empty(Mage::helper('favizone_recommender/common')->getStoreInfo($store->getId())->getData())){

   
                    // Load the product model for this particular store view.
                    $product = Mage::getModel('catalog/product')
                                            ->setStoreId($store->getId())
                                            ->load($product->getId());
                    if (!is_null($product) && $product->isVisibleInSiteVisibility()) {
                        $favizoneProduct = $meta->loadProductData($product, $store);
                        Mage::helper('favizone_recommender/product')->updateTaggingProductData($favizoneProduct, 'update', $store->getId());
                    }
                }
            }
        }
    }
    /**
     * Event handler for the "catalog_product_delete_after" event.
     * Sends a delete product event to Favizone.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Favizone_Recommender_Model_Observer
     */
    public function sendDeleteProduct(Varien_Event_Observer $observer)
    {
        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $observer->getEvent()->getProduct();
            foreach (Mage::app()->getStores() as $store) {
                if(!empty(Mage::helper('favizone_recommender/common')->getStoreInfo($store->getId())->getData()))
                    Mage::helper('favizone_recommender/product')->updateTaggingProductData($product, 'delete', $store->getId());
            }
        }
    }

    /**
     * Sends a new category's to Favizone.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Favizone_Recommender_Model_Observer
     */
    public function sendUpdateCategory(Varien_Event_Observer $observer)
    {
        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {
            $category = $observer->getEvent()->getCategory();
            foreach ($category->getStoreIds() as $storeId) {
                if(!empty(Mage::helper('favizone_recommender/common')->getStoreInfo($storeId)->getData())){

                    Mage::helper('favizone_recommender/category')->sendCategoryData((int)$category->getId(), 'update', (int)$storeId);
                }
            }
        }
    }
    /**
     * Sends deleted category to Favizone.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     */
    public function sendDeleteCategory(Varien_Event_Observer $observer)
    {
        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {

            $category = $observer->getEvent()->getCategory();
            foreach ($category->getStoreIds() as $storeId) {
                if(!empty(Mage::helper('favizone_recommender/common')->getStoreInfo($storeId)->getData())){

                    Mage::helper('favizone_recommender/category')->sendCategoryData($category->getId(), 'delete', $storeId);
                }
            }
        }

    }

    public function addBlockAfterMainContent(Varien_Event_Observer $observer)
    {
        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {

            $layout = $observer->getEvent()->getLayout()->getUpdate();
            $layout->addHandle('favizone_recommender_page_default_footer');
        }

        return $this;
    }

    /**
     * Sends add to cart event to Favizone.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     */
    public function sendAddToCartEvent(Varien_Event_Observer $observer){
        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {
            $store = Mage::app()->getStore();
            if(!empty(Mage::helper('favizone_recommender/common')->getStoreInfo($store->getId())->getData())){

                if (!$store->isAdmin()) {
                    $item = $observer->getEvent()->getQuoteItem();
                    $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
                    $product  = $item->getProduct();
                    if($item->getQty()==1){

                        $quote_item_id = $product->getId();
                        Mage::helper('favizone_recommender/product')->sendProductEvent($quote_item_id, 'addToCart', $store->getId());
                    }
                }
            }
        }
    }

    /**
     * Sends a confirm order event to Favizone.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Favizone_Recommender_Model_Observer
     */
    public function sendConfirmOrderEvent(Varien_Event_Observer $observer){

        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {
            $order = $observer->getEvent()->getOrder();
            $store = Mage::app()->getStore();
            if(!empty(Mage::helper('favizone_recommender/common')->getStoreInfo($order->getStoreId())->getData())){

                Mage::helper('favizone_recommender/order')->sendOrderData($order, $order->getStoreId(), $store->getId());
            }
        }
    }

    /**
     * Sends customer's to Favizone.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Favizone_Recommender_Model_Observer
     */
    public function sendCustomerData(Varien_Event_Observer $observer){

        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {
             $groupId = Mage::app()->getStore()->getGroupId();
             $common_helper = Mage::helper('favizone_recommender/common');
             $stores = Mage::getModel('core/store')->getCollection()->addFieldToFilter('group_id',$groupId);
             $customer = $observer->getEvent()->getCustomer();
             foreach($stores as $store) {
                 if(!empty($common_helper->getStoreInfo($store->getId())->getData())){
                    if($common_helper->getSessionIdentifier($store->getId()) != "anonymous")
                        Mage::helper('favizone_recommender/customer')->sendCustomerData($customer->getId(), $store->getId());
                 }
             }
        }
    }

    /**
     * Event handler for the "logout_user" event.
     * Resets cookies data
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Favizone_Recommender_Model_Observer
     */
    public function resetData(Varien_Event_Observer $observer){

        if (Mage::helper('favizone_recommender')->isModuleEnabled()) {
            $store = Mage::app()->getStore();
            Mage::getModel('core/cookie')->delete('favizone_connection_identifier_'.$store->getId(), '/');
            Mage::getModel('core/cookie')->delete('favizone_AB_'.$store->getId(), '/');
        }
    }

    /**
     * Generates connection identifier
     *
     * @return Favizone_Recommender_Model_Observer
     */
    public function generateSessionIdentifier(Varien_Event_Observer $observer){
        if (Mage::helper('favizone_recommender')->isModuleEnabled() &&  Mage::helper('favizone_recommender/common')->validateContext()) {

            Mage::helper('favizone_recommender/common')->generateSessionIdentifier();


            //Adds special block to be show in all pages
            $layout = $observer->getEvent()->getLayout()->getUpdate();
            $layout->addHandle(self::LAYOUT_PAGE_DEFAULT_FOOTER);

        }

        return $this;
    }
}