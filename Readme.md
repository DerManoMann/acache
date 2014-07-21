ACache, a flexible PHP cache library
====================================

[![Build Status](https://travis-ci.org/DerManoMann/acache.png)](https://travis-ci.org/DerManoMann/acache)
[![Coverage Status](https://coveralls.io/repos/DerManoMann/acache/badge.png)](https://coveralls.io/r/DerManoMann/acache)

ACache - another PHP cache library.

```php
<?php
require_once __DIR__.'/vendor/autoload.php';

$cache = new ACache\ArrayCache();

$cache->save('yin', 'yang');

echo 'yin and '.$cache->fetch('yin');
```

ACache requires PHP 5.3 or later.



## Features

ACache is inspired by the doctrine [cache][https://github.com/doctrine/cache] component.
In fact, there is even a decorator to allow you to use any ACache instance in place of doctrine cache.

Since some features were hard to add on top of that I ended up writing my own :)


### Namespaces

The `ACache\Cache` interface allows to explicitely use a namespace for any given id.

```php
<?php
include 'vendor/autoload.php';
define('MY_NAMESPACE', 'my');

$cache = new ACache\ArrayCache();

$cache->save('yin', 'yang', MY_NAMESPACE);

echo 'my yin and '.$cache->fetch('yin', MY_NAMESPACE).PHP_EOL;
```

While that works well it sometimes is desirable to do this a little bit more transparent (and save some typing).

```php
<?php
include 'vendor/autoload.php';
define('MY_NAMESPACE', 'my');

$cache = new ACache\ArrayCache();
// wrap given cache in namespace
$myCache = new ACache\NamespaceCache($cache, MY_NAMESPACE);

$myCache->save('yin', 'yang');

echo 'my yin and '.$myCache->fetch('yin').PHP_EOL;
// or, using the decorated cache directly
echo 'my yin and '.$cache->fetch('yin', MY_NAMESPACE).PHP_EOL;
```

Wrapping an existing cache instance in a `ACache\NamespaceCache` effectively allows to partition that cache without the need to 
carry the namespace around for all method calls.


### Multi level

Sometimes losing and re-building your cache due to a reboot or similar can be quite expensive. One way to cope with that is by using a multi-level cache.

A fast (non-persistent) cache is used as primary cache. If an entry cannot be found in that (for example, due to a reboot) it will fall back to a persistent cache (filesystem, db).
Only if all configured cache instances are queried an entry would be declared as not found.

```php
<?php
include 'vendor/autoload.php';

// two level cache stack
$cache = new ACache\MultiLevelCache(array(
    new ACache\ArrayCache(),
    new ACache\FilesystemCache(__DIR__.'/cache')
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

$cache = new ACache\MultiLevelCache(array(
    new ACache\ArrayCache(),
    new ACache\FilesystemCache(__DIR__.'/cache')
));

// save both in ArrayCache and FilesystemCache
//$cache->save('yin', 'yang');

// lookup will only use ArrayCache
echo 'my yin and '.$cache->fetch('yin').PHP_EOL;
```

Here the `ACache\ArrayCache` instance will be empty and the `ACache\MultiLevelCache` will fall back to using the file based cache to lookup (and find)
the cache entry.


### Nesting

Both namespace and multi-level cache instances can be arbitrary nested.



## Installation

The recommended way to install ACache is [through
composer](http://getcomposer.org). Just create a `composer.json` file and
run the `php composer.phar install` command to install it:

    {
        "require": {
            "radebatz/acache": "1.0.*@dev"
        }
    }

Alternatively, you can download the [`acache.zip`][https://github.com/DerManoMann/acache/archive/master.zip] file and extract it.



## Tests

ACache comes with a pretty complete set of tests for a single cache instance and also
combinations of multi-level and namespace caches.

To run the test suite, you will need [PHPUnit](http://phpunit.de/manual/current/en/).



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
