<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\CurrencyGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CurrencyHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;

class CurrencyGateway implements CurrencyGatewayInterface
{
    private FieldHelper $fieldHelper;

    private CurrencyHydrator $hydrator;

    private Connection $connection;

    public function __construct(
        CurrencyHydrator $hydrator,
        FieldHelper $fieldHelper,
        Connection $connection
    ) {
        $this->hydrator = $hydrator;
        $this->fieldHelper = $fieldHelper;
        $this->connection = $connection;
    }

    /**
     * @param int[] $ids
     *
     * @return Currency[] indexed by id
     */
    public function getList($ids)
    {
        $currencies = $this->getCurrencies($ids);
        $result = [];
        foreach ($currencies as $row) {
            $currency = $this->hydrator->hydrate($row);
            $result[$currency->getId()] = $currency;
        }

        return $result;
    }

    /**
     * @param int[] $ids
     *
     * @return array<array<string, mixed>>
     */
    private function getCurrencies(array $ids): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect($this->fieldHelper->getCurrencyFields())
            ->from('s_core_currencies', 'currency')
            ->where('currency.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }
}
