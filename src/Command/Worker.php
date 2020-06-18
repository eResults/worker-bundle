<?php

namespace eResults\WorkerBundle\Command;

use Exception;
use LogicException;
use eResults\WorkerBundle\Queue\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Worker extends Command implements ContainerAwareInterface
{
    const STATE_READY = 100;
    const STATE_EXCEPTION = 101;
    const STATE_MEMORY_LIMIT_REACHED = 102;
    const STATE_QUEUE_EMPTY = 103;
    const STATE_SHUTDOWN = 104;
    const STATE_WORKLOAD_LIMIT_REACHED = 105;

    private ContainerInterface $container;
    private InputInterface $input;
    private OutputInterface $output;
    private int $limit = 0;
    private int $memoryLimit = 0;
    private int $workloadsProcessed = 0;
    private ?Queue $queue;
    private ?string $queueName;

    final protected function configure()
    {
        $this
            ->addOption('exit-on-exception', null, InputOption::VALUE_NONE, 'Stop the worker on exception')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit (Mb)', 0)
            ->addOption('workload-limit', null, InputOption::VALUE_REQUIRED, 'Number of workload to process', 0)
            ->addOption('workload-timeout', null, InputOption::VALUE_REQUIRED, 'Number of second to wait for a new workload', 0)
        ;

        $this->doConfigure();

        if (!$this->queueName && !$this->queue) {
            throw new \LogicException('The worker queue must be specified by the setQueueName or setQueue method in your doConfigure method');
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        while (self::STATE_READY === ($state = $this->isReady())) {
            $queue = $this->getQueue();

            if (null === $queue) {
                return $this->shutdown(self::STATE_SHUTDOWN);
            }

            $workload = $queue->get($input->getOption('workload-timeout'));

            if (null === $workload) {
                $state = $this->onNoWorkload($queue);

                if (self::STATE_READY !== $state) {
                    return $this->shutdown($state);
                }

                continue;
            }

            $this->workloadsProcessed++;

            try {
                $state = $this->doProcess($workload, $input, $output);

                if (self::STATE_READY !== $state) {
                    return $this->shutdown($state);
                }
            } catch (Exception $e) {
                $state = $this->onException($e, $workload);

                if (self::STATE_READY !== $state) {
                    return $this->shutdown($state);
                }

                if ($input->getOption('exit-on-exception')) {
                    return $this->shutdown(self::STATE_EXCEPTION);
                }
            }
        }

        $shutdownState = $this->shutdown($state);

        if ($shutdownState === self::STATE_SHUTDOWN || $shutdownState === self::STATE_READY) {
            return 0;
        }

        return $shutdownState;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    final protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;

        // Limits
        $this->limit = intval($input->getOption('workload-limit'));
        $this->memoryLimit = intval($input->getOption('memory-limit'));
    }

    /**
     * Indicates if the worker can process another workload.
     * Reasons :
     *   - limit reached
     *   - memory limit reached
     *   - custom limit reached
     *
     * @return int
     */
    private function isReady(): int
    {
        if ($this->limit > 0 && $this->workloadsProcessed >= $this->limit) {
            return self::STATE_WORKLOAD_LIMIT_REACHED;
        }

        if ($this->memoryLimit > 0) {
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;

            if ($memoryUsage > $this->memoryLimit) {
                return self::STATE_MEMORY_LIMIT_REACHED;
            }
        }

        return self::STATE_READY;
    }

    protected function doConfigure(): void
    {
    }

    /**
     * @param mixed $workload
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function doProcess($workload, InputInterface $input, OutputInterface $output): int
    {
        throw new LogicException('You must override the doProcess() method in the concrete worker class.');
    }

    /**
     * Called when Exception is caught during workload processing.
     *
     * @param Queue $queue
     * @param Exception $exception
     *
     * @return int
     */
    protected function onException(Exception $exception, $workload): int
    {
        $queue = $this->getQueue();
        $this->output->writeln("Exception during workload processing for queue {$queue->getName()}. Class=".get_class($exception).". Message={$exception->getMessage()}. Code={$exception->getCode()}");

        return self::STATE_EXCEPTION;
    }

    /**
     * Called when no workload was provided from the queue.
     *
     * @param Queue $queue
     *
     * @return int
     */
    protected function onNoWorkload(Queue $queue): int
    {
        return self::STATE_QUEUE_EMPTY;
    }

    /**
     * Get the queue
     * @return Queue
     */
    final protected function getQueue(): Queue
    {
        if ($this->queue === null) {
            $this->queue = $this->container->get('eresults_worker.queue.'.$this->queueName);
        }

        return $this->queue;
    }

    /**
     * @param int $controlCode
     *
     * @return int
     */
    final private function shutdown(int $controlCode): int
    {
        return $this->onShutdown($controlCode);
    }

    /**
     * Called before exit.
     *
     * @param int $controlCode
     *
     * @return int Used as command exit code
     */
    protected function onShutdown(int $controlCode): int
    {
        return $controlCode;
    }

    /** @inheritDoc */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function setQueue(Queue $queue): void
    {
        $this->queue = $queue;
        $this->queueName = null;
    }

    /**
     * @param string $queueName
     *
     * @return $this
     */
    public function setQueueName(string $queueName)
    {
        $this->queue = null;
        $this->queueName = $queueName;

        return $this;
    }
}
