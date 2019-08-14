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

class Favizone_Recommender_Model_Meta_Order extends Mage_Core_Model_Abstract{

    public function loadOrderData($order){

        $orders_events = array();
        foreach($order->getAllVisibleItems() as $item){

            array_push($orders_events,strtotime($order->getCreatedAt()). " favizone_xxx ".$order->getCustomerId().' confirm '.Mage::helper('favizone_recommender/order')->buildItemProductId($item).' '.$item->getPriceInclTax().' '.(int)$item->getQtyOrdered());
        }

        return $orders_events;
    }

}