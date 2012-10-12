<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_articles_prices_attributes")
 */
class ArticlePrice extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $articlePriceId
 *
 * @ORM\Column(name="priceID", type="integer", nullable=true)
 */
 protected $articlePriceId;


/**
 * @var \Shopware\Models\Article\Price
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Price", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="priceID", referencedColumnName="id")
 * })
 */
private $articlePrice;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getArticlePriceId()
{
    return $this->articlePriceId;
}
        

public function setArticlePriceId($articlePriceId)
{
    $this->articlePriceId = $articlePriceId;
    return $this;
}
        

public function getArticlePrice()
{
    return $this->articlePrice;
}

public function setArticlePrice($articlePrice)
{
    $this->articlePrice = $articlePrice;
    return $this;
}
        
}