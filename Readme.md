# Translation Bundle

[![Latest Version](https://img.shields.io/github/release/php-translation/symfony-bundle.svg?style=flat-square)](https://github.com/php-translation/symfony-bundle/releases)
[![Build Status](https://img.shields.io/travis/php-translation/symfony-bundle.svg?style=flat-square)](https://travis-ci.org/php-translation/symfony-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-translation/symfony-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/symfony-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/php-translation/symfony-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-translation/symfony-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c289ebe2-41c4-429f-afba-de2f905b9bdb/mini.png)](https://insight.sensiolabs.com/projects/c289ebe2-41c4-429f-afba-de2f905b9bdb)
[![Total Downloads](https://img.shields.io/packagist/dt/php-translation/symfony-bundle.svg?style=flat-square)](https://packagist.org/packages/php-translation/symfony-bundle)
[![Coding Style](https://styleci.io/repos/75462210/shield)](https://styleci.io/repos/75462210)


**Symfony integration for PHP Translation**

## Install

Via Composer

``` bash
$ composer require php-translation/symfony-bundle
```

```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Translation\Bundle\TranslationBundle(),
        }
    }
}
```

An example configuration looks like this: 

```yaml
# config.yml
translation:
    locales: ["en", "sv"]
    symfony_profiler: # must be placed in config_dev.yml
        enabled: true
    webui:
        enabled: true
    edit_in_place:
        enabled: true
        config_name: default # the first one or one of your configs
        activator: php_translation.edit_in_place.activator
    configs:
        app:
            dirs: ["%kernel.root_dir%/Resources/views", "%kernel.root_dir%/../src"]
            output_dir: "%kernel.root_dir%/Resources/translations"
            excluded_names: ["*TestCase.php", "*Test.php"]
            excluded_dirs: [cache, data, logs]
```

```yaml
# routing_dev.yml
_translation_webui:
    resource: "@TranslationBundle/Resources/config/routing_webui.yml"
    prefix:  /admin
  
_translation_profiler:
    resource: '@TranslationBundle/Resources/config/routing_symfony_profiler.yml'
```

```yaml
# routing.yml
_translation_edit_in_place:
    resource: '@TranslationBundle/Resources/config/routing_edit_in_place.yml'
    prefix:  /admin
```

## Documentation

Read the full documentation at [http://php-translation.readthedocs.io](http://php-translation.readthedocs.io/en/latest/).
