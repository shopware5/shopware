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

namespace Shopware\Bundle\CartBundle\Infrastructure\Voucher;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTax;
use Shopware\Bundle\CartBundle\Domain\Tax\PercentageTaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Container\AndRule;
use Shopware\Bundle\CartBundle\Domain\Voucher\Voucher;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherCollection;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherGatewayInterface;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherProcessor;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VoucherGateway implements VoucherGatewayInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function get(array $codes, CalculatedCart $calculatedCart, ShopContextInterface $context): VoucherCollection
    {
        $query = $this->fetchSimpleVouchers($codes);

        $rows = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $vouchers = new VoucherCollection();
        foreach ($rows as $row) {
            $vouchers->add($this->hydrate($row, $calculatedCart));
        }

        if (count($codes) === count($rows)) {
            return $vouchers;
        }

        //fetch individual code

        return $vouchers;
    }

    private function hydrate(array $row, CalculatedCart $calculatedCart): Voucher
    {
        $percentage = (float) $row['value'];

        $price = new PriceDefinition(
            $percentage,
            $this->buildPercentageTaxRule(
                $calculatedCart->getCalculatedLineItems()->getPrices()->getTotalPrice()
            ),
            1,
            true
        );

        $rule = new AndRule();

        $mode = $row['percental'] ? VoucherProcessor::TYPE_PERCENTAGE : VoucherProcessor::TYPE_ABSOLUTE;

        if (count($rule->getRules()) === 0) {
            $rule = null;
        }

        return new Voucher($row['vouchercode'], $mode, $percentage, $price, $rule);
    }

    private function buildPercentageTaxRule(Price $price): TaxRuleCollection
    {
        $rules = new TaxRuleCollection([]);

        /** @var CalculatedTax $tax */
        foreach ($price->getCalculatedTaxes() as $tax) {
            $rules->add(
                new PercentageTaxRule(
                    $tax->getTaxRate(),
                    $tax->getPrice() / $price->getTotalPrice() * 100
                )
            );
        }

        return $rules;
    }

    private function fetchSimpleVouchers(array $codes): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'vouchercode',
            'modus',
            'percental',
            'value',

            //validations
            'customergroup',
            'subshopID',
            'valid_from',
            'valid_to',
            'bindtosupplier',
            'minimumcharge',
            'restrictarticles',
        ]);
        $query->from('s_emarketing_vouchers', 'voucher');
        $query->where('voucher.vouchercode IN (:codes)');
        $query->setParameter(':codes', $codes, Connection::PARAM_STR_ARRAY);

        return $query;
    }
}
