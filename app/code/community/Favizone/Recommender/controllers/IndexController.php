<?php
/**
 * 2016 Favizone Solutions Ltd
 *
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com  for more information.
 *
 * Favizone Admin  controller
 *
 * @category  Favizone
 * @package   Favizone_Recommender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */

class Favizone_Recommender_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
      echo $this->getRequest()->getParam('limit');
    //  echo Mage::app()->getStore()->getId();
    //  echo "Favizone Recommender index";
    }
}