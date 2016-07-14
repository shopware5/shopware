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
 * Shopware order document type model represents a single order document.
 * <br>
 * The Shopware order document type model represents a row of the core_documents table.
 * The core_documents table has the follows indices:
 * <code>
 * </code>
 *
 * @ORM\Entity
 * @ORM\Table(name="s_core_documents")
 */
class Type extends ModelEntity
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string $template
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     */
    private $template;

    /**
     * @var string $numbers
     *
     * @ORM\Column(name="numbers", type="string", length=25, nullable=false)
     */
    private $numbers;

    /**
     * @var integer $left
     *
     * @ORM\Column(name="left", type="integer", nullable=false)
     */
    private $left;

    /**
     * @var integer $right
     *
     * @ORM\Column(name="right", type="integer", nullable=false)
     */
    private $right;

    /**
     * @var integer $top
     *
     * @ORM\Column(name="top", type="integer", nullable=false)
     */
    private $top;

    /**
     * @var integer $bottom
     *
     * @ORM\Column(name="bottom", type="integer", nullable=false)
     */
    private $bottom;

    /**
     * @var integer $pageBreak
     *
     * @ORM\Column(name="pagebreak", type="integer", nullable=false)
     */
    private $pageBreak;

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
     * Set name
     *
     * @param string $name
     * @return Type
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set template
     *
     * @param string $template
     * @return Type
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set numbers
     *
     * @param string $numbers
     * @return Type
     */
    public function setNumbers($numbers)
    {
        $this->numbers = $numbers;
        return $this;
    }

    /**
     * Get numbers
     *
     * @return string
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * Set left
     *
     * @param integer $left
     * @return Type
     */
    public function setLeft($left)
    {
        $this->left = $left;
        return $this;
    }

    /**
     * Get left
     *
     * @return integer
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set right
     *
     * @param integer $right
     * @return Type
     */
    public function setRight($right)
    {
        $this->right = $right;
        return $this;
    }

    /**
     * Get right
     *
     * @return integer
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set top
     *
     * @param integer $top
     * @return Type
     */
    public function setTop($top)
    {
        $this->top = $top;
        return $this;
    }

    /**
     * Get top
     *
     * @return integer
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * Set bottom
     *
     * @param integer $bottom
     * @return Type
     */
    public function setBottom($bottom)
    {
        $this->bottom = $bottom;
        return $this;
    }

    /**
     * Get bottom
     *
     * @return integer
     */
    public function getBottom()
    {
        return $this->bottom;
    }

    /**
     * Set pageBreak
     *
     * @param integer $pageBreak
     * @return Type
     */
    public function setPageBreak($pageBreak)
    {
        $this->pageBreak = $pageBreak;
        return $this;
    }

    /**
     * Get pageBreak
     *
     * @return integer
     */
    public function getPageBreak()
    {
        return $this->pageBreak;
    }
}
