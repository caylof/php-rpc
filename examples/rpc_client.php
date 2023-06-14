<?php

require __DIR__.'/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    $pieces = explode('\\', $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $pieces) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});


/// test json (array)
$client = new \Caylof\Rpc\Driver\WorkmanFrameClient(
    protocol: 'tcp',
    host: '127.0.0.1',
    port: 2345,
);
$result = $client->call('TestSrv@hello', ['name' => 'cctv']);
var_dump($result);


/// test protobuf
$result = $client->call('TestSrv@haha', new \GPBMetadata\Dto\User\UserSearchRequest([
    'page' => 1,
    'perPage' => 15,
    'id' => 'u1',
]));
var_dump($result->serializeToJsonString());

/// test mix
$result = $client->call('TestSrv@hi', new \GPBMetadata\Dto\User\UserSearchRequest([
    'page' => 1,
    'perPage' => 15,
    'id' => 'u1',
]));
var_dump($result);
