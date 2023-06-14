<?php

namespace Caylof\Rpc;

use Psr\Container\ContainerInterface;

class ServiceRepository implements CallerRepositoryInterface
{
    public function __construct(
        protected ContainerInterface $container,
    ) {}

    public function get(string $caller): callable
    {
        $pieces = explode('@', $caller);
        if (count($pieces) !== 2) {
            throw new \InvalidArgumentException('invalid rpc caller');
        }
        [$clazz, $fn] = $pieces;
        return $this->container->get($clazz)->$fn(...);
    }

}