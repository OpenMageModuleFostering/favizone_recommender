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

class  Favizone_Recommender_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * @var int the limit of items to fetch.
     */
    public $limit = 100;

    /**
     * @var int the offset of items to fetch.
     */
    public $offset = 1;
    /**
     * Send the order's data To favizone
     *
     * @param $order object
     */
    public function sendOrderData($order, $store_id){

        $events = array();
        $common_helper = Mage::helper('favizone_recommender/common');
        $customer_meta = Mage::getModel('favizone_recommender/meta_customer');
        $session_identifier = $common_helper->getSessionIdentifier();
        if($session_identifier == "anonymous") {
            $session_identifier =  $order->getCustomerId();
        }

        $event = $common_helper->getTestingVersion();
        $event.= ' '.$session_identifier.' confirm';
        $access_key = Mage::helper('favizone_recommender/common')->getStoreAccessKey($store_id);
        foreach($order->getAllVisibleItems() as $item){

            array_push($events,
                        $event.' '.$this->buildItemProductId($item).' '.$item->getPriceInclTax().
                        ' '.$item->getQtyOrdered());
        }

        $cart = Mage::getModel('checkout/cart')->getQuote();
                $cart_id = $cart->getId();
        $custom_data = array_merge($customer_meta->loadCustomerData($order->getCustomerId()), 
                                    array('id_cart'=>$cart_id ,'id_order' => $order->getId()));
        $data_to_send =  array( "key"=>$access_key,
                                "events" => $events,
                                "session"=>$session_identifier,
                                "custom_event_value"=> $custom_data);

        $sender = Mage::helper('favizone_recommender/sender');
        $data = Mage::helper('favizone_recommender/data');

        $sender->postRequest($data->getSendEventUrl(), $data_to_send);
    }

     /**
     * Returns the product id for a quote item.
     * Always try to find the "parent" product ID if the product is a child of
     * another product type. We do this because it is the parent product that
     * we tag on the product page, and the child does not always have it's own
     * product page. This is important because it is the tagged info on the
     * product page that is used to generate recommendations and email content.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return int
     */
    public function buildItemProductId($item)
    {
        $parent = $item->getProductOptionByCode('super_product_config');
        if (isset($parent['product_id'])) {
            return $parent['product_id'];
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
            $model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $model->getParentIdsByChild($item->getProductId());
            $attributes = $item->getBuyRequest()->getData('super_attribute');
            // If the product has a configurable parent, we assume we should tag
            // the parent. If there are many parent IDs, we are safer to tag the
            // products own ID.
            if (count($parentIds) === 1 && !empty($attributes)) {
                return $parentIds[0];
            }
        }
        return $item->getProductId();
    }

    public function sendOrdersData($store_id){

        $number_orders = $this->getCountOrders($store_id);
        $pagination = $number_orders/$this->limit;
        $pagination = (int)$pagination;
        if($number_orders%$this->limit>0)
            $pagination +=1;
        $meta = Mage::getModel('favizone_recommender/meta_order');
        /** Sending paginated orders data **/
        while($this->offset <= $pagination){
            $orders_collection = array();
            foreach ($this->getPaginatedOrders($store_id) as $order){

                $favizone_order= $meta->loadOrderData($order, $store_id);
                $orders_collection = array_merge($orders_collection, $favizone_order);
            }

            $this->offset += 1;
            $this -> sendInitOrder($orders_collection, $store_id);
        }

        return  "orders sent" ;
    }

    /**
     * Gets all available orders number
     */
    protected function getCountOrders($store_id){

        return  Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('store_id', $store_id)
            ->count();
    }

    /**
     * @param $store_id
     * @return mixed
     */
    protected function getPaginatedOrders($store_id){

        return Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('store_id', $store_id)
            ->setOrder('entity_id','DESC')
            ->setPageSize($this->limit)
            ->setCurPage($this->offset);

    }

    protected function sendInitOrder($orders, $store_id){

        $access_key = Mage::helper('favizone_recommender/common')->getStoreAccessKey($store_id);

        $data_to_send =  array( "key"=>$access_key,
                                "orders" => $orders);

        $sender = Mage::helper('favizone_recommender/sender');
        $data = Mage::helper('favizone_recommender/data');

        $sender->postRequest($data->getInitOrderUrl(), $data_to_send);
    }
}