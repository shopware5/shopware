<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Order\Document;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\Document as DocumentAttribute;
use Shopware\Models\Order\Order;

/**
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
     * INVERSE SIDE
     *
     * @var DocumentAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Document", mappedBy="document", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $typeId;

    /**
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var int
     *
     * @ORM\Column(name="orderID", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable=false)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="docID", type="string", nullable=false)
     */
    private $documentId;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=false)
     */
    private $hash;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order", inversedBy="documents")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="id", nullable=false)
     */
    private $order;

    /**
     * @var \Shopware\Models\Document\Document
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Document\Document")
     * @ORM\JoinColumn(name="type", referencedColumnName="id", nullable=false)
     */
    private $type;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param DateTimeInterface $date
     *
     * @return Document
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $customerId
     *
     * @return Document
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $orderId
     *
     * @return Document
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param float $amount
     *
     * @return Document
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $documentId
     *
     * @return Document
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param string $hash
     *
     * @return Document
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
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
     * @return \Shopware\Models\Document\Document
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Shopware\Models\Document\Document $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return DocumentAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param DocumentAttribute|null $attribute
     *
     * @return Document
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, DocumentAttribute::class, 'attribute', 'document');
    }
}
