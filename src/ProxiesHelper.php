<?php

namespace Fmaj\CloudfrontTrustedProxies;

use GuzzleHttp\Client;
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
    public function __construct(?CacheItemPoolInterface $cachePool = null, $source = self::DEFAULT_SOURCE)
    {
        $this->source = $source;
        $this->cachePool = $cachePool;
    }

    public function list(): array
    {
        if ($this->cachePool) {
            $cached = $this->cachePool->getItem('cloudfront_proxy_ip_addresses');
            if ($cached->isHit()) {
                return $cached->get();
            }
        }
        $client = new Client();
        $response = $client->request('GET', $this->source);
        $json = json_decode($response->getBody(), true);
        if (empty($json['prefixes'])) {
            throw new InvalidArgumentException('Bad structure of ip-ranges.json file');
        }
        $ips = array_values(array_filter(array_map(static function ($item) {
            return $item['service'] === 'CLOUDFRONT' ? $item['ip_prefix'] : null;
        }, $json['prefixes'])));

        if ($this->cachePool & isset($cached)) {
            $cached->set($ips);
        }

        return $ips;
    }
}
