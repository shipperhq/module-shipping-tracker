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

namespace ShipperHQ\Tracker\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Tracking\Result;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Psr\Log\LoggerInterface;

class AbstractCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier
{
    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var StatusFactory
     */
    private $trackStatusFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    private $trackFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param StatusFactory $trackStatusFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        StatusFactory $trackStatusFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory = $rateResultFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->trackFactory = $trackFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Determines if tracking is set in the admin panel
     **/
    public function isTrackingAvailable()
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        return true;
    }

    /**
     * Dummy method - need to make this carrier work.
     * But tracker is only used for tracking - not for sending!
     * @param RateRequest $request
     * @return Result|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectRates(RateRequest $request)
    {
        return false;
    }

    /**
     * @param $tracking
     * @param $postcode
     * @return false|mixed|string
     * @throws LocalizedException
     */
    public function getTrackingInfo($tracking, $postcode = null)
    {
        $result = $this->getTracking($tracking, $postcode);

        if ($result instanceof Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * @param $trackings
     * @param $postcode
     * @return \Magento\Shipping\Model\Rate\Result|null
     * @throws LocalizedException
     */
    public function getTracking($trackings, $postcode = null)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->_getCgiTracking($trackings, $postcode);

        return $this->_result;
    }

    /**
     * @return void
     */
    protected function setTrackingReqeust()
    {
        $r = $this->dataObjectFactory->create();

        $userId = $this->getConfigData('userid');
        $r->setUserId($userId);

        $this->_rawTrackRequest = $r;
    }

    /**
     * Popup window to tracker
     *
     * @param $trackings
     * @param $postcode
     * @return void
     * @throws LocalizedException
     */
    protected function _getCgiTracking($trackings, $postcode = null)
    {
        $this->_result = $this->trackFactory->create();

        $defaults = $this->getDefaults();
        foreach ($trackings as $tracking) {
            $status = $this->trackStatusFactory->create();

            $status->setCarrier('Tracker');
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $manualUrl = $this->getConfigData('url');
            $preUrl = $this->getConfigData('preurl');
            if ($preUrl != 'none') {
                $taggedUrl = $this->getCode('tracking_url', $preUrl);
            } else {
                $taggedUrl = $manualUrl;
            }
            if (strpos($taggedUrl, '#SPECIAL#')!== false) {
                $taggedUrl = str_replace("#SPECIAL#", "", $taggedUrl);
                $fullUrl = str_replace("#TRACKNUM#", "", $taggedUrl);
            } else {
                $fullUrl = str_replace("#TRACKNUM#", $tracking, $taggedUrl);
                if ($postcode && strpos($taggedUrl, '#POSTCODE#')!== false) {
                    $fullUrl = str_replace("#POSTCODE#", $postcode, $fullUrl);
                }
            }
            $status->setUrl($fullUrl);
            $this->_result->append($status);
        }
    }

    /**
     * Gets the code and naming
     *
     * @param $type
     * @param $code
     * @return mixed
     * @throws LocalizedException
     */
    public function getCode($type, $code = '')
    {
        $codes = [

            'preurl' => [
                'none' => __('Use Manual Url'),
                'post_danmark' => __('PostNord (Denmark)'),
                'tnt' => __('TNT (now FedEx)'),
                'dhl_de' => __('DHL (Germany)'),
                'dpd_de' => __('DPD (Germany)'),
                'gls' => __('GLS (Germany)'),
                'apc' => __('APC Overnight (UK)'),
                'dpd_uk' => __('DPD (UK)'),
                'dhl_uk' => __('DHL Express (UK)'),
                'fedex' => __('FedEx (Global)'),
                'fedex_us' => __('FedEx (USA)'),
                'parcelforce' => __('Parcelforce Worldwide (UK)'),
                'royal_mail' => __('Royal Mail (UK)'),
                'uk_mail' => __('DHL eCommerce UK'), // formerly DHL Parcel UK / UK Mail
                'usps_usa' => __('USPS (USA)'),
            ],

            'tracking_url' => [
                'post_danmark' => 'https://portal.postnord.com/tracking/details/#TRACKNUM#',
                'tnt' => 'https://www.tnt.com/express/en_gb/site/shipping-tools/tracking.html?searchType=con&cons=#TRACKNUM#',
                'dpd_de' => 'https://tracking.dpd.de/status/en_GB/parcel/#TRACKNUM#',
                'dhl_de' => 'https://www.dhl.de/track?piececode=#TRACKNUM#',
                'gls' => 'https://gls-group.com/EU/en/parcel-tracking?match=#TRACKNUM#',
                'apc' => 'https://apc-overnight.com/receiving-a-parcel/tracking?ref=#TRACKNUM#',
                'dpd_uk' => 'https://track.dpd.co.uk/search?reference=#TRACKNUM#',
                'dhl_uk' => 'https://www.dhl.com/gb-en/home/tracking.html?tracking-id=#TRACKNUM#',
                'fedex' => 'https://www.fedex.com/apps/fedextrack/?tracknumbers=#TRACKNUM#',
                'fedex_us' => 'https://www.fedex.com/apps/fedextrack/?tracknumbers=#TRACKNUM#',
                'parcelforce' => 'https://www.parcelforce.com/track-trace?trackNumber=#TRACKNUM#',
                'royal_mail' => 'https://www.royalmail.com/track-your-item#/tracking-results/#TRACKNUM#',
                'uk_mail' => 'https://track.dhlparcel.co.uk/?con=#TRACKNUM#',
                'usps_usa' => 'https://tools.usps.com/go/TrackConfirmAction?tLabels=#TRACKNUM#',
            ]
        ];

        if (!isset($codes[$type])) {
            throw new LocalizedException(
                __('Invalid Tracking code type: %1.', $type)
            );
        }

        if ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw new LocalizedException(
                __('Invalid Tracking code for type %1: %2.', $type, $code)
            );
        }

        return $codes[$type][$code];
    }
}
