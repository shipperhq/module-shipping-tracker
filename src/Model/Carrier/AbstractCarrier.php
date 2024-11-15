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

namespace ShipperHQ\Tracker\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class AbstractCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier
{

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    private $trackStatusFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    private $trackFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory = $rateResultFactory;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->trackFactory = $trackFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Determins if tracking is set in the admin panel
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

    public function getTrackingInfo($tracking, $postcode = null)
    {
        $result = $this->getTracking($tracking, $postcode);

        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    public function getTracking($trackings, $postcode = null)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->_getCgiTracking($trackings, $postcode);

        return $this->_result;
    }

    protected function setTrackingReqeust()
    {
        $r = $this->dataObjectFactory->create();

        $userId = $this->getConfigData('userid');
        $r->setUserId($userId);

        $this->_rawTrackRequest = $r;
    }

    /** Popup window to tracker **/
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

    public function getCode($type, $code = '')
    {
        $codes = [

            'preurl' => [
                'none' => __('Use Manual Url'),
                'ddt' => __('DDT (Italy)'),
                'post_danmark' => __('PostNord (Denmark)'), // Updated to reflect Post Denmark's rebranding to PostNord
                'tnt' => __('TNT (Global)'), // Updated to indicate global coverage via the main TNT tracking site
                'dhl_de' => __('DHL (Germany)'),
                'dpd_de' => __('DPD (Germany)'),
                'gls' => __('GLS (Germany)'),
                'apc' => __('APC Overnight (UK)'), // Clarified that it's APC Overnight
                'dpd_uk' => __('DPD (UK)'),
                'dhl_uk' => __('DHL (UK)'),
                'fedex' => __('FedEx (Global)'), // Adjusted to reflect broader coverage
                'fedex_us' => __('FedEx (USA)'),
                'parcelforce' => __('Parcelforce (UK)'),
                'royal_mail' => __('Royal Mail (UK)'),
                'uk_mail' => __('DHL Parcel UK'), // Updated to reflect UK Mail's acquisition by DHL
                'tnt_uk' => __('TNT (UK)'),
                'usps_usa' => __('USPS (USA)'),
            ],

            'tracking_url' => [
                'ddt' => 'http://www.DDT.com/portal/pw/track?trackNumber=#TRACKNUM#',
                'post_danmark' => 'https://www.postnord.dk/en/track-and-trace?shipmentid=#TRACKNUM#',
                'tnt' => 'https://www.tnt.com/express/en_gb/site/shipping-tools/tracking.html?searchType=con&cons=#TRACKNUM#',
                'dpd_de' => 'https://tracking.dpd.de/status/en_US/parcel/#TRACKNUM#',
                'dhl_de' => 'https://www.dhl.de/en/privatkunden.html?piececode=#TRACKNUM#',
                'gls' => 'https://gls-group.com/DE/en/parcel-tracking?match=#TRACKNUM#',
                'apc' => 'https://apc-overnight.com/receiving-a-parcel/tracking?ref=#TRACKNUM#',
                'dpd_uk' => 'https://www.dpd.co.uk/tracking?parcelNumber=#TRACKNUM#',
                'dhl_uk' => 'https://www.dhl.com/gb-en/home/tracking.html?tracking-id=#TRACKNUM#',
                'fedex' => 'https://www.fedex.com/apps/fedextrack/?tracknumbers=#TRACKNUM#',
                'fedex_us' => 'https://www.fedex.com/apps/fedextrack/?tracknumbers=#TRACKNUM#',
                'parcelforce' => 'https://www.parcelforce.com/track-trace?trackNumber=#TRACKNUM#',
                'royal_mail' => 'https://www.royalmail.com/track-your-item?trackNumber=#TRACKNUM#',
                'uk_mail' => 'https://www.dhlparcel.co.uk/en/business-users/tracking.html?tracking-id=#TRACKNUM#',
                'tnt_uk' => 'https://www.tnt.com/express/en_gb/site/shipping-tools/tracking.html?searchType=con&cons=#TRACKNUM#',
                'usps_usa' => 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=#TRACKNUM#',
            ],

        ];

        if (!isset($codes[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid Tracking code type: %1.', $type)
            );
        }

        if ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid Tracking code for type %1: %2.', $type, $code)
            );
        }

        return $codes[$type][$code];
    }
}
