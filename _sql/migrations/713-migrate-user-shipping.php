<?php

class Migrations_Migration713 extends Shopware\Components\Migrations\AbstractMigration
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
        $attributeSelectorSql = $this->getAttributeSelectors($attributeColumns);

        $sql = <<<SQL
INSERT IGNORE INTO s_user_addresses_migration (user_id, company, department, salutation, firstname, lastname, street, zipcode, city, additional_address_line1, additional_address_line2, country_id, state_id, checksum, $attributeFields)
(
  SELECT
    userID, company, department, salutation, firstname, lastname, street, zipcode, city, additional_address_line1, additional_address_line2, countryID, IF(stateID = 0, NULL, stateID),
    MD5(CONCAT_WS('', userID, company, department, salutation, firstname, lastname, street, zipcode, city, additional_address_line1, additional_address_line2, countryID, stateID, null, null)),
    $attributeSelectorSql
  FROM s_user_shippingaddress
  LEFT JOIN s_user_shippingaddress_attributes AS attr ON attr.shippingID = s_user_shippingaddress.id
  INNER JOIN s_user ON s_user_shippingaddress.userID = s_user.id
  INNER JOIN s_core_countries ON s_user_shippingaddress.countryID = s_core_countries.id
)
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

    private function getAttributeSelectors(array $columns)
    {
        $selectors = [];
        $existingColumns = array_column($this->getConnection()->query('DESCRIBE s_user_shippingaddress_attributes')->fetchAll(\PDO::FETCH_ASSOC),
            'Field');

        foreach ($columns as $column) {
            if (in_array($column['Field'], $existingColumns)) {
                $selectors[] = 'attr.' . $column['Field'];
            } else {
                $selectors[] = 'NULL';
            }
        }

        return join(", ", $selectors);
    }
}
