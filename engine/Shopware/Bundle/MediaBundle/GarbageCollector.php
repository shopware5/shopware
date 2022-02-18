<?php

declare(strict_types=1);
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
use Shopware\Models\Media\Album;

class GarbageCollector
{
    /**
     * @var MediaPosition[]
     */
    private array $mediaPositions;

    private Connection $connection;

    private MediaServiceInterface $mediaService;

    /**
     * @var array{id: array<int>, path: array<string>}
     */
    private array $queue = [
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

        return \count($this->mediaPositions);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return (int) $this->connection->createQueryBuilder()
            ->select('count(*) as cnt')
            ->from('s_media')
            ->where('albumID = :trashAlbumId')
            ->setParameter('trashAlbumId', Album::ALBUM_GARBAGE)
            ->execute()
            ->fetchOne();
    }

    private function createTempTable(): void
    {
        $this->connection->executeStatement('CREATE TEMPORARY TABLE IF NOT EXISTS s_media_used (id int auto_increment, mediaId int NOT NULL, PRIMARY KEY pkid (id), INDEX media (mediaId))');
    }

    private function moveToTrash(): void
    {
        $sql = <<<'SQL'
UPDATE s_media media
LEFT JOIN s_media_used used_media
    ON used_media.mediaId = media.id
LEFT JOIN s_media_album album
    ON media.albumID = album.id
SET albumID = ?
WHERE album.garbage_collectable = 1
    AND used_media.id IS NULL;
SQL;
        $this->connection->executeStatement($sql, [Album::ALBUM_GARBAGE]);
    }

    private function find(MediaPosition $mediaPosition): void
    {
        switch ($mediaPosition->getParseType()) {
            case MediaPosition::PARSE_JSON:
                $this->handleTablesWithJsonContent($mediaPosition);
                break;

            case MediaPosition::PARSE_SERIALIZE:
                $this->handleTablesWithSerializedContent($mediaPosition);
                break;

            case MediaPosition::PARSE_HTML:
                $this->handleTablesWithHtmlContent($mediaPosition);
                break;

            case MediaPosition::PARSE_PIPES:
                $this->handleTablesWithIdsSeparatedByPipes($mediaPosition);
                break;

            default:
                $this->handleTable($mediaPosition);
        }
    }

    private function handleTablesWithJsonContent(MediaPosition $mediaPosition): void
    {
        foreach ($this->fetchColumn($mediaPosition) as $row) {
            if (!\is_string($row)) {
                continue;
            }

            $jsonValues = json_decode($row);

            if (empty($jsonValues)) {
                continue;
            }

            if (\is_array($jsonValues)) {
                foreach ($jsonValues as $value) {
                    if (isset($value->mediaId)) {
                        $this->addMediaByIdToUsedTable((int) $value->mediaId);
                    } elseif (isset($value->path)) {
                        $this->addMediaByPathToUsedTable($value->path);
                    }
                }
            } elseif (\is_object($jsonValues)) {
                if (isset($jsonValues->mediaId)) {
                    $this->addMediaByIdToUsedTable((int) $jsonValues->mediaId);
                } elseif (isset($jsonValues->path)) {
                    $this->addMediaByPathToUsedTable($jsonValues->path);
                }
            }
        }
    }

    private function handleTablesWithSerializedContent(MediaPosition $mediaPosition): void
    {
        foreach ($this->fetchColumn($mediaPosition) as $value) {
            $value = unserialize($value, ['allowed_classes' => false]);
            if (\is_string($value)) {
                $this->addMediaByPathToUsedTable($value);
            }
        }
    }

    private function handleTablesWithHtmlContent(MediaPosition $mediaPosition): void
    {
        foreach ($this->fetchColumn($mediaPosition) as $value) {
            if (!\is_string($value)) {
                continue;
            }

            // Media path matches
            preg_match_all("/{{1}media[\s+]?path=[\"'](?'mediaTag'\S*)[\"']}{1}/mi", $value, $mediaMatches);
            // Src tag matches
            preg_match_all("/<?img[^<]*src=[\"'](?'srcTag'[^{]*?)[\"'][^>]*\/?>?/mi", $value, $srcMatches);
            // Link matches
            preg_match_all("/<?a[^<]*href=[\"'](?'hrefTag'[^{]*?)[\"'][^>]*\/?>?/mi", $value, $hrefMatches);

            if ($mediaMatches['mediaTag']) {
                foreach ($mediaMatches['mediaTag'] as $match) {
                    $match = $this->mediaService->normalize($match);
                    $this->addMediaByPathToUsedTable($match);
                }
            }

            if ($srcMatches['srcTag']) {
                foreach ($srcMatches['srcTag'] as $match) {
                    $match = $this->mediaService->normalize($match);
                    $this->addMediaByPathToUsedTable($match);
                }
            }

            if ($hrefMatches['hrefTag']) {
                foreach ($hrefMatches['hrefTag'] as $match) {
                    $match = $this->mediaService->normalize($match);

                    // Only add normalized media links and not arbitrary links
                    if (str_starts_with($match, 'media/')) {
                        $this->addMediaByPathToUsedTable($match);
                    }
                }
            }
        }
    }

    private function handleTablesWithIdsSeparatedByPipes(MediaPosition $mediaPosition): void
    {
        foreach ($this->fetchColumn($mediaPosition) as $value) {
            foreach (array_filter(explode('|', $value)) as $id) {
                $this->addMediaByIdToUsedTable((int) $id);
            }
        }
    }

    private function handleTable(MediaPosition $mediaPosition): void
    {
        $sql = sprintf(
            'INSERT INTO s_media_used
                SELECT DISTINCT NULL, media.id
                FROM s_media media
                INNER JOIN %1$s
                    ON %1$s.%2$s = media.%3$s',
            $mediaPosition->getSourceTable(),
            $mediaPosition->getSourceColumn(),
            $mediaPosition->getMediaColumn()
        );

        $this->connection->executeStatement($sql);
    }

    private function addMediaByPathToUsedTable(string $path): void
    {
        $path = $this->mediaService->normalize($path);
        $this->queue['path'][] = $path;
    }

    private function addMediaByIdToUsedTable(int $mediaId): void
    {
        $this->queue['id'][] = $mediaId;
    }

    private function processQueue(): void
    {
        // Process paths
        if (!empty($this->queue['path'])) {
            $paths = array_unique($this->queue['path']);
            $sql = 'INSERT INTO s_media_used SELECT DISTINCT NULL, media.id FROM s_media media WHERE media.path IN (:mediaPaths)';
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
     * @return array<int, mixed>
     */
    private function fetchColumn(MediaPosition $mediaPosition): array
    {
        return $this->connection
            ->createQueryBuilder()
            ->select($mediaPosition->getSourceColumn())
            ->from($mediaPosition->getSourceTable())
            ->execute()
            ->fetchFirstColumn();
    }
}
