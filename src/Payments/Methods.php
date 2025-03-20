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

namespace Sylius\MolliePlugin\Payments;

use Mollie\Api\Resources\Method;
use Sylius\MolliePlugin\Payments\Methods\AbstractMethod;
use Sylius\MolliePlugin\Payments\Methods\MethodInterface;

final class Methods implements MethodsInterface
{
    /** @var AbstractMethod[] */
    private array $methods;

    public function add(Method $mollieMethod): void
    {
        foreach (self::GATEWAYS as $gateway) {
            $payment = new $gateway();

            if ($mollieMethod->id === $payment->getMethodId()) {
                $payment->setName($mollieMethod->description);
                $payment->setMinimumAmount((array) $mollieMethod->minimumAmount);
                $payment->setMaximumAmount((array) $mollieMethod->maximumAmount);
                $payment->setImage((array) $mollieMethod->image);

                /** @var array<array-key, mixed>|null $issuers */
                $issuers = $mollieMethod->issuers;
                $payment->setIssuers((array) $issuers);

                $this->methods[] = $payment;
            }
        }
    }

    public function getAllEnabled(): array
    {
        $methods = [];
        foreach ($this->methods as $method) {
            if (true === $method->isEnabled()) {
                $methods[] = $method->isEnabled();
            } else {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}
