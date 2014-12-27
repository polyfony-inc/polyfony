[![SensioLabsInsight](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0/big.png)](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0)

## Polyfony 2 is a PHP micro framework that brings the cool parts of Symfony in a simpler way

Support for : routing, bundles, controllers, views, database abstraction, environments, locales, cache… 
Without pre-compilation stage, cumbersome cache, dozens configuration files, composer, cli binary or other annoying steps.


## Requirements
* PHP >= 5.3 with mbstring and PDO
* A rewrite module (mod_rewrite)

## Installation
* Point your domain to `/Public/`
* Under Apache, `/Public/.htaccess` already rewrites everything
* Under lighttpd, set this rewrite rule
```php
url.rewrite-once = ("^(?!/Assets/).*" => "/?")
```

## Quick tour
You can read this quick tour, or just browe the `../Private/Bundles/Demo/` code.

```php

// quick tour will come soon

```

## Performance
Polyfony has been designed to be fast, no compromise.
The whole framework takes less than 400 Kb of disk space (a third of it being comment lines) and runs on 650 Kb of RAM.

## Security
The codebase is small, straightforward and abundantly commented. It's audited using SensioInsight, RIPS, and Sonar.

## Coding Standard
Polyfony2 follows the PSR-0, PSR-1, PSR-4 coding standards. It does not respect PSR-2, as tabs are used for indentation.
