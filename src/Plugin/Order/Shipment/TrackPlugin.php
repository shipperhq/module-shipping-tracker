<?php
/* ExtName
 *
 * User        karen
 * Date        8/14/16
 * Time        4:29 PM
 * @category   Webshopapps
 * @package    Webshopapps_Tracker
 * @copyright   Copyright (c) 2016 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2016, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


namespace WebShopApps\Tracker\Plugin\Order\Shipment;


class TrackPlugin {

    public function afterGetNumberDetail(\Magento\Shipping\Model\Order\Track $trackObj, $result) {

        // check its the tracker carrier
        if (strpos($this->getCarrierCode(),'tracker')!== false && 
            strpos($result, 'No detail for number') !== false) {
            // didn't find tracking details, lets see if we can get from our extn
            $carrierInstance = $this->_carrierFactory->create($this->getCarrierCode());
            if (!$carrierInstance) {
                $custom = [];
                $custom['title'] = $this->getTitle();
                $custom['number'] = $this->getTrackNumber();
                return $custom;
            } else {
                $carrierInstance->setStore($this->getStore());
            }

            $rplChars = array(" " => '');
            $string = $this->getShipment()->getShippingAddress()->getPostcode();
            $postcode = strtr($string,$rplChars);
            
            $trackingInfo = $carrierInstance->getTrackingInfo($this->getNumber(), $postcode);
            if (!$trackingInfo) {

                return Mage::helper('sales')->__('No detail for number "%s"', $this->getNumber());
            }

            return $trackingInfo;
        }
        return $result;

    }

}