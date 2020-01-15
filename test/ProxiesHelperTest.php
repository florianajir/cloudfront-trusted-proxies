<?php

namespace Fmaj\CloudfrontTrustedProxies\Tests;

use Fmaj\CloudfrontTrustedProxies\ProxiesHelper;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ProxiesHelperTest extends TestCase
{
    public function testListCached(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock->expects(self::once())->method('isHit')->willReturn(true);
        $cacheItemMock->expects(self::once())->method('get')->willReturn([
            '127.0.0.1',
            '127.0.0.2',
        ]);
        $cachePoolMock = $this->createMock(CacheItemPoolInterface::class);
        $cachePoolMock
            ->method('getItem')
            ->willReturn($cacheItemMock);
        $proxiesHelper = new ProxiesHelper($cachePoolMock);

        self::assertEquals([
            '127.0.0.1',
            '127.0.0.2',
        ], $proxiesHelper->list());
    }

    public function testListWithoutCache(): void
    {
        $proxiesHelper = new ProxiesHelper();
        $results = $proxiesHelper->list();
        self::assertNotEmpty($results);
        self::assertIsArray($results);
    }

    public function testListEmptyCache(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock->expects(self::once())->method('isHit')->willReturn(false);
        $cacheItemMock->expects(self::once())->method('set');
        $cachePoolMock = $this->createMock(CacheItemPoolInterface::class);
        $cachePoolMock
            ->method('getItem')
            ->willReturn($cacheItemMock);
        $proxiesHelper = new ProxiesHelper($cachePoolMock);
        self::assertNotEmpty($proxiesHelper->list());
    }
}
