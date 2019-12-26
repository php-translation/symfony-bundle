# Translation Bundle

[![Latest Version](https://img.shields.io/github/release/php-translation/symfony-bundle.svg?style=flat-square)](https://github.com/php-translation/symfony-bundle/releases)
[![Build Status](https://img.shields.io/travis/php-translation/symfony-bundle.svg?style=flat-square)](https://travis-ci.org/php-translation/symfony-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-translation/symfony-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/symfony-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-translation/symfony-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/symfony-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c289ebe2-41c4-429f-afba-de2f905b9bdb/mini.png)](https://insight.sensiolabs.com/projects/c289ebe2-41c4-429f-afba-de2f905b9bdb)
[![Total Downloads](https://img.shields.io/packagist/dt/php-translation/symfony-bundle.svg?style=flat-square)](https://packagist.org/packages/php-translation/symfony-bundle)


**Symfony integration for PHP Translation**

## Install

Install this bundle via Composer:

``` bash
$ composer require php-translation/symfony-bundle
```

If you're using [Symfony Flex][symfony_flex] - you're done! Symfony Flex will create default
configuration for you, change it if needed. If you don't use Symfony Flex, you will need to do
a few more simple steps.

1. First, register the bundle:

```php
# config/bundles.php
return [
    // ...
    Translation\Bundle\TranslationBundle::class => ['all' => true],
];
```

2. Then, configure the bundle. An example configuration looks like this:

```yaml
# config/packages/php_translation.yaml
translation:
    locales: ["en"]
    edit_in_place:
        enabled: false
        config_name: app
    configs:
        app:
            dirs: ["%kernel.project_dir%/templates", "%kernel.project_dir%/src"]
            output_dir: "%kernel.project_dir%/translations"
            excluded_names: ["*TestCase.php", "*Test.php"]
            excluded_dirs: [cache, data, logs]
```

```yaml
# config/packages/dev/php_translation.yaml
translation:
    symfony_profiler:
        enabled: true
    webui:
        enabled: true
```

3. And the last step, add new routes:

```yaml
# config/routes/dev/php_translation.yaml
_translation_webui:
    resource: "@TranslationBundle/Resources/config/routing_webui.yaml"
    prefix:  /admin

_translation_profiler:
    resource: '@TranslationBundle/Resources/config/routing_symfony_profiler.yaml'
```

```yaml
# config/routes/php_translation.yaml
_translation_edit_in_place:
    resource: '@TranslationBundle/Resources/config/routing_edit_in_place.yaml'
    prefix:  /admin
```

## Documentation

Read the full documentation at [http://php-translation.readthedocs.io](https://php-translation.readthedocs.io/en/latest/).


[symfony_flex]: https://github.com/symfony/flex
