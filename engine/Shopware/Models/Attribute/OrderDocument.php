<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\Models\Attribute;

use Doctrine\ORM\Mapping as ORM,
    Shopware\Components\Model\ModelEntity;

/**
 * Shopware\Models\Attribute\OrderDocument
 *
 * @ORM\Table(name="s_order_documents_attributes")
 * @ORM\Entity
 */
class OrderDocument extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $documentId
     *
     * @ORM\Column(name="documentID", type="integer", nullable=true)
     */
    private $documentId = null;

    /**
     * @var Shopware\Models\Order\Document\Document
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Document\Document", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="documentID", referencedColumnName="ID")
     * })
     */
    private $document;

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
     * Set document
     *
     * @param Shopware\Models\Order\Document\Document $document
     * @return OrderDocument
     */
    public function setDocument(\Shopware\Models\Order\Document\Document $document = null)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * Get document
     *
     * @return Shopware\Models\Order\Document\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set documentId
     *
     * @param integer $documentId
     * @return OrderDocument
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
}
