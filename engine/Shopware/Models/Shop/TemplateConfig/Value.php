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

namespace Shopware\Models\Shop\TemplateConfig;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Shop\Shop;

/**
 * @ORM\Table(name="s_core_templates_config_values")
 * @ORM\Entity()
 */
class Value extends ModelEntity
{
    /**
     * @var Element
     * @ORM\ManyToOne(
     *     targetEntity="Shopware\Models\Shop\TemplateConfig\Element",
     *     inversedBy="values"
     * )
     * @ORM\JoinColumn(name="element_id", referencedColumnName="id")
     */
    protected $element;

    /**
     * @var Shop
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    protected $shop;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="element_id", type="integer", nullable=false)
     */
    private $elementId;

    /**
     * @var int
     * @ORM\Column(name="shop_id", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @var array
     * @ORM\Column(name="value", type="array", nullable=false)
     */
    private $value;

    /**
     * @param \Shopware\Models\Shop\TemplateConfig\Element $element
     */
    public function setElement($element)
    {
        $this->element = $element;
    }

    /**
     * @return \Shopware\Models\Shop\TemplateConfig\Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param array $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \Shopware\Models\Shop\Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return \Shopware\Models\Shop\Shop|null
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
