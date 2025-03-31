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

namespace Sylius\MolliePlugin\Order;

use Sylius\Component\Core\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\MolliePlugin\Model\PaymentSurchargeFeeType;

interface AdjustmentInterface extends BaseAdjustmentInterface
{
    public const PERCENTAGE_ADJUSTMENT = PaymentSurchargeFeeType::PERCENTAGE;

    public const FIXED_AMOUNT_ADJUSTMENT = PaymentSurchargeFeeType::FIXED;

    public const PERCENTAGE_AND_AMOUNT_ADJUSTMENT = PaymentSurchargeFeeType::FIXED_AND_PERCENTAGE;

    public const VOUCHER_ADJUSTMENT = 'voucher';
}
