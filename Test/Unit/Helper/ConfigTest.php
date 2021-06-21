<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Latitude\Payment\Test\Unit\Helper;

use Latitude\Payment\Test\Unit\LatitudeTestCase;
use Latitude\Payment\Helper\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Latitude\Payment\Helper\Config
 */
class ConfigTest extends LatitudeTestCase
{
    /**
     * Helper
     *
     * @var Config
     */
    private $helper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(['getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStore']
        );

        $storeMock = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getBaseCurrencyCode', 'getCurrentCurrencyCode']
        );
    
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->helper = $this->objectManagerHelper->getObject(
            Config::class,
            [
                'context' => $contextMock,
                'storeRepository' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Test isLatitudepayEnabled()
     */
    public function testIsLatitudepayEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1');

        $this->assertTrue($this->helper->isLatitudepayEnabled());
    }

    /**
     * Test isGenoapayEnabled()
     */
    public function testIsGenoapayEnabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1');

        $this->assertTrue($this->helper->isGenoapayEnabled());
    }
    
    /**
     * Test isDisplayBillingOnPaymentPageAvailable()
     */
    public function testIsDisplayBillingOnPaymentPageAvailable(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1');

        $this->assertTrue($this->helper->isDisplayBillingOnPaymentPageAvailable());
    }

    /**
     * Test getEnvironment()
     */
    public function testGetEnvironment(): void
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('1');

        $this->assertIsString($this->helper->getEnvironment(1));
    }

    /**
     * Test getConfigData()
     */
    public function testGetConfigData(): void
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('1');

        $this->assertIsString($this->helper->getConfigData('field',1));
    }
}