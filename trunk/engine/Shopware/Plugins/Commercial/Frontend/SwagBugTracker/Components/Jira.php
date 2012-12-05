<?php
use \Shopware\Components\Jira\Core\Rest\Client;
use \Shopware\Components\Jira\Core\Service\Context;
use \Shopware\Components\Jira\Core\Mapper\MapperFactory;
use \Shopware\Components\Jira\Core\Storage\Mixed\GatewayFactory;
use \Shopware\Components\Jira\API\Model\Query;

// Load Guzzle HTTP library
require_once(dirname(__FILE__) . '/Jira/Vendor/guzzle.phar');

/**
 * This component interacts with the jira api interface
 *
 * @copyright Copyright (c) 2011, Shopware AG
 * @author d.scharfenberg
 * @author $Author$
 * @package Shopware
 * @subpackage Controllers_Frontend
 * @creation_date 21.05.12 13:10
 * @version $Id$
 */
class Shopware_Components_Jira extends Enlight_Class
{
    /**
     * Holds the configuration of the jira interface
     * This data are required for the interaction with the jira server
     *
     * @var array
     */
    private $config = array(
        'jiraRestServer' => 'http://jira_extern:123a21ex%@217.89.109.14:1234/jira',
        'jiraUser' => 'Qafoo GmbH',
        'db_overwrite' => array(
              'dbname' => 'jira',
             'username' => 'jiraextern',
	         'password' => 'Lx2xLz6KfrYLahSH',
	         'host' => '217.89.109.14',
	         'port' => '54622',
	         'charset' => 'utf8'
        )
    );

    /**
     * Holds the context implementation of the jira api
     * @var Shopware\Components\Jira\Core\Service\Context
     */
    private $context;

    /**
     * Initialize a context implementation
     * of the jira api interface
     *
     * - Initializes the restful client to interact with the jira server
     * - Initializes the context implementation of the jira api
     */
    public function init() {
        //Initializes a restful client
        $rest = new Client(
            new \Guzzle\Http\Client($this->config['jiraRestServer'], array('version' => 'latest'))
        );

        //Initializes the pdo object for the jira api
        //Uses the default database data and starts a overwrite with the local data
        $default_db = Shopware()->getOption('db');
        $db_config = array_merge($default_db, $this->config['db_overwrite']);
        $db = Enlight_Components_Db::factory(
            isset($db_config['adapter']) ? $db_config['adapter'] : 'PDO_MYSQL',
            $db_config
        );
        $db->getConnection();

        //Initializes a context implementation
        $this->context = new Context();
        $this->context->initialize(
            new MapperFactory($this->context),
            new GatewayFactory($db, $rest),
            $this->config['jiraUser']
        );
    }

    /**
     * Checks if the given method is available in the context object
     * and bubbles the call to the context instance
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if(method_exists($this->context, $method)) {
            return call_user_func(array( $this->context, $method), $args);
        } else {
            throw new Enlight_Exception(sprintf('Method "%s::%s" not found failure', get_class($this->context), $method));
        }
    }

    /**                                                         **
     * --------------------------------------------------------- *
     * THE FOLLOWING METHODS ARE HELPER METHODS FOR THE JIRA API *
     * --------------------------------------------------------- *
     **                                                         **/

    /**
     * Generates a cleaned sort for the jira api query
     * For the parameter sort will expected a array in the
     * extjs store format
     *
     * @param $sort array An array with the values "property" and "direction"
     *
     * For example:
     * Array(
     *      ["property"] => "createdAt",
     *      ["direction"] => "ASC"
     * )
     *
     * See also the constants of the class Shopware\Components\Jira\API\Model\Query
     *
     * @return array An array with the same structure as the $sort parameter
     */
    public function prettyUpSort($sort)
    {
        if($sort['direction'] == 'ASC') {
            $sort_direction = Query::ORDER_ASC;
        } else {
            $sort_direction = Query::ORDER_DESC;
        }

        switch($sort['property']) {
            case 'name':
                $sort_property = Query::ORDER_BY_SUMMARY;
            break;
            case 'status':
                $sort_property = Query::ORDER_BY_STATUS;
            break;
            case 'priority':
                $sort_property = Query::ORDER_BY_PRIORITY;
            break;
            case 'createdAt':
                $sort_property = Query::ORDER_BY_CREATED_AT;
            break;
            case 'modifiedAt':
                $sort_property = Query::ORDER_BY_MODIFIED_AT;
            break;
            case 'reporter':
                $sort_property = Query::ORDER_BY_REPORTER;
            break;
            case 'assignee':
                $sort_property = Query::ORDER_BY_ASSIGNEE;
            break;
            case 'votes':
                $sort_property = Query::ORDER_BY_VOTES;
            break;
            case 'type':
                $sort_property = Query::ORDER_BY_TYPE;
            break;
            default:
                $sort_property = Query::ORDER_BY_CREATED_AT;
            break;
        }

        return array(
            'property'  => $sort_property,
            'direction' => $sort_direction
        );
    }
}