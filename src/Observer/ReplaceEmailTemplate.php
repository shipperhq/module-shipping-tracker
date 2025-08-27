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
 * @package ShipperHQ\Tracker
 * @copyright Copyright (c) 2016 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 *
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
