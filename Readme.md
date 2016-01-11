ACache, a flexible PHP cache library
====================================

[![Build Status](https://travis-ci.org/DerManoMann/acache.png)](https://travis-ci.org/DerManoMann/acache)
[![Coverage Status](https://coveralls.io/repos/DerManoMann/acache/badge.png)](https://coveralls.io/r/DerManoMann/acache)

ACache - another PHP cache library.

```php
<?php
require_once __DIR__.'/vendor/autoload.php';

$cache = new Radebatz\ACache\ArrayCache();

$cache->save('yin', 'yang');

echo 'yin and '.$cache->fetch('yin');
```

ACache requires PHP 5.3 or later.


## Installation

Install the latest version with
```
$ composer require radebatz/acache
```


## Features

ACache is inspired by the doctrine [cache](https://github.com/doctrine/cache) component.
In fact, there is even a decorator to allow you to use any ACache instance in place of doctrine cache.

Since some features were hard to add on top of that I ended up writing my own :)


### Namespaces

The `Radebatz\ACache\Cache` interface allows to explicitely use a namespace for any given id.

```php
<?php
include 'vendor/autoload.php';
define('MY_NAMESPACE', 'my');

$cache = new Radebatz\ACache\ArrayCache();

$cache->save('yin', 'yang', MY_NAMESPACE);

echo 'my yin and '.$cache->fetch('yin', MY_NAMESPACE).PHP_EOL;
```

While that works well it sometimes is desirable to do this a little bit more transparent (and save some typing).

```php
<?php
include 'vendor/autoload.php';
define('MY_NAMESPACE', 'my');

$cache = new Radebatz\ACache\ArrayCache();
// wrap given cache in namespace
$myCache = new Radebatz\ACache\NamespaceCache($cache, MY_NAMESPACE);

$myCache->save('yin', 'yang');

echo 'my yin and '.$myCache->fetch('yin').PHP_EOL;
// or, using the decorated cache directly
echo 'my yin and '.$cache->fetch('yin', MY_NAMESPACE).PHP_EOL;
```

Wrapping an existing cache instance in a `Radebatz\ACache\NamespaceCache` effectively allows to partition that cache without the need to 
carry the namespace around for all method calls.


### Multi level

Sometimes losing and re-building your cache due to a reboot or similar can be quite expensive. One way to cope with that is by using a multi-level cache.

A fast (non-persistent) cache is used as primary cache. If an entry cannot be found in that (for example, due to a reboot) it will fall back to a persistent cache (filesystem, db).
Only if all configured cache instances are queried an entry would be declared as not found.

```php
<?php
include 'vendor/autoload.php';

// two level cache stack
$cache = new Radebatz\ACache\MultiLevelCache(array(
    new Radebatz\ACache\ArrayCache(),
    new Radebatz\ACache\FilesystemCache(__DIR__.'/cache')
));

// save both in ArrayCache and FilesystemCache
$cache->save('yin', 'yang');

// lookup will only use ArrayCache
echo 'my yin and '.$cache->fetch('yin').PHP_EOL;
```

Running the same code again will result in the same output, even if the `save()` call is commented out.

```php
<?php
include 'vendor/autoload.php';

$cache = new Radebatz\ACache\MultiLevelCache(array(
    new Radebatz\ACache\ArrayCache(),
    new Radebatz\ACache\FilesystemCache(__DIR__.'/cache')
));

// save both in ArrayCache and FilesystemCache
//$cache->save('yin', 'yang');

// lookup will only use ArrayCache
echo 'my yin and '.$cache->fetch('yin').PHP_EOL;
```

Here the `Radebatz\ACache\ArrayCache` instance will be empty and the `Radebatz\ACache\MultiLevelCache` will fall back to using the file based cache to lookup (and find)
the cache entry.


### Nesting

Both namespace and multi-level cache instances can be arbitrary nested.


### Psr/Cache

All cache instances can be used as `Psr\Cache\CacheItemPoolInterface` instances by wrapping them in a Psr decorator.

````
use Radebatz\ACache\ArrayCache;
use Radebatz\ACache\Decorators\Psr\CacheItemPool;

$cache = new ArrayCache();

$psrCache = new CacheItemPool($cache);

$cacheItem = $prsCache->getItem('foo');
...

````


## Testing

ACache comes with a pretty complete set of tests for a single cache instance and also
combinations of multi-level and namespace caches.


## License

ACache is licensed under the MIT license.


## Changelog
All issues that break backwards compatibility are flagged [BC].

### v1.1.0
* [BC] make namespace last argument of `save()` method [#4]
* add changelog to provide upgrade details [#8]
* add `DoctrineCache` decorator class [#9]
* make autoloader use prs-4
* allow to override defaults in PHPUnit tests [#12]
* add `NullCache` class [#13]

### v1.1.1
* [BC] allow to configure a default time-to-live [#7]
  This changes the default of `$lifeTime` argument of the `save()` method to `null`
* add some sort of GC to ApcCache [#16]

### v1.2.0
* [BC] Refactor Apc GC into separat class [#18]

### v1.2.1
* Add log support to MultiLevelCache [#1]
* Streamline multi level cache stack validation

### v1.2.2
* Add psr-6 support

### v1.2.3
* Add option to allow ArrayCache instances to share a single cache instance [#27]
* Integrate 'cache/integrationtests' (PHP5.4+ only)
