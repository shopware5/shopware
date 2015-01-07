<?php
class Migrations_Migration431 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE s_core_config_elements SET value = 's:334:"frontend/listing 3600
frontend/index 3600
frontend/detail 3600
frontend/campaign 14400
widgets/listing 14400
frontend/custom 14400
frontend/sitemap 14400
frontend/blog 14400
widgets/index 3600
widgets/checkout 3600
widgets/compare 3600
widgets/emotion 14400
widgets/recommendation 14400
widgets/lastArticles 3600
widgets/campaign 3600";'

WHERE name='cacheControllers';
EOD;
        $this->addSql($sql);

        // Check if the element already has a custom value
        try {
            $statement = $this->getConnection()->prepare("SELECT id, value FROM s_core_config_values WHERE element_id = (SELECT id FROM s_core_config_elements WHERE name='cacheControllers')");
            $statement->execute();
            $data = $statement->fetchAll();
        } catch(Exception $e) {
            return;
        }

        // If not - return
        if (empty($data)) {
            return;
        }

        $rowData = array_shift($data);

        $content = unserialize($rowData['value']);

        if (strpos($content, 'widgets/campaign ') === false) {
            $content .= "\nwidgets/campaign 3600";

            $statement = $this->connection->prepare("UPDATE s_core_config_values SET value = ? WHERE id = ?");
            $statement->execute(array(serialize($content), $rowData['id']));
        }
    }
}
