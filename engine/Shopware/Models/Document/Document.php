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

namespace Shopware\Models\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware document model represents a document.
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_core_documents")
 * @ORM\HasLifecycleCallbacks()
 */
class Document extends ModelEntity
{
    /**
     * The id property is an identifier property which means
     * doctrine associations can be defined over this field
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the name of the document.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name = '';

    /**
     * An internal key, which can be used to identify a document type independently
     * from its id or name, because these values may have been changed by the user.
     *
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", nullable=true, unique=true)
     */
    private $key;

    /**
     * Contains the template-file of the document.
     *
     * @var string
     *
     * @ORM\Column(name="template", type="string", nullable=false)
     */
    private $template = '';

    /**
     * Contains the numbers of the document.
     *
     * @var string
     *
     * @ORM\Column(name="numbers", type="string", nullable=false)
     */
    private $numbers = '';

    /**
     * Contains the left-value of the document.
     *
     * @var int
     *
     * @ORM\Column(name="`left`", type="integer", nullable=false)
     */
    private $left = 0;

    /**
     * Contains the right-value of the document.
     *
     * @var int
     *
     * @ORM\Column(name="`right`", type="integer", nullable=false)
     */
    private $right = 0;

    /**
     * Contains the top-value of the document.
     *
     * @var int
     *
     * @ORM\Column(name="top", type="integer", nullable=false)
     */
    private $top = 0;

    /**
     * Contains the bottom-value of the document.
     *
     * @var int
     *
     * @ORM\Column(name="bottom", type="integer", nullable=false)
     */
    private $bottom = 0;

    /**
     * Contains the pageBreak-value of the document.
     *
     * @var int
     *
     * @ORM\Column(name="pagebreak", type="integer", nullable=false)
     */
    private $pageBreak = 0;

    /**
     * INVERSED SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Document\Element>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Document\Element", mappedBy="document", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="documentID")
     */
    private $elements;

    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

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
     * @return \Shopware\Models\Document\Document
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
     * Sets the document's unique key
     *
     * @param string $key
     *
     * @return \Shopware\Models\Document\Document
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Gets the document's unique key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Sets the documents template-file.
     *
     * @param string $template
     *
     * @return \Shopware\Models\Document\Document
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Gets the name of the template-file.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the documents numbers.
     *
     * @param string $numbers
     *
     * @return \Shopware\Models\Document\Document
     */
    public function setNumbers($numbers)
    {
        $this->numbers = $numbers;

        return $this;
    }

    /**
     * Gets the numbers of the document.
     *
     * @return string
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * Sets the bottom-value for the document.
     *
     * @param int $bottom
     *
     * @return \Shopware\Models\Document\Document
     */
    public function setBottom($bottom)
    {
        $this->bottom = $bottom;

        return $this;
    }

    /**
     * Gets the bottom-value of the document.
     *
     * @return int
     */
    public function getBottom()
    {
        return $this->bottom;
    }

    /**
     * Sets the left-value for the document.
     *
     * @param int $left
     *
     * @return \Shopware\Models\Document\Document
     */
    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    /**
     * Gets the left-value of the document.
     *
     * @return int
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Sets the pageBreak-value for the document.
     *
     * @param int $pageBreak
     *
     * @return \Shopware\Models\Document\Document
     */
    public function setPageBreak($pageBreak)
    {
        $this->pageBreak = $pageBreak;

        return $this;
    }

    /**
     * Gets the pageBreak-value of the document.
     *
     * @return int
     */
    public function getPageBreak()
    {
        return $this->pageBreak;
    }

    /**
     * Sets the right-value for the document.
     *
     * @param int $right
     *
     * @return \Shopware\Models\Document\Document
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * Gets the right-value of the document.
     *
     * @return int
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Sets the top-value for the document.
     *
     * @param int $top
     *
     * @return \Shopware\Models\Document\Document
     */
    public function setTop($top)
    {
        $this->top = $top;

        return $this;
    }

    /**
     * Gets the top-value of the document.
     *
     * @return int
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * Sets the form-elements.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Document\Element>|Element[] $elements
     *
     * @return Document
     */
    public function setElements($elements)
    {
        $this->setOneToMany($elements, \Shopware\Models\Document\Element::class, 'elements', 'document');

        return $this;
    }

    /**
     * Gets the form-elements.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Document\Element>
     */
    public function getElements()
    {
        return $this->elements;
    }
}
