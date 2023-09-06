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

namespace Shopware\Models\ProductStream;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\ProductStream as ProductStreamAttribute;

/**
 * @ORM\Table(name="s_product_streams")
 * @ORM\Entity()
 */
class ProductStream extends ModelEntity
{
    public const TYPE_CONDITION = 1;
    public const TYPE_SELECTION = 2;

    /**
     * INVERSE SIDE
     *
     * @var ProductStreamAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ProductStream", mappedBy="productStream", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var self::TYPE_*
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="sorting", type="string", nullable=false)
     */
    private $sorting;

    /**
     * @var string|null
     *
     * @ORM\Column(name="conditions", type="string", nullable=true)
     */
    private $conditions;

    /**
     * @var int|null
     *
     * @ORM\Column(name="sorting_id", type="integer", nullable=true)
     */
    private $sortingId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param string $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return self::TYPE_*
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param self::TYPE_* $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param string $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * @return ProductStreamAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ProductStreamAttribute|array|null $attribute
     *
     * @return ProductStream
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductStreamAttribute::class, 'attribute', 'productStream');
    }

    /**
     * @return int|null
     */
    public function getSortingId()
    {
        return $this->sortingId;
    }

    /**
     * @param int $sortingId
     */
    public function setSortingId($sortingId)
    {
        $this->sortingId = $sortingId;
    }
}
