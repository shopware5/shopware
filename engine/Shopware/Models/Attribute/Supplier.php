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
 * Shopware\Models\Attribute\Supplier
 *
 * @ORM\Table(name="s_articles_supplier_attributes")
 * @ORM\Entity
 */
class Supplier extends ModelEntity
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
     * @var integer $articleSupplierId
     *
     * @ORM\Column(name="supplierID", type="integer", nullable=true)
     */
    private $articleSupplierId = null;

    /**
     * @var Shopware\Models\Article\Supplier
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Supplier", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="supplierID", referencedColumnName="id")
     * })
     */
    private $articleSupplier;

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
     * Set articleSupplier
     *
     * @param Shopware\Models\Article\Supplier $articleSupplier
     * @return Supplier
     */
    public function setArticleSupplier(\Shopware\Models\Article\Supplier $articleSupplier = null)
    {
        $this->articleSupplier = $articleSupplier;
        return $this;
    }

    /**
     * Get articleSupplier
     *
     * @return Shopware\Models\Article\Supplier
     */
    public function getArticleSupplier()
    {
        return $this->articleSupplier;
    }

    /**
     * Set articleSupplierId
     *
     * @param integer $articleSupplierId
     * @return Supplier
     */
    public function setArticleSupplierId($articleSupplierId)
    {
        $this->articleSupplierId = $articleSupplierId;
        return $this;
    }

    /**
     * Get articleSupplierId
     *
     * @return integer
     */
    public function getArticleSupplierId()
    {
        return $this->articleSupplierId;
    }
}
