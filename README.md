# SILEX DoctrineMongoDbODMProvider

*This Silex service provider for mongodb odm is based on Dominik Zogg <dominik.zogg@gmail.com> great repository [saxulum-doctrine-mongodb-odm-provider](https://github.com/saxulum/saxulum-doctrine-mongodb-odm-provider) containing some improvements and refactoring to gain compatibility for silex 3.n and php7. This documentation isn't fully done yet - i'll working on to build the first 1.0.0 stable release within the next days*

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![System Version](https://img.shields.io/badge/version-0.9.9-blue.svg)](VERSION)
[![PHP 7 ready](http://php7ready.timesplinter.ch/dunkelfrosch/silex-mongodb-odm-provider/badge.svg?branch=master)](https://travis-ci.org/dunkelfrosch/silex-mongodb-odm-provider)
[![Build Status](https://travis-ci.org/dunkelfrosch/silex-mongodb-odm-provider.svg?branch=master)](https://travis-ci.org/dunkelfrosch/silex-mongodb-odm-provider)


Provides Doctrine MongoDB ODM Document Managers as services to Pimple applications.

## Features

 - Default Document Manager can be bound to any database connection
 - Mechanism for allowing Service Providers to register their own mappings
 - Multiple Document Managers can be defined


## Requirements

 * PHP 5.4+
 * Doctrine MongoDB ODM ~1.0


## Optional Dependencies

### PSR-0 Resource Locator Service Provider

An implementation of [dflydev/psr0-resource-locator-service-provider][3]
is required if using namespace resource mapping. See documentation
for **mongodbodm.generate_psr0_mapping** for more information.


## Installation

Through [Composer](http://getcomposer.org) as [df/silex-doctrine-mongodb-odm-provider][4].
```$
composer require df/silex-doctrine-mongodb-provider 
```


## Usage

To get up and running, register `DoctrineMongoDbOdmProvider` and
manually specify the directory that will contain the proxies along
with at least one mapping.

In each of these examples an Document Manager that is bound to the
default database connection will be provided. It will be accessible
via **mongodbodm.dm**.

```php
<?php

// Default document manager.
$em = $app['mongodbodm.dm'];
```


### Pimple

```php
<?php

use DF\DoctrineMongoDb\Provider\DoctrineMongoDbProvider;
use DF\DoctrineMongoDbOdm\Provider\DoctrineMongoDbOdmProvider;

$container = new \Pimple;

$container["mongodb.options"] = [
    "server" => "mongodb://localhost:27017",
    "options" => [
        'username' => 'your-username',
        'password' => 'your-password',
        'db' => 'mongo_db_01'
    ]
];

$container["mongodbodm.proxies_dir"] = "/path/to/proxies";
$container["mongodbodm.hydrator_dir"] = "/path/to/hydrator";
$container["mongodbodm.dm.options"] = [
    "database" => "test",
    "mappings" => [
        // Using actual filesystem paths
        [
            "type" => "annotation",
            "namespace" => "Foo\Entities",
            "path" => __DIR__."/src/Foo/Entities",
        ],
        [
            "type" => "xml",
            "namespace" => "Bat\Entities",
            "path" => __DIR__."/src/Bat/Resources/mappings",
        ],
        // Using PSR-0 namespace embedded resources
        // (requires registering a PSR-0 Resource Locator
        // Service Provider)
        [
            "type" => "annotation",
            "namespace" => "Baz\Entities",
            "resources_namespace" => "Baz\Entities",
        ],
        [
            "type" => "xml",
            "namespace" => "Bar\Entities",
            "resources_namespace" => "Bar\Resources\mappings",
        ],
    ],
];

$doctrineMongoDbServiceProvider = new DoctrineMongoDbProvider;
$doctrineMongoDbServiceProvider->register($container);

$doctrineMongoDbOdmServiceProvider = new DoctrineMongoDbOdmProvider;
$doctrineMongoDbOdmServiceProvider->register($container);
```


### Silex

```php
<?php

use DF\DoctrineMongoDb\Silex\Provider\DoctrineMongoDbProvider;
use DF\DoctrineMongoDbOdm\Silex\Provider\DoctrineMongoDbOdmProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;

$app = new Application;

$app->register(new DoctrineMongoDbProvider, [
    "mongodb.options" => [
        "server" => "mongodb://localhost:27017",
        "options" => [
            'username' => 'your-username',
            'password' => 'your-password',
            'db' => 'mongo_db_01'
        ],
    ],
]);

$app->register(new DoctrineMongoDbOdmProvider, array(
    "mongodbodm.proxies_dir" => "/path/to/proxies",
    "mongodbodm.hydrator_dir" => "/path/to/hydrator",
    "mongodbodm.dm.options" => array(
        "database" => "test",
        "mappings" => array(
            // Using actual filesystem paths
            array(
                "type" => "annotation",
                "namespace" => "Foo\Entities",
                "path" => __DIR__."/src/Foo/Entities",
            ),
            array(
                "type" => "xml",
                "namespace" => "Bat\Entities",
                "path" => __DIR__."/src/Bat/Resources/mappings",
            ),
            // Using PSR-0 namespace embedded resources
            // (requires registering a PSR-0 Resource Locator
            // Service Provider)
            array(
                "type" => "annotation",
                "namespace" => "Baz\Entities",
                "resources_namespace" => "Baz\Entities",
            ),
            array(
                "type" => "xml",
                "namespace" => "Bar\Entities",
                "resources_namespace" => "Bar\Resources\mappings",
            ),
        ),
    ),
));
```


### Cilex

```php
<?php

use DF\DoctrineMongoDb\Cilex\Provider\DoctrineMongoDbProvider;
use DF\DoctrineMongoDbOdm\Cilex\Provider\DoctrineMongoDbOdmProvider;
use Cilex\Application;
use Cilex\Provider\DoctrineServiceProvider;

$app = new Application('My Application');

$app->register(new DoctrineMongoDbProvider, array(
    /** same as the Silex example **/
));

$app->register(new DoctrineMongoDbOdmProvider, array(
    /** same as the Silex example **/
));
```


## Configuration

### Parameters

 * **mongodbodm.dm.options**:
   Array of Document Manager options.

   These options are available:
   * **connection** (Default: default):
     String defining which database connection to use. Used when using
     named databases via **mongodbs**.
   * **database**
     The database which should be uses
   * **mappings**:
     Array of mapping definitions.

     Each mapping definition should be an array with the following
     options:
     * **type**: Mapping driver type, one of `annotation`, `xml`, `yml`, `simple_xml`, `simple_yml` or `php`.
     * **namespace**: Namespace in which the entities reside.

     Additionally, each mapping definition should contain one of the
     following options:
     * **path**: Path to where the mapping files are located. This should
       be an actual filesystem path. For the php driver it can be an array
       of paths
     * **resources_namespace**: A namespace path to where the mapping
       files are located. Example: `Path\To\Foo\Resources\mappings`

     Each mapping definition can have the following optional options:
     * **alias** (Default: null): Set the alias for the document namespace.

     Each **annotation** mapping may also specify the following options:
     * **use_simple_annotation_reader** (Default: true):
       If `true`, only simple notations like `@Document` will work.
       If `false`, more advanced notations and aliasing via `use` will
       work. (Example: `use Doctrine\ODM\MongoDB\Mapping AS ODM`, `@ODM\Document`)
       Note that if set to `false`, the `AnnotationRegistry` will probably
       need to be configured correctly so that it can load your Annotations
       classes. See this FAQ:
       [Why aren't my Annotations classes being found?](#why-arent-my-annotations-classes-being-found)
   * **metadata_cache** (Default: setting specified by mongodbodm.default_cache):
     String or array describing metadata cache implementation.
   * **types**
     An array of custom types in the format of 'typeName' => 'Namespace\To\Type\Class'
 * **mongodbodm.dms.options**:
   Array of Document Manager configuration sets indexed by each Document Manager's
   name. Each value should look like **mongodbodm.dm.options**.

   Example configuration:

   ```php
   <?php
   $app['mongodbodm.dms.default'] = 'sqlite';
   $app['mongodbodm.dms.options'] = array(
        'mongo1' => array(
            'server' => 'mongodb://localhost:27017',
            'options' => array(
                'username' => 'root',
                'password' => 'root',
                'db' => 'admin'
            )
        ),
        'mongo2' => array(
            'server' => 'mongodb://localhost:27018',
            'options' => array(
                'username' => 'root',
                'password' => 'root',
                'db' => 'admin'
            )
        )
   );
   ```

   Example usage:

   ```php
   <?php
   $emMysql = $app['mongodbodm.dms']['mongo1'];
   $emSqlite = $app['mongodbodm.dms']['mongo2'];
   ```
 * **mongodbodm.dms.default** (Default: first Document Manager processed):
   String defining the name of the default Document Manager.
 * **mongodbodm.proxies_dir**:
   String defining path to where Doctrine generated proxies should be located.
 * **mongodbodm.proxies_namespace** (Default: DoctrineProxy):
   String defining namespace in which Doctrine generated proxies should reside.
 * **mongodbodm.auto_generate_proxies**:
   Boolean defining whether or not proxies should be generated automatically.
 * **mongodbodm.hydrator_dir**:
   String defining path to where Doctrine generated hydrator should be located.
 * **mongodbodm.hydrator_namespace** (Default: DoctrineHydrator):
   String defining namespace in which Doctrine generated hydrator should reside.
 * **mongodbodm.default_cache**:
   String or array describing default cache implementation.
 * **mongodbodm.add_mapping_driver**:
   Function providing the ability to add a mapping driver to an Document Manager.

   These params are available:
    * **$mappingDriver**:
      Mapping driver to be added,
      instance `Doctrine\Common\Persistence\Mapping\Driver\MappingDriver`.
    * **$namespace**:
      Namespace to be mapped by `$mappingDriver`, string.
    * **$name**:
      Name of Document Manager to add mapping to, string, default `null`.
 * **mongodbodm.dm_name_from_param**:
   Function providing the ability to retrieve an document manager's name from
   a param.

   This is useful for being able to optionally allow users to specify which
   document manager should be configured for a 3rd party service provider
   but fallback to the default document manager if not explitely declared.

   For example:

   ```php
   <?php
   $emName = $app['mongodbodm.dm_name_from_param']('3rdparty.provider.dm');
   $em = $app['mongodbodm.dms'][$emName];
   ```

   This code should be able to be used inside of a 3rd party service provider
   safely, whether the user has defined `3rdparty.provider.dm` or not.
 * **mongodbodm.generate_psr0_mapping**:
   Leverages [dflydev/psr0-resource-locator-service-provider][3] to process
   a map of namespaceish resource directories to their mapped entities.

   Example usage:
   ```php
   <?php
   $app['mongodbodm.dms.config'] = $app->share($app->extend('mongodbodm.dms.config', function ($config, $app) {
       $mapping = $app['mongodbodm.generate_psr0_mapping'](array(
           'Foo\Resources\mappings' => 'Foo\Entities',
           'Bar\Resources\mappings' => 'Bar\Entities',
       ));

       $chain = $app['mongodbodm.mapping_driver_chain.locator']();

       foreach ($mapping as $directory => $namespace) {
           $driver = new XmlDriver($directory);
           $chain->addDriver($driver, $namespace);
       }

       return $config;
   }));
   ```

### Services

 * **mongodbodm.dm**:
   Document Manager, instance `Doctrine\ODM\MongoDB\DocumentManager`.
 * **mongodbodm.dms**:
   Document Managers, array of `Doctrine\ODM\MongoDB\DocumentManager` indexed by name.


## Frequently Asked Questions

### Why aren't my Annotations classes being found?

When **use_simple_annotation_reader** is set to `False` for an document,
the `AnnotationRegistry` needs to have the project's autoloader added
to it.

Example:

```php
<?php
$loader = require __DIR__ . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
```


## License

MIT, see LICENSE.


## Not Invented Here


This project is based heavily on the work done by [@dflydev][1]
on the [dflydev/dflydev-doctrine-orm-service-provider][2] project.


## Copyright

- Patrick Paechnatz <patrick.paechnatz@gmail.com>
- Dominik Zogg <dominik.zogg@gmail.com>
- Beau Simensen <beau@dflydev.com> ([Doctrine ORM Service Provider][2])


[1]: https://github.com/dflydev
[2]: https://github.com/dflydev/dflydev-doctrine-orm-service-provider
[3]: https://github.com/dflydev/dflydev-psr0-resource-locator-service-provider
[4]: https://packagist.org/packages/df/silex-doctrine-mongodb-odm-provider
