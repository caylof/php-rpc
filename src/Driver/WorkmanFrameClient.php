<?php

namespace Caylof\Rpc\Driver;

use Caylof\Rpc\Payload;
use Caylof\Rpc\Protocol;
use Google\Protobuf\Internal\Message;

class WorkmanFrameClient
{
    /* @var $sock resource */
    protected $sock;

    public function __construct(
        protected string $protocol,
        protected string $host,
        protected int $port,
    ) {
        $this->connect();
    }

    public function connect(): void {
        $sock = stream_socket_client(
            address:sprintf('%s://%s:%d', $this->protocol, $this->host, $this->port),
            error_code: $errCode,
            error_message: $errMsg,
            timeout: 5
        );
        if (false === $sock) {
            throw new \InvalidArgumentException(sprintf('socket connect error, code: %s, message: %s', $errCode, $errMsg));
        }
        $this->sock = $sock;
    }

    public function call(string $caller, array | Message $param): array | Message
    {
        $request = new Payload();
        $request->caller = $caller;
        $request->putRawData($param);

        $segment = Protocol::encode($request);
        /// workman frame protocol
        $segment = pack('N', 4 + strlen($segment)) . $segment;

        stream_socket_sendto($this->sock, $segment);
        $data = $this->receivePackage();
        if (false === $data) {
            throw new \BadMethodCallException('rpc call error');
        }

        /// workman frame protocol
        $data = substr($data, 4);

        $result = Protocol::decode($data);
        return $result->getRawData();
    }

    protected function receivePackage(): false|string {
        // workman frame protocol 定义初始化收包长度为4，用于获取一个完整数据包的长度
        $totLen = $readMaxSize = 4;
        $buff = '';
        $buffLen = 0;
        while ($buffLen < $totLen) {
            $read = stream_socket_recvfrom($this->sock, $readMaxSize);
            if (empty($read)) {
                return false;
            }
            $buff .= $read;
            $buffLen += strlen($read);
            if ($buffLen < 4) {
                $readMaxSize = 4 - $buffLen;
                continue;
            }
            if ($buffLen === 4) {
                $unpack_data = unpack('Ntotal_len', $buff);
                $totLen = $unpack_data['total_len'];
            }
            $readMaxSize = min(512, $totLen - $buffLen);
        }
        return $buff;
    }

    public function __destruct() {
        if (! empty($this->sock)) {
            fclose($this->sock);
        }
    }
}
