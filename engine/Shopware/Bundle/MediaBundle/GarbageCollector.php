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

namespace Shopware\Bundle\MediaBundle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

class GarbageCollector
{
    /**
     * @var MediaPosition[]
     */
    private $mediaPositions;

    /**
     * @var Connection $connection
     */
    private $connection = null;

    /**
     * @param MediaPosition[] $mediaPositions
     * @param Connection $dbConnection
     */
    public function __construct(array $mediaPositions, Connection $dbConnection)
    {
        $this->mediaPositions = $mediaPositions;
        $this->connection = $dbConnection;
    }

    public function run()
    {
        // create temp table
        $this->createTempTable();

        foreach ($this->mediaPositions as $mediaPosition) {
            $this->find($mediaPosition);
        }

        $this->moveToTrash();

        return count($this->mediaPositions);
    }

    private function createTempTable()
    {
        $this->connection->exec("CREATE TEMPORARY TABLE IF NOT EXISTS s_media_used (id int auto_increment, mediaId int NOT NULL, PRIMARY KEY pkid (id))");
    }

    private function moveToTrash()
    {
        $sql = "UPDATE s_media m SET albumID=-13 WHERE m.id NOT IN (SELECT mediaId FROM s_media_used) AND albumID <> -13";
        $this->connection->exec($sql);
    }

    private function find(MediaPosition $mediaPosition)
    {
        if ($mediaPosition->getTableName() == 's_emotion_element_value') {
            $this->handleJsonTable();
        }

        $sql = sprintf('INSERT INTO s_media_used
                    SELECT DISTINCT NULL, m.id
                    FROM s_media m
                    INNER JOIN %1$s
                        ON %1$s.%2$s = m.%3$s', $mediaPosition->getTableName(), $mediaPosition->getColumnName(), $mediaPosition->getType());

        $this->connection->exec($sql);
    }

    public function getCount()
    {
        $query = $this->connection->query("SELECT count(*) AS cnt FROM `s_media` WHERE albumID = -13");

        return $query->fetchColumn(0);
    }

    /**
     * Handles the special json table-column 's_emotion_element_value.value"
     */
    private function handleJsonTable()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connection->createQueryBuilder();

        $fieldQuery = $queryBuilder->select('id')
            ->from('s_library_component_field', 'clf')
            ->where("value_type = :type")
            ->setParameter('type', 'json');

        $fieldIds = $fieldQuery->execute()->fetchAll(\PDO::FETCH_COLUMN);
        $queryBuilder->resetQueryParts();

        $values = $queryBuilder->select('eev.*')
            ->from('s_emotion_element_value', 'eev')
            ->add('where', $queryBuilder->expr()->in('fieldID', $fieldIds))
            ->execute()->fetchAll();

        foreach ($values as $value) {
            if ($value["value"] === 'null') {
                continue;
            }
            $jsonValues = json_decode($value['value']);

            $mediaIds = array();
            foreach ($jsonValues as $jsonValue) {
                if (isset($jsonValue->mediaId)) {
                    $mediaIds[] = $jsonValue->mediaId;
                }
            }

            if (!empty($mediaIds)) {
                $idString = '(' . implode('),(', $mediaIds) . ')';
                $sql = sprintf("INSERT INTO s_media_used (mediaId) VALUES %s", $idString);

                $this->connection->exec($sql);
            }
        }
    }
}
