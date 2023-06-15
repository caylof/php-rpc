<?php

namespace Caylof\Rpc;

class ServiceCaller
{
    protected CallerRepositoryInterface $callerRepository;

    public function call(Payload $request): Payload
    {
        $fn = $this->callerRepository->get($request->caller);
        $param = $request->getRawData();
        $callResult = $fn($param);

        $reply = new Payload();
        $reply->caller = $request->caller;
        $reply->putRawData($callResult);
        return $reply;
    }

    public function setCallerRegistry(CallerRepositoryInterface $callerRepository): void
    {
        $this->callerRepository = $callerRepository;
    }
}