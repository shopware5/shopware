<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
     * @var boolean
     */
    private $update;

    /**
     * @var boolean
     */
    private $force;

    /**
     * @var boolean
     */
    private $allowReset;

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->update = true;
        $this->force = false;
        $this->allowReset = false;
    }

    public function write($data, $namespace, $localeId, $shopId)
    {
        if (empty($data)) {
            throw new \Exception('You called write() but provided no data to be written');
        }

        if (!isset($this->db)) {
            throw new \Exception('Required database connection is missing');
        }

        // If no update are allowed, we can speed up using INSERT IGNORE
        if (!$this->update) {
            $this->db->beginTransaction();
            try {
                foreach ($data as $name => $value) {
                    $queryData = array(
                        'namespace' => $namespace,
                        'shopID' => $shopId,
                        'localeID' => $localeId,
                        'name' => $name,
                        'value' => $value,
                        'created' => date('Y-m-d H:i:s', time()),
                        'updated' => date('Y-m-d H:i:s', time()),
                        'dirty' => 0
                    );

                    $query = 'INSERT IGNORE INTO s_core_snippets'
                        . ' (' . implode(', ', array_keys($queryData)) . ')'
                        . ' VALUES (' . implode(', ', array_fill(0, count($queryData), '?')) . ')';

                    $this->db->executeUpdate($query, array_values($queryData));
                }
                $this->db->commit();
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw new \Exception(sprintf('An error occurred when importing namespace "%s" for locale "%s"', $namespace, $localeId), 0, $e);
            }
        } else {
            $rows = $this->db->fetchAll(sprintf(
                'SELECT * FROM s_core_snippets WHERE shopID = %s AND localeID = %s AND namespace = \'%s\'',
                $shopId, $localeId, $namespace)
            );

            $this->db->beginTransaction();
            try {
                foreach ($data as $name => $value) {
                    $row = null;

                    // Find the matching value in db, if it exists
                    foreach ($rows as $key => $values) {
                        if ($values['name'] == $name) {
                            $row = $values;
                            unset($rows[$key]);
                            break;
                        }
                    }

                    if ($row !== null) {
                        // Found a matching value, try update

                        // If not forced, value is dirty and columns are different, skip
                        if (!$this->force && $row['dirty'] == 1 && (!$this->allowReset || $row['value'] != $value)) {
                            continue;
                        }

                        // If values are the same and they are not dirty or not allowReset, skip
                        if ((!$this->allowReset || $row['dirty'] == 0) && $row['value'] == $value && !$this->force) {
                            continue;
                        }

                        $queryData = array(
                            'value' => $value,
                            'updated' => date('Y-m-d H:i:s', time()),
                            'dirty' => 0
                        );

                        if ($this->allowReset && $row['value'] == $value) {
                            $queryData['dirty'] = 0;
                        }

                        $this->db->update('s_core_snippets', $queryData, array('id' => $row['id']));
                    } else {
                        // No matching value, just insert a new one
                        $queryData = array(
                            'namespace' => $namespace,
                            'shopID' => $shopId,
                            'localeID' => $localeId,
                            'name' => $name,
                            'value' => $value,
                            'created' => date('Y-m-d H:i:s', time()),
                            'updated' => date('Y-m-d H:i:s', time()),
                            'dirty' => 0
                        );

                        $query = 'INSERT IGNORE INTO s_core_snippets'
                            . ' (' . implode(', ', array_keys($queryData)) . ')'
                            . ' VALUES (' . implode(', ', array_fill(0, count($queryData), '?')) . ')';

                        $this->db->executeUpdate($query, array_values($queryData));
                    }
                }
                $this->db->commit();
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw new \Exception(sprintf('An error occurred when importing namespace "%s" for locale "%s"', $namespace, $localeId), 0, $e);
            }
        }
    }

    /**
     * @param boolean $update
     */
    public function setUpdate($update)
    {
        $this->update = $update;
    }

    /**
     * @return boolean
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @param boolean $allowReset
     */
    public function setAllowReset($allowReset)
    {
        $this->allowReset = $allowReset;
    }

    /**
     * @return boolean
     */
    public function getAllowReset()
    {
        return $this->allowReset;
    }

    /**
     * @param boolean $force
     */
    public function setForce($force)
    {
        $this->force = $force;
    }

    /**
     * @return boolean
     */
    public function getForce()
    {
        return $this->force;
    }
}
