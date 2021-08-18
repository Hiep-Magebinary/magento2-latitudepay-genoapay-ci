<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Latitude\Payment\Test\Unit\Helper;

use Latitude\Payment\Test\Unit\LatitudeTestCase;
use Latitude\Payment\Helper\Curl;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\HTTP\Client\Curl as ClientUrl;
use Latitude\Payment\Logger\Logger;
use Magento\Framework\Encryption\Encryptor;
use Latitude\Payment\Helper\Config as ConfigHelper;
/**
 * @covers \Latitude\Payment\Helper\Curl
 */
class CurlTest extends LatitudeTestCase
{
    /**
     * Helper
     *
     * @var Curl
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
     * @var ClientUrl|MockObject
     */
    private $curlMock;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var Encryptor|MockObject
     */
    private $encryptorMock;

    /**
     * @var ConfigHelper|MockObject
     */
    private $configHelperMock;

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

        $this->curlMock = $this->createMock(ClientUrl::class);

        $this->encryptorMock = $this->createMock(Encryptor::class);
        $this->configHelperMock = $this->createMock(ConfigHelper::class);
        $this->loggerMock = $this->createMock(Logger::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->helper = $this->objectManagerHelper->getObject(
            Curl::class,
            [
                'configHelper' => $this->configHelperMock,
                'curl' => $this->curlMock,
                'encryptor' => $this->encryptorMock,
                'customLogger' => $this->loggerMock,
                'context' => $contextMock
            ]
        );
    }

    /**
     * Test getToken() on success
     */
    public function testGetTokenSuccess(): void
    {
        $this->curlMock->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode([
                "authToken" => "xxxxxxxxxxxxxxxxxxxxxxxx",
                "expiryDate" =>"2029-08-24T14:15:22Z"
            ]));
        
        $expectedResult = (object)[
            "authToken" => "xxxxxxxxxxxxxxxxxxxxxxxx",
            "expiryDate" =>"2029-08-24T14:15:22Z"
        ];
        $this->assertEquals($expectedResult,$this->helper->getToken(1,'latitudepay'));
    }

    /**
     * Test getToken() on failed
     * 
     * @expectedException \Exception
     * @expectedExceptionMessage Exception: Invalid response line returned from server: HTTP/2 200
     */
    // public function testGetTokenFailed(): void
    // {
    //     $this->curlMock->expects($this->once())
    //         ->method('getBody')
    //         ->willReturn(json_encode([
    //             "authToken" => "xxxxxxxxxxxxxxxxxxxxxxxx",
    //             "expiryDate" =>"2029-08-24T14:15:22Z"
    //         ]));

    //     $this->curlMock->expects($this->once())
    //         ->method('post')
    //         ->will($this->throwException(new \Exception('Invalid response line returned from server: HTTP/2 200')));
        
    //     $this->helper->getToken(1,'latitudepay');
    //     $this->expectException(\Exception::class);
    //     $this->expectExceptionMessage('Invalid response line returned from server: HTTP/2 200');
    // }

    /**
     * Test getConfiguration()
     */
    public function testGetConfiguration(): void
    {
        $this->curlMock->expects($this->once())
            ->method('getBody')
            ->willReturn('{
                "name": "string",
                "description": "string",
                "minimumAmount": 0,
                "maximumAmount": 0,
                "availability": [
                  {
                    "country": "string",
                    "countryCode": "string",
                    "currency": "string",
                    "currencySymbol": "string"
                  }
                ]
            }');
        
        $expectedResult = json_decode('{
            "name": "string",
            "description": "string",
            "minimumAmount": 0,
            "maximumAmount": 0,
            "availability": [
              {
                "country": "string",
                "countryCode": "string",
                "currency": "string",
                "currencySymbol": "string"
              }
            ]
        }');
        $this->assertEquals($expectedResult,json_decode($this->helper->getConfiguration()));
    }

    /**
     * Test getSignatureHash()
     */
    public function testGetSignatureHash(): void
    {
        $expectedResult = 'aef24549a597c94f486b134651ac07ca083c2c8d3e6b8c614da3f075ddf2257f';
        $this->assertEquals($expectedResult,$this->helper->getSignatureHash('xxxxxxxxx'));
    }

    /**
     * Test createEcommercePurchase()
     */
    public function testCreateEcommercePurchase(): void
    {
        $this->curlMock
            ->method('getBody')
            ->willReturnOnConsecutiveCalls(json_encode([
                "authToken" => "xxxxxxxxxxxxxxxxxxxxxxxx",
                "expiryDate" =>"2029-08-24T14:15:22Z"
            ]), '{
                "token": "xxxxxxxxxxxxx",
                "paymentUrl": "string",
                "expiryDate": "2019-08-24T14:15:22Z"
            }');
        $requestBody = '{
            "customer": {
                "mobileNumber": "string",
                "firstName": "string",
                "surname": "string",
                "email": "string",
                "address": {
                "addressLine1": "string",
                "addressLine2": "string",
                "suburb": "string",
                "cityTown": "string",
                "state": "string",
                "postcode": "string",
                "countryCode": "string"
                },
                "dateOfBirth": "2019-08-24"
            },
            "shippingAddress": {
                "addressLine1": "string",
                "addressLine2": "string",
                "suburb": "string",
                "cityTown": "string",
                "state": "string",
                "postcode": "string",
                "countryCode": "string"
            },
            "billingAddress": {
                "addressLine1": "string",
                "addressLine2": "string",
                "suburb": "string",
                "cityTown": "string",
                "state": "string",
                "postcode": "string",
                "countryCode": "string"
            },
            "products": [
                {
                "name": "string",
                "price": {
                    "amount": 0,
                    "currency": "string"
                },
                "sku": "string",
                "quantity": 0,
                "taxIncluded": true
                }
            ],
            "shippingLines": [
                {
                "carrier": "string",
                "price": {
                    "amount": 0,
                    "currency": "string"
                },
                "taxIncluded": true
                }
            ],
            "taxAmount": {
                "amount": 0,
                "currency": "string"
            },
            "reference": "string",
            "totalAmount": {
                "amount": 0,
                "currency": "string"
            },
            "returnUrls": {
                "successUrl": "string",
                "failUrl": "string",
                "callbackUrl": "string"
            }
        }';
        $expectedResult = json_decode('{
            "token": "xxxxxxxxxxxxx",
            "paymentUrl": "string",
            "expiryDate": "2019-08-24T14:15:22Z"
        }');
        $token = $this->helper->getToken(1,'latitudepay');
        $signatureHash = $this->helper->getSignatureHash('xxxxxxxxx');
        $this->assertEquals($expectedResult,json_decode($this->helper->createEcommercePurchase($requestBody, $token, $signatureHash)));
    }

    /**
     * Test createRefund()
     */
    public function testCreateRefund(): void
    {
        $this->curlMock
            ->method('getBody')
            ->willReturnOnConsecutiveCalls(json_encode([
                "authToken" => "xxxxxxxxxxxxxxxxxxxxxxxx",
                "expiryDate" =>"2029-08-24T14:15:22Z"
            ]), '{
                "refundId": "string",
                "refundDate": "2019-08-24T14:15:22Z",
                "reference": "string",
                "commissionAmount": 0
            }');
        
        $expectedResult = json_decode('{
            "refundId": "string",
            "refundDate": "2019-08-24T14:15:22Z",
            "reference": "string",
            "commissionAmount": 0
        }');
        $requestBody = '{
            "amount": {
                "amount": 0,
                "currency": "string"
            },
            "reason": "string",
            "reference": "string"
        }';
        $storeId = 1;
        $methodCode = 'latitudepay';
        $token = 'xxxxxxxxx';
        $this->assertEquals($expectedResult,json_decode($this->helper->createRefund($requestBody, $token, $storeId, $methodCode)));
    }
}