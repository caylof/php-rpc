<?php

namespace Caylof\Rpc;

interface CallerRepositoryInterface
{
    public function get(string $caller): callable;
}
