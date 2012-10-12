<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_articles_img_attributes")
 */
class ArticleImage extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $articleImageId
 *
 * @ORM\Column(name="imageID", type="integer", nullable=true)
 */
 protected $articleImageId;


/**
 * @var string $attribute1
 *
 * @ORM\Column(name="attribute1", type="string", nullable=false)
 */
 protected $attribute1;


/**
 * @var string $attribute2
 *
 * @ORM\Column(name="attribute2", type="string", nullable=false)
 */
 protected $attribute2;


/**
 * @var string $attribute3
 *
 * @ORM\Column(name="attribute3", type="string", nullable=false)
 */
 protected $attribute3;


/**
 * @var \Shopware\Models\Article\Image
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Image", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="imageID", referencedColumnName="id")
 * })
 */
private $articleImage;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getArticleImageId()
{
    return $this->articleImageId;
}
        

public function setArticleImageId($articleImageId)
{
    $this->articleImageId = $articleImageId;
    return $this;
}
        

public function getAttribute1()
{
    return $this->attribute1;
}
        

public function setAttribute1($attribute1)
{
    $this->attribute1 = $attribute1;
    return $this;
}
        

public function getAttribute2()
{
    return $this->attribute2;
}
        

public function setAttribute2($attribute2)
{
    $this->attribute2 = $attribute2;
    return $this;
}
        

public function getAttribute3()
{
    return $this->attribute3;
}
        

public function setAttribute3($attribute3)
{
    $this->attribute3 = $attribute3;
    return $this;
}
        

public function getArticleImage()
{
    return $this->articleImage;
}

public function setArticleImage($articleImage)
{
    $this->articleImage = $articleImage;
    return $this;
}
        
}