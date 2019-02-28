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

namespace Shopware\Bundle\EmotionBundle\Struct;

use Shopware\Bundle\EmotionBundle\Struct\Library\Component;

class Element implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $emotionId;

    /**
     * @var int
     */
    protected $componentId;

    /**
     * @var int
     */
    protected $startRow;

    /**
     * @var int
     */
    protected $endRow;

    /**
     * @var int
     */
    protected $startCol;

    /**
     * @var int
     */
    protected $endCol;

    /**
     * @var string
     */
    protected $cssClass;

    /**
     * @var ElementConfig
     */
    protected $config;

    /**
     * @var Component
     */
    protected $component;

    /**
     * @var ElementViewport[]
     */
    protected $viewports = [];

    /**
     * @var ElementData
     */
    protected $data;

    public function __construct()
    {
        $this->data = new ElementData();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

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
    public function getEmotionId()
    {
        return $this->emotionId;
    }

    /**
     * @param int $emotionId
     */
    public function setEmotionId($emotionId)
    {
        $this->emotionId = $emotionId;
    }

    /**
     * @return int
     */
    public function getComponentId()
    {
        return $this->componentId;
    }

    /**
     * @param int $componentId
     */
    public function setComponentId($componentId)
    {
        $this->componentId = $componentId;
    }

    /**
     * @return int
     */
    public function getStartRow()
    {
        return $this->startRow;
    }

    /**
     * @param int $startRow
     */
    public function setStartRow($startRow)
    {
        $this->startRow = $startRow;
    }

    /**
     * @return int
     */
    public function getEndRow()
    {
        return $this->endRow;
    }

    /**
     * @param int $endRow
     */
    public function setEndRow($endRow)
    {
        $this->endRow = $endRow;
    }

    /**
     * @return int
     */
    public function getStartCol()
    {
        return $this->startCol;
    }

    /**
     * @param int $startCol
     */
    public function setStartCol($startCol)
    {
        $this->startCol = $startCol;
    }

    /**
     * @return int
     */
    public function getEndCol()
    {
        return $this->endCol;
    }

    /**
     * @param int $endCol
     */
    public function setEndCol($endCol)
    {
        $this->endCol = $endCol;
    }

    /**
     * @return ElementConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ElementConfig $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return Component
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @param Component $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * @return ElementData
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return ElementViewport[]
     */
    public function getViewports()
    {
        return $this->viewports;
    }

    /**
     * @param ElementViewport[] $viewports
     */
    public function setViewports(array $viewports)
    {
        $this->viewports = $viewports;
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        return $this->cssClass;
    }

    /**
     * @param string $cssClass
     */
    public function setCssClass($cssClass)
    {
        $this->cssClass = $cssClass;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
