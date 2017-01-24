<?php

namespace Shopware\Models\ProductStream;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="s_product_streams")
 * @ORM\Entity
 */
class ProductStream extends ModelEntity
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
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string $name
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @deprecated since version 5.3, to be removed in 6.0 - Use \Shopware\Models\ProductStream\ProductStream::$sortingId instead
     * @ORM\Column(name="sorting", type="string", nullable=false)
     */
    private $sorting;

    /**
     * @var array
     * @ORM\Column(name="conditions", type="string", nullable=true)
     */
    private $conditions = true;

    /**
     * @var int
     * @ORM\Column(name="sorting_id", type="integer", nullable=true)
     */
    private $sortingId;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ProductStream", mappedBy="productStream", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\ProductStream
     */
    protected $attribute;

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
     * @param $name string
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
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @deprecated since version 5.3, to be removed in 6.0 - Use \Shopware\Models\ProductStream\ProductStream::$sortingId instead
     * @return mixed
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @deprecated since version 5.3, to be removed in 6.0 - Use \Shopware\Models\ProductStream\ProductStream::$sortingId instead
     * @param mixed $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * @return \Shopware\Models\Attribute\ProductStream
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\ProductStream|array|null $attribute
     * @return \Shopware\Models\Attribute\ProductStream
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\ProductStream', 'attribute', 'productStream');
    }

    /**
     * @return int
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
