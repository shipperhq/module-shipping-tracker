<?php
/* ExtName
 *
 * User        karen
 * Date        8/14/16
 * Time        11:35 PM
 * @category   Webshopapps
 * @package    Webshopapps_ExtnName
 * @copyright   Copyright (c) 2015 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2015, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

namespace WebShopApps\Tracker\Helper;


/**
 * Helper Class Track
 *
 * Retrieves the URL to send in the Email
 *
 * @package Webshopapps\Tracker\Helper
 */
class Track extends  \Magento\Framework\App\Helper\AbstractHelper
{


    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->_shippingConfig = $shippingConfig;
    }

    public function getTrackUrl($title, $trackref = null, $postcode = null)
    {
        if (empty($trackref)) {
            return null;
        }

        $fullUrl = "";

        $carrierInstances = $this->_shippingConfig->getAllCarriers();

        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                if ($carrier->getConfigData('title') == $title) {
                    $manualUrl = $carrier->getConfigData('url');
                    $preUrl = $carrier->getConfigData('preurl');
                    if ($preUrl != 'none') {
                        $taggedUrl = $carrier->getCode('tracking_url', $preUrl);
                    } else {
                        $taggedUrl = $manualUrl;
                    }
                    if (strpos($taggedUrl, '#SPECIAL#')) {
                        $taggedUrl = str_replace("#SPECIAL#", "", $taggedUrl);
                        $fullUrl = str_replace("#TRACKNUM#", "", $taggedUrl);
                    } else {
                        $fullUrl = str_replace("#TRACKNUM#", $trackref, $taggedUrl);
                        if ($postcode && strpos($taggedUrl, '#POSTCODE#')) {
                            $fullUrl = str_replace("#POSTCODE#", $postcode, $fullUrl);
                        }
                    }

                    break;
                }
            }
        }

        return $fullUrl;
    }

}