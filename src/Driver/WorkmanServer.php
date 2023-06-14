<?php

namespace Caylof\Rpc\Driver;

use Caylof\Rpc\Protocol;
use Caylof\Rpc\ServiceCaller;
use Workerman\Connection\TcpConnection;

class WorkmanServer
{
    protected ServiceCaller $serviceCaller;

    public function setServiceCaller(ServiceCaller $serviceCaller): void
    {
        $this->serviceCaller = $serviceCaller;
    }

    public function onMessage(TcpConnection $conn, string $data): void
    {
        $payload = Protocol::decode($data);
        $result = $this->serviceCaller->call($payload);
        $conn->send(Protocol::encode($result));
    }
}
