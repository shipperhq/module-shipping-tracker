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

namespace ShipperHQ\Tracker\Plugin\Order\Shipment;

use Magento\Shipping\Model\CarrierFactory;

class TrackPlugin
{
    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @param CarrierFactory $carrierFactory
     */
    public function __construct(CarrierFactory $carrierFactory)
    {
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * Retrieve tracking details with the shipment postcode.
     *
     * @param \Magento\Shipping\Model\Order\Track $trackObj
     * @param mixed $result
     * @return mixed
     */
    public function afterGetNumberDetail(\Magento\Shipping\Model\Order\Track $trackObj, $result)
    {
        // check its the tracker carrier
        if (strpos($trackObj->getCarrierCode(), 'tracker') !== false &&
            strpos($result, 'No detail for number') !== false
        ) {
            // didn't find tracking details, lets see if we can get from our extn
            $carrierInstance = $this->carrierFactory->create($trackObj->getCarrierCode());
            if (!$carrierInstance) {
                $custom = [];
                $custom['title'] = $trackObj->getTitle();
                $custom['number'] = $trackObj->getTrackNumber();
                return $custom;
            } else {
                $carrierInstance->setStore($trackObj->getStore());
            }

            $rplChars = [" " => ''];
            $string = $trackObj->getShipment()->getShippingAddress()->getPostcode();
            $postcode = strtr($string, $rplChars);

            $trackingInfo = $carrierInstance->getTrackingInfo($trackObj->getNumber(), $postcode);
            if (!$trackingInfo) {
                return __('No detail for number "%s"', $trackObj->getNumber());
            }

            return $trackingInfo;
        }

        return $result;
    }
}
