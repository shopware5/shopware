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

use JsonSerializable;

class Type implements JsonSerializable
{
    /**
     * @var string
     */
    protected $internalName;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Field[]
     */
    protected $fields;

    /**
     * @var Fieldset[]
     */
    protected $fieldSets;

    /**
     * @var string
     */
    protected $menuIcon = 'sprite-application-block';

    /**
     * @var int
     */
    protected $menuPosition = 200;

    /**
     * @var string
     */
    protected $menuParent = 'Content';

    /**
     * @var string
     */
    protected $source;

    /**
     * @var array
     */
    protected $custom;

    /**
     * @var bool
     */
    protected $showInFrontend = false;

    /**
     * @var string
     */
    protected $viewTitleFieldName;

    /**
     * @var string
     */
    protected $viewDescriptionFieldName;

    /**
     * @var string
     */
    protected $viewImageFieldName;

    /**
     * @var string
     */
    protected $viewMetaTitleFieldName;

    /**
     * @var string
     */
    protected $viewMetaDescriptionFieldName;

    /**
     * @var string
     */
    protected $seoUrlTemplate = '{$type.name}/{$item[$type.viewTitleFieldName]}';

    /**
     * @var string
     */
    protected $seoRobots = 'index,follow';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param Field[] $fields
     *
     * @return Type
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getControllerName(): string
    {
        return 'Custom' . ucfirst($this->internalName);
    }

    public function getTableName(): string
    {
        return 's_custom_' . strtolower($this->internalName);
    }

    public function getSnippetNamespaceBackend(): string
    {
        return 'backend/' . strtolower($this->getControllerName()) . '/main';
    }

    public function getSnippetNamespaceFrontend(): string
    {
        return 'frontend/' . strtolower($this->getControllerName()) . '/main';
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function setInternalName(string $internalName): self
    {
        $this->internalName = $internalName;

        return $this;
    }

    /**
     * @return Fieldset[]
     */
    public function getFieldSets(): array
    {
        return $this->fieldSets;
    }

    public function setFieldSets(array $fieldSets): self
    {
        $this->fieldSets = $fieldSets;

        return $this;
    }

    public function getMenuIcon(): string
    {
        return $this->menuIcon;
    }

    public function setMenuIcon(string $menuIcon): self
    {
        $this->menuIcon = $menuIcon;

        return $this;
    }

    public function getMenuPosition(): int
    {
        return $this->menuPosition;
    }

    public function setMenuPosition(int $menuPosition): self
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    public function getMenuParent(): string
    {
        return $this->menuParent;
    }

    public function setMenuParent(string $menuParent): self
    {
        $this->menuParent = $menuParent;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getCustom(): array
    {
        return $this->custom;
    }

    public function setCustom(array $custom): Type
    {
        $this->custom = $custom;

        return $this;
    }

    public function isShowInFrontend(): bool
    {
        return $this->showInFrontend;
    }

    public function setShowInFrontend(bool $showInFrontend): self
    {
        $this->showInFrontend = $showInFrontend;

        return $this;
    }

    public function getViewTitleFieldName(): string
    {
        return $this->viewTitleFieldName;
    }

    public function setViewTitleFieldName(string $viewTitleFieldName): self
    {
        $this->viewTitleFieldName = $viewTitleFieldName;

        return $this;
    }

    public function getViewDescriptionFieldName(): string
    {
        return $this->viewDescriptionFieldName;
    }

    public function setViewDescriptionFieldName(string $viewDescriptionFieldName): self
    {
        $this->viewDescriptionFieldName = $viewDescriptionFieldName;

        return $this;
    }

    public function getViewImageFieldName(): string
    {
        return $this->viewImageFieldName;
    }

    public function setViewImageFieldName(string $viewImageFieldName): self
    {
        $this->viewImageFieldName = $viewImageFieldName;

        return $this;
    }

    public function getViewMetaTitleFieldName(): string
    {
        if (empty($this->viewMetaTitleFieldName)) {
            return $this->viewTitleFieldName;
        }

        return $this->viewMetaTitleFieldName;
    }

    public function setViewMetaTitleFieldName(string $viewMetaTitleFieldName): Type
    {
        $this->viewMetaTitleFieldName = $viewMetaTitleFieldName;

        return $this;
    }

    public function getViewMetaDescriptionFieldName(): string
    {
        if (empty($this->viewMetaDescriptionFieldName)) {
            return $this->viewDescriptionFieldName;
        }

        return $this->viewMetaDescriptionFieldName;
    }

    public function setViewMetaDescriptionFieldName(string $viewMetaDescriptionFieldName): Type
    {
        $this->viewMetaDescriptionFieldName = $viewMetaDescriptionFieldName;

        return $this;
    }

    public function getSeoUrlTemplate(): string
    {
        return $this->seoUrlTemplate;
    }

    public function setSeoUrlTemplate(string $seoUrlTemplate): Type
    {
        $this->seoUrlTemplate = $seoUrlTemplate;

        return $this;
    }

    public function getSeoRobots(): string
    {
        return $this->seoRobots;
    }

    public function setSeoRobots(string $seoRobots): self
    {
        $this->seoRobots = $seoRobots;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
