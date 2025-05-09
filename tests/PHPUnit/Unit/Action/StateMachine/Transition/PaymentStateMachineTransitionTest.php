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

namespace Tests\SyliusMolliePlugin\PHPUnit\Unit\Action\StateMachine\Transition;

use PHPUnit\Framework\TestCase;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;
use SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransition;
use SyliusMolliePlugin\Action\StateMachine\Transition\PaymentStateMachineTransitionInterface;
use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Transitions\MollieSubscriptionPaymentProcessingTransitions;

final class PaymentStateMachineTransitionTest extends TestCase
{
    private FactoryInterface $subscriptionSateMachineFactoryMock;

    private PaymentStateMachineTransition $paymentStateMachineTransition;

    protected function setUp(): void
    {
        $this->subscriptionSateMachineFactoryMock = $this->createMock(FactoryInterface::class);
        $this->paymentStateMachineTransition = new PaymentStateMachineTransition($this->subscriptionSateMachineFactoryMock);
    }

    function testImplementInterface(): void
    {
        $this->assertInstanceOf(PaymentStateMachineTransitionInterface::class, $this->paymentStateMachineTransition);
    }

    function testAppliesTransition(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $stateMachineMock = $this->createMock(StateMachineInterface::class);

        $this->subscriptionSateMachineFactoryMock->expects($this->once())->method('get')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH)->willReturn($stateMachineMock);
        $stateMachineMock->expects($this->once())->method('can')->with(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(true);
        $stateMachineMock->expects($this->once())->method('apply')->with(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(true);
        $this->paymentStateMachineTransition->apply($subscriptionMock,MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
    }

    function testCannotAppliesTransition(): void
    {
        $subscriptionMock = $this->createMock(MollieSubscriptionInterface::class);
        $stateMachineMock = $this->createMock(StateMachineInterface::class);

        $this->subscriptionSateMachineFactoryMock->expects($this->once())->method('get')->with($subscriptionMock, MollieSubscriptionPaymentProcessingTransitions::GRAPH)->willReturn($stateMachineMock);
        $stateMachineMock->expects($this->once())->method('can')->with(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN)->willReturn(false);
        $stateMachineMock->expects($this->never())->method('apply')->with(MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
        $this->paymentStateMachineTransition->apply($subscriptionMock,MollieSubscriptionPaymentProcessingTransitions::TRANSITION_BEGIN);
    }
}
