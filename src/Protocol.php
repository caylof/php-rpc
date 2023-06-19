<?php

namespace Caylof\Rpc;

final class Protocol
{
    public static function encode(Payload $payload): string
    {
        return pack('nna*a*a*',
            strlen($payload->caller),
            strlen($payload->protobufClass),
            $payload->caller,
            $payload->protobufClass,
            $payload->serializeData,
        );
    }

    public static function decode(string $data): Payload
    {
        $chuck = unpack('ncaller_len/nclazz_len', substr($data, 0, 4));
        $callerLen = $chuck['caller_len'];
        $clazzLen = $chuck['clazz_len'];
        $data = substr($data, 4);

        $chuck = unpack(sprintf('a%dcaller/a%dclazz/a*data', $callerLen, $clazzLen), $data);
        $caller = $chuck['caller'];
        $protobufClass = $chuck['clazz'];
        $serializeData = $chuck['data'];

        $payload = new Payload();
        $payload->caller = $caller;
        $payload->protobufClass = $protobufClass;
        $payload->serializeData = $serializeData;
        return $payload;
    }
}
