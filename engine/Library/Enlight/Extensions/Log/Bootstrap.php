<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Extensions
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Enlight log extension to support various writer.
 *
 * The Enlight_Extensions_Log_Bootstrap sets the log resource available.
 * It supports various writer, for example firebug, database tables and log files.
 * In additionally the Enlight_Extensions_Log_Bootstrap support to log the ip and the user agents.
 *
 * @category   Enlight
 * @package    Enlight_Extensions
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Extensions_Log_Bootstrap extends Enlight_Plugin_Bootstrap_Config
{
    /**
     * @var Zend_Wildfire_Channel_HttpHeaders Contains an instance of the Zend_Wildfire_Channel_HttpHeaders
     */
    protected $channel;

    /**
     * @var Enlight_Components_Log Contains an instance of the Enlight_Components_Log.
     */
    protected $log;

    /**
     * Installs the log extension plugin.
     * Subscribes the init resource event to initial the log resource,
     * the Enlight_Controller_Front_RouteStartup event to startup the routing process and
     * the Enlight_Controller_Front_DispatchLoopShutdown event to flush the wildfire channel.
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_Log', 'onInitResourceLog');

        $this->subscribeEvent('Enlight_Controller_Front_RouteStartup', 'onRouteStartup');

        $this->subscribeEvent('Enlight_Controller_Front_DispatchLoopShutdown', 'onDispatchLoopShutdown', 500);
        /*array(
            'writerName' => 'Db',
            'writerParams' => array(
                'table' => 'log',
                'db' => $this->Application()->Db(),
                'columnMap' => array(
                    'priority'       => 'priorityName',
                    'message'        => 'message',
                    'date'           => 'timestamp',
                    'remote_address' => 'remote_address',
                    'user_agent'     => 'user_agent',
                )
            ),
            'filterName' => 'Priority',
            'filterParams' => array(
                'priority' => Enlight_Components_Log::ERR
            )
        )
        array(
            'writerName' => 'Mail',
            'writerParams' => array(
                //'mail' => '',
                'from' => 'info@shopware.de',
                'to' => 'info@shopware.de',
                'subjectPrependText' => 'Fehler: '
            ),
            'filterName' => 'Priority',
            'filterParams' => array(
                'priority' => Enlight_Components_Log::WARN
            )
        )*/
        return true;
    }

    /**
     * Sets the given Zend_Log object into the internal log property.
     * If no log given, a new instance with the internal configuration will be created.
     * @param Enlight_Components_Log|Zend_Log $log
     */
    public function setResource(Zend_Log $log = null)
    {
        if ($log === null) {
            $config = $this->Config();
            if (count($config) === 0) {
               $config = new Enlight_Config(array(
                   array('writerName' => 'Null'),
                   array('writerName' => 'Firebug')
               ));
            }
            $log = Enlight_Components_Log::factory($config);
        }
        $this->log = $log;
    }

    /**
     * Setter method for the channel property. If no channel given
     * a new instance of the Zend_Wildfire_Channel_HttpHeaders will be used.
     *
     * @param Zend_Wildfire_Channel_HttpHeaders $channel
     */
    public function setFirebugChannel($channel = null)
    {
        if ($channel === null) {
            $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        }
        $this->channel = $channel;
    }

    /**
     * Getter method for the log property which contains an instance of the Enlight_Components_Log.
     * @return Enlight_Components_Log
     */
    public function Resource()
    {
        if ($this->log === null) {
            $this->setResource();
        }
        return $this->log;
    }

    /**
     * Getter method of the channel property. If the channel isn't instantiated
     * a new instance of the Zend_Wildfire_Channel_HttpHeaders will be initial.
     * @return Zend_Wildfire_Channel_HttpHeaders
     */
    public function FirebugChannel()
    {
        if ($this->channel === null) {
            $this->setFirebugChannel();
        }
        return $this->channel;
    }

    /**
     * Resource handler for log plugin
     *
     * @param   Enlight_Event_EventArgs $args
     * @return  Enlight_Components_Log
     */
    public function onInitResourceLog(Enlight_Event_EventArgs $args)
    {
        return $this->Resource();
    }

    /**
     * Listener method for the Enlight_Controller_Front_RouteStartup event.
     * Adds the user-agent and the remote-address to the log component.
     * Sets the request and the response object into the Zend_Wildfire_Channel_HttpHeaders.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onRouteStartup(Enlight_Event_EventArgs $args)
    {
        /** @var $request Enlight_Controller_Request_Request */
        $request = $args->getSubject()->Request();
        /** @var $request Enlight_Controller_Request_ResponseHttp */
        $response = $args->getSubject()->Response();

        /** @var $log Zend_Log */
        $log = $this->Resource();

        $log->setEventItem('remote_address', $request->getClientIp(false));
        $log->setEventItem('user_agent', $request->getHeader('USER_AGENT'));

        $channel = $this->FirebugChannel();
        $channel->setRequest($request);
        $channel->setResponse($response);
    }

    /**
     * Listener method for the Enlight_Controller_Front_DispatchLoopShutdown event.
     * On Dispatch Shutdown collect sql performance results and dump to log component.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
    {
        if ($this->channel !== null) {
            $this->channel->flush();
        }
    }
}
