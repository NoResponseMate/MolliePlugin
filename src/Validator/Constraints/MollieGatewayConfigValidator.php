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

namespace Sylius\MolliePlugin\Validator\Constraints;

use Doctrine\ORM\PersistentCollection;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MollieGatewayConfigValidator extends ConstraintValidator
{
    private const MINIMUM_FIELD = 'minimumAmount';

    private const MAXIMUM_FIELD = 'maximumAmount';

    private const AMOUNT_LIMITS_FIELD = 'amountLimits';

    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MollieGatewayConfigValidatorType::class);

        if (!$value instanceof PersistentCollection) {
            return;
        }

        /** @var MollieGatewayConfigInterface[] $configs */
        $configs = $value->getSnapshot();

        foreach ($configs as $index => $config) {
            $limits = $config->getAmountLimits();
            if ($limits === null) {
                continue;
            }

            $configMinimum = $limits->getMinimumAmount();
            $configMaximum = $limits->getMaximumAmount();

            if ($configMinimum !== null && $configMaximum !== null && $configMinimum > $configMaximum) {
                $this->context->buildViolation($constraint->minGreaterThanMaxMessage)
                    ->atPath("[{$index}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MAXIMUM_FIELD)
                    ->addViolation();

                continue;
            }

            if ($configMinimum !== null) {
                $apiMinimum = $config->getMinimumAmount()['value'] ?? null;
                if ($apiMinimum === null) {
                    continue;
                }

                $this->validateConfigMinimumNotBelowApiMinimum(
                    $configMinimum,
                    (float) $apiMinimum,
                    $constraint,
                    $index,
                );
            }

            if ($configMaximum === null) {
                continue;
            }

            $apiMaximum = $config->getMaximumAmount()['value'] ?? null;
            if ($apiMaximum !== null) {
                if ($this->shouldSkipMaximumValidation($config->getMethodId())) {
                    continue;
                }

                $this->validateConfigMaximumNotAboveApiMaximum(
                    $configMaximum,
                    (float) $apiMaximum,
                    $constraint,
                    $index,
                );
            }
        }
    }

    /** @param MollieGatewayConfigValidatorType $constraint */
    private function validateConfigMinimumNotBelowApiMinimum(
        float $configMinimum,
        float $apiMinimum,
        Constraint $constraint,
        int $index,
    ): void {
        if ($configMinimum < $apiMinimum) {
            $this->context->buildViolation($constraint->minLessThanMollieMinMessage)
                ->setParameter('%amount%', (string) $apiMinimum)
                ->atPath("[{$index}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MINIMUM_FIELD)
                ->addViolation();
        }
    }

    /** @param MollieGatewayConfigValidatorType $constraint */
    private function validateConfigMaximumNotAboveApiMaximum(
        float $configMaximum,
        float $apiMaximum,
        Constraint $constraint,
        int $index,
    ): void {
        if ($configMaximum > $apiMaximum) {
            $this->context->buildViolation($constraint->maxGreaterThanMollieMaxMessage)
                ->setParameter('%amount%', (string) $apiMaximum)
                ->atPath("[{$index}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MAXIMUM_FIELD)
                ->addViolation();
        }
    }

    private function shouldSkipMaximumValidation(?string $paymentMethodName = null): bool
    {
        return $paymentMethodName === 'creditcard';
    }
}
