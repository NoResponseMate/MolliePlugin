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

namespace SyliusMolliePlugin\Order;

use Sylius\Component\Core\Model\ShipmentInterface;

interface ShipmentClonerInterface
{
    public function clone(ShipmentInterface $shipment): ShipmentInterface;
}
