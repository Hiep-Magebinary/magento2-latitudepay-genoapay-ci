<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Latitude\Payment\Test\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
/**
 * @covers \Latitude\Payment\Controller\Latitude\Callback
 */
class LatitudeTestCase extends TestCase
{
    /**
     * @param MockObject $mock
     * @param string $method
     *
     * @return InvocationMocker
     */
    protected function basicStub($mock, $method): InvocationMocker
    {
        return $mock->method($method)
            ->withAnyParameters();
    }

    /**
     * @param string $className
     * @return MockObject
     */
    protected function basicMock(string $className): MockObject
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}