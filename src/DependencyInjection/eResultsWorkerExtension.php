<?php

namespace eResults\WorkerBundle\DependencyInjection;

use eResults\WorkerBundle\Queue\Queue;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class eResultsWorkerExtension extends Extension
{
    public function getAlias()
    {
        return 'eresults_worker';
    }

    /** @inheritDoc */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['providers'])) {
            foreach ($config['providers'] as $id => $provider) {
                $definition = new Definition($provider['class'], $provider['arguments']);
                $definition->setPublic(true);

                $container->setDefinition(
                    $this->getAlias().'.provider.'.$id,
                    $definition
                );
            }
        }

        if (isset($config['queues'])) {
            foreach ($config['queues'] as $id => $queue) {
                $definition = new Definition(Queue::class, [
                    $queue['name'],
                    new Reference($this->getAlias().'.provider.'.$queue['provider']),
                ]);

                $definition->setPublic(true);

                $container->setDefinition(
                    $this->getAlias().'.queue.'.$id,
                    $definition
                );
            }
        }
    }
}
