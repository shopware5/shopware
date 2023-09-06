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

namespace Shopware\Models\Article\Configurator;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_article_configurator_price_variations")
 */
class PriceVariation extends ModelEntity
{
    /**
     * @var Set
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Set", inversedBy="priceVariations")
     * @ORM\JoinColumn(name="configurator_set_id", referencedColumnName="id", nullable=false)
     */
    protected $configuratorSet;

    /**
     * @var int|null
     *
     * @ORM\Column(name="is_gross", type="integer", nullable=true)
     */
    protected $isGross;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="options", type="text", nullable=true)
     */
    private $options;

    /**
     * @var string
     *
     * @ORM\Column(name="variation", type="decimal", precision=10, scale=3, nullable=false)
     */
    private $variation;

    /**
     * @var int|null
     *
     * @ORM\Column(name="configurator_set_id", type="integer", nullable=true)
     */
    private $configuratorSetId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @param string $variation
     */
    public function setVariation($variation)
    {
        $this->variation = $variation;
    }

    /**
     * @return Set
     */
    public function getConfiguratorSet()
    {
        return $this->configuratorSet;
    }

    /**
     * @param Set $configuratorSet
     */
    public function setConfiguratorSet($configuratorSet)
    {
        $this->configuratorSet = $configuratorSet;
    }

    /**
     * @param string $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return string|null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return int|null
     */
    public function getIsGross()
    {
        return $this->isGross;
    }

    /**
     * @param int|null $isGross
     */
    public function setIsGross($isGross)
    {
        $this->isGross = $isGross;
    }
}
