<?php
/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Onsitemessaging\Test\Unit\Block;

use Klarna\Base\Test\Unit\Mock\MockFactory;
use Klarna\Base\Test\Unit\Mock\TestObjectFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config;
use Klarna\Onsitemessaging\Block\Product;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * @coversDefaultClass \Klarna\Onsitemessaging\Block\Product
 */
class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    /**
     * @covers ::showOnProduct
     */
    public function testShowOnProduct(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(true);
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabledOnProductPage')
            ->willReturn(true);

        $this->assertTrue($this->model->showOnProduct());
    }

    /**
     * @covers ::showOnProduct
     */
    public function testShowOnProductReturnsFalseWhenOsmDisabled(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(false);

        $this->assertFalse($this->model->showOnProduct());
    }

    /**
     * @covers ::showOnProduct
     */
    public function testShowOnProductReturnsFalseWhenProductDisabled(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(true);
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabledOnProductPage')
            ->willReturn(false);

        $this->assertFalse($this->model->showOnProduct());
    }

    /**
     * @covers ::getLocale
     */
    public function testGetLocale(): void
    {
        $this->dependencyMocks['locale']
            ->method('getLocale')
            ->willReturn('en-US');
        $this->assertEquals('en-US', $this->model->getLocale());
    }

    /**
     * @covers ::getTheme
     */
    public function testGetThemeHasValueWithSubstringDarkReturnsDark(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('getTheme')
            ->willReturn('dark_with_badge');

        $this->assertEquals('dark', $this->model->getTheme());
    }

    /**
     * @covers ::getTheme
     */
    public function testGetThemeHasValueWithSubstringLightReturnsLight(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('getTheme')
            ->willReturn('light_with_badge');

        $this->assertEquals('light', $this->model->getTheme());
    }

    public function testGetThemeIsCustomThemeReturnsCustomThemeName(): void
    {
        $expected = 'custom_theme';
        $this->dependencyMocks['osmConfiguration']
            ->method('isCustomTheme')
            ->willReturn(true);
        $this->dependencyMocks['osmConfiguration']
            ->method('getCustomThemeName')
            ->willReturn($expected);

        static::assertEquals($expected, $this->model->getTheme());
    }

    public function testGetKeyThemeIsWithBadgeReturnsValueWithBadge(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('getTheme')
            ->willReturn('light_with_badge');
        $this->assertEquals('credit-promotion-badge', $this->model->getKey());
    }

    public function testGetKeyThemeIsWithoutBadgeReturnsValueWithBadge(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('getTheme')
            ->willReturn('light_without_badge');
        $this->assertEquals('credit-promotion-auto-size', $this->model->getKey());
    }

    /**
     * @covers ::getPurchaseAmount
     */
    public function testGetPurchaseAmountTaxIncluded(): void
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->dependencyMocks['productHelper']
            ->method('getProduct')
            ->willReturn($productMock);
        $productMock->method('getQty')->willReturn(1);
        $productMock->method('getFinalPrice')->willReturn(10.00);

        $amountMock = $this->createMock(AmountInterface::class);
        $amountMock
            ->method('getValue')
            ->willReturn(11.90);
        $amountMock
            ->method('getBaseAmount')
            ->willReturn(10.00);

        $this->dependencyMocks['taxHelper']
            ->method('displayPriceExcludingTax')
            ->willReturn(false);

        $this->dependencyMocks['calculator']
            ->method('getAmount')
            ->willReturn($amountMock);

        $this->assertEquals(1190, $this->model->getPurchaseAmount());
    }

    /**
     * @covers ::getPurchaseAmount
     */
    public function testGetPurchaseAmountTaxExcluded(): void
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->dependencyMocks['productHelper']
            ->method('getProduct')
            ->willReturn($productMock);
        $productMock->method('getQty')->willReturn(1);
        $productMock->method('getFinalPrice')->willReturn(10.00);

        $amountMock = $this->createMock(AmountInterface::class);
        $amountMock
            ->method('getValue')
            ->willReturn(11.90);
        $amountMock
            ->method('getBaseAmount')
            ->willReturn(10.00);

        $this->dependencyMocks['taxHelper']
            ->method('displayPriceExcludingTax')
            ->willReturn(true);

        $this->dependencyMocks['calculator']
            ->method('getAmount')
            ->willReturn($amountMock);

        $this->assertEquals(1000, $this->model->getPurchaseAmount());
    }

    protected function setUp(): void
    {
        $mockFactory   = new MockFactory($this);
        $objectFactory = new TestObjectFactory($mockFactory);
        $storeManager   = $mockFactory->create(
            StoreManager::class,
            [
                'setIsSingleStoreModeAllowed',
                'hasSingleStore',
                'isSingleStoreMode',
                'getStore',
                'getStores',
                'getWebsite',
                'getWebsites',
                'reinitStores',
                'getDefaultStoreView',
                'getGroup',
                'getGroups',
                'setCurrentStore'
            ]
        );
        $store = $mockFactory->create(Store::class);
        $storeManager->method('getStore')
            ->willReturn($store);
        $scopeConfig   = $mockFactory->create(
            Config::class,
            ['getValue', 'isSetFlag']
        );
        $context       = $mockFactory->create(Context::class, ['getScopeConfig', 'getStoreManager']);
        $context->method('getScopeConfig')->willReturn($scopeConfig);
        $context->method('getStoreManager')->willReturn($storeManager);
        $this->model                           = $objectFactory->create(
            Product::class,
            [
                Context::class => ['getScopeConfig', 'getStoreManager']
            ],
            [
                Context::class => $context
            ]
        );
        $this->dependencyMocks                 = $objectFactory->getDependencyMocks();
        $this->dependencyMocks['_scopeConfig'] = $scopeConfig;
        $this->dependencyMocks['_storeManager'] = $storeManager;
        $this->dependencyMocks['_storeManager']->method('getStore')->willReturn('base');

        $quote = $mockFactory->create(Quote::class);
        $address = $mockFactory->create(Address::class);
        $quote->method('getShippingAddress')->willReturn($address);
        $this->dependencyMocks['checkoutSession']->method('getQuote')
            ->willReturn($quote);
    }
}
