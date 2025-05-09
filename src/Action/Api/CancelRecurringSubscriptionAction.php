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

namespace SyliusMolliePlugin\Action\Api;

use SyliusMolliePlugin\Entity\MollieSubscriptionInterface;
use SyliusMolliePlugin\Logger\MollieLoggerActionInterface;
use SyliusMolliePlugin\Request\Api\CancelRecurringSubscription;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Resources\Customer;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Webmozart\Assert\Assert;

final class CancelRecurringSubscriptionAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /** @var MollieLoggerActionInterface */
    private $loggerAction;

    public function __construct(MollieLoggerActionInterface $loggerAction)
    {
        $this->loggerAction = $loggerAction;
    }

    /** @param CancelRecurringSubscription|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var MollieSubscriptionInterface $subscription */
        $subscription = $request->getModel();
        $configuration = $subscription->getSubscriptionConfiguration();

        try {
            Assert::notNull($configuration->getCustomerId());
            /** @var Customer $customer */
            $customer = $this->mollieApiClient->customers->get($configuration->getCustomerId());
        } catch (\Exception $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error with get customer in recurring subscription with: %s', $e->getMessage()));

            throw new ApiException('Error with get customer in recurring subscription with ' . $e->getMessage());
        }

        $this->loggerAction->addLog(sprintf('Cancel recurring subscription with id:  %s', $configuration->getSubscriptionId()));

        Assert::notNull($configuration->getSubscriptionId());
        $customer->cancelSubscription($configuration->getSubscriptionId());
    }

    public function supports($request): bool
    {
        return
            $request instanceof CancelRecurringSubscription &&
            $request->getModel() instanceof MollieSubscriptionInterface
            ;
    }
}
