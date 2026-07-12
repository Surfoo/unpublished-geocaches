<?php

namespace App\Service;

class GeocachingApiException extends \RuntimeException
{
    public function __construct(
        private readonly int $apiStatusCode,
        string $message,
        private readonly ?int $retryAfterSeconds = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }

    public function getApiStatusCode(): int
    {
        return $this->apiStatusCode;
    }

    public function getRetryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }
}
