# UPGRADE FROM 2.0 TO 2.1

1. The following templates have been removed:
   - `templates/bundles/SyliusAdminBundle/Layout/layout.html.twig`
   - `templates/bundles/SyliusAdminBundle/Order/Show/_payment.html.twig`
   - `templates/bundles/SyliusAdminBundle/Product/Tab/_details.html.twig`
   - `templates/bundles/SyliusAdminBundle/Shipment/Partial/_ship.html.twig`
   - `templates/bundles/SyliusRefundPlugin/orderRefunds.html.twig`
   - `templates/bundles/SyliusShopBundle/Checkout/_support.html.twig`
   - `templates/bundles/SyliusShopBundle/Product/_info.html.twig`
   - `templates/bundles/SyliusUiBundle/Layout/sidebar.html.twig`
1. The following classes have been marked as deprecated:

   - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransition`

1. Winzou State Machine deprecations

   The constructors of the following classes have been changed:

   Their content is now served through template events.
   If you've already copied them to your project, remove them.
   - `Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProvider`
   - `Sylius\MolliePlugin\Controller\Admin\RefundAction`
   - `Sylius\MolliePlugin\Controller\Shop\PayumController`
   - `Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicator`

    ```diff
    public function __construct(
    -   private readonly FactoryInterface $stateMachineFactory,
    +   private readonly FactoryInterface|StateMachineInterface $stateMachineFactory,
    ) {
    ```
