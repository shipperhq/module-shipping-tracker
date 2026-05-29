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

namespace ShipperHQ\Tracker\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use ShipperHQ\Tracker\Helper\Data;

/**
 * Tracker observer to replace the email template
 */
class ReplaceEmailTemplate implements ObserverInterface
{
    /**
     * @var Data
     */
    private $trackerDataHelper;

    /**
     * ReplaceEmailTemplate constructor.
     * @param Data $shipperDataHelper
     */
    public function __construct(
        Data $shipperDataHelper
    ) {
        $this->trackerDataHelper = $shipperDataHelper;
    }

    /**
     * Record order shipping information after order is placed
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->trackerDataHelper->getConfigValue('carriers/shqtracker1/active')) {
            $blockentity = $observer->getBlock();
            if ($blockentity->getTemplate() == 'Magento_Sales::email/shipment/track.phtml') {
                $blockentity->setTemplate('ShipperHQ_Tracker::email/shipment/track.phtml');
            }
        }
    }
}
