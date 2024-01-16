<?php

require __DIR__.'/../vendor/autoload.php';
spl_autoload_register(function ($class) {
    $pieces = explode('\\', $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $pieces) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use Workerman\Worker;

/// 定义服务 rpc service
class TestSrv
{
    /// auth service
    public function auth(array $param): array
    {
        $verified = ($param['token'] === '123456');
        return compact('verified');
    }
    /// json (array)
    public function hello(array $param): array
    {
        return [...$param, 'server_reply' => 'xxx'];
    }

    /// protobuf
    public function haha(\GPBMetadata\Dto\User\UserSearchRequest $request): \GPBMetadata\Dto\User\UserSearchResult
    {
        $id = $request->getId();

        $data = [
            'total' => 1,
            'items' => [
                ['id' => $id, 'name' => 'cctv', 'gender' => 'M'],
            ],
        ];
        $result = new \GPBMetadata\Dto\User\UserSearchResult();
        $result->mergeFromJsonString(json_encode($data));
        return $result;
    }

    /// mix
    public function hi(\GPBMetadata\Dto\User\UserSearchRequest $request): array
    {
        $id = $request->getId();
        return ['id' => $id, 'name' => 'cctv'];
    }
}

$container = new \Illuminate\Container\Container();
$container->singleton(TestSrv::class, TestSrv::class);

$serviceCaller = new \Caylof\Rpc\ServiceCaller();
$serviceCaller->setCallerRegistry(new \Caylof\Rpc\ServiceRepository($container));

$server = new \Caylof\Rpc\Driver\WorkmanServer();
$server->setServiceCaller($serviceCaller);
//$server->setAuthHandler('TestSrv@auth', function($result) {
//    return $result['verified'];
//});

/// 启动 workman server
Worker::$pidFile = __DIR__ . '/rpc-server-pid';
Worker::$logFile = '/dev/null';
Worker::$statusFile = '/dev/null';
$host = '0.0.0.0';
$port = 2345;
$worker = new Worker(sprintf('frame://%s:%d', $host, $port));
$worker->count = 1;
$worker->onConnect = $server->onConnect(...);
$worker->onMessage = $server->onMessage(...);
Worker::runAll();
