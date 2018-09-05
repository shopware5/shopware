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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @deprecated The condition below will be removed in Shopware 5.6, together with the old version of the model.
 *
 * This is a hack to support both an uppercase "ID" as well as a lowercase "id" in the table `s_core_documents`.
 *
 * Historically it was named "ID" but to support MySQL 8, the column name had to be changed to "id" (See comment in
 * \Shopware\Components\Compatibility\LegacyDocumentIdConverter for a deeper explanation). This change of
 * the column name is only being done when MySQL 8 is currently being used, in order to otherwise be able
 * to downgrade the Shopware installation to a previous version.
 */
$documentIdConversion = Shopware()->Container()->get('legacy_documentid_converter');

if ($documentIdConversion->isMigrationNecessary()) {
    $documentIdConversion->migrateTable();
    $cacheManager = Shopware()->Container()->get('shopware.cache_manager');
    $cacheManager->clearProxyCache();
    $cacheManager->clearOpCache();
}

if ($documentIdConversion->isDocumentIdUpperCase()) {
    /**
     * OLD VERSION!
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
         * INVERSE SIDE
         *
         * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Document", mappedBy="document", orphanRemoval=true, cascade={"persist"})
         *
         * @var \Shopware\Models\Attribute\Document
         */
        protected $attribute;

        /**
         * @var int
         *
         * @ORM\Column(name="ID", type="integer", nullable=false)
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * @var \DateTime
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
         * @var int
         *
         * @ORM\Column(name="docID", type="integer", nullable=false)
         */
        private $documentId;

        /**
         * @var string
         *
         * @ORM\Column(name="hash", type="string", length=255, nullable=false)
         */
        private $hash;

        /**
         * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order", inversedBy="documents")
         * @ORM\JoinColumn(name="orderID", referencedColumnName="id")
         *
         * @var \Shopware\Models\Order\Order
         */
        private $order;

        /**
         * @ORM\OneToOne(targetEntity="\Shopware\Models\Document\Document")
         * @ORM\JoinColumn(name="type", referencedColumnName="id")
         *
         * @var \Shopware\Models\Document\Document
         */
        private $type;

        /**
         * Get id
         *
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set date
         *
         * @param \DateTime $date
         *
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
         * Get customerId
         *
         * @return int
         */
        public function getCustomerId()
        {
            return $this->customerId;
        }

        /**
         * Set orderId
         *
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
         * Get orderId
         *
         * @return int
         */
        public function getOrderId()
        {
            return $this->orderId;
        }

        /**
         * Set amount
         *
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
         * @param int $documentId
         *
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
         * @return int
         */
        public function getDocumentId()
        {
            return $this->documentId;
        }

        /**
         * Set hash
         *
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
         * @return \Shopware\Models\Attribute\Document
         */
        public function getAttribute()
        {
            return $this->attribute;
        }

        /**
         * @param \Shopware\Models\Attribute\Document|array|null $attribute
         *
         * @return \Shopware\Models\Attribute\Document
         */
        public function setAttribute($attribute)
        {
            return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Document', 'attribute', 'document');
        }
    }
} else {
    /**
     * NEW VERSION!
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
         * INVERSE SIDE
         *
         * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Document", mappedBy="document", orphanRemoval=true, cascade={"persist"})
         *
         * @var \Shopware\Models\Attribute\Document
         */
        protected $attribute;

        /**
         * @var int
         *
         * @ORM\Column(name="id", type="integer", nullable=false)
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * @var \DateTime
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
         * @var int
         *
         * @ORM\Column(name="docID", type="integer", nullable=false)
         */
        private $documentId;

        /**
         * @var string
         *
         * @ORM\Column(name="hash", type="string", length=255, nullable=false)
         */
        private $hash;

        /**
         * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order", inversedBy="documents")
         * @ORM\JoinColumn(name="orderID", referencedColumnName="id")
         *
         * @var \Shopware\Models\Order\Order
         */
        private $order;

        /**
         * @ORM\OneToOne(targetEntity="\Shopware\Models\Document\Document")
         * @ORM\JoinColumn(name="type", referencedColumnName="id")
         *
         * @var \Shopware\Models\Document\Document
         */
        private $type;

        /**
         * Get id
         *
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set date
         *
         * @param \DateTime $date
         *
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
         * Get customerId
         *
         * @return int
         */
        public function getCustomerId()
        {
            return $this->customerId;
        }

        /**
         * Set orderId
         *
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
         * Get orderId
         *
         * @return int
         */
        public function getOrderId()
        {
            return $this->orderId;
        }

        /**
         * Set amount
         *
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
         * @param int $documentId
         *
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
         * @return int
         */
        public function getDocumentId()
        {
            return $this->documentId;
        }

        /**
         * Set hash
         *
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
         * @return \Shopware\Models\Attribute\Document
         */
        public function getAttribute()
        {
            return $this->attribute;
        }

        /**
         * @param \Shopware\Models\Attribute\Document|array|null $attribute
         *
         * @return \Shopware\Models\Attribute\Document
         */
        public function setAttribute($attribute)
        {
            return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Document', 'attribute', 'document');
        }
    }
}
