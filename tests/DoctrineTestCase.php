<?php

namespace DF\Tests;

use Silex\Application;
use Pimple\Container;
use DF\DoctrineMongoDbOdm\Provider\DoctrineMongoDbOdmProvider;
use Doctrine\ODM\MongoDB\Configuration as MongoDbConfiguration;

/**
 * Class DoctrineTestCase
 *
 * @package DF\Tests
 */
class DoctrineTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var MongoDbConfiguration $mongoDbConfig */
    protected $mongoDbConfig;

    /**
     * @return MongoDbConfiguration
     */
    public function getMongoDbConfig()
    {
        return $this->mongoDbConfig;
    }

    /**
     * @param MongoDbConfiguration $mongoDbConfig
     */
    public function setMongoDbConfig($mongoDbConfig)
    {
        $this->mongoDbConfig = $mongoDbConfig;
    }

    /**
     * @return string
     */
    protected function getCacheDir()
    {
        $cacheDir =  sprintf('%s/../../../cache', __DIR__);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        return $cacheDir;
    }

    /**
     * @return Container
     */
    protected function createMockDefaultApp()
    {
        list ($app, $connection, $eventManager) = $this->createMockDefaultAppAndDependencies();

        return $app;
    }

    /**
     * @return array
     */
    protected function createMockDefaultAppAndDependencies()
    {
        $app = new Application;

        $eventManager = $this->getMock('Doctrine\Common\EventManager');
        $connection = $this
            ->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));

        $app['mongodbs'] = new Container(['default' => $connection]);
        $app['mongodbs.event_manager'] = new Container(['default' => $eventManager]);

        return [$app, $connection, $eventManager];
    }

    protected function setUp()
    {
        parent::setUp();

        /** @var Container $app */
        $app = $this->createMockDefaultApp();
        $app->register(new DoctrineMongoDbOdmProvider);

        /** @var MongoDbConfiguration $mongoDbConfig */
        $this->mongoDbConfig = $app['mongodbodm.dm.config'];
    }
}
