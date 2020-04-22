<?php
/**
 * @package   Mediarox_IdealoTrackingPixel
 * @copyright Copyright 2020 (c) mediarox UG (haftungsbeschraenkt) (http://www.mediarox.de)
 * @author    Marcus Bernt <mbernt@mediarox.de>
 */

namespace Mediarox\IdealoTrackingPixel\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class IdealoTracking
 */
class IdealoTracking implements ArgumentInterface
{
    const IDEALO_TRACKING_URL = 'https://marketing.net.idealo-partner.com/ts/';
    const PARTNER_CODE_SYSTEM_CONFIG_PATH = 'mediarox_idealo_tracking/general/partner_code';
    const MANUFACTURER_CODE_SYSTEM_CONFIG_PATH = 'mediarox_idealo_tracking/general/manufacturer';
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var Currency
     */
    private $currency;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * IdealoTracking constructor.
     *
     * @param Session              $checkoutSession
     * @param Currency             $currency
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface         $url
     */
    public function __construct(
        Session $checkoutSession,
        Currency $currency,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->currency = $currency;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getIdealoTrackingUrl(): string
    {
        $this->order = $this->checkoutSession->getLastRealOrder();
        $idealoId = $this->checkoutSession->getIdealoId();
        $orderItems = $this->getOrderItemsJson();
        $trackingData = [
            'trc' => 'basket', /* idealo tracking category */
            'ctg' => 'sale', /* name of the conversion target -> idealo */
            'sid' => 'checkout', /* order id of the conversion target -> idealo */
            'cid' => $this->order->getIncrementId(), /* order id */
            'orv' => $this->order->getGrandTotal(), /* product price including taxes */
            'orc' => $this->order->getOrderCurrencyCode(), /* ISO currency */
            'tst' => strtotime($this->order->getCreatedAt()),
            'bsk' => $orderItems, /* filled basket array */
            'cli' => $idealoId,
        ];
        return $this->prepareTrackingUrl($trackingData);
    }

    /**
     * @return string
     */
    private function getOrderItemsJson(): string
    {
        $basketData = [];
        $orderItems = $this->order->getAllItems();
        $manufacturerAttrCode = $this->scopeConfig->getValue(self::MANUFACTURER_CODE_SYSTEM_CONFIG_PATH);
        foreach ($orderItems as $orderItem) {
            $itemData['pid'] = $orderItem->getSku(); /* product id*/
            $itemData['prn'] = $orderItem->getName(); /* product name */
            $itemData['brn'] = $orderItem->getProduct()->getData($manufacturerAttrCode); /* product brand */
            $itemData['pri'] = $this->currency->formatPrecision(
                $orderItem->getPrice(),
                2,
                ['display' => \Zend_Currency::NO_SYMBOL],
                false
            ); /* product price including taxes */
            $itemData['qty'] = (int)$orderItem->getQtyOrdered(); /* ordered quantity */
            $basketData[] = $itemData;
        }
        $jsonBasket = \json_encode($basketData);
        return \urlencode($jsonBasket);
    }

    /**
     * @param array $trackingData
     * @return string
     */
    private function prepareTrackingUrl(array $trackingData): string
    {
        $partnerCode = $this->scopeConfig->getValue(self::PARTNER_CODE_SYSTEM_CONFIG_PATH);
        $baseUrl = self::IDEALO_TRACKING_URL . $partnerCode . DIRECTORY_SEPARATOR . 'tsa';
        return $this->url->getDirectUrl($baseUrl, ['_query' => $trackingData]);
    }
}
