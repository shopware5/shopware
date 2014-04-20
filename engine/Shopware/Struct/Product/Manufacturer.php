<?php

namespace Shopware\Struct\Product;

use Shopware\Struct\Extendable;

/**
 * @package Shopware\Struct
 */
class Manufacturer extends Extendable
{
    /**
     * Unique identifier of the manufacturer
     * @var int
     */
    private $id;

    /**
     * Name of the manufacturer.
     *
     * The name isn't translatable.
     *
     * @var string
     */
    private $name;

    /**
     * Description of the manufacturer.
     * This value can be translated.
     *
     * @var string
     */
    private $description;

    /**
     * Title for the seo optimization.
     *
     * @var string
     */
    private $metaTitle;

    /**
     * Description for the seo optimization.
     *
     * @var string
     */
    private $metaDescription;

    /**
     * Keywords for the seo optimization.
     *
     * @var string
     */
    private $metaKeywords;

    /**
     * Contains the link to the manufacturer home page.
     *
     * @var string
     */
    private $link;

    /**
     * Contains the file url for the cover file.
     *
     * @var string
     */
    private $coverFile;

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaKeywords
     * @return $this
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $metaTitle
     * @return $this
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getCoverFile()
    {
        return $this->coverFile;
    }

    /**
     * @param string $coverFile
     */
    public function setCoverFile($coverFile)
    {
        $this->coverFile = $coverFile;
    }
}