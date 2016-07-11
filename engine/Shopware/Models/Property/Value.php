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

namespace Shopware\Models\Property;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Models\Media\Media;

/**
 * Shopware Article Property Model
 *
 * @ORM\Entity
 * @ORM\Table(name="s_filter_values")
 */
class Value extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * Position of this value
     *
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * Id of assigned Option
     *
     * @var integer $optionId
     *
     * @ORM\Column(name="optionID", type="integer")
     */
    private $optionId;

    /**
     * @var string $name
     *
     * @ORM\ManyToOne(targetEntity="Option", inversedBy="values", cascade={"persist"})
     * @ORM\JoinColumn(name="optionID", referencedColumnName="id")
     */
    private $option;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article", mappedBy="propertyValues")
     * @ORM\JoinTable(name="s_filter_articles",
     *      joinColumns={@ORM\JoinColumn(name="valueID", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="articleID", referencedColumnName="id")}
     * )
     */
    private $articles;

    /**
     * @var int $mediaId
     * @ORM\Column(name="media_id", type="integer", nullable=true)
     */
    private $mediaId = null;

    /**
     * @var Media
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Media\Media", inversedBy="properties")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id")
     */
    private $media;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\PropertyValue", mappedBy="propertyValue", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\PropertyValue
     */
    protected $attribute;

    /**
     * Class constructor.
     *
     * @param \Shopware\Models\Property\Option $option
     * @param string $value
     */
    public function __construct(Option $option, $value)
    {
        $this->option = $option;
        $this->setValue($value);
        $this->articles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return \Shopware\Models\Property\Value
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param Option $option
     */
    public function setOption($option)
    {
        $this->option = $option;
    }

    /**
     * @return Option
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @return \Shopware\Models\Media\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param \Shopware\Models\Media\Media $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return \Shopware\Models\Attribute\PropertyValue
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\PropertyValue|array|null $attribute
     * @return \Shopware\Models\Attribute\PropertyValue
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\PropertyValue', 'attribute', 'propertyValue');
    }
}
