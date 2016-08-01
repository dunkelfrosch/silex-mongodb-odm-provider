<?php

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->setPsr4('DF\\Tests\\', __DIR__);
$loader->setPsr4('DF\\Tests\\DoctrineMongoDbOdm\\', __DIR__);
$loader->setPsr4('DF\\DoctrineMongoDb\\', sprintf('%s/../vendor/df/silex-doctrine-mongodb-provider/src/', __DIR__));

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
