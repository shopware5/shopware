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
 * @ORM\Table(name="s_emotion_element")
 */
class Element extends ModelEntity
{
    /**
     * OWNING SIDE
     * Contains the assigned \Shopware\Models\Emotion\Emotion
     * which can be configured in the backend emotion module.
     * The assigned emotion contains the definition of the emotion elements.
     * The element model is the owning side (primary key in this table) of the association between
     * emotion and grid elements.
     *
     * @var \Shopware\Models\Emotion\Emotion
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Emotion\Emotion", inversedBy="elements", cascade={"persist"})
     * @ORM\JoinColumn(name="emotionID", referencedColumnName="id")
     */
    protected $emotion;

    /**
     * Contains the assigned \Shopware\Models\Emotion\Library\Component
     * which can be configured in the backend emotion module.
     * The assigned library component contains the data definition for the grid element.
     *
     * @var \Shopware\Models\Emotion\Library\Component
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Emotion\Library\Component")
     * @ORM\JoinColumn(name="componentID", referencedColumnName="id")
     */
    protected $component;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\Data>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Data", mappedBy="element", orphanRemoval=true, cascade={"persist"})
     */
    protected $data;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\ElementViewport>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\ElementViewport", mappedBy="element", orphanRemoval=true, cascade={"persist"})
     */
    protected $viewports;

    /**
     * Unique identifier field of the element model.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Id of the associated \Shopware\Models\Emotion\Emotion model.
     * The emotion contains all defined grid elements which defined
     * over the emotion backend module.
     *
     * @var int
     *
     * @ORM\Column(name="emotionID", type="integer", nullable=false)
     */
    private $emotionId;

    /**
     * Id of the associated \Shopware\Models\Emotion\Library\Component model.
     * The library component contains the data configuration for the grid element (article, banner, ...).
     *
     * @var int
     *
     * @ORM\Column(name="componentID", type="integer", nullable=false)
     */
    private $componentId;

    /**
     * Defines on which row the element starts.
     *
     * @var int
     *
     * @ORM\Column(name="start_row", type="integer", nullable=false)
     */
    private $startRow;

    /**
     * Defines on which col the element starts.
     *
     * @var int
     *
     * @ORM\Column(name="start_col", type="integer", nullable=false)
     */
    private $startCol;

    /**
     * Defines on which row the element ends.
     *
     * @var int
     *
     * @ORM\Column(name="end_row", type="integer", nullable=false)
     */
    private $endRow;

    /**
     * Defines on which col the element ends.
     *
     * @var int
     *
     * @ORM\Column(name="end_col", type="integer", nullable=false)
     */
    private $endCol;

    /**
     * Defines a custom user CSS class for every element.
     *
     * @var string
     *
     * @ORM\Column(name="css_class", type="string", length=255, nullable=true)
     */
    private $cssClass;

    public function __construct()
    {
        $this->data = new \Doctrine\Common\Collections\ArrayCollection();
        $this->viewports = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;

        $this->emotionId = null;

        $dataArray = [];
        foreach ($this->data as $data) {
            $newData = clone $data;

            $newData->setElement($this);

            $dataArray[] = $newData;
        }

        $this->data = new \Doctrine\Common\Collections\ArrayCollection($dataArray);

        $viewportData = [];
        foreach ($this->viewports as $viewport) {
            $newViewport = clone $viewport;

            $newViewport->setElement($this);

            $viewportData[] = $newViewport;
        }

        $this->viewports = new \Doctrine\Common\Collections\ArrayCollection($viewportData);
    }

    /**
     * Unique identifier field of the element model.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Defines on which column the element starts.
     *
     * @return int
     */
    public function getStartRow()
    {
        return $this->startRow;
    }

    /**
     * Defines on which column the element starts.
     *
     * @param int $startRow
     */
    public function setStartRow($startRow)
    {
        $this->startRow = $startRow;
    }

    /**
     * Defines on which row the element starts.
     *
     * @return int
     */
    public function getStartCol()
    {
        return $this->startCol;
    }

    /**
     * Defines on which row the element starts.
     *
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
     * @param string $cssClass
     */
    public function setCssClass($cssClass)
    {
        $this->cssClass = $cssClass;
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        return $this->cssClass;
    }

    /**
     * @param int $endCol
     */
    public function setEndCol($endCol)
    {
        $this->endCol = $endCol;
    }

    /**
     * Contains the assigned \Shopware\Models\Emotion\Emotion
     * which can be configured in the backend emotion module.
     * The assigned grid contains the definition of the emotion elements.
     * The emotion model is the owning side (primary key in this table) of the association between
     * emotion and grid.
     *
     * @return \Shopware\Models\Emotion\Emotion
     */
    public function getEmotion()
    {
        return $this->emotion;
    }

    /**
     * Contains the assigned \Shopware\Models\Emotion\Emotion
     * which can be configured in the backend emotion module.
     * The assigned emotion contains the definition of the emotion elements.
     * The emotion model is the owning side (primary key in this table) of the association between
     * emotion and grid elements.
     *
     * @param \Shopware\Models\Emotion\Emotion $emotion
     */
    public function setEmotion($emotion)
    {
        $this->emotion = $emotion;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param \Shopware\Models\Emotion\Data[]|null $data
     *
     * @return Element
     */
    public function setData($data)
    {
        return $this->setOneToMany($data, \Shopware\Models\Emotion\Data::class, 'data', 'element');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\ElementViewport>
     */
    public function getViewports()
    {
        return $this->viewports;
    }

    /**
     * @param \Shopware\Models\Emotion\ElementViewport[]|null $viewports
     *
     * @return Element
     */
    public function setViewports($viewports)
    {
        return $this->setOneToMany($viewports, \Shopware\Models\Emotion\ElementViewport::class, 'viewports', 'element');
    }

    /**
     * Contains the assigned \Shopware\Models\Emotion\Library\Component
     * which can be configured in the backend emotion module.
     * The assigned library component contains the data definition for the grid element.
     *
     * @return \Shopware\Models\Emotion\Library\Component
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Contains the assigned \Shopware\Models\Emotion\Library\Component
     * which can be configured in the backend emotion module.
     * The assigned library component contains the data definition for the grid element.
     *
     * @param \Shopware\Models\Emotion\Library\Component $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }
}
