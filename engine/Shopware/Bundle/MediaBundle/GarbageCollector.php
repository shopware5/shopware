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

/**
 * Class GarbageCollector
 * @package Shopware\Bundle\MediaBundle
 */
class GarbageCollector
{
    /**
     * @var MediaPosition[]
     */
    private $mediaPositions;

    /**
     * @var Connection
     */
    private $connection = null;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var array
     */
    private $queue = [
        'id' => [],
        'path' => []
    ];

    /**
     * @param MediaPosition[] $mediaPositions
     * @param Connection $dbConnection
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(array $mediaPositions, Connection $dbConnection, MediaServiceInterface $mediaService)
    {
        $this->mediaPositions = $mediaPositions;
        $this->connection = $dbConnection;
        $this->mediaService = $mediaService;
    }

    /**
     * Start garbage collector job
     *
     * @return int
     */
    public function run()
    {
        // create temp table
        $this->createTempTable();

        foreach ($this->mediaPositions as $mediaPosition) {
            $this->find($mediaPosition);
        }

        // write media refs to used table
        $this->processQueue();

        // change album to recycle bin
        $this->moveToTrash();

        return count($this->mediaPositions);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createTempTable()
    {
        $this->connection->exec("CREATE TEMPORARY TABLE IF NOT EXISTS s_media_used (id int auto_increment, mediaId int NOT NULL, PRIMARY KEY pkid (id))");
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function moveToTrash()
    {
        $sql = "UPDATE s_media m SET albumID=-13 WHERE m.id NOT IN (SELECT mediaId FROM s_media_used) AND albumID = -1";
        $this->connection->exec($sql);
    }

    /**
     * @param MediaPosition $mediaPosition
     * @throws \Doctrine\DBAL\DBALException
     */
    private function find(MediaPosition $mediaPosition)
    {
        switch ($mediaPosition->getParseType()) {
            case MediaPosition::PARSE_JSON:
                $this->handleJsonTable($mediaPosition);
                break;

            case MediaPosition::PARSE_SERIALIZE:
                $this->handleSerializeTable($mediaPosition);
                break;

            case MediaPosition::PARSE_HTML:
                $this->handleHtmlTable($mediaPosition);
                break;

            default:
                $this->handleTable($mediaPosition);
        }
    }

    /**
     * @return bool|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCount()
    {
        $query = $this->connection->query("SELECT count(*) AS cnt FROM `s_media` WHERE albumID = -13");

        return $query->fetchColumn();
    }

    /**
     * Handles tables with json content
     * @param MediaPosition $mediaPosition
     * @throws \Doctrine\DBAL\DBALException
     */
    private function handleJsonTable(MediaPosition $mediaPosition)
    {
        $rows = $this->fetchColumn($mediaPosition);

        foreach ($rows as $row) {
            $jsonValues = json_decode($row);

            if (!$jsonValues || empty($jsonValues)) {
                continue;
            }

            if (is_array($jsonValues)) {
                foreach ($jsonValues as $value) {
                    if (isset($value->mediaId)) {
                        $this->addMediaById((int) $value->mediaId);
                    } elseif (isset($value->path)) {
                        $this->addMediaByPath($value->path);
                    }
                }
            } elseif (is_object($jsonValues)) {
                if (isset($jsonValues->mediaId)) {
                    $this->addMediaById((int) $jsonValues->mediaId);
                } elseif (isset($jsonValues->path)) {
                    $this->addMediaByPath($jsonValues->path);
                }
            }
        }
    }

    /**
     * Handles tables with serialized content
     *
     * @param MediaPosition $mediaPosition
     */
    private function handleSerializeTable(MediaPosition $mediaPosition)
    {
        $values = $this->fetchColumn($mediaPosition);

        foreach ($values as $value) {
            $value = unserialize($value);
            $this->addMediaByPath($value);
        }
    }

    /**
     * Handles tables with html content
     *
     * @param MediaPosition $mediaPosition
     */
    private function handleHtmlTable(MediaPosition $mediaPosition)
    {
        $values = $this->fetchColumn($mediaPosition);

        foreach ($values as $value) {
            preg_match_all("/<(\s+)?img(?:.*src=[\"'](.*?)[\"'].*)\/>?/mi", $value, $matches);

            if (isset($matches[2]) && !empty($matches[2])) {
                foreach ($matches[2] as $match) {
                    $match = $this->mediaService->normalize($match);
                    $this->addMediaByPath($match);
                }
            }
        }
    }

    /**
     * @param MediaPosition $mediaPosition
     * @throws \Doctrine\DBAL\DBALException
     */
    private function handleTable(MediaPosition $mediaPosition)
    {
        $sql = sprintf(
            'INSERT INTO s_media_used
                    SELECT DISTINCT NULL, m.id
                    FROM s_media m
                    INNER JOIN %1$s
                        ON %1$s.%2$s = m.%3$s',
            $mediaPosition->getSourceTable(),
            $mediaPosition->getSourceColumn(),
            $mediaPosition->getMediaColumn()
        );

        $this->connection->exec($sql);
    }

    /**
     * Adds a media by path to used table
     *
     * @param $path
     */
    private function addMediaByPath($path)
    {
        $path = $this->mediaService->normalize($path);
        $this->queue['path'][] = $path;
    }

    /**
     * Adds a media by id to used table
     *
     * @param $mediaId
     */
    private function addMediaById($mediaId)
    {
        $this->queue['id'][] = $mediaId;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function processQueue()
    {
        // process paths
        if (!empty($this->queue['path'])) {
            $paths = array_unique($this->queue['path']);
            $sql = 'INSERT INTO s_media_used SELECT DISTINCT NULL, m.id FROM s_media m WHERE m.path IN (:mediaPaths)';
            $this->connection->executeQuery(
                $sql,
                [
                    ':mediaPaths' => $paths
                ],
                [
                    ':mediaPaths' => Connection::PARAM_INT_ARRAY
                ]
            );
        }

        // process ids
        if (!empty($this->queue['id'])) {
            $ids = array_unique($this->queue['id']);
            $idString = '(' . implode('),(', $ids) . ')';
            $sql = sprintf("INSERT INTO s_media_used (mediaId) VALUES %s", $idString);
            $this->connection->executeQuery($sql);
        }
    }

    /**
     * @param MediaPosition $mediaPosition
     * @return array
     */
    private function fetchColumn(MediaPosition $mediaPosition)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->connection->createQueryBuilder();

        $values = $queryBuilder->select($mediaPosition->getSourceColumn())
            ->from($mediaPosition->getSourceTable())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        return $values;
    }
}
