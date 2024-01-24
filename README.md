# A simple PHP RPC library

## Install

```shell
composer require caylof/rpc
```

## Example

see examples folder


### start example server

```shell
docker run -it --name eg-rpc --rm -v D:\code\github\php-rpc:/code -w /code caylof/php:8.2.4-cli-alpine /bin/sh

/code # composer install
/code # php ./examples/rpc_server.php start
```

### run example client

```shell
docker exec -it eg-rpc /bin/sh

/code # php ./examples/rpc_client.php
```