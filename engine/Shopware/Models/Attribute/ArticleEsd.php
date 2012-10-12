<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_articles_esd_attributes")
 */
class ArticleEsd extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $articleEsdId
 *
 * @ORM\Column(name="esdID", type="integer", nullable=true)
 */
 protected $articleEsdId;


/**
 * @var \Shopware\Models\Article\Esd
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Esd", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="esdID", referencedColumnName="id")
 * })
 */
private $articleEsd;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getArticleEsdId()
{
    return $this->articleEsdId;
}
        

public function setArticleEsdId($articleEsdId)
{
    $this->articleEsdId = $articleEsdId;
    return $this;
}
        

public function getArticleEsd()
{
    return $this->articleEsd;
}

public function setArticleEsd($articleEsd)
{
    $this->articleEsd = $articleEsd;
    return $this;
}
        
}