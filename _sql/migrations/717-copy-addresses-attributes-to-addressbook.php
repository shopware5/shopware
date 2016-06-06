<?php

class Migrations_Migration717 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        if ($modus == self::MODUS_INSTALL) {
            return;
        }

        $attributeColumns = $this->getAttributeColumns();
        $attributeFields = join(", ", array_column($attributeColumns, 'Field'));
        $attributeSelectors = join(", ", array_map(function ($column) { return "migration." . $column; }, array_column($attributeColumns, 'Field')));

        $sql = <<<SQL
SET foreign_key_checks=0;

INSERT IGNORE INTO s_user_addresses_attributes (address_id, $attributeFields)
(
  SELECT adr.id, $attributeSelectors
  FROM s_user_addresses AS adr
  INNER JOIN s_user_addresses_migration AS migration ON adr.migration_id = migration.id
);

SET foreign_key_checks=1;
SQL;

        $this->addSql($sql);
    }

    private function getAttributeColumns()
    {
        $columns = $this->getConnection()->query('DESCRIBE s_user_addresses_attributes')->fetchAll(\PDO::FETCH_ASSOC);

        $columns = array_filter($columns, function ($column) {
            return !in_array($column['Field'], ['id', 'address_id']);
        });

        return $columns;
    }
}
