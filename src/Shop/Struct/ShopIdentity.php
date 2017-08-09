<?php

namespace Shopware\Shop\Struct;

use Shopware\Framework\Struct\Struct;
use Shopware\Locale\Struct\Locale;

class ShopIdentity extends Struct
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $isDefault;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * Id of the parent shop if current shop is a language shop,
     * Id of the current shop otherwise.
     *
     * @var int
     */
    protected $parentId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $fallbackId;

    /**
     * @var Locale
     */
    protected $locale;

    /**
     * @var int
     */
    protected $position;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): void
    {
        $this->isDefault = $isDefault;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }

    public function getFallbackId(): int
    {
        return $this->fallbackId;
    }

    public function setFallbackId(int $fallbackId): void
    {
        $this->fallbackId = $fallbackId;
    }

    public function isMain(): bool
    {
        return $this->getId() == $this->getParentId();
    }
}