<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_order_documents_attributes")
 */
class Document extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $documentId
 *
 * @ORM\Column(name="documentID", type="integer", nullable=true)
 */
 protected $documentId;


/**
 * @var \Shopware\Models\Order\Document\Document
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Document\Document", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="documentID", referencedColumnName="ID")
 * })
 */
private $document;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getDocumentId()
{
    return $this->documentId;
}
        

public function setDocumentId($documentId)
{
    $this->documentId = $documentId;
    return $this;
}
        

public function getDocument()
{
    return $this->document;
}

public function setDocument($document)
{
    $this->document = $document;
    return $this;
}
        
}