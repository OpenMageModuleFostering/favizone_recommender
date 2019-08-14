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
 *//**
 * Created by PhpStorm.
 * User: marwa
 * Date: 25/01/16
 * Time: 02:47 م
 */
class Favizone_Recommender_Model_AccessKey extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('favizone_recommender/accessKey');
    }

}