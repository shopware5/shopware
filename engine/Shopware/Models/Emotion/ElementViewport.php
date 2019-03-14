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

namespace Shopware\Models\Emotion;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_emotion_element_viewports")
 */
class ElementViewport extends ModelEntity
{
    /**
     * @var \Shopware\Models\Emotion\Element
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Emotion\Element", inversedBy="viewports")
     * @ORM\JoinColumn(name="elementID", referencedColumnName="id")
     */
    protected $element;

    /**
     * @var \Shopware\Models\Emotion\Emotion
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Emotion\Emotion")
     * @ORM\JoinColumn(name="emotionID", referencedColumnName="id")
     */
    protected $emotion;

    /**
     * Unique identifier field of the viewport model.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Id of the associated \Shopware\Models\Emotion\Element
     *
     * @var int
     *
     * @ORM\Column(name="elementID", type="integer", nullable=false)
     */
    private $elementId;

    /**
     * Id of the associated \Shopware\Models\Emotion\Emotion model.
     *
     * @var int
     *
     * @ORM\Column(name="emotionID", type="integer", nullable=false)
     */
    private $emotionId;

    /**
     * The short name of the viewport.
     *
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=255, nullable=false)
     */
    private $alias;

    /**
     * Defines on which row the element starts for this viewport.
     *
     * @var int
     *
     * @ORM\Column(name="start_row", type="integer", nullable=false)
     */
    private $startRow;

    /**
     * Defines on which column the element starts for this viewport.
     *
     * @var int
     *
     * @ORM\Column(name="start_col", type="integer", nullable=false)
     */
    private $startCol;

    /**
     * Defines on which row the element ends for this viewport.
     *
     * @var int
     *
     * @ORM\Column(name="end_row", type="integer", nullable=false)
     */
    private $endRow;

    /**
     * Defines on which column the element ends for this viewport.
     *
     * @var int
     *
     * @ORM\Column(name="end_col", type="integer", nullable=false)
     */
    private $endCol;

    /**
     * Defines if the element is visible for this viewport.
     *
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;

    public function __clone()
    {
        $this->id = null;
        $this->emotionId = null;
        $this->elementId = null;
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
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
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
     * @return Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param Element $element
     */
    public function setElement($element)
    {
        $this->element = $element;
    }

    /**
     * @return Emotion
     */
    public function getEmotion()
    {
        return $this->emotion;
    }

    /**
     * @param Emotion $emotion
     */
    public function setEmotion($emotion)
    {
        $this->emotion = $emotion;
    }

    /**
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }
}
