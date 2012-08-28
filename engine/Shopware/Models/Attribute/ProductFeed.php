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
 * Shopware\Models\Attribute\ProductFeed
 *
 * @ORM\Table(name="s_export_attributes")
 * @ORM\Entity
 */
class ProductFeed extends ModelEntity
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
     * @var integer $productFeedId
     *
     * @ORM\Column(name="exportID", type="integer", nullable=true)
     */
    private $productFeedId = null;

    /**
     * @var Shopware\Models\ProductFeed\ProductFeed
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\ProductFeed\ProductFeed", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="exportID", referencedColumnName="id")
     * })
     */
    private $productFeed;

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
     * Set productFeed
     *
     * @param Shopware\Models\ProductFeed\ProductFeed $productFeed
     * @return ProductFeed
     */
    public function setProductFeed(\Shopware\Models\ProductFeed\ProductFeed $productFeed = null)
    {
        $this->productFeed = $productFeed;
        return $this;
    }

    /**
     * Get productFeed
     *
     * @return Shopware\Models\ProductFeed\ProductFeed
     */
    public function getProductFeed()
    {
        return $this->productFeed;
    }

    /**
     * Set productFeedId
     *
     * @param integer $productFeedId
     * @return ProductFeed
     */
    public function setProductFeedId($productFeedId)
    {
        $this->productFeedId = $productFeedId;
        return $this;
    }

    /**
     * Get productFeedId
     *
     * @return integer
     */
    public function getProductFeedId()
    {
        return $this->productFeedId;
    }
}
