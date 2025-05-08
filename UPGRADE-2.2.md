# UPGRADE FROM 2.1 TO 2.2

1. The following classes have been marked as deprecated:

   - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransition`

1. Winzou State Machine deprecations

   The constructors of the following classes have been changed:

   - `Sylius\MolliePlugin\ApplePay\Provider\OrderPaymentApplePayDirectProvider`
   - `Sylius\MolliePlugin\Controller\Admin\RefundAction`
   - `Sylius\MolliePlugin\Controller\Shop\PayumController`
   - `Sylius\MolliePlugin\StateMachine\Applicator\MollieOrderStatesApplicator`
   - `Sylius\MolliePlugin\StateMachine\Transition\PaymentStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\ProcessingStateMachineTransition`
   - `Sylius\MolliePlugin\StateMachine\Transition\StateMachineTransition`
   - `Sylius\MolliePlugin\Console\Command\BeginProcessingSubscriptions`
   - `Sylius\MolliePlugin\Console\Command\ProcessSubscriptions`

      ```diff
      public function __construct(
          ...
      -   private readonly FactoryInterface $stateMachineFactory,
      +   private readonly FactoryInterface|StateMachineInterface $stateMachineFactory,
          ...
      ) {
      ```
