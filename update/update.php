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

include '../engine/Shopware/Components/Model/CategoryDenormalization.php';
$categoryAdmin = new \Shopware\Components\Model\CategoryDenormalization($conn);

var_dump($categoryAdmin->removeAllAssignments());

$fixedPathes = $categoryAdmin->rebuildCategoryPath();
echo "Fixed Categorypathes: $fixedPathes\n";


$fixedAssignments = $categoryAdmin->rebuildAllAssignments();
echo "Fixed assignments $fixedAssignments\n";


$fixedAssignments = $categoryAdmin->rebuildAssignments(3);
echo "Fixed assignments $fixedAssignments\n";

//$ebuildAssignmentsCount = $categoryAdmin->rebuildAssignmentsCount(3);
//echo "rebuild assignmetnsfor $ebuildAssignmentsCount categories \n";
//
//$ebuildAssignmentsCount = $categoryAdmin->rebuildAssignments(3);
//echo "$ebuildAssignmentsCount assignmens rebuilded \n";


$update = new update($conn);
var_dump($update->fixCategoryPosition());

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
     * @param array $stats
     * @return int
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

        return $newRows;
    }
}
