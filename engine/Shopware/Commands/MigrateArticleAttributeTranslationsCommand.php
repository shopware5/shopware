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

namespace Shopware\Commands;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Migrates article attribute translations from Shopware 5.1 to Shopware 5.2.
 */
class MigrateArticleAttributeTranslationsCommand extends ShopwareCommand
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    private $updateStatement;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:migrate:article:attribute:translations');
        $this->setDescription('Migrates product attribute translations from Shopware 5.1 to Shopware 5.2');
        $this->setHelp('The <info>%command.name%</info> migrates article attribute translations from Shopware 5.1 to Shopware 5.2.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->connection = $this->container->get('dbal_connection');
        $this->updateStatement = $this->connection->prepare('UPDATE s_core_translations SET objectdata=:data WHERE id=:id');
        $this->columns = $this->getColumns();

        $result = $this->connection
            ->query('SELECT MAX(id) AS maxId, count(1) AS count FROM s_core_translations;')
            ->fetch(\PDO::FETCH_ASSOC);

        $this->migrate($io->createProgressBar($result['count']), $result['maxId']);
    }

    /**
     * @param int $maxId
     */
    private function migrate(ProgressBar $progressBar, $maxId)
    {
        $selectStatement = $this->connection->prepare(
<<<'EOL'
SELECT id, objectdata
FROM s_core_translations
WHERE objecttype IN ("article", "variantMain", "variant")
AND id > :lastId
AND id <= :maxId
ORDER BY id ASC
LIMIT 250
EOL
        );

        $lastId = 0;
        $progressBar->start();

        do {
            $selectStatement->execute([':lastId' => $lastId, ':maxId' => $maxId]);
            $rows = $selectStatement->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                continue;
            }

            $this->migrateTranslations($rows);
            $progressBar->advance(count($rows));

            $lastId = array_pop($rows)['id'];
        } while (!empty($rows));

        $progressBar->finish();
    }

    /**
     * @return string[]
     */
    private function getColumns()
    {
        $columns = $this->connection->query('SHOW COLUMNS FROM s_articles_attributes')->fetchAll(\PDO::FETCH_ASSOC);

        $columns = array_column($columns, 'Field');

        $mapping = [];
        foreach ($columns as $column) {
            $mapping[$column] = $column;
            $camelCase = $this->underscoreToCamelCase($column);
            $mapping[$camelCase] = $column;
        }

        return $mapping;
    }

    /**
     * @param string $underscored
     *
     * @return string
     */
    private function underscoreToCamelCase($underscored)
    {
        return preg_replace_callback('/_([a-zA-Z])/', function ($c) {
            return strtoupper($c[1]);
        }, $underscored);
    }

    /**
     * @param array $data
     *
     * @return array|null
     */
    private function filter($data, array $columns)
    {
        if (!is_array($data)) {
            return null;
        }

        $updated = false;
        foreach ($columns as $key => $column) {
            if (array_key_exists($key, $data)) {
                $newKey = CrudService::EXT_JS_PREFIX . $column;

                if (!array_key_exists($newKey, $data)) {
                    $data[$newKey] = $data[$key];
                    $updated = true;
                }
            }
        }

        if (!$updated) {
            return null;
        }

        return $data;
    }

    /**
     * @param array[]  $rows
     * @param string[] $columns
     *
     * @return string[] indexed by translation id
     */
    private function getUpdatedTranslations($rows, $columns)
    {
        $values = [];

        foreach ($rows as $row) {
            try {
                $updated = $this->filter(unserialize($row['objectdata'], ['allowed_classes' => false]), $columns);
            } catch (\Exception $e) {
                //serialize error - continue with next translation
                continue;
            }

            if ($updated === null) {
                continue;
            }

            $values[$row['id']] = serialize($updated);
        }

        return $values;
    }

    /**
     * @param array $rows
     */
    private function migrateTranslations($rows)
    {
        $values = $this->getUpdatedTranslations($rows, $this->columns);
        foreach ($values as $id => $objectData) {
            $this->updateStatement->execute([':id' => $id, ':data' => $objectData]);
        }
    }
}
