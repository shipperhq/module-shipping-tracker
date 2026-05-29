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

class TrackPlugin
{
    public function afterGetNumberDetail(\Magento\Shipping\Model\Order\Track $trackObj, $result)
    {
        // check its the tracker carrier
        if (strpos($this->getCarrierCode(), 'tracker')!== false &&
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

            $rplChars = [" " => ''];
            $string = $this->getShipment()->getShippingAddress()->getPostcode();
            $postcode = strtr($string, $rplChars);

            $trackingInfo = $carrierInstance->getTrackingInfo($this->getNumber(), $postcode);
            if (!$trackingInfo) {
                return __('No detail for number "%s"', $this->getNumber());
            }

            return $trackingInfo;
        }

        return $result;
    }
}
