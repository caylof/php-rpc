<?php

namespace Caylof\Rpc;

final class Protocol
{
    public static function encode(Payload $payload): string
    {
        return pack('nnna*a*a*',
            $payload->callError,
            strlen($payload->caller),
            strlen($payload->protobufClass),
            $payload->caller,
            $payload->protobufClass,
            $payload->serializeData,
        );
    }

    public static function decode(string $data): Payload
    {
        $chuck = unpack('nerr/ncaller_len/nclazz_len', substr($data, 0, 6));
        $callError = $chuck['err'];
        $callerLen = $chuck['caller_len'];
        $clazzLen = $chuck['clazz_len'];
        $data = substr($data, 6);

        $chuck = unpack(sprintf('a%dcaller/a%dclazz/a*data', $callerLen, $clazzLen), $data);
        $caller = $chuck['caller'];
        $protobufClass = $chuck['clazz'];
        $serializeData = $chuck['data'];

        $payload = new Payload();
        $payload->callError = $callError;
        $payload->caller = $caller;
        $payload->protobufClass = $protobufClass;
        $payload->serializeData = $serializeData;
        return $payload;
    }
}
