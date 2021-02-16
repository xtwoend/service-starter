<?php

namespace App\Command;

use Psr\Container\ContainerInterface;
use Hyperf\Command\Command as HyperfCommand;

class HalloCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('hallo');
        $this->container = $container;
    }


    public function handle()
    {
        $name = $this->ask("Enter your name:");
        $this->info("Hallo {$name}");
    }
}