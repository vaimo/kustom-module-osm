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
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Klarna\Base\Model\Api\MagentoToKlarnaLocaleMapper;
use Magento\Checkout\Model\Session;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @internal
 */
abstract class AbstractPosition extends Template
{
    /**
     * @var MagentoToKlarnaLocaleMapper
     */
    protected MagentoToKlarnaLocaleMapper $localeResolver;
    /**
     * @var Osm
     */
    protected Osm $osmConfiguration;
    /**
     * @var General
     */
    private General $generalConfig;
    /**
     * @var Session
     */
    protected Session $checkoutSession;

    /**
     * @param Context $context
     * @param array $data
     * @param MagentoToKlarnaLocaleMapper $localeResolver
     * @param Osm $osmConfiguration
     * @param General $generalConfig
     * @param Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        array $data,
        MagentoToKlarnaLocaleMapper $localeResolver,
        Osm $osmConfiguration,
        General $generalConfig,
        Session $checkoutSession
    ) {
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
        $this->osmConfiguration = $osmConfiguration;
        $this->generalConfig = $generalConfig;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get the locale according to ISO_3166-1 standard
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->localeResolver->getLocale($this->_storeManager->getStore());
    }

    /**
     * Get theme
     *
     * @return string
     */
    public function getTheme(): string
    {
        if ($this->osmConfiguration->isCustomTheme($this->_storeManager->getStore())) {
            return $this->osmConfiguration->getCustomThemeName($this->_storeManager->getStore());
        }

        $theme = $this->osmConfiguration->getTheme($this->_storeManager->getStore());

        if (str_contains($theme, 'dark')) {
            return 'dark';
        }
        if (str_contains($theme, 'light')) {
            return 'light';
        }

        return '';
    }

    /**
     * Getting back the key
     *
     * @return string
     */
    public function getKey(): string
    {
        $theme = $this->osmConfiguration->getTheme($this->_storeManager->getStore());
        if (str_contains($theme, 'with_badge')) {
            return 'credit-promotion-badge';
        }

        return 'credit-promotion-auto-size';
    }

    /**
     * Returns true if KEC is enabled and the country is allowed
     *
     * @param StoreInterface $store
     * @return bool
     */
    protected function isEnabledAndValidCountry(StoreInterface $store): bool
    {
        $result = $this->osmConfiguration->isEnabled($store);
        if (!$result) {
            return false;
        }

        $quote = $this->checkoutSession->getQuote();
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $quoteCountry = $address->getCountryId();
        if (!$quoteCountry) {
            return true;
        }

        return $this->generalConfig->isCountryAllowed($store, $quoteCountry);
    }
}
