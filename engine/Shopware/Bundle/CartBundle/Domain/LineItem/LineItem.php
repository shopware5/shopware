<?php
declare(strict_types=1);
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

use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;

class LineItem implements LineItemInterface
{
    use JsonSerializableTrait;

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

    public function __construct(
        string $identifier,
        string $type,
        int $quantity,
        array $extraData = []
    ) {
        $this->identifier = $identifier;
        $this->quantity = $quantity;
        $this->type = $type;
        $this->extraData = $extraData;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExtraData(): array
    {
        return $this->extraData;
    }

    public function serialize(): string
    {
        return json_encode(get_object_vars($this));
    }

    public static function unserialize(string $data): LineItemInterface
    {
        $data = json_decode($data, true);

        return new self(
            (string) $data['identifier'],
            (string) $data['type'],
            (int) $data['quantity'],
            (array) $data['extraData']
        );
    }
}
