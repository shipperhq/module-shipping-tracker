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

namespace ShipperHQ\Tracker\Model\Carrier;

class Tracker2 extends \ShipperHQ\Tracker\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'shqtracker2';

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['shqtracker2' => $this->getConfigData('name')];
    }
}
