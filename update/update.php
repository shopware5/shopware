<?php

if (file_exists('../config.php')) {
    $config = include '../config.php';
} elseif (file_exists('../engine/Shopware/Configs/Custom.php')) {
    $config = include '../engine/Shopware/Configs/Custom.php';
} else {
    die('Could not find config');
}

$dbConfig = $config['db'];

try {
    $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'], $dbConfig['username'], $dbConfig['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
    exit(1);
}

$update = new update($conn);
$stats = array();

$update->fixTreePath($stats);
var_dump($stats);

$update->fixCategoryTree($stats);
var_dump($stats);

$stats = array();
$update->fixCategoryPosition($stats);
var_dump($stats);



/**
 * Class update
 */
class update
{
    /**
     * @var PDO
     */
    protected $conn;

    /**
     * @param PDO $conn
     */
    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param $stats
     */
    public function fixTreePath(&$stats)
    {
        $baseMemory = memory_get_usage();
        $startTime  = microtime(true);
        $errors     = array();

        $updateSql = "UPDATE s_categories SET `path` = :path WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateSql);

        $stmt = 'SELECT c1.id, c1.parent, c1.path FROM `s_categories` c1';
        $stmt = $this->conn->query($stmt);

        $this->conn->beginTransaction();

        while ($row = $stmt->fetch()) {
            $path = $this->getParentCategories($row['parent']);

            if (empty($path)) {
                $path = null;
            } else {
                $path = implode('|', $path);
                $path = '|' . $path . '|';
            }

            if ($path !== $row['path']) {
                $errors[] = sprintf("Path mismatch for categoryId: %d, path in db: %s, new path; %s", $row['id'], $row['path'], $path);
                $updateStmt->execute(array(
                        ':id'   => $row['id'],
                        ':path' => $path
                    ));
            }
        }

        $this->conn->commit();

        $stats = array(
            'errors'     => $errors,
            'runtime'    => number_format((microtime(true) - $startTime), 2) . " seconds",
            'memory'     => number_format(((memory_get_usage() - $baseMemory) / 1024 / 1024), 2) . " MB",
            'peakMemory' => number_format(((memory_get_peak_usage() - $baseMemory) / 1024 / 1024), 2) . " MB",
        );
    }

    /**
     * @param array $stats
     */
    public function fixCategoryTree(&$stats = array())
    {
        $baseMemory = memory_get_usage();
        $startTime  = microtime(true);
        $errors     = array();

        $assignmentSql = "SELECT id FROM s_articles_categories_ro c WHERE c.categoryID = :categoryId AND c.articleID = :articleID AND c.articleID = :articleID AND parentCategoryID = :parentCategoryId";
        $assignmentStmt = $this->conn->prepare($assignmentSql);

        $insertSql = 'INSERT INTO s_articles_categories_ro (articleID, categoryID, parentCategoryID) VALUES (:articleId, :categoryId, :parentCategoryId)';
        $insertStmt = $this->conn->prepare($insertSql);

        $allAssignsSql = "
            SELECT DISTINCT ac.id, ac.articleID, ac.categoryID, c.parent
            FROM s_articles_categories ac
            INNER JOIN s_categories c ON ac.categoryID = c.id
            LEFT JOIN s_categories c2 ON c.id = c2.parent
            WHERE c2.id IS NULL
            ORDER BY articleID
        ";
        $assignments = $this->conn->query($allAssignsSql);

        $newRows = 0;
        $this->conn->beginTransaction();

        while ($assignment = $assignments->fetch(PDO::FETCH_ASSOC)) {
            if (empty($assignment['parent'])) {
                continue;
            }

            $parents = $this->getParentCategories($assignment['parent']);
            if (empty($parents)) {
                continue;
            }

            array_unshift($parents, $assignment['categoryID']);

            foreach ($parents as $parentId) {
                $assignmentStmt->execute(array(
                    'articleID'        => $assignment['articleID'],
                    'categoryId'       => $parentId,
                    'parentCategoryId' => $assignment['categoryID']
                ));

                if ($assignmentStmt->fetchColumn() === false) {
                    $newRows++;
                    $errors[] = sprintf("Missing entry: categoryId: %d, articleId: %d, parentCategoryId: %d", $parentId, $assignment['articleID'], $assignment['categoryID']);

                    $insertStmt->execute(array(
                        ':categoryId'       => $parentId,
                        ':articleId'        => $assignment['articleID'],
                        ':parentCategoryId' => $assignment['categoryID']
                    ));
                }
            }
        }

        $this->conn->commit();

        $stats = array(
            'newRows'        => $newRows,
            'errors'         => $errors,
            'runtime'        => number_format((microtime(true) - $startTime), 2) . " seconds",
            'memory'         => number_format(((memory_get_usage() - $baseMemory) / 1024 / 1024), 2) . " MB",
            'peakMemory'     => number_format(((memory_get_peak_usage() - $baseMemory) / 1024 / 1024), 2) . " MB",
        );
    }

    /**
     * @param array $stats
     */
    public function fixCategoryPosition(&$stats = array())
    {
        $baseMemory = memory_get_usage();
        $startTime = microtime(true);

        $preConditionSql = 'SELECT MAX(`left`) FROM s_categories';
        $result = $this->conn->query($preConditionSql)->fetchColumn();

        if ($result == 0) {
            return;
        }

        $sql = "SELECT id, parent, description, position FROM s_categories ORDER BY parent, s_categories.left";

        $stmt = $this->conn->query($sql);

        $oldParent = -1;
        $counter = 0;

        $newRows = 0;

        $updateSql = "UPDATE s_categories SET `position` = :position WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateSql);

        while ($row = $stmt->fetch()) {
            if ($row['parent'] != $oldParent) {
                $counter = 0;
                $oldParent = $row['parent'];
            }

            if ($row['position'] === null || $row['position'] != $counter) {
                $newRows++;
                $updateStmt->execute(array(
                        ':id'       => $row['id'],
                        ':position' => $counter
                    ));
            }

            $counter++;
        }

        $this->conn->exec("UPDATE s_categories SET `left` =  '0', `right` =  '0', `level` =  '0'");

        $stats = array(
            'fixedPositions' => $newRows,
            'runtime'        => number_format((microtime(true) - $startTime), 2) . " seconds",
            'memory'         => number_format(((memory_get_usage() - $baseMemory) / 1024 / 1024), 2) . " MB",
            'peakMemory'     => number_format(((memory_get_peak_usage() - $baseMemory) / 1024 / 1024), 2) . " MB",
        );
    }

    /**
     * @param integer $parentId
     * @return array
     */
    public function getParentCategories($parentId)
    {
        static $cache = array();

        if (isset($cache[$parentId])) {
            return $cache[$parentId];
        }

        $stmt = $this->conn->prepare('SELECT id, parent FROM  s_categories WHERE id = :parentId AND parent IS NOT NULL');
        $stmt->execute(array(':parentId' => $parentId));
        $parent = $stmt->fetch();
        if (!$parent) {
            return false;
        }

        $result = array($parent['id']);

        $parent = $this->getParentCategories($parent['parent']);
        if ($parent) {
            $result = array_merge($result, $parent);
        }

        $cache[$parentId] = $result;

        return $result;
    }
}
