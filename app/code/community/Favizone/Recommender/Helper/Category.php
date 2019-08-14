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
 *
 */
class  Favizone_Recommender_Helper_Category extends Mage_Core_Helper_Abstract
{
    /**
     * Prepares to send the category data to Favizone
     *
     * @param $categoryId Integer
     * @param $operation_key String
     */
    public function sendCategoryData($categoryId, $operation_key, $storeId){

        $access_key = Mage::helper('favizone_recommender/common')->getStoreAccessKey($storeId);
        switch($operation_key) {
            case "update":

                $favizoneCategoryData = $this->getSingleCategoryData($categoryId, $storeId);
                if(count($favizoneCategoryData)>0){
                    $sender = Mage::helper('favizone_recommender/sender');
                    $data = Mage::helper('favizone_recommender/data');
                    $data_to_send =  array(
                        "key"=>$access_key,
                        "category"=>$favizoneCategoryData,
                        "id_category"=>$categoryId
                    );

                    $sender->postRequest($data->getUpdateCategoryUrl(), $data_to_send);
                }
                break;
            case "delete":
                $sender = Mage::helper('favizone_recommender/sender');
                $data = Mage::helper('favizone_recommender/data');
                $data_to_send =  array(
                    "key"=>$access_key,
                    "id_category"=>$categoryId
                );

                $sender->postRequest($data->getDeleteCategoryUrl(), $data_to_send);
                break;
        }
    }

    /**
     * Prepares to send all categories data to Favizone
     *
     * @param $categories array
     */
    public function sendCategoriesData($store_id){

        $sender = Mage::helper('favizone_recommender/sender');
        $data = Mage::helper('favizone_recommender/data');
        $access_key = Mage::helper('favizone_recommender/common')->getStoreAccessKey($store_id);
        $data_to_send =  array(
            "key"=>$access_key,
            "categories"=>$this ->getAllCategories($store_id)
        );
        $sender->postRequest($data->getCategoryUrl(), $data_to_send);
    }

    /**
     * Gets  category data
     *
     *@return array
     */
    public function getCategoryData($categoryId, $storeId){

        $favizoneCategoryData = array();
        $category = Mage::getModel('catalog/category')->setStoreId($storeId)->load($categoryId);
        if(!is_null($category)){

            $isoCode = substr(Mage::getStoreConfig('general/locale/code', $storeId),0,2);
            $isRoot = 0;
            if((int)$category->getLevel()== 1 || (int)$category->getLevel()== 0)
                $isRoot = 1;
            $path = Favizone_Recommender_Helper_Category::getCategoryPath($category->getPath(), $storeId) ;
            $data = array(
                    "idLang"=>$storeId,
                    "isoCode"=>$isoCode,
                    "idCategory"=>$path,
                    "path"=>$path,
                    "nameCategory"=>$category->getName(),
                    "level"=>$category->getLevel(),
                    "isCategoryRoot"=>$isRoot,
                    "url"=>$category->getUrl()
                );
            //image
            if($category->getImageUrl())
                $data['image'] = $category->getImageUrl() ;
            //parentCategory
            $parent = Mage::getModel('catalog/category')->load($category->getParentId()) ;
            $data['idParent'] = Favizone_Recommender_Helper_Category::getCategoryPath($parent->getPath(), $storeId) ;
            array_push($favizoneCategoryData, $data);
        }
        return $favizoneCategoryData; 
    }

    /**
     * Gets all categories data
     *
     *@return array
     */
    protected function getAllCategories($store_id){

        $categories = array();
        $rootCategoryId = Mage::app()->getStore($store_id)->getRootCategoryId();
        $categoriesCollection = Mage::getModel('catalog/category')
            ->getCollection()
             ->setStore($store_id)
            ->addFieldToFilter('is_active', 1)
            ->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"))
            ->addAttributeToSelect('*');


        $isoCode = substr(Mage::getStoreConfig('general/locale/code', $store_id),0,2);
        foreach($categoriesCollection as $category){

            $isRoot = 0;
            if((int)$category->getLevel()== 1 || (int)$category->getLevel()== 0)
                $isRoot = 1;
            $path = Favizone_Recommender_Helper_Category::getCategoryPath($category->getPath(), $storeId);
            $data = array(
                    "idLang"=>$storeId,
                    "isoCode"=>$isoCode,
                    "idCategory"=>$path,
                    "path"=>$path,
                    "nameCategory"=>$category->getName(),
                    "level"=>$category->getLevel(),
                    "isCategoryRoot"=>$isRoot,
                    "url"=>$category->getUrl()
                );
            //image
            if($category->getImageUrl())
                $data['image'] = $category->getImageUrl() ;
            //parentCategory
            $parent = Mage::getModel('catalog/category')->load($category->getParentId()) ;
            $data['idParent'] = Favizone_Recommender_Helper_Category::getCategoryPath($parent->getPath(), $storeId) ;
            array_push($categories, $data) ;
        }

        //Root category
        $rootCategory = Mage::getModel('catalog/category')->setStoreId($store_id)->load($rootCategoryId);
        $path = Favizone_Recommender_Helper_Category::getCategoryPath($rootCategory->getPath(), $storeId) ;
        $data = array(
            "idLang"=>$store_id,
            "isoCode"=>$isoCode,
            "idCategory"=>$path,
            "path"=>$path,
            "idParent"=>$rootCategory->getParentId(),
            "nameCategory"=>$rootCategory->getName(),
            "level"=>$rootCategory->getLevel(),
            "image"=>($rootCategory->getImageUrl()?$rootCategory->getImageUrl():''),
            "isCategoryRoot"=>1
        ) ;
        //image
        if($category->getImageUrl())
            $data['image'] = $rootCategory->getImageUrl() ;  
        array_push($categories, $data);
        /**
         * Sending categories data
         */
        return $categories;
    }

    /**
     * Gets  category data
     *
     *@return array
     */
    protected function getSingleCategoryData($categoryId, $storeId){

        $favizoneCategoryData = array();
        $category = Mage::getModel('catalog/category')->setStoreId($storeId)->load($categoryId);
        if(!is_null($category)){

            $isoCode = substr(Mage::getStoreConfig('general/locale/code', $storeId),0,2);
            $isRoot = 0;
            if((int)$category->getLevel()== 1 || (int)$category->getLevel()== 0)
                $isRoot = 1;
            
            $path = Favizone_Recommender_Helper_Category::getCategoryPath($category->getPath(), $storeId) ;
            $data = array(
                    "idLang"=>$storeId,
                    "isoCode"=>$isoCode,
                    "idCategory"=>$path,
                    "path"=>$path,
                    "nameCategory"=>$category->getName(),
                    "level"=>$category->getLevel(),
                    "isCategoryRoot"=>$isRoot,
                    "url"=>$category->getUrl()
                );
            //image
            if($category->getImageUrl())
                $data['image'] = $category->getImageUrl() ;
            //parentCategory
            $parent = Mage::getModel('catalog/category')->load($category->getParentId()) ;
            $data['idParent'] = Favizone_Recommender_Helper_Category::getCategoryPath($parent->getPath(), $storeId) ;
            array_push($favizoneCategoryData, $data);
        }
        return $favizoneCategoryData;
    }

    public function getCategoryPath($pathStructure, $storeId){

        $pathIds = explode('/', $pathStructure);            
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId($storeId)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active')
            ->addFieldToFilter('entity_id', array('in' => $pathIds));

        $pahtByName = '';
        foreach($collection as $cat){                
            $pahtByName .= '/' . $cat->getName();
        }

        if($pahtByName[0]==='/')
            $pahtByName = substr($pahtByName, 1);

        return $pahtByName ;
    }
}