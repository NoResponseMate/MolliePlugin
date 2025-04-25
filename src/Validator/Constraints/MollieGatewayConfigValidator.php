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
use Mollie\Api\Types\PaymentMethod;
use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MollieGatewayConfigValidator extends ConstraintValidator
{
    private const MINIMUM_FIELD = 'minimumAmount';

    private const MAXIMUM_FIELD = 'maximumAmount';

    private const AMOUNT_LIMITS_FIELD = 'amountLimits';

    /**@param MollieGatewayConfigValidatorType $constraint */
    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MollieGatewayConfigValidatorType::class);

        if (!$value instanceof PersistentCollection) {
            return;
        }

        /** @var MollieGatewayConfigInterface[] $configs */
        $configs = $value->getSnapshot();

        foreach ($configs as $index => $config) {
            $amountLimits = $config->getAmountLimits();
            if ($amountLimits === null) {
                continue;
            }

            $limitMinimumAmount = $amountLimits->getMinimumAmount();
            $limitMaximumAmount = $amountLimits->getMaximumAmount();

            if ($limitMinimumAmount !== null && $limitMaximumAmount !== null && $limitMinimumAmount > $limitMaximumAmount) {
                $this->context->buildViolation($constraint->minGreaterThanMaxMessage)
                    ->atPath("[{$index}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MAXIMUM_FIELD)
                    ->addViolation();

                continue;
            }

            if ($limitMinimumAmount !== null) {
                $apiMinimumAmount = $config->getMinimumAmount()['value'] ?? null;
                if ($apiMinimumAmount !== null) {
                    $this->validateConfigMinimumNotBelowApiMinimum(
                        $limitMinimumAmount,
                        (float) $apiMinimumAmount,
                        $constraint,
                        $index,
                    );
                }
            }

            if ($limitMaximumAmount === null) {
                continue;
            }

            $apiMaximumAmount = $config->getMaximumAmount()['value'] ?? null;
            if ($apiMaximumAmount === null || $this->shouldSkipMaximumValidation($config->getMethodId())) {
                continue;
            }

            $this->validateConfigMaximumNotAboveApiMaximum(
                $limitMaximumAmount,
                (float) $apiMaximumAmount,
                $constraint,
                $index,
            );
        }
    }

    /** @param MollieGatewayConfigValidatorType $constraint */
    private function validateConfigMinimumNotBelowApiMinimum(
        float $limitMinimumAmount,
        float $apiMinimumAmount,
        Constraint $constraint,
        int $index,
    ): void {
        if ($limitMinimumAmount >= $apiMinimumAmount) {
            return;
        }

        $this->context->buildViolation($constraint->minLessThanMollieMinMessage)
            ->setParameter('%amount%', (string) $apiMinimumAmount)
            ->atPath("[{$index}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MINIMUM_FIELD)
            ->addViolation()
        ;
    }

    /** @param MollieGatewayConfigValidatorType $constraint */
    private function validateConfigMaximumNotAboveApiMaximum(
        float $limitMaximumAmount,
        float $apiMaximumAmount,
        Constraint $constraint,
        int $index,
    ): void {
        if ($limitMaximumAmount <= $apiMaximumAmount) {
            return;
        }

        $this->context->buildViolation($constraint->maxGreaterThanMollieMaxMessage)
            ->setParameter('%amount%', (string) $apiMaximumAmount)
            ->atPath("[{$index}]." . self::AMOUNT_LIMITS_FIELD . '.' . self::MAXIMUM_FIELD)
            ->addViolation()
        ;
    }

    private function shouldSkipMaximumValidation(?string $paymentMethodName = null): bool
    {
        return $paymentMethodName === PaymentMethod::CREDITCARD;
    }
}
