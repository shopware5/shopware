<?php
class Shopware_Components_DbExport_Mysql extends Shopware_Components_DbExport_Abstract
{
    public function listFields($tableName)
    {
        $query = $this->db->query('DESCRIBE `' . $tableName . '`');
        $fields = array();
        while ($field = $query->fetch(PDO::FETCH_ASSOC)) {
            $fields[] = $field['Field'];
        }
        return $fields;
    }

    public function listTables()
    {
        $query = $this->db->query('SHOW TABLES');
        return $query->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function createData($limit, $offset = 0)
    {
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT * FROM `{$this->table}` LIMIT $limit OFFSET $offset";
        $result = $this->db->query($sql);
        if (!$result->rowCount()) {
            return false;
        }

        $rows = array();
        while ($values = $result->fetch(PDO::FETCH_NUM)) {
            $row = array();
            foreach ($values as $value) {
                $row[] = $value === NULL ? 'NULL' : $this->db->quote($value);
            }
            $rows[] = implode(', ', $row);
        }
        $rows = implode("),\n(", $rows);

        $fields = '`' . implode('`, `', $this->fields) . '`';
        $return = "INSERT INTO `{$this->table}` ($fields) VALUES\n($rows);\n";

        return $return;
    }

    public function createTable($newTable = null)
    {
        if ($newTable === null) {
            $newTable = $this->table;
        }
        $return_sql = "\nDROP TABLE IF EXISTS `$newTable`;";
        $return_sql .= "\nCREATE TABLE `$newTable` (\n";

        foreach ($this->getTableFields($this->table) as $name => $field) {
            $lines[] = "$name` $field";
        }
        foreach ($this->getTableKeys($this->table) as $type => $indexes) {
            foreach ($indexes as $name => $fields) {
                $line = $type;
                if ($type != 'INDEX') {
                    $line .= " KEY";
                }
                if ($type != 'PRIMARY') {
                    $line .= " `$name`";
                }
                $line .= " (`" . implode("`, `", $fields) . "`)";
                $lines[] = $line;
            }
        }
        $return_sql .= "\t" . implode(",\n\t", $lines) . "\n";

        $return_sql .= ')';

        $sql = 'SHOW TABLE STATUS WHERE Name=?';
        $query = $this->db->prepare($sql);
        $query->execute(array($this->table));
        $status = $query->fetch(PDO::FETCH_ASSOC);

        if (!empty($status['Engine'])) {
            $return_sql .= ' ENGINE=' . $status['Engine'];
        }
        if (!empty($status['Collation'])) {
            $status['Charset'] = strstr($status['Collation'], '_', true);
            $return_sql .= ' DEFAULT CHARSET=' . $status['Charset'] .' ' .
                           'COLLATE=' . $status['Collation'];
        }
        if (!empty($status['Auto_increment'])) {
            $return_sql .= ' AUTO_INCREMENT=' . $status['Auto_increment'];
        }

        $return_sql .= ";\n\n";
        return $return_sql;
    }

    public function getTableFields($table)
    {
        $sql = "SHOW FULL COLUMNS FROM `$table`";
        $result = $this->db->query($sql);
        $fields = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $line = "{$row['Type']} ";
            if ($row['Null'] != 'YES') {
                $line .= 'NOT NULL ';
            }
            if ($row['Default'] == 'CURRENT_TIMESTAMP') {
                $line .= 'default CURRENT_TIMESTAMP ';
            } elseif (isset($row['Default'])) {
                $line .= "default '{$row['Default']}'";
            } elseif ($row['Null'] == 'YES') {
                $line .= 'default NULL';
            }
            if (!empty($row['Extra'])) {
                $line .= $row['Extra'];
            }
            $fields[$row['Field']] = $line;
        }
        return $fields;
    }

    public function getTableKeys($table)
    {
        $keys = array('PRIMARY' => array(), 'FULLTEXT' => array(), 'UNIQUE' => array(), 'INDEX' => array());

        $sql = "SHOW KEYS FROM `$table`";
        $result = $this->db->query($sql);

        if ($result->rowCount()) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                if ($row['Key_name'] == 'PRIMARY') {
                    $keys["PRIMARY"]["PRIMARY"][] = $row['Column_name'];
                } elseif ($row['Index_type'] == 'FULLTEXT') {
                    $keys["FULLTEXT"][$row['Key_name']][] = $row['Column_name'];
                } elseif ($row['Non_unique'] == 0) {
                    $keys["UNIQUE"][$row['Key_name']][] = $row['Column_name'];
                } else {
                    $keys["INDEX"][$row['Key_name']][] = $row['Column_name'];
                }
            }
        }

        return $keys;
    }
}