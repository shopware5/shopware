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

namespace Shopware\Models\Shop;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Default Currency Model Entity
 *
 * @ORM\Table(name="s_core_currencies")
 * @ORM\Entity()
 */
class Currency extends ModelEntity
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
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=255, nullable=false)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="standard", type="integer", nullable=false)
     */
    private $default = false;

    /**
     * @var float
     *
     * @ORM\Column(name="factor", type="float", nullable=false)
     */
    private $factor;

    /**
     * @var string
     *
     * @ORM\Column(name="templatechar", type="string", length=255, nullable=false)
     */
    private $symbol = '';

    /**
     * @var int
     *
     * @ORM\Column(name="symbol_position", type="integer", nullable=false)
     */
    private $symbolPosition = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $currency
     *
     * @return Currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $name
     *
     * @return Currency
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $default
     *
     * @return Currency
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param float $factor
     *
     * @return Currency
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;

        return $this;
    }

    /**
     * @return float
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * @param string $symbol
     *
     * @return Currency
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param int $symbolPosition
     *
     * @return Currency
     */
    public function setSymbolPosition($symbolPosition)
    {
        $this->symbolPosition = $symbolPosition;

        return $this;
    }

    /**
     * @return int
     */
    public function getSymbolPosition()
    {
        return $this->symbolPosition;
    }

    /**
     * @param int $position
     *
     * @return Currency
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->getCurrency();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $options = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'currency' => $this->getCurrency(),
            'factor' => $this->getFactor(),
        ];
        if ($this->getSymbol()) {
            $options['symbol'] = $this->getSymbol();
        }
        if ($this->getSymbolPosition() > 0) {
            $options['position'] = $this->getSymbolPosition();
        }

        return $options;
    }
}
