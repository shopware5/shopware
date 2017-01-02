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

use Shopware\Bundle\CustomerSearchBundle\Criteria;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearch;
use Shopware\Bundle\CustomerSearchBundle\CustomerStream\CustomerStreamCriteriaFactory;
use Shopware\Bundle\CustomerSearchBundle\CustomerStream\StreamIndexer;
use Shopware\Components\ReflectionHelper;
use Shopware\Models\Customer\CustomerStream;

class Shopware_Controllers_Backend_CustomerStream extends Shopware_Controllers_Backend_Application
{
    const INDEXING_LIMIT = 100;

    protected $model = CustomerStream::class;

    public function indexStreamAction()
    {
        $streamId = (int) $this->Request()->getParam('streamId');
        $total = (int) $this->Request()->getParam('total');

        $iteration = (int) $this->Request()->getParam('iteration', 1);
        $offset = ($iteration - 1) * self::INDEXING_LIMIT;

        /** @var StreamIndexer $indexer */
        $indexer = $this->get('shopware_customer_search.stream_indexer');

        /** @var CustomerStreamCriteriaFactory $factory */
        $factory = $this->get('shopware_customer_search.stream_criteria_factory');

        $criteria = $factory->createCriteria($streamId);

        $criteria->offset($offset)
            ->limit(self::INDEXING_LIMIT)
            ->setFetchTotal(false);

        if ($criteria->getOffset() === 0) {
            $indexer->clearStreamIndex($streamId);
        }

        $indexer->populatePartial($streamId, $criteria);

        $handled = $offset + self::INDEXING_LIMIT;

        if ($handled >= $total) {
            $this->View()->assign([
                'success' => true,
                'finish' => true,
                'progress' => 1,
                'text' => 'All customers indexed'
            ]);
            return;
        }

        $this->View()->assign([
            'success' => true,
            'finish' => false,
            'text' => sprintf('Indexing %s of %s customers', $handled, $total),
            'progress' => $handled / $total
        ]);
    }

    public function loadStreamAction()
    {
        $request = $this->Request();

        if ($request->has('conditions')) {
            $conditions = $request->getParam('conditions', '');
        } else {
            $stream = $this->getDetail(
                $request->getParam('streamId')
            );
            $conditions = $stream['data']['conditions'];
        }

        $offset = (int) $request->getParam('start', 0);
        $limit = (int) $request->getParam('limit', 50);
        $conditions = json_decode($conditions, true);

        $criteria = $this->createCriteria($conditions);

        $criteria->offset($offset);
        $criteria->limit($limit);

        /** @var CustomerNumberSearch $numberSearch */
        $numberSearch = $this->get('shopware_customer_search.customer_number_search');

        $result = $numberSearch->search($criteria);

        $this->View()->assign([
            'success' => true,
            'total' => $result->getTotal(),
            'data' => $result->getRows()
        ]);
    }

    /**
     * @param array $conditions
     * @return Criteria
     */
    private function createCriteria(array $conditions)
    {
        $criteria = new Criteria();

        $reflector = new ReflectionHelper();

        foreach ($conditions as $className => $arguments) {
            $className = explode('|', $className);
            $className = $className[0];
            $condition = $reflector->createInstanceFromNamedArguments($className, $arguments);
            $criteria->addCondition($condition);
        }
        return $criteria;
    }

}