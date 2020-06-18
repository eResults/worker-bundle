# WorkerBundle
``eResults\WorkerBundle`` adds abstraction to queue providers and allow to create Workers to consume queue workload.

## Requirements
* PHP >=7.4
* Symfony ^4.4|^5.0

## Installation
```bash
composer req eresults/worker-bundle
```

## Configuration

```yml
eresults_worker:
    providers:
        sqs:
            class: eResults\WorkerBundle\Provider\AwsSQS
            arguments:
                -
                    version: "latest"
                    region: "us-west-2"
                    credentials:
                        key: "xxxxxx"
                        secret: "xxxxxx"
    queues:
        my_queue:
            name: https://eu-west-1.queue.amazonaws.com/xxxxxx/xxxx
            provider: sqs
```

## Usage

You can access any configured provider or queue through the Symfony Container

```php
<?php

$provider = $this->get('eresults_worker.provider.sqs');
$provider->put('ThisIsMyQueue', 'Hello World');

$queue = $this->get('eresults_worker.queue.my_queue');
echo $queue->count()." item(s) in the queue";
```

You can easily create Workers

```php
<?php

// src/Acme/DemoBundle/Command/DemoWorkerCommand.php

namespace Acme\DemoBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use eResults\WorkerBundle\Command\Worker;

class DemoWorkerCommand extends Worker
{
    protected function configureWorker()
    {
        $this
            // Queue name from the configuration
            ->setQueueName('queue1')

            // Or load the queue directly through your own dependency injection
//            ->setQueue($this->myQueue)

            // Inherited Command methods
            ->setName('demo-worker')
            ->setDescription('Test a worker')
        ;
    }

    protected function doProcess($workload, InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($workload);

        // Stop worker when some end condition is reached
        if ($this->hasSomeReasonToStopAndExit()) {
            return self::STATE_SHUTDOWN;
        }

        // else continue
        return self::STATE_READY;
    }

    protected function onException(Exception $e, $workload): int
    {
        // If an exception occurs, check if the worker can continue running
        if ($e instanceof MyNonFatalException) {
            // Log the exception if appropriate
            // $this->logger->logException($e);

            return self::STATE_READY;
        }

        return self::STATE_EXCEPTION;
    }
 }

```

Then you can launch your worker like any other command

```sh
$ app/console demo-worker
Hello World
```

You can pass options.

```sh
$ app/console\
    --workload-timeout=60\
    --workload-limit=10\
    --memory-limit=128\
    --exit-on-exception
```

This command wait 60 seconds for a workload from the queue, will process a maximum of 10 workloads or exit when used memory exceeds 128Mb and exit if the ``executeWorker()`` throw an exception.
