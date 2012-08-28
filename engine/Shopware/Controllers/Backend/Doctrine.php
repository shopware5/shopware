<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Customer
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;

/**
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Oliver Denter
 * @author $Author$
 * @package Shopware_Controllers
 * @subpackage Backend
 */
class Shopware_Controllers_Backend_Doctrine extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Index action of the doctrine controller. Starts the model generation.
     */
    public function indexAction() {
        try {
            $this->generate();
            die();
        }
        catch (Exception $e) {
           echo "<pre>";
           print_r($e);
           echo "</pre>";
           exit();
        }
    }

    /**
     * Generates the models for the extension tables like s_user_extension.
     * This tables can be modified from plugins so we have to generate the models after each plugin installation.
     */
    private function generate()
    {
        $tableMapping = $this->getTableMapping();
        $generator = $this->getGenerator();
        $generator->tableMappings = $tableMapping;
        $enginePath = Shopware()->OldPath('engine');
        $enginePath = substr($enginePath, 0, strlen($enginePath)-1);
        $allMetaData = $this->getExtensionTables();

        if (count($allMetaData) > 0) {
            foreach($allMetaData as $metaData) {
                if (empty($metaData)) {
                    continue;
                }
                if (array_key_exists($metaData->name, $tableMapping)) {
                    $generator->writeEntityClass($metaData, $enginePath);
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getExtensionTables()
    {
        $factory = $this->getFactoryWithDatabaseDriver();
        $allMetaData = $factory->getAllMetadata();
        $extensionTables = array();

        /**@var $metaData \Doctrine\ORM\Mapping\ClassMetadata*/
        foreach($allMetaData as $metaData) {
            if (strpos($metaData->getTableName(), '_attributes') !== false) {
                $extensionTables[] = $metaData;
            }
        }
        return $extensionTables;
    }

    /**
     * Creates a disconnected meta data factory with a database mapping driver
     * to get the meta data for the extension tables directly from the database.
     *
     * @return Doctrine\ORM\Tools\DisconnectedClassMetadataFactory
     */
    private function getFactoryWithDatabaseDriver() {
        $driver = $this->getDatabaseDriver();
        Shopware()->Models()->getConfiguration()->setMetadataDriverImpl($driver);
        $factory = new DisconnectedClassMetadataFactory();
        $factory->setEntityManager(Shopware()->Models());
        return $factory;
    }

    /**
     * @return Shopware\Components\Model\DatabaseDriver
     */
    private function getDatabaseDriver() {
        $platform = Shopware()->Models()->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
        $driver = new \Shopware\Components\Model\DatabaseDriver(
            Shopware()->Models()->getConnection()->getSchemaManager()
        );
        return $driver;
    }

    /**
     * Generates the Shopware\Components\Model\Generator
     * @return Shopware\Components\Model\Generator
     */
    private function getGenerator() {
        $generator = new \Shopware\Components\Model\Generator();
        $generator->setGenerateAnnotations(true);
        $generator->setGenerateStubMethods(true);
        $generator->setRegenerateEntityIfExists(true);
        $generator->setUpdateEntityIfExists(false);
        $generator->setBackupExisting(false);
        return $generator;
    }

    /**
     * Creates a mapping array for each shopware model.
     * The array key is the table name and the array item contains the model name and the namespace of the model.
     * @return array
     */
    private function getTableMapping()
    {
        $driver = $this->getDatabaseDriver();
        $allMetaData = Shopware()->Models()->getMetadataFactory()->getAllMetadata();
        $tableMapping = array();
        /**@var $metaData \Doctrine\ORM\Mapping\ClassMetadata*/
        foreach($allMetaData as $metaData) {
            $arrayKey = $driver->getClassNameForTable($metaData->getTableName());
            $tableMapping[$arrayKey] = array(
                'namespace' => $metaData->namespace,
                'name' =>  str_replace($metaData->namespace . '\\', '', $metaData->name)
            );
        }

        return $tableMapping;
    }

}