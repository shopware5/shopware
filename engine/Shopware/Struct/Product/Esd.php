<?php

namespace Shopware\Struct\Product;

use Shopware\Struct\Extendable;

class Esd extends Extendable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var bool
     */
    protected $hasSerials;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $hasSerials
     */
    public function setHasSerials($hasSerials)
    {
        $this->hasSerials = $hasSerials;
    }

    /**
     * @return boolean
     */
    public function hasSerials()
    {
        return $this->hasSerials;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }


}