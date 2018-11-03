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

namespace   Shopware\Models\Document;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware document model represents a document.
 *
 * @ORM\Entity
 * @ORM\Table(name="s_core_documents_box")
 * @ORM\HasLifecycleCallbacks
 */
class Element extends ModelEntity
{
    /**
     * The id property is an identifier property which means
     * doctrine associations can be defined over this field
     *
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the name of the document.
     *
     * @var int
     * @ORM\Column(name="documentID", type="integer", nullable=false)
     */
    private $documentId = '';

    /**
     * Contains the name of the document.
     *
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name = '';

    /**
     * Contains the name of the document.
     *
     * @var string
     * @ORM\Column(name="style", type="string", nullable=false)
     */
    private $style = '';

    /**
     * Contains the name of the document.
     *
     * @var string
     * @ORM\Column(name="value", type="string", nullable=false)
     */
    private $value = '';

    /**
     * Owning Side
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Document\Document", inversedBy="elements")
     * @ORM\JoinColumn(name="documentID", referencedColumnName="id")
     *
     * @var \Shopware\Models\Document\Document
     */
    private $document;

    /**
     * Getter function for the unique id identifier property
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the name of the document.
     *
     * @param string $name
     *
     * @return \Shopware\Models\Document\Element
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the documents name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value for an element.
     *
     * @param string $value
     *
     * @return \Shopware\Models\Document\Element
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Gets the value for an element.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the style for
     *
     * @param string $style
     *
     * @return \Shopware\Models\Document\Element
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param \Shopware\Models\Document\Document $document
     *
     * @return \Shopware\Models\Document\Element
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return \Shopware\Models\Document\Document
     */
    public function getDocument()
    {
        return $this->document;
    }
}
