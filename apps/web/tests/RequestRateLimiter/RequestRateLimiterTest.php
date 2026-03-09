<?php

declare(strict_types=1);

namespace App\Tests\RequestRateLimiter;

use App\RequestRateLimiter\RequestRateLimiter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class RequestRateLimiterTest extends TestCase
{
    public function testAllowsRequestsWithinConfiguredLimit(): void
    {
        $limiter = new RequestRateLimiter(new ArrayAdapter(), 2, 30);

        $first = $limiter->check('127.0.0.1');
        $second = $limiter->check('127.0.0.1');

        self::assertTrue($first->allowed);
        self::assertSame(1, $first->remaining);
        self::assertNull($first->retryAfterSeconds);

        self::assertTrue($second->allowed);
        self::assertSame(0, $second->remaining);
        self::assertNull($second->retryAfterSeconds);
    }

    public function testBlocksWhenLimitIsExceededInSameWindow(): void
    {
        $limiter = new RequestRateLimiter(new ArrayAdapter(), 2, 30);

        $limiter->check('127.0.0.1');
        $limiter->check('127.0.0.1');
        $blocked = $limiter->check('127.0.0.1');

        self::assertFalse($blocked->allowed);
        self::assertSame(0, $blocked->remaining);
        self::assertNotNull($blocked->retryAfterSeconds);
        self::assertGreaterThanOrEqual(1, $blocked->retryAfterSeconds);
    }

    public function testWindowResetsAfterExpiration(): void
    {
        $limiter = new RequestRateLimiter(new ArrayAdapter(), 1, 1);

        $limiter->check('127.0.0.1');
        $blocked = $limiter->check('127.0.0.1');
        self::assertFalse($blocked->allowed);

        sleep(2);

        $afterWindow = $limiter->check('127.0.0.1');
        self::assertTrue($afterWindow->allowed);
        self::assertSame(0, $afterWindow->remaining);
        self::assertNull($afterWindow->retryAfterSeconds);
    }
}
