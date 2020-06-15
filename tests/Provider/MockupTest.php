<?php

namespace Riverline\WorkerBundle\Provider;

use PHPUnit\Framework\TestCase;

/**
 * Class MockupTest
 * @package Riverline\WorkerBundle\Provider
 */
class MockupTest extends TestCase
{
    /**
     * @var Semaphore
     */
    private $provider;

    public function setUp(): void
    {
        $this->provider = new Mockup();
    }

    public function testPutArray()
    {
        $this->provider->put('test', ['workload' => 'heavy']);
    }

    public function testCount()
    {
        $count = $this->provider->count('test');

        $this->assertEquals(1, $count);
    }

    public function testGetArray()
    {
        $workload = $this->provider->get('test');

        $this->assertSame(['workload' => 'heavy'], $workload);
    }
}
