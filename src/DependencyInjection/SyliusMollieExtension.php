<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\DependencyInjection;

use SyliusLabs\DoctrineMigrationsExtraBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SyliusMollieExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('doctrine_migrations') || !$container->hasExtension('sylius_labs_doctrine_migrations_extra')) {
            return;
        }

        $doctrineConfig = $container->getExtensionConfig('doctrine_migrations');
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => \array_merge(\array_pop($doctrineConfig)['migrations_paths'] ?? [], [
                'Sylius\MolliePlugin\Migrations' => __DIR__ . '/../Migrations',
            ]),
        ]);

        $container->prependExtensionConfig('sylius_labs_doctrine_migrations_extra', [
            'migrations' => [
                'Sylius\MolliePlugin\Migrations' => ['Sylius\Bundle\CoreBundle\Migrations'],
            ],
        ]);
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }
}
