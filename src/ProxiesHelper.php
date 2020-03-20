<?php

namespace Fmaj\CloudfrontTrustedProxies;

use InvalidArgumentException;
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
     * @param CacheItemPoolInterface|null $cachePool
     * @param string                      $source
     */
    public function __construct(?CacheItemPoolInterface $cachePool = null, string $source = self::DEFAULT_SOURCE)
    {
        $this->source = $source;
        $this->cachePool = $cachePool;
    }

    /**
     * Retrieves the list of cloudfront ip addresses
     *
     * @return array
     */
    public function list(): array
    {
        $cached = null;
        if ($this->cachePool) {
            $cached = $this->cachePool->getItem('cloudfront_proxy_ip_addresses');
            if ($cached->isHit()) {
                return $cached->get();
            }
        }
        $response = file_get_contents($this->source);
        if ($response === false) {
            throw new InvalidArgumentException('Request error retrieving ip-ranges.json file');
        }
        $json = json_decode($response, true);
        if (empty($json['prefixes'])) {
            throw new InvalidArgumentException('Bad structure of ip-ranges.json file');
        }
        $ips = array_values(array_filter(array_map(static function ($item) {
            return $item['service'] === 'CLOUDFRONT' ? $item['ip_prefix'] : null;
        }, $json['prefixes'])));

        if ($this->cachePool) {
            $cached->set($ips);
            $this->cachePool->save($cached);
        }

        return $ips;
    }
}
