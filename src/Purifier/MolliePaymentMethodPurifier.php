<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Purifier;

use Sylius\MolliePlugin\Entity\MollieGatewayConfigInterface;
use Sylius\MolliePlugin\Resolver\MollieMethodsResolverInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class MolliePaymentMethodPurifier implements MolliePaymentMethodPurifierInterface
{
    /** @var RepositoryInterface */
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function removeAllNoLongerSupportedMethods(): void
    {
        foreach (MollieMethodsResolverInterface::UNSUPPORTED_METHODS as $methodId) {
            $this->removeMethod($methodId);
        }
    }

    public function removeMethod(string $methodId): void
    {
        $methodConfig = $this->repository->findOneBy(['methodId' => $methodId]);

        if ($methodConfig instanceof MollieGatewayConfigInterface) {
            /** @phpstan-ignore-next-line Ecs yields about doc comment in wrong place */
            $this->repository->remove($methodConfig);
        }
    }
}
