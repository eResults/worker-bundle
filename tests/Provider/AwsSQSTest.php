<?php

namespace eResults\WorkerBundle\Provider;

use PHPUnit\Framework\TestCase;
use eResults\WorkerBundle\Queue\Queue;

class AwsSQSTest extends TestCase
{
    private AwsSQS $provider;

    private const QUEUE_NAME = 'WorkerBundleTest_SQS';

    public function setUp(): void
    {
        $this->provider = new AwsSQS([
            'credentials' => [
                'key' => '',
                'secret' => '',
            ],
            'region' => 'us-east-1',
            'version' => 'latest',
            'endpoint' => 'http://localhost:4566',
        ]);
    }

    public function testCreateQueue()
    {
        $newQueue = $this->provider->createQueue(self::QUEUE_NAME, ['VisibilityTimeout' => '15']);

        $this->assertTrue($newQueue instanceof Queue);
    }

    public function testPutArray()
    {
        $this->provider->put(self::QUEUE_NAME, ['workload' => 'heavy']);
        $this->assertTrue(true);
    }

    public function testCount()
    {
        $count = $this->provider->count(self::QUEUE_NAME);

        $this->assertEquals(1, $count);
    }

    public function testGetArray()
    {
        $workload = $this->provider->get(self::QUEUE_NAME);

        $this->assertSame(['workload' => 'heavy'], $workload);
    }

    public function testTimeout()
    {
        $tic = time();

        $this->provider->get(self::QUEUE_NAME, 3);

        $this->assertGreaterThanOrEqual(3, time() - $tic);
    }

    public function testMultiPut()
    {
        $workloads = [];
        for ($i = 0; $i < 10; $i++) {
            $workloads[] = 'workload$i';
        }

        $this->provider->multiPut(self::QUEUE_NAME, $workloads);

        sleep(5);

        $count = $this->provider->count(self::QUEUE_NAME);

        $this->assertEquals(10, $count);
    }

    public function testGetQueueOptions()
    {
        $queueOptions = $this->provider->getQueueOptions(self::QUEUE_NAME);

        $this->assertTrue(is_array($queueOptions));
        $this->assertArrayHasKey('VisibilityTimeout', $queueOptions);
        $this->assertEquals('15', $queueOptions['VisibilityTimeout']);
    }

    public function testListQueues()
    {
        $queues = $this->provider->listQueues('WorkerBundleTest_');

        $this->assertCount(1, $queues);
    }

    public function testQueueExists()
    {
        $queueExists = $this->provider->queueExists(self::QUEUE_NAME);
        $this->assertTrue($queueExists);

        $queueNotExists = $this->provider->queueExists('WorkerBundleTest_create_x');
        $this->assertFalse($queueNotExists);
    }

    public function testUpdateQueue()
    {
        $this->assertTrue(
            $this->provider->updateQueue(self::QUEUE_NAME, [
                'ReceiveMessageWaitTimeSeconds' => '20',
            ])
        );

        $this->assertTrue(
            $this->provider->updateQueue(self::QUEUE_NAME, [
                'ReceiveMessageWaitTimeSeconds' => '0',
            ])
        );
    }

    public function testDeleteQueue()
    {
        $this->assertTrue(
            $this->provider->deleteQueue(self::QUEUE_NAME)
        );
    }
}
