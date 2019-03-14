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

use Doctrine\DBAL\Connection;

class DatabaseWriter
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * @var bool
     */
    private $update;

    /**
     * Whether or not overwrite dirty snippets
     *
     * @var bool
     */
    private $force;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->update = true;

        $this->force = false;
    }

    /**
     * @param array  $data
     * @param string $namespace
     * @param int    $localeId
     * @param int    $shopId
     *
     * @throws \Exception
     */
    public function write($data, $namespace, $localeId, $shopId)
    {
        if (empty($data)) {
            throw new \Exception('You called write() but provided no data to be written');
        }

        if (!isset($this->db)) {
            throw new \Exception('Required database connection is missing');
        }

        $this->db->beginTransaction();
        try {
            // If no update are allowed, we can speed up using INSERT IGNORE
            if (!$this->update) {
                $this->insertBatch($data, $namespace, $localeId, $shopId);
            } else {
                $rows = $this->db->fetchAll(
                    'SELECT * FROM s_core_snippets WHERE shopID = :shopId AND localeID = :localeId AND namespace = :namespace',
                    [
                        'shopId' => $shopId,
                        'localeId' => $localeId,
                        'namespace' => $namespace,
                    ]
                );

                foreach ($data as $name => $value) {
                    $row = null;

                    // Find the matching value in db, if it exists
                    foreach ($rows as $key => $values) {
                        if (strtolower($values['name']) === strtolower($name)) {
                            $row = $values;
                            unset($rows[$key]);
                            break;
                        }
                    }

                    if ($row !== null) {
                        // Found a matching value, try update
                        $this->updateRecord($name, $value, $row);
                    } else {
                        // No matching value, just insert a new one
                        $this->insertRecord($name, $value, $namespace, $localeId, $shopId);
                    }
                }
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception(sprintf('An error occurred when importing namespace "%s" for locale "%s"', $namespace, $localeId), 0, $e);
        }
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

    /**
     * @param bool $force
     */
    public function setForce($force)
    {
        $this->force = $force;
    }

    /**
     * @return bool
     */
    public function getForce()
    {
        return $this->force;
    }

    /**
     * @param array  $data
     * @param string $namespace
     * @param int    $localeId
     * @param int    $shopId
     */
    private function insertBatch($data, $namespace, $localeId, $shopId)
    {
        $insertSql = 'INSERT INTO s_core_snippets (namespace, shopID, localeID, name, value, created, updated, dirty)
                      VALUES (:namespace, :shopId, :localeId, :name, :value, :created, :updated, 0)';

        $insertStmt = $this->db->prepare($insertSql);
        foreach ($data as $name => $value) {
            $insertStmt->execute(
                [
                    'namespace' => $namespace,
                    'shopId' => $shopId,
                    'localeId' => $localeId,
                    'name' => $name,
                    'value' => $value,
                    'created' => date('Y-m-d H:i:s', time()),
                    'updated' => date('Y-m-d H:i:s', time()),
                ]
            );
        }
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $namespace
     * @param int    $localeId
     * @param int    $shopId
     */
    private function insertRecord($name, $value, $namespace, $localeId, $shopId)
    {
        $queryData = [
            'namespace' => $namespace,
            'shopID' => $shopId,
            'localeID' => $localeId,
            'name' => $name,
            'value' => $value,
            'created' => date('Y-m-d H:i:s', time()),
            'updated' => date('Y-m-d H:i:s', time()),
            'dirty' => 0,
        ];

        $this->db->insert('s_core_snippets', $queryData);
    }

    /**
     * @param string $name
     * @param string $value
     * @param array  $row
     */
    private function updateRecord($name, $value, $row)
    {
        $hasSameValue = $name === $row['name'] && $value === $row['value'];

        $isDirty = $row['dirty'] == 1;

        // snippet was never touched after insert
        if (!$this->force && $hasSameValue && !$isDirty) {
            return;
        }

        // If not forced, value is dirty and columns are different, skip
        if (!$this->force && $isDirty && !$hasSameValue) {
            return;
        }

        $queryData = [
            'name' => $name,
            'value' => $value,
            'updated' => date('Y-m-d H:i:s', time()),
            'dirty' => 0,
        ];

        $this->db->update('s_core_snippets', $queryData, ['id' => $row['id']]);
    }
}
