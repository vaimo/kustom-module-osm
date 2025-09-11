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
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * @internal
 */
class Cart extends AbstractPosition
{
    /**
     * Check to see if display on cart is enabled
     *
     * @return bool
     */
    public function showInCart(): bool
    {
        $store = $this->_storeManager->getStore();
        $result = $this->osmConfiguration->isEnabledOnCartPage($store);
        if (!$result) {
            return false;
        }

        return $this->isEnabledAndValidCountry($store);
    }

    /**
     * Get the amount of the purchase formated as an integer `round(amount * 100)`
     *
     * @return int
     */
    public function getPurchaseAmount(): int
    {
        $quote = $this->checkoutSession->getQuote();
        $price = $quote->getGrandTotal();
        return (int)round($price * 100);
    }
}
