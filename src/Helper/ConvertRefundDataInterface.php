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

namespace Sylius\MolliePlugin\Helper;

interface ConvertRefundDataInterface
{
    /**
     * @param array<array-key, mixed> $data
     *
     * @return array{
     *     currency: string,
     *     value: string,
     * }
     */
    public function convert(array $data, string $currency): array;
}
