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

namespace Shopware\Bundle\ContentTypeBundle\Services;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\TypeReader;
use Shopware\Bundle\ContentTypeBundle\Field\FieldInterface;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;

class DatabaseContentTypeSynchronizer implements DatabaseContentTypeSynchronizerInterface
{
    /**
     * @var SynchronizerServiceInterface
     */
    private $synchronizerService;

    /**
     * @var array
     */
    private $fieldAlias;

    /**
     * @var array
     */
    private $pluginFolders;

    /**
     * @var TypeBuilder
     */
    private $typeBuilder;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContentTypeCleanupServiceInterface
     */
    private $cleanupService;

    /**
     * @param FieldInterface[] $fieldAlias
     * @param string[]         $pluginFolders
     */
    public function __construct(
        array $fieldAlias,
        array $pluginFolders,
        TypeBuilder $typeBuilder,
        Connection $connection,
        SynchronizerServiceInterface $synchronizerService,
        ContentTypeCleanupServiceInterface $cleanupService
    ) {
        $this->fieldAlias = $fieldAlias;
        $this->pluginFolders = $pluginFolders;
        $this->typeBuilder = $typeBuilder;
        $this->connection = $connection;
        $this->synchronizerService = $synchronizerService;
        $this->cleanupService = $cleanupService;
    }

    public function sync(array $installedPlugins, bool $destructive = false): array
    {
        $types = (new TypeReader())->getTypes($installedPlugins, $this->pluginFolders, $this->fieldAlias);
        $typeProvider = new TypeProvider($types, $this->typeBuilder);
        $types = $typeProvider->getTypes();

        $this->updateContentTypesTable($types);
        $this->cleanup($types);

        $this->synchronizerService->setTypeProvider($typeProvider);

        return $this->synchronizerService->sync($destructive);
    }

    private function updateContentTypesTable(array $types): void
    {
        $dbal = $this->connection;

        /** @var Type $type */
        foreach ($types as $type) {
            $id = $dbal->fetchColumn('SELECT id FROM s_content_types WHERE internalName = ?', [$type->getInternalName()]);
            $update = [
                'internalName' => $type->getInternalName(),
                'name' => $type->getName(),
                'source' => $type->getSource(),
                'config' => json_encode($type),
            ];

            if ($id) {
                $dbal->update('s_content_types', $update, ['id' => $id]);
            } else {
                $dbal->insert('s_content_types', $update);
            }
        }
    }

    private function cleanup(array $types): void
    {
        $types = array_map(static function (Type $type) {
            return $type->getInternalName();
        }, $types);

        if (!empty($types)) {
            $names = $this->connection->executeQuery('SELECT internalName FROM s_content_types WHERE source IS NOT NULL AND internalName NOT IN(:names)', [
                'names' => $types,
            ], [
                'names' => Connection::PARAM_STR_ARRAY,
            ])->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($names as $name) {
                $this->cleanupService->deleteContentType($name);
            }

            $this->connection->executeQuery('DELETE FROM s_content_types WHERE source IS NOT NULL AND internalName NOT IN(:names)', [
                'names' => $types,
            ], [
                'names' => Connection::PARAM_STR_ARRAY,
            ]);
        } else {
            $this->connection->executeQuery('DELETE FROM s_content_types WHERE source IS NOT NULL');
        }
    }
}
