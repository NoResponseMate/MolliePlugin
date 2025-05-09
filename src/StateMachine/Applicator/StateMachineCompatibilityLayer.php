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

namespace Sylius\MolliePlugin\StateMachine\Applicator;

use Sylius\Abstraction\StateMachine\StateMachineInterface;

/**
 * This class is a helper for maintaining backward compatibility and will be removed in MolliePlugin 3.0.
 * @internal
 */
final class StateMachineCompatibilityLayer
{
    public static function apply(
        StateMachineInterface $stateMachine,
        object $subject,
        string $graph,
        string $transitions,
    ): void {
        if ($stateMachine->can($subject, $graph, $transitions)) {
            $stateMachine->apply($subject, $graph, $transitions);
        }
    }
}
