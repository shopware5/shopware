<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Models\Article\Configurator;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_article_configurator_price_surcharges")
 */
class PriceSurcharge extends ModelEntity
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
     * @var integer
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId = null;

    /**
     * @var integer
     * @ORM\Column(name="child_id", type="integer", nullable=true)
     */
    private $childId = null;

    /**
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="surchargeParents")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @var \Shopware\Models\Article\Configurator\Option
     */
    private $parentOption;

    /**
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="surchargeChildren")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="id")
     * @var \Shopware\Models\Article\Configurator\Option
     */
    private $childOption;

    /**
     * @var float $price
     *
     * @ORM\Column(name="surcharge", type="float", nullable=false, precision=3)
     */
    private $surcharge;

    /**
     * @var \Shopware\Models\Article\Configurator\Set
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Set", inversedBy="priceSurcharges")
     * @ORM\JoinColumn(name="configurator_set_id", referencedColumnName="id")
     */
    protected $configuratorSet;

    /**
     * @var integer
     * @ORM\Column(name="configurator_set_id", type="integer", nullable=true)
     */
    private $configuratorSetId = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopware\Models\Article\Configurator\Option
     */
    public function getParentOption()
    {
        return $this->parentOption;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Option $parentOption
     */
    public function setParentOption($parentOption)
    {
        $this->parentOption = $parentOption;
    }

    /**
     * @return \Shopware\Models\Article\Configurator\Option
     */
    public function getChildOption()
    {
        return $this->childOption;
    }

    /**
     * @param $childOption
     * @return void
     */
    public function setChildOption($childOption)
    {
        $this->childOption = $childOption;
    }

    /**
     * @return float
     */
    public function getSurcharge()
    {
        return $this->surcharge;
    }

    /**
     * @param float $surcharge
     */
    public function setSurcharge($surcharge)
    {
        $this->surcharge = $surcharge;
    }

    /**
     * @return \Shopware\Models\Article\Configurator\Set
     */
    public function getConfiguratorSet()
    {
        return $this->configuratorSet;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Set $configuratorSet
     */
    public function setConfiguratorSet($configuratorSet)
    {
        $this->configuratorSet = $configuratorSet;
    }
}
