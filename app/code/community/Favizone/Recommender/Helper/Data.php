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
*@category  Favizone
* @package   Favizone_Recommender
* @author    Favizone Solutions Ltd <contact@favizone.com>
* @copyright 2015-2016 Favizone Solutions Ltd
*/
class  Favizone_Recommender_Helper_Data extends Mage_Core_Helper_Abstract

{
    private $host = "https://api.favizone.com";
    private $accountUrl = "/user/add-account";
    private $initOrderUrl = "/order/init";
    private $categoryUrl = "/category/categories";
    private $updateCategoryUrl ="/category/update";
    private $deleteCategoryUrl = "/category/delete";
    private $addCategoryUrl = "/category/add";
    private $initProductUrl = "/product/first-init";
    private $updateProductUrl ="/product/update";
    private $AddProductUrl ="/product/add";
    private $deleteProductUrl ="/product/delete";
    private $sendCustomUrl ="/api/custom-data";
    private $sendEventPath = "/api/addEvent";
    private $checkInitUrl = "/product/check-init";
    private $initABTestUrl ="/ab-test/init";
    private $endABTestUrl ="/ab-test/end";
    private $recommendationRendererUrl ="/api/allrecs";
    public  $registerProfiletUrl = "/api/profile/register";
    private $attributes = array("name","description","short_description","sku","weight"
                             ,"news_from_date","old_id","news_to_date","status"
                             ,"url_key","visibility","url_path","country_of_manufacture"
                             ,"category_ids","required_options","has_options"
                             ,"image_label","small_image_label","thumbnail_label"
                             ,"created_at","updated_at","price_type","sku_type"
                             ,"weight_type","shipment_type"
                             ,"links_purchased_separately","samples_title"
                             ,"links_title","links_exist"
                             ,"open_amount_min","open_amount_max","price"
                             ,"group_price","special_price","minimal_price"
                             ,"special_from_date","special_to_date"
                             ,"ost","tier_price","msrp_enabled","manufacturer"
                             ,"msrp_display_actual_price_type","srp"
                             ,"is_redeemable","tax_class_id","use_config_is_redeemable"
                             ,"price_view","lifetime","use_config_lifetime","email_template"
                             ,"use_config_email_template"
                             ,"allow_message","use_config_allow_message"
                             ,"meta_title","meta_keyword"
                             ,"meta_description","is_recurring","recurring_profile"
                             ,"custom_design","custom_design_from","custom_design_to"
                             ,"custom_layout_update","page_layout","options_container"
                             ,"gift_message_available","gift_wrapping_available","gift_wrapp");
    
    /**
     * @return string
     */
    public function getLifeTime()
    {
        $lifeTime =    365 * 24 * 60 * 60;
        return $lifeTime ;
    }

    /**
     * @return string
     */
    public function getInitOrderUrl()
    {
        return $this->host.$this->initOrderUrl;
    }


    /**
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->host.$this->accountUrl;
    }
    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getCategoryUrl()
    {
        return $this->host.$this->categoryUrl;
    }

    /**
     * @return mixed
     */
    public function getUpdateCategoryUrl()
    {
        return $this->host.$this->updateCategoryUrl;
    }

    /**
     * @return string
     */
    public function getDeleteCategoryUrl()
    {
        return $this->host.$this->deleteCategoryUrl;
    }

    /**
     * @return string
     */
    public function getAddCategoryUrl()
    {
        return $this->addCategoryUrl;
    }

    /**
     * @return string
     */
    public function getInitProductUrl()
    {
        return $this->host.$this->initProductUrl;
    }

    /**
     * @return string
     */
    public function getUpdateProductUrl()
    {
        return $this->host.$this->updateProductUrl;
    }

    /**
     * @return string
     */
    public function getAddProductUrl()
    {
        return $this->host.$this->AddProductUrl;
    }

    /**
     * @return string
     */
    public function getDeleteProductUrl()
    {
        return $this->host.$this->deleteProductUrl;
    }

    /**
     * @return string
     */
    public function getSendCustomUrl()
    {
        return $this->sendCustomUrl;
    }

    /**
     * @return string
     */
    public function getSendEventUrl()
    {
        return $this->host.$this->sendEventPath;
    }

    /**
     * @return string
     */
    public function getSendEventPath()
    {
        return $this->sendEventPath;
    }

    /**
     * @return string
     */
    public function getRecommendationRendererUrl()
    {
        return $this->host.$this->recommendationRendererUrl;
    }

    /**
     * @return string
     */
    public function getCheckInitUrl()
    {
        return $this->host.$this->checkInitUrl;
    }

    /**
     * @return string
     */
    public function getInitABTestUrl()
    {
        return $this->host.$this->initABTestUrl;
    }

    /**
     * @return string
     */
    public function getEndABTestUrl()
    {
        return $this->host.$this->endABTestUrl;
    }

    /**
     * @return string
     */
    public function getRegisterProfiletUrl()
    {
        return $this->host.$this->registerProfiletUrl;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}