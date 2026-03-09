<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testPhpUnitBootstrapsTestEnvironment(): void
    {
        self::assertSame('test', $_SERVER['APP_ENV'] ?? null);
    }
}
