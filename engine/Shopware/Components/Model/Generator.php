<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Model;

use Doctrine\DBAL\Schema\AbstractSchemaManager;

class Generator
{
    /**
     * Definition of the "create target directory failure" exception.
     */
    const CREATE_TARGET_DIRECTORY_FAILED = 1;

    /**
     * Contains the standard php file header tag
     */
    const PHP_FILE_HEADER = '<?php';

    /**
     * Contains the AGPLv3 licence
     */
    const SHOPWARE_LICENCE = '
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
';

    /**
     * Contains the required namespaces for a shopware model
     */
    const NAMESPACE_HEADER = '
namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
';

    /**
     * Definition of the standard shopware model class header.
     */
    const CLASS_HEADER = '
/**
 * @ORM\Entity
 * @ORM\Table(name="%tableName%")
 */
class %className% extends ModelEntity
{
    ';

    /**
     * Definition of the standard shopware model property
     */
    const COLUMN_PROPERTY = '
    /**
     * @var %propertyType% $%propertyName%
    %ID%
     * @ORM\Column(name="%columnName%", type="%columnType%", nullable=%nullable%)
     */
     protected $%propertyName%;
';

    /**
     * Definition of the standard shopware id property.
     */
    const PRIMARY_KEY = ' * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")';

    /**
     * Definition of a standard shopware association property.
     */
    const ASSOCIATION_PROPERTY = '
    /**
     * @var \%foreignClass%
     *
     * @ORM\OneToOne(targetEntity="%foreignClass%", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="%localColumn%", referencedColumnName="%foreignColumn%")
     * })
     */
    protected $%property%;
    ';

    /**
     * Definition of a constructor for initializing properties.
     */
    const CONSTRUCTOR = '
    public function __construct()
    {
        %propertyInitializations%
    }
    ';

    /**
     * Definition of the standard shopware getter and setter
     * functions of a single model column property.
     */
    const COLUMN_FUNCTIONS = '
    public function get%upperPropertyName%()
    {
        return $this->%lowerPropertyName%;
    }

    public function set%upperPropertyName%($%lowerPropertyName%)
    {
        $this->%lowerPropertyName% = $%lowerPropertyName%;
        return $this;
    }
    ';

    /**
     * Definition of the standard shopware getter and setter
     * functions of a single model association property.
     */
    const ASSOCIATION_FUNCTIONS = '
    public function get%upperPropertyName%()
    {
        return $this->%lowerPropertyName%;
    }

    public function set%upperPropertyName%($%lowerPropertyName%)
    {
        $this->%lowerPropertyName% = $%lowerPropertyName%;
        return $this;
    }
    ';

    /**
     * Contains the schema manager which is used to get the database definition
     *
     * @var AbstractSchemaManager
     */
    protected $schemaManager = null;

    /**
     * Contains the table mapping for the existing Shopware models.
     *
     * @var array
     */
    protected $tableMapping = [];

    /**
     * Target path for the generated models
     *
     * @var string
     */
    protected $path = '';

    /**
     * Path of the shopware models directory.
     *
     * @var string
     */
    protected $modelPath = '';

    /**
     * @param string $path
     * @param string $modelPath
     */
    public function __construct(AbstractSchemaManager $schemaManager, $path, $modelPath)
    {
        $this->schemaManager = $schemaManager;
        $this->path = $path;
        $this->modelPath = $modelPath;
    }

    /**
     * @return string
     */
    public function getModelPath()
    {
        return $this->modelPath;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return AbstractSchemaManager
     */
    public function getSchemaManager()
    {
        return $this->schemaManager;
    }

    /**
     * @param array $tableMapping
     */
    public function setTableMapping($tableMapping)
    {
        $this->tableMapping = $tableMapping;
    }

    /**
     * @return array
     */
    public function getTableMapping()
    {
        if (empty($this->tableMapping)) {
            $this->tableMapping = $this->createTableMapping();
        }

        return $this->tableMapping;
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getSourceCodeForTable($tableName)
    {
        $table = $this->getSchemaManager()->listTableDetails($tableName);

        return $this->generateModel($table);
    }

    /**
     * Generates the models for the extension tables like s_user_extension.
     * This tables can be modified from plugins so we have to generate the models after each plugin installation.
     *
     * @param string[] $tableNames
     *
     * @throws \Exception
     *
     * @return array
     */
    public function generateAttributeModels($tableNames = [])
    {
        if (empty($this->tableMapping)) {
            $this->tableMapping = $this->createTableMapping();
        }

        try {
            $this->createTargetDirectory($this->getPath());
        } catch (\Exception $e) {
            return ['success' => false, 'error' => self::CREATE_TARGET_DIRECTORY_FAILED, 'message' => $e->getMessage()];
        }

        $errors = [];
        foreach ($this->getSchemaManager()->listTableNames() as $tableName) {
            if (!empty($tableNames) && !in_array($tableName, $tableNames)) {
                continue;
            }

            if (!$this->stringEndsWith($tableName, '_attributes')) {
                continue;
            }

            $table = $this->getSchemaManager()->listTableDetails($tableName);

            $sourceCode = $this->generateModel($table);
            $result = $this->createModelFile($table, $sourceCode);
            if ($result === false) {
                $errors[] = $table->getName();
            }
        }

        return ['success' => empty($errors), 'errors' => $errors];
    }

    /**
     * @param \Doctrine\DBAL\Schema\Table $table
     * @param string                      $sourceCode
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createModelFile($table, $sourceCode)
    {
        // At least we need a file name for the current table object.
        $className = $this->getClassNameOfTableName($table->getName());
        if (strpos($table->getName(), '_attributes')) {
            $tableName = str_replace('_attributes', '', $table->getName());
            $className = $this->getClassNameOfTableName($tableName);
        }

        if ($className === '') {
            return false;
        }

        $file = $this->getPath() . $className . '.php';

        if (file_exists($file) && !is_writable($file)) {
            throw new \Exception(
                sprintf('File: "%s" isn\'t writable, please check the file permissions for this model!', $file), 501
            );
        }

        $result = file_put_contents($file, $sourceCode);

        return $result !== false;
    }

    /**
     * Helper function to convert some database types into supported
     * php types. For example the database type "text" will be converted
     * to "string"
     *
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    public function getPropertyTypeOfColumnType($column)
    {
        if ($column->getType() instanceof \Doctrine\DBAL\Types\TextType) {
            return 'string';
        }

        return $column->getType()->getName();
    }

    /**
     * Helper function to create an table - class mapping of all defined
     * shopware models.
     * Used for the parent classes of attributes and association target entities.
     *
     * @return array
     */
    public function createTableMapping()
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->getModelPath()),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $classes = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if ($extension !== 'php' || $file->isDir()) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            // preg match for the model class name!
            $matches = [];
            preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches);
            if (count($matches) === 0) {
                continue;
            }
            $className = $matches[1];

            // preg match for the model namespace!
            $matches = [];
            preg_match('/namespace\s+(.*);/', $content, $matches);
            if (count($matches) === 0) {
                continue;
            }
            $namespace = $matches[1];

            // preg match for the model table name!
            $matches = [];
            preg_match('/@ORM\\\Table\\(name="(.*)"\\)/', $content, $matches);

            // Repository has no table annotation
            if (count($matches) === 0) {
                continue;
            }
            $tableName = $matches[1];

            $classes[$tableName] = [
                'class' => $className,
                'namespace' => $namespace,
            ];
        }

        return $classes;
    }

    /**
     * Creates a new directory for the models which will be generated.
     *
     * @param string $dir
     */
    protected function createTargetDirectory($dir)
    {
        if (!is_dir($dir)) {
            if (@mkdir($dir, 0777, true) === false && !is_dir($dir)) {
                throw new \RuntimeException(sprintf("Unable to create directory (%s)\n", $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf("Unable to write in directory (%s)\n", $dir));
        }
    }

    /**
     * The generate model function create the doctrine model for
     * the passed table name.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     *
     * @return string
     */
    protected function generateModel($table)
    {
        // First we have to create the file header for a standard php file
        $fileHeader = self::PHP_FILE_HEADER;

        // After the file header has been created, we can add the shopware AGPLv3 licence tag
        $licenceHeader = self::SHOPWARE_LICENCE;

        // After the licence has been added, we can declare all namespace and used namespaces
        $namespaceHeader = self::NAMESPACE_HEADER;

        // The last header is the class header, which contains the definition of the class
        $classHeader = $this->getClassDefinition($table);

        // Now, that all headers are defined, we can add the class content.
        // First we create all normal column properties.
        $columnProperties = $this->getColumnsProperties($table);

        // After the normal column properties have been created, we can add the association properties.
        $associationProperties = $this->getAssociationProperties($table);

        // Add the constructor
        $constructor = $this->getConstructor($table);

        // Now all properties are declared, but the properties need getter and setter functions to be accessible
        $columnFunctions = $this->getColumnsFunctions($table);

        // The association property needs getter and setter, too.
        $associationFunctions = $this->getAssociationsFunctions($table);

        // To concat the different source code paths, we create an array with all source code fragments
        $paths = [
            $fileHeader,
            $licenceHeader,
            $namespaceHeader,
            $classHeader,
            implode("\n", $columnProperties),
            implode("\n", $associationProperties),
            $constructor,
            implode("\n", $columnFunctions),
            implode("\n", $associationFunctions),
            '}',
        ];

        // Then we implode the source code paths with a line break

        return implode("\n", $paths);
    }

    /**
     * Returns the class definition for the passed table object
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     */
    protected function getClassDefinition($table)
    {
        $source = self::CLASS_HEADER;
        $className = $table->getName();

        // Check if the passed table is a Shopware attribute table.
        if (strpos($table->getName(), '_attributes')) {
            //if the table is an attribute table we have to use the class name of the parent table.
            $parentClass = str_replace('_attributes', '', $table->getName());
            $className = $this->getClassNameOfTableName($parentClass);

        // If the passed table is not an attribute table, we have to check if the table is already declared
        } elseif (array_key_exists($table->getName(), $this->getTableMapping())) {
            // If this is the case we will use the already declared class name
            $className = $this->tableMapping[$table->getName()]['class'];
        }

        $source = str_replace('%className%', $className, $source);
        $source = str_replace('%tableName%', $table->getName(), $source);

        return $source;
    }

    /**
     * Helper function to get a class name of the passed table.
     * This function uses the internal property "tableMapping".
     * The tableMapping array contains the class names and namespace for
     * each already declared shopware model/table.
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function getClassNameOfTableName($tableName)
    {
        if (!array_key_exists($tableName, $this->tableMapping)) {
            return '';
        }

        $parentTable = $this->tableMapping[$tableName];
        $fragments = explode('\\', $parentTable['namespace']);
        $lastFragment = array_pop($fragments);

        if ($lastFragment === $parentTable['class']) {
            $fullName = $parentTable['class'];
        } else {
            $fullName = $lastFragment . ucfirst($parentTable['class']);
        }

        return ucfirst($fullName);
    }

    /**
     * The getColumnsProperties function creates the source code
     * for all table column properties. This function returns
     * only the definition of the properties, not of the getters and setters.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     *
     * @return array
     */
    protected function getColumnsProperties($table)
    {
        $columns = [];
        /** @var \Doctrine\DBAL\Schema\Column $column */
        foreach ($table->getColumns() as $column) {
            if ($table->getName() === 's_articles_attributes' && $column->getName() === 'articleID') {
                continue;
            }

            $columns[] = $this->getColumnProperty($table, $column);
        }

        return $columns;
    }

    /**
     * The getColumnProperty function creates the source code
     * for the passed column. The table parameter is used
     * to check the column foreign/primary key.
     * This function creates only the property definition for a single
     * column, not the getter and setter function.
     *
     * @param \Doctrine\DBAL\Schema\Table  $table
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    protected function getColumnProperty($table, $column)
    {
        $source = self::COLUMN_PROPERTY;

        $source = str_replace('%columnName%', $column->getName(), $source);

        $source = str_replace('%propertyName%', $this->getPropertyNameOfColumnName($table, $column), $source);

        $source = str_replace('%nullable%', $this->getColumnNullProperty($column), $source);

        $source = str_replace('%columnType%', $column->getType()->getName(), $source);

        $source = str_replace('%propertyType%', $this->getPropertyTypeOfColumnType($column), $source);

        $primary = ' *';
        if ($this->isPrimaryColumn($table, $column)) {
            $primary = self::PRIMARY_KEY;
        }

        $source = str_replace('%ID%', $primary, $source);

        return $source;
    }

    /**
     * Helper function to convert the table column name to the shopware standard definition of
     * a class property. Filters the under score words to camel case and
     * checks if the passed column is a foreign key column.
     * If the passed column is a foreign key column the function uses the class
     * name of the foreign table as property with an additional suffix "Id".
     *
     * @param \Doctrine\DBAL\Schema\Table  $table
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    protected function getPropertyNameOfColumnName($table, $column)
    {
        $foreignKey = $this->getColumnForeignKey($table, $column);
        if ($foreignKey instanceof \Doctrine\DBAL\Schema\ForeignKeyConstraint) {
            $table = $foreignKey->getForeignTableName();

            $fullName = $this->getClassNameOfTableName($table);

            return lcfirst($fullName) . 'Id';
        }

        return lcfirst($this->underscoreToCamelCase($column->getName()));
    }

    /**
     * Converts underscore separated string into a camelCase separated string
     *
     * @param string $str
     *
     * @return string
     */
    protected function underscoreToCamelCase($str)
    {
        $func = function ($c) {
            return strtoupper($c[1]);
        };

        return preg_replace_callback('/_([a-zA-Z])/', $func, $str);
    }

    /**
     * Helper function to get the foreign key for the
     * passed column object.
     * If the column has a foreign key definition, the class name
     * of the foreign table will be used for the property name of the column.
     *
     * @param \Doctrine\DBAL\Schema\Table  $table
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint|null
     */
    protected function getColumnForeignKey($table, $column)
    {
        /** @var \Doctrine\DBAL\Schema\ForeignKeyConstraint $foreignKey */
        foreach ($table->getForeignKeys() as $foreignKey) {
            foreach ($foreignKey->getLocalColumns() as $foreignKeyColumn) {
                if ($foreignKeyColumn === $column->getName()) {
                    return $foreignKey;
                }
            }
        }

        return null;
    }

    /**
     * Helper function to convert the boolean value of the function "column->getNotNull()" to
     * a string which can be used for the doctrine annotation.
     *
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    protected function getColumnNullProperty($column)
    {
        if ($column->getNotnull()) {
            return 'false';
        }

        return 'true';
    }

    /**
     * Helper function to check if the passed column is the primary key
     * column.
     * In this case doctrine requires the @ORM\ID() annotation and a primary key
     * strategy.
     *
     * @param \Doctrine\DBAL\Schema\Table  $table
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return bool
     */
    protected function isPrimaryColumn($table, $column)
    {
        if ($table->getPrimaryKey() === null) {
            return false;
        }
        foreach ($table->getPrimaryKey()->getColumns() as $primaryColumn) {
            if ($column->getName() === $primaryColumn) {
                return true;
            }
        }

        return false;
    }

    /**
     * The getAssociationProperties function creates the source
     * code for the doctrine association properties for the passed table object.
     * This function creates only the property definition source code, not the getter
     * and setter source code for the properties.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     *
     * @return array
     */
    protected function getAssociationProperties($table)
    {
        $associations = [];
        /** @var \Doctrine\DBAL\Schema\ForeignKeyConstraint $foreignKey */
        foreach ($table->getForeignKeys() as $foreignKey) {
            if ($table->getName() === 's_articles_attributes' && $foreignKey->getColumns()[0] === 'articleID') {
                continue;
            }

            $associations[] = $this->getAssociationProperty($table, $foreignKey);
        }

        return $associations;
    }

    /**
     * Helper function to create the source code for a single table association.
     * This function creates only the source code for the property definition.
     * The getter and setter function for the properties are created over the
     * "getAssociationFunctions" function.
     *
     * @param \Doctrine\DBAL\Schema\Table                $table
     * @param \Doctrine\DBAL\Schema\ForeignKeyConstraint $foreignKey
     *
     * @return string
     */
    protected function getAssociationProperty($table, $foreignKey)
    {
        $source = self::ASSOCIATION_PROPERTY;

        if (!array_key_exists($foreignKey->getForeignTableName(), $this->tableMapping)) {
            return '';
        }

        $referenceTable = $this->tableMapping[$foreignKey->getForeignTableName()];
        $className = $this->getClassNameOfTableName($foreignKey->getForeignTableName());
        $namespace = $referenceTable['namespace'] . '\\' . $referenceTable['class'];

        $localColumn = $foreignKey->getLocalColumns();
        $foreignColumn = $foreignKey->getForeignColumns();

        $source = str_replace('%foreignClass%', $namespace, $source);
        $source = str_replace('%localColumn%', $localColumn[0], $source);
        $source = str_replace('%foreignColumn%', $foreignColumn[0], $source);
        $source = str_replace('%property%', lcfirst($className), $source);

        return $source;
    }

    /**
     * Creates the source code for the custom constructor, which initializes all
     * not-null properties with their respective default value.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     *
     * @return string
     */
    protected function getConstructor($table)
    {
        // Create the property initializations
        $initializations = [];
        foreach ($table->getColumns() as $column) {
            if ($column->getDefault() === null || $this->isPrimaryColumn($table, $column)
                // With MariaDB >= 10.2.7 and doctrine/dbal < 2.7.0 'NULL'
                // will be returned as string if there is no default, see:
                // https://github.com/doctrine/doctrine2/issues/6565
                || $column->getDefault() === 'NULL') {
                continue;
            }

            // Make sure not to set default values for foreign key properties
            $isForeignKeyColumn = false;
            foreach ($table->getForeignKeys() as $foreignKey) {
                if (in_array($column->getName(), $foreignKey->getLocalColumns())) {
                    $isForeignKeyColumn = true;
                    break;
                }
            }
            if ($isForeignKeyColumn) {
                continue;
            }

            // Determine property name, type and default value
            $property = $this->getPropertyNameOfColumnName($table, $column);
            $type = $this->getPropertyTypeOfColumnType($column);
            $default = $column->getDefault();
            switch ($type) {
                case 'string':
                    $default = '"' . $default . '"';
                    break;
                case 'boolean':
                    $default = ($default) ? 'true' : 'false';
                    break;
                case 'date':
                case 'datetime':
                    // MariaDB has already quoted the default value
                    $default = trim($default, '\'');
                    $default = 'new \DateTime("' . $default . '")';
            }

            $initializations[] = '$this->' . lcfirst($property) . ' = ' . $default . ';';
        }
        if (count($initializations) === 0) {
            // No need for a constructor
            return '';
        }

        // Compile the source snippet
        $source = self::CONSTRUCTOR;
        $source = str_replace('%propertyInitializations%', implode("\n        ", $initializations), $source);

        return $source;
    }

    /**
     * The getColumnsFunctions function creates the source code for the
     * getter and setter for all table columns properties.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     *
     * @return array
     */
    protected function getColumnsFunctions($table)
    {
        $functions = [];
        foreach ($table->getColumns() as $column) {
            $functions[] = $this->getColumnFunctions($table, $column);
        }

        return $functions;
    }

    /**
     * Helper function to create the getter and setter source code
     * for the passed column object.
     *
     * @param \Doctrine\DBAL\Schema\Table  $table
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return string
     */
    protected function getColumnFunctions($table, $column)
    {
        $source = self::COLUMN_FUNCTIONS;

        $property = $this->getPropertyNameOfColumnName($table, $column);

        $source = str_replace('%upperPropertyName%', ucfirst($property), $source);
        $source = str_replace('%lowerPropertyName%', lcfirst($property), $source);

        return $source;
    }

    /**
     * Creates the getter and setter functions source code for all association
     * properties of the passed table object.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     *
     * @return array
     */
    protected function getAssociationsFunctions($table)
    {
        $columns = [];
        /** @var \Doctrine\DBAL\Schema\ForeignKeyConstraint $foreignKey */
        foreach ($table->getForeignKeys() as $foreignKey) {
            if ($table->getName() === 's_articles_attributes' && $foreignKey->getColumns()[0] === 'articleID') {
                continue;
            }

            $columns[] = $this->getAssociationFunctions($foreignKey);
        }

        return $columns;
    }

    /**
     * Creates the getter and setter function source code for the passed
     * foreign key constraint object.
     *
     * @param \Doctrine\DBAL\Schema\ForeignKeyConstraint $foreignKey
     *
     * @return string
     */
    protected function getAssociationFunctions($foreignKey)
    {
        $source = self::ASSOCIATION_FUNCTIONS;
        $className = $this->getClassNameOfTableName($foreignKey->getForeignTableName());

        $source = str_replace('%upperPropertyName%', ucfirst($className), $source);
        $source = str_replace('%lowerPropertyName%', lcfirst($className), $source);

        return $source;
    }

    /**
     * Checks if given string in $haystack end the with string in $needle
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private function stringEndsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length === 0) {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }
}
