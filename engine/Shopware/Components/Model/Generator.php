<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 * Shopware 4
 * Copyright © shopware AG
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
     * The namespace which contains all generated attribute models.
     */
    const ATTRIBUTE_NAMESPACE = 'Shopware\Models\Attribute';

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
    const ONE_TO_ONE_ASSOCIATION_PROPERTY = '
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
     * Definition of an OneToMany association property, which inverts
     * an association property of a custom model.
     */
    const ONE_TO_MANY_ASSOCIATION_PROPERTY = '
    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="%owningClass%", mappedBy="%owningProperty%")
     */
    protected $%property%;
    ';

    /**
     * Definitition of a constructor for initializing properties.
     */
    const CONSTRUCTOR = '
    public function __construct() {
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
     * Definition of the getter for a OneToMany association property.
     */
    const ONE_TO_MANY_ASSOCIATION_FUNCTION = '
    public function get%upperPropertyName%()
    {
        return $this->%lowerPropertyName%;
    }
    ';

    /**
     * Contains the schema manager which is used to get the database definition
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected $schemaManager = null;

    /**
     * Contains the table mapping for the existing showpare models.
     * @var array
     */
    protected $tableMapping = array();

    /**
     * Contains the table mapping for the custom models, that is
     * models in Shopware\CustomModels namespace.
     * @var array
     */
    protected $customModelsTableMapping = array();

    /**
     * Target path for the generated models
     * @var string
     */
    protected $path = '';

    /**
     * Path of the shopware models directory.
     * @var string
     */
    protected $modelPath = '';


    /**
     * @param string $modelPath
     */
    public function setModelPath($modelPath)
    {
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
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param \Doctrine\DBAL\Schema\AbstractSchemaManager $schemaManager
     */
    public function setSchemaManager($schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    /**
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
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
     * @return array
     */
    public function getCustomModelsTableMapping()
    {
        if (empty($this->customModelsTableMapping)) {
            $this->customModelsTableMapping = $this->createCustomModelsTableMapping();
        }
        return $this->customModelsTableMapping;
    }

    /**
     * @param string $tableName
     * @return int
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
     * @param array $tableNames
     *
     * @throws \Exception
     * @return array
     */
    public function generateAttributeModels($tableNames = array())
    {
        if (empty($this->tableMapping)) {
            $this->tableMapping = $this->createTableMapping();
        }

        $this->createTargetDirectory();
        if (!file_exists($this->getPath())) {
            return array('success' => false, 'error' => self::CREATE_TARGET_DIRECTORY_FAILED);
        }

        $errors = array();
        /**@var $table \Doctrine\DBAL\Schema\Table*/
        foreach ($this->getSchemaManager()->listTables() as $table) {
            if (!empty($tableNames) && !in_array($table->getName(), $tableNames)) {
                continue;
            }
            if (strpos($table->getName(), '_attributes') === false) {
                continue;
            }
            $sourceCode = $this->generateModel($table);
            $result = $this->createModelFile($table, $sourceCode);
            if ($result === false) {
                $errors[] = $table->getName();
            }

        }

        return array('success' => empty($errors), 'errors' => $errors);
    }

    /**
     * @param $table \Doctrine\DBAL\Schema\Table
     * @param $sourceCode string
     *
     * @return int
     * @throws \Exception
     */
    public function createModelFile($table, $sourceCode)
    {
        //at least we need a file name for the current table object.
        $className = $this->getClassNameOfTableName($table->getName());
        if (strpos($table->getName(), '_attributes')) {
            $tableName = str_replace('_attributes', '', $table->getName());
            $className = $this->getClassNameOfTableName($tableName);
        }

        $file = $this->getPath() . $className . '.php';

        if (file_exists($file) && !is_writable($file)) {
            throw new \Exception("File: " . $file . " isn't writable, please check the file permissions for this model!", 501);
        }

        $result = file_put_contents($file, $sourceCode);

        return ($result !== false);
    }

    /**
     * Creates a new directory for the models which will be generated.
     */
    protected function createTargetDirectory()
    {
        if (file_exists($this->getPath())) {
            return true;
        }
        return mkdir($this->getPath(), 0777);
    }

    /**
     * The generate model function create the doctrine model for
     * the passed table name.
     *
     * @param $table \Doctrine\DBAL\Schema\Table
     * @return int
     */
    protected function generateModel($table)
    {
        // Determine all custom models, which have an owning OneToMany association to
        // the given table
        $owningOneToManyModels = $this->getOwningOneToManyModels($table);

        //first we have to create the file header for a standard php file
        $fileHeader = self::PHP_FILE_HEADER;

        //after the file header created, we can add the shopware AGPLv3 licence tag
        $licenceHeader = self::SHOPWARE_LICENCE;

        //after the licence added, we can declare all namespace and used namespaces
        $namespaceHeader = self::NAMESPACE_HEADER;

        //the last header is the class header, which contains the definition of the class
        $classHeader = $this->getClassDefinition($table);

        //now all headers are defined, we can add the class content.
        //first we create all normal column properties.
        $columnProperties = $this->getColumnsProperties($table);

        //after the normal column properties created, we can add the association properties.
        $associationProperties = $this->getAssociationProperties($table);

        //add the properties for the inversed association.
        $inversedAssociationProperties = $this->getInversedAssociationProperties($owningOneToManyModels);

        //add the constructor.
        $constructor = $this->getConstructor($owningOneToManyModels);

        //now all properties are declared, but the properties needs getter and setter function to get access from extern
        $columnFunctions = $this->getColumnsFunctions($table);

        //the association properties needs getter and setter, too.
        $associationFunctions = $this->getAssociationsFunctions($table);

        //add the getter functions for the inversed association properties.
        $inversedAssociationFunctions = $this->getInversedAssociationFunctions($owningOneToManyModels);

        //to concat the different source code paths, we create an array with all source code fragments
        $paths = array(
            $fileHeader,
            $licenceHeader,
            $namespaceHeader,
            $classHeader,
            implode("\n", $columnProperties),
            implode("\n", $associationProperties),
            implode("\n", $inversedAssociationProperties),
            $constructor,
            implode("\n", $columnFunctions),
            implode("\n", $associationFunctions),
            implode("\n", $inversedAssociationFunctions),
            '}'
        );

        //than we implode the source code paths with a line break
        $sourceCode = implode("\n", $paths);

        return $sourceCode;
    }

    /**
     * Returns the class definition for the passed table object
     * @param $table \Doctrine\DBAL\Schema\Table
     * @return mixed
     */
    protected function getClassDefinition($table)
    {
        $source = self::CLASS_HEADER;
        $className = $this->getClassNameOfTable($table);

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
     * @param $tableName
     * @return string
     */
    protected function getClassNameOfTableName($tableName)
    {
        if (!array_key_exists($tableName, $this->tableMapping)) {
            return '';
        }

        $parentTable = $this->tableMapping[$tableName];
        $fragments = explode("\\", $parentTable['namespace']);
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
     * only the defintion of the properties, not of the getters and seters.
     *
     * @param $table \Doctrine\DBAL\Schema\Table
     * @return array
     */
    protected function getColumnsProperties($table)
    {
        $columns = array();
        /**@var $column \Doctrine\DBAL\Schema\Column*/
        foreach ($table->getColumns() as $column) {
            $columns[] = $this->getColumnProperty($table,$column);
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
     * @param $table \Doctrine\DBAL\Schema\Table
     * @param $column \Doctrine\DBAL\Schema\Column
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
     * a class property. Filters the under score words to camcel case and
     * checks if the passed column is a foreign key column.
     * If the passed column is a foreign key column the function uses the class
     * name of the foreign table as property with an additional suffix "Id".
     *
     * @param $table \Doctrine\DBAL\Schema\Table
     * @param $column \Doctrine\DBAL\Schema\Column
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
        } else {
            return lcfirst($this->underscoreToCamelCase($column->getName()));
        }
    }

    /**
     * Converts underscore separated string into a camelCase separated string
     *
     * @param string $str
     * @return string
     */
    protected function underscoreToCamelCase($str)
    {
        $func = create_function('$c', 'return strtoupper($c[1]);');

        return preg_replace_callback('/_([a-zA-Z])/', $func, $str);
    }

    /**
     * Helper function to get the foreign key for the
     * passed column object.
     * If the column has a foreign key definition, the class name
     * of the foreign table will be used for the property name of the column.
     *
     * @param $table \Doctrine\DBAL\Schema\Table
     * @param $column \Doctrine\DBAL\Schema\Column
     * @return bool|\Doctrine\DBAL\Schema\ForeignKeyConstraint
     */
    protected function getColumnForeignKey($table, $column)
    {
        /**@var $foreignKey \Doctrine\DBAL\Schema\ForeignKeyConstraint*/
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
     * Helper function to covnert the boolean value of the function "column->getNotNull()" to
     * a string which can be used for the doctrine annoation.
     *
     * @param $column \Doctrine\DBAL\Schema\Column
     * @return string
     */
    protected function getColumnNullProperty($column)
    {
        if ($column->getNotnull()) {
            return 'false';
        } else {
            return 'true';
        }
    }

    /**
     * Helper function to convert some database types into supported
     * php types. For example the database type "text" will be converted
     * to "string"
     *
     * @param $column \Doctrine\DBAL\Schema\Column
     * @return string
     */
    public function getPropertyTypeOfColumnType($column)
    {
        if ($column->getType() instanceof \Doctrine\DBAL\Types\TextType) {
            return 'string';
        } else {
            return $column->getType()->getName();
        }
    }

    /**
     * Helper function to check if the passed column is the primary key
     * column.
     * In this case doctrine requires the @ORM\ID annoation and a primary key
     * strategy.
     *
     * @param $table \Doctrine\DBAL\Schema\Table
     * @param $column \Doctrine\DBAL\Schema\Column
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
     * code for the doctrine associaton properties for the passed table object.
     * This function creates only the property definition source code, not the getter
     * and setter source code for the properties.
     * @param $table \Doctrine\DBAL\Schema\Table
     * @return array
     */
    protected function getAssociationProperties($table)
    {
        $associations = array();
        /**@var $foreignKey \Doctrine\DBAL\Schema\ForeignKeyConstraint*/
        foreach ($table->getForeignKeys() as $foreignKey) {
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
     * @param $table \Doctrine\DBAL\Schema\Table
     * @param $foreignKey \Doctrine\DBAL\Schema\ForeignKeyConstraint
     * @return string
     */
    protected function getAssociationProperty($table, $foreignKey)
    {
        $source = self::ONE_TO_ONE_ASSOCIATION_PROPERTY;

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
     * Creates the source code for the doctrine association properties,
     * which inverse the passed models. That is, models having a property
     * that references the currently generated model in an 'inversedBy' field
     * of a ManyToOne annotation.
     *
     * @param array $referencingModels
     * @return string[]
     */
    protected function getInversedAssociationProperties($referencingModels)
    {
        // Generate one property for each referncing model
        $properties = array();
        foreach ($referencingModels as $referencingModel => $relationProperties) {
            // Compile the source snippet
            $source = self::ONE_TO_MANY_ASSOCIATION_PROPERTY;
            $source = str_replace('%owningClass%', $referencingModel, $source);
            $source = str_replace('%owningProperty%', $relationProperties['owningProperty'], $source);
            $source = str_replace('%property%', $relationProperties['invertingProperty'], $source);

            $properties[] = $source;
        }

        return $properties;
    }

    /**
     * Creates the source code for the custom constructor,
     * which initializes each ManyToOne property with an empty
     * ArrayCollection.
     *
     * @param array $referencingModels
     * @return string
     */
    protected function getConstructor($referencingModels)
    {
        if (count($referencingModels) === 0) {
            // No custom constructor required
            return '';
        }

        // Create an ArrayCollection initializer for each inverting property
        $initializations = array_map(function($item) {
            return '$this->'.$item['invertingProperty'].' = new ArrayCollection();';
        }, $referencingModels);

        // Compile the source snippet
        $source = self::CONSTRUCTOR;
        $source = str_replace('%propertyInitializations%', implode("\n        ", $initializations), $source);

        return $source;
    }

    /**
     * The getColumnsFunctions function creates the source code for the
     * getter and setter for all table columns properties.
     *
     * @param $table \Doctrine\DBAL\Schema\Table
     * @return array
     */
    protected function getColumnsFunctions($table)
    {
        $functions = array();
        foreach ($table->getColumns() as $column) {
            $functions[] = $this->getColumnFunctions($table, $column);
        }
        return $functions;

    }

    /**
     * Helper function to create the getter and setter source code
     * for the passed column object.
     *
     * @param $table \Doctrine\DBAL\Schema\Table
     * @param $column \Doctrine\DBAL\Schema\Column
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
     * @param $table \Doctrine\DBAL\Schema\Table
     * @return array
     */
    protected function getAssociationsFunctions($table)
    {
        $columns = array();
        /**@var $foreignKey \Doctrine\DBAL\Schema\ForeignKeyConstraint*/
        foreach ($table->getForeignKeys() as $foreignKey) {
            $columns[] = $this->getAssociationFunctions($foreignKey);
        }
        return $columns;
    }

    /**
     * Creates the getter and setter function source code for the passed
     * foreign key constraint object.
     *
     * @param $foreignKey \Doctrine\DBAL\Schema\ForeignKeyConstraint
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
     * Creates the source code for getter of the association properties,
     * which inverse the passed models. That is, models having a property
     * that references the currently generated model in an 'inversedBy' field
     * of a ManyToOne annotation.
     *
     * @param array $referencingModels
     * @return string[]
     */
    protected function getInversedAssociationFunctions($referencingModels)
    {
        // Generate one getter for each referencing model
        $getters = array();
        foreach ($referencingModels as $relationProperties) {
            // Compile the source snippet
            $source = self::ONE_TO_MANY_ASSOCIATION_FUNCTION;
            $source = str_replace('%upperPropertyName%', ucfirst($relationProperties['invertingProperty']), $source);
            $source = str_replace('%lowerPropertyName%', lcfirst($relationProperties['invertingProperty']), $source);

            $getters[] = $source;
        }

        return $getters;
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
        // Load the classes of the default path
        return $this->createTableMappingForPath($this->getModelPath());
    }

    /**
     * Helper function to create an table - class mapping of all models, which
     * are defined in the Shopware\CustomModels namespace
     *
     * @return array
     */
    public function createCustomModelsTableMapping()
    {
        // Load the classes of all registered custom model paths
        $classes = array();
        $modelPaths = Shopware()->ModelAnnotations()->getPaths();
        $registeredCustomModels = Shopware()->Loader()->getRegisteredNamespaces('Shopware\CustomModels');
        foreach ($registeredCustomModels  as $customModel) {
            if (in_array($customModel['path'], $modelPaths)) {
                $classes += $this->createTableMappingForPath($customModel['path']);
            }
        }

        return $classes;
    }

    /**
     * Helper function to create a table - class mapping of all models defined at
     * the given path.
     *
     * @param string $path The path from which the model files will be loaded.
     * @return An array containing the table mappings of the loaded model classes.
     */
    public function createTableMappingForPath($path)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $classes = array();

        /**@var $file \SplFileInfo*/
        foreach ($iterator as $file) {
            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if ($file->isDir() || $extension !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            //preg match for the model class name!
            $matches = array();
            preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches);
            $className = $matches[1];

            //preg match for the model namespace!
            $matches = array();
            preg_match('/namespace\s+(.*);/', $content, $matches);
            if (count($matches) === 0) {
                continue;
            }
            $namespace = $matches[1];

            //preg match for the model table name!
            $matches = array();
            preg_match('/@ORM\\\Table\\(name="(.*)"\\)/', $content, $matches);

            //repositories has no table annoation.
            if (count($matches) === 0) {
                continue;
            }
            $tableName = $matches[1];

            $classes[$tableName] = array(
                'class' => $className,
                'namespace' => $namespace
            );
        }
        return $classes;
    }

    /**
     * Uses the Doctrine annotation reader to check all models, which are
     * contained in the Shopware\CustomModels namespace, for a OneToMany
     * annotation which references the given table. All matching annotations
     * are parsed and both the names of the mapping property ('owningProperty')
     * and the inverting property ('invertingProperty') are added to an array,
     * which is saved together with the full class name of the model.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     * @return array
     * @throws \Exception
     */
    private function getOwningOneToManyModels($table)
    {
        // Create the full name (incl. namespace) of the given table
        $invertingClassName = $this->getClassNameOfTable($table);
        $invertingNamespace = self::ATTRIBUTE_NAMESPACE . '\\' . $invertingClassName;

        $referencingModels = array();
        $invertingPropertyNames = array();
        // Check all custom models
        foreach ($this->getCustomModelsTableMapping() as $mapping) {
            // Find the referencing property in the owning model as well as
            // the designated name of the inverting property
            $owningNamespace = $mapping['namespace'] . '\\' . $mapping['class'];
            $metaData = Shopware()->Models()->getClassMetadata($owningNamespace);
            foreach ($metaData->getReflectionProperties() as $property) {
                // Try to find a ManyToOne annotation at the current property,
                // which is inverted by the given table
                $mappingAnnotation = Shopware()->ModelAnnotations()->getReader()->getPropertyAnnotation($property, 'Doctrine\ORM\Mapping\ManyToOne');
                if ($mappingAnnotation === null || $mappingAnnotation->targetEntity !== $invertingNamespace) {
                    continue;
                }

                // Check for duplicates
                if (in_array($mappingAnnotation->inversedBy, $invertingPropertyNames)) {
                    throw new \Exception('Duplicate association property named "' . $mappingAnnotation->inversedBy . '" for generated model ' . $invertingNamespace . '. Please use a unique prefix for you attribute associations.');
                }
                $invertingPropertyNames[] = $mappingAnnotation->inversedBy;

                // Namespaces do match, hence save the property
                $referencingModels[$owningNamespace] = array(
                    'owningProperty' => $property->name,
                    'invertingProperty' => $mappingAnnotation->inversedBy
                );

                // A model should reference the same foreign model only once, hence stop searching
                break;
            }
        }

        return $referencingModels;
    }

    /**
     * Determines the class name for the given table. The difference to
     * 'getClassNameOfTableName()' is, that this method handles attribute tables
     * correctly, by returning the name of their parent class.
     *
     * @param \Doctrine\DBAL\Schema\Table $table
     * @return string
     */
    private function getClassNameOfTable($table)
    {
        $className = $table->getName();

        //check if the passed table is an shopware attribute table.
        if (strpos($table->getName(), '_attributes')) {

            //if the table is an attribute table we have to use the class name of the parent table.
            $parentClass = str_replace('_attributes', '', $table->getName());
            $className = $this->getClassNameOfTableName($parentClass);

            //if the passed table is not an attribute table, we have to check if the table is already declared
        } elseif (array_key_exists($table->getName(), $this->getTableMapping())) {

            //if this is the case we will use the already declared class name
            $className = $this->tableMapping[$table->getName()]['class'];
        }

        return $className;
    }

}
