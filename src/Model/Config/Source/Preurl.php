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

namespace ShipperHQ\Tracker\Model\Config\Source;

use ShipperHQ\Tracker\Model\Carrier\Tracker1;

class Preurl implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Tracker1
     */
    private $carrierTracker;

    /**
     * @param Tracker1 $carrierTracker1
     */
    public function __construct(Tracker1 $carrierTracker1)
    {
        $this->carrierTracker = $carrierTracker1;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->carrierTracker->getCode('preurl') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        return $arr;
    }
}
