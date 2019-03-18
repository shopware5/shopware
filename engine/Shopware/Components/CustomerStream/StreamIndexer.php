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
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchInterface;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchResult;
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Bundle\SearchBundle\Criteria;

class StreamIndexer implements StreamIndexerInterface
{
    /**
     * @var CustomerStreamCriteriaFactory
     */
    private $criteriaFactory;

    /**
     * @var CustomerNumberSearchInterface
     */
    private $numberSearch;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        CustomerStreamCriteriaFactory $criteriaFactory,
        CustomerNumberSearchInterface $numberSearch,
        Connection $connection
    ) {
        $this->criteriaFactory = $criteriaFactory;
        $this->numberSearch = $numberSearch;
        $this->connection = $connection;
    }

    /**
     * @param int $streamId
     */
    public function populate($streamId, ProgressHelperInterface $helper)
    {
        $this->connection->transactional(function () use ($streamId, $helper) {
            $criteria = $this->criteriaFactory->createCriteria($streamId);
            $criteria->offset(0)
                ->limit(100);

            $customers = $this->numberSearch->search($criteria);
            $helper->start($customers->getTotal(), 'Start indexing customers');

            $this->clearStreamIndex($streamId);

            $insert = $this->connection->prepare(
                'INSERT INTO s_customer_streams_mapping (stream_id, customer_id) VALUES (:streamId, :customerId)'
            );

            while ($customers->getCustomers()) {
                foreach ($customers->getIds() as $customerId) {
                    $insert->execute([
                        ':streamId' => $streamId,
                        ':customerId' => $customerId,
                    ]);
                }

                $helper->advance($criteria->getLimit());
                $criteria->offset($criteria->getOffset() + $criteria->getLimit());
                $customers = $this->numberSearch->search($criteria);
            }

            $helper->finish();
        });
    }

    /**
     * @param int $streamId
     *
     * @return CustomerNumberSearchResult
     */
    public function populatePartial($streamId, Criteria $criteria)
    {
        $result = $this->numberSearch->search($criteria);

        $insert = $this->connection->prepare(
            'INSERT IGNORE INTO s_customer_streams_mapping (stream_id, customer_id) VALUES (:streamId, :customerId)'
        );

        foreach ($result->getIds() as $customerId) {
            $insert->execute([
                ':streamId' => $streamId,
                ':customerId' => $customerId,
            ]);
        }

        return $result;
    }

    /**
     * @param int $streamId
     */
    public function clearStreamIndex($streamId)
    {
        $this->connection->delete('s_customer_streams_mapping', [
            'stream_id' => $streamId,
        ]);
    }
}
