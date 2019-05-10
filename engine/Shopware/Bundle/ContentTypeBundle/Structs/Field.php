<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\ContentTypeBundle\Structs;

use Shopware\Bundle\ContentTypeBundle\Field\FieldInterface;

class Field implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var FieldInterface
     */
    protected $type;

    /**
     * @var string
     */
    protected $typeName;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $showListing = false;

    /**
     * @var bool
     */
    protected $searchAble = true;

    /**
     * @var bool
     */
    protected $translatable = false;

    /**
     * @var string
     */
    protected $helpText;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $custom = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $flags = [];

    /**
     * @var array
     */
    protected $store;

    /**
     * @var bool
     */
    protected $required = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): FieldInterface
    {
        return $this->type;
    }

    public function setType(FieldInterface $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeName(): string
    {
        return $this->typeName;
    }

    public function setTypeName(string $typeName): self
    {
        $this->typeName = $typeName;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function isShowListing(): bool
    {
        return $this->showListing;
    }

    public function setShowListing(bool $showListing): self
    {
        $this->showListing = $showListing;

        return $this;
    }

    public function isSearchAble()
    {
        return $this->searchAble;
    }

    public function setSearchAble(bool $searchAble)
    {
        $this->searchAble = $searchAble;

        return $this;
    }

    public function isTranslatable(): bool
    {
        return $this->translatable;
    }

    public function setTranslatable(bool $translatable)
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    public function setHelpText(string $helpText): self
    {
        $this->helpText = $helpText;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCustom(): array
    {
        return $this->custom;
    }

    public function setCustom(array $custom): self
    {
        $this->custom = $custom;

        return $this;
    }

    public function getStore(): ?array
    {
        return $this->store;
    }

    public function setStore(array $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function setFlags(array $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        $vars['type'] = $this->typeName;
        unset($vars['typeName']);

        return $vars;
    }
}
