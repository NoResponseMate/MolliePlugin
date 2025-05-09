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

namespace SyliusMolliePlugin\Provider\Order;

use SyliusMolliePlugin\Entity\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface OrderPaymentApplePayDirectProviderInterface
{
    public function provideOrderPayment(OrderInterface $order, string $targetState): ?PaymentInterface;
}
