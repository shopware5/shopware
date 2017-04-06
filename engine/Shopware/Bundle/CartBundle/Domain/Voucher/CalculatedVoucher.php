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

namespace Shopware\Bundle\CartBundle\Domain\Voucher;

use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Infrastructure\View\ViewLineItemInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;

class CalculatedVoucher implements CalculatedLineItemInterface, ViewLineItemInterface
{
    use JsonSerializableTrait;

    /**
     * @var LineItemInterface
     */
    protected $lineItem;

    /**
     * @var Price
     */
    protected $price;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $identifier;

    public function __construct(
        string $code,
        LineItemInterface $lineItem,
        Price $price
    ) {
        $this->price = $price;
        $this->lineItem = $lineItem;
        $this->code = $code;
        $this->identifier = $this->lineItem->getIdentifier();
        $this->label = $code;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getLineItem(): CalculatedLineItemInterface
    {
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCover(): ? Media
    {
        return null;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getQuantity(): int
    {
        return $this->lineItem->getQuantity();
    }
}
