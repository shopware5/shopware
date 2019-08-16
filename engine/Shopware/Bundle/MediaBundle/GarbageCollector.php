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
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

class GarbageCollector
{
    /**
     * @var MediaPosition[]
     */
    private $mediaPositions;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var array
     */
    private $queue = [
        'id' => [],
        'path' => [],
    ];

    /**
     * @param MediaPosition[] $mediaPositions
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
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return int
     */
    public function run()
    {
        // Create temp table
        $this->createTempTable();

        foreach ($this->mediaPositions as $mediaPosition) {
            $this->find($mediaPosition);
        }

        // Write media refs to used table
        $this->processQueue();

        // Change album to recycle bin
        $this->moveToTrash();

        return count($this->mediaPositions);
    }

    /**
     * @return bool|string
     */
    public function getCount()
    {
        return $this->connection->createQueryBuilder()
            ->select('count(*) as cnt')
            ->from('s_media')
            ->where('albumID = -13')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createTempTable()
    {
        $this->connection->exec('CREATE TEMPORARY TABLE IF NOT EXISTS s_media_used (id int auto_increment, mediaId int NOT NULL, PRIMARY KEY pkid (id), INDEX media (mediaId))');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function moveToTrash()
    {
        $sql = '
            UPDATE s_media m
            LEFT JOIN s_media_used u
            ON u.mediaId = m.id
            LEFT JOIN s_media_album a
            ON m.albumID = a.id
            SET albumID=-13
            WHERE a.garbage_collectable = 1
            AND u.id IS NULL
        ';
        $this->connection->exec($sql);
    }

    /**
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

            case MediaPosition::PARSE_PIPES:
                $this->handlePipeTable($mediaPosition);
                break;

            default:
                $this->handleTable($mediaPosition);
        }
    }

    /**
     * Handles tables with json content
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
     */
    private function handleSerializeTable(MediaPosition $mediaPosition)
    {
        $values = $this->fetchColumn($mediaPosition);

        foreach ($values as $value) {
            $value = unserialize($value, ['allowed_classes' => false]);
            $this->addMediaByPath($value);
        }
    }

    /**
     * Handles tables with html content
     */
    private function handleHtmlTable(MediaPosition $mediaPosition)
    {
        $values = $this->fetchColumn($mediaPosition);

        foreach ($values as $value) {
            // Media path matches
            preg_match_all("/{{1}media[\s+]?path=[\"'](?'mediaTag'\S*)[\"']}{1}/mi", $value, $mediaMatches);
            // Src tag matches
            preg_match_all("/<?img[^<]*src=[\"'](?'srcTag'[^{]*?)[\"'][^>]*\/?>?/mi", $value, $srcMatches);
            // Link matches
            preg_match_all("/<?a[^<]*href=[\"'](?'hrefTag'[^{]*?)[\"'][^>]*\/?>?/mi", $value, $hrefMatches);

            if ($mediaMatches['mediaTag']) {
                foreach ($mediaMatches['mediaTag'] as $match) {
                    $match = $this->mediaService->normalize($match);
                    $this->addMediaByPath($match);
                }
            }

            if ($srcMatches['srcTag']) {
                foreach ($srcMatches['srcTag'] as $match) {
                    $match = $this->mediaService->normalize($match);
                    $this->addMediaByPath($match);
                }
            }

            if ($hrefMatches['hrefTag']) {
                foreach ($hrefMatches['hrefTag'] as $match) {
                    $match = $this->mediaService->normalize($match);

                    // Only add normalized media links and not arbitrary links
                    if (strpos($match, 'media/') === 0) {
                        $this->addMediaByPath($match);
                    }
                }
            }
        }
    }

    /**
     * Handles tables with IDs separated by pipes
     *
     * @param MediaPosition $mediaPosition
     */
    private function handlePipeTable($mediaPosition)
    {
        $values = $this->fetchColumn($mediaPosition);

        foreach ($values as $value) {
            /** @var array $mediaIds */
            $mediaIds = array_filter(explode('|', $value));

            foreach ($mediaIds as $id) {
                $this->addMediaById($id);
            }
        }
    }

    /**
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
     * @param string $path
     */
    private function addMediaByPath($path)
    {
        $path = $this->mediaService->normalize($path);
        $this->queue['path'][] = $path;
    }

    /**
     * Adds a media by id to used table
     *
     * @param int $mediaId
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
        // Process paths
        if (!empty($this->queue['path'])) {
            $paths = array_unique($this->queue['path']);
            $sql = 'INSERT INTO s_media_used SELECT DISTINCT NULL, m.id FROM s_media m WHERE m.path IN (:mediaPaths)';
            $this->connection->executeQuery(
                $sql,
                [':mediaPaths' => $paths],
                [':mediaPaths' => Connection::PARAM_STR_ARRAY]
            );
        }

        // Process ids
        if (!empty($this->queue['id'])) {
            $ids = array_keys(array_flip($this->queue['id']));
            $this->connection->executeQuery(
                sprintf('INSERT INTO s_media_used (mediaId) VALUES (%s)', implode('),(', $ids))
            );
        }
    }

    /**
     * @return array
     */
    private function fetchColumn(MediaPosition $mediaPosition)
    {
        return $this->connection
            ->createQueryBuilder()
            ->select($mediaPosition->getSourceColumn())
            ->from($mediaPosition->getSourceTable())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
