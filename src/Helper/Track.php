<?php
/**
 * ShipperHQ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * ShipperHQ Tracker
 *
 * @category ShipperHQ
 * @package ShipperHQ_Tracker
 * @copyright Copyright (c) 2016 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 *
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Tracker\Helper;

/**
 * Helper Class Track
 *
 * Retrieves the URL to send in the Email
 *
 * @package ShipperHQ\Tracker\Helper
 */
class Track extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;

    /**
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->shippingConfig = $shippingConfig;
    }

    public function getTrackUrl($title, $trackref = null, $postcode = null)
    {
        if (empty($trackref)) {
            return null;
        }

        $fullUrl = "";

        $carrierInstances = $this->shippingConfig->getAllCarriers();

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
                    if (strpos($taggedUrl, '#SPECIAL#')!== false) {
                        $taggedUrl = str_replace("#SPECIAL#", "", $taggedUrl);
                        $fullUrl = str_replace("#TRACKNUM#", "", $taggedUrl);
                    } else {
                        $fullUrl = str_replace("#TRACKNUM#", $trackref, $taggedUrl);
                        if ($postcode && strpos($taggedUrl, '#POSTCODE#') !== false) {
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
