# README

## What is eResults\WorkerBundle

``eResults\WorkerBundle`` add abstraction to queue providers and allow to create Workers to consume queue workload.

## Requirements

* PHP >=7.4
* Symfony ^4.4|^5.0

## Installation

``eResults\WorkerBundle`` is compatible with composer and any prs-0 autoloader

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

$provider = $this->get('eresults_worker.provider.predis');
$provider->put('ThisIsMyQueue', 'Hello World');

$queue = $this->get('eresults_worker.queue.queue1');
echo $queue->count()." item(s) in the queue";
```

You can easily create Workers

```php
<?php

// src/Acme/DemoBundle/Command/DemoWorkerCommand.php

namespace Acme\DemoBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use eResults\WorkerBundle\Command\Worker;
use eResults\WorkerBundle\Command\WorkerControlCodes;

class DemoWorkerCommand extends Worker
{
    protected function configureWorker()
    {
        $this
            // Queue name from the configuration
            ->setQueueName('queue1')

            // Inhered Command methods
            ->setName('demo-worker')
            ->setDescription('Test a worker')
        ;
    }

    protected function executeWorker(InputInterface $input, OutputInterface $output, $workload)
    {
        $output->writeln($workload);

        // Stop worker and dot not process other workloads
        if ($someReasonToStopAndExit)
        {
            return WorkerControlCodes::STOP_EXECUTION;
        }

        // else continue
        return WorkerControlCodes::CAN_CONTINUE;
    }
}

```

Then you can launch your worker like any other command

```sh
$ php app/console demo-worker
Hello World
```

You can pass options.

```sh
$ php app/console --worker-wait-timeout=60 --worker-limit=10 --memory-limit=128 --worker-exit-on-exception
```

This command wait 60 seconds for a workload from the queue, will process a maximum of 10 workloads or exit when usaed memory exceed 128Mb and exit if the ``executeWorker()`` throw an exception.
