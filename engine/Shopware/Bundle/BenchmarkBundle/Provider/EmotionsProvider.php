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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;

class EmotionsProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'emotions';
    }

    /**
     * @return array
     */
    public function getBenchmarkData()
    {
        return [
            'total' => $this->getTotalEmotions(),
            'landingPages' => $this->getLandingPageEmotions(),
            'timed' => $this->getTimedEmotions(),
            'elementUsages' => $this->getElementUsages(),
            'viewportUsages' => $this->getViewportUsages(),
        ];
    }

    /**
     * @return int
     */
    private function getTotalEmotions()
    {
        $queryBuilder = $this->getBasicEmotionCountQueryBuilder();

        return (int) $queryBuilder->execute()->fetchColumn();
    }

    /**
     * @return int
     */
    private function getLandingPageEmotions()
    {
        $queryBuilder = $this->getBasicEmotionCountQueryBuilder();

        return (int) $queryBuilder->where('emotion.is_landingpage = 1')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getTimedEmotions()
    {
        $queryBuilder = $this->getBasicEmotionCountQueryBuilder();

        return (int) $queryBuilder->where('emotion.valid_from IS NOT NULL OR emotion.valid_to IS NOT NULL')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return array
     */
    private function getElementUsages()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('COUNT(element.id) as elementCount, element.x_type as elementName')
            ->from('s_emotion_element', 'elementRelation')
            ->innerJoin('elementRelation', 's_library_component', 'element', 'element.id = elementRelation.componentID')
            ->groupBy('elementRelation.componentID')
            ->execute()
            ->fetchAll();
    }

    /**
     * @return array
     */
    private function getViewportUsages()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $devicesUsed = $queryBuilder->select("GROUP_CONCAT(emotion.device SEPARATOR ',') as devicesUsed")
            ->from('s_emotion', 'emotion')
            ->execute()
            ->fetchColumn();

        $devicesUsed = explode(',', $devicesUsed);

        $deviceCounts = [];
        foreach ($devicesUsed as $device) {
            if (array_key_exists($device, $deviceCounts)) {
                ++$deviceCounts[$device];
                continue;
            }

            $deviceCounts[$device] = 1;
        }

        return $deviceCounts;
    }

    /**
     * @return QueryBuilder
     */
    private function getBasicEmotionCountQueryBuilder()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('COUNT(emotion.id)')
            ->from('s_emotion', 'emotion');
    }
}
