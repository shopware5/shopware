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

namespace Shopware\Components\CustomerStream;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\LogawareReflectionHelper;

class CustomerStreamCriteriaFactory implements CustomerStreamCriteriaFactoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LogawareReflectionHelper
     */
    private $reflectionHelper;

    public function __construct(Connection $connection, LogawareReflectionHelper $reflectionHelper)
    {
        $this->connection = $connection;
        $this->reflectionHelper = $reflectionHelper;
    }

    /**
     * @param int $streamId
     *
     * @return Criteria
     */
    public function createCriteria($streamId)
    {
        $stream = $this->getStream($streamId);
        if (!$stream) {
            throw new \RuntimeException(sprintf('Stream by id %s not found', $streamId));
        }

        if (empty($stream['conditions'])) {
            throw new \RuntimeException(sprintf('Stream %s has no conditions', $stream['name']));
        }

        $conditions = $this->reflectionHelper->unserialize(
            json_decode($stream['conditions'], true),
            sprintf('Error while unserialize stream conditions')
        );

        $criteria = new Criteria();

        foreach ($conditions as $condition) {
            $criteria->addCondition($condition);
        }

        return $criteria;
    }

    /**
     * @param int $streamId
     *
     * @return array|false
     */
    private function getStream($streamId)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('*');
        $query->from('s_customer_streams');
        $query->where('id = :id');
        $query->setParameter(':id', $streamId);

        return $query->execute()->fetch(\PDO::FETCH_ASSOC);
    }
}
