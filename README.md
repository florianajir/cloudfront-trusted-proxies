# cloudfront-trusted-proxies
Provides a way to retrieve cloudfront proxies ip ranges with caching mechanism

## Symfony context
The initial purpose of this library is to used in a symfony project. 
As you can read in the [official documentation](https://symfony.com/doc/current/deployment/proxies.html#but-what-if-the-ip-of-my-reverse-proxy-changes-constantly)  
If you are using CloudFront on top of your load balancer symfony does not provide an easy way to trust proxies traffic, as it will only trust the node sitting directly above your application (in this case your load balancer). 
You also need to append the IP addresses or ranges of any additional proxy (in this case CloudFront IP ranges) to the array of trusted proxies.

### Usage

```php
// public/index.php

$cache = new FilesystemAdapter('cloudfront_trusted_ips', 3600);
$proxyHelper = new ProxiesHelper($cache);
$trustedProxies = $proxyHelper->list();
Request::setTrustedProxies(
    array_merge(['127.0.0.1', 'REMOTE_ADDR'], $trustedProxies),
    Request::HEADER_X_FORWARDED_AWS_ELB
);
```
