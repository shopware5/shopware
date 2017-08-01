<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Symfony\Component\DependencyInjection\Container;

class Generator
{
    private $tables = [
            'product',
            'product_also_bought_ro',
            'product_attribute',
            'product_avoid_customergroup',
            'product_category',
            'product_category_ro',
            'product_category_seo',
            'product_configurator_dependency',
            'product_configurator_group',
            'product_configurator_group_attribute',
            'product_configurator_option',
            'product_configurator_option_attribute',
            'product_configurator_option_relation',
            'product_configurator_price_variation',
            'product_configurator_set',
            'product_configurator_set_group_relation',
            'product_configurator_set_option_relation',
            'product_configurator_template',
            'product_configurator_template_attribute',
            'product_configurator_template_price',
            'product_configurator_template_price_attribute',
            'product_detail',
            'product_download',
            'product_download_attribute',
            'product_esd',
            'product_esd_attribute',
            'product_esd_serial',
            'product_img',
            'product_img_attribute',
            'product_img_mapping',
            'product_img_mapping_rule',
            'product_information',
            'product_information_attribute',
            'product_notification',
            'product_price',
            'product_price_attribute',
            'product_relationship',
            'product_similar',
            'product_similar_shown_ro',
            'product_supplier',
            'product_supplier_attribute',
            'product_top_seller_ro',
            'product_translation',
            'product_vote',
    ];

    private $classTemplate = <<<'EOD'
<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field\%s;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\%s;

class %s extends %s
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('%s', '%s', $constraintBuilder);
    }%s

}
EOD;

    private $serviceDefinitionTemplate = <<<'EOD'
<service id="shopware.product.writer_field_%s" class="Shopware\Product\Writer\Field\%s\%s">
    <argument type="service" id="shopware.validation.constraint_builder"/>   
    
    <tag name="shopware.product.%s.writer_field"/> 
</service>
EOD;

    private $serviceFileTemplate = <<<'EOD'
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <services>
        %s
    </services>
</container>
EOD;

    /**
     * @var Container
     */
    private $container;

    private $map = [
        'description_long' => 'HtmlTextField',
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function generateAll()
    {
        $path = __DIR__ . '/Field';

        foreach ($this->tables as $table) {
            $this->generate($table, $path);
        }
    }

    public function generate(string $table, string $path)
    {

        $path .=  '/' . ucfirst($this->toCammelCase($table));
        $connection = $this->container->get('dbal_connection');


        $schemaManager = $connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns($table);
        $rawForeignKeys = $schemaManager->listTableForeignKeys($table);

        $foreignKeys = [];
        /** @var ForeignKeyConstraint $key */
        foreach($rawForeignKeys as $key) {
            if(1 !== count($key->getLocalColumns())) {
                echo "ERROR: Unable to generate\n";

                continue;
            }

            $foreignKeys[$key->getLocalColumns()[0]] = $key;
        }

        @mkdir($path, 0777, true);

        $services = [];

        /** @var Column $column */
        foreach($columns as $column) {
            if('id' === $column->getName()) {
                continue;
            }

            $cammelCaseName = $this->toCammelCase($column->getName());
            $className = ucfirst($cammelCaseName) . 'Field';

            echo $path . '::' . $column->getName() . '::' . $column->getType() . "\n";

            $fieldClass = 'AbstractField';
            switch($column->getType()) {
                case 'Integer':
                case IntegerType::class:
                    $fieldClass = 'IntField';
                    break;
                case 'DateTime':
                case 'Date':
                case DateType::class:
                    $fieldClass = 'DateField';
                    break;
                case 'Text':
                    $fieldClass = 'TextField';
                    break;
                case 'String':
                case StringType::class:
                    $fieldClass = 'StringField';
                    break;
                default:
                    echo "ERROR: {$column->getType()}\n";
            }

            $addIn = '';
            if(false !== strpos($column->getName(), '_uuid')) {
                $fieldClass = 'ReferenceField';
            }

            if(array_key_exists($column->getName(), $this->map)) {
                $fieldClass = $this->map[$column->getName()];
            }

            file_put_contents(
                $path . '/' . $className . '.php',
                sprintf($this->classTemplate, ucfirst($this->toCammelCase($table)), $fieldClass, $className, $fieldClass, $cammelCaseName, $column->getName(), $addIn)
            );

            $services[] = sprintf($this->serviceDefinitionTemplate,
                $this->toCammelCase($column->getName()),
                ucfirst($this->toCammelCase($table)),
                $className,
                $table
            );
        }

        file_put_contents(
            $path . '/../' . $this->toMinusCase($table) . '-fields.xml',
            sprintf($this->serviceFileTemplate, implode('        ' . PHP_EOL, $services))
        );

        echo '$loader->load(\'../Writer/Field/' . $this->toMinusCase($table) . '-fields.xml\');' . "\n";
    }

    private function toCammelCase($value)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
    }

    private function toMinusCase($value)
    {
        return str_replace('_', '-', $value);
    }
}