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

namespace Shopware\Bundle\ESIndexingBundle\Property;

use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Shopware\Bundle\ESIndexingBundle\Struct\ShopIndex;
use Shopware\Bundle\ESIndexingBundle\Subscriber\ORMBacklogSubscriber;
use Shopware\Bundle\ESIndexingBundle\SynchronizerInterface;

class PropertySynchronizer implements SynchronizerInterface
{
    /**
     * @var PropertyIndexer
     */
    private $propertyIndexer;

    public function __construct(PropertyIndexer $propertyIndexer)
    {
        $this->propertyIndexer = $propertyIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public function synchronize(ShopIndex $shopIndex, array $backlog)
    {
        $ids = $this->getPropertyIdsOfBacklog($backlog);

        if (empty($ids)) {
            return;
        }

        $size = 100;
        $chunks = array_chunk($ids, $size);
        foreach ($chunks as $chunk) {
            $this->propertyIndexer->indexProperties($shopIndex, $chunk);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(): string
    {
        return $this->propertyIndexer->supports();
    }

    /**
     * @param Backlog[] $backlogs
     *
     * @return int[]
     */
    private function getPropertyIdsOfBacklog($backlogs)
    {
        $ids = [];
        foreach ($backlogs as $backlog) {
            $payload = $backlog->getPayload();

            switch ($backlog->getEvent()) {
                case ORMBacklogSubscriber::EVENT_PROPERTY_GROUP_DELETED:
                case ORMBacklogSubscriber::EVENT_PROPERTY_GROUP_INSERTED:
                case ORMBacklogSubscriber::EVENT_PROPERTY_GROUP_UPDATED:
                    $ids[] = $payload['id'];
                    break;

                case ORMBacklogSubscriber::EVENT_PROPERTY_OPTION_DELETED:
                case ORMBacklogSubscriber::EVENT_PROPERTY_OPTION_INSERTED:
                case ORMBacklogSubscriber::EVENT_PROPERTY_OPTION_UPDATED:
                    $ids[] = $payload['groupId'];
                    break;
            }
        }

        return array_unique(array_filter($ids));
    }
}
