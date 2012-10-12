<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_articles_information_attributes")
 */
class ArticleLink extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $articleLinkId
 *
 * @ORM\Column(name="informationID", type="integer", nullable=true)
 */
 protected $articleLinkId;


/**
 * @var \Shopware\Models\Article\Link
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Link", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="informationID", referencedColumnName="id")
 * })
 */
private $articleLink;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getArticleLinkId()
{
    return $this->articleLinkId;
}
        

public function setArticleLinkId($articleLinkId)
{
    $this->articleLinkId = $articleLinkId;
    return $this;
}
        

public function getArticleLink()
{
    return $this->articleLink;
}

public function setArticleLink($articleLink)
{
    $this->articleLink = $articleLink;
    return $this;
}
        
}