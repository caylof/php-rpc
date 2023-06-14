<?php

namespace Caylof\Rpc;

use Google\Protobuf\Internal\Message;

class Payload
{
    public string $caller = '';
    public string $serializeData = '';
    public string $protobufClass = '';

    public function putRawData(array | Message $data): void
    {
        if (is_array($data)) {
            $this->serializeData = json_encode($data);
        } else if ($data instanceof Message) {
            $this->serializeData = $data->serializeToString();
            $this->protobufClass = $data::class;
        }
    }

    public function getUnSerializeData(): array | Message
    {
        if (empty($this->protobufClass)) {
            return json_decode($this->serializeData, true);
        } else if (is_subclass_of($this->protobufClass, Message::class)) {
            /* @var $proto Message */
            $proto = new ($this->protobufClass);
            $proto->mergeFromString($this->serializeData);
            return $proto;
        } else {
            throw new \BadMethodCallException('property protobufClass is invalid');
        }
    }
}
