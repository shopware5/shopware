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

namespace Shopware\Bundle\CartBundle\Domain\Cart;

use Ramsey\Uuid\Uuid;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;

class CartContainer implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var LineItemCollection
     */
    private $items;

    /**
     * @var string
     */
    private $token;

    private function __construct(string $name, string $token, LineItemCollection $items)
    {
        $this->name = $name;
        $this->token = $token;
        $this->items = $items;
    }

    public static function createNew(string $name): CartContainer
    {
        return new self($name, Uuid::uuid4()->toString(), new LineItemCollection());
    }

    public static function createExisting(string $name, string $token, array $items): CartContainer
    {
        return new self($name, $token, new LineItemCollection($items));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getLineItems(): LineItemCollection
    {
        return $this->items;
    }

    public function serialize(): string
    {
        $items = $this->items->map(function (LineItemInterface $item) {
            return $item->serialize();
        });

        return json_encode([
            'items' => $items,
            'token' => $this->getToken(),
            'name' => $this->getName(),
        ]);
    }

    public static function unserialize(string $json): CartContainer
    {
        $data = json_decode($json, true);

        $items = array_map(function ($item) {
            return self::unserializeItem($item);
        }, $data['items']);

        return self::createExisting($data['name'], $data['token'], $items);
    }

    public static function unserializeItem(string $json): LineItemInterface
    {
        $decoded = json_decode($json, true);

        if (is_array($decoded) && array_key_exists('_class', $decoded)) {
            /** @var LineItemInterface $class */
            $class = $decoded['_class'];

            return $class::unserialize($json);
        }

        return LineItem::unserialize($json);
    }
}
