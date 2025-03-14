<?php


declare(strict_types=1);

namespace spec\Sylius\MolliePlugin\Factory;

use Sylius\MolliePlugin\Entity\MollieSubscriptionConfigurationInterface;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Factory\PaymentDetailsFactory;
use Sylius\MolliePlugin\Factory\PaymentDetailsFactoryInterface;
use PhpSpec\ObjectBehavior;

final class PaymentDetailsFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(PaymentDetailsFactory::class);
    }

    function it_should_implements_payment_details_factory_interface(): void
    {
        $this->shouldImplement(PaymentDetailsFactoryInterface::class);
    }

    function it_creates_payment_details_for_subscription_and_order(
        MollieSubscriptionConfigurationInterface $subscriptionConfiguration,
        OrderInterface $order
    ): void {
        $details = [
            'gateway' => [
                'metadata' => [
                    'gateway'=>'test_gateway'
                ]
            ],
            'metadata' => [
                'gateway' => 'test_gateway'
            ]
        ];
        $subscriptionConfiguration->getPaymentDetailsConfiguration()
            ->willReturn($details);
        $subscriptionConfiguration->getMandateId()->willReturn(null);

        $this->createForSubscriptionAndOrder(
            $subscriptionConfiguration,
            $order
        )->shouldReturn([
            'recurring' => true,
            'cartToken' => null,
            'mandateId' => null,
            'metadata' => [
                'gateway' => $details['metadata']['gateway']
            ],
        ]);
    }
}
