<?php

namespace eResults\WorkerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class eResultsWorkerBundle extends Bundle
{
    public function getContainerExtension()
    {
        return $this->extension ?: ($this->extension = $this->createContainerExtension());
    }
}
