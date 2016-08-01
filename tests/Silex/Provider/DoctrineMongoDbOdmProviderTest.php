<?php

namespace DF\Tests\DoctrineMongoDbOdm\Silex\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Silex\Application;
use Doctrine\ODM\MongoDB\Configuration as MongoDbConfiguration;
use Doctrine\ODM\MongoDB\DocumentRepository;
use DF\DoctrineMongoDb\Silex\Provider\DoctrineMongoDbProvider;
use DF\DoctrineMongoDbOdm\Silex\Provider\DoctrineMongoDbOdmProvider;
use DF\Tests\DoctrineMongoDbOdm\Document\Page;
use DF\Tests\DoctrineTestCase;

/**
 * Class DoctrineMongoDbOdmProviderTest
 *
 * @package DF\Tests\DoctrineMongoDbOdm\Silex\Provider
 */
class DoctrineMongoDbOdmProviderTest extends DoctrineTestCase
{
    /**
     * check/test registration
     */
    public function testRegister()
    {
        /** @var Application $app */
        $app = $this->createMockDefaultApp();
        $app->register(new DoctrineMongoDbOdmProvider);
        /** @var MongoDbConfiguration $mongoDbConfig */
        $mongoDbConfig = $app['mongodbodm.dm.config'];

        $this->assertEquals($app['mongodbodm.dm'], $app['mongodbodm.dms']['default']);
        $this->assertInstanceOf('Doctrine\Common\Cache\ArrayCache', $mongoDbConfig->getMetadataCacheImpl());
        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain', $mongoDbConfig->getMetadataDriverImpl());
    }

    /**
     * check/test annotation mapping
     */
    public function testAnnotationMapping()
    {
        $proxyPath = sprintf('%s/doctrine/proxies', $this->getCacheDir());
        $hydratorPath = sprintf('%s/doctrine/hydrator', $this->getCacheDir());

        @mkdir($proxyPath, 0777, true);
        @mkdir($hydratorPath, 0777, true);

        $app = new Application;

        $app->register(new DoctrineMongoDbProvider(), array(
            'mongodb.options' => array(
                'server' => 'mongodb://localhost:27017'
            )
        ));

        $app->register(new DoctrineMongoDbOdmProvider, [
            "mongodbodm.proxies_dir" => $proxyPath,
            "mongodbodm.hydrator_dir" => $hydratorPath,
            "mongodbodm.dm.options" => [
                "database" => "test",
                "mappings" => [
                    [
                        "type" => "annotation",
                        "namespace" => "DF\\Tests\\DoctrineMongoDbOdm\\Document",
                        "path" => __DIR__."../../Document",
                        "use_simple_annotation_reader" => false
                    ]
                ],
            ],
        ]);

        $title = 'my test title';
        $body = 'my test body';

        $page = new Page();
        $page->setTitle($title);
        $page->setBody($body);

        /** @var DocumentManager $dm */
        $dm = $app['mongodbodm.dm'];
        $dm->persist($page);
        $dm->flush();

        /** @var DocumentRepository $repository */
        $repository = $dm->getRepository("DF\\Tests\\DoctrineMongoDbOdm\\Document\\Page");

        /** @var Page $pageFromDb */
        $pageFromDb = $repository->find($page->getId());
        $this->assertTrue($pageFromDb instanceof Page);
        $this->assertEquals($title, $pageFromDb->getTitle());
        $this->assertEquals($body, $pageFromDb->getBody());
    }
}
