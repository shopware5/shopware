<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Media
{
    /**
     * @var
     */
    private $id;

    /**
     * Name of the media file
     * @var string $name
     */
    private $name;

    /**
     * @var
     */
    private $description;

    /**
     * @var boolean
     */
    private $preview;

    /**
     * @var
     */
    private $type;

    /**
     * @var string
     */
    private $file;

    /**
     * @var
     */
    private $extension;

    /**
     * @param mixed $id
     *
     */
    public function setId($id)
    {
        $this->id = $id;

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     *
     */
    public function setName($name)
    {
        $this->name = $name;

    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $type
     *
     */
    public function setType($type)
    {
        $this->type = $type;

    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param boolean $preview
     *
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

    }

    /**
     * @return boolean
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param string $file
     *
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
     * @param mixed $description
     *
     */
    public function setDescription($description)
    {
        $this->description = $description;

    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $extension
     *
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }


}