<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_articles_downloads_attributes")
 */
class ArticleDownload extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $articleDownloadId
 *
 * @ORM\Column(name="downloadID", type="integer", nullable=true)
 */
 protected $articleDownloadId;


/**
 * @var \Shopware\Models\Article\Download
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Download", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="downloadID", referencedColumnName="id")
 * })
 */
private $articleDownload;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getArticleDownloadId()
{
    return $this->articleDownloadId;
}
        

public function setArticleDownloadId($articleDownloadId)
{
    $this->articleDownloadId = $articleDownloadId;
    return $this;
}
        

public function getArticleDownload()
{
    return $this->articleDownload;
}

public function setArticleDownload($articleDownload)
{
    $this->articleDownload = $articleDownload;
    return $this;
}
        
}