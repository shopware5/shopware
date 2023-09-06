<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Emotion;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Emotion\Library\Component;
use Shopware\Models\Emotion\Library\Field;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_emotion_element_value")
 */
class Data extends ModelEntity
{
    /**
     * @var Element
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Emotion\Element", inversedBy="data")
     * @ORM\JoinColumn(name="elementID", referencedColumnName="id", nullable=false)
     */
    protected $element;

    /**
     * @var Emotion
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Emotion\Emotion")
     * @ORM\JoinColumn(name="emotionID", referencedColumnName="id", nullable=false)
     */
    protected $emotion;

    /**
     * @var Component
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Emotion\Library\Component")
     * @ORM\JoinColumn(name="componentID", referencedColumnName="id", nullable=false)
     */
    protected $component;

    /**
     * @var Field
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Emotion\Library\Field")
     * @ORM\JoinColumn(name="fieldID", referencedColumnName="id", nullable=false)
     */
    protected $field;

    /**
     * Unique identifier field for the shopware emotion.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the id of the emotion
     *
     * @var int
     *
     * @ORM\Column(name="emotionID", type="integer", nullable=false)
     */
    private $emotionId;

    /**
     * Contains the name of the emotion.
     *
     * @var int
     *
     * @ORM\Column(name="elementID", type="integer", nullable=false)
     */
    private $elementId;

    /**
     * Contains the id of the assigned element component
     *
     * @var int
     *
     * @ORM\Column(name="componentID", type="integer", nullable=false)
     */
    private $componentId;

    /**
     * @var int
     *
     * @ORM\Column(name="fieldID", type="integer", nullable=false)
     */
    private $fieldId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;

    public function __clone()
    {
        $this->id = null;
        $this->emotionId = null;
        $this->elementId = null;
        $this->fieldId = null;
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
    public function getComponentId()
    {
        return $this->componentId;
    }

    /**
     * @param int $elementId
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
    }

    /**
     * @return int
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @param int $fieldId
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;
    }

    /**
     * @return int
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param Field $field
     */
    public function setField($field)
    {
        $this->field = $field;
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
}
