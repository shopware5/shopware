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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\PropertyValue as PropertyValueAttribute;
use Shopware\Models\Media\Media;

/**
 * Shopware Article Property Model
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_filter_values")
 */
class Value extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var PropertyValueAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\PropertyValue", mappedBy="propertyValue", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * Position of this value
     *
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * Id of assigned Option
     *
     * @var int
     *
     * @ORM\Column(name="optionID", type="integer")
     */
    private $optionId;

    /**
     * @var Option
     *
     * @ORM\ManyToOne(targetEntity="Option", inversedBy="values", cascade={"persist"})
     * @ORM\JoinColumn(name="optionID", referencedColumnName="id")
     */
    private $option;

    /**
     * @var ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article", mappedBy="propertyValues")
     * @ORM\JoinTable(name="s_filter_articles",
     *     joinColumns={@ORM\JoinColumn(name="valueID", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="articleID", referencedColumnName="id")}
     * )
     */
    private $articles;

    /**
     * @var int
     *
     * @ORM\Column(name="media_id", type="integer", nullable=true)
     */
    private $mediaId;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Media\Media", inversedBy="properties")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id")
     */
    private $media;

    /**
     * @param string $value
     */
    public function __construct(Option $option, $value)
    {
        $this->option = $option;
        $this->setValue($value);
        $this->articles = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $value
     *
     * @return Value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $position
     *
     * @return Value
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
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
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param Media $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return PropertyValueAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param PropertyValueAttribute|array|null $attribute
     *
     * @return Value
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, PropertyValueAttribute::class, 'attribute', 'propertyValue');
    }
}
