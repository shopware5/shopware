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

namespace Shopware\Bundle\CartBundle\Domain\LineItem;

class LineItem implements LineItemInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $extraData;

    /**
     * @param string $identifier
     * @param string $type
     * @param float $quantity
     * @param array $extraData
     */
    public function __construct(
        $identifier,
        $type,
        $quantity,
        array $extraData = []
    ) {
        $this->identifier = $identifier;
        $this->quantity = $quantity;
        $this->type = $type;
        $this->extraData = $extraData;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getExtraData()
    {
        return $this->extraData;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode(get_object_vars($this));
    }

    /**
     * @param string $json
     * @return LineItem
     */
    public static function unserialize($json)
    {
        $data = json_decode($json, true);
        return new self(
            $data['identifier'],
            $data['type'],
            $data['quantity'],
            $data['extraData']
        );
    }
}
