<?php


namespace App\DependencyInjection\Compiler;

use App\Service\RateManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RateProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(RateManager::class)) {
            return;
        }
        $definition = $container->findDefinition(RateManager::class);

        foreach ($container->findTaggedServiceIds('exchange.rate_provider') as $id => $tags) {
            $definition->addMethodCall('addProvider', [new Reference($id)]);
        }
    }
}
