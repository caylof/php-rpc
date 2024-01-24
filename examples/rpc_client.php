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
//$client->call('TestSrv@auth', ['token' => '123456']);

$reply = $client->call('TestSrv@hello', ['name' => 'cctv']);
var_dump($reply);


/// test protobuf
$reply = $client->call('TestSrv@haha', new \GPBMetadata\Dto\User\UserSearchRequest([
    'page' => 1,
    'perPage' => 15,
    'id' => 'u1',
]));
[$errCode, $result] = $reply;
var_dump($errCode, $result->serializeToJsonString());

/// test mix
$reply = $client->call('TestSrv@hi', new \GPBMetadata\Dto\User\UserSearchRequest([
    'page' => 1,
    'perPage' => 15,
    'id' => 'u1',
]));
var_dump($reply);

/// test error
$reply = $client->call('TestSrv@throws', ['name' => 'cctv']);
var_dump($reply);
