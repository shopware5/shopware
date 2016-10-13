<?php

use Shopware\Components\Migrations\AbstractMigration;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class Migrations_Migration793 extends AbstractMigration
{
    /** @var CamelCaseToSnakeCaseNameConverter */
    protected $converter;

    /**
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        parent::__construct($connection);
        $this->converter = new CamelCaseToSnakeCaseNameConverter();
    }

    public function up($modus)
    {
        $statement = $this->connection->query("SELECT * FROM `s_core_engine_elements`");
        $attributes = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($attributes as $attribute) {
            $nameConverted = $this->converter->normalize($attribute['name']);
            $statement = $this->connection->prepare("UPDATE `s_attribute_configuration` SET `column_name` = ? WHERE `column_name` = ?");
            $statement->execute([$nameConverted, $attribute['name']]);
        }
    }
}
