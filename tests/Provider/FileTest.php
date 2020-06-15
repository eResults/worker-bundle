<?php

namespace Riverline\WorkerBundle\Provider;

use PHPUnit\Framework\TestCase;
use Riverline\WorkerBundle\Provider\File as FileProvider;
use Riverline\WorkerBundle\Queue\Queue;

class FileTest extends TestCase
{
    private FileProvider $provider;

    public function setUp(): void
    {
        $this->provider = new FileProvider();
    }

    public function testCreateQueue()
    {
        $newQueue = $this->provider->createQueue('WorkerBundleTest_file_create');

        $this->assertTrue($newQueue instanceof Queue);
    }

    public function testPutArray()
    {
        $this->provider->put('WorkerBundleTest_file_create', ['workload' => 'heavy']);
    }

    public function testCount()
    {
        $count = $this->provider->count('WorkerBundleTest_file_create');

        $this->assertEquals(1, $count);
    }

    public function testGetArray()
    {
        $workload = $this->provider->get('WorkerBundleTest_file_create');

        $this->assertSame(['workload' => 'heavy'], $workload);
    }

    public function testMultiPut()
    {
        $workloads = [];
        for ($i = 0; $i < 10; $i++) {
            $workloads[] = 'workload$i';
        }

        $this->provider->multiPut('WorkerBundleTest_file_create', $workloads);

        sleep(5);

        $count = $this->provider->count('WorkerBundleTest_file_create');

        $this->assertEquals(10, $count);
    }

    public function testDeleteQueue()
    {
        $deleted = $this->provider->deleteQueue('WorkerBundleTest_file_create');

        $this->assertTrue($deleted);
    }

    public function testListQueues()
    {
        $this->provider->createQueue('WorkerBundleTest_file_listqueue1');
        $this->provider->createQueue('WorkerBundleTest_file_listqueue2');

        $queues = $this->provider->listQueues('WorkerBundleTest');

        $this->assertEquals(2, count($queues));

        $this->provider->deleteQueue('WorkerBundleTest_file_listqueue1');
        $this->provider->deleteQueue('WorkerBundleTest_file_listqueue2');
    }

    public function testQueueExists()
    {
        $queueName = 'WorkerBundleTest_file_queue1';
        $this->provider->createQueue($queueName);

        $queueExists = $this->provider->queueExists($queueName);
        $this->assertTrue($queueExists);

        $this->provider->deleteQueue($queueName);

        $queueNotExists = $this->provider->queueExists('WorkerBundleTest_file_queueX');
        $this->assertFalse($queueNotExists);
    }

}
