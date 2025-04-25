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

namespace Sylius\MolliePlugin\EventListener\Workflow;

use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\StateResolver\OrderShippingStateResolver;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Webmozart\Assert\Assert;

final class ResolveShipmentStateListener
{
    public function __construct(private readonly OrderShippingStateResolver $resolver)
    {
    }

    public function __invoke(TransitionEvent $event): void
    {
        $shipment = $event->getSubject();
        Assert::isInstanceOf($shipment, ShipmentInterface::class);

        $this->resolver->resolve($shipment->getOrder());
    }
}
