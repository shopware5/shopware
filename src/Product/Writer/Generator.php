<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Symfony\Component\DependencyInjection\Container;

class Generator
{
    private $classTemplate = <<<'EOD'
<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Field;

use Shopware\Framework\Validation\ConstraintBuilder;
use Shopware\Product\Writer\Api\%s;

class %s extends %s
{
    public function __construct(ConstraintBuilder $constraintBuilder)
    {
        parent::__construct('%s', '%s', $constraintBuilder);
    }

}
EOD;

    private $serviceDefinitionTemplate = <<<'EOD'
<service id="shopware.product.writer_field_%s" class="Shopware\Product\Writer\Field\%s">
    <argument type="service" id="shopware.validation.constraint_builder"/>   
    
    <tag name="shopware.product.writer_field"/> 
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

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function generate()
    {
        $path = __DIR__ . '/Field';
        $table = 'product';

        $connection = $this->container->get('dbal_connection');

        $schemaManager = $connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns($table);

        $services = [];

        /** @var Column $column */
        foreach($columns as $column) {
            $cammelCaseName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $column->getName()))));
            $className = ucfirst($cammelCaseName) . 'Field';

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
                case 'String':
                case StringType::class:
                    $fieldClass = 'StringField';
                    break;
                default:
                    echo "ERROR: {$column->getType()}\n";

            };

            file_put_contents(
                $path . '/' . $className . '.php',
                sprintf($this->classTemplate, $fieldClass, $className, $fieldClass, $cammelCaseName, $column->getName())
            );

            $services[] = sprintf($this->serviceDefinitionTemplate, strtolower($column->getName()), $className);
        }

        file_put_contents(
            $path . '/../' . $table . '-fields.xml',
            sprintf($this->serviceFileTemplate, implode('        ' . PHP_EOL, $services))
        );
    }
}