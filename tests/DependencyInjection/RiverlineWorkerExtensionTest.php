<?php

namespace Riverline\WorkerBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
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
                        'class' => 'Riverline\WorkerBundle\Provider\Mockup',
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

        $this->assertInstanceOf(
            'Riverline\WorkerBundle\Provider\Mockup',
            $container->get('riverline_worker.provider.mock')
        );

        $this->assertInstanceOf(
            'Riverline\WorkerBundle\Queue\Queue',
            $container->get('riverline_worker.queue.test')
        );
    }
}
