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

namespace Shopware\Models\Config;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Shop\Shop;

/**
 * @ORM\Table(name="s_core_config_values")
 * @ORM\Entity()
 */
class Value extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Element
     *
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="values")
     * @ORM\JoinColumn(name="element_id", referencedColumnName="id", nullable=false)
     */
    private $element;

    /**
     * @var Shop|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    private $shop;

    /**
     * @var int|null
     *
     * @ORM\Column(name="shop_id", type="integer", nullable=true)
     */
    private $shopId;

    /**
     * @var array<array-key, mixed>|bool|float|int|string|null
     *
     * @ORM\Column(name="value", type="object", nullable=false)
     */
    private $value;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Element $element
     *
     * @return Value
     */
    public function setElement($element)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * @return Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param Shop|null $shop
     *
     * @return Value
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * @return Shop|null
     */
    public function getShop()
    {
        return $this->shop;
    }

    public function getShopId(): ?int
    {
        return $this->shopId;
    }

    public function setShopId(?int $shopId): self
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * @param array<array-key, mixed>|bool|float|int|string|null $value
     *
     * @return Value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return array<array-key, mixed>|bool|float|int|string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
