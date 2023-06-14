<?php

namespace Caylof\Rpc;

final class Protocol
{
    public static function encode(Payload $payload): string
    {
        return pack('na*na*a*',
            strlen($payload->caller),
            $payload->caller,
            strlen($payload->protobufClass),
            $payload->protobufClass,
            $payload->serializeData,
        );
    }

    public static function decode(string $data): Payload
    {
        $chuck = unpack('ncaller_len', substr($data, 0, 2));
        $callerLen = $chuck['caller_len'];
        $data = substr($data, 2);

        $chuck = unpack(sprintf('a%dcaller/nclazz_len', $callerLen), $data);
        $caller = $chuck['caller'];
        $clazzLen = $chuck['clazz_len'];
        $data = substr($data, $callerLen + 2);

        $chuck = unpack(sprintf('a%dclazz/a*data', $clazzLen), $data);
        $protobufClass = $chuck['clazz'];
        $serializeData = $chuck['data'];

        $payload = new Payload();
        $payload->caller = $caller;
        $payload->protobufClass = $protobufClass;
        $payload->serializeData = $serializeData;
        return $payload;
    }
}
