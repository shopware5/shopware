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

namespace Shopware\Components\MultiEdit\Resource\Product;

/**
 * The backup class creates and loads backups
 */
class Backup
{
    /**
     * @var DqlHelper
     */
    protected $dqlHelper;

    /**
     * @var \Shopware_Components_Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $affectedTables = [];

    /**
     * @var string
     */
    protected $outputPath;

    /**
     * @var string
     */
    protected $backupPath;

    /**
     * @var string
     */
    protected $backupBaseName = 'me-backup-';

    /**
     * @param DqlHelper                   $dqlHelper
     * @param \Shopware_Components_Config $config
     *
     * @throws \RuntimeException
     */
    public function __construct($dqlHelper, $config)
    {
        $this->dqlHelper = $dqlHelper;
        $this->config = $config;

        $this->setupBackupDir();
    }

    /**
     * @return DqlHelper
     */
    public function getDqlHelper()
    {
        return $this->dqlHelper;
    }

    /**
     * @return \Shopware_Components_Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Make sure a valid backup dir is available
     *
     * @throws \RuntimeException
     */
    public function setupBackupDir()
    {
        $projectDir = Shopware()->Container()->getParameter('shopware.app.rootdir');
        $this->backupPath = $projectDir . 'files/backup/multi_edit';
        $this->backupPath = rtrim($this->backupPath, '/\\') . '/';

        if (!is_dir($this->backupPath)) {
            // Create directory
            if (!is_dir($this->backupPath)) {
                mkdir($this->backupPath, 0777, true);

                // Fix chmod - creating directories recursively with permissions does not seem
                // to work in some cases
                chmod($this->backupPath, 0777);
            }

            if (!is_dir($this->backupPath)) {
                throw new \RuntimeException(sprintf('Could not find nor create "%s"', $this->backupPath));
            }
        }
    }

    /**
     * Returns a list of backup files
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset, $limit)
    {
        /** @var \Doctrine\ORM\Query $query */
        $query = $this->getDqlHelper()->getEntityManager()->getRepository(\Shopware\Models\MultiEdit\Backup::class)->getBackupListQuery($offset, $limit);
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        /** @var \Doctrine\ORM\Tools\Pagination\Paginator $paginator */
        $paginator = Shopware()->Models()->createPaginator($query);
        $totalCount = $paginator->count();

        $backups = $paginator->getIterator()->getArrayCopy();

        return [
            'totalCount' => $totalCount,
            'data' => $backups,
        ];
    }

    /**
     * Will create a backup for $detailIds. The columns and tables to backup will be generated from $operations
     * Depending on $newBackup a existing file will be appended or overwritten. The name of the backup is chosen
     * depending on $id.
     *
     * @param int[] $detailIds
     * @param array $operations
     * @param bool  $newBackup
     * @param int   $id
     */
    public function create($detailIds, $operations, $newBackup, $id)
    {
        // When backups are disabled, return
        if (!$this->getConfig()->getByNamespace('SwagMultiEdit', 'enableBackup', true)) {
            return;
        }

        $name = $this->backupBaseName . $id;

        $this->buildAffectedTableArray($operations);
        $this->outputPath = $this->getOutputPath($name);

        // Dump every single affected table into a own file
        foreach ($this->getAffectedTables() as $table) {
            $ids = $this->getDqlHelper()->getIdForForeignEntity($this->getPrefixFromTable($table), $detailIds);
            $this->dumpTable($table, $name, $ids, $newBackup);
        }
    }

    /**
     * Finish a backup - compresses it and creates a model for the backup.
     *
     * @param string $filterString
     * @param array  $operations
     * @param int    $items
     * @param int    $id
     */
    public function finishBackup($filterString, $operations, $items, $id)
    {
        // When backups are disabled, return
        if (!$this->getConfig()->getByNamespace('SwagMultiEdit', 'enableBackup', true)) {
            return;
        }

        $name = $this->backupBaseName . $id;

        // Create a zip archive at last
        $result = $this->compressBackup($name);

        if ($result) {
            $this->saveBackup($result, $filterString, $operations, $items);
        }

        try {
            $this->deleteAbandonedBackups();
        } catch (\Exception $e) {
            // If an error occurs during cleanup, we do not need to cancel the process
        }
    }

    /**
     * Restores a backup from zip archive. Will only run one sql file per query
     *
     * @param int $id
     * @param int $offset
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function restore($id, $offset = 0)
    {
        $entityManager = $this->getDqlHelper()->getEntityManager();
        /** @var \Shopware\Models\MultiEdit\Backup|null $backup */
        $backup = $entityManager->find(\Shopware\Models\MultiEdit\Backup::class, $id);

        if (!$backup) {
            throw new \RuntimeException(sprintf('Backup by id %d not found', $id));
        }

        $path = $backup->getPath();
        $dir = dirname($path);

        if ($offset === 0) {
            $zip = new \ZipArchive();
            $zip->open($path);
            $success = $zip->extractTo($dir);
            if (!$success) {
                throw new \RuntimeException(sprintf('Could not extract %s to %s', $path, $dir));
            }
            $zip->close();
        }

        // Get list of data sql files
        $dataFiles = $this->getDirectoryList($dir . '/', ['datasql']);
        $numFiles = null;

        if (!empty($dataFiles)) {
            $tables = [];

            // Group by table
            foreach ($dataFiles as $file) {
                // securely remove extensions
                $table = preg_replace('/\.[0-9a-zA-Z]+\.datasql$/', '', basename($file));
                $tables[$table][] = $file;
            }

            // Get one table and one data sql file
            $keys = array_keys($tables);
            $table = array_pop($keys);
            $dataPath = array_pop($tables[$table]);
            $headerPath = $dir . '/' . $table . '.headersql';
            $footerPath = $dir . '/' . $table . '.footersql';

            // Insert
            $query = file_get_contents($headerPath) . file_get_contents($dataPath) . file_get_contents($footerPath);
            $this->getDqlHelper()->getDb()->exec($query);

            $numFiles = count($dataFiles);

            unlink($dataPath);
        }

        // When done, delete the extracted files again
        if (empty($dataFiles) || $numFiles == 1) {
            $numFiles = 1;

            $files = $this->getDirectoryList($dir . '/');

            foreach ($files as $file) {
                unlink($file);
            }
        }

        return [
            'totalCount' => $numFiles + $offset,
            'offset' => $offset + 1,
            'done' => $numFiles == 1,
        ];
    }

    /**
     * Deletes a given backup
     *
     * @param int $id
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function delete($id)
    {
        $entityManager = $this->getDqlHelper()->getEntityManager();
        /** @var \Shopware\Models\MultiEdit\Backup|null $backup */
        $backup = $entityManager->find(\Shopware\Models\MultiEdit\Backup::class, $id);

        if (!$backup) {
            throw new \RuntimeException(sprintf('Backup by id %d not found', $id));
        }

        $dir = dirname($backup->getPath());

        // Delete the zip file
        unlink($backup->getPath());

        // Delete .sql and .dump files from our backup folder. Any other files will not be deleted
        $files = $this->getDirectoryList($dir);
        foreach ($files as $file) {
            unlink($file);
        }

        $entityManager->remove($backup);
        $entityManager->flush();

        // Delete the empty directory
        return rmdir($dir);
    }

    /**
     * Will delete all backup files from folders within the backup path which do not have a db entry associated
     * and have no zip file
     */
    public function deleteAbandonedBackups()
    {
        $path = $this->backupPath;

        $folders = scandir($path);
        $resultFolders = [];
        foreach ($folders as $key => $folder) {
            $folderPath = $path . $folder . '/';
            // Remove non-folders and non-backup folders
            if (is_dir($folderPath) && strpos($folder, $this->backupBaseName) !== false) {
                $resultFolders[] = $folderPath;
            }
        }

        foreach ($resultFolders as $folder) {
            $zips = $this->getDirectoryList($folder, ['zip']);
            $dataFiles = $this->getDirectoryList($folder);

            // If no zip archive exists in the backup dir…
            if (empty($zips)) {
                $query = $this->getDqlHelper()->getEntityManager()->createQueryBuilder()
                    ->select('backup')
                    ->from(\Shopware\Models\MultiEdit\Backup::class, 'backup')
                    ->where('backup.path LIKE ?1')
                    ->setParameter(1, $folder . '%')
                    ->getQuery();
                $result = $query->getArrayResult();

                // …and no database record exists for this path
                if (empty($result)) {
                    // delete the data files
                    foreach ($dataFiles as $file) {
                        unlink($file);
                    }
                    // Try to delete the (empty) folder.
                    rmdir($folder);
                }
            }
        }
    }

    /**
     * Try do determine the data type of the value in order to backup it properly
     *
     * @param int|float $value
     *
     * @return int|null
     */
    public function getDataTypeForExport($value)
    {
        // Non-numeric values needs to be encoded as string (default)
        // so returning null here.
        if (!is_numeric($value)) {
            return null;
        }

        // If the value casted to float differs from the value casted to int,
        // use float as type
        if ((float) $value != (int) $value) {
            return \Zend_Db::FLOAT_TYPE;
            // Else encode it as int
        }

        return \Zend_Db::INT_TYPE;
    }

    /**
     * Builds an array of tables and columns, we need to backup
     *
     * @param array $operations Array of operations
     */
    protected function buildAffectedTableArray($operations)
    {
        $prefixes = [];
        $fields = [];
        // Create a assoc array of tables and their fields
        foreach ($operations as $operation) {
            list($prefix, $field) = explode('.', $operation['column']);
            $prefix = ucfirst(strtolower($prefix));
            $prefixes[] = $prefix;

            $fields[$prefix][] = $field;
        }

        $tables = [];
        // Build a list of tables affected by the given operations array
        // Associate columns which are affected by the given operations array
        foreach ($this->getDqlHelper()->getColumnsForProductListing() as $config) {
            $prefix = ucfirst(strtolower($config['entity']));
            // Only check for prefix, if prefix array was set
            // Else, all default tables will be exported
            if ($prefixes && !in_array($prefix, $prefixes)) {
                continue;
            }
            if ($config['editable']) {
                if (in_array($config['field'], $fields[$prefix])) {
                    // We always need the id field
                    $tables[$config['table']]['prefix'] = $prefix;
                    $tables[$config['table']]['columns']['id'] = 'id';
                    $tables[$config['table']]['columns'][$config['columnName']] = $config['columnName'];
                }
            }
        }
        $this->affectedTables = $tables;
    }

    /**
     * Returns an array of tables we need to backup
     *
     * @return array
     */
    protected function getAffectedTables()
    {
        return array_keys($this->affectedTables);
    }

    /**
     * Returns a prefix for a given table
     *
     * @param string $table
     *
     * @throws \RuntimeException
     */
    protected function getPrefixFromTable($table)
    {
        $prefix = $this->affectedTables[$table]['prefix'];

        if (!$prefix) {
            throw new \RuntimeException(sprintf('Empty prefix for %s', $table));
        }

        return $prefix;
    }

    /**
     * Return an array of columns which needs to be backed up for a given table
     *
     * @param string $table
     *
     * @throws \RuntimeException
     */
    protected function getAffectedColumns($table)
    {
        $columns = $this->affectedTables[$table]['columns'];

        if (!$columns) {
            throw new \RuntimeException(sprintf('Empty column for %s', $table));
        }

        return $columns;
    }

    /**
     * Returns a string from a given operations array
     *
     * @param array $operations
     *
     * @return string
     */
    protected function operationsToString($operations)
    {
        $out = [];
        foreach ($operations as $operation) {
            $out[] = implode(' ', $operation);
        }

        return implode("\n", $out);
    }

    /**
     * Creates a backup model for a given backup
     *
     * @param string $path
     * @param string $filterString
     * @param array  $operations
     * @param int    $items
     */
    protected function saveBackup($path, $filterString, $operations, $items)
    {
        $backup = new \Shopware\Models\MultiEdit\Backup();

        $backup->setFilterString($filterString);
        $backup->setOperationString($this->operationsToString($operations));
        $backup->setItems($items);
        $backup->setPath($path);
        $backup->setHash(sha1_file($path));
        $backup->setSize(filesize($path));

        $backup->setDate(new \DateTime());

        $this->getDqlHelper()->getEntityManager()->persist($backup);
        $this->getDqlHelper()->getEntityManager()->flush($backup);
    }

    /**
     * Dumps a given table to disc - as only needed columns are exported, this is quite fast
     *
     * @param string $table
     * @param string $name
     * @param int[]  $ids
     * @param bool   $newBackup
     *
     * @throws \RuntimeException
     */
    protected function dumpTable($table, $name, $ids, $newBackup)
    {
        $quotedIds = '(' . $this->getDqlHelper()->getDb()->quote($ids, \PDO::PARAM_INT) . ')';
        $path = $this->getOutputPath($name);

        $hash = uniqid();

        $outFileData = $path . $table . '.' . $hash . '.datasql';
        $outFileHeader = $path . $table . '.headersql';
        $outFileFooter = $path . $table . '.footersql';

        $fileHandle = fopen($outFileData, 'w');

        $columns = $this->getAffectedColumns($table);
        $sqlColumns = implode(', ', $columns);

        // When a new backup is created, we create header and footer sql once
        if ($newBackup) {
            file_put_contents($outFileHeader, "INSERT INTO {$table} ({$sqlColumns}) VALUES ");

            // Build update values
            $duplicateUpdateColumns = array_map(
                function ($column) {
                    return "{$column} = VALUES({$column})";
                },
                $columns
            );
            $duplicateUpdateColumns = implode(', ', $duplicateUpdateColumns);
            file_put_contents($outFileFooter, " ON DUPLICATE KEY UPDATE {$duplicateUpdateColumns};");
        }

        // Get the current values
        $sql = "SELECT {$sqlColumns} FROM {$table} WHERE id IN " . $quotedIds;
        $result = $this->getDqlHelper()->getDb()->fetchAll($sql);

        // Prepare the data (quoting)
        $output = [];
        foreach ($result as $values) {
            $vals = [];
            foreach ($values as $value) {
                // Special quoting for numbers
                $type = $this->getDataTypeForExport($value);
                // Everything else is quoted as string - except 'null'
                $vals[] = $value === null ? 'NULL' : $this->getDqlHelper()->getDb()->quote($value, $type);
            }
            $output[] = '(' . implode(', ', $vals) . ')';
        }

        // Actually write the file
        fwrite($fileHandle, implode(',', $output));
    }

    /**
     * Returns output directory for a given name and takes care for directory permissions
     *
     * @param string $name
     *
     * @return string
     */
    protected function getOutputPath($name)
    {
        $path = $this->backupPath . $name . '/';

        // Create director
        if (!is_dir($path)) {
            mkdir($path, 0777, true);

            // Fix chmod - creating directories recursively with permissions does not seem
            // to work in some cases
            chmod($path, 0777);
        }

        return $path;
    }

    /**
     * Compresses the backup and delete old uncompressed files
     *
     * @param string $name
     *
     * @return string
     */
    protected function compressBackup($name)
    {
        // Zip the files
        $result = $this->createZip($name);

        // If zip was created successfully, delete the uncompressed files
        if ($result && is_file($result)) {
            $path = $this->getOutputPath($name);
            $files = $this->getDirectoryList($path);

            foreach ($files as $file) {
                unlink($file);
            }
        }

        return $result;
    }

    /**
     * Zips the backup directory content
     *
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return bool|string
     */
    protected function createZip($name)
    {
        $zipPath = $this->outputPath . $name . '.zip';

        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException(sprintf('Could not open %s, please check the permissions.', $zipPath));
        }

        $files = $this->getDirectoryList($this->outputPath);
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $result = $zip->close();

        if ($result) {
            return $zipPath;
        }

        return false;
    }

    /**
     * Return a list of files with a certain extension
     *
     * @param string $path
     * @param array  $findExtension
     * @param array  $blacklistName
     *
     * @return array
     */
    protected function getDirectoryList($path, $findExtension = ['datasql', 'headersql', 'footersql'], $blacklistName = [])
    {
        $files = scandir($path);
        foreach ($files as $key => &$file) {
            $extension = pathinfo($path . $file, PATHINFO_EXTENSION);
            if ($file == '.' || $file == '..' || in_array($file, $blacklistName) || !in_array($extension, $findExtension)) {
                unset($files[$key]);
            }
            $file = $path . $file;
        }

        return $files;
    }
}
