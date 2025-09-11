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
use Magento\Framework\App\Config;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Klarna\Onsitemessaging\Block\Cart;
use Magento\Store\Model\Store;
use Magento\Directory\Model\Currency;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * @coversDefaultClass \Klarna\Onsitemessaging\Block\Cart
 */
class CartTest extends TestCase
{
    /**
     * @var Cart
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;
    /**
     * @var Address
     */
    private Address $address;

    /**
     * @covers ::showInCart
     */
    public function testShowInCartEnabledAndNoCountryGivenImpliesReturnsTrue(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(true);
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabledOnCartPage')
            ->willReturn(true);
        $this->address->method('getCountryId')
            ->willReturn('');

        $this->assertTrue($this->model->showInCart());
    }

    public function testShowInCartEnabledAndValidCountryGivenImpliesReturnsTrue(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(true);
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabledOnCartPage')
            ->willReturn(true);
        $this->address->method('getCountryId')
            ->willReturn('DE');
        $this->dependencyMocks['generalConfig']->expects(static::once())
            ->method('isCountryAllowed')
            ->willReturn(true);

        $this->assertTrue($this->model->showInCart());
    }

    public function testShowInCartEnabledAndInvalidCountryGivenImpliesReturnsFalse(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(true);
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabledOnCartPage')
            ->willReturn(true);
        $this->address->method('getCountryId')
            ->willReturn('DE');
        $this->dependencyMocks['generalConfig']->expects(static::once())
            ->method('isCountryAllowed')
            ->willReturn(false);

        $this->assertFalse($this->model->showInCart());
    }

    public function testShowInCartDisabledOnPageImpliesReturningFalse(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(false);
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabledOnCartPage')
            ->willReturn(false);

        $this->assertFalse($this->model->showInCart());
    }

    /**
     * @covers ::getPurchaseAmount
     */
    public function testGetPurchaseAmount(): void
    {
        $this->assertEquals(12345, $this->model->getPurchaseAmount());
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
        $currency = $mockFactory->create(Currency::class);
        $currency->method('getCode')
            ->willReturn('EUR');
        $store = $mockFactory->create(Store::class);
        $store->method('getCurrentCurrency')
            ->willReturn($currency);

        $storeManager->method('getStore')
            ->willReturn($store);

        $scopeConfig   = $mockFactory->create(
            Config::class,
            ['getValue', 'isSetFlag']
        );
        $context       = $mockFactory->create(Context::class, ['getScopeConfig', 'getStoreManager']);
        $context->method('getStoreManager')->willReturn($storeManager);
        $context->method('getScopeConfig')->willReturn($scopeConfig);
        $this->model                           = $objectFactory->create(
            Cart::class,
            [
                Context::class => ['getScopeConfig', 'getStoreManager']
            ],
            [
                Context::class => $context
            ]
        );
        $this->dependencyMocks                  = $objectFactory->getDependencyMocks();
        $this->dependencyMocks['_storeManager'] = $storeManager;
        $this->dependencyMocks['_scopeConfig']  = $scopeConfig;
        $this->dependencyMocks['_storeManager']->method('getStore')->willReturn('base');

        $quote = $mockFactory->create(
            Quote::class,
            ['getShippingAddress', 'isVirtual'],
            ['getGrandTotal']
        );
        $this->address = $mockFactory->create(Address::class);
        $quote->method('getShippingAddress')->willReturn($this->address);
        $quote->method('getGrandTotal')->willReturn(123.45);
        $this->dependencyMocks['checkoutSession']->method('getQuote')
            ->willReturn($quote);
    }
}
