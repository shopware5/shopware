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
 * @subpackage ModelManager
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Fabian Engels
 * @author     $Author$
 */

use DoctrineExtensions\Paginate\Paginate;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
/**
 * Shopware Controller ModelManager
 *
 * The model manager backend controller handles all actions concerning the model manager backend module
 */
class Shopware_Controllers_Backend_ModelManager extends Enlight_Controller_Action
{

    /**
     * default init method
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function init()
    {
        $this->Front()->Plugins()->ScriptRenderer()->setRender();
        $this->Front()->Plugins()->JsonRequest()
              ->setParseInput()
              ->setParseParams(array('group', 'sort'))
              ->setPadding($this->Request()->targetField);
    }

    /**
     * since only json data will be renderer, set the json renderer as default
     *
     * @return void
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), array('index', 'load'))) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    /**
     * @codeCoverageIgnore
     * @return void|string
     */
    public function indexAction()
    {

    }

    /**
     * load action for the script renderer.
     */
    public function loadAction()
    {

    }

    /**
     * this function will generate a query to get tables for the center grid
     * per default, it will get all tables starting with "s_"
     * if a search was performed, thus, the filter variable is set, it will remove any occurrences of
     * "s_" in the search string itself and return all tables starting with "s_" AND also containing the searchstring afterwards
     */
    public function getTablesAction()
	{
        $start = $this->Request()->start;
        $limit = $this->Request()->limit;
        $searchString = $this->Request()->filter;
        $config = Shopware()->Db()->getConfig();

        //filter user input starting with "s_"
        if (substr($searchString,0,2) == 's_') {
            $searchString = substr_replace($searchString,'',0,2);
        }

        //if no search was performed, display all tables starting with "s_"
        //else display all tables containing the searchstring and starting with "s_"
        if (isset($searchString)) {
            $like = "AND TABLE_NAME LIKE 's\_%" . $searchString ."%'";
        } else {
            $like = "AND TABLE_NAME LIKE 's\_%' ";
        }

        $sql= "SELECT TABLE_NAME as name FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = ? " . $like . " ORDER BY TABLE_NAME";

        //add start and limit, if necessary
        if (isset($start) && isset($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $data = Shopware()->Db()->fetchAll($sql, array($config['dbname']));
        $total = Shopware()->Db()->fetchOne("SELECT COUNT(*) FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = ? " . $like, array($config['dbname']));

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
	}

    /**
     * this will generate a doctrine model based on the requested tableName
     * to do this, it will take a table name like "s_articles_bundles" and
     * convert it into a valid string for the getEntityCode() function to accept (like "SArticlesBundles")
     * afterwards, it will replace all invalid php-types with the proper ones, remove the table name prefix,
     * the orm prefix and also multiple linebreaks, to get the code, which in turn will be handed to the view
     *
     */
    public function getDoctrineModelAction()
    {
        $params = $this->Request()->getParams();
        $tableName = $params['tableName'];

        if (!empty($tableName)) {

            //format the table name, so the getEntityCode function will accept it
            //converts "s_articles_bundles" to "SArticlesBundles"
            $tableName = str_replace("_"," ",$tableName);
            $tableName = ucwords($tableName);
            $tableName = str_replace(" ","",$tableName);

            //generates the basic model
            $code = $this->getEntityCode($tableName);

            //since datetime is not a valid php type, replace it with DateTime
            $code = str_ireplace('DateTime', '\DateTime', $code);
            $code = str_ireplace('@var datetime', '@var \DateTime',$code);
            $code = str_ireplace('@param datetime', '@param \DateTime',$code);
            $code = str_ireplace('@return datetime', '@return \DateTime',$code);
            $code = str_ireplace('@var date', '@var \DateTime',$code);
            $code = str_ireplace('@param date', '@param \DateTime',$code);
            $code = str_ireplace('@return date', '@return \DateTime',$code);

            //since text is not a valid php type, replace it with string
            $code = str_ireplace('@var text', '@var string',$code);
            $code = str_ireplace('@param text', '@param string',$code);
            $code = str_ireplace('@return text', '@return string',$code);

            //removes the table prefix, since it's already set in the doctrine connection
            $code = str_replace('Table(name="s_s_',  'Table(name="', $code);
            //shouldn't be used since models would be automatically updated otherwise
            $code = str_replace('use Doctrine\ORM\Mapping as ORM;', '', $code);
            //remove the orm prefix
            $code = str_replace('ORM\\', '', $code);

            //remove multiple linebreaks to get cleaner code
            $code = preg_replace("/\n\n+/", "\n\n", $code);

            //generate the data array, which will be handed to the view for rendering
            $data = array('tableName' => $params['tableName'],'content' => $code);
            $this->View()->assign(array('success' => true, 'data' => $data));
        }
    }

//    /**
//     * this will generate an ExtJS model based on the requested tableName
//     */
//    public function getExtJsModel() {
//
//        $params = $this->Request()->getParams();
//        $tableName = $params['tableName'];
//
//        $config = Shopware()->Db()->getConfig();
//        $sql = "SELECT COLUMN_NAME, DATA_TYPE FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = ? AND `TABLE_NAME`= ? ORDER BY POSITION";
//        $columns = Shopware()->Db()->fetchAll($sql, array($config['dbname'], $tableName));
//
//        array (
//            'datetime' => 'date',
//            'date' => 'date',
//            'varchar' => 'string',
//            'char' => 'string',
//            'longtext' => 'string',
//            'integer' => 'int',
//            'int' => 'int',
//            'decimal' => 'int',
//            'number' => 'int',
//            'varchar' => 'float',
//            'varchar' => 'object',
//
//        )
//
//
//
//    }

    /**
     * returns the php code for the passed table name
     * expects that the table name has the follow format:
     * name in database : "s_articles"
     * expected name    : "SArticles"
     *
     * @param $tableName
     * @return string
     */
    private function getEntityCode($tableName) {
        $generator = $this->getGenerator();
        $factory = $this->getFactoryWithDatabaseDriver();
        $metaData = $factory->getMetadataFor($tableName);
        $code = $generator->generateEntityClass($metaData);
        return $code;
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

}