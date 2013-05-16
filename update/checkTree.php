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

$update = new check($conn);
$stats = array();

$update->checkTreePath($stats);

if ($stats) {
    echo implode("\n", $stats) . "\n";
} else {
    echo "Tree Path is OK\n";
}

$stats = array();
$update->checkCategoryTree($stats);

if (!empty($stats['errors'])) {
    echo "Rows missing: " . $stats['newRows'] . "\n";
    echo implode("\n", $stats['errors']) . "\n";
} else {
    echo "Entries OK\n";
}


/**
 * Class update
 */
class check
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
    public function checkTreePath(&$stats)
    {
        $stmt = 'SELECT c1.id, c1.parent, c1.path FROM `s_categories` c1';

        $stmt = $this->conn->query($stmt);
        while ($row = $stmt->fetch()) {
            $path = $this->getParentCategories($row['parent']);

            if (empty($path)) {
                $path = null;
            } else {
                $path = implode('|', $path);
                $path = '|' . $path . '|';
            }

            if ($path !== $row['path']) {
                $stats[] = sprintf("Path mismatch for categoryId: %d, path in db: %s, new path; %s", $row['id'], $row['path'], $path);
            }
        }
    }

    /**
     * @param array $stats
     */
    public function checkCategoryTree(&$stats = array())
    {
        $baseMemory = memory_get_usage();
        $startTime = microtime(true);

        $errors = array();

        $assignmentSql = "SELECT id FROM s_articles_categories_ro c WHERE c.categoryID = :categoryId AND c.articleID = :articleID AND c.articleID = :articleID AND parentCategoryID = :parentCategoryId";
        $assignmentStmt = $this->conn->prepare($assignmentSql);

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
