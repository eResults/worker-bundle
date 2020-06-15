<?php

namespace Riverline\WorkerBundle\Provider;

use PHPUnit\Framework\TestCase;
use Riverline\WorkerBundle\Queue\Queue;

/**
 * Class PRedisTest
 * @package Riverline\WorkerBundle\Provider
 */
class PRedisTest extends TestCase
{
    private Queue $queue;

    public function setUp(): void
    {
        $this->queue = new Queue(
            'Test',
            new PRedis([
                'host' => '127.0.0.1',
            ])
        );

        $this->markTestSkipped('Tests should be fixed');
    }

    public function testPutArray()
    {
        $this->queue->put(['workload' => 'heavy']);
    }

    public function testCount()
    {
        $count = $this->queue->count();

        $this->assertEquals(1, $count);
    }

    public function testGetArray()
    {
        $workload = $this->queue->get();

        $this->assertSame(['workload' => 'heavy'], $workload);
    }

    public function testTimeout()
    {
        $tic = time();

        $this->queue->get(5);

        $this->assertGreaterThan(5, time() - $tic);
    }

}
