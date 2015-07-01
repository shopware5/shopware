<?php

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway\CurrencyGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CurrencyHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;

class CurrencyGateway implements CurrencyGatewayInterface
{
    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var CurrencyHydrator
     */
    private $hydrator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param CurrencyHydrator $hydrator
     * @param FieldHelper $fieldHelper
     * @param Connection $connection
     */
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
     * @return \array[]
     */
    private function getCurrencies($ids)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect($this->fieldHelper->getCurrencyFields())
            ->from('s_core_currencies', 'currency')
            ->where('currency.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }
}
