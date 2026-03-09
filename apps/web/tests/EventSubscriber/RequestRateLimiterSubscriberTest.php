<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\RequestRateLimiterSubscriber;
use App\RequestRateLimiter\RequestRateLimiter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class RequestRateLimiterSubscriberTest extends TestCase
{
    public function testAllowsRequestWhenUnderLimit(): void
    {
        $limiter = new RequestRateLimiter(new ArrayAdapter(), 2, 30);
        $subscriber = new RequestRateLimiterSubscriber($limiter, 2);

        $event = $this->createMainRequestEvent('GET', '/dashboard', '127.0.0.1');
        $subscriber->onKernelRequest($event);

        self::assertFalse($event->hasResponse());
    }

    public function testReturnsTooManyRequestsWhenLimitExceeded(): void
    {
        $limiter = new RequestRateLimiter(new ArrayAdapter(), 1, 30);
        $subscriber = new RequestRateLimiterSubscriber($limiter, 1);

        $first = $this->createMainRequestEvent('GET', '/dashboard', '127.0.0.1');
        $subscriber->onKernelRequest($first);
        self::assertFalse($first->hasResponse());

        $second = $this->createMainRequestEvent('GET', '/dashboard', '127.0.0.1');
        $subscriber->onKernelRequest($second);

        self::assertTrue($second->hasResponse());
        $response = $second->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        self::assertSame('1', $response->headers->get('X-RateLimit-Limit'));
        self::assertSame('0', $response->headers->get('X-RateLimit-Remaining'));
        self::assertNotNull($response->headers->get('Retry-After'));
    }

    public function testSkipsExcludedPaths(): void
    {
        $limiter = new RequestRateLimiter(new ArrayAdapter(), 0, 30);
        $subscriber = new RequestRateLimiterSubscriber($limiter, 0);

        $event = $this->createMainRequestEvent('GET', '/_profiler/abc', '127.0.0.1');
        $subscriber->onKernelRequest($event);

        self::assertFalse($event->hasResponse());
    }

    public function testSkipsOptionsRequests(): void
    {
        $limiter = new RequestRateLimiter(new ArrayAdapter(), 0, 30);
        $subscriber = new RequestRateLimiterSubscriber($limiter, 0);

        $event = $this->createMainRequestEvent('OPTIONS', '/dashboard', '127.0.0.1');
        $subscriber->onKernelRequest($event);

        self::assertFalse($event->hasResponse());
    }

    private function createMainRequestEvent(string $method, string $path, string $ip): RequestEvent
    {
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response();
            }
        };

        $request = Request::create($path, $method, server: ['REMOTE_ADDR' => $ip]);

        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
