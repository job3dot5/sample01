<?php

declare(strict_types=1);

namespace App\RequestRateLimiter;

use Psr\Cache\CacheItemPoolInterface;

final class RequestRateLimiter
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly int $maxRequests,
        private readonly int $windowSeconds,
    ) {
    }

    public function check(string $ip): RateLimitResult
    {
        $safeIp = '' !== $ip ? $ip : 'unknown';
        $cacheKey = 'rate_limit.ip.'.hash('sha256', $safeIp);
        $now = time();

        $item = $this->cache->getItem($cacheKey);

        if (!$item->isHit()) {
            $item->set([
                'count' => 1,
                'window_start' => $now,
            ]);
            $item->expiresAfter($this->windowSeconds);
            $this->cache->save($item);

            return new RateLimitResult(true, $this->maxRequests - 1, null);
        }

        $data = $item->get();
        $count = \is_array($data) ? (int) ($data['count'] ?? 0) : 0;
        $windowStart = \is_array($data) ? (int) ($data['window_start'] ?? 0) : 0;

        if ($now - $windowStart >= $this->windowSeconds) {
            $item->set([
                'count' => 1,
                'window_start' => $now,
            ]);
            $item->expiresAfter($this->windowSeconds);
            $this->cache->save($item);

            return new RateLimitResult(true, $this->maxRequests - 1, null);
        }

        if ($count >= $this->maxRequests) {
            $retryAfter = max(1, $this->windowSeconds - ($now - $windowStart));

            return new RateLimitResult(false, 0, $retryAfter);
        }

        $newCount = $count + 1;
        $item->set([
            'count' => $newCount,
            'window_start' => $windowStart,
        ]);
        $item->expiresAfter(max(1, $this->windowSeconds - ($now - $windowStart)));
        $this->cache->save($item);

        return new RateLimitResult(true, max(0, $this->maxRequests - $newCount), null);
    }
}
