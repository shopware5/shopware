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
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}

$update = new update($conn);

$stats = array();

$update->fixCategoryTree($stats);
var_dump($stats);

$stats = array();
$update->fixCategoryPosition($stats);
var_dump($stats);


$update->fixPath();

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

    public function fixPath()
    {
        $stmt = 'SELECT c1.id, c1.parent, c1.path FROM `s_categories` c1
            LEFT JOIN `s_categories` c2 ON c2.parent = c1.id
            WHERE c2.id IS NULL
        ';

        $stmt = $this->conn->query($stmt);
        while ($row = $stmt->fetch()) {
            $this->fixPathForParentCategories($row['id']);
        }
    }

    /**
     * @param integer $parentId
     * @return array
     */
    public function fixPathForParentCategories($parentId)
    {
        static $cache = array();

        if (isset($cache[$parentId])) {
            return $cache[$parentId];
        }

        $updateSql = "UPDATE s_categories SET `path` = :path WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateSql);


        $stmt = $this->conn->prepare('SELECT id, description, parent FROM  s_categories WHERE id = :parentId AND parent IS NOT NULL');
        $stmt->execute(array(':parentId' => $parentId));
        $parent = $stmt->fetch();
        if (!$parent) {
            return false;
        }

        $result = array($parent['id']);

        $parent = $this->fixPathForParentCategories($parent['parent']);
        if ($parent) {
            $result = array_merge($result, $parent);
        }

        $tmp = $result;
        array_shift($tmp);

        $path = implode('|', $tmp);
        if (empty($path)) {
            $path = null;
        } else {
            $path = '|' . $path . '|';
        }


        $updateStmt->execute(array(
            ':id'   => $parentId,
            ':path' => $path
        ));

        $cache[$parentId] = $result;

        return $result;
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




    /**
     * @param integer $parentId
     * @return array
     */
    public function getFixCategories($parentId)
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

    /**
     * @param array $stats
     */
    public function fixCategoryPosition(&$stats = array())
    {
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
        );
    }

    /**
     * @param array $stats
     */
    public function fixCategoryTree(&$stats = array())
    {
        $baseMemory = memory_get_usage();
        $startTime = microtime(true);

        $assignmentSql = "SELECT id FROM s_articles_categories c WHERE c.categoryID = :categoryId AND c.articleID = :articleID";
        $assignmentStmt = $this->conn->prepare($assignmentSql);

        $insertSql = 'INSERT INTO s_articles_categories (categoryId, articleID) VALUES (:categoryId, :articleId)';
        $insertStmt = $this->conn->prepare($insertSql);

        $allAssignsSql = "
            SELECT DISTINCT ac.id, ac.articleID, ac.categoryId, c.parent
            FROM  s_articles_categories ac
            INNER JOIN s_categories c
            ON ac.categoryID = c.id
        ";

        $assignments = $this->conn->query($allAssignsSql);

        $newRows = 0;
        $this->conn->beginTransaction();

        while ($assignment = $assignments->fetch()) {
            if (empty($assignment['parent'])) {
                continue;
            }

            $parents = $this->getParentCategories($assignment['parent']);

            if (empty($parents)) {
                continue;
            }

            foreach ($parents as $parentId) {
                $assignmentStmt->execute(array('categoryId' => $parentId, 'articleID' => $assignment['articleID']));
                if ($assignmentStmt->fetchColumn() === false) {
                    $newRows++;

                    $insertStmt->execute(array(
                        ':categoryId' => $parentId,
                        ':articleId' => $assignment['articleID']
                    ));
                }
            }
        }

        $this->conn->commit();

        $stats = array(
            'newRows'        => $newRows,
            'runtime'        => number_format((microtime(true) - $startTime), 2) . " seconds",
            'memory'         => number_format(((memory_get_usage() - $baseMemory) / 1024 / 1024), 2) . " MB",
            'peakMemory'     => number_format(((memory_get_peak_usage() - $baseMemory) / 1024 / 1024), 2) . " MB",
        );
    }
}
