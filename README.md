# cloudfront-trusted-proxies
Provides a way to retrieve cloudfront proxies ip ranges with caching mechanism

## Installation

`composer require fmaj/cloudfront-trusted-proxies`

## Symfony context
The initial purpose of this library is to used in a symfony project. 
As you can read in the [official documentation](https://symfony.com/doc/current/deployment/proxies.html#but-what-if-the-ip-of-my-reverse-proxy-changes-constantly)  
If you are using CloudFront on top of your load balancer symfony does not provide an easy way to trust proxies traffic, as it will only trust the node sitting directly above your application (in this case your load balancer). 
You also need to append the IP addresses or ranges of any additional proxy (in this case CloudFront IP ranges) to the array of trusted proxies.

### Usage

#### Without caching

```php
// public/index.php

$proxyHelper = new ProxiesHelper();
Request::setTrustedProxies(
    $proxyHelper->list(),
    Request::HEADER_X_FORWARDED_AWS_ELB
);
```

#### With Filesystem caching

```php
// public/index.php

$cache = new FilesystemAdapter('cloudfront_trusted_ips', 3600); 
// You can inject to ProxiesHelper any CacheInterface instance
$proxyHelper = new ProxiesHelper($cache);
Request::setTrustedProxies(
    $proxyHelper->list(),
    Request::HEADER_X_FORWARDED_AWS_ELB
);
```
