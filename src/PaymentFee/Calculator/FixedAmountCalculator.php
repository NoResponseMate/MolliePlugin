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

namespace Sylius\MolliePlugin\PaymentFee\Calculator;

use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;
use Sylius\MolliePlugin\Order\AdjustmentInterface;
use Sylius\MolliePlugin\Payments\PaymentTerms\Options;
use Sylius\MolliePlugin\Provider\Divisor\DivisorProviderInterface;
use Webmozart\Assert\Assert;

final class FixedAmountCalculator implements PaymentSurchargeCalculatorInterface
{
    public function __construct(
        private readonly AdjustmentFactoryInterface $adjustmentFactory,
        private readonly DivisorProviderInterface $divisorProvider,
    ) {
    }

    public function supports(string $type): bool
    {
        return Options::FIXED_FEE === array_search($type, Options::getAvailablePaymentSurchargeFeeType(), true);
    }

    public function calculate(OrderInterface $order, MollieGatewayConfig $paymentMethod): void
    {
        Assert::notNull($paymentMethod->getPaymentSurchargeFee());
        $fixedAmount = $paymentMethod->getPaymentSurchargeFee()->getFixedAmount();
        Assert::notNull($fixedAmount);

        if (false === $order->getAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT)->isEmpty()) {
            $order->removeAdjustments(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        }

        $adjustment = $this->adjustmentFactory->createNew();
        $adjustment->setType(AdjustmentInterface::FIXED_AMOUNT_ADJUSTMENT);
        $adjustment->setAmount((int) ($fixedAmount * $this->divisorProvider->getDivisor()));
        $adjustment->setNeutral(false);

        $order->addAdjustment($adjustment);
    }
}
