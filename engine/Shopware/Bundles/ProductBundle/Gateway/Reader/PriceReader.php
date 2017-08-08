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

namespace ProductBundle\Gateway;

use Doctrine\DBAL\Connection;
use ProductBundle\Struct\Price;
use ProductBundle\Struct\PriceCollection;
use ProductBundle\Struct\ProductPriceCollection;
use Shopware\Cart\Tax\TaxCalculator;
use Shopware\Cart\Tax\TaxRule;
use Shopware\Cart\Tax\TaxRuleCollection;
use Shopware\Framework\Struct\FieldHelper;
use Shopware\Context\Struct\TranslationContext;

class PriceReader
{
//    public function read(array $productNumbers, TranslationContext $context): ProductPriceCollection
//    {
//        $query = $this->connection->createQueryBuilder();
//
//        $query->addSelect('variants.ordernumber as number');
//        $query->addSelect($this->fieldHelper->getPriceFields());
//        $query->addSelect($this->fieldHelper->getTaxFields());
//
//        $query->from('s_articles_prices', 'price')
//            ->leftJoin('price', 's_articles_prices_attributes', 'priceAttribute', 'priceAttribute.priceID = price.id')
//
//            ->innerJoin('price', 's_articles_details', 'variants', 'variants.id = price.articledetailsID')
//            ->innerJoin('variants', 's_articles', 'product', 'product.id = variants.articleID')
//            ->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID')
//            ->andWhere('variants.ordernumber IN (:products)')
//            ->addOrderBy('price.articledetailsID', 'ASC')
//            ->addOrderBy('price.from', 'ASC')
//            ->setParameter(':products', $productNumbers, Connection::PARAM_INT_ARRAY);
//
//        $this->fieldHelper->addPriceTranslation($query, $context);
//
//        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
//        $statement = $query->execute();
//
//        $data = $statement->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_ASSOC);
//
//        $productPriceCollection = new ProductPriceCollection();
//
//        foreach ($data as $number => $rows) {
//            $priceCollection = new PriceCollection();
//
//            foreach ($rows as $row) {
//                $net = (float) $row['__price_price'];
//
//                $rules = new TaxRuleCollection([
//                    new TaxRule((float) $row['__tax_tax']),
//                ]);
//
//                $gross = $this->taxCalculator->calculateGross($net, $rules);
//
//                $taxes = $this->taxCalculator->calculateGrossTaxes($gross, $rules);
//
//                $price = new Price();
//                $price->setId((int) $row['__price_id']);
//                $price->setFrom((int) $row['__price_from']);
//                $price->setTo($row['__price_to'] !== 'beliebig' ? (int) $row['__price_to'] : null);
//                $price->setNetPrice($net);
//                $price->setGrossPrice($gross);
//                $price->setTaxes($taxes);
//                $price->setTaxRules($rules);
//                $price->setCustomerGroupKey($row['__price_pricegroup']);
//
//                $priceCollection->add($price);
//            }
//            $priceCollection->sortMatrix();
//
//            $productPriceCollection->add($number, $priceCollection);
//        }
//
//        return $productPriceCollection;
//    }
}
