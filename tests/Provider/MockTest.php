<?php

namespace Riverline\WorkerBundle\Provider;

use PHPUnit\Framework\TestCase;

class MockTest extends TestCase
{
    private Mock $provider;

    public function setUp(): void
    {
        $this->provider = new Mock();
    }

    public function testPutArray()
    {
        $this->provider->put('test', ['workload' => 'heavy']);
        $this->assertTrue(true);
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
