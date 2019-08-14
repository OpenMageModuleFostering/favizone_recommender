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
class  Favizone_Recommender_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * @var int the limit of items to fetch.
     */
    public $limit = 500;

    /**
     * @var int the offset of items to fetch.
     */
    public $offset = 1;

	/**
	 * Sends the paginated products data to Favizone
	 */
	public function initTaggingProductData(Mage_Core_Model_Store $store){

			$init_done = false;
			$number_products= $this->getCountAvailableProducts( $store->getId());
      $pagination = $number_products/$this->limit;
      $pagination = (int)$pagination;
      if($number_products%$this->limit>0)
          $pagination += 1;
      $meta = Mage::getModel('favizone_recommender/meta_product');
      /** Sending paginated products data **/
      while($this->offset <= $pagination){
          $products_collection = array();
          foreach ($this->getPaginatedProducts($store->getId()) as $product){

              $favizone_product= $meta->loadProductData($product, $store);
              array_push($products_collection, $favizone_product);
          }

          $this->offset += 1;
          if($this->offset > $pagination)
              $init_done = true;
          $this -> sendInitProductData($products_collection, $store->getId(), $init_done);
      }

		return "done";
	}

  public function exportProducts($store, $limit, $offset){

    $meta = Mage::getModel('favizone_recommender/meta_product');
    //$products_collection = array();
    $products_collection = "";
    foreach ($this->getPaginatedProducts($store->getId(), $limit, $offset) as $product){
      $favizone_product= $meta->loadProductDataToCsv($product, $store);
      //array_push($products_collection, implode(",", $favizone_product));
      $products_collection .=  $favizone_product."\r\n";
    }
    return $products_collection;
  }

	/**
	 * Sends product's event data
	 *
	 * @param $product_identifier integer
	 * @param $event_key String
	 */
	public function sendProductEvent($product_identifier, $event_key, $store_id){

		$events = array();
        $access_key = Mage::helper('favizone_recommender/common')->getStoreAccessKey($store_id);
        $helper = Mage::helper('favizone_recommender/common');
        $session_identifier = $helper->getSessionIdentifier();
        if($session_identifier != "anonymous" ){

        	switch($event_key){

			case 'addToCart':

				$event = '';
				$event.= $helper->getTestingVersion().' ';
				$event.= $helper->getSessionIdentifier().' ';
				$event.= $event_key.' ';
				$event.=$product_identifier.' ';
				$event.="1 1";
				$cart = Mage::getModel('checkout/cart')->getQuote();
				$cart_id = $cart->getId();
				$event.=" 1 1 ".$cart_id;
				array_push($events, $event);

			}

			$data_to_send =  array( "key"=>$access_key,
									"events" => $events,
									"session"=>$helper->getSessionIdentifier(),
									"cart"=>$helper->getCurrentCart());

			$sender = Mage::helper('favizone_recommender/sender');
			$data = Mage::helper('favizone_recommender/data');

			$sender->postRequest($data->getSendEventUrl(), $data_to_send);
        }

	}

	/**
	 * gets  paginated products data
	 *
	 */
	protected function getPaginatedProducts($store_id , $limitProducts = null, $offsetProducts = null){
    if($limitProducts == null){
      $limitProducts = $this->limit;
    }
    if($offsetProducts == null){
      $offsetProducts = $this->offset;
    }
    $collection  = Mage::getModel('favizone_recommender/product')->getCollection();
    $products = $collection
                    ->setStore($store_id)
                    ->addStoreFilter($store_id)
                    ->addAttributeToSelect('*')
                   ->addAttributeToFilter(
                          'status', array(
                              'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                          )
                      )
                      ->addFieldToFilter(
                          'visibility',
                          Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                      ) 
                      ->addUrlRewrite()
                      ->setPageSize($limitProducts)
                      ->setCurPage($offsetProducts);

	/*	$products = Mage::getResourceModel('catalog/product_collection')
           ->setStoreId($store_id)
      			->addAttributeToSelect('*')
      			->addAttributeToFilter(
      				'status', array(
      					'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED
      				)
      			)
      			->addFieldToFilter(
      				'visibility',
      				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
      			)

      			->addUrlRewrite()
      			->setPageSize($limitProducts)
      			->setCurPage($offsetProducts);*/


		return $products;
	}

	/**
	 * Gets all available products number
	 */
	public function getCountAvailableProducts($store_id){

    $collection  = Mage::getModel('favizone_recommender/product')->getCollection();
    return $collection->addStoreFilter($store_id)
                   ->addAttributeToFilter(
                          'status', array(
                              'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                          )
                      )
                      ->addFieldToFilter(
                          'visibility',
                          Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                      )->count();
	}


	protected function sendCheckInitProduct($store_id){

		$sender = Mage::helper('favizone_recommender/sender');
		$data = Mage::helper('favizone_recommender/data');
        $access_key = Mage::helper('favizone_recommender/common')->getStoreAccessKey($store_id);

		$data_to_send =  array( "key"=>$access_key
                                ,"cms_version"=>("1.9")
								,"cms_name"=>"magento");

		$result = $sender->postRequest($data->getCheckInitUrl(), $data_to_send);
		return json_decode($result) ;
	}


	public function updateTaggingProductData($product, $operation_key, $store_id){

		$sender = Mage::helper('favizone_recommender/sender');
		$data = Mage::helper('favizone_recommender/data');
        $access_key = Mage::helper('favizone_recommender/common')->getStoreAccessKey($store_id);

        switch($operation_key){

			case "update":
				$data_to_send =  array( "key"=>$access_key,
										"product" => json_encode($product));
				$sender->postRequest($data->getUpdateProductUrl(), $data_to_send);
				break;
			case "add":
                $data_to_send =  array( "key"=>$access_key,
                                        "product" => json_encode($product));
                $sender->postRequest($data->getAddProductUrl(), $data_to_send);
                break;
			case "delete":
				$data_to_send =  array( "key"=>$access_key,
										"product" => $product->getId());
				$sender->postRequest($data->getDeleteProductUrl(), $data_to_send);
				break;
		}
	}

	protected function sendInitProductData($products, $store_id, $init_done = false){


		$sender = Mage::helper('favizone_recommender/sender');
		$data = Mage::helper('favizone_recommender/data');

        $access_key = Mage::helper('favizone_recommender/common')->getStoreAccessKey($store_id);
		$data_to_send =  array( "key"=>$access_key,
								"init_done" => $init_done,
								"products" => json_encode($products));
		$sender->postRequest($data->getInitProductUrl(), $data_to_send);
	}

}