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

namespace SyliusMolliePlugin\Action;

use SyliusMolliePlugin\Action\Api\BaseApiAwareAction;
use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Helper\ConvertOrderInterface;
use SyliusMolliePlugin\Helper\IntToStringConverterInterface;
use SyliusMolliePlugin\Helper\PaymentDescriptionInterface;
use SyliusMolliePlugin\Provider\Divisor\DivisorProviderInterface;
use SyliusMolliePlugin\Request\Api\CreateCustomer;
use SyliusMolliePlugin\Resolver\PaymentLocaleResolverInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

final class ConvertMollieSubscriptionPaymentAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /** @var PaymentDescriptionInterface */
    private $paymentDescription;

    /** @var RepositoryInterface */
    private $mollieMethodsRepository;

    /** @var ConvertOrderInterface */
    private $orderConverter;

    /** @var CustomerContextInterface */
    private $customerContext;

    /** @var PaymentLocaleResolverInterface */
    private $paymentLocaleResolver;

    /** @var IntToStringConverterInterface */
    private $intToStringConverter;

    /** @var DivisorProviderInterface */
    private $divisorProvider;

    public function __construct(
        PaymentDescriptionInterface $paymentDescription,
        RepositoryInterface $mollieMethodsRepository,
        ConvertOrderInterface $orderConverter,
        CustomerContextInterface $customerContext,
        PaymentLocaleResolverInterface $paymentLocaleResolver,
        IntToStringConverterInterface $intToStringConverter,
        DivisorProviderInterface $divisorProvider
    ) {
        $this->paymentDescription = $paymentDescription;
        $this->mollieMethodsRepository = $mollieMethodsRepository;
        $this->orderConverter = $orderConverter;
        $this->customerContext = $customerContext;
        $this->paymentLocaleResolver = $paymentLocaleResolver;
        $this->intToStringConverter = $intToStringConverter;
        $this->divisorProvider = $divisorProvider;
    }

    /** @param Convert|mixed $request */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        if (null === $order->getRecurringSequenceIndex()) {
            $order->setRecurringSequenceIndex(0);
        }

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        Assert::notNull($payment->getCurrencyCode());
        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        $divisor = $this->divisorProvider->getDivisorForCurrency($currency);

        Assert::notNull($payment->getAmount());
        $amount = $this->intToStringConverter->convertIntToString($payment->getAmount(), $divisor);
        $paymentOptions = $payment->getDetails();

        $cartToken = $paymentOptions['cartToken'];
        $sequenceType = array_key_exists(
            'recurring',
            $paymentOptions
        ) && true === $paymentOptions['recurring'] ? 'recurring' : 'first';

        if (isset($paymentOptions['metadata'])) {
            $paymentMethod = $paymentOptions['metadata']['molliePaymentMethods'] ?? null;
        } else {
            $paymentMethod = $paymentOptions['molliePaymentMethods'] ?? null;
        }

        $details = [
            'amount' => [
                'value' => "$amount",
                'currency' => $currency->code,
            ],
            'description' => $order->getNumber(),
            'sequenceType' => $sequenceType,
            'metadata' => [
                'order_id' => $order->getId(),
                'customer_id' => $customer->getId() ?? null,
                'molliePaymentMethods' => $paymentMethod ?? null,
                'cartToken' => $cartToken ?? null,
                'sequenceType' => $sequenceType,
                'gateway' => $request->getToken()->getGatewayName(),
            ],
            'full_name' => $customer->getFullName() ?? null,
            'email' => $customer->getEmail() ?? null,
        ];
        $details['metadata'] = array_merge($details['metadata'], $paymentOptions['metadata'] ?? []);

        if (isset($paymentOptions['mandateId'])) {
            $details['mandateId'] = $paymentOptions['mandateId'];
        }

        $this->gateway->execute($mollieCustomer = new CreateCustomer($details));
        $model = $mollieCustomer->getModel();
        $details['customerId'] = $model['customer_mollie_id'];

        $request->setResult($details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getSource()->getOrder() instanceof OrderInterface
            && 'array' === $request->getTo();
    }
}
