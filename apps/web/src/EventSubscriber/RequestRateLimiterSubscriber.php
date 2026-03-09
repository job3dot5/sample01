<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\RequestRateLimiter\RequestRateLimiter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestRateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestRateLimiter $rateLimiter,
        private readonly int $maxRequests,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 200],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if ('OPTIONS' === $request->getMethod()) {
            return;
        }

        if ($this->isExcludedPath($path)) {
            return;
        }

        $ip = (string) ($request->getClientIp() ?? 'unknown');
        $result = $this->rateLimiter->check($ip);

        if ($result->allowed) {
            return;
        }

        $response = new Response('Too Many Requests', Response::HTTP_TOO_MANY_REQUESTS, [
            'Retry-After' => (string) ($result->retryAfterSeconds ?? 1),
            'X-RateLimit-Limit' => (string) $this->maxRequests,
            'X-RateLimit-Remaining' => '0',
        ]);

        $event->setResponse($response);
    }

    private function isExcludedPath(string $path): bool
    {
        // Skip profiler/debug endpoints and static assets: they are framework/tooling traffic,
        // not user business requests, and throttling them would create noisy false positives.
        return str_starts_with($path, '/_profiler')
            || str_starts_with($path, '/_wdt')
            || str_starts_with($path, '/css/')
            || str_starts_with($path, '/images/')
            || str_starts_with($path, '/js/')
            || '/favicon.ico' === $path;
    }
}
