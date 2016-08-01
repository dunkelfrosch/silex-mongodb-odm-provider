<?php

namespace DF\Tests\DoctrineMongoDbOdm\Provider;

use MongoDB\Exception\InvalidArgumentException;
use DF\DoctrineMongoDbOdm\Provider\DoctrineMongoDbOdmProvider;
use DF\Tests\DoctrineTestCase;

/**
 * Class DoctrineMongoDbOdmProviderTest
 * 
 * @package DF\Tests\DoctrineMongoDbOdm\Provider
 */
class DoctrineMongoDbOdmProviderTest extends DoctrineTestCase
{
    /**
     * Test registration (test expected class for default implementations)
     */
    public function testRegisterDefaultImplementations()
    {
        $container = $this->createMockDefaultApp();

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $this->assertEquals($container['mongodbodm.dm'], $container['mongodbodm.dms']['default']);
        $this->assertInstanceOf('Doctrine\Common\Cache\ArrayCache', $container['mongodbodm.dm.config']->getMetadataCacheImpl());
        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain', $container['mongodbodm.dm.config']->getMetadataDriverImpl());
    }

    /**
     * Test registration (test equality for defined implementations)
     */
    public function testRegisterDefinedImplementations()
    {
        $container = $this->createMockDefaultApp();

        $metadataCache = $this->getMock('Doctrine\Common\Cache\ArrayCache');

        $mappingDriverChain = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain');

        $container['mongodbodm.cache.instances.default.metadata'] = $metadataCache;

        $container['mongodbodm.mapping_driver_chain.instances.default'] = $mappingDriverChain;

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $this->assertEquals($container['mongodbodm.dm'], $container['mongodbodm.dms']['default']);
        $this->assertEquals($metadataCache, $container['mongodbodm.dm.config']->getMetadataCacheImpl());
        $this->assertEquals($mappingDriverChain, $container['mongodbodm.dm.config']->getMetadataDriverImpl());
    }

    /**
     * Test proxy configuration (defaults)
     */
    public function testProxyConfigurationDefaults()
    {
        $container = $this->createMockDefaultApp();

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $this->assertContains('/../../cache/doctrine/proxies', $container['mongodbodm.dm.config']->getProxyDir());
        $this->assertEquals('DoctrineProxy', $container['mongodbodm.dm.config']->getProxyNamespace());
        $this->assertTrue($container['mongodbodm.dm.config']->getAutoGenerateProxyClasses());
    }

    /**
     * Test proxy configuration (defined)
     */
    public function testProxyConfigurationDefined()
    {
        $container = $this->createMockDefaultApp();

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $container['mongodbodm.proxies_dir'] = '/path/to/proxies';
        $container['mongodbodm.proxies_namespace'] = 'TestDoctrineMongoDbOdmProxiesNamespace';
        $container['mongodbodm.auto_generate_proxies'] = false;

        $this->assertEquals('/path/to/proxies', $container['mongodbodm.dm.config']->getProxyDir());
        $this->assertEquals('TestDoctrineMongoDbOdmProxiesNamespace', $container['mongodbodm.dm.config']->getProxyNamespace());
        $this->assertFalse($container['mongodbodm.dm.config']->getAutoGenerateProxyClasses());
    }

    /**
     * Test hydrator configuration (defaults)
     */
    public function testHydratorConfigurationDefaults()
    {
        $container = $this->createMockDefaultApp();

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $this->assertContains('/../../cache/doctrine/hydrator', $container['mongodbodm.dm.config']->getHydratorDir());
        $this->assertEquals('DoctrineHydrator', $container['mongodbodm.dm.config']->getHydratorNamespace());
        $this->assertTrue($container['mongodbodm.dm.config']->getAutoGenerateHydratorClasses());
    }

    /**
     * Test hydrator configuration (defined)
     */
    public function testHydratorConfigurationDefined()
    {
        $container = $this->createMockDefaultApp();

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $container['mongodbodm.hydrator_dir'] = '/path/to/hydrators';
        $container['mongodbodm.hydrator_namespace'] = 'TestDoctrineMongoDbOdmHydratorsNamespace';
        $container['mongodbodm.auto_generate_hydrators'] = false;

        $this->assertEquals('/path/to/hydrators', $container['mongodbodm.dm.config']->getHydratorDir());
        $this->assertEquals('TestDoctrineMongoDbOdmHydratorsNamespace', $container['mongodbodm.dm.config']->getHydratorNamespace());
        $this->assertFalse($container['mongodbodm.dm.config']->getAutoGenerateHydratorClasses());
    }

    /**
     * Test Driver Chain locator
     */
    public function testMappingDriverChainLocator()
    {
        $container = $this->createMockDefaultApp();

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $default = $container['mongodbodm.mapping_driver_chain.locator']();
        $this->assertEquals($default, $container['mongodbodm.mapping_driver_chain.locator']('default'));
        $this->assertEquals($default, $container['mongodbodm.dm.config']->getMetadataDriverImpl());
    }

    /**
     * Test adding a mapping driver (use default document manager)
     */
    public function testAddMappingDriverDefault()
    {
        $container = $this->createMockDefaultApp();

        $mappingDriver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver');

        $mappingDriverChain = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain');
        $mappingDriverChain
            ->expects($this->once())
            ->method('addDriver')
            ->with($mappingDriver, 'Test\Namespace');

        $container['mongodbodm.mapping_driver_chain.instances.default'] = $mappingDriverChain;

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $container['mongodbodm.add_mapping_driver']($mappingDriver, 'Test\Namespace');
    }

    /**
     * Test adding a mapping driver (specify default document manager by name)
     */
    public function testAddMappingDriverNamedEntityManager()
    {
        $container = $this->createMockDefaultApp();

        $mappingDriver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver');

        $mappingDriverChain = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain');
        $mappingDriverChain
            ->expects($this->once())
            ->method('addDriver')
            ->with($mappingDriver, 'Test\Namespace');

        $container['mongodbodm.mapping_driver_chain.instances.default'] = $mappingDriverChain;

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $container['mongodbodm.add_mapping_driver']($mappingDriver, 'Test\Namespace');
    }

    /**
     * Test mongodbodm.dm_name_from_param_key ()
     */
    public function testNameFromParamKey()
    {
        $container = $this->createMockDefaultApp();

        $container['my.baz'] = 'baz';

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $container['mongodbodm.dms.default'] = 'foo';

        $this->assertEquals('foo', $container['mongodbodm.dms.default']);
        $this->assertEquals('foo', $container['mongodbodm.dm_name_from_param_key']('my.bar'));
        $this->assertEquals('baz', $container['mongodbodm.dm_name_from_param_key']('my.baz'));
    }

    /**
     * Test specifying an invalid mapping configuration (not an array of arrays)
     *
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage The 'mongodbodm.dm.options' option 'mappings' should be an array of arrays.
     */
    public function testInvalidMappingAsOption()
    {
        $container = $this->createMockDefaultApp();
        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $container['mongodbodm.dm.options'] = [
            'mappings' => [
                'type' => 'annotation',
                'namespace' => 'Foo\Entities',
                'path' => __DIR__.'/src/Foo/Entities',
            ],
        ];

        $container['mongodbodm.dms.config'];
    }

    /**
     * Test if namespace alias can be set through the mapping options
     */
    public function testMappingAlias()
    {
        $container = $this->createMockDefaultApp();

        $doctrineOrmServiceProvider = new DoctrineMongoDbOdmProvider;
        $doctrineOrmServiceProvider->register($container);

        $alias = 'Foo';
        $namespace = 'Foo\Entities';

        $container['mongodbodm.dm.options'] = [
            'mappings' => [
                [
                    'type' => 'annotation',
                    'namespace' => $namespace,
                    'path' => __DIR__.'/src/Foo/Entities',
                    'alias' => $alias
                ]
            ],
        ];

        $this->assertEquals($namespace, $container['mongodbodm.dm.config']->getDocumentNameSpace($alias));
    }
}
