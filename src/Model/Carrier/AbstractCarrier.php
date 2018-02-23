<?php

/* ExtName
 *
 * User        karen
 * Date        8/10/16
 * Time        1:40 PM
 * @category   Webshopapps
 * @package    Webshopapps_ExtnName
 * @copyright   Copyright (c) 2015 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2015, Zowta, LLC - US license
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

namespace WebShopApps\Tracker\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class AbstractCarrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier
{

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    protected $_trackStatusFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    protected $_trackFactory;



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
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->_rateResultFactory = $rateResultFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        $this->_trackFactory = $trackFactory;

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
            $trackings = array($trackings);
        }
        $this->_getCgiTracking($trackings, $postcode);

        return $this->_result;
    }

    protected function setTrackingReqeust()
    {
        $r = new \Magento\Framework\DataObject();

        $userId = $this->getConfigData('userid');
        $r->setUserId($userId);

        $this->_rawTrackRequest = $r;
    }

    /** Popup window to tracker **/
    protected function _getCgiTracking($trackings, $postcode = null)
    {

        $this->_result = $this->_trackFactory->create();

        //try

        $defaults = $this->getDefaults();
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();

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
            if (strpos($taggedUrl, '#SPECIAL#')) {
                $taggedUrl = str_replace("#SPECIAL#", "", $taggedUrl);
                $fullUrl = str_replace("#TRACKNUM#", "", $taggedUrl);
            } else {
                $fullUrl = str_replace("#TRACKNUM#", $tracking, $taggedUrl);
                if ($postcode && strpos($taggedUrl, '#POSTCODE#')) {
                    $fullUrl = str_replace("#POSTCODE#", $postcode, $fullUrl);
                }
            }
            $status->setUrl($fullUrl);
            //  $status->setUrl("http://www.parcelforce.com/portal/pw/track?trackNumber=$tracking");
            $this->_result->append($status);
        }
    }


    public function getCode($type, $code = '')
    {
        $codes = array(

            'preurl' => array(
                'none' => __('Use Manual Url'),
                'ddt' => __('DDT (Italy)'),
                'post_danmark' => __('Post Danmark (Denmark)'),
                'tnt' => __('TNT (Netherlands)'),
                'dhl_de' => __('DHL (Germany)'),
                'dpd_de' => __('DPD (Germany)'),
                'gls' => __('GLS (Germany)'),
                'apc' => __('APC (UK)'),
                'dpd_uk' => __('DPD (UK)'),
                'dhl_uk' => __('DHL (UK)'),
                'fedex' => __('Fedex (UK)'),
                'fedex_us' => __('Fedex (USA)'),
                'parcelforce' => __('Parcelforce (UK)'),
                'royal_mail' => __('Royal Mail (UK)'),
                'uk_mail' => __('UK Mail (UK)'),
                'tnt_uk' => __('TNT (UK)'),
                'usps_usa' => __('USPS (USA)'),
            ),

            'tracking_url' => array(
                'ddt' => 'http://www.DDT.com/portal/pw/track?trackNumber=#TRACKNUM#',
                'post_danmark' => 'http://www.postdanmark.dk/tracktrace/TrackTrace.do?i_stregkode=#TRACKNUM#&i;_lang=IND',
                'tnt' => 'https://securepostplaza.tntpost.nl/TPGApps/tracktrace/findByBarcodeServlet?BARCODE=#TRACKNUM#&ZIPCODE=#POSTCODE#',
                'dpd_de' => 'http://extranet.dpd.de/cgi-bin/delistrack?pknr=#TRACKNUM#&typ=1&lang=de',
                'dhl_de' => 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=#TRACKNUM#',
                'gls' => 'http://www.gls-group.eu/276-I-PORTAL-WEB/content/GLS/DE03/DE/5001.htm?txtAction=71000&txtQuery=#TRACKNUM#&x=0&y=0',
                'apc' => 'https://emea.netdespatch.com/mba/9116x0/track/?type=1&ref=#TRACKNUM#',
                'dpd_uk' => 'http://www.dpd.co.uk/tracking/trackingSearch.do?search.searchType=0&search.parcelNumber=#TRACKNUM#',
                'dhl_uk' => 'http://www.dhl.co.uk/content/gb/en/express/tracking.shtml?brand=DHL&AWB=#TRACKNUM#',
                'fedex' => 'http://www.fedexuk.net/accounts/QuickTrack.aspx?consignment=#TRACKNUM#',
                'fedex_us' => 'http://www.fedex.com/Tracking?language=english&cntry_code=us&tracknumbers=#TRACKNUM#',
                'parcelforce' => 'http://www.parcelforce.com/portal/pw/track?trackNumber=#TRACKNUM#',
                'royal_mail' => 'http://www.royalmail.com/track-trace/?trackNumber=#TRACKNUM#',
                'uk_mail' => 'http://www.business-post.com/scripts/wsisa.dll/ws_quickpod.html?lc_SearchValue=#TRACKNUM#',
                'tnt_uk' => 'http://www.kiosk.tnt.com/webshipper/doTrack.asp?C=#TRACKNUM#&L=EN',
                'usps_usa' => 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=#TRACKNUM#',
            ),

        );

        if (!isset($codes[$type])) {
            throw new LocalizedException(__('Invalid Tracking code type: %1.', $type));
        }

        if ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw new LocalizedException(__('Invalid Tracking code for type %1: %2.', $type, $code));
        }

        return $codes[$type][$code];
    }

}
    
