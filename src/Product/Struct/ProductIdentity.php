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

namespace Shopware\Product\Struct;

use Shopware\Framework\Struct\Struct;

class ProductIdentity extends Struct
{
    /**
     * Unique identifier of the product (s_articles).
     *
     * @var int
     */
    protected $id;

    /**
     * Unique identifier of the product variation (s_articles_details).
     *
     * @var int
     */
    protected $variantId;

    /**
     * Unique identifier field.
     * Shopware order number for the product, which
     * is used to load the product or add the product
     * to the basket.
     *
     * @var string
     */
    protected $number;

    /**
     * @var int
     */
    protected $mainVariantId;

    /**
     * @var bool
     */
    protected $isMainVariant;

    /**
     * @var bool
     */
    protected $productActive;

    /**
     * @var bool
     */
    protected $variantActive;

    public function __construct(int $id, int $variantId, string $number, int $mainVariantId, bool $productActive, bool $variantActive)
    {
        $this->id = $id;
        $this->variantId = $variantId;
        $this->number = $number;
        $this->mainVariantId = $mainVariantId;
        $this->isMainVariant = $this->variantId === $this->mainVariantId;
        $this->productActive = $productActive;
        $this->variantActive = $variantActive;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVariantId(): int
    {
        return $this->variantId;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getMainVariantId(): int
    {
        return $this->mainVariantId;
    }

    public function isMainVariant(): bool
    {
        return $this->isMainVariant;
    }

    public function isProductActive(): bool
    {
        return $this->productActive;
    }

    public function isVariantActive(): bool
    {
        return $this->variantActive;
    }
}
