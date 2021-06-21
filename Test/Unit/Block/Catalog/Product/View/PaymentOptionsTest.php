<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Latitude\Payment\Test\Unit\Block\Catalog\Product\View;

use Latitude\Payment\Test\Unit\LatitudeTestCase;
use Latitude\Payment\Block\Catalog\Product\View\PaymentOptions;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Latitude\Payment\Helper\Config;
use Magento\Checkout\Model\Session;
use Magento\Cms\Block\Block as CmsBlock;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;

/**
 * @covers \Latitude\Payment\Block\Catalog\Product\View\PaymentOptions
 */
class PaymentOptionsTest extends LatitudeTestCase
{
    /**
     * @var PaymentOptions
     */
    private $paymentOptionsBlock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /** @var Cart|MockObject */
    private $contextMock;

    /** @var LayoutInterface|MockObject */
    private $layoutMock;

    /** @var Config|MockObject */
    private $configHelperMock;

    /** @var PriceCurrencyInterface|MockObject */
    private $priceCurrencyMock;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->contextMock = $this->createPartialMock(Context::class, ['getEscaper', 'getLayout','getRegistry']);
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->escaperMock = $objectManager->getObject(Escaper::class);
        $this->configHelperMock = $this->createMock(Config::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->productMock = $this->createPartialMock(Product::class,['getFinalPrice']);

        $this->contextMock->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
        $this->contextMock->expects($this->once())->method('getLayout')->willReturn($this->layoutMock);
        $this->contextMock->expects($this->once())->method('getRegistry')->willReturn($this->registryMock);
        

        /** @var $paymentOptionsBlock PaymentOptions */
        $this->paymentOptionsBlock = $objectManager->getObject(
            PaymentOptions::class,
            [
                'context'=> $this->contextMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'configHelper' => $this->configHelperMock,
            ]
        );
    }

    public function testGetInstallmentText()
    {
        $amount = '100 AUD';
        $expectedText = __('<span >Starting interest free at <strong>%1 </strong> for <strong>10</strong> payments</span>',$amount);
        $cmsBlockMock = $this->createPartialMock(CmsBlock::class,['toHtml']);
        $cmsBlockMock->expects($this->any())->method('toHtml')->willReturn('<span >Starting interest free at <strong>%1 </strong> for <strong>10</strong> payments</span>');
        $this->layoutMock->expects($this->any())->method('createBlock')->with('Magento\Cms\Block\Block')->willReturn($cmsBlockMock);   
        $this->priceCurrencyMock->expects($this->any())->method('convertAndFormat')->willReturn($amount);
        $this->productMock->expects($this->any())->method('getFinalPrice')->willReturn(100);
        $this->registryMock->expects($this->any())->method('registry')->with('current_product')->willReturn($this->productMock);  
        $this->assertEquals($expectedText, $this->paymentOptionsBlock->getInstallmentText());
    }
}