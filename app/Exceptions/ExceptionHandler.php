<?php

namespace App\Exceptions;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

use App\Providers\Response\ResponseProvider;

class ExceptionHandler
{
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        ?LoggerInterface $logger = null
    ) {
        if ($logger) {
            $logger->error($exception->getMessage());
        }

        return response()->setMessage($exception->getMessage())->error();
    }
}
