<?php

namespace Fmaj\CloudfrontTrustedProxies;

use Psr\Cache\CacheItemPoolInterface;

class ProxiesHelper
{
    private const DEFAULT_SOURCE = 'https://ip-ranges.amazonaws.com/ip-ranges.json';

    /**
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @var string
     */
    private $source;

    /**
     * @param CacheItemPoolInterface $cachePool
     * @param string                 $source
     */
    public function __construct(CacheItemPoolInterface $cachePool, string $source = self::DEFAULT_SOURCE)
    {
        $this->source = $source;
        $this->cachePool = $cachePool;
    }

    /**
     * Retrieves the list of cloudfront ip addresses
     *
     * @return string[]
     */
    public function list(): array
    {
        // look if a cached version is available
        $cached = $this->cachePool->getItem('cloudfront_proxy_ip_addresses');
        if ($cached->isHit()) {
            return $cached->get();
        }
        // fetch the file
        $data = json_decode(file_get_contents($this->source), true);
        // filter to keep only cloudfront addresses
        $ips = array_values(array_filter(
            array_map(static function (array $item) {
                return $item['service'] === 'CLOUDFRONT' ? $item['ip_prefix'] : null;
            }, $data['prefixes'])
        ));
        // update cache
        $cached->set($ips);
        $this->cachePool->save($cached);

        return $ips;
    }
}
