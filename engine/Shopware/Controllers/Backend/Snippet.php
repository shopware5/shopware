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

use Shopware\Models\Snippet\Snippet;

class Shopware_Controllers_Backend_Snippet extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var string path to temporary uploaded file for import
     */
    protected $uploadedFilePath;

    /**
     * Garbage-Collector
     * Deletes uploaded file
     */
    public function __destruct()
    {
        if (!empty($this->uploadedFilePath)) {
            $this->get('shopware.components.stream_protocol_validator')->validate($this->uploadedFilePath);
            if (file_exists($this->uploadedFilePath)) {
                @unlink($this->uploadedFilePath);
            }
        }
    }

    /**
     * Get locales action
     */
    public function getLocalesAction()
    {
        // Get locales from s_core_shops. Join over snippets in order to get default snippets referring onto main-shop
        // as well as snippets which have no own shop
        $locales = Shopware()->Db()->fetchAll("
            SELECT
              DISTINCT s.id as shopId,
              IFNULL(sn.localeID, s.locale_id) as localeId,
              CONCAT(IF(s.id=1, 'Default', s.name), ' / ', IFNULL(l.locale, l2.locale)) as displayName
            FROM s_core_shops s
            LEFT JOIN s_core_snippets sn ON s.id = sn.shopID
            LEFT JOIN s_core_locales l ON sn.localeID=l.id
            LEFT JOIN s_core_locales l2 ON s.locale_id=l2.id
            ORDER BY s.id, localeId
        ");

        $this->View()->assign([
            'success' => true,
            'data' => $locales,
            'total' => count($locales),
        ]);
    }

    /**
     * Get snippets action
     * Returns an filtered and paginated array of Snippets.
     *
     * Snippets of the main language (shopId = 1 && localeId = 1) that do not
     * exists in the selected language are joined into the result set using a UNION Select
     *
     * <code>
     * (
     * SELECT `s`.`id`, `s`.`namespace`, `s`.`name`, `s`.`value`, `s`.`shopId`, `s`.`localeId`
     * FROM `s_core_snippets` AS `s`
     * WHERE (s.localeId = 1)
     *     AND (s.shopID = 1)
     * )
     * UNION ALL
     * (
     *     SELECT `s2`.`id`, `s1`.`namespace`, `s1`.`name`, `s2`.`value`, 1 as shopId, 1 as localeID
     *     FROM `s_core_snippets` AS `s1`
     *     LEFT JOIN `s_core_snippets` AS `s2`
     *         ON s1.namespace = s2.namespace
     *         AND s1.name = s2.name
     *         AND s2.localeId = 1
     *         AND s2.shopId = 1
     *     WHERE (s1.localeId = 1)
     *     AND (s1.shopID = 1)
     *     AND (s2.id IS NULL)
     * )
     * ORDER BY `namespace` ASC LIMIT 30
     * </code>
     */
    public function getSnippetsAction()
    {
        $start = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 20);
        $localeId = (int) $this->Request()->getParam('localeId');
        $shopId = (int) $this->Request()->getParam('shopId');
        $namespace = $this->Request()->getParam('namespace');
        $name = $this->Request()->getParam('name');
        $filterParams = $this->Request()->getParam('filter');

        $order = $this->Request()->getParam('sort', []);
        if (!empty($order)) {
            $order = array_pop($order);
        }

        $filters = [];
        foreach ($filterParams as $singleFilter) {
            $filters[$singleFilter['property']] = $singleFilter['value'];
        }

        $secondStmt = Shopware()->Db()
                                ->select()
                                ->from(['s1' => 's_core_snippets']);

        $secondStmt->joinLeft(
            ['s2' => 's_core_snippets'],
            "s1.namespace = s2.namespace AND s1.name = s2.name AND s2.localeId = $localeId AND s2.shopId = $shopId"
        );

        $secondStmt->reset('columns');
        $secondStmt->columns([
            's2.id',
            's1.namespace',
            's1.name',
            's2.value',
            's1.value as defaultValue',
            new Zend_Db_Expr("$shopId as shopId"),
            new Zend_Db_Expr("$localeId as localeID"),
        ]);

        $secondStmt->where('s1.localeId = ?', 1);
        $secondStmt->where('s1.shopID = ?', 1);
        $secondStmt->where('s2.id IS NULL');

        $stmt = Shopware()->Db()
          ->select()
          ->from(
              ['s' => 's_core_snippets'],
              ['id', 'namespace', 'name', 'value', 'value as defaultValue', 'shopId', 'localeId']
          );

        // Filter by locale
        if (!empty($localeId)) {
            $stmt->where('s.localeId = ?', $localeId);
        }

        // Filter by shop
        if (!empty($shopId)) {
            $stmt->where('s.shopID = ?', $shopId);
        }

        // Filter by namespace
        if (!empty($namespace)) {
            $namespaceWildcard = $namespace . '/%';
            $stmt->where(
                Shopware()->Db()->quoteInto('s.namespace LIKE ?', $namespace) .
                ' OR ' .
                Shopware()->Db()->quoteInto('s.namespace LIKE ?', $namespaceWildcard)
            );

            $secondStmt->where(
                Shopware()->Db()->quoteInto('s1.namespace LIKE ?', $namespace) .
                ' OR ' .
                Shopware()->Db()->quoteInto('s1.namespace LIKE ?', $namespaceWildcard)
            );
        }

        // Filter by name
        if (!empty($name)) {
            $stmt->where(
                Shopware()->Db()->quoteInto('s.name IN (?)', $name)
            );

            $secondStmt->where(
                Shopware()->Db()->quoteInto('s1.name IN (?)', $name)
            );
        }

        // Filter empty values
        if (isset($filters['filterEmpty'])) {
            $stmt->where('(s.value LIKE "" OR s.value IS NULL)');
        }

        // Search
        if (isset($filters['search'])) {
            $filter = '%' . $filters['search'] . '%';

            $stmt->where('(s.namespace LIKE ? OR s.name LIKE ? OR s.value LIKE ?)', $filter);
            $secondStmt->where('(s1.namespace LIKE ? OR s1.name LIKE ? OR s1.value LIKE ?)', $filter);
        }

        $selectUnion = Shopware()->Db()->select()->union(['(' . $stmt . ')', '(' . $secondStmt . ')'], Zend_Db_Select::SQL_UNION_ALL);

        if (!empty($order)) {
            $selectUnion->order($order['property'] . ' ' . $order['direction']);
        } else {
            $selectUnion->order('namespace');
        }

        $selectUnion->limit($limit, $start);

        $countStmt = clone $selectUnion;
        $countStmt->reset('limitcount')
                ->reset('limitoffset');

        $sql = 'SELECT COUNT(*) FROM (' . $countStmt . ') as counter';
        $totalCount = Shopware()->Db()->fetchOne($sql);

        $result = Shopware()->Db()->query($selectUnion)->fetchAll();

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => $totalCount,
        ]);
    }

    /**
     * Create snippet action
     */
    public function createSnippetAction()
    {
        $snippets = $this->Request()->getPost();
        $isSingleSnippet = false;
        $result = [];

        if (array_key_exists('namespace', $snippets)) {
            $snippets = [$snippets];
            $isSingleSnippet = true;
        }

        foreach ($snippets as $params) {
            $snippet = new Snippet();
            $snippet->fromArray($params);
            $snippet->setDirty(true);

            if (!$this->isSnippetValid($snippet)) {
                $result[$snippet->getId()] = $params;
                continue;
            }

            try {
                Shopware()->Models()->persist($snippet);
                Shopware()->Models()->flush();
            } catch (Exception $e) {
                $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

                return;
            }

            $result[$snippet->getId()] = Shopware()->Models()->toArray($snippet);
        }

        if ($isSingleSnippet) {
            $result = current($result);
        }

        $this->View()->assign(['success' => !empty($result), 'data' => $result]);
    }

    /**
     * Update snippet action
     */
    public function updateSnippetAction()
    {
        $snippets = $this->Request()->getParam('snippets', []);

        // Batch mode
        if (!empty($snippets)) {
            foreach ($snippets as $snippet) {
                /** @var Snippet $snippetModel */
                $snippetModel = Shopware()->Models()->getRepository(Snippet::class)->find($snippet['id']);
                $dirty = ($snippetModel->getDirty() || strcmp($snippetModel->getValue(), $snippet['value']) != 0);
                $snippetModel->setDirty($dirty);
                $snippetModel->setValue($snippet['value']);

                if (!$this->isSnippetValid($snippetModel)) {
                    Shopware()->Models()->remove($snippetModel);
                    continue;
                }
            }
            Shopware()->Models()->flush();
            $this->View()->assign(['success' => true]);

            return;
        }

        $id = $this->Request()->getParam('id', false);
        if ($id === false) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        /** @var Snippet|null $result */
        $result = Shopware()->Models()->getRepository(Snippet::class)->find($id);
        if (!$result) {
            $this->View()->assign(['success' => false, 'message' => 'Snippet not found']);

            return;
        }

        $params = $this->Request()->getPost();
        $dirty = ($result->getDirty() || strcmp($result->getValue(), $params['value']) != 0);
        $result->setDirty($dirty);
        $result->fromArray($params);

        if (!$this->isSnippetValid($result)) {
            Shopware()->Models()->remove($result);
        }

        Shopware()->Models()->flush();

        $data = Shopware()->Models()->toArray($result);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Remove snippet action
     *
     * Removes a snipped identified by it's id
     */
    public function removeSnippetAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);
        }

        /** @var Snippet|null $snippet */
        $snippet = Shopware()->Models()->getRepository(Snippet::class)->find($id);
        if (!$snippet) {
            $this->View()->assign(['success' => false, 'message' => 'Snippet not found']);

            return;
        }

        try {
            Shopware()->Models()->remove($snippet);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Import snippet action
     */
    public function importSnippetAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                  'success' => false,
                  'message' => 'Could not upload file',
             ]);

            return;
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            echo json_encode([
                  'success' => false,
                  'message' => 'Unsecure file detected',
             ]);

            return;
        }

        $fileName = basename($_FILES['file']['name']);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!in_array($extension, ['csv', 'txt', 'xml'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Unknown Extension',
            ]);

            return;
        }

        $destPath = Shopware()->DocPath('media_temp');
        if (!is_dir($destPath)) {
            // Try to create directory with write permissions
            mkdir($destPath, 0777, true);
        }

        $destPath = realpath($destPath);
        if (!file_exists($destPath)) {
            echo json_encode([
                'success' => false,
                'message' => sprintf("Destination directory '%s' does not exist.", $destPath),
            ]);

            return;
        }

        if (!is_writable($destPath)) {
            echo json_encode([
                'success' => false,
                'message' => sprintf("Destination directory '%s' does not have write permissions.", $destPath),
            ]);

            return;
        }

        $filePath = tempnam($destPath, 'snippets_');

        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath) === false) {
            echo json_encode([
                'success' => false,
                'message' => sprintf('Could not move %s to %s.', $_FILES['file']['tmp_name'], $filePath),
            ]);

            return;
        }

        $this->uploadedFilePath = $filePath;
        chmod($filePath, 0644);

        if ($extension === 'xml') {
            $xml = simplexml_load_string(@file_get_contents($filePath), 'SimpleXMLElement', LIBXML_NOCDATA);
            $snippets = $xml->Worksheet->Table->Row;
            $headers = $this->readXmlRow(current($snippets));
        } else {
            $snippets = new Shopware_Components_CsvIterator($filePath, ';');
            $headers = $snippets->GetHeader();
        }

        if (empty($headers) || !in_array('namespace', $headers) || !in_array('name', $headers)) {
            echo json_encode([
                'success' => false,
                'message' => 'File not in right format',
            ]);

            return;
        }

        $translations = [];
        foreach ($headers as $header) {
            $pos = strpos($header, 'value-');
            if ($pos === false) {
                continue;
            }
            $row = explode('-', $header);
            $translations[] = [
                'both' => $row[1] . '-' . $row[2],
                'localeID' => $this->getLocaleId($row[1]),
                'shopID' => $row[2],
            ];
        }

        $counter = 0;
        foreach ($snippets as $snippet) {
            if ($extension === 'xml') {
                $snippet = $this->readXmlRow($snippet, $headers);
                if ($snippet['name'] === 'name') {
                    continue;
                }
            }

            foreach ($translations as $translation) {
                if (empty($snippet['value-' . $translation['both']])) {
                    continue;
                }
                $namespace = trim(ltrim($snippet['namespace'], "'"));
                $name = trim(ltrim($snippet['name'], "'"));
                if (empty($name)) {
                    continue;
                }

                $value = $snippet['value-' . $translation['both']];
                $value = trim($value[0] === '\'' ? substr($value, 1) : $value);
                $value = $this->getFormatSnippetForSave($value);

                $dirty = 0;
                if (array_key_exists('dirty-' . $translation['both'], $snippet)) {
                    $dirty = trim(ltrim($snippet['dirty-' . $translation['both']], "'"));
                }

                $sql = '
                    INSERT INTO `s_core_snippets` (`namespace`, `name`, `localeID`, `shopID`, `value`, `updated`, `created`, `dirty`)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)
                    ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), `updated`=NOW()
                ';

                Shopware()->Db()->query($sql, [
                    $namespace,
                    $name,
                    $translation['localeID'],
                    $translation['shopID'],
                    $value,
                    $dirty,
                 ]);

                ++$counter;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Successfully saved $counter rows",
        ]);
    }

    /**
     * Export snippet action
     */
    public function exportSnippetAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $format = strtolower($this->Request()->getParam('format', 'sql'));

        if ($format === 'csv' || $format === 'csvexcel') {
            $sql = '
            SELECT DISTINCT s.shopID as shopId, l.id as localeId, l.locale
            FROM s_core_snippets s, s_core_locales l, s_core_shops o
            WHERE l.id = s.localeID
            AND o.id = s.shopID
            ORDER BY shopId, localeId';
            $locales = Shopware()->Db()->query($sql)->fetchAll();

            $baseLocale = $locales[0];
            $alias = $baseLocale['locale'] . $baseLocale['shopId'];

            $stmt = Shopware()->Db()
                ->select()
                ->from(['s1' => 's_core_snippets'], ['namespace', 'name', "value as $alias", "dirty as $alias-dirty"])
                ->where('s1.localeId = ?', $baseLocale['localeId'])
                ->where('s1.shopId = ?', $baseLocale['shopId'])
                ->order('s1.namespace');

            $counter = 1;
            foreach ($locales as $locale) {
                if ($counter++ == 1) {
                    continue;
                }

                $prefix = 's' . $counter;
                $localeId = $locale['localeId'];
                $shopId = $locale['shopId'];
                $alias = $locale['locale'] . $locale['shopId'];

                $stmt->joinLeft(
                    [$prefix => 's_core_snippets'],
                    "s1.namespace = $prefix.namespace AND s1.name = $prefix.name AND $prefix.localeId = $localeId AND $prefix.shopId = $shopId",
                    ["value as $alias", "dirty as $alias-dirty"]
                );
            }

            $result = Shopware()->Db()->query($stmt)->fetchAll();

            $header = [];
            $header[] = 'namespace';
            $header[] = 'name';
            foreach ($locales as $locale) {
                $header[] = 'value-' . $locale['locale'] . '-' . $locale['shopId'];
                $header[] = 'dirty-' . $locale['locale'] . '-' . $locale['shopId'];
            }

            echo implode($header, ';');
            echo "\r\n";

            $encoding = null;

            if ($format === 'csv') {
                $encoding = 'utf-8';
            } elseif ($format === 'csvexcel') {
                $encoding = 'iso-8859-15';
            }
            $this->Response()->headers->set('content-type', 'text/x-comma-separated-values;charset=' . $encoding);
            $this->Response()->headers->set('content-disposition', 'attachment; filename="export.csv"');

            foreach ($result as $row) {
                foreach ($row as $key => $elem) {
                    $row[$key] = $this->getFormatSnippetForExport($elem, $encoding);
                }
                echo $this->encodeLine($row, array_keys($row));
            }

            return;
        }

        if ($format === 'sql') {
            $this->Response()->headers->set('content-type: text/plain', '');
            $this->Response()->headers->set('content-disposition', 'attachment; filename="export.sql"');

            $sql = 'SELECT * FROM s_core_snippets ORDER BY namespace';
            $result = Shopware()->Db()->query($sql);
            $rows = null;

            echo "REPLACE INTO `s_core_snippets` (`namespace`, `name`, `value`, `localeID`, `shopID`,`created`, `updated`, `dirty`) VALUES \r\n";
            foreach ($result->fetchAll() as $row) {
                $value = Shopware()->Db()->quote($row['value']);
                $value = str_replace("\n", '\\n', $value);

                $rows[] = sprintf("(%s, %s, %s, '%s', '%s', '%s', NOW(), %d)",
                      Shopware()->Db()->quote($row['namespace']),
                      Shopware()->Db()->quote($row['name']),
                      $value,
                      (int) $row['localeID'],
                      (int) $row['shopID'],
                      $row['created'],
                      $row['dirty']
                );
            }
            echo implode(",\r\n", $rows) . ';';

            return;
        }
    }

    /**
     * Get namespace action
     */
    public function getNamespacesAction()
    {
        $node = $this->Request()->getParam('node');

        if ($node !== 'root') {
            $snippets = Shopware()->Models()
                                  ->getRepository('Shopware\Models\Snippet\Snippet')
                                  ->findBy(['namespace' => $node]);

            $snippets = Shopware()->Models()->toArray($snippets);

            $result = [];
            foreach ($snippets as $snippet) {
                $result[] = [
                    'id' => $snippet['id'],
                    'namespace' => $snippet['name'],
                    'fullNamespace' => $snippet['namespace'],
                    'leaf' => true,
                ];
            }

            $this->View()->assign([
                'success' => true,
                'data' => $result,
                'total' => count($result),
            ]);

            return;
        }

        /** @var \Doctrine\ORM\QueryBuilder $builder */
        $builder = Shopware()->Models()
                             ->getRepository('Shopware\Models\Snippet\Snippet')
                             ->createQueryBuilder('snippet');

        $builder->select('snippet.namespace, count(snippet.id) as snippetCount')
                ->groupBy('snippet.namespace')
                ->orderBy('snippet.namespace');

        $result = $builder->getQuery()->execute();

        $result = $this->prepareNamespaceTree($result);

        $this->View()->assign([
           'success' => true,
           'data' => $result,
           'total' => count($result),
        ]);
    }

    /**
     * Remove namespace action
     */
    public function removeNamespaceAction()
    {
        if (!($namespace = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Namespace not found']);

            return;
        }

        /** @var \Doctrine\ORM\QueryBuilder $builder */
        $builder = Shopware()->Models()->createQueryBuilder();

        $builder->delete('Shopware\Models\Snippet\Snippet', 's')
                ->andWhere('s.namespace LIKE :namespace')
                ->orWhere('s.namespace LIKE :namespaceWildcard')
                ->setParameter('namespace', $namespace)
                ->setParameter('namespaceWildcard', $namespace . '/%');

        $result = $result = $builder->getQuery()->execute();

        $this->View()->assign([
           'success' => true,
           'data' => $result,
        ]);
    }

    /**
     * Read xml row action
     *
     * @param array $xml
     * @param array $keys
     *
     * @return array
     */
    public function readXmlRow($xml, $keys = null)
    {
        $data = [];
        foreach ($xml as $cell) {
            $data[] = (string) $cell->Data;
        }
        if ($keys !== null) {
            $key_data = [];
            foreach ($keys as $key => $name) {
                $key_data[$name] = isset($data[$key]) ? $data[$key] : '';
            }

            return $key_data;
        }

        return $data;
    }

    /**
     * Method to define acl dependencies in backend controllers
     */
    protected function initAcl()
    {
        $this->addAclPermission('getSnippets', 'read');
        $this->addAclPermission('createSnippet', 'create');
        $this->addAclPermission('updateSnippet', 'update');
        $this->addAclPermission('removeSnippet', 'delete');
        $this->addAclPermission('importSnippet', 'create');
        $this->addAclPermission('exportSnippet', 'read');
        $this->addAclPermission('getNamespaces', 'read');
    }

    /**
     * Returns locale id by locale
     *
     * @param string $locale
     *
     * @return string
     */
    protected function getLocaleId($locale)
    {
        $sql = '
            SELECT `id`
            FROM `s_core_locales`
            WHERE `locale` = ?
        ';

        return Shopware()->Db()->fetchOne($sql, [$locale]);
    }

    /**
     * Transforms the data to an ExtJs-Tree-Compatible format
     *
     * @param array $array
     *
     * @return array
     */
    protected function prepareNamespaceTree($array)
    {
        $nodes = [];

        foreach ($array as $item) {
            $nodes[] = $this->toTree($item);
        }

        $result = [];

        foreach ($nodes as $arr) {
            $result = array_merge_recursive($result, $arr);
        }

        return $this->normalize($result);
    }

    /**
     * Recursive function that transforms the data to an ExtJs-Tree-Compatible format
     *
     * @param array  $items
     * @param string $ns
     *
     * @return array
     */
    protected function normalize($items, $ns = '')
    {
        $result = [];
        foreach ($items as $namespace => $value) {
            $tmp = [];

            $tmp['namespace'] = $namespace;
            $tmp['id'] = $ns . $namespace;

            if (is_array($value['data'])) {
                $tmp['data'] = $this->normalize($value['data'], $tmp['id'] . '/');
            } else {
                $tmp['leaf'] = true;
            }

            $result[] = $tmp;
        }

        return $result;
    }

    /**
     * Recursive function that transforms the namespaced array values into a tree-structure
     *
     * @param array $item
     *
     * @return array
     */
    protected function toTree($item)
    {
        $result = [];
        $namespace = $item['namespace'];

        if (($pos = stripos($namespace, '/')) !== false) {
            $currentNamespace = substr($namespace, 0, $pos);
            $carryOver = substr($namespace, $pos + 1);

            $result[$currentNamespace] = [
                'data' => $this->toTree(['namespace' => $carryOver]),
            ];
        } else {
            $result[$namespace] = [
                'leaf' => true,
            ];
        }

        return $result;
    }

    /**
     * @deprecated since 5.6, will be removed in 5.7
     *
     * Helper method to prefix properties
     *
     * @param array  $properties
     * @param string $prefix
     *
     * @return array
     */
    protected function prefixProperties($properties = [], $prefix = '')
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        foreach ($properties as $key => $property) {
            if (isset($property['property'])) {
                $properties[$key]['property'] = $prefix . '.' . $property['property'];
            }
        }

        return $properties;
    }

    /**
     * Encode line for csv
     *
     * @param array $line
     * @param array $keys
     *
     * @return string
     */
    protected function encodeLine($line, $keys)
    {
        $settings = [
            'separator' => ';',
            'fieldmark' => '"',
            'escaped_fieldmark' => '""',
            'newline' => "\r\n",
            'escaped_newline' => '',
        ];

        $csv = '';
        $lastKey = end($keys);
        foreach ($keys as $key) {
            if ($line[$key] !== null) {
                if (strpos($line[$key], "\r") !== false || strpos($line[$key], "\n") !== false || strpos(
                    $line[$key], $settings['fieldmark']
                ) !== false || strpos($line[$key], $settings['separator']) !== false
                ) {
                    $csv .= $settings['fieldmark'] . str_replace(
                        $settings['fieldmark'], $settings['escaped_fieldmark'], $line[$key]
                    ) . $settings['fieldmark'];
                } else {
                    $csv .= "'" . $line[$key];
                }
            }
            if ($lastKey != $key) {
                $csv .= $settings['separator'];
            } else {
                $csv .= $settings['newline'];
            }
        }

        return $csv;
    }

    /**
     * Format snippet for export
     *
     * @param string $string
     * @param string $encoding
     *
     * @return string
     */
    protected function getFormatSnippetForExport($string, $encoding = 'utf-8')
    {
        if ($encoding !== 'utf-8') {
            $string = mb_convert_encoding($string, $encoding, 'UTF-8');
        }

        return $string;
    }

    /**
     * Format snippet for save
     *
     * @param string $string
     *
     * @return string
     */
    protected function getFormatSnippetForSave($string)
    {
        $string = mb_convert_encoding($string, 'HTML-ENTITIES', mb_detect_encoding($string, ['utf-8', 'iso-8859-1', 'iso-8859-15', 'windows-1251']));

        $string = html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');

        return $string;
    }

    /**
     * Validates the value of the snippet. Returns false if the snippet value is empty and the shopId/localeId is
     * not 1.
     *
     * @return bool
     */
    private function isSnippetValid(Snippet $snippet)
    {
        if (!$snippet->getValue()) {
            if ($snippet->getShopId() != 1 || $snippet->getLocaleId() != 1) {
                return false;
            }
        }

        return true;
    }
}
