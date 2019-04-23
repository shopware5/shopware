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

namespace Shopware\Models\Category;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Article as Product;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_categories_manual_sorting")
 */
class ManualSorting extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * OWNING SIDE
     *
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $category_id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $product_id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * OWNING SIDE
     *
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): ManualSorting
    {
        $this->category = $category;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): ManualSorting
    {
        $this->product = $product;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): ManualSorting
    {
        $this->position = $position;

        return $this;
    }
}
