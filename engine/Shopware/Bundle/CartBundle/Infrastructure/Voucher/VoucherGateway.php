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
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Rule\Container\AndRule;
use Shopware\Bundle\CartBundle\Domain\Rule\DateRangeRule;
use Shopware\Bundle\CartBundle\Domain\Rule\ProductOfManufacturerRule;
use Shopware\Bundle\CartBundle\Domain\Rule\Rule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Domain\Voucher\AbsoluteVoucherData;
use Shopware\Bundle\CartBundle\Domain\Voucher\PercentageVoucherData;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherData;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherDataCollection;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherGatewayInterface;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\CustomerGroupRule;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\GoodsPriceRule;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\LineItemInCartRule;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\ShopRule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class VoucherGateway implements VoucherGatewayInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function get(array $codes, ShopContextInterface $context): VoucherDataCollection
    {
        $query = $this->createVoucherQuery($codes);

        $rows = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $vouchers = new VoucherDataCollection();
        foreach ($rows as $row) {
            $vouchers->add($this->hydrate($row));
        }

        if (count($codes) === count($rows)) {
            return $vouchers;
        }

        //fetch individual code

        return $vouchers;
    }

    private function hydrate(array $row): VoucherData
    {
        $price = (float) $row['value'];

        if ($row['percental']) {
            return new PercentageVoucherData(
                $row['vouchercode'],
                $this->buildRule($row),
                $price
            );
        }

        return new AbsoluteVoucherData(
            $row['vouchercode'],
            $this->buildRule($row),
            new PriceDefinition($price, new TaxRuleCollection(), 1, true)
        );
    }

    private function createVoucherQuery(array $codes): QueryBuilder
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

    private function buildRule($row): Rule
    {
        $rule = new AndRule();
        if ($row['customergroup']) {
            $rule->addRule(
                new CustomerGroupRule([(int) $row['customergroup']])
            );
        }

        if ($row['valid_from'] || $row['valid_to']) {
            $rule->addRule(
                new DateRangeRule(
                    $row['valid_from'] ? new \DateTime($row['valid_from']) : null,
                    $row['valid_to'] ? new \DateTime($row['valid_to']) : null
                )
            );
        }

        if ($row['subshopID']) {
            $rule->addRule(
                new ShopRule([(int) $row['subshopID']], Rule::OPERATOR_EQ)
            );
        }

        if ($row['bindtosupplier']) {
            $rule->addRule(
                new ProductOfManufacturerRule([(int) $row['bindtosupplier']])
            );
        }

        if ($row['minimumcharge']) {
            $rule->addRule(
                new GoodsPriceRule(
                    (float) $row['minimumcharge'],
                    Rule::OPERATOR_GTE
                )
            );
        }

        if ($row['restrictarticles']) {
            $rule->addRule(
                new LineItemInCartRule(
                    explode(';', $row['restrictarticles'])
                )
            );
        }

        return $rule;
    }
}
