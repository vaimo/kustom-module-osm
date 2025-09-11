<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Onsitemessaging\Block;

use Klarna\AdminSettings\Model\Configurations\General;
use Klarna\AdminSettings\Model\Configurations\Osm;
use Klarna\Base\Model\Api\MagentoToKlarnaLocaleMapper;
use Magento\Catalog\Helper\Data;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;

/**
 * @internal
 */
class Product extends AbstractPosition
{
    /**
     * @var Data
     */
    private $productHelper;
    /**
     * @var Calculator
     */
    private $calculator;
    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param Context $context
     * @param MagentoToKlarnaLocaleMapper $locale
     * @param Data $productHelper
     * @param Calculator $calculator
     * @param TaxHelper $taxHelper
     * @param Osm $osmConfiguration
     * @param General $generalConfig
     * @param Session $checkoutSession
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        MagentoToKlarnaLocaleMapper $locale,
        Data $productHelper,
        Calculator $calculator,
        TaxHelper $taxHelper,
        Osm $osmConfiguration,
        General $generalConfig,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data, $locale, $osmConfiguration, $generalConfig, $checkoutSession);
        $this->productHelper = $productHelper;
        $this->calculator = $calculator;
        $this->taxHelper = $taxHelper;
    }

    /**
     * Check to see if display on product is enabled or not
     *
     * @return bool
     */
    public function showOnProduct(): bool
    {
        $store = $this->_storeManager->getStore();
        return $this->osmConfiguration->isEnabledOnProductPage($store) && $this->isEnabledAndValidCountry($store);
    }

    /**
     * Get the amount of the purchase formated as an integer `round(amount * 100)`
     *
     * @return int
     */
    public function getPurchaseAmount(): int
    {
        $product = $this->productHelper->getProduct();
        $productPrice   = $product->getFinalPrice($product->getQty());

        $amount = $this->calculator->getAmount($productPrice, $product);
        $price = $amount->getValue();
        if ($this->taxHelper->displayPriceExcludingTax()) {
            $price = $amount->getBaseAmount();
        }
        return (int)round($price * 100);
    }
}
