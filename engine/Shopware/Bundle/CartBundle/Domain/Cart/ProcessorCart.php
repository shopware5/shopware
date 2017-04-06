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

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Error\Error;
use Shopware\Bundle\CartBundle\Domain\Error\ErrorCollection;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;

class ProcessorCart implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var CalculatedLineItemCollection
     */
    protected $calculatedLineItems;

    /**
     * @var DeliveryCollection
     */
    protected $deliveries;

    /**
     * @var Error[]
     */
    protected $errors;

    public function __construct(
        CalculatedLineItemCollection $calculatedLineItems,
        DeliveryCollection $deliveries
    ) {
        $this->calculatedLineItems = $calculatedLineItems;
        $this->deliveries = $deliveries;
        $this->errors = new ErrorCollection();
    }

    public function getCalculatedLineItems(): CalculatedLineItemCollection
    {
        return $this->calculatedLineItems;
    }

    public function getDeliveries(): DeliveryCollection
    {
        return $this->deliveries;
    }

    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }
}
