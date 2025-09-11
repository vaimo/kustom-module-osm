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
use Klarna\Onsitemessaging\Block\Footer;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * @coversDefaultClass \Klarna\Onsitemessaging\Block\Footer
 */
class FooterTest extends TestCase
{
    /**
     * @var Footer
     */
    private $model;
    /**
     * @var MockObject[]
     */
    private $dependencyMocks;

    public function testShowInFooterEnabledAndValidCountryImpliesReturningTrue(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(true);
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabledOnFooter')
            ->willReturn(true);

        $this->assertTrue($this->model->showInFooter());
    }

    public function testShowInFooterReturnsFalseWhenOsmDisabled(): void
    {
        $this->dependencyMocks['osmConfiguration']
            ->method('isEnabled')
            ->willReturn(false);

        $this->assertFalse($this->model->showInFooter());
    }

    protected function setUp(): void
    {
        $mockFactory   = new MockFactory($this);
        $objectFactory = new TestObjectFactory($mockFactory);
        $storeManager   = $mockFactory->create(StoreManager::class);

        $store = $mockFactory->create(Store::class);
        $storeManager->method('getStore')
            ->willReturn($store);

        $scopeConfig   = $mockFactory->create(
            Config::class,
            ['getValue', 'isSetFlag']
        );
        $context       = $mockFactory->create(Context::class, ['getScopeConfig', 'getStoreManager']);
        $context->method('getStoreManager')->willReturn($storeManager);
        $context->method('getScopeConfig')->willReturn($scopeConfig);
        $this->model = $objectFactory->create(
            Footer::class,
            [
                Context::class => ['getScopeConfig', 'getStoreManager']
            ],
            [
                Context::class => $context
            ]
        );
        $this->dependencyMocks = $objectFactory->getDependencyMocks();

        $quote = $mockFactory->create(Quote::class);
        $address = $mockFactory->create(Address::class);
        $quote->method('getShippingAddress')->willReturn($address);
        $this->dependencyMocks['checkoutSession']->method('getQuote')
            ->willReturn($quote);
    }
}
