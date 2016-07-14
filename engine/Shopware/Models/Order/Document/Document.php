<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Models\Order\Document;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * Shopware order detail model represents a single detail data of an order .
 * <br>
 * The Shopware order detail model represents a row of the order_details table.
 * The s_order_details table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`ID`),
 *   - KEY `orderID` (`orderID`),
 *   - KEY `userID` (`userID`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_order_documents")
 */
class Document extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime $date
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var integer $typeId
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $typeId;

    /**
     * @var integer $customerId
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="orderID", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="float", nullable=false)
     */
    private $amount;

    /**
     * @var integer $documentId
     *
     * @ORM\Column(name="docID", type="integer", nullable=false)
     */
    private $documentId;

    /**
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=false)
     */
    private $hash;

    /**
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order", inversedBy="documents")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="id")
     * @var \Shopware\Models\Order\Order
     */
    private $order;

    /**
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Document\Type")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     * @var
     */
    private $type;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Document", mappedBy="document", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\Document
     */
    protected $attribute;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Document
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set customerId
     *
     * @param integer $customerId
     * @return Document
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * Get customerId
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set orderId
     *
     * @param integer $orderId
     * @return Document
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Get orderId
     *
     * @return integer
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return Document
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set documentId
     *
     * @param integer $documentId
     * @return Document
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;
        return $this;
    }

    /**
     * Get documentId
     *
     * @return integer
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return Document
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return \Shopware\Models\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Shopware\Models\Order\Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param int $typeId
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
    }

    /**
     * @return
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param  $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \Shopware\Models\Attribute\Document
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Document|array|null $attribute
     * @return \Shopware\Models\Attribute\Document
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Document', 'attribute', 'document');
    }
}
