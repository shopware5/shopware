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

use Doctrine\DBAL\Schema\Comparator;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;
use Shopware\Components\Model\ModelManager;

class SynchronizerService implements SynchronizerServiceInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var TypeProvider
     */
    private $provider;

    /**
     * @var MenuSynchronizerInterface
     */
    private $menuSynchronizer;

    /**
     * @var AclSynchronizerInterface
     */
    private $aclSynchronizer;

    public function __construct(
        ModelManager $modelManager,
        TypeProvider $provider,
        MenuSynchronizerInterface $menuSynchronizer,
        AclSynchronizerInterface $aclSynchronizer
    ) {
        $this->modelManager = $modelManager;
        $this->provider = $provider;
        $this->menuSynchronizer = $menuSynchronizer;
        $this->aclSynchronizer = $aclSynchronizer;
    }

    public function sync(bool $destructive = false): array
    {
        $types = $this->provider->getTypes();

        $this->menuSynchronizer->synchronize(self::getMenuEntries($types));
        $this->createTables($types, $destructive);
        $this->aclSynchronizer->update(array_map(static function (Type $type) {
            return strtolower($type->getControllerName());
        }, $types));

        return $types;
    }

    public function setTypeProvider(TypeProvider $typeProvider): void
    {
        $this->provider = $typeProvider;
    }

    private static function getMenuEntries(array $types): array
    {
        $menu = [];

        /** @var Type $type */
        foreach ($types as $type) {
            $menu[] = [
                'name' => $type->getName(),
                'parent' => [
                    'controller' => $type->getMenuParent(),
                ],
                'label' => [
                    'en' => $type->getName(),
                ],
                'controller' => $type->getControllerName(),
                'action' => 'index',
                'active' => true,
                'class' => $type->getMenuIcon(),
                'position' => $type->getMenuPosition(),
                'isRootMenu' => false,
                'contentType' => $type->getInternalName(),
            ];
        }

        return $menu;
    }

    private function createTables(array $types, bool $destructive): void
    {
        $con = $this->modelManager->getConnection();
        $currentSchema = $con->getSchemaManager()->createSchema();
        $schema = clone $currentSchema;

        // Mark all content type to be deleted, hopefully they will be redefined in the next step
        foreach ($schema->getTables() as $table) {
            if (strpos($table->getName(), 's_custom_') === 0) {
                $schema->dropTable($table->getName());
            }
        }

        // Create all tables
        foreach ($types as $type) {
            $myTable = $schema->createTable($type->getTableName());
            $myTable->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $myTable->setPrimaryKey(['id']);

            /** @var Field $field */
            foreach ($type->getFields() as $field) {
                $myTable->addColumn($field->getName(), $field->getType()::getDbalType(), ['notnull' => $field->isRequired()]);
            }

            $myTable->addColumn('created_at', 'datetime', []);
            $myTable->addColumn('updated_at', 'datetime', []);
        }

        $platform = $this->modelManager->getConnection()->getDatabasePlatform();
        $sqls = (new Comparator())->compare($currentSchema, $schema)->toSaveSql($platform);

        if ($destructive) {
            $sqls = (new Comparator())->compare($currentSchema, $schema)->toSql($platform);
        }

        foreach ($sqls as $sql) {
            $con->executeQuery($sql);
        }
    }
}
