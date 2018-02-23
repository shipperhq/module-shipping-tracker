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
namespace ShipperHQ\Tracker\Model\Config\Source;

class Preurl implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \ShipperHQ\Tracker\Model\Carrier\Tracker1
     */
    protected $_carrierTracker;

    /**
     * @param \ShipperHQ\Tracker\Model\Carrier\Tracker1 $carrierTracker1
     */
    public function __construct(\ShipperHQ\Tracker\Model\Carrier\Tracker1 $carrierTracker1)
    {
        $this->_carrierTracker = $carrierTracker1;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->_carrierTracker->getCode('preurl') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        return $arr;
    }
}
