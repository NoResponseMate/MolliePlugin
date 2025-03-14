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

namespace SyliusMolliePlugin\Refund\Units;

use Mollie\Api\Resources\Order;
use Sylius\Component\Core\Model\OrderInterface;

interface UnitsShipmentOrderRefundInterface
{
    public function refund(Order $order, OrderInterface $syliusOrder): array;
}
