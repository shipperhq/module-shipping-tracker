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

use Magento\Store\Model\ScopeInterface;

/**
 * Tracker data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Get Config Value
     *
     * @param $configField
     * @return mixed
     */
    public function getConfigValue($configField)
    {
        return $this->scopeConfig->getValue(
            $configField,
            ScopeInterface::SCOPE_STORE
        );
    }
}
