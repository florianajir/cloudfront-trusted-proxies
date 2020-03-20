<?php

namespace Fmaj\CloudfrontTrustedProxies\Tests;

use Fmaj\CloudfrontTrustedProxies\ProxiesHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ProxiesHelperTest extends TestCase
{
    public function testListEmptyCache(): void
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock->expects(self::once())->method('isHit')->willReturn(false);
        $cacheItemMock->expects(self::once())->method('set');
        /** @var MockObject|CacheItemPoolInterface $cachePoolMock */
        $cachePoolMock = $this->createMock(CacheItemPoolInterface::class);
        $cachePoolMock
            ->method('getItem')
            ->willReturn($cacheItemMock);
        $cachePoolMock
            ->expects($this->once())
            ->method('save');
        $proxiesHelper = new ProxiesHelper($cachePoolMock);
        self::assertNotEmpty($proxiesHelper->list());
    }

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
}
