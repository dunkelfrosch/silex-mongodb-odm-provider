<?php

namespace DF\Tests\DoctrineMongoDbOdm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Page
 *
 * @ODM\Document(
 *     collection="Page",
 *     requireIndexes=false
 * )
 *
 * @package DF\Tests\DoctrineMongoDbOdm\Document
 *
 * @codeCoverageIgnore
 */
class Page
{
    /**
     * @var string
     * 
     * @ODM\Id
     */
    protected $id;

    /**
     * @var string
     * 
     * @ODM\Field(type="string")
     */
    protected $title;

    /**
     * @var string
     * 
     * @ODM\Field(type="string")
     */
    protected $body;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $title
     * 
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $body
     * 
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
