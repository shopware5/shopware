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

namespace Shopware\Components\Snippet\Writer;

class QueryWriter
{
    /**
     * @var array
     */
    private $queries;

    /**
     * @var bool
     */
    private $update;

    public function __construct()
    {
        $this->update = true;
    }

    /**
     * @param array      $data
     * @param string     $namespace
     * @param int|string $localeId
     * @param int        $shopId
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function write($data, $namespace, $localeId, $shopId)
    {
        if (empty($data)) {
            throw new \Exception('You called write() but provided no data to be written');
        }

        if (!$this->update) {
            $this->generateInsertQueries($data, $namespace, $localeId, $shopId);

            return $this;
        }

        $this->generateUpdateInsertQueries($data, $namespace, $localeId, $shopId);

        return $this;
    }

    /**
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * @param bool $update
     */
    public function setUpdate($update)
    {
        $this->update = $update;
    }

    /**
     * @return bool
     */
    public function getUpdate()
    {
        return $this->update;
    }

    private function generateUpdateInsertQueries($data, $namespace, $localeId, $shopId)
    {
        foreach ($data as $name => $value) {
            $queryData = [
                'namespace' => '\'' . addslashes($namespace) . '\'',
                'shopID' => $shopId,
                'localeID' => $localeId,
                'name' => '\'' . addslashes($name) . '\'',
                'value' => '\'' . addslashes($value) . '\'',
                'created' => '\'' . date('Y-m-d H:i:s', time()) . '\'',
                'updated' => '\'' . date('Y-m-d H:i:s', time()) . '\'',
                'dirty' => 0,
            ];

            $updateData = [
                'updated=IF(dirty = 1, updated, \'' . date('Y-m-d H:i:s', time()) . '\')',
                'value=IF(dirty = 1, value, \'' . addslashes($value) . '\')',
                'dirty=IF(value = \'' . addslashes($value) . '\', 0, 1)',
            ];

            $this->queries[] = 'INSERT INTO s_core_snippets'
                . ' (' . implode(', ', array_keys($queryData)) . ')'
                . ' VALUES (' . implode(', ', array_values($queryData)) . ')'
                . ' ON DUPLICATE KEY UPDATE ' . implode(', ', array_values($updateData)) . ';';
        }
    }

    /**
     * @param array      $data
     * @param string     $namespace
     * @param int|string $localeId
     * @param int        $shopId
     *
     * @return $this
     */
    private function generateInsertQueries($data, $namespace, $localeId, $shopId)
    {
        $insertSql = 'INSERT IGNORE INTO s_core_snippets (namespace, shopID, localeID, name, value, created, updated, dirty) VALUES ';

        $counter = 0;
        foreach ($data as $name => $value) {
            $queryData = [
                    'namespace' => '\'' . addslashes($namespace) . '\'',
                    'shopID' => $shopId,
                    'localeID' => $localeId,
                    'name' => '\'' . addslashes($name) . '\'',
                    'value' => '\'' . addslashes($value) . '\'',
                    'created' => '\'' . date('Y-m-d H:i:s', time()) . '\'',
                    'updated' => '\'' . date('Y-m-d H:i:s', time()) . '\'',
                    'dirty' => 0,
            ];

            $values[] = '(' . implode(', ', array_values($queryData)) . ')';
            if (++$counter % 50 == 0) {
                $this->queries[] = $insertSql . implode(', ', $values) . ';';
                $values = [];
            }
        }

        if (!empty($values)) {
            $this->queries[] = $insertSql . implode(', ', $values) . ';';
        }

        return $this;
    }
}
