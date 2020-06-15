<?php

namespace Riverline\WorkerBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Riverline\WorkerBundle\Provider\Mock;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RiverlineWorkerExtensionTest extends TestCase
{
    public function testServiceConstruction()
    {
        $container = new ContainerBuilder();
        $config = [
            'riverline_worker' => [
                'providers' => [
                    'mock' => [
                        'class' => Mock::class,
                    ],
                ],
                'queues' => [
                    'test' => [
                        'provider' => 'mock',
                        'name' => 'test',
                    ],
                ],
            ],
        ];

        $extension = new RiverlineWorkerExtension();
        $extension->load($config, $container);

        $container->compile();

        $this->assertInstanceOf(
            Mock::class,
            $container->get('riverline_worker.provider.mock')
        );

        $this->assertInstanceOf(
            'Riverline\WorkerBundle\Queue\Queue',
            $container->get('riverline_worker.queue.test')
        );
    }
}
