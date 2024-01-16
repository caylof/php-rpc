<?php

namespace Caylof\Rpc\Driver;

use Caylof\Rpc\Protocol;
use Caylof\Rpc\ServiceCaller;
use Workerman\Connection\TcpConnection;
use Workerman\Timer;

class WorkmanServer
{
    protected ServiceCaller $serviceCaller;
    /**
     * @var array<string, callable>
     */
    protected array $authHandler = [];

    public function __construct(?ServiceCaller $serviceCaller = null)
    {
        if (! is_null($serviceCaller)) {
            $this->setServiceCaller($serviceCaller);
        }
    }

    public function setServiceCaller(ServiceCaller $serviceCaller): void
    {
        $this->serviceCaller = $serviceCaller;
    }

    public function setAuthHandler(string $authCaller, callable $resultVerifyFn): void
    {
        $this->authHandler = [$authCaller, $resultVerifyFn];
    }

    public function onConnect(TcpConnection $conn): void
    {
        /* @var TcpConnection|mixed $conn */
        if (empty($this->authHandler)) {
            $conn->needAuth = false;
            return;
        }
        $conn->needAuth = true;
        $conn->authTimerId = Timer::add(30, function() use ($conn) {
            $conn->close();
        }, null, false);
    }

    public function onMessage(TcpConnection $conn, string $data): void
    {
        /* @var TcpConnection|mixed $conn */
        $payload = Protocol::decode($data);
        if ($conn->needAuth) {
            [$authCaller, $resultVerifyFn] = $this->authHandler;
            if ($payload->caller !== $authCaller) {
                $conn->close();
                return;
            }
            $result = $this->serviceCaller->call($payload);
            if (!$resultVerifyFn($result->getRawData())) {
                $conn->close();
                return;
            }
            $conn->needAuth = false;
            Timer::del($conn->authTimerId);
        } else {
            $result = $this->serviceCaller->call($payload);
        }
        $conn->send(Protocol::encode($result));
    }
}
