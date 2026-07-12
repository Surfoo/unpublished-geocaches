<?php

namespace App\EventListener;

use App\Service\GeocachingApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener(event: 'kernel.exception')]
class GeocachingApiExceptionListener
{
    public function __construct(private readonly LoggerInterface $apiLogger)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof GeocachingApiException) {
            return;
        }

        $this->apiLogger->error('GeocachingApiException', [
            'apiStatusCode'     => $exception->getApiStatusCode(),
            'retryAfterSeconds' => $exception->getRetryAfterSeconds(),
            'message'           => $exception->getMessage(),
        ]);

        $isRateLimited = $exception->getApiStatusCode() === 429;
        $headers       = [];
        if ($isRateLimited && $exception->getRetryAfterSeconds() !== null) {
            $headers['Retry-After'] = (string) $exception->getRetryAfterSeconds();
        }

        $event->setResponse(new JsonResponse(
            ['success' => false, 'error' => $exception->getMessage()],
            $isRateLimited ? JsonResponse::HTTP_TOO_MANY_REQUESTS : JsonResponse::HTTP_BAD_GATEWAY,
            $headers
        ));
    }
}
