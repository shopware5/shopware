<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_articles_supplier_attributes")
 */
class ArticleSupplier extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $articleSupplierId
 *
 * @ORM\Column(name="supplierID", type="integer", nullable=true)
 */
 protected $articleSupplierId;


/**
 * @var \Shopware\Models\Article\Supplier
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Supplier", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="supplierID", referencedColumnName="id")
 * })
 */
private $articleSupplier;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getArticleSupplierId()
{
    return $this->articleSupplierId;
}
        

public function setArticleSupplierId($articleSupplierId)
{
    $this->articleSupplierId = $articleSupplierId;
    return $this;
}
        

public function getArticleSupplier()
{
    return $this->articleSupplier;
}

public function setArticleSupplier($articleSupplier)
{
    $this->articleSupplier = $articleSupplier;
    return $this;
}
        
}