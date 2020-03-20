# cloudfront-trusted-proxies
[![Build](https://travis-ci.org/florianajir/cloudfront-trusted-proxies.svg?branch=master)](https://travis-ci.org/florianajir/cloudfront-trusted-proxies)
[![codecov](https://codecov.io/gh/florianajir/cloudfront-trusted-proxies/branch/master/graph/badge.svg)](https://codecov.io/gh/florianajir/cloudfront-trusted-proxies)
[![Latest Stable Version](https://poser.pugx.org/fmaj/cloudfront-trusted-proxies/version)](https://packagist.org/packages/fmaj/cloudfront-trusted-proxies)
[![Total Downloads](https://poser.pugx.org/fmaj/cloudfront-trusted-proxies/downloads)](https://packagist.org/packages/fmaj/cloudfront-trusted-proxies)
[![Latest Unstable Version](https://poser.pugx.org/fmaj/cloudfront-trusted-proxies/v/unstable)](https://packagist.org/packages/fmaj/cloudfront-trusted-proxies)
[![License](https://poser.pugx.org/fmaj/cloudfront-trusted-proxies/license)](https://packagist.org/packages/fmaj/cloudfront-trusted-proxies)

Provides a way to retrieve cloudfront proxies ip ranges with caching mechanism

## Installation

`composer require fmaj/cloudfront-trusted-proxies`

## Symfony context
The initial purpose of this library was to be used in a symfony project, but it's theorically operational in other contexts like a laravel project. 

As you can read in the [official documentation](https://symfony.com/doc/current/deployment/proxies.html#but-what-if-the-ip-of-my-reverse-proxy-changes-constantly)  
If you are using CloudFront on top of your load balancer symfony does not provide an easy way to trust proxies traffic, as it will only trust the node sitting directly above your application (in this case your load balancer). 
You also need to append the IP addresses or ranges of any additional proxy (in this case CloudFront IP ranges) to the array of trusted proxies.

### Usage

You have to inject a CacheInterface to ProxiesHelper constructor.

In this example the FilesystemAdapter is used to store the cloudfront ips for one hour (3600 seconds).

_Note that filesystem is often the worst choice for caching performances in production (except tmpfs storage)._

```php
// public/index.php
use Fmaj\CloudfrontTrustedProxies\ProxiesHelper;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

/** @var \Psr\Cache\CacheItemPoolInterface $cachePool */
$cachePool = new FilesystemAdapter('cloudfront_trusted_ips', 3600);
$proxyHelper = new ProxiesHelper($cachePool);
Request::setTrustedProxies(
    $proxyHelper->list(),
    Request::HEADER_X_FORWARDED_AWS_ELB
);
```
