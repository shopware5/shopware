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

namespace Shopware\Models\Article\Configurator\Template;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\LazyFetchModelEntity;
use Shopware\Models\Attribute\TemplatePrice as TemplatePriceAttribute;
use Shopware\Models\Customer\Group as CustomerGroup;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_article_configurator_template_prices")
 */
class Price extends LazyFetchModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Template|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Template\Template", inversedBy="prices")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", nullable=true)
     * @ORM\OrderBy({"customerGroupKey" = "ASC", "from" = "ASC"})
     */
    protected $template;

    /**
     * INVERSE SIDE
     *
     * @var TemplatePriceAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\TemplatePrice", mappedBy="templatePrice", cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    private $templateId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="customer_group_key", type="string", length=15, nullable=true)
     */
    private $customerGroupKey;

    /**
     * @var int
     *
     * @ORM\Column(name="`from`", type="integer", nullable=false)
     */
    private $from;

    /**
     * @var string
     *
     * @ORM\Column(name="`to`", type="string", nullable=false)
     */
    private $to = 'beliebig';

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price = 0.0;

    /**
     * @var float|null
     *
     * @ORM\Column(name="pseudoprice", type="float", nullable=true)
     */
    private $pseudoPrice = 0.0;

    /**
     * @ORM\Column(name="regulation_price", type="float", nullable=true)
     */
    private ?float $regulationPrice = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="percent", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $percent = '0.0';

    /**
     * @var CustomerGroup|null
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_key", referencedColumnName="groupkey", nullable=true)
     */
    private $customerGroup;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param CustomerGroup|null $customerGroup
     *
     * @return Price
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * @return CustomerGroup|null
     */
    public function getCustomerGroup()
    {
        return $this->fetchLazy($this->customerGroup, ['key' => $this->customerGroupKey]);
    }

    /**
     * @param int $from
     *
     * @return Price
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param int|string|null $to
     *
     * @return Price
     */
    public function setTo($to)
    {
        if ($to === null) {
            $to = 'beliebig';
        }
        $this->to = (string) $to;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTo()
    {
        return $this->to < 0 ? null : $this->to;
    }

    /**
     * @param float $price
     *
     * @return Price
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float|null $pseudoPrice
     *
     * @return Price
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * @param string|null $percent
     *
     * @return Price
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @return TemplatePriceAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param TemplatePriceAttribute|array|null $attribute
     *
     * @return Price
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, TemplatePriceAttribute::class, 'attribute', 'templatePrice');
    }

    /**
     * @return Template|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param Template|null $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getRegulationPrice(): ?float
    {
        return $this->regulationPrice;
    }

    public function setRegulationPrice(?float $regulationPrice): void
    {
        $this->regulationPrice = $regulationPrice;
    }
}
