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

class Favizone_Recommender_Model_Meta_Product extends Mage_Core_Model_Abstract{

    const  DATE_FORMAT = "Y-m-d H:i:s";

    /**
     * Return the product data
     *
     * @param $product Mage_Catalog_Model_Product
     * @param Mage_Core_Model_Store $store
     * @return array
     */
    public function loadProductData(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store, $wholeSale = true){


        $product_data =   array();
        $utcTz = new DateTimeZone("UTC");

        //Store identifier
        $product_data['id_shop'] = $store->getId();

        //product's identifier
        $product_data['identifier'] = $product->getId();

        //product's reference
        $product_data['reference'] = $product->getSku();

        //description
        $product_data['description'] = $product->getDescription();
        //short description
        $product_data['shortDescription']= $product->getShortDescription();
        
        //Price
        $product_data['price']= $this->getProductPrice($product);
        //wholesale
        if($wholeSale) {
            $wholesalePrice = $this->getProductWholeSalePrice($product, $store);
            if(!is_null($wholesalePrice))
                $product_data['wholesale_price'] = (float)$wholesalePrice;
        }
        
        //Cover image
        $product_data['cover'] = $this->getProductImageUrl($product, $store);

        //Currency
        $product_data['currency'] = $store->getCurrentCurrencyCode();

        //Url
        $product_data['url'] = $product
            ->getUrlInStore(
                array('_ignore_category' => true, '_store' => $store->getCode(),));


        //Is product is in stock
        //$product_data['stock'] = $product->isAvailable();
        $product_data['stock'] = false;
        if( $product->getStockItem()->getQty()>0)
            $product_data['stock'] = true;

        //If product is available for sale
        $product_data['available_for_order']  = false;
        if($product->isSalable()==1 || $product->isSalable()=="1"||$product->isSalable()==true||$product->isSalable()=="true")
            $product_data['available_for_order'] = true ;

        //Is product active
        $product_data['active'] =  ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        //Manufacturer
        if ($product->hasData('manufacturer'))
            $product_data['brand'] = $product->getAttributeText('manufacturer');

        if ($product->hasData('created_at')) {
            $product_data['published_date'] = $product->getData('created_at');
        }

        //Categories
        $categories = $this->getProductCategories($product);
        $product_data['categoriesNames'] = $categories['categories_names'];
        $product_data['categories']= $categories['categories_ids'];

        //Tags
        $product_data['tags']  = $this ->getProductTags($product, $store);

        //Default language
        $product_data['lang'] =  substr(Mage::getStoreConfig('general/locale/code', $store->getId()),0,2);

        //Product's names
        $product_data['title'] = $product->getName();


        $product_data['isNew'] = $this->isProductNew($product);
        if($product_data['isNew'] ){
            $newFromDate = (new DateTime($product->getNewsFromDate()))->setTimezone($utcTz)->format(self::DATE_FORMAT) ;
            $newToDate   = (new DateTime( $product->getNewsToDate()))->setTimezone($utcTz)->format(self::DATE_FORMAT);
            $product_data['isNew_from_date'] = $newFromDate;
            $product_data['isNew_to_date'] = $newToDate;
        }
        $product_rule = $this->getProductRule($product, $store);
        //$product_data['getSpecialPrice'] = $product->getSpecialPrice();
        if(!is_null($product->getSpecialPrice()) && !empty($product->getSpecialPrice())){

            $product_data['isReduced'] = true;
            $product_data['reduction_type'] = 'amount';
            $product_data['price'] = Mage::helper('tax')
                                    ->getPrice($product, $product->getSpecialPrice());
            $product_data['price_without_reduction'] =Mage::helper('tax')
                                                        ->getPrice($product, $product->getPrice());
            $product_data['reduction'] =  $product_data['price_without_reduction'] - $product_data['price'];
            $product_data['reduction_expiry_date'] = (new DateTime($product->getSpecialToDate()))->setTimezone($utcTz)->format(self::DATE_FORMAT);

            $product_data['reduction_tax'] = true;//TTC

        }else{
            if(count($product_rule)>0){

                //$tz = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
                $product_rule = $product_rule[0];
                $product_data['isReduced'] = true;
                $product_data['reduction'] = $product_rule ['action_amount'];
                $product_data['reduction_type'] = 'amount';
                if($product_rule ['action_amount']=='by_percent'){
                    $product_data['reduction_type'] = 'percentage';
                }

                $to_date = new DateTime('@'.$product_rule['to_time']);
                $product_data['reduction_expiry_date'] = $to_date->setTimezone($utcTz)->format(self::DATE_FORMAT);
                $product_data['reduction_tax'] = true;//TTC
                $product_data['price_without_reduction'] = Mage::helper('tax')
                                                             ->getPrice($product, $product->getPrice());

                $product_data['price'] = $this->getProductPriceRule($product, $store);
            }
        }

        //product's facets
        $facets = array();
        $attributes = Mage::getModel('catalog/product_attribute_api')->items($product->getAttributeSetId());
        $notFacetAttributes =  Mage::helper('favizone_recommender/data')->getAttributes();
        foreach($attributes as $_attribute){
           
            if(!in_array($_attribute['code'], $notFacetAttributes)){
                if($product->getData($_attribute['code'])){
                    if(!is_a($product->getData($_attribute['code']), 'array')) {
                        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $_attribute['code']);
                        
                        if($_attribute['type']== "textarea" || $_attribute['type']== "textfield")
                            $facets[$attribute->getFrontend()->getLabel()] = $product->getData($_attribute['code']); 
                        else
                            $facets[$attribute->getFrontend()->getLabel()] = $product->getAttributeText($_attribute['code']); 
                    }
                }
            }          
        }
        if ($product->getData('type_id') == "configurable"){
          //get the configurable data from the product
            $config = $product->getTypeInstance(true);
            $configAttributes =  array();
            //loop through the attributes
            foreach($config->getConfigurableAttributesAsArray($product) as $attributes)
            {
               $configAttributes[$attributes['store_label']] = array();
               foreach($attributes['values'] as $value){
                    array_push($configAttributes[$attributes['store_label']], $value['store_label']);  
               }
            } 
            $facets = array_merge($facets, $configAttributes); 
        }
       
        if(!empty($facets))
            $product_data['facets'] = $facets;

        return $product_data;
    }

    protected function getProductPriceRule($product, $store){
        $discounted_price = Mage::getResourceModel('catalogrule/rule')->getRulePrice(
            Mage::app()->getLocale()->storeTimeStamp($store->getId()),
            Mage::app()->getStore($store->getId())->getWebsiteId(),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            $product->getId());

        if ($discounted_price===false) { // if no rule applied for the product
            return Mage::helper('tax')
                ->getPrice($product, $product->getFinalPrice());
           //return $product->getFinalPrice();
        }else{
            return Mage::helper('tax')
                ->getPrice($product, $discounted_price);
           //$discounted_price,2);
        }
    }

    protected function  getProductRule($product, $store){

        /**
         * Getting active rule data
         */
        $rule = Mage::getResourceModel('catalogrule/rule')->getRulesFromProduct(
            Mage::app()->getLocale()->storeTimeStamp($store->getId()),
            Mage::app()->getStore($store->getId())->getWebsiteId(),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            $product->getId());

        return $rule;
    }

    protected function isProductNew(Mage_Catalog_Model_Product $product)
    {
        $newFromDate = $product->getNewsFromDate();
        $newToDate   = $product->getNewsToDate();
        if (!$newFromDate && !$newToDate) {
            return false;
        }
        return Mage::app()->getLocale()
            ->isStoreDateInInterval($product->getStoreId(), $newFromDate, $newToDate);
    }

    protected function getProductTags(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $tags = array();

        if (Mage::helper('core')->isModuleEnabled('Mage_Tag')) {
            $tagCollection = Mage::getModel('tag/tag')
                ->getCollection()
                ->addPopularity()
                ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED)
                ->addProductFilter($product->getId())
                ->setFlag('relation', true)
                //->addStoreFilter($store->getId())
                ->setActiveFilter();
            foreach ($tagCollection as $tag) {

                $tags[] = $tag->getName();
            }
        }

        return $tags;
    }

    protected function getProductImageUrl(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {

        $url = null;
        $img = $product->getData('image');

        if( !empty($img) && $img !== 'no_selection') {

            $baseUrl = rtrim($store->getBaseUrl('media'), '/');
            $file = str_replace(DS, '/', $img);
            $file = ltrim($file, '/');
            $url = $baseUrl.'/catalog/product/'.$file;
        }
        return $url;
    }

    protected function getProductCategories($product){

        $categories = array('categories_names'=> array(), 'categories_ids'=>array());
        $categories_collection = $product->getCategoryCollection()
            ->addAttributeToSelect('name');
        foreach ($categories_collection as $category) {

            array_push($categories['categories_ids'], $category->getId());
            array_push($categories['categories_names'], $category->getName());
        }
        return $categories;
    }

    protected function getProductName($productId, $storeId){

        $product = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('name')
            //->addStoreFilter($storeId)
            ->addIdFilter($productId)
            ->getFirstItem();

        return $product->getName();

    }

    protected function getProductPrice($product, $finalPrice = true, $inclTax = true)
    {
        $price = 0;

        switch ($product->getTypeId()) {
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                // Get the bundle product "from" price.
                $price = $product->getPriceModel()
                    ->getTotalPrices($product, 'min', $inclTax);
                break;

            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                // Get the grouped product "starting at" price.
                /** @var $tmpProduct Mage_Catalog_Model_Product */
                $tmpProduct = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect(
                        Mage::getSingleton('catalog/config')
                            ->getProductAttributes()
                    )
                    ->addAttributeToFilter('entity_id', $product->getId())
                    ->setPage(1, 1)
                    ->addMinimalPrice()
                    ->addTaxPercents()
                    ->load()
                    ->getFirstItem();
                if ($tmpProduct) {
                    $price = $tmpProduct->getMinimalPrice();
                    if ($inclTax) {
                        $price = Mage::helper('tax')
                            ->getPrice($tmpProduct, $price, true);
                    }
                }
                break;

            default:
                $price = $finalPrice
                    ? $product->getFinalPrice()
                    : $product->getPrice();
                if ($inclTax) {
                    $price = Mage::helper('tax')
                        ->getPrice($product, $price, true);
                }
                break;
        }

        return $price;
    }

    protected function getProductUrl(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $product->unsetData('url');
        return $product
            ->getUrlInStore(
                array(
                    '_nosid' => true,
                    '_ignore_category' => true,
                    '_store' => $store->getCode(),
                )
            );
    }

    protected function getProductWholeSalePrice(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store) 
    {
        $groupName = "wholesale";
        $targetGroup = Mage::getModel('customer/group')->load($groupName, 'customer_group_code');
        $groupPrices = $product->getData('group_price');

        if (is_null($groupPrices)) {
            $attribute = $product->getResource()->getAttribute('group_price');
            if ($attribute){
                $attribute->getBackend()->afterLoad($product);
                $groupPrices = $product->getData('group_price');
            }
        }

        if (!is_null($groupPrices) || is_array($groupPrices)) {

            foreach ($groupPrices as $groupPrice) {
                if ((int)$groupPrice['cust_group'] == (int)$targetGroup->getData('customer_group_id')) {

                   return $groupPrice['website_price']; 
                }
            }
        }
        return null ;
    }
}