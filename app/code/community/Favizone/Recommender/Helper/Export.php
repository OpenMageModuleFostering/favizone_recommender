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
class  Favizone_Recommender_Helper_Export extends Mage_Core_Helper_Abstract
{

    /**
     * @param $store
     * @param $product_collection
     */
    public function exportDataToCsv($store, $product_collection) {

        $fopen = fopen('var/export/favizone_products_catalog_'.$store->getId().'_'.$store->getName().'.csv', 'w');
        $csvHeader = array("id_shop","identifier","reference", "description",
        "shortDescription","price","wholesale_price","cover",
        "currency","url","stock","available_for_order",
        "active","brand","published_date",
        "categoriesNames","categories","tags",
        "lang","title","isNew","isNew_from_date","isNew_to_date",
        "isReduced","reduction_type","price_without_reduction",
        "reduction","reduction_from_date","reduction_expiry_date","reduction_tax",
        "hasDeclination","facets");

        fputcsv( $fopen , $csvHeader,",");
        fwrite($fopen, $product_collection);
        fclose($fopen);
    }
}