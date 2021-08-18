<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Latitude\Payment\Test\Unit\Model\Payment;

use Latitude\Payment\Test\Unit\LatitudeTestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Latitude\Payment\Model\Payment\Genoapay;
use Magento\Payment\Block\Info as InfoBlock;
use Magento\Payment\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method;
use Magento\Payment\Model\Method\Substitution;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\Info as InfoModel;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class GenoapayTest extends LatitudeTestCase
{
    /** @var ObjectManager */
    protected $objectManagerHelper;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;
    
    /**
     * @var Genoapay
     */
    private $genoapay;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $paymentDataMock = $this->createMock(Data::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStore']
        );
        
        $this->storeMock = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getBaseCurrencyCode', 'getCurrentCurrencyCode']
        );

        $this->storeMock
            ->method('getCurrentCurrencyCode')
            ->willReturn('NZD');
    
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);

        $this->genoapay = $this->objectManagerHelper->getObject(
            Genoapay::class,
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * Test getInfoBlockType()
     */
    public function testGetInfoBlockType()
    {
        $this->assertEquals(InfoBlock::class, $this->genoapay->getInfoBlockType());
    }

    /**
     * Test isActive()
     */
    public function testIsActive()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('1');
        $this->assertTrue($this->genoapay->isActive());
    }

    /**
     * Test isNotActive()
     */
    public function testIsNotActive()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('0');
        $this->assertFalse($this->genoapay->isActive());
    }

    /**
     * Test isAvailable()
     */
    public function testIsAvailable()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('1');
        $this->assertTrue($this->genoapay->isAvailable());
    }

    /**
     * Test isNotAvailable()
     */
    public function testIsNotAvailable()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('0');
        $this->assertFalse($this->genoapay->isAvailable());
    }

    /**
     * Test canUseForCountry()
     */
    public function testCanUseForCountry()
    {
        $this->assertTrue($this->genoapay->canUseForCountry('NZ'));
    }

    /**
     * Test canNotUseForCountry()
     */
    public function testCanNotUseForCountry()
    {
        $this->assertFalse($this->genoapay->canUseForCountry('AU'));
    }

    /**
     * Test CanUseForNZDCurrency()
     */
    public function testCanUseForNzdCurrency()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('NZD');
        $this->assertTrue($this->genoapay->canUseForCurrency('NZD'));
    }

    /**
     * Test CanNotUseForNZDCurrency()
     */
    public function testCanNotUseForNzdCurrency()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnOnConsecutiveCalls(null,'AUD');
        $this->assertFalse($this->genoapay->canUseForCurrency('AUD'));
        $this->assertFalse($this->genoapay->canUseForCurrency('AUD'));
    }

    /**
     * Test CanNotUseForNZDCurrency()
     */
    public function testRefund()
    {
        /** @var Payment $paymentMock */
        $paymentMock = $this->getPaymentMock();
        $paymentMock->expects(static::any())
        ->method('getParentTransactionId')
        ->willReturn('xxxxxxxxxxxxx');

        $orderMock = $this->getOrderMock();
        $paymentMock->expects($this->once())
        ->method('getOrder')
        ->willReturn($orderMock);
         $amount = 213.04;
         $this->assertEquals($this->genoapay, $this->genoapay->refund($paymentMock, $amount));
    }

    /**
     * Create mock object for payment model
     * @return MockObject
     */
    protected function getPaymentMock()
    {
        $paymentMock = $this->getMockBuilder(InfoModel::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAdditionalInformation', 'getParentTransactionId', 'getOrder',
                'getCcNumber', 'getCcExpMonth', 'getCcExpYear', 'getCcCid'
            ])
            ->getMock();

        $cardData = [
            'number' => 4111111111111111,
            'month' => 12,
            'year' => 18,
            'cvv' => 123
        ];
        $paymentMock->expects(static::any())
            ->method('getCcNumber')
            ->willReturn($cardData['number']);
        $paymentMock->expects(static::any())
            ->method('getCcExpMonth')
            ->willReturn($cardData['month']);
        $paymentMock->expects(static::any())
            ->method('getCcExpYear')
            ->willReturn($cardData['year']);
        $paymentMock->expects(static::any())
            ->method('getCcCid')
            ->willReturn($cardData['cvv']);
        
        return $paymentMock;
    }

    /**
     * Create mock object for order model
     * @return MockObject
     */
    protected function getOrderMock()
    {
        $orderData = [
            'currency' => 'NZD',
            'id' => 4,
            'increment_id' => '0000004'
        ];
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrencyCode', 'getIncrementId', 'getId', 'getBillingAddress', 'getShippingAddress','getStore','getPayment'])
            ->getMock();

        $orderMock->expects(static::any())
            ->method('getId')
            ->willReturn($orderData['id']);
        $orderMock->expects(static::any())
            ->method('getBaseCurrencyCode')
            ->willReturn($orderData['currency']);
        $orderMock->expects(static::any())
            ->method('getIncrementId')
            ->willReturn($orderData['increment_id']);
        
        $orderMock->expects(static::any())
        ->method('getPayment')
        ->willReturn($this->genoapay);

        $orderMock->expects(static::any())
        ->method('getStore')
        ->willReturn($this->storeMock);
        return $orderMock;
    }
}