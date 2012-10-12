<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_user_attributes")
 */
class Customer extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $customerId
 *
 * @ORM\Column(name="userID", type="integer", nullable=true)
 */
 protected $customerId;


/**
 * @var integer $swagShoeSize
 *
 * @ORM\Column(name="swag_shoe_size", type="integer", nullable=true)
 */
 protected $swagShoeSize;


/**
 * @var integer $articleId
 *
 * @ORM\Column(name="article_id", type="integer", nullable=false)
 */
 protected $articleId;


/**
 * @var string $varcharColumn
 *
 * @ORM\Column(name="varchar_column", type="string", nullable=true)
 */
 protected $varcharColumn;


/**
 * @var string $textColumn
 *
 * @ORM\Column(name="text_column", type="text", nullable=false)
 */
 protected $textColumn;


/**
 * @var \Shopware\Models\Customer\Customer
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Customer", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="userID", referencedColumnName="id")
 * })
 */
private $customer;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getCustomerId()
{
    return $this->customerId;
}
        

public function setCustomerId($customerId)
{
    $this->customerId = $customerId;
    return $this;
}
        

public function getSwagShoeSize()
{
    return $this->swagShoeSize;
}
        

public function setSwagShoeSize($swagShoeSize)
{
    $this->swagShoeSize = $swagShoeSize;
    return $this;
}
        

public function getArticleId()
{
    return $this->articleId;
}
        

public function setArticleId($articleId)
{
    $this->articleId = $articleId;
    return $this;
}
        

public function getVarcharColumn()
{
    return $this->varcharColumn;
}
        

public function setVarcharColumn($varcharColumn)
{
    $this->varcharColumn = $varcharColumn;
    return $this;
}
        

public function getTextColumn()
{
    return $this->textColumn;
}
        

public function setTextColumn($textColumn)
{
    $this->textColumn = $textColumn;
    return $this;
}
        

public function getCustomer()
{
    return $this->customer;
}

public function setCustomer($customer)
{
    $this->customer = $customer;
    return $this;
}
        
}