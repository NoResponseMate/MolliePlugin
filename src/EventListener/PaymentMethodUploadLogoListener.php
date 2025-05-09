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

namespace SyliusMolliePlugin\EventListener;

use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Entity\MollieGatewayConfigInterface;
use SyliusMolliePlugin\Uploader\PaymentMethodLogoUploaderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

final class PaymentMethodUploadLogoListener
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PaymentMethodLogoUploaderInterface */
    private $logoUploader;

    public function __construct(EntityManagerInterface $entityManager, PaymentMethodLogoUploaderInterface $logoUploader)
    {
        $this->entityManager = $entityManager;
        $this->logoUploader = $logoUploader;
    }

    public function uploadLogo(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        Assert::isInstanceOf($subject, PaymentMethodInterface::class);

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $subject->getGatewayConfig();

        if (null === $gatewayConfig->getMollieGatewayConfig()) {
            return;
        }

        if ($gatewayConfig->getMollieGatewayConfig()->isEmpty()) {
            return;
        }

        $this->uploadMollieConfigLogo($gatewayConfig);
    }

    private function uploadMollieConfigLogo(GatewayConfigInterface $gatewayConfig): void
    {
        $mollieConfigs = $gatewayConfig->getMollieGatewayConfig();

        Assert::notNull($mollieConfigs);
        $this->logoUploader->upload($mollieConfigs);

        /** @var MollieGatewayConfigInterface $mollieConfig */
        foreach ($mollieConfigs as $mollieConfig) {
            Assert::notNull($mollieConfig->getCustomizeMethodImage());
            if (false === $mollieConfig->getCustomizeMethodImage()->hasFile()) {
                $mollieConfig->getCustomizeMethodImage()->setFile(null);
            }
        }

        $gatewayConfig->setMollieGatewayConfig($mollieConfigs);
        $this->entityManager->persist($gatewayConfig);
    }
}
