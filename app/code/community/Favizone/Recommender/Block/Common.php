<?php

/**
 * 2016 Favizone Solutions Ltd
 *
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com  for more information.
 *
 * Favizone Common block
 * Adds Common html rendering data
 *
 * @category  Favizone
 * @package   Favizone_Recommender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
class Favizone_Recommender_Block_Common extends Mage_Core_Block_Template
{

    const DEFAULT_ID = 'favizone_element';
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
     * Returns the block's canal value
     *
     * @return string
     */
    public function getCanalData(){

        $canal = $this->getCanal();
        return $canal;

    }

    protected function _toHtml()
    {
        if (!Mage::helper('favizone_recommender')->isModuleEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Returns the session identifier
     *
     * @return string
     */
    public function getSessionId(){

        $helper = Mage::helper('favizone_recommender/common');
        return $helper->getSessionIdentifier();
    }

    /**
     * Returns a product's json data
     *
     * @return Object
     */
    public function getProductData(){

        $meta = Mage::getModel('favizone_recommender/meta_product');
        $store = Mage::app()->getStore();
        $product = Mage::registry('current_product');
        // Load the product model for this particular store view.
        $product = Mage::getModel('catalog/product')
                    ->setStoreId($store->getId())
                    ->load($product->getId());
        
        $favizone_product = $meta->loadProductData($product, $store, false);
    
        return json_encode($favizone_product);
    }

    /**
     * Returns the currently selected store view identifier.
     *
     * @return Mage_Core_Model_Store|null the store view or null if not found.
     */
    public static function getSelectedStore()
    {
        if (Mage::app()->isSingleStoreMode()) {
            return Mage::app()->getStore(true);
        } elseif (($storeId = (int)Mage::app()->getRequest()->getParam('store')) !== 0) {
            return Mage::app()->getStore($storeId);
        } else {
            return null;
        }
    }

}