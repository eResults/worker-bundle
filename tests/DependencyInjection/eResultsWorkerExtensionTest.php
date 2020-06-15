<?php

namespace eResults\WorkerBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use eResults\WorkerBundle\Provider\Mock;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class eResultsWorkerExtensionTest extends TestCase
{
    public function testServiceConstruction()
    {
        $container = new ContainerBuilder();
        $config = [
            'eresults_worker' => [
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

        $extension = new eResultsWorkerExtension();
        $extension->load($config, $container);

        $container->compile();

        $this->assertInstanceOf(
            Mock::class,
            $container->get('eresults_worker.provider.mock')
        );

        $this->assertInstanceOf(
            'eResults\WorkerBundle\Queue\Queue',
            $container->get('eresults_worker.queue.test')
        );
    }
}
