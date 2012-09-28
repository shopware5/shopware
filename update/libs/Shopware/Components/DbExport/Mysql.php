<?php
class Shopware_Components_DbExport_Mysql extends Shopware_Components_DbExport_Abstract
{
    public function listTables()
    {
        $query = $this->db->query('SHOW TABLES');
        return $query->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function getTableData($limit, $offset = 0)
    {
        return Shopware_Components_DbDiff_Mysql::getTableData($this->db, $this->table, array(
            'limit' => $limit,
            'offset' => $offset,
        ));
    }

    public function getTable($newTable = null)
    {
        return Shopware_Components_DbDiff_Mysql::getTable($this->db, $this->table, array(
            'dropIfExists' => true
        ));
    }
}