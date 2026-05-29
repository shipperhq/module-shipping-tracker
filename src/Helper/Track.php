<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Tracker
 * @copyright Copyright (c) 2016 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Tracker\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Shipping\Model\Config;

/**
 * Helper Class Track
 *
 * Retrieves the URL to send in the Email
 */
class Track extends AbstractHelper
{

    /**
     * @var Config
     */
    private $shippingConfig;

    /**
     * @param Config $shippingConfig
     */
    public function __construct(
        Config $shippingConfig
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
