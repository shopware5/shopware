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

namespace Shopware\Bundle\SearchBundleDBAL\SearchTerm;

use Doctrine\DBAL\Connection;
use Shopware\Components\MemoryLimit;

class SearchIndexer implements SearchIndexerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var TermHelperInterface
     */
    private $termHelper;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param int $batchSize
     */
    public function __construct(
        \Shopware_Components_Config $config,
        Connection $connection,
        TermHelperInterface $termHelper,
        $batchSize = 4000
    ) {
        $this->config = $config;
        $this->connection = $connection;
        $this->termHelper = $termHelper;
        $this->batchSize = $batchSize > 0 ? $batchSize : 4000;
    }

    /**
     * Check if search index is valid anymore and rebuild if necessary
     */
    public function validate()
    {
        $strategy = $this->config->get('searchRefreshStrategy', 3);

        // Search index refresh strategy is configured for "live refresh"?
        if ($strategy !== 3) {
            return;
        }

        $interval = (int) $this->config->get('cacheSearch');

        if (empty($interval) || $interval < 360) {
            $interval = 86400;
        }

        $sql = "
            SELECT NOW() AS current, cf.value AS last, (SELECT 1 FROM s_search_index LIMIT 1) AS not_force
            FROM s_core_config_elements ce, s_core_config_values cf
            WHERE ce.name = 'fuzzysearchlastupdate'
            AND cf.element_id = ce.id
            AND cf.shop_id = 1
        ";
        $result = $this->connection->fetchAll($sql);

        if (empty($result) || !isset($result[0])) {
            $this->build();

            return;
        }

        $result = $result[0];

        $last = !empty($result['last']) ? unserialize($result['last'], ['allowed_classes' => false]) : null;

        if (empty($last) || empty($result['not_force']) || strtotime($last) < strtotime($result['current']) - $interval) {
            $this->build();
        }
    }

    /**
     * Rebuilds the search index for the shopware default search query builder.
     */
    public function build()
    {
        MemoryLimit::setMinimumMemoryLimit(1024 * 1024 * 512);
        @set_time_limit(0);

        $this->setNextUpdateTimestamp();

        // Truncate search index table
        $this->connection->executeUpdate('TRUNCATE TABLE `s_search_index`');

        // Get a list of all tables and columns in this tables that should be processed by search
        /**
         * Example return:
         * tableID | table      | where  | reference_table       | fieldIDs | fields                    | foreign_key
         * 1       | s_articles | NULL   | NULL                  | 3,4      | name, keywords            | NULL
         * 2       | s_categories | NULL | s_articles_categories | 1,2      | metakeywords, description | categoryID
         */
        $tables = $this->getSearchTables();

        if (!empty($tables)) {
            foreach ($tables as $table) {
                // Set primary key
                $table['elementID'] = empty($table['foreign_key']) && $table['table'] !== 's_articles' ? 'articleID' : 'id';

                if ($table['table'] === 's_articles_attributes') {
                    $table['elementID'] = '(SELECT articleID FROM s_articles_details WHERE id = articledetailsID LIMIT 1)';
                }

                // Build sql query to fetch values from this table
                $sql = 'SELECT ' . $table['elementID'] . ' as id, ' . $table['fields'] . ' FROM ' . $table['table'];

                // If any where condition is set, add to query
                if (!empty($table['where'])) {
                    $sql .= ' WHERE ' . $table['where'];
                }

                // Get all fields & values from current table
                $getTableKeywords = $this->connection->fetchAll($sql);

                if ($table['table'] === 's_categories') {
                    $getTableKeywords = $this->mapCategoryKeywords($getTableKeywords);
                }

                // If no result, return
                if (empty($getTableKeywords)) {
                    continue;
                }

                // Build array from columns fieldIDs, fields and do_not_split
                $fieldIds = explode(', ', $table['fieldIDs']);
                $fieldNames = explode(', ', $table['fields']);
                $doNotSplits = explode(', ', $table['doNotSplit']);
                $fields = [];
                foreach ($fieldIds as $key => $fieldId) {
                    $fields[$fieldId] = ['fieldName' => $fieldNames[$key], 'doNotSplit' => $doNotSplits[$key]];
                }

                $keywords = [];
                $sqlIndex = [];

                // Go through every row of result
                foreach ($getTableKeywords as $currentRow => $row) {
                    if ($row['id'] === null) {
                        continue;
                    }

                    // Go through every column of result
                    foreach ($fields as $fieldID => $field) {
                        $field_keywords = [$row[$field['fieldName']]];

                        if (!(bool) $field['doNotSplit']) {
                            // Split string from column into keywords
                            $field_keywords = $this->termHelper->splitTerm($row[$field['fieldName']]);
                        }

                        if (empty($field_keywords)) {
                            continue;
                        }

                        foreach ($field_keywords as &$keyword) {
                            $keyword = $this->connection->quote($keyword);
                            $keywords[] = $keyword;
                        }
                        unset($keyword);

                        // SQL-queries to fill s_search_index
                        $sqlIndex[] = 'SELECT sk.id as keywordID, ' . $row['id'] . ' as elementID, ' . $fieldID . ' as fieldID '
                            . 'FROM s_search_keywords sk '
                            . 'WHERE sk.keyword IN (' . implode(', ', $field_keywords) . ')';
                    }

                    // If no new keywords were found, proceed with next table
                    if (empty($keywords)) {
                        continue;
                    }

                    // If last row or more then 5000 keywords fetched, write results to index
                    if ($currentRow == count($getTableKeywords) - 1 || count($keywords) > $this->batchSize) {
                        $keywords = array_unique($keywords); // Remove duplicates
                        $sql_keywords = 'INSERT IGNORE INTO `s_search_keywords` (`keyword`) VALUES';
                        $sql_keywords .= ' (' . implode('), (', $keywords) . ')';

                        // Insert Keywords
                        $this->connection->executeUpdate($sql_keywords);

                        $keywords = [];

                        // Update index
                        $sqlIndex = implode("\n\nUNION ALL\n\n", $sqlIndex);
                        $sqlIndex = "INSERT IGNORE INTO s_search_index (keywordID, elementID, fieldID)\n\n" . $sqlIndex;

                        $this->connection->executeUpdate($sqlIndex);
                        $sqlIndex = [];
                    }
                }
            }
        }

        $this->cleanupIndex();

        $this->cleanupKeywords();
    }

    /**
     * Updates the last update timestamp in the database
     */
    private function setNextUpdateTimestamp()
    {
        $sql = '
            SET @parent = (SELECT id FROM s_core_config_elements WHERE name = \'fuzzysearchlastupdate\');
            DELETE FROM `s_core_config_values` WHERE element_id = @parent;
            INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES
            (@parent, 1, CONCAT(\'s:\', LENGTH(NOW()), \':"\', NOW(), \'";\'));
        ';
        $this->connection->executeUpdate($sql);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function cleanupIndex()
    {
        $tables = $this->getSearchTables();

        $sql_join = '';
        foreach ($tables as $table) {
            if (empty($table['foreign_key'])) {
                continue;
            }
            if (empty($table['referenz_table'])) {
                $table['referenz_table'] = 's_articles';
            }
            $sql_join .= "
                LEFT JOIN {$table['referenz_table']} t{$table['tableID']}
                ON si.elementID=t{$table['tableID']}.{$table['foreign_key']}
                AND si.fieldID IN ({$table['fieldIDs']})
            ";
        }

        $sql = "
            SELECT STRAIGHT_JOIN
                   keywordID, fieldID, sk.keyword
            FROM `s_search_index` si

            INNER JOIN s_search_keywords sk
            ON si.keywordID=sk.id

            $sql_join

            GROUP BY keywordID, fieldID
            HAVING COUNT(*) > (SELECT COUNT(*)*0.9 FROM `s_articles`)
        ";

        $collectToDelete = $this->connection->fetchAll($sql);
        foreach ($collectToDelete as $delete) {
            $sql = '
                DELETE FROM s_search_index
                WHERE keywordID=? AND fieldID=?
            ';
            $this->connection->executeUpdate($sql, [$delete['keywordID'], $delete['fieldID']]);
        }
    }

    /**
     * Cleanups search keywords in the database.
     */
    private function cleanupKeywords()
    {
        $sql = '
            DELETE sk FROM `s_search_keywords` sk
            LEFT JOIN s_search_index si
            ON sk.id=si.keywordID
            WHERE si.keywordID IS NULL
        ';
        $this->connection->executeUpdate($sql);
    }

    /**
     * Get all tables and columns that might be involved in this search request as an array
     *
     * @return array
     */
    private function getSearchTables()
    {
        return $this->connection->fetchAll("
            SELECT STRAIGHT_JOIN
                st.id AS tableID,
                st.table,
                st.where,
                st.referenz_table, 
                st.foreign_key,
                GROUP_CONCAT(sf.id SEPARATOR ', ') AS fieldIDs,
                GROUP_CONCAT(sf.field SEPARATOR ', ') AS `fields`,
                GROUP_CONCAT(sf.do_not_split SEPARATOR ', ') AS `doNotSplit`
            FROM s_search_fields sf FORCE INDEX (tableID)
                INNER JOIN s_search_tables st
                    ON st.id = sf.tableID
                    AND sf.relevance != 0
            GROUP BY sf.tableID
       ");
    }

    /**
     * @return array
     */
    private function mapCategoryKeywords(array $keywords)
    {
        $ids = array_column($keywords, 'id');

        $translations = $this->connection->createQueryBuilder()
            ->select(['objectkey', 'objectdata'])
            ->from('s_core_translations')
            ->where('objectkey IN (:ids)')
            ->andWhere('objecttype = :type')
            ->setParameter(':type', 'category')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);

        $mapping = [];
        foreach ($keywords as $keyword) {
            $mapping[] = $keyword;
            $id = $keyword['id'];

            if (!isset($translations[$id])) {
                continue;
            }

            unset($keyword['id']);
            $keywords = array_keys($keyword);
            $field = array_pop($keywords);

            $categoryTranslations = $translations[$id];
            foreach ($categoryTranslations as $translation) {
                $translation = unserialize($translation, ['allowed_classes' => false]);
                $mapping[] = ['id' => $id, $field => $translation[$field]];
            }
        }

        return $mapping;
    }
}
