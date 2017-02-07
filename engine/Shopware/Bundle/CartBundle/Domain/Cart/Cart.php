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

namespace Shopware\Bundle\CartBundle\Domain\Cart;

use Ramsey\Uuid\Uuid;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;

class Cart
{
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

    /**
     * @param string $name
     * @param $token
     * @param LineItemCollection $items
     */
    private function __construct($name, $token, LineItemCollection $items)
    {
        $this->name = $name;
        $this->token = $token;
        $this->items = $items;
    }

    /**
     * @param string $name
     * @return Cart
     */
    public static function createNew($name)
    {
        return new self($name, Uuid::uuid4()->toString(), new LineItemCollection());
    }

    /**
     * @param string $name
     * @param string $token
     * @param LineItemInterface[] $items
     * @return Cart
     */
    public static function createExisting($name, $token, array $items = [])
    {
        return new self($name, $token, new LineItemCollection($items));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return LineItemCollection
     */
    public function getLineItems()
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        $items = $this->items->map(function (LineItemInterface $item) {
            return $item->serialize();
        });

        return json_encode([
            'items' => $items,
            'token' => $this->getToken(),
            'name' => $this->getName()
        ]);
    }

    /**
     * @param string $json
     * @return Cart
     */
    public static function unserialize($json)
    {
        $data = json_decode($json, true);

        $items = array_map(function ($item) {
            return self::unserializeItem($item);
        }, $data['items']);

        return self::createExisting($data['name'], $data['token'], $items);
    }

    /**
     * @param string $json
     * @return LineItem|LineItemInterface
     */
    public static function unserializeItem($json)
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
