<?php

namespace Caylof\Rpc;

class ServiceCaller
{
    protected CallerRepositoryInterface $callerRepository;
    /**
     * @var callable(\Throwable): array
     */
    protected $errorHandler;

    public function __construct(?CallerRepositoryInterface $callerRepository = null)
    {
        if (! is_null($callerRepository)) {
            $this->setCallerRegistry($callerRepository);
        }
    }

    public function call(Payload $request): Payload
    {
        $fn = $this->callerRepository->get($request->caller);
        $param = $request->getRawData();
        $reply = new Payload();
        $reply->caller = $request->caller;
        try {
            $callResult = $fn($param);
            $reply->callError = 0;
            $reply->putRawData($callResult);
        } catch (\Throwable $e) {
            $reply->callError = 1;
            $reply->putRawData(($this->errorHandler)($e));
        }
        return $reply;
    }

    public function setCallerRegistry(CallerRepositoryInterface $callerRepository): void
    {
        $this->callerRepository = $callerRepository;
    }

    public function setErrorHandler(callable $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }
}