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
use Magento\Store\Model\ScopeInterface;

/**
 * Tracker data helper
 */
class Data extends AbstractHelper
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
