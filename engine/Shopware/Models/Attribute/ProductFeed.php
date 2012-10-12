<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_export_attributes")
 */
class ProductFeed extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $productFeedId
 *
 * @ORM\Column(name="exportID", type="integer", nullable=true)
 */
 protected $productFeedId;


/**
 * @var \Shopware\Models\ProductFeed\ProductFeed
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\ProductFeed\ProductFeed", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="exportID", referencedColumnName="id")
 * })
 */
private $productFeed;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getProductFeedId()
{
    return $this->productFeedId;
}
        

public function setProductFeedId($productFeedId)
{
    $this->productFeedId = $productFeedId;
    return $this;
}
        

public function getProductFeed()
{
    return $this->productFeed;
}

public function setProductFeed($productFeed)
{
    $this->productFeed = $productFeed;
    return $this;
}
        
}