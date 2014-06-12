<?php

namespace Shopware\Struct;

class Category extends Extendable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var array
     */
    private $path;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $metaKeywords;

    /**
     * @var string
     */
    private $metaDescription;

    /**
     * @var string
     */
    private $cmsHeadline;

    /**
     * @var string
     */
    private $cmsText;

    /**
     * @var string
     */
    private $template;

    /**
     * @var boolean
     */
    private $blog;

    /**
     * @var boolean
     */
    private $allowViewSelect;

    /**
     * @var boolean
     */
    private $displayPropertySets;

    /**
     * @var boolean
     */
    private $displayFacets;

    /**
     * @var boolean
     */
    private $displayInNavigation;

    /**
     * @var string
     */
    private $externalLink;

    /**
     * @var Media
     */
    private $media;

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
     * @param array $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $cmsHeadline
     */
    public function setCmsHeadline($cmsHeadline)
    {
        $this->cmsHeadline = $cmsHeadline;
    }

    /**
     * @return string
     */
    public function getCmsHeadline()
    {
        return $this->cmsHeadline;
    }

    /**
     * @param string $cmsText
     */
    public function setCmsText($cmsText)
    {
        $this->cmsText = $cmsText;
    }

    /**
     * @return string
     */
    public function getCmsText()
    {
        return $this->cmsText;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
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
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $externalLink
     */
    public function setExternalLink($externalLink)
    {
        $this->externalLink = $externalLink;
    }

    /**
     * @return string
     */
    public function getExternalLink()
    {
        return $this->externalLink;
    }

    /**
     * @param boolean $allowViewSelect
     */
    public function setAllowViewSelect($allowViewSelect)
    {
        $this->allowViewSelect = $allowViewSelect;
    }

    /**
     * @param boolean $displayFacets
     */
    public function setDisplayFacets($displayFacets)
    {
        $this->displayFacets = $displayFacets;
    }

    /**
     * @param boolean $displayInNavigation
     */
    public function setDisplayInNavigation($displayInNavigation)
    {
        $this->displayInNavigation = $displayInNavigation;
    }

    /**
     * @param boolean $displayPropertySets
     */
    public function setDisplayPropertySets($displayPropertySets)
    {
        $this->displayPropertySets = $displayPropertySets;
    }

    /**
     * @param boolean $blog
     */
    public function setBlog($blog)
    {
        $this->blog = $blog;
    }

    /**
     * @param \Shopware\Struct\Media $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return \Shopware\Struct\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return boolean
     */
    public function allowViewSelect()
    {
        return $this->allowViewSelect;
    }

    /**
     * @return boolean
     */
    public function isBlog()
    {
        return $this->blog;
    }

    /**
     * @return boolean
     */
    public function displayFacets()
    {
        return $this->displayFacets;
    }

    /**
     * @return boolean
     */
    public function displayInNavigation()
    {
        return $this->displayInNavigation;
    }

    /**
     * @return boolean
     */
    public function displayPropertySets()
    {
        return $this->displayPropertySets;
    }
}
