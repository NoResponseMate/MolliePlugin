<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Entity;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SyliusMolliePlugin\Entity\MollieSubscriptionConfiguration;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;

final class MollieSubscriptionConfigurationTest extends TestCase
{
    private MockObject $subscriptionMock;

    private MollieSubscriptionConfiguration $mollieSubscriptionConfiguration;

    protected function setUp(): void
    {
        $this->subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $this->mollieSubscriptionConfiguration = new MollieSubscriptionConfiguration($this->subscriptionMock);
    }

    function testImplementInterface(): void
    {
        $this->assertInstanceOf(MollieSubscriptionInterface::class, $this->subscriptionMock);
    }

    function testHasNullIdByDefault(): void
    {
        $this->assertNull($this->mollieSubscriptionConfiguration->getId());
    }

    function testGetsHostName(): void
    {
        $this->mollieSubscriptionConfiguration->setHostName('test_host');
        $this->assertSame('test_host', $this->mollieSubscriptionConfiguration->getHostName());
    }

    function testGetsPort(): void
    {
        $this->mollieSubscriptionConfiguration->setPort(3308);
        $this->assertSame(3308, $this->mollieSubscriptionConfiguration->getPort());
    }

    function testGetsSubscriptionId(): void
    {
        $this->mollieSubscriptionConfiguration->setSubscriptionId('id_1');
        $this->assertSame('id_1', $this->mollieSubscriptionConfiguration->getSubscriptionId());
    }

    function testGetsCustomerId(): void
    {
        $this->mollieSubscriptionConfiguration->setCustomerId('id_1');
        $this->assertSame('id_1', $this->mollieSubscriptionConfiguration->getCustomerId());
    }

    function testGetsMandateId(): void
    {
        $this->mollieSubscriptionConfiguration->setMandateId('id_1');
        $this->assertSame('id_1', $this->mollieSubscriptionConfiguration->getMandateId());
    }

    function testGetsInterval(): void
    {
        $this->mollieSubscriptionConfiguration->setInterval('60');
        $this->assertSame('60', $this->mollieSubscriptionConfiguration->getInterval());
    }

    function testGetsNumberOfRepetitions(): void
    {
        $this->mollieSubscriptionConfiguration->setNumberOfRepetitions(69);
        $this->assertSame(69, $this->mollieSubscriptionConfiguration->getNumberOfRepetitions());
    }

    function testGetsPaymentDetailsConfiguration(): void
    {
        $this->mollieSubscriptionConfiguration->setPaymentDetailsConfiguration([
            'id' => 1,
            'code' => 'test_code',
            'status' => 'open',
        ]);
        $this->assertSame([
            'id' => 1,
            'code' => 'test_code',
            'status' => 'open',
        ], $this->mollieSubscriptionConfiguration->getPaymentDetailsConfiguration());
    }
}
